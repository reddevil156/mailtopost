<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\controller;

/**
* @ignore
*/
use phpbb\config\config;
use phpbb\request\request;
use phpbb\user;
use phpbb\cache\service;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\db\driver\driver_interface;
use david63\mailtopost\core\functions;
use david63\mailtopost\pop3mail\pop3;
use david63\mailtopost\core\mailtopost;

/**
* Admin controller
*/
class admin_actions_controller implements admin_actions_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	* The database table the mailtopost log is stored in
	*
	* @var string
	*/
	protected $mailtopost_table;

	/** @var \david63\mailtopost\core\functions */
	protected $functions;

	/** @var \david63\mailtopost\pop3mail\pop3 */
	protected $pop3;

	/** @var \david63\mailtopost\core\mailtopost */
	protected $mailtopost;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin process controller
	*
	* @param \phpbb_config							$config 				Config object
	* @param \phpbb\request\request					$request				Request object
	* @param \phpbb\user							$user					User object
	* @param \phpbb\cache\service					$cache					Cache object
	* @param \phpbb\template\template				$template				Template object
	* @param \phpbb\language\language				$language				Language object
	* @param \phpbb_db_driver						$db						The db connection
	* @param string									$smailtopost_log_table  Name of the table used to store mailtopost log data
	* @param \david63\mailtopost\core\functions		$functions				Functions for the extension
	* @param \david63\mailtopost\pop3mail\pop3		$pop3					Mail pop3 class
	* @param \david63\mailtopost\core\mailtopost	$mailtopost				Mail to Post process class
	*
	* @return \david63\mailtopost\controller\admin_process_controller
	* @access public
	*/
	public function __construct(config $config, request $request, user $user, service $cache, template $template, language $language, driver_interface $db, $smailtopost_log_table, functions $functions, pop3 $pop3, mailtopost $mailtopost)
	{
		$this->config				= $config;
		$this->request				= $request;
		$this->user					= $user;
		$this->cache				= $cache;
		$this->template				= $template;
		$this->language				= $language;
		$this->db					= $db;
		$this->mailtopost_log_table	= $smailtopost_log_table;
		$this->functions			= $functions;
		$this->pop3					= $pop3;
		$this->mailtopost			= $mailtopost;
	}

	/**
	* Run the Mail to Post process from the ACP
	*
	* @return null
	* @access public
	*/
	public function actions()
	{
		// Add the language files
		$this->language->add_lang('acp_tools_mailtopost', $this->functions->get_ext_namespace());
		$this->language->add_lang('acp_mailtopost_log', $this->functions->get_ext_namespace());

		// Make sure debug is turned off
		$this->pop3->debug = $this->pop3->html_debug = 0;

		$mailtopost_message = '';
		$messages = 0;

		// Is the Cron unlock form being submitted?
		if ($this->request->is_set_post('cron_unlock'))
		{
			$this->config->set('cron_lock', 0);
			$this->cache->purge();

			// Now log the action
			$this->mtp_log('MTP_CRON_UNLOCKED');

			// Processing has been run
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('CRON_UNLOCKED') . adm_back_link($this->u_action));
		}

		// Is the delete message form being submitted?
		if ($this->request->is_set_post('actions'))
		{
			// We can now delete the messages
			$this->pop3->Open();
			$this->pop3->Login($this->config['mtp_user'], $this->config['mtp_password'], $this->config['mtp_apop']);
			$this->pop3->Statistics($messages, $size);

			for ($message = 1; $message <= $messages; $message++)
			{
				$this->pop3->DeleteMessage($message);
			}

			$this->pop3->Close();
			$this->pop3->CloseConnection();

			// Now log the action
			$this->mtp_log('MAILBOX_EMPTIED');

			// Processing has been run
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('MESSAGES_DELETED') . adm_back_link($this->u_action));
		}

		// Do the cron stuff
		$cron_locked 	= false;
		$cron_time		= '';
		if ($this->config['cron_lock'] && $this->config['mtp_last_cron_task'] == 'cron.task.mailtopost_process')
		{
			$cron_locked = true;

			$time 		= explode(' ', $this->config['cron_lock']);
			$cron_time	= $this->user->format_date((int) $time[0]);
		}

		// Get number of messages in the mailbox
		if ($error = $this->pop3->Open() == '')
		{
			if ($error = $this->pop3->Login($this->config['mtp_user'], $this->config['mtp_password'], $this->config['mtp_apop']) == '')
			{
				if ($error = $this->pop3->Statistics($messages, $size) == '')
				{
					$mailtopost_message = $this->language->lang('MTP_MESSAGES', (int) $messages);
				}
			}
			else
			{
				$mailtopost_message = $this->language->lang('LOGIN_ERROR');
			}
		}
		else
		{
			$mailtopost_message = $this->language->lang('MAILBOX_ERROR');
		}

		$this->pop3->Close();
		$this->pop3->CloseConnection();

		// Template vars for header panel
		$this->template->assign_vars(array(
			'HEAD_TITLE'		=> $this->language->lang('MAIL_TO_POST_TOOLS'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('MAIL_TO_POST_TOOLS_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_PERM_SET'		=> $this->functions->get_perms_count(),
			'S_VERSION_CHECK'	=> $this->functions->version_check(),

			'VERSION_NUMBER'	=> $this->functions->get_this_version(),
		));

		$this->template->assign_vars(array(
			'CRON_TIME'		=> $cron_time,

			'MESSAGE_COUNT'	=> $mailtopost_message,

			'S_CRON_LOCKED'	=> $cron_locked,
			'S_MESSAGES'	=> ($messages > 0) ? true : false,

			'U_ACTION'		=> $this->u_action,
		));

		// Are there any messages to process manually?
		if ($messages > 0)
		{
			$this->mailtopost->process(false);
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
			'log_subject'	=> '',
			'log_time'		=> time(),
			'mtp_forum'		=> 0,
			'topic_id'		=> 0,
			'type'			=> 'M',
			'user_email'	=> '',
			'user_id'		=> $this->user->data['user_id'],
		);

		// Insert the log data into the database
		$sql = 'INSERT INTO ' . $this->mailtopost_log_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		return $this->u_action = $u_action;
	}
}
