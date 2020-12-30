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
	'DEFAULT_MTP_PIN'		=> '<strong>This is the default - please change</strong>',

	'FORUM_SELECT'			=> 'Select forum',
	'FORUM_SELECT_EXPLAIN'	=> 'Select the forum that the user’s emails will be posted to.',

	'INVALID_PIN'			=> 'The PIN is invalid. It must contain six characters and not be the default.',

	'MTP_FORUM'				=> 'Mail to Post forum',
	'MTP_FORUM_UPDATED'		=> 'Mail to Post forum updated',
	'MTP_PIN'				=> 'User PIN',
	'MTP_PIN_EXPLAIN'		=> 'The PIN that the user will use in their mail message.',

	'NO_FORUM_SELECTED'		=> 'No forum selected',
));
