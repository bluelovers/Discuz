<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_pm.php 15030 2010-08-18 06:34:38Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loaducenter();

$list = array();

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

if($_GET['subop'] == 'view') {

	if($touid) {
		$list = uc_pm_view($_G['uid'], 0, $touid, $daterange);
		$pmid = empty($list)?0:$list[0]['pmid'];
	} elseif($pmid) {
		$list = uc_pm_view($_G['uid'], $pmid);
		if(!empty($_G['gp_from']) && $_G['gp_from'] == 'privatepm') {
			dsetcookie('viewannouncepmid', $pmid, 31536000);
		}
	}

	$actives = array($daterange=>' class="a"');

} elseif($_GET['subop'] == 'ignore') {

	$ignorelist = uc_pm_blackls_get($_G['uid']);
	$actives = array('ignore'=>' class="a"');

} else {

	$filter = in_array($_GET['filter'], array('newpm', 'privatepm', 'systempm', 'announcepm'))?$_GET['filter']:'privatepm';

	$perpage = 10;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;

	$newannouncepm = array();
	if($filter == 'privatepm' && $page == 1) {
		$result = uc_pm_list($_G['uid'], 1, 1, 'inbox', 'announcepm', 100);
		if(!empty($result['data'][0]) && is_array($result['data'][0]) && $result['data'][0]['pmid'] != $_G['cookie']['viewannouncepmid']) {
			$newannouncepm = $result['data'][0];
		}
	}

	$result = uc_pm_list($_G['uid'], $page, $perpage, 'inbox', $filter, 100);
	$count = $result['count'];
	$list = $result['data'];
	if($_G['member']['newpm']) {

		if($filter == 'privatepm' && !$count) {
			$filter = 'systempm';
			$result = uc_pm_list($_G['uid'], $page, $perpage, 'inbox', $filter, 100);
			$count = $result['count'];
			$list = $result['data'];
		}
		DB::update('common_member', array('newpm'=>0), array('uid'=>$_G['uid']));
		uc_pm_ignore($_G['uid']);
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=space&do=pm&filter=$filter");
	$actives = array($filter=>' class="a"');
}

if($list) {
	$today = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;
	foreach ($list as $key => $value) {
		$value['message'] = str_replace('&amp;', '&', $value['message']);
		$value['message'] = preg_replace("/&[a-z]+\;/i", '', $value['message']);
		$value['daterange'] = 5;
		if($value['dateline'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['dateline'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['dateline'] >= $today - 172800) {
			$value['daterange'] = 3;
		} elseif($value['dateline'] >= $today - 604800) {
			$value['daterange'] = 4;
		}
		$list[$key] = $value;
	}

}

include_once template("diy:home/space_pm");

?>