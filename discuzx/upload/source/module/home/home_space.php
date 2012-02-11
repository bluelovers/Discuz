<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_space.php 27593 2012-02-07 03:25:04Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$dos = array('index', 'doing', 'blog', 'album', 'friend', 'wall',
	'notice', 'share', 'home', 'pm', 'videophoto', 'favorite',
	'thread', 'trade', 'poll', 'activity', 'debate', 'reward', 'profile', 'plugin', 'follow');

$do = (!empty($_GET['do']) && in_array($_GET['do'], $dos))?$_GET['do']:'index';

if(in_array($do, array('home', 'doing', 'blog', 'album', 'share', 'wall'))) {
	if(!$_G['setting']['homestatus']) {
		showmessage('home_status_off');
	}
} else {
	$_G['mnid'] = 'mn_common';
}

$uid = empty($_GET['uid']) ? 0 : intval($_GET['uid']);

$member = array();
if($_GET['username']) {
	$member = C::t('common_member')->fetch_by_username($_GET['username']);
	if(empty($member) && !($member = C::t('common_member_archive')->fetch_by_username($_GET['username']))) {
		showmessage('space_does_not_exist');
	}
	$uid = $member['uid'];
}

if($_GET['view'] == 'admin') {
	$_GET['do'] = $do;
}
if(empty($uid) || in_array($do, array('notice', 'pm'))) $uid = $_G['uid'];
if($_G['setting']['followreferer'] && empty($_GET['do']) && !isset($_GET['diy']) || $do == 'follow' || (!$_G['inajax'] && !$_G['setting']['homestatus'] && in_array($do, array('profile', 'follow', 'index', 'thread')))) {
	if($do == 'thread') {
		$_GET['view'] = 'thread';
	}
	if($_G['adminid'] == 1 && $_G['setting']['allowquickviewprofile'] && !in_array($do, array('follow', 'thread'))) {
		dheader("Location:home.php?mod=follow&uid=$uid&do=view&view=profile");
	} elseif($uid != $_G['uid']) {
		$_GET['do'] = 'view';
		$_GET['uid'] = $uid;
	}
	require_once libfile('home/follow', 'module');
	exit;
}

if($uid && empty($member)) {
	$space = getuserbyuid($uid, 1);
	if(empty($space)) {
		showmessage('space_does_not_exist');
	}
} else {
	$space = &$member;
}

if(empty($space)) {
	if(in_array($do, array('doing', 'blog', 'album', 'share', 'home', 'thread', 'trade', 'poll', 'activity', 'debate', 'reward', 'group'))) {
		$_GET['view'] = 'all';
		$space['uid'] = 0;
	} else {
		showmessage('login_before_enter_home', null, array(), array('showmsg' => true, 'login' => 1));
	}
} else {

	$navtitle = $space['username'];

	if($space['status'] == -1 && $_G['adminid'] != 1) {
		showmessage('space_has_been_locked');
	}

	if(in_array($space['groupid'], array(4, 5, 6)) && ($_G['adminid'] != 1 && $space['uid'] != $_G['uid'])) {
		$_GET['do'] = $do = 'profile';
	}

	if($do != 'profile' && $do != 'index' && !ckprivacy($do, 'view')) {
		$_G['privacy'] = 1;
		require_once libfile('space/profile', 'include');
		include template('home/space_privacy');
		exit();
	}

	if(!$space['self'] && $_GET['view'] != 'eccredit' && $_GET['view'] != 'admin') $_GET['view'] = 'me';

	get_my_userapp();

	get_my_app();
}

$diymode = 0;

$seccodecheck = $_G['setting']['seccodestatus'] & 4;
$secqaacheck = $_G['setting']['secqaa']['status'] & 2;

require_once libfile('space/'.$do, 'include');

?>