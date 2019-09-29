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
use david63\mailtopost\core\mailtopost;

class mailtopost_process extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \david63\mailtopost\core\mailtopost */
	protected $mailtopost;

	/**
	* Constructor.
	*
	* @param \phpbb_config							$config 		Config object
	* @param \david63\mailtopost\core\mailtopost	$mailtopost		Mail to Post process class
	*/
	public function __construct(config $config, mailtopost $mailtopost)
	{
		$this->config		= $config;
		$this->mailtopost	= $mailtopost;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->mailtopost->process(true);
		$this->config->set('mtp_last_process', time(), true);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['mtp_process_frequency'] > 0;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return time() > ($this->config['mtp_last_process'] + (($this->config['mtp_process_frequency'] * $this->config['mtp_interval_type']) * 60));
	}
}
