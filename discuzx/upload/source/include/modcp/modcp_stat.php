<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_stat.php 12590 2010-07-12 03:08:47Z maruitao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$before = $_G['gp_before'];
$before = (isset($before) && $before > 0 && $before <=  $_G['setting']['maxmodworksmonths']) ? intval($before) : 0 ;

list($now['year'], $now['month'], $now['day']) = explode("-", dgmdate(TIMESTAMP, 'Y-n-j'));

$monthlinks = array();
$uid = !empty($_G['gp_uid']) ? $_G['gp_uid'] : 0;
for($i = 0; $i <= $_G['setting']['maxmodworksmonths']; $i++) {
	$month = date("Y-m", mktime(0, 0, 0, $now['month'] - $i, 1, $now['year']));
	if($i != $before) {
		$monthlinks[$i] = "<li><a href=\"forum.php?mod=modcp&action=stat&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
	} else {
		$thismonth = $month;
		$starttime = $month.'-01';
		$endtime = date("Y-m-01", mktime(0, 0, 0, $now['month'] - $i + 1 , 1, $now['year']));
		$daysofmonth = date("t", mktime(0, 0, 0, $now['month'] - $i , 1, $now['year']));
		$monthlinks[$i] = "<li class=\"xw1 a\"><a href=\"forum.php?mod=modcp&action=stat&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
	}
}

$expiretime = date('Y-m', mktime(0, 0, 0, $now['month'] - $_G['setting']['maxmodworksmonths'] - 1, 1, $now['year']));
$daysofmonth = empty($before) ? $now['day'] : $daysofmonth;

$mergeactions = array('OPN' => 'CLS', 'ECL' => 'CLS', 'UEC' => 'CLS', 'EOP' => 'CLS', 'UEO' => 'CLS',
	'UDG' => 'DIG', 'EDI' =>'DIG', 'UED' => 'DIG', 'UST' => 'STK', 'EST' => 'STK',	'UES' => 'STK',
	'DLP' => 'DEL',	'PRN' => 'DEL',	'UDL' => 'DEL',	'UHL' => 'HLT',	'EHL' => 'HLT',	'UEH' => 'HLT',
	'SPL' => 'MRG', 'ABL' => 'EDT', 'RBL' => 'EDT');

if($uid) {

	$uid = $_G['gp_uid'];
	$member = DB::fetch_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$uid' AND adminid>'0'");
	if(!$member) {
		showmessage('undefined_action');
	}

	$modactions = $totalactions = array();
	for($i = 1; $i <= $daysofmonth; $i++) {
		$modactions[sprintf("$thismonth-%02d", $i)] = array();
	}

	$query = DB::query("SELECT * FROM ".DB::table('forum_modwork')." WHERE uid='$uid' AND dateline>='{$starttime}' AND dateline<'$endtime'");
	while($data = DB::fetch($query)) {
		if(isset($mergeactions[$data['modaction']])) {
			$data['modaction'] = $mergeactions[$data['modaction']];
		}
		$modactions[$data['dateline']][$data['modaction']]['count'] += $data['count'];
		$modactions[$data['dateline']][$data['modaction']]['posts'] += $data['posts'];
		$totalactions[$data['modaction']]['count'] += $data['count'];
		$totalactions[$data['modaction']]['posts'] += $data['posts'];
	}

} else {

	$members = array();
	$uids = $totalmodactions = 0;

	$query = DB::query("SELECT uid, username, adminid FROM ".DB::table('common_member')." WHERE adminid IN (1, 2, 3) ORDER BY adminid, uid");
	while($member = DB::fetch($query)) {
		$members[$member['uid']] = $member;
		$uids .= ', '.$member['uid'];
	}

	$query = DB::query("SELECT uid, modaction, SUM(count) AS count, SUM(posts) AS posts
			FROM ".DB::table('forum_modwork')."
			WHERE uid IN ($uids) AND dateline>='$starttime' AND dateline<'$endtime' GROUP BY uid, modaction");

	while($data = DB::fetch($query)) {
		if(isset($mergeactions[$data['modaction']])) {
			$data['modaction'] = $mergeactions[$data['modaction']];
		}
		$members[$data['uid']]['total'] += $data['count'];
		$totalmodactioncount += $data['count'];

		$members[$data['uid']][$data['modaction']]['count'] += $data['count'];
		$members[$data['uid']][$data['modaction']]['posts'] += $data['posts'];

	}

	$avgmodactioncount = @($totalmodactioncount / count($members));
	foreach($members as $id => $member) {
		$members[$id]['totalactions'] = intval($members[$id]['totalactions']);
		$members[$id]['username'] = ($members[$id]['total'] < $avgmodactioncount / 2) ? ('<b><i>'.$members[$id]['username'].'</i></b>') : ($members[$id]['username']);
	}

	if(!empty($before)) {
		DB::query("DELETE FROM ".DB::table('forum_modwork')." WHERE dateline<'{$expiretime}-01'", 'UNBUFFERED');
	} else {
		$members['thismonth'] = $starttime;
		$members['lastupdate'] = TIMESTAMP;
		unset($members['lastupdate'], $members['thismonth']);
	}
}

$modactioncode = lang('forum/modaction');

$bgarray = array();
foreach($modactioncode as $key => $val) {
	if(isset($mergeactions[$key])) {
		unset($modactioncode[$key]);
	}
}

$tdcols = count($modactioncode) + 1;
$tdwidth = floor(90 / ($tdcols - 1)).'%';

?>