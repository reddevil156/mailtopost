<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\acp;

class acp_mailtopost_log_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name		= 'mailtopost_log';
		$this->page_title	= $phpbb_container->get('language')->lang('MAILTOPOST_LOG');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.mailtopost.log.controller');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		$admin_controller->display_output();
	}
}
