<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_ajax.php 17421 2010-10-18 14:09:19Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if($_G['gp_action'] == 'checkusername') {


	$username = trim($_G['gp_username']);
	loaducenter();
	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('profile_username_illegal');
	} elseif($ucresult == -2) {
		showmessage('profile_username_protect');
	} elseif($ucresult == -3) {
		if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$username'")) {
			showmessage('register_check_found');
		} else {
			showmessage('register_activation');
		}
	}

} elseif($_G['gp_action'] == 'checkemail') {

	$email = trim($_G['gp_email']);
	loaducenter();
	$ucresult = uc_user_checkemail($email);

	if($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	}

} elseif($_G['gp_action'] == 'checkinvitecode') {

	$invitecode = trim($_G['gp_invitecode']);
	if(!$invitecode) {
		showmessage('no_invitation_code');
	}
	$result = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE code='$invitecode'");
	if($invite = DB::fetch($query)) {
		if(empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
			$result['uid'] = $invite['uid'];
			$result['id'] = $invite['id'];
			$result['appid'] = $invite['appid'];
		}
	}
	if(empty($result)) {
		showmessage('wrong_invitation_code');
	}

} elseif($_G['gp_action'] == 'checkuserexists') {

	$check = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".trim($_G['gp_username'])."'");
	$check ? showmessage('<img src="'.$_G['style']['imgdir'].'/check_right.gif" width="13" height="13">', '', array(), array('msgtype' => 3))
		: showmessage('username_nonexistence', '', array(), array('msgtype' => 3));

} elseif($_G['gp_action'] == 'attachlist') {

	require_once libfile('function/post');
	loadcache('groupreadaccess');
	$attachlist = getattach($_G['gp_pid'], intval($_G['gp_posttime']));
	$attachlist = $attachlist['attachs']['unused'];
	$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

	include template('common/header_ajax');
	include template('forum/ajax_attachlist');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'imagelist') {

	require_once libfile('function/post');
	$attachlist = getattach($_G['gp_pid'], intval($_G['gp_posttime']));
	$imagelist = $attachlist['imgattachs']['unused'];

	include template('common/header_ajax');
	include template('forum/ajax_imagelist');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'secondgroup') {

	require_once libfile('function/group');
	$groupselect = get_groupselect($_G['gp_fupid'], $_G['gp_groupid']);
	include template('common/header_ajax');
	include template('forum/ajax_secondgroup');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'displaysearch_adv') {
	$display = $_G['gp_display'] == 1 ? 1 : '';
	dsetcookie('displaysearch_adv', $display);
} elseif($_G['gp_action'] == 'checkgroupname') {
	$groupname = stripslashes(trim($_G['gp_groupname']));
	if(empty($groupname)) {
		showmessage('group_name_empty', '', array(), array('msgtype' => 3));
	}
	$tmpname = cutstr($groupname, 20, '');
	if($tmpname != $groupname) {
		showmessage('group_name_oversize', '', array(), array('msgtype' => 3));
	}
	if(DB::result_first("SELECT fid FROM ".DB::table('forum_forum')." WHERE name='".addslashes($groupname)."'")) {
		showmessage('group_name_exist', '', array(), array('msgtype' => 3));
	}
	showmessage('', '', array(), array('msgtype' => 3));
	include template('common/header_ajax');
	include template('common/footer_ajax');
	dexit();
} elseif($_G['gp_action'] == 'getthreadtypes') {
	include template('common/header_ajax');
	echo '<select name="threadtypeid">';
	if(!empty($_G['forum']['threadtypes']['types'])) {
		if(!$_G['forum']['threadtypes']['required']) {
			echo '<option value="0"></option>';
		}
		foreach($_G['forum']['threadtypes']['types'] as $typeid => $typename) {
			echo '<option value="'.$typeid.'">'.$typename.'</option>';
		}
	} else {
		echo '<option value="0" /></option>';
	}
	echo '</select>';
	include template('common/footer_ajax');
}
showmessage($_G['setting']['reglinkname']);

?>