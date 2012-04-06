<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: switch.inc.php 21516 2011-03-30 01:43:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if($_G['gp_formhash'] != FORMHASH) {
	showmessage('undefined_action');
}

$myrepeatsusergroups = (array)unserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
$referer = dreferer();

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

$_G['myrepeats_loginperm'] = logincheck($_G['gp_username']);
if(!$_G['myrepeats_loginperm']) {
	showmessage('myrepeats:login_strike', '', array('loginperm' => $_G['myrepeats_loginperm']));
}

if(!empty($_G['gp_authorfirst']) && submitcheck('myrepeatssubmit')) {
	$result = userlogin($_G['gp_username'], $_G['gp_password'], $_G['gp_questionid'], $_G['gp_answer']);
	$_G['myrepeats_ucresult'] = $result['ucresult'];
	if($result['status'] > 0) {
		$logindata = addslashes(authcode($_G['gp_password']."\t".$_G['gp_questionid']."\t".$_G['gp_answer'], 'ENCODE', $_G['config']['security']['authkey']));
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]' AND username='$_G[gp_username]'")) {
			DB::query("UPDATE ".DB::table('myrepeats')." SET logindata='$logindata' WHERE uid='$_G[uid]' AND username='$_G[gp_username]'");
		} else {
			DB::query("INSERT INTO ".DB::table('myrepeats')." (uid, username, logindata, comment) VALUES ('$_G[uid]', '$_G[gp_username]', '$logindata', '')");
		}
	} else {
		myrepeats_loginfailure($_G['gp_username'], $_G['gp_password'], $_G['gp_questionid'], $_G['gp_answer']);
	}
}

$user = DB::fetch_first("SELECT * FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]' AND username='$_G[gp_username]'");
$olddiscuz_uid = $_G['uid'];
$olddiscuz_user = $_G['username'];
$olddiscuz_userss = $_G['member']['username'];

if(!$user) {
	$newuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_username]'");
	if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('myrepeats')." WHERE uid='$newuid' AND username='".addslashes($olddiscuz_userss)."'")) {
		$username = htmlspecialchars($_G['gp_username']);
		include template('myrepeats:switch_login');
		exit;
	}
	showmessage('myrepeats:user_nonexistence');
} elseif($user['locked']) {
	$usernamess = stripslashes($_G['gp_username']);
	showmessage('myrepeats:user_locked', '', array('user' => $usernamess));
}

list($password, $questionid, $answer) = explode("\t", authcode($user['logindata'], 'DECODE', $_G['config']['security']['authkey']));

$result = userlogin($_G['gp_username'], $password, $questionid, $answer);
$_G['myrepeats_ucresult'] = $result['ucresult'];
if($result['status'] > 0) {
	setloginstatus($result['member'], 2592000);
	DB::query("UPDATE ".DB::table('myrepeats')." SET lastswitch='".TIMESTAMP."' WHERE uid='$olddiscuz_uid' AND username='$_G[gp_username]'");
	$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
	dsetcookie('mrn', '');
	dsetcookie('mrd', '');
	$comment = $user['comment'] ? '('.$user['comment'].') ' : '';
	showmessage('myrepeats:login_succeed', $referer, array('user' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'comment' => $comment), array('showmsg' => 1, 'showdialog' => 1, 'locationtime' => 3, 'extrajs' => $ucsynlogin));
} elseif($result['status'] == -1) {
	clearcookies();
	$_G['myrepeats_ucresult']['username'] = addslashes($_G['myrepeats_ucresult']['username']);
	$_G['username'] = '';
	$_G['uid'] = 0;
	$auth = authcode($_G['myrepeats_ucresult']['username']."\t".formhash(), 'ENCODE');
	showmessage('myrepeats:login_activation', 'member.php?mod='.$_G['setting']['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode($referer), array('user' => $_G['myrepeats_ucresult']['username']), array('showmsg' => 1, 'showdialog' => 1, 'locationtime' => 3));
} else {
	myrepeats_loginfailure($_G['gp_username'], $password, $questionid, $answer);
}

function myrepeats_loginfailure($username, $password, $questionid, $answer) {
	global $_G;
	$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
	$errorlog = dhtmlspecialchars(
		TIMESTAMP."\t".
		($_G['myrepeats_ucresult']['username'] ? $_G['myrepeats_ucresult']['username'] : stripslashes($username))."\t".
		$password."\t".
		"Ques #".intval($questionid)."\t".
		$_G['clientip']);
	writelog('illegallog', $errorlog);
	loginfailed($username);
	$fmsg = $_G['myrepeats_ucresult']['uid'] == '-3' ? (empty($questionid) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
	showmessage('myrepeats:'.$fmsg, '', array('loginperm' => $_G['myrepeats_loginperm']));
}

?>