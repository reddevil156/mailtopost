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
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\pagination;
use phpbb\user;
use phpbb\language\language;
use david63\mailtopost\core\functions;

/**
* Admin controller
*/
class log_controller implements log_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \david63\mailtopost\core\functions */
	protected $functions;

	/** @var string phpBB tables */
	protected $tables;

	/** @var string custom constants */
	protected $mailtopost_constants;

	/**
	* The database table the mailtopost log is stored in
	*
	* @var string
	*/
	protected $mailtopost_table;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for log controller
	*
	* @param \phpbb\config\config					$config					Config object
	* @param \phpbb\db\driver\driver_interface		$db						The db connection
	* @param \phpbb\request\request					$request				Request object
	* @param \phpbb\template\template				$template				Template object
	* @param \phpbb\pagination						$pagination				Pagination object
	* @param \phpbb\user							$user					User object
	* @param \phpbb\language\language				$language				Language object
	* @param \david63\mailtopost\core\functions		$functions				Functions for the extension
	* @param string 								$phpbb_root_path		phpBB root path
	* @param string 								$php_ext				php ext
	* @param array									$tables					phpBB db tables
	* @param array	                            	$mailtopost_constants	Custom constants
	*
	* @return \david63\mailtopost\controller\log_controller
	* @access public
	*/
	public function __construct(config $config, driver_interface $db, request $request, template $template, pagination $pagination, user $user, language $language, functions $functions, $phpbb_root_path, $php_ext, $tables, $mailtopost_constants)
	{
		$this->config			= $config;
		$this->db  				= $db;
		$this->request			= $request;
		$this->template			= $template;
		$this->pagination		= $pagination;
		$this->user				= $user;
		$this->language			= $language;
		$this->functions		= $functions;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $php_ext;
		$this->tables			= $tables;
		$this->constants		= $mailtopost_constants;
	}

	/**
	* Display the output for this extension
	*
	* @return null
	* @access public
	*/
	public function display_output()
	{
		// Add the language files
		$this->language->add_lang(array('acp_log_mailtopost', 'acp_mailtopost_log'), $this->functions->get_ext_namespace());

		// Start initial var setup
		$action		= $this->request->variable('action', '');
		$deletemark = $this->request->variable('delmarked', false, false, \phpbb\request\request_interface::POST);
		$marked		= $this->request->variable('mark', array(0));
		$start		= $this->request->variable('start', 0);

		$back = false;

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_dir	= $this->request->variable('sd', 'd');
		$sort_key	= $this->request->variable('sk', 't');

		// Sorting
		$limit_days = array(0 => $this->language->lang('ALL_ENTRIES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR'));
		$sort_by_text = array('u' => $this->language->lang('SORT_USERNAME'), 't' => $this->language->lang('SORT_DATE'), 'e' => $this->language->lang('SORT_EMAIL'));
		$sort_by_sql = array('u' => 'u.username_clean', 't' => 'l.log_time', 'e' => 'u.user_email');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where	= ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort	= $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$log_count = 0;

		// Get total log count for pagination
		$sql = 'SELECT COUNT(log_id) AS total_logs
			FROM ' . $this->tables['mailtopost_log'] . '
				WHERE log_time >= ' . (int) $sql_where;
		$result		= $this->db->sql_query($sql);

		$log_count	= (int) $this->db->sql_fetchfield('total_logs');

		$this->db->sql_freeresult($result);

		$action		= $this->u_action . "&amp;$u_sort_param";
		$start		= $this->pagination->validate_start($start, $this->config['mtp_log_items_page'], $log_count);
		$this->pagination->generate_template_pagination($action, 'pagination', 'start', $log_count, $this->config['mtp_log_items_page'], $start);

		$sql = 'SELECT l.*, u.username, u.username_clean, u.user_colour
			FROM ' . $this->tables['mailtopost_log'] . ' l, ' . $this->tables['users'] . ' u
			WHERE u.user_id = l.user_id
			AND l.log_time >= ' . (int) $sql_where . "
			ORDER BY $sql_sort";
		$result = $this->db->sql_query_limit($sql, $this->config['mtp_log_items_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('log', array(
				'USER_EMAIL'		=> $row['user_email'],
				'DATE'				=> $this->user->format_date($row['log_time']),
				'FORUM'				=> $this->functions->get_forum_name($row['mtp_forum']),
				'MAIL_SERVER_IP'	=> $row['mail_ip'],
				'STATUS'			=> $this->language->lang_raw($row['log_status']),
				'SUBJECT'	   		=> ($row['topic_id'] == 0) ? $row['log_subject'] : '<a href="' . $this->phpbb_root_path . 'viewtopic.' . $this->phpEx . '?f=' . $row['mtp_forum'] .'&amp;t=' . $row['topic_id'] . '">' . $row['log_subject'] . '</a>',
				'TYPE'				=> ($row['type'] == $this->constants['type_cron']) ? $this->language->lang('CRON') : $this->language->lang('MANUAL'),
				'USERNAME'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'USER_EMAIL'		=> $row['user_email'],
			));
		}

		$this->db->sql_freeresult($result);

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'HEAD_TITLE'		=> $this->language->lang('MTP_LOG'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('MTP_LOG_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));

		$this->template->assign_vars(array(
			'S_LIMIT_DAYS'	=> $s_limit_days,
			'S_SHOW_IP'		=> $this->config['mtp_show_ip'],
			'S_SORT_DIR'	=> $s_sort_dir,
			'S_SORT_KEY'	=> $s_sort_key,

			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param&amp;start=$start",
		));
	}

	/**
	* Get a language variable from a language variable array
	*
	* @return $data
	* @access protected
	*/
	public function get_lang_var($lang_array, $lang_key)
	{
		foreach ($this->language->lang_raw($lang_array) as $key => $data)
		{
			if ($key == $lang_key)
			{
				return $data;
			}
		}
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
		$this->u_action = $u_action;
	}
}
