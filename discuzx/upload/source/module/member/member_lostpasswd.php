<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_lostpasswd.php 26124 2011-12-02 05:52:24Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

$discuz_action = 141;

if(submitcheck('lostpwsubmit')) {
	loaducenter();
	$_GET['email'] = strtolower(trim($_GET['email']));
	if($_GET['username']) {
		list($tmp['uid'], , $tmp['email']) = uc_get_user(addslashes($_GET['username']));
		if($_GET['email'] != $tmp['email']) {
			showmessage('getpasswd_account_notmatch');
		}
		$member = getuserbyuid($tmp['uid'], 1);
	} else {
		$emailcount = C::t('common_member')->count_by_email($_GET['email'], 1);
		if(!$emailcount) {
			showmessage('抱歉，使用此 Email 的用户不存在，不能使用取回密码功能');
		}
		if($emailcount > 1) {
			showmessage('抱歉，存在多个使用此 Email 的用户，请填写您需要找回密码的用户名');
		}
		$member = C::t('common_member')->fetch_by_email($_GET['email'], 1);
		list($tmp['uid'], , $tmp['email']) = uc_get_user(addslashes($member['username']));
	}
	if(!$member) {
		showmessage('getpasswd_account_notmatch');
	} elseif($member['adminid'] == 1 || $member['adminid'] == 2) {
		showmessage('getpasswd_account_invalid');
	}

	$table_ext = $member['_inarchive'] ? '_archive' : '';
	if($member['email'] != $tmp['email']) {
		C::t('common_member'.$table_ext)->update($tmp['uid'], array('email' => $tmp['email']));
	}

	$idstring = random(6);
	C::t('common_member_field_forum'.$table_ext)->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
	require_once libfile('function/mail');
	$get_passwd_subject = lang('email', 'get_passwd_subject');
	$get_passwd_message = lang(
		'email',
		'get_passwd_message',
		array(
			'username' => $member['username'],
			'bbname' => $_G['setting']['bbname'],
			'siteurl' => $_G['siteurl'],
			'uid' => $member['uid'],
			'idstring' => $idstring,
			'clientip' => $_G['clientip'],
		)
	);
	if(!sendmail("$_GET[username] <$tmp[email]>", $get_passwd_subject, $get_passwd_message)) {
		runlog('sendmail', "$tmp[email] sendmail failed.");
	}
	showmessage('getpasswd_send_succeed', $_G['siteurl'], array(), array('showdialog' => 1, 'locationtime' => true));
}

?>