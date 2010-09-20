<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todaypost_daily.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

DB::query("UPDATE ".DB::table('category_sort')." SET todaythreads='0'");
DB::query("UPDATE ".DB::table('category_house_member')." SET todaythreads='0', todaypush='0', todayrecommend='0', todayhighlight='0'");

?>