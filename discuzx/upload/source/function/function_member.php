<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_login.php 12578 2010-07-09 15:41:43Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

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
		if(preg_match('/^[1-9]\d*$/', $username)) {
			$return['ucresult'] = uc_user_login($username, $password, 1, 1, $questionid, $answer);
		} elseif(isemail($username)) {
			$return['ucresult'] = uc_user_login($username, $password, 2, 1, $questionid, $answer);
		}
		if($return['ucresult'][0] <= 0 && $return['ucresult'][0] != -3) {
			$return['ucresult'] = uc_user_login($username, $password, 0, 1, $questionid, $answer);
		}
	} else {
		$return['ucresult'] = uc_user_login($username, $password, $isuid, 1, $questionid, $answer);
	}
	$tmp = array();
	$duplicate = '';
	list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email'], $duplicate) = daddslashes($return['ucresult'], 1);
	$return['ucresult'] = $tmp;
	if($duplicate && $return['ucresult']['uid'] > 0) {
		if($olduid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".addslashes($return['ucresult']['username'])."'")) {
			if($olduid != $return['ucresult']['uid']) {
				membermerge($olduid, $return['ucresult']['uid']);
			}
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
		$return['status'] = -1;
		return $return;
	}
	$return['member'] = $member;
	$return['status'] = 1;

	if(addslashes($member['email']) != $return['ucresult']['email']) {
		DB::query("UPDATE ".DB::table('common_member')." SET email='".$return['ucresult']['email']."' WHERE uid='".$return['ucresult']['uid']."'");
	}

	return $return;
}

function setloginstatus($member, $cookietime) {
	global $_G;
	$_G['uid'] = $member['uid'];
	$_G['username'] = addslashes($member['username']);
	$_G['adminid'] = $member['adminid'];
	$_G['groupid'] = $member['groupid'];
	$_G['formhash'] = formhash();
	$_G['session']['invisible'] = getuserprofile('invisible');
	$_G['member'] = $member;
	loadcache('usergroup_'.$_G['groupid']);
	$discuz = & discuz_core::instance();
	$discuz->session->isnew = true;

	dsetcookie('auth', authcode("{$member['password']}\t{$member['uid']}", 'ENCODE'), $cookietime, 1, true);
	dsetcookie('loginuser');
	dsetcookie('activationauth');
	dsetcookie('pmnum');

	include_once libfile('function/stat');
	updatestat('login', 1);
	if(defined('IN_MOBILE')) {
		updatestat('mobilelogin', 1);
	}
	if($_G['setting']['connect']['allow'] && $_G['member']['conisbind']) {
		updatestat('connectlogin', 1);
	}
	updatecreditbyaction('daylogin', $_G['uid']);
	checkusergroup($_G['uid']);
}

function logincheck($username) {
	global $_G;
	$return = 0;
	$username = addslashes(trim(stripslashes($username)));
	$login = DB::fetch_first("SELECT count, lastupdate FROM ".DB::table('common_failedlogin')." WHERE ip='$_G[clientip]' AND username='$username'");
	$return = (!$login || (TIMESTAMP - $login['lastupdate'] > 900)) ? 4 : max(0, 4 - $login['count']);

	if(!$login) {
		DB::query("REPLACE INTO ".DB::table('common_failedlogin')." (ip, username, count, lastupdate) VALUES ('$_G[clientip]', '$username', '0', '$_G[timestamp]')");
	} elseif(TIMESTAMP - $login['lastupdate'] > 900) {
		DB::query("REPLACE INTO ".DB::table('common_failedlogin')." (ip, username, count, lastupdate) VALUES ('$_G[clientip]', '$username', '0', '$_G[timestamp]')");
		DB::query("DELETE FROM ".DB::table('common_failedlogin')." WHERE lastupdate<$_G[timestamp]-901", 'UNBUFFERED');
	}
	return $return;
}

function loginfailed($username) {
	global $_G;
	$username = addslashes(trim(stripslashes($username)));
	DB::query("UPDATE ".DB::table('common_failedlogin')." SET count=count+1, lastupdate='$_G[timestamp]' WHERE ip='$_G[clientip]' AND username='$username'");
}

function getuidfields() {
	return array(
		'common_credit_log',
		'common_credit_rule_log',
		'common_credit_rule_log_field',
		'common_invite|uid,fuid',
		'common_mailcron|touid',
		'common_member',
		'common_member_count',
		'common_member_field_forum',
		'common_member_field_home',
		'common_member_log',
		'common_member_profile',
		'common_member_status',
		'common_member_validate',
		'common_myinvite|fromuid,touid',
		'forum_access',
		'forum_activity',
		'forum_activityapply',
		'forum_attachment',
		'forum_attachment_0',
		'forum_attachment_1',
		'forum_attachment_2',
		'forum_attachment_3',
		'forum_attachment_4',
		'forum_attachment_5',
		'forum_attachment_6',
		'forum_attachment_7',
		'forum_attachment_8',
		'forum_attachment_9',
		'forum_creditslog',
		'forum_debate',
		'forum_debatepost',
		'home_favorite',
		'forum_medallog',
		'common_member_magic',
		'forum_memberrecommend|recommenduid',
		'forum_moderator',
		'forum_modwork',
		'common_mytask',
		'forum_order',
		'forum_groupinvite',
		'forum_groupuser',
		'forum_pollvoter',
		'forum_post|authorid',
		'forum_thread|authorid',
		'forum_threadmod',
		'forum_tradecomment|raterid,rateeid',
		'forum_tradelog|sellerid,buyerid',
		'home_album',
		'home_appcreditlog',
		'home_blacklist|uid,buid',
		'home_blog',
		'home_blogfield',
		'home_class',
		'home_clickuser',
		'home_comment|uid,authorid',
		'home_docomment',
		'home_doing',
		'home_feed',
		'home_feed_app',
		'home_friend|uid,fuid',
		'home_friendlog|uid,fuid',
		'home_pic',
		'home_share',
		'home_userapp',
		'home_userappfield',
		'common_admincp_member'
	);
}

function membermerge($olduid, $newuid) {
	$uidfields = getuidfields();
	foreach($uidfields as $value) {
		list($table, $field, $stepfield) = explode('|', $value);
		$fields = !$field ? array('uid') : explode(',', $field);
		foreach($fields as $field) {
			DB::query("UPDATE `".DB::table($table)."` SET `$field`='$newuid' WHERE `$field`='$olduid'");
		}
	}
}

function getinvite() {
	global $_G;

	if($_G['setting']['regstatus'] == 1) return array();
	$result = array();
	$cookies = empty($_G['cookie']['invite_auth']) ? array() : explode(',', $_G['cookie']['invite_auth']);
	$cookiecount = count($cookies);
	if($cookiecount == 2 || $_G['gp_invitecode']) {
		$id = intval($cookies[0]);
		$code = $cookies[1];
		if($_G['gp_invitecode']) {
			$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE code='$_G[gp_invitecode]'");
			$code = $_G['gp_invitecode'];
		} else {
			$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE id='$id'");
		}
		if($invite = DB::fetch($query)) {
			if($invite['code'] == $code && empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
				$result['uid'] = $invite['uid'];
				$result['id'] = $invite['id'];
				$result['appid'] = $invite['appid'];
			}
		}
	} elseif($cookiecount == 3) {
		$uid = intval($cookies[0]);
		$code = $cookies[1];
		$appid = intval($cookies[2]);

		$invite_code = space_key($uid, $appid);
		if($code == $invite_code) {
			$groupid = DB::result_first("SELECT groupid FROM ".DB::table('common_member')." WHERE uid='$uid'");
			$inviteprice = DB::result_first("SELECT inviteprice FROM ".DB::table('common_usergroup')." WHERE groupid='$groupid'");
			if($inviteprice > 0) return array();
			$result['uid'] = $uid;
			$result['appid'] = $appid;
		}
	}

	if($result['uid']) {
		$member = getuserbyuid($result['uid']);
		$result['username'] = $member['username'];
	} else {
		dsetcookie('invite_auth', '');
	}

	return $result;
}

function replacesitevar($string, $replaces = array()) {
	global $_G;
	$sitevars = array(
		'{sitename}' => $_G['setting']['sitename'],
		'{bbname}' => $_G['setting']['bbname'],
		'{time}' => dgmdate(TIMESTAMP, 'Y-n-j H:i'),
		'{adminemail}' => $_G['setting']['adminemail'],
		'{username}' => $_G['member']['username'],
		'{myname}' => $_G['member']['username']
	);
	$replaces = array_merge($sitevars, $replaces);
	return str_replace(array_keys($replaces), array_values($replaces), $string);
}

function clearcookies() {
	global $_G;
	foreach($_G['cookie'] as $k => $v) {
		if($k != 'widthauto') {
			dsetcookie($k);
		}
	}
	$_G['uid'] = $_G['adminid'] = 0;
	$_G['username'] = $_G['member']['password'] = '';
}

?>