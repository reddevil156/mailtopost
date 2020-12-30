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
	'DAYS'							=> 'Days',

	'EMAIL_REUSE'					=> 'Email reuse is allowed on this board. Be aware that this may prevent some emails from being posted by this extension.',

	'LOG_OPTIONS'					=> 'Log options',

	'HOURS'							=> 'Hours',

	'MAIL_OPTIONS'					=> 'Mail options',
	'MAIL_TO_POST_EXPLAIN'			=> 'Here you can configure the Mail to Post settings.',
	'MTP_AUTHENTICATION'			=> 'Authentication',
	'MTP_AUTHENTICATION_EXPLAIN'	=> 'SASL authentication mechanism.',
	'MTP_APOP'						=> 'APOP',
	'MTP_APOP_EXPLAIN'				=> 'Use APOP authentication.',
	'MTP_BOARD_EMAIL'				=> 'Board email',
	'MTP_BOARD_EMAIL_EXPLAIN'		=> 'The email address that posts are to be sent to.',
	'MTP_CRON_FREQUENCY'			=> 'Cron frequency',
	'MTP_CRON_FREQUENCY_EXPLAIN'	=> 'The frequency at which the Cron job will run to pull mail messages from the mail server.<br>If this is set to “0” then cron will be disabled.',
	'MTP_DATE_POST'					=> 'Post date',
	'MTP_DAYS'						=> 'Days',
	'MTP_DEBUG'						=> 'Debug mode',
	'MTP_DEBUG_EXPLAIN'				=> 'Run in debug mode.<br><strong>This should only be set when there is a problem and only in a test environment - it should NEVER be used on a live board will only be effective when being run manually.</strong>',
	'MTP_DEFAULT_FORUM'				=> 'Default forum',
	'MTP_DEFAULT_FORUM_EXPLAIN'		=> 'The default forum that will be used if a user has not selected a forum for their posts to be made to.',
	'MTP_HOSTNAME'					=> 'Mail host',
	'MTP_HOSTNAME_EXPLAIN'			=> 'The POP 3 server host name.<br>Gmail uses “pop.gmail.com”.',
	'MTP_HOURS'						=> 'Hours',
	'MTP_LOG_DAYS'					=> 'Log days',
	'MTP_LOG_DAYS_EXPLAIN'			=> 'The number of days to leave entries in the Mail to Post log.<br>Setting this to zero will disable the pruning of the Mail to Post log file.',
	'MTP_LOG_ITEMS_PAGE'			=> 'Log items per page',
	'MTP_LOG_ITEMS_PAGE_EXPLAIN'	=> 'The number of items to be displayed on each page of the Mail to Post log.',
	'MTP_MAIL_DATE'					=> 'Mail date',
	'MTP_MAIL_SPOOF'				=> 'Mail check',
	'MTP_MAIL_SPOOF_EXPLAIN'		=> 'Perform a basic check on the email address in an attempt to detect mail “spoofing”.<br>If there are “false positives” then this may need to be disabled.',
	'MTP_MINUTES'					=> 'Minutes',
	'MTP_MODERATE'					=> 'Moderate posts',
	'MTP_MODERATE_EXPLAIN'			=> 'Are the emailed messages to be moderated?',
	'MTP_NEW_TOPIC'					=> 'Post to new topic',
	'MTP_NEW_TOPIC_EXPLAIN'			=> 'Always post an email to a new topic. If this is set to “No” then posts with the same subject as an existing topic, in the same forum, will be posted as replies - if there is more than one topic in the forum with the same subject then a new topic will be created.',
	'MTP_NEXT_CRON'					=> 'Next cron run',
	'MTP_NEXT_CRON_EXPLAIN'			=> 'The date/time that the next Mail to Post cron job is expected to run.',
	'MTP_NEXT_LOG_PRUNE'			=> 'Next log prune',
	'MTP_NEXT_LOG_PRUNE_EXPLAIN'	=> 'The date/time that the Mail to Post log will be pruned.',
	'MTP_PASSWORD'					=> 'Mail password',
	'MTP_PASSWORD_EXPLAIN'			=> 'The password for the email account.',
	'MTP_PIN'						=> 'Use PIN',
	'MTP_PIN_EXPLAIN'				=> 'As a level of security the user must enter their PIN as the first six characters of the mail body.',
	'MTP_PORT'						=> 'Mail server port',
	'MTP_PORT_EXPLAIN'				=> 'The POP 3 server host port, usually 110 but some servers use other ports - Gmail uses 995.',
	'MTP_POST_DATE'					=> 'Post date',
	'MTP_POST_DATE_EXPLAIN'			=> 'Use the date/time of the email or the actual date/time when the post was made to the board.',
	'MTP_REALM'						=> 'Realm',
	'MTP_REALM_EXPLAIN'				=> 'Authentication realm or domain.',
	'MTP_REQUIRED'					=> '<strong>*** Required field ***</strong>',
	'MTP_SHOW_IP'					=> 'Show IP',
	'MTP_SHOW_IP_EXPLAIN'			=> 'Show the mail server IP address in the Mail to Post log. This may assist with troubleshooting.',
	'MTP_TLS'						=> 'TLS',
	'MTP_TLS_EXPLAIN'				=> 'Establish secure connections using TLS.<br>Gmail requires this to be set to “Yes”.',
	'MTP_USER'						=> 'Mail user',
	'MTP_USER_EXPLAIN'				=> 'The user name for the email account.',
	'MTP_USE_DEFAULT_FORUM'			=> 'Use default forum',
	'MTP_USE_DEFAULT_FORUM_EXPLAIN'	=> 'Override a user’s selected forum and post all mail messages in the default forum.',
	'MTP_WORKSTATION'		   		=> 'Workstation',
	'MTP_WORKSTATION_EXPLAIN'		=> 'Workstation for NTLM authentication.',

	'NEVER'							=> 'This is currently set not to run',
	'NO_FORUM_SET'					=> 'A default forum has not been set.',
	'NO_PERMISSIONS_SET'			=> 'No user permissions have been set for this extension.',

	'POST_OPTIONS'					=> 'Post options',

	'WARNING'						=> 'Warning'
));
