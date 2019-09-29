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
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use david63\mailtopost\core\functions;

/**
* Admin controller
*/
class admin_manage_controller implements admin_manage_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \david63\mailtopost\core\functions */
	protected $functions;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin manage controller
	*
	* @param \phpbb\config\config					$config			Config object
	* @param \phpbb\request\request					$request		Request object
	* @param \phpbb\template\template				$template		Template object
	* @param \phpbb\user							$user			User object
	* @param \phpbb\language\language				$language		Language object
	* @param \phpbb\log\log							$log			Log object
	* @param \david63\mailtopost\core\functions		$functions		Functions for the extension
	*
	* @return \david63\mailtopost\controller\admin_manage_controller
	* @access public
	*/
	public function __construct(config $config, request $request, template $template, user $user, language $language, log $log, functions $functions)
	{
		$this->config		= $config;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->language		= $language;
		$this->log			= $log;
		$this->functions	= $functions;
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options()
	{
		// Add the language files
		$this->language->add_lang('acp_manage_mailtopost', $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'mailtopost_manage';
		add_form_key($form_key);

		$back = false;

		// Is the form being submitted?
		if ($this->request->is_set_post('submit'))
		{
			// Is the submitted form is valid?
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Has a default forum been set?
			if ($this->request->variable('mtp_default_forum', 0) == 0)
			{
				trigger_error($this->language->lang('NO_FORUM_SET') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, process the form data
			// Set the options the user configured
			$this->set_manage_options();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'MAILTOPOST_MANAGE_LOG');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Template vars for header panel
		$this->template->assign_vars(array(
			'HEAD_TITLE'		=> $this->language->lang('MAIL_TO_POST'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('MAIL_TO_POST_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> $this->functions->version_check(),

			'VERSION_NUMBER'	=> $this->functions->get_this_version(),
		));

		$this->template->assign_vars(array(
			'EMAIL_REUSE'			=> $this->config['allow_emailreuse'],

			'MTP_APOP'				=> isset($this->config['mtp_apop']) ? $this->config['mtp_apop'] : '',
			'MTP_AUTHENTICATION'	=> isset($this->config['mtp_authentication']) ? $this->config['mtp_authentication'] : '',
			'MTP_BOARD_EMAIL'		=> isset($this->config['mtp_board_email']) ? $this->config['mtp_board_email'] : '',
			'MTP_CRON_FREQUENCY'	=> isset($this->config['mtp_process_frequency']) ? $this->config['mtp_process_frequency'] : '',
			'MTP_DEBUG'				=> isset($this->config['mtp_debug']) ? $this->config['mtp_debug'] : '',
			'MTP_DEFAULT_FORUM'		=> make_forum_select($this->config['mtp_default_forum']),
			'MTP_HOSTNAME'			=> isset($this->config['mtp_hostname']) ? $this->config['mtp_hostname'] : '',
			'MTP_INTERVAL_TYPE'		=> $this->get_mtp_interval_type(),
			'MTP_LOG_DAYS'			=> isset($this->config['mtp_log_days']) ? $this->config['mtp_log_days'] : '',
			'MTP_LOG_ITEMS_PAGE'	=> isset($this->config['mtp_log_items_page']) ? $this->config['mtp_log_items_page'] : '',
			'MTP_MODERATE'			=> isset($this->config['mtp_moderate']) ? $this->config['mtp_moderate'] : '',
			'MTP_NEW_TOPIC'			=> isset($this->config['mtp_new_topic']) ? $this->config['mtp_new_topic'] : '',
			'MTP_PASSWORD'			=> isset($this->config['mtp_password']) ? $this->config['mtp_password'] : '',
			'MTP_PORT'				=> isset($this->config['mtp_port']) ? $this->config['mtp_port'] : '',
			'MTP_POST_DATE'			=> isset($this->config['mtp_post_date']) ? $this->config['mtp_post_date'] : '',
			'MTP_REALM'				=> isset($this->config['mtp_realm']) ? $this->config['mtp_realm'] : '',
			'MTP_TLS'				=> isset($this->config['mtp_tls']) ? $this->config['mtp_tls'] : '',
			'MTP_USER' 				=> isset($this->config['mtp_user']) ? $this->config['mtp_user'] : '',
			'MTP_USE_DEFAULT_FORUM'	=> isset($this->config['mtp_use_default_forum']) ? $this->config['mtp_use_default_forum'] : '',
			'MTP_WORKSTATION'		=> isset($this->config['mtp_workstation']) ? $this->config['mtp_workstation'] : '',

			'NEXT_CRON_PROCESS'		=> ($this->config['mtp_process_frequency'] > 0) ? $this->user->format_date(($this->config['mtp_last_process'] + (($this->config['mtp_process_frequency'] * $this->config['mtp_interval_type'])) * 60)) : $this->language->lang('NEVER'),
			'NEXT_LOG_PRUNE'		=> ($this->config['mtp_log_days'] > 0) ? $this->user->format_date($this->config['mtp_log_prune_last_gc'] + 86400) : $this->language->lang('NEVER'),

			'U_ACTION'				=> $this->u_action,
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_manage_options()
	{
		$this->config->set('mtp_apop', $this->request->variable('mtp_apop', 0));
		$this->config->set('mtp_authentication', $this->request->variable('mtp_authentication', 'USER'));
		$this->config->set('mtp_board_email', $this->request->variable('mtp_board_email', ''));
		$this->config->set('mtp_debug', $this->request->variable('mtp_debug', 0));
		$this->config->set('mtp_default_forum', $this->request->variable('mtp_default_forum', 0));
		$this->config->set('mtp_hostname', $this->request->variable('mtp_hostname', ''));
		$this->config->set('mtp_interval_type', $this->request->variable('mtp_interval_type', 1));
		$this->config->set('mtp_log_days', $this->request->variable('mtp_log_days', 30));
		$this->config->set('mtp_log_items_page', $this->request->variable('mtp_log_items_page', 25));
		$this->config->set('mtp_moderate', $this->request->variable('mtp_moderate', 0));
		$this->config->set('mtp_new_topic', $this->request->variable('mtp_new_topic', 0));
		$this->config->set('mtp_password', $this->request->variable('mtp_password', ''));
		$this->config->set('mtp_port', $this->request->variable('mtp_port', 110));
		$this->config->set('mtp_post_date', $this->request->variable('mtp_post_date', 0));
		$this->config->set('mtp_process_frequency', $this->request->variable('mtp_process_frequency', 0));
		$this->config->set('mtp_realm', $this->request->variable('mtp_realm', ''));
		$this->config->set('mtp_tls', $this->request->variable('mtp_tls', 0));
		$this->config->set('mtp_user', $this->request->variable('mtp_user', ''));
		$this->config->set('mtp_use_default_forum', $this->request->variable('mtp_use_default_forum', 0));
		$this->config->set('mtp_workstation', $this->request->variable('mtp_workstation', ''));
	}

	/**
	* TMail to Post cron interval select
	*
	* @return select
	* @access protected
	*/
	protected function get_mtp_interval_type()
	{
		$s_mtp_type = '';

		$types = array(
			1 		=> $this->language->lang('MTP_MINUTES'),
			60		=> $this->language->lang('MTP_HOURS'),
			1440	=> $this->language->lang('MTP_DAYS'),
		);

		foreach ($types as $type => $lang)
		{
			$selected	= ($this->config['mtp_interval_type'] == $type) ? ' selected="selected"' : '';
			$s_mtp_type .= '<option value="' . $type . '"' . $selected . '>' . $this->language->lang($lang);
			$s_mtp_type .= '</option>';
		}

		return '<select name="mtp_interval_type" id="mtp_interval_type">' . $s_mtp_type . '</select>';
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
