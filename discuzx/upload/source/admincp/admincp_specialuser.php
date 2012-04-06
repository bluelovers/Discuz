<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_specialuser.php 19831 2011-01-19 07:54:16Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}

cpheader();

$operation = in_array($_G['gp_operation'], array('hotuser', 'defaultuser')) ? trim($_G['gp_operation']) : '';
$suboperation = in_array($_G['gp_suboperation'], array('adduser', 'specialuser')) ? trim($_G['gp_suboperation']) : '';
$status = ($operation == 'defaultuser') ? 1 : 0;
$op = ($status == 1) ? 'defaultuser' : 'hotuser';
$url = 'specialuser&operation='.$op.'&suboperation=specialuser';

if($suboperation !== 'adduser') {
	if($_G['gp_do'] == 'edit') {
		$_G['gp_id'] = intval($_G['gp_id']);
		if(!submitcheck('editsubmit')) {
			$info = DB::fetch_first("SELECT * FROM ".DB::table('home_specialuser'). " WHERE uid='$_G[gp_uid]' AND status='$status'");
			shownav('user', 'nav_defaultuser');
			showsubmenu('edit');
			showformheader('specialuser&operation='.$op.'&do=edit&uid='.$info[uid], '', 'userforum');
			showtableheader();
			showsetting('reason', 'reason', $info['reason'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			if(!$_G['gp_reason']) {
				cpmsg('specialuser_'.$op.'_noreason_invalid', 'action=specialuser&operation='.$op, 'error');
			}
			$updatearr = array('reason' => $_G['gp_reason']);
			DB::update('home_specialuser', $updatearr,array('uid' => $_G['gp_uid'], 'status' => $status));
			cpmsg('specialuser_defaultuser_edit_succeed', 'action=specialuser&operation='.$op, 'succeed');
		}

	} elseif(!submitcheck('usersubmit')) {

		shownav('user', 'nav_'.$op);
		showsubmenu('nav_'.$op, array(
		array('nav_'.$op, 'specialuser&operation='.$op, $operation == $op ? 1 : 0),
		array('nav_add_'.$op, 'specialuser&operation='.$op.'&suboperation=adduser', $suboperation == 'adduser' ? 1 : 0),));
		showtips('specialuser_'.$op.'_tips');
		showformheader($url, '', 'userforum');
		showtableheader();
		$status ? showsubtitle(array('', 'specialuser_order', 'uid', 'username', 'reason', 'operator', 'time', ''))
				 : showsubtitle(array('', 'specialuser_order', 'uid', 'username', 'reason', 'operator', 'time', ''));
		$query = DB::query("SELECT * FROM ".DB::table('home_specialuser')." WHERE status='$status' ORDER BY displayorder LIMIT ".(($page - 1) * $_G['ppp']).",{$_G['ppp']} ");
		while($specialuser = DB::fetch($query)) {

			$specialuser['dateline'] = dgmdate($specialuser['dateline']);
			$arr = array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$specialuser[uid]\">",
				"<input type=\"text\" name=\"displayorder[$specialuser[uid]]\" value=\"$specialuser[displayorder]\" size=\"8\">",
				$specialuser['uid'],
				"<a href=\"home.php?mod=space&uid=$specialuser[uid]\" target=\"_blank\">$specialuser[username]</a>",
				$specialuser['reason'],
				"<a href=\"home.php?mod=space&uid=$specialuser[opuid]\" target=\"_blank\">$specialuser[opusername]</a>",
				$specialuser['dateline'],
				"<a href=\"".ADMINSCRIPT."?action=specialuser&operation=$op&do=edit&uid=$specialuser[uid]\" class=\"act\">".$lang['edit']."</a>"
				);
			showtablerow('', '', $arr);
		}
		$usercount = DB::result_first("SELECT count(*) FROM ".DB::table('home_specialuser')." WHERE status='$status'");
		$multi = multi($usercount, $_G['ppp'], $page, ADMINSCRIPT."?action=specialuser&operation=$op");
		showsubmit('usersubmit', 'submit', 'del', '', $multi);
		showtablefooter();
		showformfooter();

	} else {

		$ids = array();
		if(is_array($_G['gp_delete'])) {
			foreach($_G['gp_delete'] as $id) {
				$ids[] = $id;
			}
			if($ids) {
				DB::query("DELETE FROM ".DB::table('home_specialuser')." WHERE uid IN (".dimplode($ids).") AND status='$status'");
				cpmsg('specialuser_'.$op.'_del_succeed', 'action='.$url, 'succeed');
			}
		}

		if(is_array($_G['gp_displayorder'])) {
			foreach($_G['gp_displayorder'] as $id => $val) {
				$updatearr = array('displayorder' => intval($_G['gp_displayorder'][$id]));
				DB::update('home_specialuser', $updatearr,array('uid' => $id, 'status' => $status));
			}
		}
		cpmsg('specialuser_'.$op.'_edit_succeed', 'action='.$url, 'succeed');
	}

} elseif($suboperation == 'adduser') {

		if(!submitcheck('addsubmit')) {

			shownav('user', 'nav_'.$op);
			showsubmenu('nav_'.$op, array(
			array('nav_'.$op, $url, $suboperation == 'specialuser' ? 1 : 0),
			array('nav_add_'.$op, 'specialuser&operation='.$op.'&suboperation=adduser', $suboperation == 'adduser' ? 1 : 0),));
			showtips('specialuser_defaultuser_add_tips');
			showformheader('specialuser&operation='.$op.'&suboperation=adduser', '', 'userforum');
			showtableheader();
			showsetting('username', 'username', '', 'text');
			showsetting('reason', 'reason', '', 'text');
			showsubmit('addsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$username = trim($_G['gp_username']);
			$reason = trim($_G['gp_reason']);

			if(!$username || !$reason) {
				cpmsg('specialuser_'.$op.'_add_invaild', '', 'error');
			}

			if(DB::result_first("SELECT count(*) FROM ".DB::table('home_specialuser')." WHERE status='$status' AND username='$username'")) {
				cpmsg('specialuser_'.$op.'_added_invalid', '', 'error');
			}

			$result = DB::result_first("SELECT count(*) FROM ".DB::table('common_member')." WHERE username='$username'");
			if(!$result) {
				cpmsg('specialuser_'.$op.'_nouser_invalid', '', 'error');
			}

			$result = DB::fetch_first("SELECT username, uid FROM ".DB::table('common_member')." WHERE username='$username'");
			$newusername = daddslashes($result['username']);
			$newuid = $result['uid'];
			$data = array(
				'status' => $status,
				'uid' => $newuid,
				'username' => $newusername,
				'reason' => $reason,
				'dateline' => $_G['timestamp'],
				'opuid' => $_G['member']['uid'],
				'opusername' => $_G['member']['username']
			);

			if(DB::insert('home_specialuser', $data)) {
				cpmsg('specialuser_'.$op.'_add_succeed', 'action='.$url, 'succeed');
			}
		}
}
?>