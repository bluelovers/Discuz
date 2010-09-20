<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modifypasswd.inc.php 4442 2010-09-14 09:43:34Z yumiao $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$checkresults = array();
$itemid = intval($_REQUEST['itemid']);

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
	$username = DB::result_first('SELECT username FROM '.tname('shopitems')." WHERE itemid='$itemid'");
	require_once(B_ROOT.'./uc_client/client.php');
	$ucresult = uc_user_edit($username, $_POST['newpassword1'], $_POST['newpassword1'], '', 1);
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
	cpmsg('admin_getpasswd_succeed', $BASESCRIPT.'?action=edit&m=shop&itemid='.$itemid, 'succeed');
}

$shopname = DB::result_first('SELECT subject FROM '.tname('shopitems')." WHERE itemid='$itemid'");

shownav('shop', 'nav_modifypasswd', $shopname);
$shopmenu = array(
	array('shop_edit', 'edit&m=shop&itemid='.$_GET['itemid'], 0),
	array('menu_shop_theme', 'theme&m=shop&itemid='.$_GET['itemid'], 0),
	array('menu_modifypasswd', 'modifypasswd&m=shop&itemid='.$_GET['itemid'], 1)
);
if($_G['setting']['enablemap'] == 1) {
	array_push($shopmenu, array('menu_shop_map', 'map&m=shop&itemid='.$_GET['itemid']));
}
showsubmenu('nav_modifypasswd', $shopmenu);
showtips('modifypasswd_admin_list_tips');
showformheader('modifypasswd');
showtableheader('');
$required = '<span style="color:red">*</span>';
showsetting('modifypasswd_shopname', 'shopname', $shopname, 'p');
showsetting('modifypasswd_newpasswd1', 'newpassword1', '', 'password', '', '', '', '', $required);
showsetting('modifypasswd_newpasswd2', 'newpassword2', '', 'password', '', '', '', '', $required);
showhiddenfields(array('itemid' => $itemid));
showsubmit('valuesubmit');
showtablefooter();
showformfooter();
bind_ajax_form();
exit;
?>