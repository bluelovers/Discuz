<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanfeed.php 29136 2012-03-27 08:30:31Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['setting']['feedday'] < 3) $_G['setting']['feedday'] = 3;
$deltime = $_G['timestamp'] - $_G['setting']['feedday']*3600*24;
$f_deltime = $_G['timestamp'] - $_G['setting']['feedday']*3600*24;

DB::query("DELETE FROM ".DB::table('home_feed')." WHERE dateline < '$deltime' AND hot=0");
DB::query("DELETE FROM ".DB::table('home_feed_app')." WHERE dateline < '$f_deltime'");
DB::query("OPTIMIZE TABLE ".DB::table('home_feed'), 'SILENT');
DB::query("OPTIMIZE TABLE ".DB::table('home_feed_app'), 'SILENT');


?>