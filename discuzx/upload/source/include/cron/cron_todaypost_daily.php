<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todaypost_daily.php 21547 2011-03-31 03:55:35Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$yesterdayposts = intval(DB::result_first("SELECT sum(todayposts) FROM ".DB::table('forum_forum').""));

$historypost = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='historyposts'");

$hpostarray = explode("\t", $historypost);
$_G['setting']['historyposts'] = $hpostarray[1] < $yesterdayposts ? "$yesterdayposts\t$yesterdayposts" : "$yesterdayposts\t$hpostarray[1]";

DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('historyposts', '".$_G['setting']['historyposts']."')");
$date = date('Y-m-d', TIMESTAMP - 86400);
DB::query("REPLACE INTO ".DB::table('forum_statlog')."(logdate, fid, `type`, `value`)
	SELECT '$date', fid, '1', todayposts FROM ".DB::table('forum_forum')." WHERE `type` IN ('forum', 'sub') AND `status`<>'3'");
DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts='0'");

save_syscache('historyposts', $_G['setting']['historyposts']);

?>