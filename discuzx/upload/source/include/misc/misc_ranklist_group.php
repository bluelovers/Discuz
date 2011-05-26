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

if(!$_G['setting']['groupstatus']) {
	showmessage('ranklist_this_status_off');
}

$groupsrank = '';
$view = 'threads';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_G['gp_view']) {
	case 'posts':
		$gettype = 'post';
		break;
	case 'thismonth':
		$gettype = 'post_30';
		break;
	case 'today':
		$gettype = 'post_24';
		break;
	case 'threads':
		$gettype = 'thread';
		break;
	case 'credit':
		$gettype = 'credit';
		break;
	case 'member':
		$gettype = 'member';
		break;
	default: $_G['gp_view'] = 'credit';
}
$view = $_G['gp_view'];
$groupsrank = getranklistdata($type, $view);
$lastupdate = $_G['lastupdate'];
$nextupdate = $_G['nextupdate'];

$navtitle = lang('ranklist/navtitle', 'ranklist_title_group_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_group_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_group_'.$gettype);

include template('diy:ranklist/group');

?>