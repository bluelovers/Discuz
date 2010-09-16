<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit_log.php 16218 2010-09-02 02:43:22Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$perpage = 20;
$start = ($page-1)*$perpage;

$gets = array(
	'mod' => 'spacecp',
	'op' => $_G['gp_op'],
	'ac' => 'credit',
	'suboperation' => $_G['gp_suboperation']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

if($_G['gp_suboperation'] == 'paymentlog') {

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='BTC'"), 0);
	$loglist = array();
	if($count) {
		$query = DB::query("SELECT l.*,f.fid, f.name, t.tid, t.subject, t.dateline AS tdateline FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=l.relatedid
			LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
			WHERE l.uid='$_G[uid]' AND l.operation='BTC' ORDER BY l.dateline DESC
			LIMIT $start,$perpage");

		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['tdateline'] = dgmdate($log['tdateline'], 'u');
			$loglist[] = $log;
		}
	}

} elseif($_G['gp_suboperation'] == 'incomelog') {

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='STC'"), 0);
	$loglist = array();

	if($count) {

		$query = DB::query("SELECT l.*,t.tid,t.subject,t.dateline AS tdateline FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=l.relatedid
			WHERE l.operation='STC' AND l.uid='$_G[uid]' ORDER BY l.dateline DESC LIMIT $start, $perpage");

		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['tdateline'] = dgmdate($log['tdateline'], 'u');
			$loglist[] = $log;
		}
	}

} elseif($_G['gp_suboperation'] == 'attachpaymentlog') {

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='BAC'"), 0);
	$loglist = array();
	if($count) {
		$query = DB::query("SELECT l.*, a.filename, a.pid, a.dateline as adateline, t.subject, t.tid, t.author, t.authorid
			FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('forum_attachment')." a ON a.aid=l.relatedid
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=a.tid
			WHERE l.uid='$_G[uid]' AND l.operation='BAC' ORDER BY l.dateline DESC
			LIMIT $start, $perpage");
		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['adateline'] = dgmdate($log['adateline'], 'u');
			$loglist[] = $log;
		}
	}

} elseif($_G['gp_suboperation']== 'attachincomelog') {

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='SAC'"), 0);
	if($count) {

		$query = DB::query("SELECT l.*,a.filename, a.pid, a.tid, a.dateline AS adateline, t.subject, t.tid
			FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('forum_attachment')." a ON a.aid=l.relatedid
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=a.tid
			WHERE l.operation='SAC' AND l.uid='$_G[uid]' ORDER BY l.dateline DESC LIMIT $start, $perpage");

		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['adateline'] = dgmdate($log['adateline'], 'u');
			$loglist[] = $log;
		}
	}

} elseif($_G['gp_suboperation'] == 'rewardpaylog') {

	$loglist = array();
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='RTC'"), 0);
	if($count) {

		$query = DB::query("SELECT l.*, f.fid, f.name, t.tid, t.subject, t.price
			FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=l.relatedid
			LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
			WHERE l.uid='$_G[uid]' AND l.operation='RTC' ORDER BY l.dateline DESC LIMIT $start, $perpage");

		while($log = DB::fetch($query)) {
				$log['dateline'] = dgmdate($log['dateline'], 'u');
				$log['price'] = abs($log['price']);
				$loglist[] = $log;
		}
	}

} elseif($_G['gp_suboperation'] == 'rewardincomelog') {

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='RAC'"), 0);
	if($count) {
		$loglist = array();
		$query = DB::query("SELECT l.*, f.fid, f.name, t.tid, t.subject, t.price
			FROM ".DB::table('common_credit_log')." l
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=l.relatedid
			LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
			WHERE l.uid='$_G[uid]' AND l.operation='RAC' ORDER BY l.dateline DESC
			LIMIT $start,$perpage");
		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['price'] = abs($log['price']);
			$loglist[] = $log;
		}
	}

} elseif($_G['gp_suboperation'] == 'creditrulelog') {

	$count = DB::result(DB::query("SELECT count(*) FROM ".DB::table('common_credit_rule_log')." WHERE uid='$_G[uid]'"), 0);
	if($count) {
		$query = DB::query("SELECT r.rulename, l.* FROM ".DB::table('common_credit_rule_log')." l LEFT JOIN ".DB::table('common_credit_rule')." r USING(rid) WHERE l.uid='$_G[uid]' ORDER BY l.dateline DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {
			$list[] = $value;
		}
	}
} else {

	loadcache('usergroups');
	$suboperation = 'creditslog';
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation IN('TFR', 'RCV', 'CEC', 'ECU', 'AFD', 'UGP', 'TRC', 'MRC', 'BGC', 'RGC', 'AGC')"), 0);
	if($count) {
		$loglist = array();
		$query = DB::query("SELECT l.*, m.uid AS mid, m.username FROM ".DB::table('common_credit_log')." l
							LEFT JOIN ".DB::table('common_member')." m ON m.uid=l.relatedid
							WHERE l.uid='$_G[uid]' AND l.operation IN('TFR', 'RCV', 'CEC', 'ECU', 'AFD', 'UGP', 'TRC', 'MRC', 'BGC', 'RGC', 'AGC')
							ORDER BY l.dateline DESC LIMIT $start,$perpage");
		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$loglist[] = $log;
		}
	}
}

if($count) {
	$multi = multi($count, $perpage, $page, $theurl);
}
include template('home/spacecp_credit_log');
?>