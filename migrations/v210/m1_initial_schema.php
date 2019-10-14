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

class m1_initial_schema extends migration
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
		return array('\phpbb\db\migration\data\v320\v320');
	}

	/**
	* Add the table schemas to the database:
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'mailtopost_log'	=> array(
					'COLUMNS'	=> array(
						'log_id'			=> array('INT:10', null, 'auto_increment'),
						'user_id'			=> array('INT:10', 0),
						'mail_ip'			=> array('VCHAR:40', ''),
						'user_email'		=> array('VCHAR:100', ''),
						'mtp_forum'			=> array('UINT', 0),
						'log_subject'		=> array('VCHAR:255', ''),
						'topic_id'			=> array('INT:10', 0),
						'log_time'			=> array('INT:11', 0),
						'log_status'		=> array('VCHAR:25', ''),
						'type'				=> array('CHAR:1', ''),
					),
					'PRIMARY_KEY' => 'log_id',
				),
			),

			'add_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_mtp_forum'	=> array('UINT', 0),
					'user_mtp_pin'		=> array('CHAR:6', 'Abc123'),
				),
			),
		);
	}

	/**
	* Drop the schemas from the database
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'mailtopost_log',
			),

			'drop_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_mtp_forum',
					'user_mtp_pin',
				),
			),
		);
	}
}
