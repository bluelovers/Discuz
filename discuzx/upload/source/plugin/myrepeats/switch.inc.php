<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: switch.inc.php 13947 2010-08-04 01:21:30Z zhaoxiongfei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if($_G['gp_formhash'] != FORMHASH) {
	showmessage('undefined_action', NULL);
}

$myrepeatsusergroups = (array)unserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
if(in_array('', $myrepeatsusergroups)) {
	$myrepeatsusergroups = array();
}
if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
	$query = DB::query("SELECT * FROM ".DB::table('myrepeats')." WHERE username='$_G[username]'");
	if(!DB::num_rows($query)) {
		showmessage('myrepeats:usergroup_disabled');
	} else {
		$permusers = array();
		while($user = DB::fetch($query)) {
			$permusers[] = $user['uid'];
		}
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE username='$_G[gp_username]' AND uid IN (".dimplode($permusers).")")) {
			showmessage('myrepeats:usergroup_disabled');
		}
	}
}

require_once libfile('function/member');

$user = DB::fetch_first("SELECT * FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]' AND username='$_G[gp_username]'");
$olddiscuz_uid = $_G['uid'];
$olddiscuz_user = $_G['username'];
$olddiscuz_userss = $_G['member']['username'];
if(!$user) {
	showmessage('myrepeats:user_nonexistence');
} elseif($user['locked']) {
	$usernamess = stripslashes($_G['gp_username']);
	showmessage('myrepeats:user_locked', '', array('user' => $usernamess));
}

list($password, $questionid, $answer) = explode("\t", authcode($user['logindata'], 'DECODE', $_G['config']['security']['authkey']));
$referer = dreferer();

if(!($loginperm = logincheck())) {
	showmessage('myrepeats:login_strike', '', array('loginperm' => $loginperm));
}

$result = userlogin($_G['gp_username'], $password, $questionid, $answer);
if($result['status'] > 0) {
	setloginstatus($result['member'], 2592000);
	DB::query("UPDATE ".DB::table('myrepeats')." SET lastswitch='".TIMESTAMP."' WHERE uid='$olddiscuz_uid' AND username='$_G[gp_username]'");
	$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
	dsetcookie('mrn', '');
	dsetcookie('mrd', '');
	$comment = $user['comment'] ? '('.$user['comment'].') ' : '';
	if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]' AND username='$olddiscuz_user'")) {
		$olddiscuz_userssenc = rawurlencode($olddiscuz_userss);
		showmessage('myrepeats:login_succeed_rsnonexistence', '', array('user' => $discuz_userss, 'comment' => $comment, 'olduser' => $olddiscuz_userss, 'olduserenc' => $olddiscuz_userssenc, 'referer' => $referer), array('extrajs' => $ucsynlogin));
	} else {
		showmessage('myrepeats:login_succeed', $referer, array('user' => $_G['member']['username'], 'comment' => $comment), array('extrajs' => $ucsynlogin));
	}
} elseif($result['status'] == -1) {
	$ucresult['username'] = addslashes($ucresult['username']);
	$auth = authcode("$ucresult[username]\t".FORMHASH, 'ENCODE');
	showmessage('myrepeats:login_activation', 'member.php?mod='.$_G['setting']['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode($referer), array('user' => $username));
} else {
	$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
	$errorlog = dhtmlspecialchars(
		$timestamp."\t".
		($ucresult['username'] ? $ucresult['username'] : stripslashes($username))."\t".
		$password."\t".
		($secques ? "Ques #".intval($questionid) : '')."\t".
		$onlineip);
	writelog('illegallog', $errorlog);
	loginfailed($loginperm);
	$fmsg = $ucresult['uid'] == '-3' ? (empty($questionid) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
	showmessage('myrepeats:'.$fmsg, $referer, array('loginperm' => $loginperm));
}

?>