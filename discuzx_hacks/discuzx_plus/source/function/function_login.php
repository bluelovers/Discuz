<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_login.php 615 2010-09-08 10:40:57Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
* 登陸函數
* @return 登錄狀態
* 1 = 成功， 2 = 成功自動激活，0 = 失敗
*/
function userlogin($username, $password, $questionid, $answer, $loginfield = 'username') {
	$return = array();

	if($loginfield == 'uid') {
		$isuid = 1;
	} elseif($loginfield == 'email') {
		$isuid = 2;
	} elseif($loginfield == 'auto') {
		$isuid = 3;
	} else {
		$isuid = 0;
	}

	if(!function_exists('uc_user_login')) {
		loaducenter();
	}
	if($isuid == 3) {
		if(preg_match('/^[1-9]\d*$/', $username)) {//note username 為uid的可能性很大
			$return['ucresult'] = uc_user_login($username, $password, 1, 1, $questionid, $answer);
		} elseif(isemail($username)) {//note username 為email的可能性很大
			$return['ucresult'] = uc_user_login($username, $password, 2, 1, $questionid, $answer);
		}
		if($return['ucresult'][0] <= 0) {//note 驗證失敗，嘗試使用 username 方式登錄
			$return['ucresult'] = uc_user_login($username, $password, 0, 1, $questionid, $answer);
		}
	} else {
		$return['ucresult'] = uc_user_login($username, $password, $isuid, 1, $questionid, $answer);
	}
	list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email'], $duplicate) = daddslashes($return['ucresult'], 1);
	$return['ucresult'] = $tmp;
	$return['ucresult']['uid'] = intval($return['ucresult']['uid']);

	if($duplicate && $return['ucresult']['uid'] > 0) {
		if($olduid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".addslashes($return['ucresult']['username'])."'")) {
			require_once libfile('function/membermerge');
			membermerge($olduid, $return['ucresult']['uid']);
			uc_user_merge_remove($return['ucresult']['username']);
		} else {
			$return['status'] = 0;
			return $return;
		}
	}

	if($return['ucresult']['uid'] <= 0) {
		$return['status'] = 0;
		return $return;
	}

	$member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='".$return['ucresult']['uid']."'");
	if(!$member) {
		//自動激活
		$return['member'] = autoactivationuser($return['ucresult']['uid']);
		if($return['member']) {
			$return['status'] = 2;
		}
		return $return;
	}
	$return['member'] = $member;
	$return['status'] = 1;

	if(addslashes($member['email']) != $return['ucresult']['email']) {
		DB::query("UPDATE ".DB::table('common_member')." SET email='".$return['ucresult']['email']."' WHERE uid='".$return['ucresult']['uid']."'");
	}

	return $return;
}

function autoactivationuser($uid) {
	global $_G;
	$member = null;
	if(!function_exists('uc_get_user')) {
		loaducenter();
	}
	list($uid, $username, $email) = uc_get_user($uid, 1);
	$uid = intval($uid);
	if($uid > 0) {
		$password = md5(time().rand(100000, 999999));
		$userdata = array(
			'uid' => $uid,
			'username' => addslashes($username),
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
		DB::insert('common_member_status', $status_data);
		$member = $userdata;
	}
	return $member;
}

function setloginstatus($member, $cookietime) {
	global $_G;
	$_G['uid'] = $member['uid'];
	$_G['username'] = $member['username'];
	$_G['adminid'] = $member['adminid'];
	$_G['groupid'] = $member['groupid'];
	$_G['formhash'] = formhash();
	$_G['member'] = $member;
	$_G['core']->session->isnew = 1;

	//note 存放登錄數據
	dsetcookie('auth', authcode("{$member['password']}\t{$member['uid']}", 'ENCODE'), $cookietime, 1, true);
	dsetcookie('loginuser');
	dsetcookie('activationauth');
	dsetcookie('pmnum');
}

function logincheck() {
	global $_G;
	$return = 0;
	$login = DB::fetch_first("SELECT count, lastupdate FROM ".DB::table('common_failedlogin')." WHERE ip='$_G[clientip]'");
	$return = (!$login || (TIMESTAMP - $login['lastupdate'] > 900)) ? 4 : max(0, 5 - $login['count']);

	if(!$login) {
		DB::query("REPLACE INTO ".DB::table('common_failedlogin')." (ip, count, lastupdate) VALUES ('$_G[clientip]', '1', '$_G[timestamp]')");
	} elseif(TIMESTAMP - $login['lastupdate'] > 900) {
		DB::query("REPLACE INTO ".DB::table('common_failedlogin')." (ip, count, lastupdate) VALUES ('$_G[clientip]', '1', '$_G[timestamp]')");
		DB::query("DELETE FROM ".DB::table('common_failedlogin')." WHERE lastupdate<$_G[timestamp]-901", 'UNBUFFERED');
	}
	return $return;
}

function loginfailed() {
	global $_G;
	DB::query("UPDATE ".DB::table('common_failedlogin')." SET count=count+1, lastupdate='$_G[timestamp]' WHERE ip='$_G[clientip]'");
}

?>