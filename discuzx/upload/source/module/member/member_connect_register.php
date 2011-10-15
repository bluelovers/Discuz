<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: member_connect_register.php 24736 2011-10-10 02:46:07Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$from_connect = $_G['setting']['connect']['allow'] ? 1 : 0;
$regname = 'connect';

require_once libfile('function/connect');

if(empty($_POST)) {

	$params = $_GET;
	connect_params($params, $connect_params);

	if(!$connect_params['is_user_info']) {
		$connect_params['x_usernames'] = '';
		$connect_params['x_nick'] = '';
		$connect_params['x_email'] = '';
		$connect_params['x_sex'] = 0;
	}

	$connect_usernames = base64_decode($connect_params['x_usernames']);
	$connect_nick = connect_filter_username(base64_decode($connect_params['x_nick']));
	$_G['qc']['connect_auth_hash'] = $connect_params['auth_hash'];
	$_G['qc']['connect_email'] = $connect_params['x_email'];
	$_G['qc']['dreferer'] = dreferer();

	$auth_code = authcode($_G['qc']['connect_auth_hash']);
	if (empty($auth_code)) {
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}
	$auth_code = explode('|', $auth_code);
	$conopenid = authcode($auth_code[2]);

	$_G['qc']['connect_is_user_info'] = $connect_params['is_user_info'];
	$_G['qc']['connect_is_feed'] = $connect_params['is_feed'];

	$_G['qc']['connect_app_id'] = $_G['setting']['connectappid'];
	$_G['qc']['connect_openid'] = $conopenid;
	unset($auth_code, $conopenid);

	$_G['qc']['connect_is_notify'] = $connect_params['is_notify'] == 1 ? $connect_params['is_notify'] : 0;

	if ($connect_nick) {
		$_G['qc']['qq_nick'] = "<strong> $connect_nick </strong>";
	}

	$_G['qc']['usernames'] = $available_usernames = $unavailable_usernames = array();
	$connect_usernames = array_unique(array_map('connect_filter_username', array_filter(explode(',', $connect_usernames))));

	$_G['qc']['first_available_username'] = '';
	$flag = false;
	$_G['qc']['available_username_count'] = 0;
	if($connect_usernames && is_array($connect_usernames)) {
		foreach($connect_usernames as $username) {
			$username = trim($username);
			$val = array('username' => $username, 'available' => true);
			$ucresult = uc_user_checkname($username);
			if($ucresult < 0) {
				$val['available'] = false;
				array_push($unavailable_usernames, $val);
			} else {
				if (!$flag) {
					$_G['qc']['first_available_username'] = $val['username'];
				}
				array_push($available_usernames, $val);
				$_G['qc']['available_username_count']++;
				$flag = true;
			}
		}
		$_G['qc']['usernames'] = array_merge($available_usernames, $unavailable_usernames);
	}

	$ucresult = uc_user_checkname($connect_nick);
	if($ucresult >= 0) {
		$_G['qc']['first_available_username'] = $connect_nick;
	}

	$connectdefault['gender'] = $connect_params['x_sex'];

	list($connectdefault['birthyear'], $connectdefault['birthmonth'], $connectdefault['birthday']) = explode('-', $connect_params['x_birthday']);
	foreach($_G['cache']['fields_register'] as $field) {
		$fieldid = $field['fieldid'];
		$html = profile_setting($fieldid, $connectdefault);
		if($html) {
			$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
			$htmls[$fieldid] = $html;
		}
	}

} else {

	if(!empty($_G['setting']['checkuinlimit']) && !empty($_G['gp_uin'])) {
		if($_G['qc']['uinlimit']) {
			showmessage('qqconnect:connect_register_uinlimit', '', array('limit' => $this->setting['connect']['register_uinlimit']));
		}
		if(!$_G['setting']['regconnect']) {
			showmessage('qqconnect:connect_register_closed');
		}
	}

	if(empty($_G['gp_auth_hash'])) {
		$_G['gp_auth_hash'] = $_G['cookie']['con_auth_hash'];
	}
	$auth_code = authcode($_G['gp_auth_hash']);
	$auth_code = explode('|', authcode($_G['gp_auth_hash']));
	$conuin = authcode($auth_code[0]);
	$conuinsecret = authcode($auth_code[1]);
	$conopenid = authcode($auth_code[2]);
	$user_auth_fields = authcode($auth_code[3]);

	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', 1, $cookie_expires);

	if (!$conuin || !$conuinsecret || !$conopenid) {
		showmessage('qqconnect:connect_get_request_token_failed');
	}

	$conispublishfeed = $conispublisht = 0;
	if ($_G['gp_is_feed']) {
		$conispublishfeed = $conispublisht = 1;
	}
	$is_qzone_avatar = !empty($_G['gp_use_qzone_avatar']) ? 1 : 0;
	$userdata['avatarstatus'] = !empty($_G['gp_use_qzone_avatar']) ? 1 : 0;
	$userdata['conisbind'] = 1;

	DB::query("INSERT INTO ".DB::table('common_member_connect')." (uid, conuin, conuinsecret, conopenid, conispublishfeed, conispublisht, conisregister, conisqzoneavatar, conisfeed) VALUES ('$uid', '$conuin', '$conuinsecret', '$conopenid', '$conispublishfeed', '$conispublisht', '1', '$is_qzone_avatar', '$user_auth_fields')");

	if ($_G['gp_is_notify']) {
		dsetcookie('connect_js_name', 'user_bind', 86400);
		dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'register'))), 86400);
	}
	dsetcookie('connect_login', 1, 31536000);
	dsetcookie('connect_is_bind', '1', 31536000);
	dsetcookie('connect_uin', $conopenid, 31536000);
	dsetcookie('stats_qc_reg', 1, 86400);
	if ($_G['gp_is_feed']) {
		dsetcookie('connect_synpost_tip', 1, 31536000);
	}

	DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$uid', '$conopenid', '1', '$_G[timestamp]')");

	dsetcookie('con_auth_hash');

	if($_G['setting']['connect']['register_groupid']) {
		$userdata['groupid'] = $groupinfo['groupid'] = $_G['setting']['connect']['register_groupid'];
	}

	if($_G['setting']['connect']['register_addcredit']) {
		$init_arr[$_G['setting']['connect']['register_rewardcredit']] += $_G['setting']['connect']['register_addcredit'];
	}

}

function connect_filter_username($username) {
	$username = str_replace(' ', '_', trim($username));
	return cutstr($username, 15, '');
}

?>