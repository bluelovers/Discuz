<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_register.php 507 2010-08-27 02:18:00Z yexinhao $


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

loaducenter();

if(!function_exists('sendmail')) {
	include libfile('function/mail');
}

empty($mrefreshtime) && $mrefreshtime = 2000; //跳轉間隔時間

if($_G['uid']) {
	$ucsynlogin = uc_user_synlogin($_G['uid']);
	showmessage('login_succeed', 'index.php', array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['uid']));
}

$fromuid = !empty($_G['cookie']['promotion']) && $_G['setting']['creditspolicy']['promotion_register'] ? intval($_G['cookie']['promotion']) : 0;

$username = isset($_G['gp_username']) ? $_G['gp_username'] : '';

$bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
$auth = $_G['gp_auth'];

if(!submitcheck('regsubmit', 0, null, null)) {

	$_G['referer'] = isset($_G['referer']) ? dhtmlspecialchars($_G['referer']) : dreferer();

	$username = dhtmlspecialchars($username);

	$htmls = $settings = array();

	include template('member/register');

} else {

	if($bbrules && $bbrulehash != $_POST['agreebbrule']) {
		showmessage('register_rules_agree');
	}

	$username = addslashes(trim(dstripslashes($username)));
	if(uc_get_user($username) && !DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$username'")) {
		if($_G['inajax']) {
			showmessage('profile_username_duplicate');
		} else {
			showmessage('register_activation_message', 'member.php?mod=logging&action=login', array('username' => $username));
		}
	}

	if($_G['gp_password'] !== $_G['gp_password2']) {
		showmessage('profile_passwd_notmatch');
	}

	if(!$_G['gp_password'] || $_G['gp_password'] != addslashes($_G['gp_password'])) {
		showmessage('profile_passwd_illegal');
	}

	$email = trim($_G['gp_email']);
	$password = $_G['gp_password'];

	$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';

	if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
		showmessage('profile_username_protect');
	}

	$profile = $verifyarr = array();

	$uid = uc_user_register($username, $password, $email, $questionid, $answer, $_G['clientip']);

	if($uid <= 0) {
		if($uid == -1) {
			showmessage('profile_username_illegal');
		} elseif($uid == -2) {
			showmessage('profile_username_protect');
		} elseif($uid == -3) {
			showmessage('profile_username_duplicate');
		} elseif($uid == -4) {
			showmessage('profile_email_illegal');
		} elseif($uid == -5) {
			showmessage('profile_email_domain_illegal');
		} elseif($uid == -6) {
			showmessage('profile_email_duplicate');
		} else {
			showmessage('undefined_action', NULL);
		}
	}

	if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid'")) {
		showmessage('profile_uid_duplicate', '', array('uid' => $uid));
	}

	$password = md5(random(10));

	$userdata = array(
		'uid' => $uid,
		'username' => $username,
		'password' => $password,
		'email' => $email,
		'adminid' => 0,
		'groupid' => 0,
		'regdate' => TIMESTAMP,
		'credits' => 0,
		);
	DB::insert('common_member', $userdata);
	$status_data = array(
		'uid' => $uid,
		'regip' => $_G['clientip'],
		'lastip' => $_G['clientip'],
		'lastvisit' => TIMESTAMP,
		'lastactivity' => TIMESTAMP,
		);
	$profile['uid'] = $uid;

	$_G['uid'] = $uid;
	$_G['username'] = $username;
	$_G['member']['username'] = dstripslashes($_G['username']);
	$_G['member']['password'] = $password;
	$_G['groupid'] = $groupinfo['groupid'];

	$_CORE = & discuz_core::instance();
	$_CORE->session->set('uid', $uid);
	$_CORE->session->set('username', $username);

	dsetcookie('auth', authcode("{$_G['member']['password']}\t$_G[uid]", 'ENCODE'), 2592000, 1, true);

	if($welcomemsg && !empty($welcomemsgtxt)) {
		$welcomtitle = !empty($_G['setting']['welcomemsgtitle']) ? $_G['setting']['welcomemsgtitle'] : "Welcome to ".$_G['setting']['bbname']."!";
		$welcomtitle = addslashes(replacesitevar($welcomtitle));
		$welcomemsgtxt = addslashes(replacesitevar($welcomemsgtxt));
		if($welcomemsg == 1) {
			sendpm($uid, $welcomtitle, $welcomemsgtxt, 0);
		} elseif($welcomemsg == 2) {
			sendmail("$username <$email>", $welcomtitle, $welcomemsgtxt);
		}
	}

	dsetcookie('loginuser', '');
	dsetcookie('activationauth', '', -86400 * 365);
	dsetcookie('invite_auth', '', -86400 * 365);

	$_G['setting']['msgforward'] = unserialize($_G['setting']['msgforward']);
	$mrefreshtime = intval($_G['setting']['msgforward']['refreshtime']) * 1000;
	$message = 1;
	include template('member/register');

	$param = array('bbname' => $_G['setting']['bbname'], 'username' => $_G['username'], 'uid' => $_G['uid']);
	showmessage('register_succeed', dreferer(), $param);

}
 */
?>