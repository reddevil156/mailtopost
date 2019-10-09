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
use phpbb\language\language;
use phpbb\extension\manager;
use phpbb\db\driver\driver_interface;

/**
* functions
*/
class functions
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string phpBB tables */
	protected $tables;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/**
	* Constructor for functions
	*
	* @param \phpbb\config\config		$config						Config object
	* @param \phpbb\auth\auth 			$auth						Auth object
	* @param \phpbb\language\language	$language					Language object
	* @param \phpbb\extension\manager 	$phpbb_extension_manager	Extension manager
	* @param \phpbb_db_driver			$db							The db connection
	* @param array						$tables						phpBB db tables
	* @param string 					phpbb_root_path				phpBB root path
	* @param string 					$php_ext					php ext
	*
	* @access public
	*/
	public function __construct(config $config, auth $auth, language $language, manager $phpbb_extension_manager, driver_interface $db, $tables, $phpbb_root_path, $php_ext)
	{
		$this->config			= $config;
		$this->auth				= $auth;
		$this->language			= $language;
		$this->ext_manager		= $phpbb_extension_manager;
		$this->db				= $db;
		$this->tables			= $tables;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $php_ext;

		$this->namespace		= __NAMESPACE__;
	}

	/**
	* Get the extension's namespace
	*
	* @return $extension_name
	* @access public
	*/
	public function get_ext_namespace($mode = 'php')
	{
		// Let's extract the extension name from the namespace
		$extension_name = substr($this->namespace, 0, -(strlen($this->namespace) - strrpos($this->namespace, '\\')));

		// Now format the extension name
		switch ($mode)
		{
			case 'php':
				$extension_name = str_replace('\\', '/', $extension_name);
			break;

			case 'twig':
				$extension_name = str_replace('\\', '_', $extension_name);
			break;
		}

		return $extension_name;
	}

	/**
	* Check if there is an updated version of the extension
	*
	* @return $new_version
	* @access public
	*/
	public function version_check()
	{
		$md_manager 	= $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());
		$versions 		= $this->ext_manager->version_check($md_manager);
		$new_version	= (array_key_exists('current', $versions) ? $versions['current'] : false);

		return $new_version;
	}

	/**
	* Get the version number of this extension
	*
	* @return $meta_data
	* @access public
	*/
	public function get_this_version()
	{
		$md_manager = $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());
		$meta_data	= $md_manager->get_metadata('version');

		return $meta_data;
	}

	/**
	* Get the forum name, and url if required
	*
	* @return $forum_name
	* @access public
	*/
	public function get_forum_name($forum_id, $url = true)
	{
		if ($forum_id == 0)
		{
			$forum_name = '';
		}
		else
		{
			$sql = 'SELECT forum_name
				FROM ' . $this->tables['forums'] . '
				where forum_id = ' . $forum_id;

			$result 	= $this->db->sql_query($sql);
			$forum_name	= $this->db->sql_fetchfield('forum_name');

			$this->db->sql_freeresult($result);

			if ($url)
			{
				$default = ($forum_id == $this->config['mtp_default_forum']) ? $this->language->lang('MTP_DEFAULT') : '';
				$forum_name = '<a href="' . $this->phpbb_root_path . 'viewforum.' . $this->phpEx . '?f=' . $forum_id . '">' . $forum_name . $default . '</a>';
			}
		}

		return $forum_name;
	}

	/**
	* Check if any permissions for this extension have been set
	*
	* @return
	* @access public
	*/
	public function get_perms_count()
	{
		//(count($this->auth->acl_get_list(false, 'u_mailtopost')[0]['u_mailtopost']) > 0) ? false : true,
		$perm_set = $this->auth->acl_get_list(false, 'u_mailtopost');
		if (empty($perm_set))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Get the text into a useable format
	*
	* @return $text
	* @access public
	*/
	public function reformat_text($text)
	{
		$unique_id = uniqid();
		$text = utf8_encode($text);
		$text = str_ireplace(PHP_EOL . PHP_EOL, $unique_id, $text);
		$text = str_ireplace(PHP_EOL, ' ', $text);
		$text = str_ireplace($unique_id, PHP_EOL . PHP_EOL, $text);

		return $text;
	}
}
