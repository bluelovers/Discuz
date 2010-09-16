<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_top.php 11682 2010-06-11 02:38:30Z chenchunshao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$ranklist_setting[$type]['available']||!$_G['setting']['groupstatus']) {
	showmessage('ranklist_this_status_off');
}
$cache_time = $ranklist_setting[$type]['cache_time'];
$cache_num =  $ranklist_setting[$type]['show_num'];
if($cache_time <= 0 ) $cache_time = 5;
$cache_time = $cache_time * 3600;
if($cache_num <= 0 ) $cache_num = 20;

$groupsrank = '';
$orderby = 'threads';
switch($_G['gp_orderby']) {
	case 'posts':
		$orderby = 'posts';
		break;
	case 'thismonth':
		$orderby = 'thismonth';
		break;
	case 'today':
		$orderby = 'today';
		break;
	case 'threads':
		$orderby = 'threads';
		break;
	case 'credit':
		$orderby = 'credit';
		break;
	case 'member':
		$orderby = 'member';
		break;
	default: $_G['gp_orderby'] = 'credit';
}

$groupsrank = getranklistcache_groups();
$lastupdate = $groupsrank['lastupdate'];
$nextupdate = $groupsrank['nextupdate'];
unset($groupsrank['lastupdated'], $groupsrank['lastupdate'], $groupsrank['nextupdate']);

include template('diy:ranklist/group');

function getranklistcache_groups() {
	global $_G, $cache_time, $cache_num, $orderby;

	loadcache('ranklist_group');
	$ranklistvars = & $_G['cache']['ranklist_group'][$orderby];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_groups($orderby, $cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_group'][$orderby] = $ranklistvars;
	save_syscache('ranklist_group', $_G['cache']['ranklist_group']);
	$lastupdate = $ranklistvars['lastupdate'];
	return $ranklistvars;
}

?>