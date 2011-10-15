<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_connect_logging.php 24736 2011-10-10 02:46:07Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!empty($_POST)) {
	if($result['member']['conisbind']) {
		showmessage('qqconnect:connect_register_bind_already');
	}
	if($result['member']['groupid'] == 8) {
		showmessage('qqconnect:connect_register_bind_need_inactive');
	}

	$auth_code = authcode($_G['gp_auth_hash']);
	$auth_code = explode('|', authcode($_G['gp_auth_hash']));
	$conuin = authcode($auth_code[0]);
	$conuinsecret = authcode($auth_code[1]);
	$conopenid = authcode($auth_code[2]);
	$user_auth_fields = authcode($auth_code[3]);

	$conispublishfeed = $conispublisht = 0;
	if ($_G['gp_is_feed']) {
		$conispublishfeed = $conispublisht = 1;
	}

	if ($conuin && $conuinsecret && $conopenid) {
		$connect_member = DB::fetch_first("SELECT uid FROM ".DB::table('common_member_connect')." WHERE uid='$uid'");
		if ($connect_member) {
			DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conispublishfeed='$conispublishfeed', conispublisht='$conispublisht', conisregister='0', conisqzoneavatar='0', conisfeed='$user_auth_fields' WHERE uid='$uid'");
		} else {
			DB::query("INSERT INTO ".DB::table('common_member_connect')." (uid, conuin, conuinsecret, conopenid, conispublishfeed, conispublisht, conisregister, conisqzoneavatar, conisfeed) VALUES ('$uid', '$conuin', '$conuinsecret', '$conopenid', '$conispublishfeed', '$conispublisht', '0', '0', '$user_auth_fields')");
		}
		DB::query("UPDATE ".DB::table('common_member')." SET conisbind='1' WHERE uid='$uid'");
		DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$uid', '$conopenid', '1', '$_G[timestamp]')");

		if ($_G['gp_is_notify']) {
			dsetcookie('connect_js_name', 'user_bind', 86400);
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'registerbind'))), 86400);
		}
		dsetcookie('connect_login', 1, 31536000);
		dsetcookie('connect_is_bind', '1', 31536000);
		dsetcookie('connect_uin', $conopenid, 31536000);
		dsetcookie('stats_qc_reg', 2, 86400);
		if ($_G['gp_is_feed']) {
			dsetcookie('connect_synpost_tip', 1, 31536000);
		}

	} else {
		showmessage('qqconnect:connect_get_access_token_failed', dreferer());
	}
}

?>