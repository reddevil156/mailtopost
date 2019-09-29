<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\acp;

class acp_mailtopost_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\mailtopost\acp\acp_mailtopost_module',
			'title'		=> 'MAIL_TO_POST',
			'modes'		=> array(
				'manage'	=> array('title' => 'MAIL_TO_POST_MANAGE', 'auth' => 'ext_david63/mailtopost && acl_a_board', 'cat' => array('MAIL_TO_POST')),
				'actions'	=> array('title' => 'MAIL_TO_POST_ACTIONS', 'auth' => 'ext_david63/mailtopost && acl_a_board', 'cat' => array('MAIL_TO_POST')),
			),
		);
	}
}
