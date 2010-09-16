<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_announcement_daily.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

DB::query("UPDATE ".DB::table('common_task')." SET available='2' WHERE available='1' AND starttime>'0' AND starttime<='$_G[timestamp]' AND (endtime IS NULL OR endtime>'$_G[timestamp]')", 'UNBUFFERED');

DB::query("DELETE FROM ".DB::table('forum_announcement')." WHERE endtime<'$_G[timestamp]' AND endtime<>'0'");

if(DB::affected_rows()) {
	require_once libfile('function/cache');
	updatecache(array('announcements', 'announcements_forum'));
}

?>