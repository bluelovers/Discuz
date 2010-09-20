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

if(!$ranklist_setting[$type]['available']) {
	showmessage('ranklist_this_status_off');
}
$cache_time = $ranklist_setting[$type]['cache_time'];
$cache_num =  $ranklist_setting[$type]['show_num'];
if($cache_time <= 0 ) $cache_time = 5;
$cache_time = $cache_time * 3600;
if($cache_num <= 0 ) $cache_num = 20;

$forumsrank = '';
$orderby = 'threads';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_G['gp_orderby']) {
	case 'posts':
		$orderby = 'posts';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_forum_post').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_forum_post');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_forum_post');
		break;
	case 'thismonth':
		$orderby = 'thismonth';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_forum_post_30').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_forum_post_30');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_forum_post_30');
		break;
	case 'today':
		$orderby = 'today';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_forum_post_24').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_forum_post_24');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_forum_post_24');
		break;
	case 'threads':
		$orderby = 'threads';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_forum_thread').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_forum_thread');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_forum_thread');
		break;
	default: $_G['gp_orderby'] = 'threads';
}

$forumsrank = getranklistcache_forums();
$lastupdate = $forumsrank['lastupdate'];
$nextupdate = $forumsrank['nextupdate'];
unset($forumsrank['lastupdated'], $forumsrank['lastupdate'], $forumsrank['nextupdate']);

include template('diy:ranklist/forum');

function getranklistcache_forums() {
	global $_G, $cache_time, $cache_num, $orderby;

	loadcache('ranklist_forum');
	$ranklistvars = & $_G['cache']['ranklist_forum'][$orderby];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_forums($orderby, $cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_forum'][$orderby] = $ranklistvars;
	save_syscache('ranklist_forum', $_G['cache']['ranklist_forum']);
	$lastupdate = $ranklistvars['lastupdate'];
	return $ranklistvars;
}

?>