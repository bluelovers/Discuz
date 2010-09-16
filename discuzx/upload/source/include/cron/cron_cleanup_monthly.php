<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanup_monthly.php 12003 2010-06-23 07:41:55Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$myrecordtimes = TIMESTAMP - $_G['setting']['myrecorddays'] * 86400;

DB::query("DELETE FROM ".DB::table('common_mytask')." WHERE status='-1' AND dateline<'$_G[timestamp]'-2592000", 'UNBUFFERED');

DB::query("UPDATE ".DB::table('common_onlinetime')." SET thismonth='0'");
?>