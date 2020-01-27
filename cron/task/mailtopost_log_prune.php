<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\cron\task;

/**
* @ignore
*/
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\log\log;
use phpbb\user;

class mailtopost_log_prune extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB tables */
	protected $tables;

	/**
	* Constructor.
	*
	* @param \phpbb_config		$config 	Config object
	* @param \phpbb_db_driver	$db 		The db connection
	* @param \phpbb\log\log		$log		Log object
	* @param \phpbb\user		$user		User object
	* @param array				$tables		phpBB db tables
	*/
	public function __construct(config $config, driver_interface $db, log $log, user $user, $tables)
	{
		$this->config	= $config;
		$this->db		= $db;
		$this->log		= $log;
		$this->user		= $user;
		$this->tables	= $tables;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$last_log = time() - ($this->config['mtp_log_days'] * 86400);

		$sql = 'DELETE FROM ' . $this->tables['mailtopost_log'] . '
			WHERE log_time < ' . (int) $last_log;
		$this->db->sql_query($sql);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'MAILTOPOST_LOG_PRUNE_LOG');

		$this->config->set('mtp_log_prune_last_gc', time(), true);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['mtp_log_days'] > 0;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return time() > ($this->config['mtp_log_prune_last_gc'] + 86400); // Run every 24 hours
	}
}
