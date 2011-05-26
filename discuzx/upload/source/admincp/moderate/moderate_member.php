<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_member.php 20814 2011-03-04 08:03:12Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$do = empty($do) ? 'mod' : $do;

if($do == 'mod') {

	if(!submitcheck('modsubmit')) {
		$query = DB::query("SELECT status, COUNT(*) AS count FROM ".DB::table('common_member_validate')." GROUP BY status");
		while($num = DB::fetch($query)) {
			$count[$num['status']] = $num['count'];
		}

		$sendemail = isset($_G['gp_sendemail']) ? $_G['gp_sendemail'] : 0;
		$checksendemail = $sendemail ? 'checked' : '';

		$start_limit = ($page - 1) * $_G['setting']['memberperpage'];

		$validatenum = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_validate')." WHERE status='0'"), 0);
		$members = '';
		if($validatenum) {
			$multipage = multi($validatenum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT.'?action=moderate&operation=members&sendemail='.$sendemail);
			$vuids = '0';
			loadcache('fields_register');
			require_once libfile('function/profile');
			$query = DB::query("SELECT mp.*, mvi.field, m.uid, m.username, m.groupid, m.email, m.regdate, ms.regip, v.message, v.submittimes, v.submitdate, v.moddate, v.admin, v.remark, v.uid as vuid
				FROM ".DB::table('common_member_validate')." v
				LEFT JOIN ".DB::table('common_member')." m ON v.uid=m.uid
				LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid
				LEFT JOIN ".DB::table('common_member_profile')." mp ON m.uid=mp.uid
				LEFT JOIN ".DB::table('common_member_verify_info')." mvi ON mvi.uid=m.uid AND mvi.verifytype='0'
				WHERE v.status='0' ORDER BY v.submitdate DESC LIMIT $start_limit, ".$_G['setting']['memberperpage']);
			while($member = DB::fetch($query)) {
				if($member['groupid'] != 8) {
					$vuids .= ','.$member['vuid'];
					continue;
				}

				$fields = !empty($member['field']) ? unserialize($member['field']) : array();
				$str = '';
				foreach($_G['cache']['fields_register'] as $field) {
					if(!$field['available'] || in_array($field['fieldid'], array('uid', 'constellation', 'zodiac', 'birthmonth', 'birthyear', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
						continue;
					}
					$member[$field['fieldid']] = !empty($member[$field['fieldid']]) ? $member[$field['fieldid']] : $fields[$field['fieldid']];
					if($member[$field['fieldid']]) {
						$fieldstr = profile_show($field['fieldid'], $member);
						$str .= $field['title'].':'.$fieldstr."<br/>";
					}
				}
				$str = !empty($str) ? '<br/>'.$str : '';
				$member['regdate'] = dgmdate($member['regdate']);
				$member['submitdate'] = dgmdate($member['submitdate']);
				$member['moddate'] = $member['moddate'] ? dgmdate($member['moddate']) : $lang['none'];
				$member['admin'] = $member['admin'] ? "<a href=\"home.php?mod=space&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
				$members .= "<tr class=\"hover\" id=\"mod_uid_{$member[uid]}\"><td class=\"rowform\" style=\"width:80px;\"><ul class=\"nofloat\"><li><input id=\"mod_uid_{$member[uid]}_1\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"invalidate\" onclick=\"set_bg('invalidate', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_1\">$lang[invalidate]</label></li><li><input id=\"mod_uid_{$member[uid]}_2\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"validate\" onclick=\"set_bg('validate', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_2\">$lang[validate]</label></li>\n".
					"<li><input id=\"mod_uid_{$member[uid]}_3\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"delete\" onclick=\"set_bg('delete', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_3\">$lang[delete]</label></li><li><input id=\"mod_uid_{$member[uid]}_4\" class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"ignore\" onclick=\"set_bg('ignore', $member[uid]);\"><label for=\"mod_uid_{$member[uid]}_4\">$lang[ignore]</label></li></ul></td><td><b><a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a></b>\n".
					"<br />$lang[members_edit_regdate] $member[regdate]<br />$lang[members_edit_regip] $member[regip] ".convertip($member['regip'])."<br />Email: $member[email]$str</td>\n".
					"<td align=\"center\"><textarea rows=\"4\" name=\"userremark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[message]</textarea></td>\n".
					"<td>$lang[moderate_members_submit_times]: $member[submittimes]<br />$lang[moderate_members_submit_time]: $member[submitdate]<br />$lang[moderate_members_admin]: $member[admin]<br />\n".
					"$lang[moderate_members_mod_time]: $member[moddate]</td><td><textarea rows=\"4\" id=\"remark[$member[uid]]\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[remark]</textarea></td></tr>\n";
			}
			if($vuids) {
				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($vuids)", 'UNBUFFERED');
			}
		}
		shownav('user', 'nav_modmembers');
		showsubmenu('nav_moderate_users', array(
			array('nav_moderate_users_mod', 'moderate&operation=members&do=mod', 1),
			array('clean', 'moderate&operation=members&do=del', 0)
		));
		showtips('moderate_members_tips');
		$moderate_members_bad_reason = cplang('moderate_members_bad_reason');
		$moderate_members_succeed = cplang('moderate_members_succeed');
		echo <<<EOT
<script type="text/javascript">
function set_bg(operation, uid) {
	if(operation == 'invalidate') {
		$('mod_uid_' + uid).className = "mod_invalidate";
		$('remark[' + uid + ']').value = '$moderate_members_bad_reason';
	} else if(operation == 'validate') {
		$('mod_uid_' + uid).className = "mod_validate";
		$('remark[' + uid + ']').value = '$moderate_members_succeed';
	} else if(operation == 'ignore') {
		$('mod_uid_' + uid).className = "mod_ignore";
		$('remark[' + uid + ']').value = '';
	} else if(operation == 'delete') {
		$('mod_uid_' + uid).className = "mod_delete";
		$('remark[' + uid + ']').value = '';
	}
	$('chk_apply_all').disabled = true;
	$('chk_apply_all').checked = false;
}
function set_bg_all(operation) {
	var trs = $('cpform').getElementsByTagName('TR');
	for(var i in trs) {
		if(trs[i].id && trs[i].id.substr(0, 8) == 'mod_uid_') {
			uid = trs[i].id.substr(8);
			if(operation == 'invalidate') {
				trs[i].className = 'mod_invalidate';
				$('remark[' + uid + ']').value = '$moderate_members_bad_reason';
			} else if(operation == 'validate') {
				trs[i].className = 'mod_validate';
				$('remark[' + uid + ']').value = '$moderate_members_succeed';
			} else if(operation == 'ignore') {
				trs[i].className = 'mod_ignore';
				$('remark[' + uid + ']').value = '';
			} else if(operation == 'delete') {
				trs[i].className = 'mod_delete';
				$('remark[' + uid + ']').value = '';
			}else if(operation == 'cancel') {
				trs[i].className = '';
				$('remark[' + uid + ']').value = '';
			}
		}
	}
	if(operation != 'cancel') {
		$('chk_apply_all').disabled = false;
		$('chk_apply_all').value = operation;
	} else {
		$('chk_apply_all').disabled = true;
		$('chk_apply_all').checked = false;
	}


}
function cancelallcheck() {
	var form = $('cpform');
	var checkall = 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.type == 'radio') {
			e.checked = '';
		}
	}
}
</script>
EOT;
		showformheader('moderate&operation=members&do=mod');
		showtableheader('moderate_members', 'fixpadding');
		showsubtitle(array('operation', 'members_edit_info', 'moderate_members_message', 'moderate_members_info', 'moderate_members_remark'));
		echo $members;
		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'invalidate\');set_bg_all(\'invalidate\');">'.cplang('moderate_all_invalidate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'validate\');set_bg_all(\'validate\');">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'delete\');set_bg_all(\'delete\');">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'ignore\');set_bg_all(\'ignore\');">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="cancelallcheck();set_bg_all(\'cancel\');">'.cplang('moderate_all_cancel').'</a><input class="checkbox" type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').' &nbsp;<input class="checkbox" type="checkbox" name="sendemail" id="sendemail" value="1" '.$checksendemail.' /><label for="sendemail"> '.cplang('moderate_members_email').'</label>', $multipage);
		showtablefooter();
		showformfooter();

	} else {

		$moderation = array('invalidate' => array(), 'validate' => array(), 'delete' => array(), 'ignore' => array());

		$uids = 0;
		$uidsql = '';
		if(!$_G['gp_apply_all']) {
			if(is_array($_G['gp_modtype'])) {
				foreach($_G['gp_modtype'] as $uid => $act) {
					$uid = intval($uid);
					$uids .= ','.$uid;
					$moderation[$act][] = $uid;
				}
				$uidsql = "v.uid IN ($uids) AND";
			}
		}

		$members = array();
		$uidarray = array(0);
		$query = DB::query("SELECT v.*, m.uid, m.username, m.email, m.regdate FROM ".DB::table('common_member_validate')." v, ".DB::table('common_member')." m
			WHERE $uidsql m.uid=v.uid AND m.groupid='8'");
		while($member = DB::fetch($query)) {
			$members[$member['uid']] = $member;
			$uidarray[] = $member['uid'];
			if($_G['gp_apply_all']) {
				$uids .= ','.$member['uid'];
				$moderation[$_G[gp_apply_all]][] = $member['uid'];
			}
		}
		if(is_array($uidarray) && !empty($uidarray)) {
			$uids = implode(',', $uidarray);
			$numdeleted = $numinvalidated = $numvalidated = 0;

			if(!empty($moderation['delete']) && is_array($moderation['delete'])) {
				$deleteuids = '\''.implode('\',\'', $moderation['delete']).'\'';
				DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($deleteuids) AND uid IN ($uids)");
				$numdeleted = DB::affected_rows();

				DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($deleteuids) AND uid IN ($uids)");
				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($deleteuids) AND uid IN ($uids)");

				loaducenter();
				uc_user_delete($moderation['delete']);
			} else {
				$moderation['delete'] = array();
			}

			if(!empty($moderation['validate']) && is_array($moderation['validate'])) {
				$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE creditshigher<=0 AND 0<creditslower LIMIT 1");
				$validateuids = '\''.implode('\',\'', $moderation['validate']).'\'';
				DB::query("UPDATE ".DB::table('common_member')." SET adminid='0', groupid='$newgroupid' WHERE uid IN ($validateuids) AND uid IN ($uids)");
				$numvalidated = DB::affected_rows();

				DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($validateuids) AND uid IN ($uids)");
			} else {
				$moderation['validate'] = array();
			}

			if(!empty($moderation['invalidate']) && is_array($moderation['invalidate'])) {
				foreach($moderation['invalidate'] as $uid) {
					$numinvalidated++;
					DB::query("UPDATE ".DB::table('common_member_validate')." SET moddate='$_G[timestamp]', admin='$_G[username]', status='1', remark='".dhtmlspecialchars($_G['gp_remark'][$uid])."' WHERE uid='$uid' AND uid IN ($uids)");
				}
			} else {
				$moderation['invalidate'] = array();
			}

			foreach(array('validate', 'invalidate') as $o) {
				foreach($moderation[$o] as $uid) {
					if($_G['gp_remark'][$uid]) {
						switch($o) {
							case 'validate':
								notification_add($uid, 'mod_member', 'member_moderate_validate', array('remark' => $_G['gp_remark'][$uid]));
								break;
							case 'invalidate':
								notification_add($uid, 'mod_member', 'member_moderate_invalidate', array('remark' => $_G['gp_remark'][$uid]));
								break;
						}
					} else {
						switch($o) {
							case 'validate':
								notification_add($uid, 'mod_member', 'member_moderate_validate_no_remark');
								break;
							case 'invalidate':
								notification_add($uid, 'mod_member', 'member_moderate_invalidate_no_remark');
								break;
						}
					}
				}
			}

			if($_G['gp_sendemail']) {
				if(!function_exists('sendmail')) {
					include libfile('function/mail');
				}
				foreach(array('delete', 'validate', 'invalidate') as $o) {
					foreach($moderation[$o] as $uid) {
						if(isset($members[$uid])) {
							$member = $members[$uid];
							$member['regdate'] = dgmdate($member['regdate']);
							$member['submitdate'] = dgmdate($member['submitdate']);
							$member['moddate'] = dgmdate(TIMESTAMP);
							$member['operation'] = $o;
							$member['remark'] = $_G['gp_remark'][$uid] ? dhtmlspecialchars($_G['gp_remark'][$uid]) : $lang['none'];
							$moderate_member_message = lang('email', 'moderate_member_message', array(
								'username' => $member['username'],
								'bbname' => $_G['setting']['bbname'],
								'regdate' => $member['regdate'],
								'submitdate' => $member['submitdate'],
								'submittimes' => $member['submittimes'],
								'message' => $member['message'],
								'modresult' => lang('email', 'moderate_member_'.$member['operation']),
								'moddate' => $member['moddate'],
								'adminusername' => $_G['member']['username'],
								'remark' => $member['remark'],
								'siteurl' => $_G['siteurl'],
							));

							sendmail("$member[username] <$member[email]>", lang('email', 'moderate_member_subject'), $moderate_member_message);
						}
					}
				}
			}
		}
		cpmsg('moderate_members_op_succeed', "action=moderate&operation=members&page=$page", 'succeed', array('numvalidated' => $numvalidated, 'numinvalidated' => $numinvalidated, 'numdeleted' => $numdeleted));

	}

} elseif($do == 'del') {

	if(!submitcheck('prunesubmit', 1)) {

		shownav('user', 'nav_modmembers');
		showsubmenu('nav_moderate_users', array(
			array('nav_moderate_users_mod', 'moderate&operation=members&do=mod', 0),
			array('clean', 'moderate&operation=members&do=del', 1)
		));
		showtips('moderate_members_tips');
		showformheader('moderate&operation=members&do=del');
		showtableheader('moderate_members_prune');
		showsetting('moderate_members_prune_submitmore', 'submitmore', '5', 'text');
		showsetting('moderate_members_prune_regbefore', 'regbefore', '30', 'text');
		showsetting('moderate_members_prune_modbefore', 'modbefore', '15', 'text');
		showsetting('moderate_members_prune_regip', 'regip', '', 'text');
		showsubmit('prunesubmit');
		showtablefooter();
		showformfooter();

	} else {

		$sql = "m.groupid='8'";
		$sql .= $_G['gp_submitmore'] ? " AND v.submittimes>'{$_G['gp_submitmore']}'" : '';
		$sql .= $_G['gp_regbefore'] ? " AND m.regdate<'".(TIMESTAMP - $_G['gp_regbefore'] * 86400)."'" : '';
		$sql .= $_G['gp_modbefore'] ? " AND v.moddate<'".(TIMESTAMP - $_G['gp_modbefore'] * 86400)."'" : '';
		$sql .= $_G['gp_regip'] ? " AND m.regip LIKE '{$_G['gp_regip']}%'" : '';

		$query = DB::query("SELECT v.uid FROM ".DB::table('common_member_validate')." v, ".DB::table('common_member')." m
			WHERE $sql AND m.uid=v.uid");

		if(!$membernum = DB::num_rows($query)) {
			cpmsg('members_search_noresults', '', 'error');
		} elseif(!$_G['gp_confirmed']) {
			cpmsg('members_delete_confirm', "action=moderate&operation=members&do=del&submitmore=".rawurlencode($_G['gp_submitmore'])."&regbefore=".rawurlencode($_G['gp_regbefore'])."&regip=".rawurlencode($_G['gp_regip'])."&prunesubmit=yes", 'form', array('membernum' => $membernum));
		} else {
			$uids = 0;
			while($member = DB::fetch($query)) {
				$uids .= ','.$member['uid'];
			}

			DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
			$numdeleted = DB::affected_rows();

			DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($uids)");
			DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)");

			cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));
		}

	}

}

?>