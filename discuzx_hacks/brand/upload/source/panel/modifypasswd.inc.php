<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modifypasswd.inc.php 4337 2010-09-06 04:48:05Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}

$checkresults = array();

if(submitcheck('valuesubmit')) {

	if($_POST['newpassword2'] !== $_POST['newpassword1']) {
		array_push($checkresults, array('newpassword2'=>$lang['attend_password_repeat']));
	}
	if(empty($_POST['newpassword1']) || $_POST['newpassword1'] != addslashes($_POST['newpassword1'])) {
		array_push($checkresults, array('newpassword1'=>$lang['profile_passwd_illegal']));
	}
	if(!empty($checkresults)) {
		cpmsg('modifypasswd_error', '', 'error', '', true, true, $checkresults);
	}
	require_once(B_ROOT.'./uc_client/client.php');
	$ucresult = uc_user_edit($_G['username'], $_POST['password'], $_POST['newpassword1']);
	if($ucresult == -1) {
		array_push($checkresults, array('password'=>$lang['old_password_invalid']));
	} elseif($ucresult == -7) {
		array_push($checkresults, array('message'=>$lang['no_change']));
	} elseif($ucresult == -8) {
		array_push($checkresults, array('message'=>$lang['protection_of_users']));
	}
	if(!empty($checkresults)) {
		cpmsg('modifypasswd_error', '', 'error', '', true, true, $checkresults);
	}
	sclearcookie();
	cpmsg('getpasswd_succeed', 'index.php', 'succeed');
}

shownav('shop', 'nav_modifypasswd');
showsubmenu('nav_modifypasswd');
showtips('modifypasswd_list_tips');
showformheader('modifypasswd');
showtableheader('');
$required = '<span style="color:red">*</span>';
showsetting('modifypasswd_passwd', 'password', '', 'password', '', '', '', '', $required);
showsetting('modifypasswd_newpasswd1', 'newpassword1', '', 'password', '', '', '', '', $required);
showsetting('modifypasswd_newpasswd2', 'newpassword2', '', 'password', '', '', '', '', $required);
showsubmit('valuesubmit');
showtablefooter();
showformfooter();
bind_ajax_form();
exit;
?>