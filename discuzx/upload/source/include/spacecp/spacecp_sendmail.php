<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_sendmail.php 10647 2010-05-13 07:05:54Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_GET['op'] = empty($_GET['op']) ? '' : trim($_GET['op']);

if(empty($_G['setting']['sendmailday'])) {
	showmessage('no_privilege_sendmailday');
}

if(submitcheck('setsendemailsubmit')) {
	$_G['gp_sendmail'] = addslashes(serialize($_G['gp_sendmail']));
	DB::update('common_member_field_home', array('acceptemail' => $_G['gp_sendmail']), array('uid' => $_G['uid']));
	showmessage('do_success', 'home.php?mod=spacecp&ac=sendmail');
}


if(empty($space['email']) || !isemail($space['email'])) {
	showmessage('email_input');
}

$sendmail = array();
if($space['acceptemail'] && is_array($space['acceptemail'])) {
	foreach($space['acceptemail'] as $mkey=>$mailset) {
		if($mkey != 'frequency') {
			$sendmail[$mkey] = empty($space['acceptemail'][$mkey]) ? '' : ' checked';
		} else {
			$sendmail[$mkey] = array($space['acceptemail']['frequency'] => 'selected');
		}
	}
}

include_once template("home/spacecp_sendmail");

?>