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
	$lang = [];
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
	'CRON_UNLOCKED'					=> 'The Cron task has been unlocked',

	'MAIL_TO_POST_TOOLS'			=> 'Mail to Post tools',
	'MAIL_TO_POST_TOOLS_EXPLAIN'	=> 'Here you will see how many, if any, messages are in the mailbox and either process those messages, delete them if necessary. You will also be shown if this process has locked the Cron task.',
	'MESSAGES_DELETED'				=> 'Messages deleted from mailbox',
	'MTP_CRON_LOCKED_EXPLAIN_1'		=> '<center>The Cron task running this process encountered an error at ',
	'MTP_CRON_LOCKED_EXPLAIN_2'		=> '<br>You can clear this error by clicking the button below and then delete the message.<br><br><strong>This should only be run in a test environment and NEVER on a live board.</strong></center>',
	'MTP_DELETE'					=> 'Delete first message',
	'MTP_DELETE_ALL'				=> 'Delete all messages',
	'MTP_DELETE_ALL_EXPLAIN'		=> '<center>Clicking the button below will remove <strong>ALL</strong> the messages from the mailbox. Make sure you are certain that this is what you want to do as this action is not reversable.</center>',
	'MTP_DELETE_EXPLAIN'			=> '<center>Clicking the button below will remove <strong>only the first</strong> message from the mailbox. Make sure you are certain that this is what you want to do as this action is not reversable.</center>',
	'MTP_PROCESS'					=> 'Retrieve all mail messages',
	'MTP_PROCESS_EXPLAIN'			=> '<center>Clicking the button below will process <strong>ALL</strong> the messages in the mailbox and attempt to post them to the board.',
	'MTP_UNLOCK'					=> 'Unlock the Cron process',

	'NO_PERMISSIONS_SET'			=> 'No user permissions have been set for this extension.',

	'MTP_MESSAGES'	=> array(
		0	=> 'There are %1$s messages in the mailbox',
		1	=> 'There is %1$s message in the mailbox',
		2	=> 'There are %1$s messages in the mailbox',
	),
));
