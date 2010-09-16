<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_viewlog.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perbatch = 200;

$logs = array();
$maxnum = $maxlogid = 0;
$query = DB::query("SELECT logid, id, idtype FROM ".DB::table('viewlog')." ORDER BY logid ASC LIMIT 0,$perbatch");
while ($value = DB::fetch($query)) {
	$logs[$value['idtype']][$value['id']]++;
	$maxnum++;
	$maxlogid = $value['logid'];
}

if($maxnum) {
	if($maxnum < $perbatch) {
		DB::query("TRUNCATE TABLE ".DB::table('viewlog'));
	} else {
		DB::query("DELETE FROM ".DB::table('viewlog')." WHERE logid<='$maxlogid'");
	}
}

if($logs['uid']) {
	$nums = renum($logs['uid']);
	foreach ($nums[0] as $num) {
		DB::query("UPDATE ".DB::table('common_member')." SET viewnum=viewnum+$num WHERE uid IN (".dimplode($nums[1][$num]).")");
	}
}

if($logs['blogid']) {
	$nums = renum($logs['blogid']);
	foreach ($nums[0] as $num) {
		DB::query("UPDATE ".DB::table('home_blog')." SET viewnum=viewnum+$num WHERE blogid IN (".dimplode($nums[1][$num]).")");
	}
}

?>