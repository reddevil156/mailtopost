<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\migrations\v210;

use phpbb\db\migration\migration;

class m5_ucp_modules extends migration
{
	/**
	* Assign migration file dependencies for this migration
	*
	* @return array Array of migration files
	* @static
	* @access public
	*/
	static public function depends_on()
	{
		return array('\david63\mailtopost\migrations\v210\m4_acp_modules');
	}

	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'ucp'
				AND module_langname = 'UCP_MAIL_TO_POST'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			array('module.add', array('ucp', 0, 'UCP_MAIL_TO_POST')),

			array('module.add', array(
				'ucp', 'UCP_MAIL_TO_POST', array(
					'module_basename'	=> '\david63\mailtopost\ucp\mailtopost_module',
					'modes'				=> array('main'),
				),
			)),
		);
	}
}
