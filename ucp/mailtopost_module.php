<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\ucp;

class mailtopost_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name		= 'ucp_mailtopost_data';
		$this->page_title	= $phpbb_container->get('language')->lang('MAIL_TO_POST');

		// Get an instance of the admin controller
		$ucp_controller = $phpbb_container->get('david63.mailtopost.ucp.controller');

		$ucp_controller->mailtopost_data();
	}
}
