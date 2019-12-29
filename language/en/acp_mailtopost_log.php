<?php
/**
*
* @package Mail to Post Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

/// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ATTACHMENT_ERROR'		=> 'Submitted message contains an attachment',

	'BLANK_MESSAGE'			=> 'The message was blank',

	'DECODE_ERROR'			=> 'Mail message decode error',
	'DECODE_PROBLEM'		=> 'A problem was encountered whilst decoding the message',
	'DEFAULT_PIN'			=> '<strong>User has not changed default PIN</strong>',

	'IMAGE_ERROR'			=> 'Submitted message contains an image',
	'INVALID_PIN'			=> '<strong>Invalid PIN entered</strong>',

	'LOGIN_ERROR'			=> 'Unable to log in to the mail server',

	'MAILBOX_EMPTIED'		=> 'Mail messages deleted',
	'MAILBOX_ERROR'			=> 'Unable to access the mailbox',
	'MTP_CRON_UNLOCKED'		=> 'Cron unlocked',
	'MULTIPLE_EMAIL'		=> 'Multiple email addresses found',

	'NO_DATA'				=> 'No messages to process',
	'NO_EMAIL'				=> '<strong>User’s email is invalid<strong>',
	'NO_EMAIL_SIGNATURE'	=> '<strong>The email does not have a dkim signature</strong>',
	'NO_MESSAGES'			=> 'No messages',
	'NO_PERMISSION'			=> '<strong>User does not have permission<strong>',
	'NO_PERM_SET'			=> 'No permissions set',

	'SPOOF_EMAIL'			=> '<strong>Suspicious email address<strong>',
	'SUCCESS'				=> 'The mail has been successfully posted',
));
