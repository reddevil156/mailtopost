<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbb\config\config;
use david63\mailtopost\controller\acp_user_controller;
use david63\mailtopost\core\mailtopost;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \david63\mailtopost\controller\acp_user_controller */
	protected $acp_user_controller;

	/** @var \david63\mailtopost\core\mailtopost */
	protected $mailtopost;

   /**
	* Constructor for listener
	*
	* @param \phpbb_config										$config 				Config object
	* @param \david63\mailtopost\controller\main_controller		$acp_user_controller	ACP User Controller
	* @param \david63\mailtopost\core\mailtopost				$mailtopost				Mailtopost class
	*
	* @access public
	*/
	public function __construct(config $config, acp_user_controller $acp_user_controller, mailtopost $mailtopost)
	{
		$this->config				= $config;
		$this->acp_user_controller	= $acp_user_controller;
		$this->mailtopost			= $mailtopost;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_users_mode_add'				=> 'mtp_acp_users',
			'core.cron_run_before'					=> 'get_last_cron_task',
			'core.modify_submit_notification_data'	=> 'modify_notification',
			'core.permissions' 						=> 'add_permissions',
		);
	}

	/**
	* Process the ACP user data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function mtp_acp_users($event)
	{
		if ($event['mode'] == 'mtpforum')
		{
			$this->acp_user_controller->acp_users($event);
		}
	}

	/**
	* Logs the name of the last executed Cron task
	*
	* @param object $event The event object
	*/
	public function get_last_cron_task($event)
	{
		$task = $event['task'];
		$this->config->set('mtp_last_cron_task', $task->get_name(), true);
	}

	/**
	* Send the notification data to mailtopost.php and modify the data
	*
	* @param object $event The event object
	* @return array
	* @access public
	*/
	public function modify_notification($event)
	{
		// We only want to run this for a Mail to Post topic
		if ($this->config['mtp_lock'])
		{
			$events	= $this->mailtopost->modify_notifications($event);
			$event	= $events;
		}
	}

	/**
	* Add the new permissions
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_permissions($event)
	{
		$permissions					= $event['permissions'];
		$permissions['u_mailtopost']	= array('lang' => 'ACL_U_MAILTOPOST', 'cat' => 'post');
		$event['permissions']			= $permissions;
	}
}
