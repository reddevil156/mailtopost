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
use phpbb\request\request;
use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\language\language;
use david63\mailtopost\core\functions;

/**
* Event listener
*/
class acp_user_controller implements acp_user_interface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \david63\mailtopost\core\functions */
	protected $functions;

	/** @var string phpBB tables */
	protected $tables;

	/**
	* Constructor
	*
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\db\driver\driver_interface		$db					The db connection
	* @param string 								$phpbb_root_path	phpBB root path
	* @param string 								$php_ext			php ext
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\language\language				$language			Language object
	* @param \david63\mailtopost\core\functions		$functions			Functions for the extension
	* @param array									$tables				phpBB db tables
	*
	* @return \david63\mailtopost\controller\acp_user_controller
	* @access public
	*/
	public function __construct(request $request, driver_interface $db, $phpbb_root_path, $php_ext, template $template, language $language, functions $functions, $tables)
	{
		$this->request				= $request;
		$this->db					= $db;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->phpEx				= $php_ext;
		$this->template				= $template;
		$this->language				= $language;
		$this->functions			= $functions;
		$this->tables				= $tables;
	}

	/**
	* Update a user's Mail to Post forum
	*
	* @return	void
	*/
	public function acp_users($event)
	{
		$user_row = $event['user_row'];

		// Add the language file
		$this->language->add_lang('acp_users_mailtopost', $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'mailtopost_data';
		add_form_key($form_key);

		$action = append_sid("{$this->phpbb_root_path}adm/index.$this->phpEx" . '?i=acp_users&amp;mode=mtpforum&amp;u=' . $event['user_id']);

		if ($this->request->is_set_post('update'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($action), E_USER_WARNING);
			}
			else
			{
				$user_forum = $this->request->variable('user_mtp_forum', 0);

				// Has the user selected a forum?
				if ($user_forum == 0)
				{
					trigger_error($this->language->lang('NO_FORUM_SELECTED') . adm_back_link($action), E_USER_WARNING);
				}
				else
				{
					// If no errors, process the form data
					$sql = 'UPDATE ' . $this->tables['users'] . '
						SET user_mtp_forum = ' . $user_forum . '
						WHERE user_id = ' . $event['user_id'];

					$this->db->sql_query($sql);
				}
			}

			// Notify user
			trigger_error($this->language->lang('MTP_FORUM_UPDATED') . adm_back_link($action));
		}

		$this->template->assign_vars(array(
			'S_MTP'			=> true,
			'FORUM_SELECT'	=> make_forum_select($user_row['user_mtp_forum']),
			'U_ACTION'		=> $action,
		));
	}
}
