<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_getpasswd.php 17149 2010-09-25 04:02:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if($_G['gp_uid'] && $_G['gp_id']) {

	$discuz_action = 141;

	$member = DB::fetch_first("SELECT m.username, m.email, mf.authstr FROM ".DB::table('common_member')." m, ".DB::table('common_member_field_forum')." mf
		WHERE m.uid='$_G[gp_uid]' AND mf.uid=m.uid");

	list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);

	if($dateline < TIMESTAMP - 86400 * 3 || $operation != 1 || $idstring != $_G['gp_id']) {
		showmessage('getpasswd_illegal', NULL);
	}

	if(!submitcheck('getpwsubmit') || $_G['gp_newpasswd1'] != $_G['gp_newpasswd2']) {
		$hashid = $_G['gp_id'];
		$uid = $_G['gp_uid'];
		include template('member/getpasswd');
	} else {
		if($_G['gp_newpasswd1'] != addslashes($_G['gp_newpasswd1'])) {
			showmessage('profile_passwd_illegal');
		}

		loaducenter();
		uc_user_edit($member['username'], $_G['gp_newpasswd1'], $_G['gp_newpasswd1'], $member['email'], 1, 0);
		$password = md5(random(10));

		DB::query("UPDATE ".DB::table('common_member')." SET password='$password' WHERE uid='$_G[gp_uid]'");
		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET authstr='' WHERE uid='$_G[gp_uid]'");

		showmessage('getpasswd_succeed', 'index.php', array(), array('login' => 1));
	}

}
?>