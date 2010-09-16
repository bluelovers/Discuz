<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_logging.php 615 2010-09-08 10:40:57Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if(!in_array($_G['gp_action'], array('login', 'logout', 'wait'))) {
	$_G['gp_action'] = $message = 'wait';
}

loadcache('navlist');
$ctl_obj = new logging_ctl();
$method = 'on_'.$_G['gp_action'];
$ctl_obj->$method();

class logging_ctl {

	var $var = null;

	function logging_ctl() {
		require_once libfile('function/misc');
		require_once libfile('function/login');
		loaducenter();
	}

	function on_wait() {
		global $_G;
		include template('member/login');
	}

	function on_login() {
		global $_G;

		empty($mrefreshtime) && $mrefreshtime = 2000;

		if($_G['uid']) {
			$ucsynlogin = uc_user_synlogin($_G['uid']);
			$param = array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['member']['uid']);
			showmessage('login_succeed', dreferer(), $param, array('showdialog' => 1, 'locationtime' => 1));
		}

		if(!($_G['member_loginperm'] = logincheck())) {
			showmessage('login_strike');
		}

		if(!submitcheck('loginsubmit', 1)) {

			$_G['referer'] = dreferer();

			$cookietimecheck = !empty($_G['cookie']['cookietime']) ? 'checked="checked"' : '';

			$username = !empty($_G['cookie']['loginuser']) ? htmlspecialchars($_G['cookie']['loginuser']) : '';
			include template('member/login');

		} else {

			$_G['uid'] = $_G['member']['uid'] = 0;
			$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
			$result = userlogin($_G['gp_username'], $_G['gp_password'], null, null, 'auto');

			if($result['status'] > 0) {
				setloginstatus($result['member'], $_G['gp_cookietime'] ? 2592000 : 0);
				$ucsynlogin = uc_user_synlogin($_G['uid']);

				$message = 1;
				$param = array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['uid']);
				showmessage('login_succeed', dreferer(), $param, array('showdialog' => 1, 'locationtime' => 1));

			} else {
				$password = preg_replace("/^(.{".round(strlen($_G['gp_password']) / 4)."})(.+?)(.{".round(strlen($_G['gp_password']) / 6)."})$/s", "\\1***\\3", $_G['gp_password']);
				$errorlog = dhtmlspecialchars(
					TIMESTAMP."\t".
					($result['ucresult']['username'] ? $result['ucresult']['username'] : dstripslashes($_G['gp_username']))."\t".
					$password."\t".
					"Ques #".intval($_G['gp_questionid'])."\t".
					$_G['clientip']);
				writelog('illegallog', $errorlog);
				loginfailed($_G['member_loginperm']);
				$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_G['gp_questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
				showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm']));
			}

		}

	}

	function on_logout() {
		global $_G;

		$ucsynlogout = uc_user_synlogout();

		if($_G['gp_formhash'] != $_G['formhash']) {
			showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
		}

		clearcookies();
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
		$_G['setting']['styleid'] = $_G['setting']['styleid'];

		showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout, 'module' => $_G['showmessage']['module'], 'tpl' => $_G['showmessage']['tpl']));
	}

}

function clearcookies() {
	global $_G;
	foreach(array('sid', 'auth', 'visitedfid', 'onlinedetail', 'loginuser', 'activationauth', 'disableprompt', 'indextype') as $k) {
		dsetcookie($k);
	}
	$_G['uid'] = $_G['adminid'] = $_G['member']['credits'] = 0;
	$_G['username'] = $_G['member']['password'] = '';
}
?>