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
	'BOARD_EMAIL'				=> 'Board email',

	'DEFAULT_FORUM'				=> 'Forum',

	'FORUM_SELECT'				=> 'Select forum',
	'FORUM_SELECT_EXPLAIN'		=> 'Select the forum that your emails will be posted to.',

	'MAILTOTOPIC_EXPLAIN'		=> 'You have the ability to send an email to the “Board email” address below (this is the only email address that will work) and the content of that email will be posted as a topic on the board. When sending an email you must use the email address that is linked to your account on this board - <strong>no other email address will work.<br>Do not use the board email for any other purpose.</strong><br><br>You cannot include images or attachments in your mail message - any such messages will be discarded.',
	'MAILTOTOPIC_EXPLAIN2'		=> '<br><br>You also need to select from the list below the forum into which you want the post to be made. If your email subject matches that of another topic (which may be one that you have posted) then your email will appear as a reply in the topic.<br>',
	'MAILTOTOPIC_FORUM_EXPLAIN'	=> '<br><br>Your mailed posts will appear in the forum shown below.',

	'NO_FORUM_SELECTED'			=> 'No forum selected',

	'PLEASE_READ'				=> '<strong>Please read</strong>',

	'UCP_MAILTOTOPIC_EXPLAIN'	=> 'Details of how you can send an email to be posted on this board.',
	'UCP_MAILTOTOPIC_TITLE'		=> 'Email to Post',

	'YOUR_EMAIL'				=> 'Your email',
));
