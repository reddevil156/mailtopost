<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\core;

use phpbb\config\config;
use phpbb\auth\auth;
use phpbb\request\request;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\db\driver\driver_interface;
use david63\mailtopost\core\functions;
use david63\mailtopost\pop3mail\pop3;
use david63\mailtopost\pop3mail\mime_parser;

/**
* Mail to Post process class
*/
class mailtopost
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \david63\mailtopost\core\functions */
	protected $functions;

	/** @var string phpBB tables */
	protected $tables;

	/** @var \david63\mailtopost\pop3mail\pop3 */
	protected $pop3;

	/** @var \david63\mailtopost\pop3mail\pop3_stream */
	protected $pop3_stream;

	/** @var \david63\mailtopost\pop3mail\mime_parser */
	protected $mime_parser;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* The database table the mailtopost log is stored in
	*
	* @var string
	*/
	protected $mailtopost_table;

	/** Variables used in this class */
	protected $username;
	protected $log_message;
	protected $mail_address;
	protected $mail_date;
	protected $mail_subject;
	protected $topic_id;
	protected $type;
	protected $user_colour;
	protected $user_email;
	protected $user_id;
	protected $user_mtp_forum;

	/** The post_data data array */
	private $post_data = array(
		'topic_id'				=> 0,
		'forum_id'				=> 0,
		'icon_id'				=> 0,
		'poster_id'				=> 0,
		'topic_first_post_id'	=> 0,
		'topic_last_post_id'	=> 0,

		// Defining Post Options
		'enable_bbcode'		=> 1,
		'enable_smilies'	=> 1,
		'enable_urls'       => 1,
		'enable_sig'        => 1,

		// Message Body
		'message'		=> '',
		'message_md5'	=> '',

		// Values from generate_text_for_storage()
		'bbcode_bitfield'	=> '',
		'bbcode_uid'		=> '',

		// Other Options
		'post_edit_locked'	=> false,
		'topic_title'		=> '',

		// Email Notification Settings
		'notify_set'	=> 0,
		'notify'		=> '',
		'post_time'		=> 0,
		'forum_name'	=> '',

		// Indexing
		'enable_indexing' => true,
	);

	/**
	* Constructor for mailtopost process class
	*
	* @param \phpbb\config\config							$config					Config object
	* @param \phpbb\auth\auth 								$auth					Auth object
	* @param \phpbb\request\request							$request				Request object
	* @param \phpbb\user									$user					User object
	* @param \phpbb\language\language						$language				Language object
	* @param \phpbb\log\log									$log					Log object
	* @param \phpbb_db_driver								$db						The db connection
	* @param \david63\mailtopost\core\functions				$functions				Functions for the extension
	* @param array											$tables					phpBB db tables
	* @param string											$smailtopost_log_table  Name of the table used to store mailtopost log data
	* @param \david63\mailtopost\pop3mail\pop3				$pop3					Mail pop3 class
	* @param \david63\mailtopost\pop3mail\mime_parser		$mime_parser			Mail mime parser class
	* @param string 										$phpbb_root_path		phpBB root path
	* @param string 										$php_ext				php ext
	*
	* @access public
	*/
	public function __construct(config $config, auth $auth, request $request, user $user, language $language, log $log, driver_interface $db, functions $functions, $tables, $smailtopost_log_table, pop3 $pop3, mime_parser $mime_parser, $phpbb_root_path, $php_ext)
	{
		$this->config				= $config;
		$this->auth					= $auth;
		$this->request				= $request;
		$this->user					= $user;
		$this->language				= $language;
		$this->log					= $log;
		$this->db					= $db;
		$this->functions			= $functions;
		$this->tables				= $tables;
		$this->mailtopost_log_table	= $smailtopost_log_table;
		$this->pop3					= $pop3;
		$this->mime_parser			= $mime_parser;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->phpEx				= $php_ext;
	}

	/**
	* Process the mail to post data
	*
	* @return null
	* @access public
	*/
	public function process($cron = true)
	{
		// Is the form being submitted?
		if ($this->request->is_set_post('submit') || $cron)
		{
			// Initialise variables
			$this->user_id = $this->user->data['user_id'];
			$this->user_mtp_forum = $this->topic_id = $this->mail_date = 0;
			$this->user_email = $this->mail_subject = $this->mail_address = $this->username = $this->user_colour = '';

			// Set the variables for the type of run
			if (!$cron)
			{
				$this->type = 'M';
				$this->pop3->debug = $this->pop3->html_debug = $this->config['mtp_debug'];

				// Add the language files
				$this->language->add_lang('acp_mailtopost_log', $this->functions->get_ext_namespace());
			}
			else
			{
				$this->type = 'C';

				// We cannot run debug in Cron
				$this->pop3->debug = $this->pop3->html_debug = 0;
			}

			stream_wrapper_register('mtp', 'david63\mailtopost\pop3mail\pop3_stream');

			// We do not want to run if no permissions are set
			if (!$this->functions->get_perms_count())
			{
				$this->mtp_log('NO_PERM_SET');
			}
			else if ($error = $this->pop3->Open() == '')
			{
				if ($error = $this->pop3->Login($this->config['mtp_user'], $this->config['mtp_password'], $this->config['mtp_apop']) == '')
				{
					if ($error = $this->pop3->Statistics($messages, $size) == '')
					{
						if ($messages > 0)
						{
							$this->pop3->GetConnectionName($connection_name);

							for ($message = 1; $message <= $messages; $message++)
							{
								// Reset the variables
								$this->user_mtp_forum = $this->topic_id = $this->mail_date = 0;
								$this->user_email = $this->mail_subject = $this->mail_address = $this->username = $this->user_colour = '';
								$mode = 'post';
								$this->config->set('mtp_lock', true, true);

								$message_file	= 'mtp://' . $connection_name . '/' . $message;
								$parameters 	= array(
									'File' => $message_file,
								);

								$success = $this->mime_parser->Decode($parameters, $decoded);

								if (!$success)
								{
									$mailtopost_message = $this->language->lang('DECODE_ERROR');
									$this->mtp_log('DECODE_ERROR');
								}
								else
								{
									// Need to trap images
									if (strpos($decoded[0]['Parts'][1]['Headers']['content-type:'], 'image') !== false)
									{
										$this->pop3->DeleteMessage($message);
										$mailtopost_message = $this->language->lang('IMAGE_ERROR');
										$this->mtp_log('IMAGE_ERROR');
									}
									// Also trap attachments
									else if (strpos($decoded[0]['Parts'][1]['Headers']['content-type:'], 'application') !== false)
									{
										$this->pop3->DeleteMessage($message);
										$mailtopost_message = $this->language->lang('ATTACHMENT_ERROR');
										$this->mtp_log('ATTACHMENT_ERROR');
									}
									else
									{
										// Grab the data we need from the mail message
										$this->mail_address	= strtolower($decoded[0]['ExtractedAddresses']['from:'][0]['address']);
										$this->mail_subject	= $decoded[0]['Headers']['subject:'];
										$this->mail_date	= strtotime($decoded[0]['Headers']['date:']);
										$mail_body 			= $decoded[0]['Parts'][0]['Body'];
										$dkim_signature		= $decoded[0]['Headers']['dkim-signature:'];
										/**
										$ip_address			= $decoded[0]['Headers']['received:'][1];
										$ip = strstr($ip_address, '[');
										$ip = substr(strstr($ip, ']', true), 1);
										**/

										// Make sure that there is some valid text in the message
										if (strlen($mail_body) == 7) // This value may not be correct
										{
											$this->pop3->DeleteMessage($message);
											$mailtopost_message = $this->language->lang('BLANK_MESSAGE');
											$this->mtp_log('BLANK_MESSAGE');
										}
										else
										{
											// Get the user's data
											$sql = 'SELECT user_id, username, user_email, user_colour, user_mtp_forum
												FROM ' . $this->tables['users'] . '
												WHERE ' . $this->db->sql_lower_text('user_email') . ' = "' . $this->mail_address . '"';

											$result = $this->db->sql_query($sql);

											// Validate the user's data
											if ($this->db->sql_affectedrows($result) == 0)
											{
												$this->db->sql_freeresult($result);
												$this->pop3->DeleteMessage($message);
												$mailtopost_message = $this->language->lang('NO_EMAIL');
												$this->mtp_log('NO_EMAIL');
											}
											else if ($this->db->sql_affectedrows($result) > 1)
											{
												$this->db->sql_freeresult($result);
												$this->pop3->DeleteMessage($message);
												$mailtopost_message = $this->language->lang('MULTIPLE_EMAIL');
												$this->mtp_log('MULTIPLE_EMAIL');
											}
											else
											{
												$row 				= $this->db->sql_fetchrow($result);
												$this->user_id		= $row['user_id'];
												$this->username 	= $row['username'];
												$this->user_email 	= $row['user_email'];
												$this->user_colour	= $row['user_colour'];

												if ($this->config['mtp_use_default_forum'])
												{
													$this->user_mtp_forum = $this->config['mtp_default_forum'];
												}
												else
												{
													$this->user_mtp_forum = ($row['user_mtp_forum'] == 0) ? $this->config['mtp_default_forum'] : $row['user_mtp_forum'];
												}

												$this->db->sql_freeresult($result);

												// Does the user posting the email have permission?
												$user_auth	= new \phpbb\auth\auth();
												$userdata 	= $user_auth->obtain_user_data($this->user_id);
												$user_auth->acl($userdata);
												if (!$user_auth->acl_get('u_mailtopost'))
												{
													$this->pop3->DeleteMessage($message);
													$mailtopost_message = $this->language->lang('NO_PERMISSION');
													$this->mtp_log('NO_PERMISSION');
												}
												// Do a basic check for mail spoofing
												else if ($this->config['mtp_mail_spoof'])
												{
													foreach ($dkim_signature as $sig)
													{
														$found = (strstr($sig, substr(strstr($this->user_email, '@'), 1))) ? true : false;
													}

													if (!$found)
													{
														$this->pop3->DeleteMessage($message);
														$mailtopost_message = $this->language->lang('SPOOF_EMAIL');
											   			$this->mtp_log('SPOOF_EMAIL');
													}
												}
												else
												{
													if (!$this->config['mtp_new_topic'])
													{
														// Is this a new topic or a reply?
														$sql = 'SELECT topic_id, topic_title
															FROM ' . $this->tables['topics'] . '
										   					WHERE topic_title = "' . $this->mail_subject . '"
															AND forum_id = ' . $this->user_mtp_forum;

														$result = $this->db->sql_query($sql);

														switch ($this->db->sql_affectedrows($result))
														{
															case 0:
																$mode = 'post';
															break;

															case 1:
																$mode = 'reply';
															break;

															default:
																$this->pop3->DeleteMessage($message);
																$mailtopost_message = $this->language->lang('MULTIPLE_TOPICS');
																$this->mtp_log('MULTIPLE_TOPICS');
															break;
														}

														$this->topic_id = $this->db->sql_fetchfield('topic_id');

														$this->db->sql_freeresult($result);
													}

													// Need to do a bit of reformatting before using $mail_body
													$mail_body = $this->functions->reformat_text($mail_body);

													// Load the message parser
													if (!class_exists('parse_message'))
													{
														include("{$this->phpbb_root_path}includes/message_parser.$this->phpEx");
													}

													$mail_parser = new \parse_message($mail_body);

													// Parse the post
													$mail_parser->parse($this->post_data['enable_bbcode'], $this->post_data['enable_urls'], $this->post_data['enable_smilies']);

													// Set the message
													$this->post_data['bbcode_bitfield']			= $mail_parser->bbcode_bitfield;
													$this->post_data['bbcode_uid']				= $mail_parser->bbcode_uid;
													$this->post_data['force_approved_state']	= $this->config['mtp_moderate'];
													$this->post_data['forum_id'] 				= $this->user_mtp_forum;
													$this->post_data['message']					= $mail_parser->message;
													$this->post_data['message_md5']				= md5($mail_parser->message);
													$this->post_data['force_visibility']		= $this->config['mtp_moderate'];
													$this->post_data['topic_id']				= $this->topic_id;

													// Now submit the post
													if (!function_exists('submit_post'))
													{
														include("{$this->phpbb_root_path}includes/functions_posting.$this->phpEx");
													}

													// Only here to not break "submit_post()"
													$poll_data = array();

													$url = submit_post($mode, $this->mail_subject, $this->username, POST_NORMAL, $poll_data, $this->post_data);

													// Get the topic_id from the returned $url string
													$this->topic_id = strstr($url, "t=");
													$this->topic_id = ($cron && $mode == 'post') ? substr($this->topic_id, 2) : substr(strstr($this->topic_id, "&", true), 2);

													// And now the post_id
													$sql = 'SELECT post_id
														FROM ' . $this->tables['posts'] . '
														WHERE topic_id = ' . $this->topic_id . '
														ORDER BY post_id DESC';

													$result 	= $this->db->sql_query($sql);
													$post_id	= $this->db->sql_fetchfield('post_id');

													$this->db->sql_freeresult($result);

													// Now update the post details in the forums, topics and posts tables
													$set_forum = array(
														'forum_last_poster_id' 		=> (int) $this->user_id,
														'forum_last_poster_name' 	=> $this->username,
														'forum_last_poster_colour'	=> $this->user_colour,
													);

													$set_topic = array(
														'topic_last_poster_id' 		=> (int) $this->user_id,
														'topic_last_poster_name' 	=> $this->username,
														'topic_last_poster_colour'	=> $this->user_colour,
													);

													$set_post = array(
														'poster_id' => (int) $this->user_id,
													);

													if ($mode != 'reply')
													{
														$set_topic = array_merge($set_topic, array(
															'topic_poster' 				=> (int) $this->user_id,
															'topic_first_poster_name' 	=> $this->username,
															'topic_first_poster_colour'	=> $this->user_colour,
														));
													}

													if (!$this->config['mtp_post_date'])
													{
														$set_forum = array_merge($set_forum, array(
															'forum_last_post_time' => (int) $this->mail_date,
														));

														$set_topic = array_merge($set_topic, array(
															'topic_time' => (int) $this->mail_date,
														));

														$set_post = array_merge($set_post, array(
															'post_time' => (int) $this->mail_date,
														));
													}

													$sql = 'UPDATE ' . $this->tables['forums'] . '
														SET ' . $this->db->sql_build_array('UPDATE', $set_forum) . '
														WHERE forum_id = ' . (int) $this->user_mtp_forum;

													$this->db->sql_query($sql);

													$sql = 'UPDATE ' . $this->tables['topics'] . '
														SET ' . $this->db->sql_build_array('UPDATE', $set_topic) . '
														WHERE topic_id = ' . $this->topic_id;

													$this->db->sql_query($sql);

													$sql = 'UPDATE ' . $this->tables['posts'] . '
														SET ' . $this->db->sql_build_array('UPDATE', $set_post) . '
														WHERE post_id = ' . $post_id;

													$this->db->sql_query($sql);

													$mailtopost_message = $this->language->lang('SUCCESS');
													$this->mtp_log('SUCCESS');

													$this->pop3->DeleteMessage($message);
												}
											}
										}
									}
								}
							}
						}
						else
						{
							$mailtopost_message = $this->language->lang('NO_DATA');
							$this->mtp_log('NO_DATA');
						}
					}
					$this->config->set('mtp_lock', false, true);
					$this->pop3->Close();
					$this->pop3->CloseConnection();
				}
				else
				{
					$mailtopost_message = $this->language->lang('LOGIN_ERROR');
					$this->mtp_log('LOGIN_ERROR');
				}
			}
			else
			{
				$mailtopost_message = $this->language->lang('MAILBOX_ERROR');
				$this->mtp_log('MAILBOX_ERROR');
			}

			if (!$cron && !$this->config['mtp_debug'])
			{
				// Processing has been run
				// Confirm this to the user and provide link back to previous page
				trigger_error($mailtopost_message . adm_back_link($this->u_action));
			}
			else if ($this->config['mtp_debug'])
			{
				// The file has been output so stop
				exit_handler();
			}
			else
			{
				return;
			}
		}
	}

	/**
	* Update the Mail to Post log table
	*
	* @return null
	* @access public
	*/
	public function mtp_log($status_message)
	{
		// Set the values required for the log
		$sql_ary = array(
			'log_status'	=> $status_message,
			'log_subject'	=> $this->mail_subject,
			'log_time'		=> time(),
			'mtp_forum'		=> $this->user_mtp_forum,
			'topic_id'		=> $this->topic_id,
			'type'			=> $this->type,
			'user_email'	=> $this->mail_address,
			'user_id'		=> $this->user_id,
		);

		// Insert the log data into the database
		$sql = 'INSERT INTO ' . $this->mailtopost_log_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
	}

	/**
	* Modify the notification data
	*
	* @return array
	* @access public
	*/
	public function modify_notifications($event)
	{
		$notification_data = $event['notification_data'];

		$notification_data['forum_name'] 	= $this->functions->get_forum_name($this->user_mtp_forum, false);
		$notification_data['poster_id'] 	= $this->user_id;
		$notification_data['topic_title']	= $this->mail_subject;
		$event['notification_data'] 		= $notification_data;

		return $event;
	}
}
