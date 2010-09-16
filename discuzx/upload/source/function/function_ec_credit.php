<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_ec_credit.php 16321 2010-09-03 06:29:51Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updatecreditcache($uid, $type, $return = 0) {
	$all = countcredit($uid, $type);
	$halfyear = countcredit($uid, $type, 180);
	$thismonth = countcredit($uid, $type, 30);
	$thisweek = countcredit($uid, $type, 7);
	$before = array(
		'good' => $all['good'] - $halfyear['good'],
		'soso' => $all['soso'] - $halfyear['soso'],
		'bad' => $all['bad'] - $halfyear['bad'],
		'total' => $all['total'] - $halfyear['total']
	);

	$data = array('all' => $all, 'before' => $before, 'halfyear' => $halfyear, 'thismonth' => $thismonth, 'thisweek' => $thisweek);

	DB::query("REPLACE INTO ".DB::table('forum_spacecache')." (uid, variable, value, expiration) VALUES ('$uid', '$type', '".addslashes(serialize($data))."', '".getexpiration()."')");
	if($return) {
		return $data;
	}
}

function countcredit($uid, $type, $days = 0) {
	$type = $type == 'buyercredit' ? 1 : 0;
	$timeadd = $days ? ("AND dateline>='".(TIMESTAMP - $days * 86400)."'") : '';
	$query = DB::query("SELECT score FROM ".DB::table('forum_tradecomment')." WHERE rateeid='$uid' AND type='$type' $timeadd");
	$good = $soso = $bad = 0;
	while($credit = DB::fetch($query)) {
		if($credit['score'] == 1) {
			$good++;
		} elseif($credit['score'] == 0) {
			$soso++;
		} else {
			$bad++;
		}
	}
	return array('good' => $good, 'soso' => $soso, 'bad' => $bad, 'total' => $good + $soso + $bad);
}

function updateusercredit($uid, $type, $level) {
	$uid = intval($uid);
	if(!$uid || !in_array($type, array('buyercredit', 'sellercredit')) || !in_array($level, array('good', 'soso', 'bad'))) {
		return;
	}

	if($cache = DB::fetch_first("SELECT value, expiration FROM ".DB::table('forum_spacecache')." WHERE uid='$uid' AND variable='$type'")) {
		$expiration = $cache['expiration'];
		$cache = unserialize($cache['value']);
	} else {
		$init = array('good' => 0, 'soso' => 0, 'bad' => 0, 'total' => 0);
		$cache = array('all' => $init, 'before' => $init, 'halfyear' => $init, 'thismonth' => $init, 'thisweek' => $init);
		$expiration = getexpiration();
	}

	foreach(array('all', 'before', 'halfyear', 'thismonth', 'thisweek') as $key) {
		$cache[$key][$level]++;
		$cache[$key]['total']++;
	}

	DB::query("REPLACE INTO ".DB::table('forum_spacecache')." (uid, variable, value, expiration) VALUES ('$uid', '$type', '".addslashes(serialize($cache))."', '$expiration')");

	$score = $level == 'good' ? 1 : ($level == 'soso' ? 0 : -1);
	DB::query("UPDATE ".DB::table('common_member_status')." SET $type=$type+($score) WHERE uid='$uid'");
}

?>