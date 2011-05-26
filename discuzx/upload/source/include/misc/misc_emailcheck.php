<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_emailcheck.php 17754 2010-11-01 01:55:58Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$uid = 0;
$email = '';
$_G['gp_hash'] = empty($_G['gp_hash']) ? '' : $_G['gp_hash'];
if($_G['gp_hash']) {
	list($uid, $email, $time) = explode("\t", authcode($_G['gp_hash'], 'DECODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16))));
	$uid = intval($uid);
}

if($uid && isemail($email) && $time > TIMESTAMP - 86400) {
	$memberarr = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$uid'");
	$setarr = array('email'=>addslashes($email), 'emailstatus'=>'1');
	updatecreditbyaction('realemail', $uid);
	DB::update('common_member', $setarr, array('uid' => $uid));

	showmessage('email_check_sucess', 'home.php?mod=spacecp&ac=profile&op=password', array('email' => $email));
} else {
	showmessage('email_check_error', 'index.php');
}

?>