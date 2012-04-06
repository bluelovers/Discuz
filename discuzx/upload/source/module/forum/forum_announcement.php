<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_announcement.php 20511 2011-02-25 02:59:51Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/discuzcode');

$query = DB::query("SELECT id, subject, groups, author, starttime, endtime, message, type FROM ".DB::table('forum_announcement')." WHERE type!=2 AND starttime<='$_G[timestamp]' AND (endtime='0' OR endtime>'$_G[timestamp]') ORDER BY displayorder, starttime DESC, id DESC");

if(!DB::num_rows($query)) {
	showmessage('announcement_nonexistence');
}

$announcelist = array();
while($announce = DB::fetch($query)) {
	$announce['authorenc'] = rawurlencode($announce['author']);
	$tmp = explode('.', dgmdate($announce['starttime'], 'Y.m'));
	$months[$tmp[0].$tmp[1]] = $tmp;
	if(!empty($_GET['m']) && $_GET['m'] != dgmdate($announce['starttime'], 'Ym')) {
		continue;
	}
	$announce['starttime'] = dgmdate($announce['starttime'], 'd');
	$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'd') : '';
	$announce['message'] = $announce['type'] == 1 ? "[url]{$announce[message]}[/url]" : $announce['message'];
	$announce['message'] = nl2br(discuzcode($announce['message'], 0, 0, 1, 1, 1, 1, 1));
	$announcelist[] = $announce;
}
$annid = isset($_G['gp_id']) ? intval($_G['gp_id']) : 0;

include template('forum/announcement');

?>