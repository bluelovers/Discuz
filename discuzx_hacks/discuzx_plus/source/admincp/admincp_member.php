<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_member.php 646 2010-09-13 03:37:40Z yexinhao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$usergrouplist = $modulelist = array();
$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')."");
while($group = DB::fetch($query)) {
	$usergrouplist[$group['groupid']] = $group['grouptitle'];
}

$query = DB::query("SELECT * FROM ".DB::table('common_module')." WHERE available='1' ORDER BY displayorder");
while($module = DB::fetch($query)) {
	if($module['type'] != 1) {
		$modulelist[$module['mid']] = $module['name'];
	}
}

$operation = in_array($operation, array('list', 'add', 'edit', 'ban')) ? $operation : 'list';

if($operation == 'list') {

	if(!submitcheck('membersubmit')) {
		shownav('global', 'nav_member');
		showsubmenu('nav_member', array(
			array('member_list', 'member&operation=list', 1),
			array('member_add', 'member&operation=add', 0),
		));
		showformheader('member');
		showtableheader('member_list', 'fixpadding');
		showsubtitle(array('', 'username', 'usergroup', 'email', 'regdate', ''));

		$where = !empty($where) ? $where : 1;
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE $where");

		$perpage = max(5, empty($_G['gp_perpage']) ? 50 : intval($_G['gp_perpage']));
		$start_limit = ($page - 1) * $perpage;
		$mpurl = ADMINSCRIPT."?action=member&operation=list";

		$multipage = multi($num, $perpage, $page, $mpurl);

		$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE $where ORDER BY uid LIMIT $start_limit, $perpage");
		while($member = DB::fetch($query)) {
			$disable = isfounder($member) ? 'disabled="true"': '';
			showtablerow('', array('', '', '', '', '', '', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$member[uid]\" $disable>",
				$member['username'],
				$usergrouplist[$member['groupid']],
				$member['email'],
				dgmdate($member['regdate'], 'Y-n-j H:i'),
				"<a href=\"".ADMINSCRIPT."?action=member&operation=edit&uid=$member[uid]\" class=\"act\">$lang[detail]</a>",
			));
		}

		showsubmit('membersubmit', 'submit', 'del', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_usergroupidnew'])) {
			foreach($_G['gp_usergroupidnew'] as $uid => $groupid) {
				$adminid = $groupid == 1 ? 1 : 0;
				DB::query("UPDATE ".DB::table('common_member')." SET adminid='".intval($adminid)."', groupid='".intval($groupid)."' WHERE uid='$uid'");
			}
		}

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
		}

		cpmsg('member_succeed', 'action=member&operation=list', 'succeed');

	}

} elseif($operation == 'add') {

	if(!submitcheck('addsubmit')) {

		shownav('global', 'nav_member');
		showsubmenu('member_add', array(
			array('member_list', 'member&operation=list', 0),
			array('member_add', 'member&operation=add', 1),
		));
		showformheader('member&operation=add');
		showtableheader();
		showsetting('username', 'newusername', '', 'text');
		showsetting('password', 'newpassword', '', 'text');
		showsetting('email', 'newemail', '', 'text');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newusername = trim($_G['gp_newusername']);
		$newpassword = trim($_G['gp_newpassword']);
		$newemail = trim($_G['gp_newemail']);

		if(!$newusername || !$newpassword || !$newemail) {
			cpmsg('members_add_invalid', '', 'error');
		}

		if(DB::result_first("SELECT count(*) FROM ".DB::table('common_member')." WHERE username='$newusername'")) {
			cpmsg('members_add_username_duplicate', '', 'error');
		}

		loaducenter();

		$uid = uc_user_register($newusername, $newpassword, $newemail);
		if($uid <= 0) {
			if($uid == -1) {
				cpmsg('members_add_illegal', '', 'error');
			} elseif($uid == -2) {
				cpmsg('members_username_protect', '', 'error');
			} elseif($uid == -3) {
				cpmsg('members_add_username_activation', '', 'error');
			} elseif($uid == -4) {
				cpmsg('members_email_illegal', '', 'error');
			} elseif($uid == -5) {
				cpmsg('members_email_domain_illegal', '', 'error');
			} elseif($uid == -6) {
				cpmsg('members_email_duplicate', '', 'error');
			} else {
				cpmsg('undefined_action', '', 'error');
			}
		}

		$data = array(
			'uid' => $uid,
			'username' => $newusername,
			'password' => md5(random(10)),
			'email' => $newemail,
			'adminid' => 0,
			'groupid' => 2,
			'regdate' => $_G['timestamp'],
			'credits' => 0,
		);
		DB::insert('common_member', $data);
		DB::insert('common_member_status', array('uid' => $uid, 'regip' => 'Manual Acting', 'lastvisit' => $_G['timestamp'], 'lastactivity' => $_G['timestamp']));
		cpmsg('members_add_succeed', '', 'succeed', array('username' => $newusername, 'uid' => $uid));

	}

} elseif($operation == 'edit') {

	$_G['gp_uid'] = intval($_G['gp_uid']);
	if(empty($_G['gp_uid']) && empty($_G['gp_username'])) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}

	$member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')."	WHERE uid='$_G[gp_uid]'");
	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}

	$uid = $member['uid'];

	$memberstatus = DB::fetch_first("SELECT * FROM ".DB::table('common_member_status')." WHERE uid='$_G[gp_uid]'");


	if(!submitcheck('editsubmit')) {

		$member['regdate'] = dgmdate($member['regdate'], 'Y-n-j h:i A');
		$member['lastvisit'] = dgmdate($member['lastvisit'], 'Y-n-j h:i A');

		shownav('user', 'member_edit');
		showsubmenu("$lang[member_edit] - $member[username]");
		showformheader("member&operation=edit&uid=$uid");
		showtableheader();
		$status = array($member['status'] => ' checked');
		showsetting('username', '', '', ' '.$member['username']);
		showsetting('avatar', '', '', ' '.avatar($uid).'<br /><br /><input name="clearavatar" class="checkbox" type="checkbox" value="1" /> '.$lang['avatar_clear']);
		showsetting('password', 'passwordnew', '', 'text');
		showsetting('email', 'emailnew', $member['email'], 'text');
		//showsetting('usergroup', '', '', (isfounder($member) ? $usergrouplist[$member['groupid']] : selectgroup($usergrouplist, $member['groupid'], $member['uid'])));
		showsetting('regip', 'regipnew', $memberstatus['regip'], 'text');
		showsetting('regdate', 'regdatenew', $member['regdate'], 'text');
		showsetting('lastip', 'lastipnew', $memberstatus['lastip'], 'text');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		loaducenter();

		$questionid = $_G['gp_clearquestion'] ? 0 : '';
		$ucresult = uc_user_edit($member['username'], $_G['gp_passwordnew'], $_G['gp_passwordnew'], $_G['gp_emailnew'], 1, $questionid);

		if($_G['gp_clearavatar']) {
			uc_user_deleteavatar($member['uid']);
		}

		$regdatenew = strtotime($_G['gp_regdatenew']);
		$lastvisitnew = strtotime($_G['gp_lastvisitnew']);
		$emailnew = $_G['gp_emailnew'] ? dhtmlspecialchars(trim($_G['gp_emailnew'])) : '';

		$emailadd = $ucresult < 0 ? '' : "email='$emailnew', ";
		$passwordadd = $ucresult < 0 ? '' : ", password='".md5(random(10))."'";

		$usergroupadd = '';
		/*
		if(!empty($_G['gp_usergroupidnew'][$_G['gp_uid']])) {
			$usergroupidnew = intval($_G['gp_usergroupidnew'][$_G['gp_uid']]);
			$usergroupadd = $usergroupidnew == 1 ? " ,adminid='1' ,groupid='$usergroupidnew'" : " ,groupid='$usergroupidnew'";
		}
		*/

		DB::query("UPDATE ".DB::table('common_member')." SET $emailadd regdate='$regdatenew', status='$status' $usergroupadd $passwordadd WHERE uid='{$_G['gp_uid']}'");
		DB::query("UPDATE ".DB::table('common_member_status')." SET regip='{$_G['gp_regipnew']}', lastip='{$_G['gp_lastipnew']}' WHERE uid='{$_G['gp_uid']}'");

		cpmsg('members_edit_succeed', 'action=member&operation=edit&uid='.$uid, 'succeed');

	}

}

function selectgroup($usergrouplist, $membergroupid, $uid) {
	$usergroupselect = '<select name="usergroupidnew['.$uid.']">';
	if($usergrouplist) {
		foreach($usergrouplist as $groupid => $group) {
			$selected = $membergroupid == $groupid  ? 'selected="selected"' : '';
			$usergroupselect .= '<option value="'.$groupid.'" '.$selected.'>'.$group.'</option>';
		}
	}
	$usergroupselect .= '</select>';

	return $usergroupselect;
}

?>