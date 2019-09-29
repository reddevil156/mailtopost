<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\acp;

class acp_mailtopost_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->page_title = $phpbb_container->get('language')->lang('MAIL_TO_POST');

		switch ($mode)
		{
			case 'manage':
				$this->tpl_name = 'mailtopost_manage';

				// Get an instance of the admin manage controller
				$admin_controller = $phpbb_container->get('david63.mailtopost.admin.manage.controller');

				// Make the $u_action url available in the admin controller
				$admin_controller->set_page_url($this->u_action);
				$admin_controller->display_options();
			break;

			case 'actions':
				$this->tpl_name = 'mailtopost_tools';

				// Get an instance of the admin actions controller
				$admin_controller = $phpbb_container->get('david63.mailtopost.admin.actions.controller');

				// Make the $u_action url available in the admin controller
				$admin_controller->set_page_url($this->u_action);
				$admin_controller->actions();
			break;
		}
	}
}
