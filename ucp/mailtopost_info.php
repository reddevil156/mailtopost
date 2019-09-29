<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\mailtopost\ucp;

class mailtopost_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\mailtopost\ucp\mailtopost_module',
			'title'		=> 'UCP_MAIL_TO_POST',
			'modes'		=> array(
				'main'	=> array('title' => 'MAIL_TO_POST', 'auth' => 'ext_david63/mailtopost && acl_u_mailtopost', 'cat' => array('UCP_MAIL_TO_POST')),
			),
		);
	}
}
