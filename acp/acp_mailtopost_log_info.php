<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\acp;

class acp_mailtopost_log_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\mailtopost\acp\acp_mailtopost_log_module',
			'title'		=> 'MAILTOPOST_LOG',
			'modes'		=> array(
				'main'	=> array('title' => 'MAILTOPOST_LOG', 'auth' => 'ext_david63/mailtopost && acl_a_board', 'cat' => array('ACP_FORUM_LOGS')),
			),
		);
	}
}
