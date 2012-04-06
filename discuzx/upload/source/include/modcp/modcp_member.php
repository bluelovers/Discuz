<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_member.php 22321 2011-04-29 09:42:42Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

if($op == 'edit') {

	$_G['gp_uid'] = isset($_G['gp_uid']) ? intval($_G['gp_uid']) : '';
	$_G['gp_username'] = isset($_G['gp_username']) ? trim($_G['gp_username']) : '';

	$member = loadmember($_G['gp_uid'], $_G['gp_username'], $error);
	$usernameenc = $member ? rawurlencode($member['username']) : '';

	if($member && submitcheck('editsubmit') && !$error) {

		$sql = 'uid=uid';
		if($_G['group']['allowedituser']) {

			if(!empty($_G['gp_clearavatar'])) {
				loaducenter();
				uc_user_deleteavatar($member['uid']);
			}

			require_once libfile('function/discuzcode');

			if($_G['gp_bionew']) {
				$biohtmlnew = nl2br(dhtmlspecialchars($_G['gp_bionew']));
			} else {
				$biohtmlnew = '';
			}

			if($_G['gp_signaturenew']) {
				$signaturenew = censor($_G['gp_signaturenew']);
				$sightmlnew = addslashes(discuzcode(dstripslashes($signaturenew), 1, 0, 0, 0, $member['allowsigbbcode'], $member['allowsigimgcode'], 0, 0, 1));
			} else {
				$sightmlnew = $signaturenew = '';
			}

			!empty($_G['gp_locationnew']) && $locationnew = dhtmlspecialchars($_G['gp_locationnew']);

			$sql .= ', sigstatus=\''.($signaturenew ? 1 : 0).'\'';
			DB::query("UPDATE ".DB::table('common_member_profile')." SET bio='$biohtmlnew' WHERE uid='$member[uid]'");
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET sightml='$sightmlnew' WHERE uid='$member[uid]'");
		}
		acpmsg('members_edit_succeed', "$cpscript?mod=modcp&action=$_G[gp_action]&op=$op");

	} elseif($member) {

		require_once libfile('function/editor');
		$bio = explode("\t\t\t", $member['bio']);
		$member['bio'] = html2bbcode($bio[0]);
		$member['biotrade'] = !empty($bio[1]) ? html2bbcode($bio[1]) : '';
		$member['signature'] = html2bbcode($member['sightml']);
		$username = !empty($_G['gp_username']) ? $member['username'] : '';

	}

} elseif($op == 'ban' && ($_G['group']['allowbanuser'] || $_G['group']['allowbanvisituser'])) {

	$_G['gp_uid'] = isset($_G['gp_uid']) ? intval($_G['gp_uid']) : '';
	$_G['gp_username'] = isset($_G['gp_username']) ? trim($_G['gp_username']) : '';
	$member = loadmember($_G['gp_uid'], $_G['gp_username'], $error);
	$usernameenc = $member ? rawurlencode($member['username']) : '';

	if($member && submitcheck('bansubmit') && !$error) {
		$sql = 'uid=uid';
		$reason = trim($_G['gp_reason']);
		if(!$reason && ($_G['group']['reasonpm'] == 1 || $_G['group']['reasonpm'] == 3)) {
			acpmsg('admin_reason_invalid');
		}

		if($_G['gp_bannew'] == 4 || $_G['gp_bannew'] == 5) {
			if($_G['gp_bannew'] == 4 && !$_G['group']['allowbanuser'] || $_G['gp_bannew'] == 5 && !$_G['group']['allowbanvisituser']) {
				acpmsg('admin_nopermission');
			}
			$groupidnew = $_G['gp_bannew'];
			$banexpirynew = !empty($_G['gp_banexpirynew']) ? TIMESTAMP + $_G['gp_banexpirynew'] * 86400 : 0;
			$banexpirynew = $banexpirynew > TIMESTAMP ? $banexpirynew : 0;
			if($banexpirynew) {
				$member['groupterms'] = $member['groupterms'] && is_array($member['groupterms']) ? $member['groupterms'] : array();
				$member['groupterms']['main'] = array('time' => $banexpirynew, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
				$member['groupterms']['ext'][$groupidnew] = $banexpirynew;
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$sql .= ', groupexpiry=0';
			}
			$adminidnew = -1;
			DB::delete('forum_postcomment', "authorid='$member[uid]' AND rpid>'0'");
		} elseif($member['groupid'] == 4 || $member['groupid'] == 5) {
			if(!empty($member['groupterms']['main']['groupid'])) {
				$groupidnew = $member['groupterms']['main']['groupid'];
				$adminidnew = $member['groupterms']['main']['adminid'];
				unset($member['groupterms']['main']);
				unset($member['groupterms']['ext'][$member['groupid']]);
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$query = DB::query("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
				$groupidnew = DB::result($query, 0);
				$adminidnew = 0;
			}
		} else {
			$groupidnew = $member['groupid'];
			$adminidnew = $member['adminid'];
		}

		$sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
		DB::query("UPDATE ".DB::table('common_member')." SET $sql WHERE uid='$member[uid]'");
		$my_opt = in_array($groupidnew, array(4, 5)) ? 'banuser' : 'unbanuser';
		my_thread_log($my_opt, array('uid' => $member['uid']));

		if(DB::affected_rows()) {
			savebanlog($member['username'], $member['groupid'], $groupidnew, $banexpirynew, $reason);
		}

		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");
		if($_G['gp_bannew'] == 4) {
			$notearr = array(
				'user' => "<a href=\"home.php?mod=space&uid=$_G[uid]\">$_G[username]</a>",
				'day' => $_G['gp_banexpirynew'],
				'reason' => $reason
			);
			notification_add($member['uid'], 'system', 'member_ban_speak', $notearr, 1);
		}
		acpmsg('modcp_member_ban_succeed', "$cpscript?mod=modcp&action=$_G[gp_action]&op=$op");

	}

} elseif($op == 'ipban' && $_G['group']['allowbanip']) {

	require_once libfile('function/misc');
	$iptoban = getgpc('ip') ? dhtmlspecialchars(explode('.', getgpc('ip'))) : array('','','','');
	$updatecheck = $addcheck = $deletecheck = $adderror = 0;

	if(submitcheck('ipbansubmit')) {
		$_G['gp_delete'] = isset($_G['gp_delete']) ? $_G['gp_delete'] : '';
		if($ids = dimplode($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('common_banned')." WHERE id IN ($ids) AND ('$_G[adminid]'='1' OR admin='$_G[username]')");
			$deletecheck = DB::affected_rows();
		}
		if($_G['gp_ip1new'] != '' && $_G['gp_ip2new'] != '' && $_G['gp_ip3new'] != '' && $_G['gp_ip4new'] != '') {
			$addcheck = ipbanadd($_G['gp_ip1new'], $_G['gp_ip2new'], $_G['gp_ip3new'], $_G['gp_ip4new'], $_G['gp_validitynew'], $adderror);
			if(!$addcheck) {
				$iptoban = array($_G['gp_ip1new'], $_G['gp_ip2new'], $_G['gp_ip3new'], $_G['gp_ip4new']);
			}
		}

		if(!empty($_G['gp_expirationnew']) && is_array($_G['gp_expirationnew'])) {
			foreach($_G['gp_expirationnew'] as $id => $expiration) {
				if($expiration == intval($expiration)) {
					$expiration = $expiration > 1 ? (TIMESTAMP + $expiration * 86400) : TIMESTAMP + 86400;
					DB::query("UPDATE ".DB::table('common_banned')." SET expiration='$expiration' WHERE id='$id' AND ('$_G[adminid]'='1' OR admin='$_G[username]')");
					empty($updatecheck) && $updatecheck = DB::affected_rows();
				}
			}
		}

		if($deletecheck || $addcheck || $updatecheck) {
			require_once(libfile('function/cache'));
			updatecache('ipbanned');
		}

	}

	$iplist = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_banned')." ORDER BY dateline");
	while($banned = DB::fetch($query)) {
		for($i = 1; $i <= 4; $i++) {
			if($banned["ip$i"] == -1) {
				$banned["ip$i"] = '*';
			}
		}
		$banned['disabled'] = $_G['adminid'] != 1 && $banned['admin'] != $_G['member']['username'] ? 'disabled' : '';
		$banned['dateline'] = dgmdate($banned['dateline'], 'd');
		$banned['expiration'] = dgmdate($banned['expiration'], 'd');
		$banned['theip'] = "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4]";
		$banned['location'] = convertip($banned['theip']);
		$iplist[$banned['id']] = $banned;
	}

} else {
	showmessage('undefined_action');
}

function loadmember(&$uid, &$username, &$error) {
	global $_G;

	$uid = !empty($_G['gp_uid']) && is_numeric($_G['gp_uid']) && $_G['gp_uid'] > 0 ? $_G['gp_uid'] : '';
	$username = isset($_G['gp_username']) && $_G['gp_username'] != '' ? dhtmlspecialchars(trim($_G['gp_username'])) : '';

	$member = array();

	if($uid || $username != '') {

		$query = DB::query("SELECT m.uid, m.username, m.groupid, m.adminid, mf.groupterms, mp.bio, mf.sightml, u.type AS grouptype, uf.allowsigbbcode, uf.allowsigimgcode, m.credits FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_forum')." mf ON mf.uid=m.uid
			LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=m.groupid
			LEFT JOIN ".DB::table('common_member_profile')." mp ON mp.uid=m.uid
			LEFT JOIN ".DB::table('common_usergroup_field')." uf ON uf.groupid=m.groupid
			WHERE ".($uid ? "m.uid='$uid'" : "m.username='$username'"));

		if(!$member = DB::fetch($query)) {
			$error = 2;
		} elseif(($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || in_array($member['adminid'], array(1,2,3))) {
			$error = 3;
		} else {
			$member['groupterms'] = unserialize($member['groupterms']);
			$member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? dgmdate($member['groupterms']['main']['time'], 'Y-n-j') : '';
			$error = 0;
		}

	} else {
		$error = 1;
	}

	return $member;
}

function ipbanadd($ip1new, $ip2new, $ip3new, $ip4new, $validitynew, &$error) {
	global $_G;

	if($ip1new != '' && $ip2new != '' && $ip3new != '' && $ip4new != '') {
		$own = 0;
		$ip = explode('.', $_G['clientip']);
		for($i = 1; $i <= 4; $i++) {

			if(!is_numeric(${'ip'.$i.'new'}) || ${'ip'.$i.'new'} < 0) {
				if($_G['adminid'] != 1) {
					$error = 1;
					return FALSE;
				}
				${'ip'.$i.'new'} = -1;
				$own++;
			} elseif(${'ip'.$i.'new'} == $ip[$i - 1]) {
				$own++;
			}
			${'ip'.$i.'new'} = intval(${'ip'.$i.'new'}) > 255 ? 255 : intval(${'ip'.$i.'new'});
		}

		if($own == 4) {
			$error = 2;
			return FALSE;
		}

		$query = DB::query("SELECT * FROM ".DB::table('common_banned')." WHERE (ip1='$ip1new' OR ip1='-1') AND (ip2='$ip2new' OR ip2='-1') AND (ip3='$ip3new' OR ip3='-1') AND (ip4='$ip4new' OR ip4='-1')");
		if($banned = DB::fetch($query)) {
			$error = 3;
			return FALSE;
		}

		$expiration = $validitynew > 1 ? (TIMESTAMP + $validitynew * 86400) : TIMESTAMP + 86400;

		DB::query("UPDATE ".DB::table('common_session')." SET groupid='6' WHERE ('$ip1new'='-1' OR ip1='$ip1new') AND ('$ip2new'='-1' OR ip2='$ip2new') AND ('$ip3new'='-1' OR ip3='$ip3new') AND ('$ip4new'='-1' OR ip4='$ip4new')");
		DB::query("INSERT INTO ".DB::table('common_banned')." (ip1, ip2, ip3, ip4, admin, dateline, expiration)
				VALUES ('$ip1new', '$ip2new', '$ip3new', '$ip4new', '$_G[username]', '$_G[timestamp]', '$expiration')");

		return TRUE;

	}

	return FALSE;

}

?>