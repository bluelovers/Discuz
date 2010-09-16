<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleantrace.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$maxday = 90;
$deltime = $_G['timestamp'] - $maxday*3600*24;

DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE dateline < '$deltime'");

DB::query("DELETE FROM ".DB::table('home_visitor')." WHERE dateline < '$deltime'");

?>