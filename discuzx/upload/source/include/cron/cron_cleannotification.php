<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleannotification.php 20530 2011-02-25 06:34:44Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$deltime = $_G['timestamp'] - 2*3600*24;
$notifytime = $_G['timestamp'] - 30*3600*24;
DB::query("DELETE FROM ".DB::table('home_notification')." WHERE new='0' AND dateline < '$deltime'");
DB::query("DELETE FROM ".DB::table('home_notification')." WHERE new='1' AND dateline < '$notifytime'");

$deltime = $_G['timestamp'] - 7*3600*24;
DB::query("DELETE FROM ".DB::table('home_pokearchive')." WHERE dateline < '$deltime'");

DB::query("OPTIMIZE TABLE ".DB::table('home_notification'), 'SILENT');

?>