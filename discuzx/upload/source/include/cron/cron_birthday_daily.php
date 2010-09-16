<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_birthday_daily.php 9485 2010-04-29 02:34:05Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['setting']['maxbdays']) {
	require_once libfile('function/cache');
	updatecache('birthdays');
	updatecache('birthdays_index');
}

include_once libfile('function/mail');
if($_G['setting']['bdaystatus']) {
	$today = dgmdate(TIMESTAMP, 'm-d', $_G['setting']['timeoffset']);
	$query = DB::query("SELECT uid, username, email, bday FROM ".DB::table('common_member')." WHERE RIGHT(bday, 5)='$today' ORDER BY bday");
	global $member;
	while($member = DB::fetch($query)) {
		$birthday_message = lang('email', 'birthday_message', array(
			'username' => $member['username'],
			'bbname' => $_G['setting']['bbname'],
			'siteurl' => $_G['siteurl'],
		));
		sendmail("$member[username] <$member[email]>", lang('email', 'birthday_subject'), $birthday_message);
	}
}

?>