<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: batch.login.php 4456 2010-09-14 13:45:18Z yexinhao $
 */
define('ACTION', 'auth');
include_once('./common.php');
include_once(B_ROOT.'./language/batch.lang.php');

$action = empty($_GET['action'])?'':$_GET['action'];
if(empty($action)) exit('Access Denied');

if(postget('refer')) {
	$refer = postget('refer');
} else {
	if(!empty($_SERVER['HTTP_REFERER'])) {
		$refer = $_SERVER['HTTP_REFERER'];
	} else {
		$refer = B_URL_ALL;
	}
}
// // 防止死循环
if(strpos($refer,'login.php')) $refer="index.php";

include_once(B_ROOT.'./uc_client/client.php');

switch ($action) {
	case 'login_form':
		 $_G['login_type'] = 'login';
		include template('templates/site/default/login.html.php', 1);
		exit;
		break;
	case 'register_form':
		 $_G['login_type'] = 'register';
		include template('templates/site/default/login.html.php', 1);
		exit;
		break;
	case 'login':
		$cookietime = 0;

		$cookietime = !empty($_POST['cookietime'])?intval($_POST['cookietime']):900;
		if (submitcheck('loginsubmit')) {
			$password = $_POST['password'];
			$username = $_POST['username'];

			$ucresult = uc_user_login($username, $password, $loginfield == 'uid');
			list($members['uid'], $members['username'], $members['password'], $members['email']) = saddslashes($ucresult);
			if($members['uid'] <= 0) {
				showmessage('login_error',  "index.php");
			} else {
				if($_G['setting']['seccode']) {
					if(!empty($_POST['seccode'])) {
						if(!ckseccode($_POST['seccode'])) {
							showmessage('incorrect_code', $refer);
						}
					} else {
						showmessage('seccode_notwrite', $refer);
					}
				}
			}

			//登录成功

			$uid = $_G['uid'] = $members['uid'];
			$_G['username'] = $members['username'];
			$query = DB::query("SELECT * FROM ".tname('members')." WHERE uid='$uid'");
			if($oldmember = DB::fetch($query)) {
				$password = $oldmember['password'];
				$dateline = $oldmember['dateline'];
				$updatetime = $oldmember['updatetime'];
				$groupid = $oldmember['groupid'];
				$email = $members['email'];
			} else {
				$password = md5($uid.'|'.random(8));
				$groupid = 2;
				$dateline = $_G['timestamp'];
				$updatetime = $_G['timestamp'];
				$email = $members['email'];
			}
			$insertsqlarr = array(
				'uid' => $uid,
				'username' => addslashes($members['username']),
				'password' => $password,
				'groupid' => $groupid,
				'email' => $email,
				'dateline' => $dateline,
				'updatetime' => $updatetime,
				'lastlogin' => $_G['timestamp'],
				'ip' => $_G['clientip']
			);
			if(empty($oldmember)) {
				inserttable('members', $insertsqlarr);
			} else {
				updatetable('members', $insertsqlarr, array('uid'=>$_G['uid']));
			}

			$cookievalue = authcode("$password\t$uid", 'ENCODE');
			ssetcookie('auth', $cookievalue, $cookietime, 1, true);
			setcookie('_refer', '');
			$ucsynlogin = uc_user_synlogin($uid);
			showmessage('login_succeed', rawurldecode($refer), 3, array('ucsynlogin' => $ucsynlogin));
		}
		break;
	case 'logout':
		obclean();
		sclearcookie();
		setcookie('_refer', '');
		$ucsynlogin = uc_user_synlogout();
		DB::query("DELETE FROM ".tname('adminsession')." WHERE uid='$_G[uid]'");
		unset($_G['uid']);
		showmessage('logout_succeed', 'index.php', 3, array('ucsynlogin' => $ucsynlogin));
		break;
	default:
		break;
}

setcookie('_refer', '');
showmessage('login_succeed', rawurldecode($refer));

?>