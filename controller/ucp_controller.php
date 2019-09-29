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
use phpbb\user;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\language\language;
use phpbb\template\template;
use david63\mailtopost\core\functions;

/**
* UCP controller
*/
class ucp_controller implements ucp_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \david63\mailtopost\core\functions */
	protected $functions;

	/** @var string phpBB tables */
	protected $tables;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for ucp controller
	*
	* @param \phpbb\config\config					$config				Config object
	* @param \phpbb\user							$user				User object
	* @param \phpbb_db_driver						$db					The db connection
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\language\language				$language			Language object
	* @param \phpbb\template\template          		$template			Template object
	* @param \david63\mailtopost\core\functions		$functions			Functions for the extension
	* @param string 								$phpbb_root_path	pjpBB root path
	* @param string 								$php_ext			php ext
	*
	* @return \david63\mailtopost\controller\ucp_controller
	* @access public
	*/
	public function __construct(config $config, user $user, driver_interface $db, request $request, language $language, template $template, functions $functions, $tables, $phpbb_root_path, $php_ext)
	{
		$this->config			= $config;
		$this->user				= $user;
		$this->db				= $db;
		$this->request			= $request;
		$this->language			= $language;
		$this->template			= $template;
		$this->functions		= $functions;
		$this->tables			= $tables;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $php_ext;
	}

	/**
	* Process the forum select data
	*
	* @return null
	* @access public
	*/
	public function mailtopost_data()
	{
		// Add the language files
		$this->language->add_lang('ucp_mailtopost', $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'mailtopost_data';
		add_form_key($form_key);

		// Is the form being submitted?
		if ($this->request->is_set_post('submit'))
		{
			// Is the form valid?
			if (!check_form_key($form_key))
			{
				$msg = $this->user->lang('FORM_INVALID');
			}
			else
			{
				$user_forum = $this->request->variable('user_mtp_forum', 0);

				// Has the user selected a forum?
				if ($user_forum == 0)
				{
					$msg = $this->user->lang('NO_FORUM_SELECTED');
				}
				else
				{
					// If no errors, process the form data
					$sql = 'UPDATE ' . $this->tables['users'] . '
						SET user_mtp_forum = ' . $user_forum . '
						WHERE user_id = ' . $this->user->data['user_id'];

					$this->db->sql_query($sql);

					$msg = $this->language->lang('PREFERENCES_UPDATED');
				}
			}

			$message = $msg . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
			trigger_error($message);
		}

		// Get the user's email
		$sql = 'SELECT user_email
			FROM ' . $this->tables['users'] . '
			WHERE user_id = ' . $this->user->data['user_id'];

		$result 	= $this->db->sql_query($sql);
		$user_email	= $this->db->sql_fetchfield('user_email');

		$this->db->sql_freeresult($result);

		if (!function_exists('make_forum_select'))
		{
			include($this->phpbb_root_path . 'includes/functions_admin.' . $this->phpEx);
		}

		$this->template->assign_vars(array(
			'BOARD_EMAIL'			=> $this->config['mtp_user'],

			'DEFAULT_FORUM'			=> $this->functions->get_forum_name($this->config['mtp_default_forum'], false),

			'FORUM_SELECT'			=> make_forum_select($this->user->data['user_mtp_forum']),

			'MTP_NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),
			'MTP_VERSION_NUMBER'	=> $this->functions->get_this_version(),

			'USER_EMAIL'			=> $user_email,

			'S_UCP_ACTION'			=> $this->u_action,
			'S_USE_DEFAULT_FORUM'	=> $this->config['mtp_use_default_forum'],
		));
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
