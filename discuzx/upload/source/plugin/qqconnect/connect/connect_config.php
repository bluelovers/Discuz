<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_config.php 26770 2011-12-22 10:10:52Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['uid'])) {
	showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
}

$op = !empty($_GET['op']) ? $_GET['op'] : '';
$referer = dreferer();

if(submitcheck('connectsubmit')) {

	if($op == 'config') { // debug 修改QQ绑定设置

		$ispublishfeed = !empty($_GET['ispublishfeed']) ? 1 : 0;
		$ispublisht = !empty($_GET['ispublisht']) ? 1 : 0;
		DB::query("UPDATE ".DB::table('common_member_connect')." SET conispublishfeed='$ispublishfeed', conispublisht='$ispublisht' WHERE uid='$_G[uid]'");
		if (!$ispublishfeed || !$ispublisht) {
			dsetcookie('connect_synpost_tip');
		}
		showmessage('qqconnect:connect_config_success', $referer);

	} elseif($op == 'unbind') {

		$connectService->connectMergeMember();

		$connect_member = DB::fetch_first("SELECT * FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");

		if ($connect_member['conuinsecret']) {

			if($_G['member']['conisregister']) {
				if($_GET['newpassword1'] !== $_GET['newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}

			$connectService->connectUserUnbind();

		} else { // debug 因为老用户access token等信息，所以没法通知connect，所以直接在本地解绑就行了，不fopen connect

			if($_G['member']['conisregister']) {
				if($_GET['newpassword1'] !== $_GET['newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}
		}

		DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='', conuinsecret='', conopenid='', conispublishfeed='0', conispublisht='0', conisregister='0', conisqzoneavatar='0', conisfeed='0' WHERE uid='$_G[uid]'");

		C::t('common_member')->update($_G['uid'], array('conisbind' => 0));
		DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$_G[uid]', '{$_G[member][conopenid]}', '2', '$_G[timestamp]')");

		if($_G['member']['conisregister']) {
			loaducenter();
			uc_user_edit(addslashes($_G['member']['username']), null, $_GET['newpassword1'], null, 1);
		}

		foreach($_G['cookie'] as $k => $v) {
			dsetcookie($k);
		}

		$_G['uid'] = $_G['adminid'] = 0;
		$_G['username'] = $_G['member']['password'] = '';

		showmessage('qqconnect:connect_config_unbind_success', 'member.php?mod=logging&action=login');
	}

} else {

	if($_G[inajax] && $op == 'synconfig') {
		DB::query("UPDATE ".DB::table('common_member_connect')." SET conispublishfeed='0', conispublisht='0' WHERE uid='$_G[uid]'");
		dsetcookie('connect_synpost_tip');

	} else {
		dheader('location: home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp');
	}
}
?>