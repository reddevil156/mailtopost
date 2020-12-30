<?php
/**
*
* @package Log Searches Extension
* @copyright (c) 2015 david63
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
	'CRON'					=> 'Cron',

	'DISPLAY_ENTRIES'		=> 'Show entries for',

	'GO'					=> 'Go',

	'MAIL_SERVER_IP'		=> 'Mail IP',
	'MANUAL'				=> 'Manual',
	'MTP_DEFAULT'			=> '&nbsp;(D)',
	'MTP_LOG'		   		=> 'Mail to Post log',
	'MTP_LOG_EXPLAIN'		=> 'This lists the actions carried out by Mail to Post.',

	'NO_ENTRIES'			=> 'There are no log entries to display.',
	'NO_PERMISSIONS_SET'	=> 'No user permissions have been set for this extension.',

	'POST_FORUM'			=> 'Forum',
	'POST_SUBJECT'			=> 'Post subject',

	'SORT_BY'		   		=> 'Sort by',
	'SORT_DATE'				=> 'Date',
	'SORT_EMAIL'			=> 'Email',
	'SORT_USERNAME'			=> 'Username',
	'STATUS'				=> 'Status',

	'TIME'					=> 'Date/Time',
	'TYPE'					=> 'Run type',

	'USERNAME'				=> 'Username',
	'USER_EMAIL'			=> 'User’s email',

	'VERSION'				=> 'Version',
));
