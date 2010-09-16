<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_moderate.php 16515 2010-09-08 01:52:08Z wangjinbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}

cpheader();

$ignore = $_G['gp_ignore'];
$filter = $_G['gp_filter'];
$modfid = $_G['gp_modfid'];
$modsubmit = $_G['gp_modsubmit'];
$moderate = $_G['gp_moderate'];
$pm = $_G['gp_pm'];

$_G['setting']['memberperpage'] = 100;
if(empty($operation)) {
	$operation = 'threads';
}
if($operation == 'members') {

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
				$multipage = multi($validatenum, $_G['setting']['memberperpage'], $page, 'action=moderate&operation=members&sendemail=$sendemail');
				$vuids = '0';
				$query = DB::query("SELECT m.uid, m.username, m.groupid, m.email, m.regdate, ms.regip, v.message, v.submittimes, v.submitdate, v.moddate, v.admin, v.remark
					FROM ".DB::table('common_member_validate')." v
					LEFT JOIN ".DB::table('common_member')." m ON v.uid=m.uid
					LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid
					WHERE v.status='0' ORDER BY v.submitdate DESC LIMIT $start_limit, ".$_G['setting']['memberperpage']);
				while($member = DB::fetch($query)) {
					if($member['groupid'] != 8) {
						$vuids .= ','.$member['uid'];
						continue;
					}
					$member['regdate'] = dgmdate($member['regdate']);
					$member['submitdate'] = dgmdate($member['submitdate']);
					$member['moddate'] = $member['moddate'] ? dgmdate($member['moddate']) : $lang['none'];
					$member['admin'] = $member['admin'] ? "<a href=\"home.php?mod=space&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
					$members .= "<tr class=\"smalltxt mod_validate\" id=\"mod_uid_{$member[uid]}\"><td><input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"invalidate\" onclick=\"set_bg('invalidate', $member[uid]);\"> $lang[invalidate]<br /><input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"validate\" onclick=\"set_bg('validate', $member[uid]);\" checked> $lang[validate]<br />\n".
						"<input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"delete\" onclick=\"set_bg('delete', $member[uid]);\"> $lang[delete]<br /><input class=\"radio\" type=\"radio\" name=\"modtype[$member[uid]]\" value=\"ignore\" onclick=\"set_bg('ignore', $member[uid]);\"> $lang[ignore]</td><td><b><a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a></b>\n".
						"<br />$lang[members_edit_regdate] $member[regdate]<br />$lang[members_edit_regip] $member[regip]<br />Email: $member[email]</td>\n".
						"<td align=\"center\"><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[message]</textarea></td>\n".
						"<td>$lang[moderate_members_submit_times]: $member[submittimes]<br />$lang[moderate_members_submit_time]: $member[submitdate]<br />$lang[moderate_members_admin]: $member[admin]<br />\n".
						"$lang[moderate_members_mod_time]: $member[moddate]</td><td><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[remark]</textarea></td></tr>\n";
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
			echo <<<EOT
<style type="text/css">
	.mod_validate td{ background: #CCFFCC !important; }
	.mod_invalidate td{	background: #FFEBE7 !important; }
	.mod_ignore td{	background: #EEEEEE !important; }
	.mod_cancel td{ background: #FFFFFF !important; }
	.mod_delete td{ background: #FF7B30 !important; }
</style>
<script type="text/javascript">
	function set_bg(operation, uid) {
		if(operation == 'invalidate') {
			$('mod_uid_' + uid).className = "mod_invalidate";
		} else if(operation == 'validate') {
			$('mod_uid_' + uid).className = "mod_validate";
		} else if(operation == 'ignore') {
			$('mod_uid_' + uid).className = "mod_ignore";
		} else if(operation == 'delete') {
			$('mod_uid_' + uid).className = "mod_delete";
		}
	}
	function set_bg_all(operation) {
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.substr(0, 8) == 'mod_uid_') {
				if(operation == 'invalidate') {
					trs[i].className = 'mod_invalidate';
				} else if(operation == 'validate') {
					trs[i].className = 'mod_validate';
				} else if(operation == 'ignore') {
					trs[i].className = 'mod_ignore';
				} else if(operation == 'delete') {
					trs[i].className = 'mod_delete';
				}
			}
		}
	}
</script>
EOT;
			showformheader('moderate&operation=members&do=mod');
			showtableheader('moderate_members', 'fixpadding');
			showsubtitle(array('operation', 'members_edit_info', 'moderate_members_message', 'moderate_members_info', 'moderate_members_remark'));
			echo $members;
			showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'invalidate\');set_bg_all(\'invalidate\');">'.cplang('moderate_all_invalidate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'validate\');set_bg_all(\'validate\');">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'delete\');set_bg_all(\'delete\');">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="checkAll(\'option\', $(\'cpform\'), \'ignore\');set_bg_all(\'ignore\');">'.cplang('moderate_all_ignore').'</a> &nbsp;<input class="checkbox" type="checkbox" name="sendemail" id="sendemail" value="1" '.$checksendemail.' /><label for="sendemail"> '.cplang('moderate_members_email').'</label>', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			$moderation = array('invalidate' => array(), 'validate' => array(), 'delete' => array(), 'ignore' => array());

			$uids = 0;
			if(is_array($_G['gp_modtype'])) {
				foreach($_G['gp_modtype'] as $uid => $act) {
					$uid = intval($uid);
					$uids .= ','.$uid;
					$moderation[$act][] = $uid;
				}
			}

			$members = array();
			$uidarray = array(0);
			$query = DB::query("SELECT v.*, m.uid, m.username, m.email, m.regdate FROM ".DB::table('common_member_validate')." v, ".DB::table('common_member')." m
				WHERE v.uid IN ($uids) AND m.uid=v.uid AND m.groupid='8'");
			while($member = DB::fetch($query)) {
				$members[$member['uid']] = $member;
				$uidarray[] = $member['uid'];
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
			cpmsg('moderate_members_succeed', "action=moderate&operation=members&page=$page", 'succeed', array('numvalidated' => $numvalidated, 'numinvalidated' => $numinvalidated, 'numdeleted' => $numdeleted));

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

} else {

	require_once libfile('function/forumlist');
	require_once libfile('function/post');

	$modfid = !empty($modfid) ? intval($modfid) : 0;

	$fids = 0;
	$recyclebins = $forumlist = array();

		$query = DB::query("SELECT fid, name, recyclebin FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group'");
		while($forum = DB::fetch($query)) {
			$recyclebins[$forum['fid']] = $forum['recyclebin'];
			$forumlist[$forum['fid']] = $forum['name'];
		}

	if($modfid && $modfid != '-1') {
		$fidadd = array('fids' => "fid='$modfid'", 'and' => ' AND ', 't' => 't.', 'p' => 'p.');
	} else {
		$fidadd = $fids ? array('fids' => "fid IN ($fids)", 'and' => ' AND ', 't' => 't.', 'p' => 'p.') : array();
	}

	if(isset($filter) && $filter == 'ignore') {
		$displayorder = -3;
		$filteroptions = '<option value="normal">'.$lang['moderate_none'].'</option><option value="ignore" selected>'.$lang['moderate_ignore'].'</option>';
	} else {
		$displayorder = -2;
		$filter = 'normal';
		$filteroptions = '<option value="normal" selected>'.$lang['moderate_none'].'</option><option value="ignore">'.$lang['moderate_ignore'].'</option>';
	}

	$forumoptions = '<option value="all"'.(empty($modfid) ? ' selected' : '').'>'.$lang['moderate_all_fields'].'</option>';
	$forumoptions .= '<option value="-1" '.($modfid == '-1' ? 'selected' : '').'>'.$lang['moderate_all_groups'].'</option>'."\n";
	foreach($forumlist as $fid => $forumname) {
		$selected = $modfid == $fid ? ' selected' : '';
		$forumoptions .= '<option value="'.$fid.'" '.$selected.'>'.$forumname.'</option>'."\n";
	}

	require_once libfile('function/misc');
	$modreasonoptions = '<option value="">'.$lang['none'].'</option><option value="">--------</option>'.modreasonselect(1);

	echo <<<EOT
<style type="text/css">
	.mod_validate td{ background: #CCFFCC !important; }
	.mod_delete td{	background: #FFEBE7 !important; }
	.mod_ignore td{	background: #EEEEEE !important; }
	.mod_cancel td{ background: #FFFFFF !important; }
</style>
<script type="text/JavaScript">
	var cookiepre = "{$_G[config][cookie][cookiepre]}";
	function mod_setbg(tid, value) {
		$('mod_' + tid + '_row1').className = 'mod_' + value;
		$('mod_' + tid + '_row2').className = 'mod_' + value;
		$('mod_' + tid + '_row3').className = 'mod_' + value;
		$("chk_apply_all").checked = false;
		$("chk_apply_all").disabled = true;
	}
	function mod_setbg_all(value) {
		checkAll('option', $('cpform'), value);
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.substr(0, 4) == 'mod_') {
				trs[i].className = 'mod_' + value;
			}
		}
		$("chk_apply_all").disabled = false;
		$("chk_apply_all").value = value;
	}
	function attachimg() {}
	function expandall() {
		var tds = $('cpform').getElementsByTagName('TD');
		for(var i in tds) {
			if(tds[i].id && tds[i].id.match(/^mod_(\d+)_row1_op$/) != null) {
				tds[i].rowSpan = "3";
			}
		}
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row1$/) != null) {
				tds = trs[i].getElementsByTagName('TD');
				for(var j in tds) {
					if(tds[j].className == "threadtitle threadopt") {
						tds[j].className = "";
					}
				}
			}
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row(2|3)$/) != null) {
				trs[i].style.display = "";
			}
		}
		setcookie("foldall", 0, 3600);
	}

	function foldall() {
		var tds = $('cpform').getElementsByTagName('TD');
		for(var i in tds) {
			if(tds[i].id && tds[i].id.match(/^mod_(\d+)_row1_op$/) != null) {
				tds[i].rowSpan = "1";
			}
		}
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row1$/) != null) {
				tds = trs[i].getElementsByTagName('TD');
				for(var j in tds) {
					if(tds[j].className == "") {
						tds[j].className = "threadtitle threadopt";
					}
				}
			}
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row(2|3)$/) != null) {
				trs[i].style.display = "none";
			}
		}
		setcookie("foldall", 1, 3600);
	}

	function display_toggle(tid) {
		var tr1 = $('mod_' + tid + '_row1');
		var tr1_op = $('mod_' + tid + '_row1_op');
		var tr2 = $('mod_' + tid + '_row2');
		var tr3 = $('mod_' + tid + '_row3');
		var tds = tr1.getElementsByTagName('TD');
		if(tr1_op.rowSpan == "1") {
			for(var i in tds) {
				if(tds[i].className == "threadtitle threadopt") {
					tds[i].className = "";
				}
			}
			tr1_op.rowSpan = "3";
			tr2.style.display = "";
			tr3.style.display = "";
		} else {
			for(var i in tds) {
				if(tds[i].className == "") {
					tds[i].className = "threadtitle threadopt";
				}
			}
			tr1_op.rowSpan = "1";
			tr2.style.display = "none";
			tr3.style.display = "none";
		}
	}

	function mod_cancel_all() {
		var inputs = $('cpform').getElementsByTagName('input');
		for(var i in inputs) {
			if(inputs[i].type == 'radio') {
				inputs[i].checked = '';
			}
		}
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row(1|2|3)$/)) {
				trs[i].className = "mod_cancel";
			}
		}
		$("chk_apply_all").checked = false;
		$("chk_apply_all").disabled = true;
	}

	function remove_element(_element) {
		var _parentElement = _element.parentNode;
		if(_parentElement){
			_parentElement.removeChild(_element);
		}
	}

	function mod_remove_row(id) {
		var id1 = "mod_" + id + "_row1";
		var id2 = "mod_" + id + "_row2";
		var id3 = "mod_" + id + "_row3";
		var node1 = parent.document.getElementById(id1);
		var node2 = parent.document.getElementById(id2);
		var node3 = parent.document.getElementById(id3);
		remove_element(node1);
		remove_element(node2);
		remove_element(node3);
	}

	window.onload = function() {
		if(getcookie("foldall")) {
			foldall();
		}
	};
</script>
EOT;

}

function callback_js($id) {
	$js = <<<EOT
<script type="text/javascript">
	mod_remove_row('$id');
</script>
EOT;
	return $js;
}
if($operation == 'threads') {

	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

		require_once libfile('function/discuzcode');

		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $tpp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}

		shownav('topic', $lang['moderate_threads']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 1),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));

		showformheader("moderate&operation=threads");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		<select name=\"modfid\" style=\"margin: 0px;\">$forumoptions</select>
		<select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_title_keyword]: <input size=\"15\" name=\"title\" type=\"text\" value=\"$_G[gp_title]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();
		showtableheader();

		$sqlwhere = '';
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND t.author='{$_G['gp_username']}'";
		}
		if(!empty($_G['gp_title'])) {
			$title = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_title']);
			$sqlwhere .= " AND t.subject LIKE '%{$title}%'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND t.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		$modcount = getcountofposts(DB::table('forum_thread')." t INNER JOIN ".DB::table('forum_post')." p ON p.tid=t.tid AND p.first='1'", "$fidadd[t]$fidadd[fids]$fidadd[and] displayorder='$displayorder'".($modfid == '-1' ? " AND isgroup='1'": '')."$sqlwhere");

		$start_limit = ($page - 1) * $tpp;
		$threadlist = getallwithposts(array(
			'select' => 'f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode, t.tid, t.fid, t.posttableid, t.sortid, t.authorid, t.author, t.subject, t.dateline, p.pid, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff',
			'from' => DB::table('forum_thread')." t INNER JOIN ".DB::table('forum_post')." p ON p.tid=t.tid AND p.first='1' LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid",
			'where' => "$fidadd[t]$fidadd[fids]$fidadd[and] t.displayorder='$displayorder'".($modfid == '-1' ? " AND t.isgroup='1'": '')." $sqlwhere",
			'order' => 't.dateline DESC',
			'limit' => "$start_limit, $tpp",
		));
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=threads&filter=$filter&modfid=$modfid&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp=$tpp");



		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> &nbsp;<a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a><p>';

		require_once libfile('function/misc');
		foreach($threadlist as $thread) {
			$threadsortinfo = '';
			$thread['useip'] = $thread['useip'] . '-' . convertip($thread['useip']);
			if($thread['authorid'] && $thread['author']) {
				$thread['author'] = "<a href=\"?action=members&operation=search&uid=$thread[authorid]&submit=yes\" target=\"_blank\">$thread[author]</a>";
			} elseif($thread['authorid'] && !$thread['author']) {
				$thread['author'] = "<a href=\"?action=members&operation=search&uid=$thread[authorid]&submit=yes\" target=\"_blank\">$lang[anonymous]</a>";
			} else {
				$thread['author'] = $lang['guest'];
			}

			$thread['dateline'] = dgmdate($thread['dateline']);
			$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff']);
			require_once libfile('class/censor');
			$censor = & discuz_censor::instance();
			$censor->highlight = '#FF0000';

			$censor->check($thread['subject']);
			$censor->check($thread['message']);
			$thread['modthreadkey'] = modauthkey($thread['tid']);
			$censor_words = $censor->words_found;
			if(count($censor_words) > 3) {
				$censor_words = array_slice($censor_words, 0, 3);
			}
			$thread['censorwords'] = implode(', ', $censor_words);

			if($thread['attachment']) {
				require_once libfile('function/attachment');

				$queryattach = DB::query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM ".DB::table('forum_attachment')." WHERE tid='$thread[tid]'");
				while($attach = DB::fetch($queryattach)) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
							? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
							 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$thread['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}

			$optiondata = $optionlist = array();
			if($thread['sortid']) {
				if(@include DISCUZ_ROOT.'./data/cache/forum_threadsort_'.$thread['sortid'].'.php') {
					$sortquery = DB::query("SELECT optionid, value FROM ".DB::table('forum_typeoptionvar')." WHERE tid='$thread[tid]'");
					while($option = DB::fetch($sortquery)) {
						$optiondata[$option['optionid']] = $option['value'];
					}

					foreach($_G['forum_dtype'] as $optionid => $option) {
						$optionlist[$option['identifier']]['title'] = $_G['forum_dtype'][$optionid]['title'];
						if($_G['forum_dtype'][$optionid]['type'] == 'checkbox') {
							$optionlist[$option['identifier']]['value'] = '';
							foreach(explode("\t", $optiondata[$optionid]) as $choiceid) {
								$optionlist[$option['identifier']]['value'] .= $_G['forum_dtype'][$optionid]['choices'][$choiceid].'&nbsp;';
							}
						} elseif(in_array($_G['forum_dtype'][$optionid]['type'], array('radio', 'select'))) {
							$optionlist[$option['identifier']]['value'] = $_G['forum_dtype'][$optionid]['choices'][$optiondata[$optionid]];
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'image') {
							$maxwidth = $_G['forum_dtype'][$optionid]['maxwidth'] ? 'width="'.$_G['forum_dtype'][$optionid]['maxwidth'].'"' : '';
							$maxheight = $_G['forum_dtype'][$optionid]['maxheight'] ? 'height="'.$_G['forum_dtype'][$optionid]['maxheight'].'"' : '';
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\"><img src=\"$optiondata[$optionid]\"  $maxwidth $maxheight border=\"0\"></a>" : '';
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'url') {
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\">$optiondata[$optionid]</a>" : '';
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'textarea') {
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? nl2br($optiondata[$optionid]) : '';
						} else {
							$optionlist[$option['identifier']]['value'] = $optiondata[$optionid];
						}
					}
				}

				foreach($optionlist as $option) {
					$threadsortinfo .= $option['title'].' '.$option['value']."<br />";
				}
			}

			if(count($censor_words)) {
				$thread_censor_text = "<span style=\"color: red;\">($thread[censorwords])</span>";
			} else {
				$thread_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$thread[tid]_row1\"", array("id=\"mod_$thread[tid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"validate\" onclick=\"mod_setbg($thread[tid], 'validate');\"><label for=\"mod_$thread[tid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"delete\" onclick=\"mod_setbg($thread[tid], 'delete');\"><label for=\"mod_$thread[tid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" onclick=\"mod_setbg($thread[tid], 'ignore');\"><label for=\"mod_$thread[tid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$thread[tid]');\">$thread[subject]</a> $thread_censor_text</h3><p>$thread[useip]</p>",
				"<a target=\"_blank\" href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\">$thread[forumname]</a>",
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$thread[authorid]&submit=yes\">$thread[author]</a></p> <p>$thread[dateline]</p>",
				"<a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=$thread[tid]&modthreadkey=$thread[modthreadkey]\">$lang[view]</a>&nbsp;<a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">$lang[edit]</a>",
			));
			showtablerow("id=\"mod_$thread[tid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$thread['message'].'<br /><br />'.$threadsortinfo.'</div>');
			showtablerow("id=\"mod_$thread[tid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a> | <a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&page=1&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">".$lang['moderate_edit_thread']."</a> &nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$thread[tid]\" id=\"pm_$thread[tid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$thread[tid]').value=this.value\">$modreasonoptions</select>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a> &nbsp;<label><input type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').'</label>', $multipage, false);
		showtablefooter();
		showformfooter();

	} else {

		$validates = $ignores = $recycles = $deletes = 0;
		$validatedthreads = $pmlist = array();
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());

		if(is_array($moderate)) {
			foreach($moderate as $tid => $act) {
				$moderation[$act][] = intval($tid);
			}
		}

		if($_G['gp_apply_all']) {
			$apply_all_action = $_G['gp_apply_all'];
			$sqlwhere = '1';
			if($modfid > 0) {
				$sqlwhere .= " AND fid='$modfid'";
			}
			if($filter == 'ignore') {
				$sqlwhere .= " AND displayorder='-3'";
			} else {
				$sqlwhere .= " AND displayorder='-2'";
			}
			if($modfid == -1) {
				$sqlwhere .= " AND isgroup='1'";
			}
			if(!empty($_G['gp_dateline']) && $_G['gp_dateline'] != 'all') {
				$sqlwhere .= " AND dateline>'{$_G['gp_dateline']}'";
			}
			if(!empty($_G['gp_username'])) {
				$sqlwhere .= " AND author='{$_G['gp_username']}'";
			}
			if(!empty($_G['gp_title'])) {
				$title = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_title']);
				$sqlwhere .= " AND subject LIKE '%{$title}%'";
			}
			$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE $sqlwhere");
			while($thread = DB::fetch($query)) {
				switch($apply_all_action) {
					case 'validate':
						$moderation['validate'][] = $thread['tid'];
						break;
					case 'delete':
						$moderation['delete'][] = $thread['tid'];
						break;
					case 'ignore':
						$moderation['ignore'][] = $thread['tid'];
						break;
				}
			}
		}

		if($moderation['ignore']) {
			$ignoretids = '\''.implode('\',\'', $moderation['ignore']).'\'';
			DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-3' WHERE tid IN ($ignoretids) AND displayorder='-2'");
			$ignores = DB::affected_rows();
		}

		if($moderation['delete']) {
			$deletetids = '0';
			$recyclebintids = '0';
			if(!empty($moderation['delete'])) {
				$deletetids = "'" . implode("','", $moderation['delete']) . "'";
			}
			$query = DB::query("SELECT tid, fid, authorid, subject FROM ".DB::table('forum_thread')." t WHERE t.tid IN ($deletetids) AND displayorder='$displayorder' $fidadd[and]$fidadd[fids]");
			while($thread = DB::fetch($query)) {
				my_thread_log('delete', array('tid' => $thread['tid']));
				if($recyclebins[$thread['fid']]) {
					$recyclebintids .= ','.$thread['tid'];
				}
				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
						'action' => 'modthreads_delete',
						'notevar' => array('threadsubject' => $threadsubject, 'reason' => stripslashes($reason)),
						'authorid' => $thread['authorid'],
						'thread' =>  $thread['subject'],
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($recyclebintids) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-1', moderated='1' WHERE tid IN ($recyclebintids)");
				$recycles = DB::affected_rows();
				updatemodworks('MOD', $recycles);

				updatepost(array('invisible' => '-1'), "tid IN ($recyclebintids)");
				updatemodlog($recyclebintids, 'DEL');
			}

			require_once libfile('function/delete');
			deletepost("tid IN ($deletetids)");
			$deletes = deletethread("tid IN ($deletetids)");
		}

		if($moderation['validate']) {
			require_once libfile('function/forum');
			$forums = array();
			$validatetids = '\''.implode('\',\'', $moderation['validate']).'\'';

			$tids = $comma = $comma2 = '';
			$authoridarray = $moderatedthread = array();
			$query = DB::query("SELECT t.fid, t.tid, t.authorid, t.subject, t.author, t.dateline, t.posttableid FROM ".DB::table('forum_thread')." t
				WHERE t.tid IN ($validatetids) $fidadd[and]$fidadd[t]$fidadd[fids]");
			while($thread = DB::fetch($query)) {
				$posttable = $thread['posttableid'] ? "forum_post_{$thread['posttableid']}" : 'forum_post';
				$poststatus = DB::result_first("SELECT status FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				my_thread_log('validate', array('tid' => $thread['tid']));
				if(getstatus($poststatus, 3) == 0) {
					updatepostcredits('+', $thread['authorid'], 'post', $thread['fid']);
				}

				$forums[] = $thread['fid'];
				$validatedthreads[] = $thread;

				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
							'action' => 'modthreads_validate',
							'notevar' => array('tid' => $_G['tid'], 'threadsubject' => $threadsubject, 'reason' => stripslashes($reason)),
							'authorid' => $thread['authorid'],
							'tid' => $thread['tid'],
							'thread' => $thread['subject'],
							'reason' => dhtmlspecialchars($$pm)
							);
				}
			}

			if($tids) {

				updatepost(array('invisible' => '0'), "tid IN ($tids) AND first='1'");
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
				$validates = DB::affected_rows();

				foreach(array_unique($forums) as $fid) {
					updateforumcount($fid);
				}

				updatemodworks('MOD', $validates);
				updatemodlog($tids, 'MOD');

			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$threadsubject = $pm['thread'];
				$tid = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['action'], $pm['notvar'], 1);
			}
		}
		if($_G['gp_fast']) {
			echo callback_js($_G['gp_tid']);
			exit;
		} else {
			cpmsg('moderate_threads_succeed', "action=moderate&operation=threads&page=$page&filter=$filter&modfid=$modfid", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
		}

	}

} elseif($operation == 'replies') {


	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

		require_once libfile('function/discuzcode');
		$select[$_G['gp_ppp']] = $_G['gp_ppp'] ? "selected='selected'" : '';
		$ppp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$ppp = !empty($_G['gp_ppp']) ? $_G['gp_ppp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}

		shownav('topic', $lang['moderate_replies']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 1),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));

		showtableheader('search');
		showtablerow('', array(''), array("<form id=\"form_search\" action=\"".ADMINSCRIPT."?action=moderate&operation=replies\" method=\"post\"><select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		<select name=\"modfid\" style=\"margin: 0px;\">$forumoptions</select>
		<select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_title_keyword]: <input size=\"15\" name=\"title\" type=\"text\" value=\"$_G[gp_title]\" />
		<select name=\"ppp\" style=\"margin: 0px;\">$ppp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" /></form>"));
		showtablefooter();

		showformheader("moderate&operation=replies");
		showhiddenfields(array('filter' => $filter, 'modfid' => $modfid));
		showtableheader();

		$sqlwhere = '';
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND p.author='{$_G['gp_username']}'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND p.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_title'])) {
			$sqlwhere .= " AND t.subject LIKE '%{$_G['gp_title']}%'";
		}
		if($modfid > 0) {
			$fidadd['and'] = ' AND';
			$fidadd['fids'] = " p.fid='$modfid'";
		}

		$modcount = getcountofposts(DB::table('forum_post').' p INNER JOIN '.DB::table('forum_thread').' t ON p.tid=t.tid', "p.invisible='$displayorder' AND p.first='0' $fidadd[and]$fidadd[fids]".($modfid == -1 ? " AND t.isgroup='1'" : '')." $sqlwhere");
		$start_limit = ($page - 1) * $ppp;
		$postarray = getallwithposts(array(
			'select' => 'f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode, p.pid, p.fid, p.tid, p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject',
			'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=p.fid",
			'where' => "p.invisible='$displayorder' AND p.first='0' $fidadd[and]$fidadd[fids]".($modfid ==-1 ? " AND t.isgroup='1'" : '')." $sqlwhere",
			'order' => 'p.dateline DESC',
			'limit' => "$start_limit, $ppp",
		));
		$multipage = multi($modcount, $ppp, $page, ADMINSCRIPT."?action=moderate&operation=replies&filter=$filter&modfid=$modfid&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&ppp=$ppp");





		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a><p>';





		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		foreach($postarray as $post) {
			$post['dateline'] = dgmdate($post['dateline']);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $post['allowsmilies'], $post['allowbbcode'], $post['allowimgcode'], $post['allowhtml']);
			$censor->check($post['tsubject']);
			$censor->check($post['message']);
			$post_censor_words = $censor->words_found;
			if(count($post_censor_words) > 3) {
				$post_censor_words = array_slice($post_censor_words, 0, 3);
			}
			$post['censorwords'] = implode(', ', $post_censor_words);
			$post['modthreadkey'] = modauthkey($post['tid']);
			$post['useip'] = $post['useip'] . '-' . convertip($post['useip']);

			if($post['attachment']) {
				require_once libfile('function/attachment');

				$queryattach = DB::query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM ".DB::table('forum_attachment')." WHERE pid='$post[pid]'");
				while($attach = DB::fetch($queryattach)) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
					 		? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
						 	 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}

			if(count($post_censor_words)) {
				$post_censor_text = "<span style=\"color: red;\">({$post['censorwords']})</span>";
			} else {
				$post_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$post[pid]_row1\"", array("id=\"mod_$post[pid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_1\" value=\"validate\" onclick=\"mod_setbg($post[pid], 'validate');\"><label for=\"mod_$post[pid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_2\" value=\"delete\" onclick=\"mod_setbg($post[pid], 'delete');\"><label for=\"mod_$post[pid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_3\" value=\"ignore\" onclick=\"mod_setbg($post[pid], 'ignore');\"><label for=\"mod_$post[pid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$post[pid]');\">$post[tsubject]</a> $post_censor_text</h3><p>$post[useip]</p>",
				"<a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\">$post[forumname]</a>",
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$post[authorid]&submit=yes\">$post[author]</a></p> <p>$post[dateline]</p>",
				"<a target=\"_blank\" href=\"forum.php?mod=redirect&goto=findpost&tid=$post[tid]&pid=$post[pid]\">$lang[view]</a>&nbsp;<a href=\"forum.php?mod=viewthread&tid=$post[tid]&modthreadkey=$post[modthreadkey]\" target=\"_blank\">$lang[edit]</a>",
			));
			showtablerow("id=\"mod_$post[pid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$post['message'].'</div>');
			showtablerow("id=\"mod_$post[pid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>&nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$post[pid]\" id=\"pm_$post[pid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$post[pid]').value=this.value\">$modreasonoptions</select>");
			showtagfooter('tbody');

		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a> &nbsp;<label><input type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').'</label>', $multipage, false);
		showtablefooter();
		showformfooter();

	} else {

		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$pmlist = array();
		$validates = $ignores = $deletes = 0;

		if(is_array($moderate)) {
			foreach($moderate as $pid => $act) {
				$moderation[$act][] = intval($pid);
			}
		}

		if($_G['gp_apply_all']) {
			$apply_all_action = $_G['gp_apply_all'];
			$sqlwhere = "p.first='0'";
			if($filter == 'ignore') {
				$sqlwhere .= " AND p.invisible='-3'";
			} else {
				$sqlwhere .= " AND p.invisible='-2'";
			}
			if($modfid > 0) {
				$sqlwhere .= " AND p.fid='$modfid'";
			}
			if($modfid == -1) {
				$sqlwhere .= " AND t.isgroup='1'";
			}
			if(!empty($_G['gp_dateline']) && $_G['gp_dateline'] != 'all') {
				$sqlwhere .= " AND p.dateline>'{$_G['gp_dateline']}";
			}
			if(!empty($_G['gp_username'])) {
				$sqlwhere .= " AND p.author='{$_G['gp_username']}'";
			}
			if(!empty($_G['gp_title'])) {
				$title = str_replace(array('_', '\%'), array('\_', '\%'), $_G['gp_title']);
				$sqlwhere .= " AND t.title LIKE '%{$title}%'";
			}
			$posts_array = getallwithposts(array(
				'select' => 'p.pid',
				'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON p.tid=t.tid",
				'where' => $sqlwhere,
			));
			foreach($posts_array as $post) {
				switch($apply_all_action) {
					case 'validate':
						$moderation['validate'][] = $post['pid'];
						break;
					case 'delete':
						$moderation['delete'][] = $post['pid'];
						break;
					case 'ignore':
						$moderation['ignore'][] = $post['pid'];
						break;
				}
			}
		}
		if($ignorepids = dimplode($moderation['ignore'])) {
			updatepost(array('invisible' => '-3'), "pid IN ($ignorepids) AND invisible='-2' AND first='0' $fidadd[and]$fidadd[fids]");
			$ignores = DB::affected_rows();
		}

		if($deletepids = dimplode($moderation['delete'])) {
			$postarray = getfieldsofposts('pid, authorid, tid, message', "pid IN ($deletepids) AND invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[fids]");
			$pids = $comma = '';
			foreach($postarray as $post) {
				$pids .= $comma.$post['pid'];
				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
						'action' => 'modreplies_delete',
						'notevar' => array('post' => $post, 'reason' => stripslashes($reason)),
						'authorid' => $post['authorid'],
						'tid' => $post['tid'],
						'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
						'reason' => dhtmlspecialchars($$pm)
					);
				}
				$comma = ',';
			}

			if($pids) {
				$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE pid IN ($deletepids)");
				while($attach = DB::fetch($query)) {
					dunlink($attach);
				}
				DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE pid IN ($pids)", 'UNBUFFERED');
				DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE pid IN ($pids)", 'UNBUFFERED');
				require_once libfile('function/delete');
				$deletes = deletepost("pid IN ($pids)");
				DB::query("DELETE FROM ".DB::table('forum_trade')." WHERE pid IN ($pids)", 'UNBUFFERED');
			}
			updatemodworks('DLP', count($moderation['delete']));
		}

		if($validatepids = dimplode($moderation['validate'])) {
			require_once libfile('function/forum');
			$forums = $threads = $lastpost = $attachments = $pidarray = $authoridarray = array();
			$postarray = getallwithposts(array(
				'select' => 't.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, p.message, p.anonymous, p.status',
				'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid",
				'where' => "pid IN ($validatepids)  AND first='0'"
			));
			foreach($postarray as $post) {
				$pidarray[] = $post['pid'];
				if(getstatus($post['status'], 3) == 0) {
					updatepostcredits('+', $post['authorid'], 'reply', $post['fid']);
				}

				$forums[] = $post['fid'];

				$threads[$post['tid']]['posts']++;
				$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
					", lastpost='$post[dateline]', lastposter='".($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]))."'" : '';
				$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';

				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
						'action' => 'modreplies_validate',
						'notevar' => array('tid' => $_G['tid'], 'post' => $post, 'reason' => stripslashes($reason)),
						'authorid' => $post['authorid'],
						'tid' => $post['tid'],
						'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			foreach($threads as $tid => $thread) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
			}

			foreach(array_unique($forums) as $fid) {
				updateforumcount($fid);
			}

			if(!empty($pidarray)) {
				$validates = updatepost(array('invisible' => '0'), "pid IN (0,".implode(',', $pidarray).")");
				updatemodworks('MOD', $validates);
			} else {
				updatemodworks('MOD', 1);
			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$post = $pm['post'];
				$tid = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['action'], $pm['notvar'], 1);
			}
		}
		if($_G['gp_fast']) {
			echo callback_js($_G['gp_pid']);
			exit;
		} else {
			cpmsg('moderate_replies_succeed', "action=moderate&operation=replies&page=$page&filter=$filter&modfid=$modfid", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
		}

	}

} elseif($operation == 'blogs') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_blogs']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 1),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));
		require_once libfile('function/discuzcode');
		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$blog_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$blog_status = 2;
		}
		showformheader("moderate&operation=blogs");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_title_keyword]: <input size=\"15\" name=\"title\" type=\"text\" value=\"$_G[gp_title]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "b.status='$blog_status'";
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND b.username='{$_G['gp_username']}'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND b.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_title'])) {
			$sqlwhere .= " AND b.subject LIKE '%{$_G['gp_title']}%'";
		}
		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('home_blog')." b
			LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
			LEFT JOIN ".DB::table('home_class')." c ON b.classid=c.classid
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT b.blogid, b.uid, b.username, b.classid, b.subject, b.dateline, bf.message, bf.postip, c.classname
				FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				LEFT JOIN ".DB::table('home_class')." c ON b.classid=c.classid
				WHERE $sqlwhere
				ORDER BY b.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=blogs&filter=$filter&modfid=$modfid&ppp=$tpp");





		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';




		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		while($blog = DB::fetch($query)) {
			$blog['dateline'] = dgmdate($blog['dateline']);
			$blog['subject'] = $blog['subject'] ? '<b>'.$blog['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$censor->check($blog['subject']);
			$censor->check($blog['message']);
			$blog_censor_words = $censor->words_found;
			if(count($post_censor_words) > 3) {
				$blog_censor_words = array_slice($blog_censor_words, 0, 3);
			}
			$blog['censorwords'] = implode(', ', $blog_censor_words);
			$blog['modblogkey'] = modauthkey($blog['blogid']);
			$blog['postip'] = $blog['postip'] . '-' . convertip($blog['postip']);

			if(count($blog_censor_words)) {
				$blog_censor_text = "<span style=\"color: red;\">({$blog['censorwords']})</span>";
			} else {
				$blog_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$blog[blogid]_row1\"", array("id=\"mod_$blog[blogid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_1\" value=\"validate\" onclick=\"mod_setbg($blog[blogid], 'validate');\"><label for=\"mod_$blog[blogid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_2\" value=\"delete\" onclick=\"mod_setbg($blog[blogid], 'delete');\"><label for=\"mod_$blog[blogid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_3\" value=\"ignore\" onclick=\"mod_setbg($blog[blogid], 'ignore');\"><label for=\"mod_$blog[blogid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$blog[blogid]');\">$blog[subject]</a> $blog_censor_text</h3><p>$blog[postip]</p>",
				$blog[classname],
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$blog[uid]&submit=yes\">$blog[username]</a></p> <p>$blog[dateline]</p>",
				"<a href=\"home.php?mod=space&do=blog&uid=$blog[uid]&id=$blog[blogid]&modblogkey=$blog[modblogkey]\" target=\"_blank\">$lang[view]</a>&nbsp;<a href=\"home.php?mod=spacecp&ac=blog&blogid=$blog[blogid]&modblogkey=$blog[modblogkey]\" target=\"_blank\">$lang[edit]</a>",
			));
			showtablerow("id=\"mod_$blog[blogid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$blog['message'].'</div>');
			showtablerow("id=\"mod_$blog[blogid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();
	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $blogid => $act) {
				$moderate[$act][] = $blogid;
			}
		}

		if($validate_blogids = dimplode($moderate['validate'])) {
			DB::update('home_blog', array('status' => '0'), "blogid IN ($validate_blogids)");
			$validates = DB::affected_rows();
			$query_t = DB::query("SELECT uid, COUNT(blogid) AS count
				FROM ".DB::table('home_blog')."
				WHERE blogid IN ($validate_blogids)
				GROUP BY uid");
			while($blog_user = DB::fetch($query_t)) {
				$credit_times = $blog_user['count'];
				updatecreditbyaction('publishblog', $blog_user['uid'], array('blogs' => 1), '', $credit_times);
			}
		}

		if($delete_blogids = dimplode($moderate['delete'])) {
			DB::delete('home_blog', "blogid IN ($delete_blogids)");
			$deletes = DB::affected_rows();
		}

		if($ignore_blogids = dimplode($moderate['ignore'])) {
			DB::update('home_blog', array('status' => '2'), "blogid IN ($ignore_blogids)");
			$ignores = DB::affected_rows();
		}

		if($_G['gp_fast']) {
			echo callback_js($_G['gp_blogid']);
			exit;
		} else {
			cpmsg('moderate_blogs_succeed', "action=moderate&operation=blogs&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
		}
	}
} elseif($operation == 'pictures') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_pictures']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 1),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));

		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $tpp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$pic_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$pic_status = 2;
		}
		showformheader("moderate&operation=pictures");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_title_keyword]: <input size=\"15\" name=\"title\" type=\"text\" value=\"$_G[gp_title]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "p.status='$pic_status'";
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND p.username='{$_G['gp_username']}'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND p.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_title'])) {
			$sqlwhere .= " AND p.title LIKE '%{$_G['gp_title']}%'";
		}
		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('home_pic')." p
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT p.picid, p.albumid, p.uid, p.username, p.title, p.dateline, p.filepath, p.thumb, p.remote, p.postip, a.albumname
				FROM ".DB::table('home_pic')." p
				LEFT JOIN ".DB::table('home_album')." a ON p.albumid=a.albumid
				WHERE $sqlwhere
				ORDER BY p.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=pictures&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp=$tpp");


		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';


		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		require_once libfile('function/home');
		while($pic = DB::fetch($query)) {
			$pic['dateline'] = dgmdate($pic['dateline']);
			$pic['title'] = $pic['title'] ? '<b>'.$pic['title'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$censor->check($pic['title']);
			$pic_censor_words = $censor->words_found;
			if(count($pic_censor_words) > 3) {
				$pic_censor_words = array_slice($pic_censor_words, 0, 3);
			}
			$pic['censorwords'] = implode(', ', $pic_censor_words);
			$pic['modpickey'] = modauthkey($pic['picid']);
			$pic['postip'] = $pic['postip'] . '-' . convertip($pic['postip']);
			$pic['url'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $picture['remote']);

			if(count($pic_censor_words)) {
				$pic_censor_text = "<span style=\"color: red;\">({$pic['censorwords']})</span>";
			} else {
				$pic_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$pic[picid]_row1\"", array("id=\"mod_$pic[picid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_1\" value=\"validate\" onclick=\"mod_setbg($pic[picid], 'validate');\"><label for=\"mod_$pic[picid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_2\" value=\"delete\" onclick=\"mod_setbg($pic[picid], 'delete');\"><label for=\"mod_$pic[picid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_3\" value=\"ignore\" onclick=\"mod_setbg($pic[picid], 'ignore');\"><label for=\"mod_$pic[picid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$pic[picid]');\">$pic[title]</a> $pic_censor_text</h3><p>$pic[postip]</p>",
				"<a target=\"_blank\" href=\"home.php?mod=space&uid=$pic[uid]&do=album&id=$pic[albumid]\">$pic[albumname]</a>",
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$pic[uid]&submit=yes\">$pic[username]</a></p> <p>$pic[dateline]</p>",
				"<a target=\"_blank\" href=\"home.php?mod=space&do=album&uid=$pic[uid]&picid=$pic[picid]&modpickey=$pic[modpickey]\">$lang[view]</a>",
			));
			showtablerow("id=\"mod_$pic[picid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;"><img src="'.$pic['url'].'" /></div>');
			showtablerow("id=\"mod_$pic[picid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();
	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $picid => $act) {
				$moderation[$act][] = $picid;
			}
		}
		if($validate_picids = dimplode($moderation['validate'])) {
			DB::update('home_pic', array('status' => '0'), "picid IN ($validate_picids)");
			$validates = DB::affected_rows();
		}

		if(!empty($moderation['delete'])) {
			require_once libfile('function/delete');
			$pics = deletepics($moderation['delete']);
			$deletes = count($pics);
		}

		if($ignore_picids = dimplode($moderation['ignore'])) {
			DB::update('home_pic', array('status' => '2'), "picid IN ($ignore_picids)");
			$ignores = DB::affected_rows();
		}

		if($_G['gp_fast']) {
			echo callback_js($_G['gp_picid']);
			exit;
		} else {
			cpmsg('moderate_pictures_succeed', "action=moderate&operation=pictures&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
		}
	}
} elseif($operation == 'doings') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_doings']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 1),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));
		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$doing_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$doing_status = 2;
		}
		showformheader("moderate&operation=doings");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_content_keyword]: <input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "d.status='$doing_status'";
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND d.username='{$_G['gp_username']}'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND d.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_keyword'])) {
			$keyword = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_keyword']);
			$sqlwhere .= " AND d.message LIKE '%$keyword%'";
		}
		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('home_doing')." d
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT d.doid, d.uid, d.username, d.dateline, d.message, d.ip
				FROM ".DB::table('home_doing')." d
				WHERE $sqlwhere
				ORDER BY d.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=doings&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp=$tpp");





		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';




		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		while($doing = DB::fetch($query)) {
			$doing['dateline'] = dgmdate($doing['dateline']);
			$short_desc = cutstr($doing['message'], 75);
			$censor->check($short_desc);
			$censor->check($doing['message']);
			$doing_censor_words = $censor->words_found;
			if(count($post_censor_words) > 3) {
				$doing_censor_words = array_slice($doing_censor_words, 0, 3);
			}
			$doing['censorwords'] = implode(', ', $doing_censor_words);
			$doing['ip'] = $doing['ip'] . '-' . convertip($doing['ip']);

			if(count($doing_censor_words)) {
				$doing_censor_text = "<span style=\"color: red;\">({$doing['censorwords']})</span>";
			} else {
				$doing_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$doing[doid]_row1\"", array("id=\"mod_$doing[doid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$doing[doid]]\" id=\"mod_$doing[doid]_1\" value=\"validate\" onclick=\"mod_setbg($doing[doid], 'validate');\"><label for=\"mod_$doing[doid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$doing[doid]]\" id=\"mod_$doing[doid]_2\" value=\"delete\" onclick=\"mod_setbg($doing[doid], 'delete');\"><label for=\"mod_$doing[doid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$doing[doid]]\" id=\"mod_$doing[doid]_3\" value=\"ignore\" onclick=\"mod_setbg($doing[doid], 'ignore');\"><label for=\"mod_$doing[doid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$doing[doid]});\">$short_desc $doing_censor_text</a></h3><p>$doing[ip]</p>",
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$doing[uid]&submit=yes\">$doing[username]</a></p> <p>$doing[dateline]</p>",
			));



			showtablerow("id=\"mod_$doing[doid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$doing['message'].'</div>');



			showtablerow("id=\"mod_$doing[doid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=doings&fast=1&doid=$doing[doid]&moderate[$doing[doid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=doings&fast=1&doid=$doing[doid]&moderate[$doing[doid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=doings&fast=1&doid=$doing[doid]&moderate[$doing[doid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();
	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $doid => $act) {
				$moderation[$act][] = $doid;
			}
		}
		if($validate_doids = dimplode($moderation['validate'])) {
			DB::update('home_doing', array('status' => '0'), "doid IN ($validate_doids)");
			$query_t = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid IN ($validate_doids)");
			while($doing = DB::fetch($query_t)) {
				$feedarr = array(
					'appid' => '',
					'icon' => 'doing',
					'uid' => $doing['uid'],
					'username' => $doing['username'],
					'dateline' => $doing['dateline'],
					'title_template' => lang('feed', 'feed_doing_title'),
					'title_data' => daddslashes(serialize(dstripslashes(array('message'=>$doing['message'])))),
					'body_template' => '',
					'body_data' => '',
					'id' => $doing['doid'],
					'idtype' => 'doid'
				);
				DB::insert('home_feed', $feedarr);
			}
			$validates = DB::affected_rows();
		}
		if(!empty($moderation['delete'])) {
			require_once libfile('function/delete');
			$doings = deletedoings($moderation['delete']);
			$deletes = count($doings);
		}
		if($ignore_doids = dimplode($moderation['ignore'])) {
			DB::update('home_doing', array('status' => '2'), "doid IN ($ignore_doids)");
			$ignores = DB::affected_rows();
		}

		if($_G['gp_fast']) {
			echo callback_js($_G['gp_doid']);
			exit;
		} else {
			cpmsg('moderate_doings_succeed', "action=moderate&operation=doings&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
		}
	}
} elseif($operation == 'shares') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_shares']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 1),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));
		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$share_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$share_status = 2;
		}
		showformheader("moderate&operation=shares");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_content_keyword]: <input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "s.status='$share_status'";
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND s.username='{$_G['gp_username']}'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND s.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_keyword'])) {
			$keyword = str_replace(array('%', '_'), array('\%', '\_'), $_G['gp_keyword']);
			$sqlwhere .= " AND s.body_general LIKE '%$keyword%'";
		}
		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('home_share')." s
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT s.sid, s.type, s.uid, s.username, s.dateline, s.body_general, s.itemid, s.fromuid
				FROM ".DB::table('home_share')." s
				WHERE $sqlwhere
				ORDER BY s.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=shares&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp=$tpp");


		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';


		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		while($share = DB::fetch($query)) {
			$short_desc = cutstr($share['body_general'], 30);
			$share['dateline'] = dgmdate($share['dateline']);

			$censor->check($short_desc);
			$censor->check($share['body_general']);
			$share_censor_words = $censor->words_found;
			if(count($share_censor_words) > 3) {
				$share_censor_words = array_slice($share_censor_words, 0, 3);
			}
			$share['censorwords'] = implode(', ', $share_censor_words);
			$share['modkey'] = modauthkey($share['itemid']);

			if(count($share_censor_words)) {
				$share_censor_text = "<span style=\"color: red;\">({$share['censorwords']})</span>";
			} else {
				$share_censor_text = '';
			}

			$shareurl = '';
			switch($share['type']) {
				case 'thread':
					$shareurl = "forum.php?mod=viewthread&tid=$share[itemid]&modthreadkey=$share[modkey]";
					$sharetitle = lang('admincp', 'share_type_thread');
					break;
				case 'pic':
					$shareurl = "home.php?mod=space&do=album&uid=$share[fromuid]&picid=$share[itemid]&modpickey=$share[modkey]";
					$sharetitle = lang('admincp', 'share_type_pic');
					break;
				case 'space':
					$shareurl = "home.php?mod=space&uid=$share[itemid]";
					$sharetitle = lang('admincp', 'share_type_space');
					break;
				case 'blog':
					$shareurl = "home.php?mod=space&do=blog&uid=$share[fromuid]&id=$share[itemid]&modblogkey=$share[modkey]";
					$sharetitle = lang('admincp', 'share_type_blog');
					break;
				case 'album':
					$shareurl = "home.php?mod=space&do=album&uid=$share[fromuid]&id=$share[itemid]&modalbumkey=$share[modkey]";
					$sharetitle = lang('admincp', 'share_type_album');
					break;
				case 'article':
					$shareurl = "portal.php?mod=view&aid=$share[itemid]&modarticlekey=$share[modkey]";
					$sharetitle = lang('admincp', 'share_type_article');
					break;
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$share[sid]_row1\"", array("id=\"mod_$share[sid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$share[sid]]\" id=\"mod_$share[sid]_1\" value=\"validate\" onclick=\"mod_setbg($share[sid], 'validate');\"><label for=\"mod_$share[sid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$share[sid]]\" id=\"mod_$share[sid]_2\" value=\"delete\" onclick=\"mod_setbg($share[sid], 'delete');\"><label for=\"mod_$share[sid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$share[sid]]\" id=\"mod_$doing[doid]_3\" value=\"ignore\" onclick=\"mod_setbg($share[sid], 'ignore');\"><label for=\"mod_$share[sid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$share[sid]});\">$short_desc $share_censor_text</a></h3>",
				$sharetitle,
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$share[uid]&submit=yes\">$share[username]</a></p> <p>$share[dateline]</p>",
				"<a target=\"_blank\" href=\"$shareurl\">$lang[view]</a>",
			));



			showtablerow("id=\"mod_$share[sid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$share['body_general'].'</div>');



			showtablerow("id=\"mod_$share[sid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=shares&fast=1&sid=$share[sid]&moderate[$share[sid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=shares&fast=1&sid=$share[sid]&moderate[$share[sid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=shares&fast=1&sid=$share[sid]&moderate[$share[sid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();

	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $sid => $act) {
				$moderation[$act][] = $sid;
			}
		}

		if($validate_sids = dimplode($moderation['validate'])) {
			require_once libfile('function/feed');
			DB::update('home_share', array('status' => '0'), "sid IN ($validate_sids)");
			$query_t = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid IN ($validate_sids)");
			while($share = DB::fetch($query_t)) {
				switch($share['type']) {
					case 'thread':
						$feed_hash_data = 'tid' . $share['itemid'];
						$share['title_template'] = lang('spacecp', 'share_thread');
						break;
					case 'space':
						$feed_hash_data = 'uid' . $share['itemid'];
						$share['title_template'] = lang('spacecp', 'share_space');
						break;
					case 'blog':
						$feed_hash_data = 'blogid' . $share['itemid'];
						$share['title_template'] = lang('spacecp', 'share_blog');
						break;
					case 'album':
						$feed_hash_data = 'albumid' . $share['itemid'];
						$share['title_template'] =  lang('spacecp', 'share_album');
						break;
					case 'pic':
						$feed_hash_data = 'picid' . $share['itemid'];
						$share['title_template'] = lang('spacecp', 'share_image');
						break;
					case 'article':
						$feed_hash_data = 'articleid' . $share['itemid'];
						$share['title_template'] = lang('spacecp', 'share_article');
						break;
					case 'link':
						$feed_hash_data = '';
						break;
				}
				feed_add('share',
					'{actor} '.$share['title_template'],
					array('hash_data' => $feed_hash_data),
					$share['body_template'],
					unserialize($share['body_data']),
					$share['body_general'],
					array($share['image']),
					array($share['image_link'])
				);
			}
			$validates = DB::affected_rows();
		}

		if(!empty($moderation['delete'])) {
			require libfile('function/delete');
			$shares = deleteshares($moderation['delete']);
			$deletes = count($shares);
		}
		if($ignore_sids = dimplode($moderation['ignore'])) {
			DB::update('home_share', array('status' => '2'), "sid IN ($ignore_sids)");
			$ignores = DB::affected_rows();
		}

		if($_G['gp_fast']) {
			echo callback_js($_G['gp_sid']);
			exit;
		} else {
			cpmsg('moderate_shares_succeed', "action=moderate&operation=shares&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
		}
	}
} elseif($operation == 'comments') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_comments']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 1),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));


		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$idtype_select = '<option value="">'.$lang['all'].'</option>';
		foreach(array('uid', 'blogid', 'picid', 'sid') as $v) {
			$selected = '';
			if($_G['gp_idtype'] == $v) {
				$selected = 'selected="selected"';
			}
			$idtype_select .= "<option value=\"$v\" $selected>".$lang["comment_$v"]."</option>";
		}
		$comment_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$comment_status = 2;
		}
		showformheader("moderate&operation=comments");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[comment_idtype]: <select name=\"idtype\">$idtype_select</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_content_keyword]: <input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "c.status='$comment_status'";
		if(!empty($_G['gp_idtype'])) {
			$sqlwhere .= " AND c.idtype='{$_G['gp_idtype']}'";
		}
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND c.author='{$_G['gp_username']}'";
		}
		if(!empty($dateline) && $dateline != 'all') {
			$sqlwhere .= " AND c.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_keyword'])) {
			$keyword = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_keyword']);
			$sqlwhere .= " AND c.message LIKE '%{$keyword}%'";
		}
		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('home_comment')." c
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT c.cid, c.uid, c.id, c.idtype, c.authorid, c.author, c.message, c.dateline, c.ip
				FROM ".DB::table('home_comment')." c
				WHERE $sqlwhere
				ORDER BY c.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=comments&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&idtype={$_G['gp_idtype']}&ppp=$tpp");


		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';


		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		while($comment = DB::fetch($query)) {
			$comment['dateline'] = dgmdate($comment['dateline']);
			$short_desc = cutstr($comment['message'], 75);
			$censor->check($short_desc);
			$censor->check($comment['message']);
			$comment_censor_words = $censor->words_found;
			if(count($comment_censor_words) > 3) {
				$comment_censor_words = array_slice($comment_censor_words, 0, 3);
			}
			$comment['censorwords'] = implode(', ', $comment_censor_words);
			$comment['ip'] = $comment['ip'] . ' - ' . convertip($comment['ip']);
			$comment['modkey'] = modauthkey($comment['id']);
			$comment['modcommentkey'] = modauthkey($comment['cid']);

			if(count($comment_censor_words)) {
				$comment_censor_text = "<span style=\"color: red;\">({$comment['censorwords']})</span>";
			} else {
				$comment_censor_text = lang('admincp', 'no_censor_word');
			}
			$viewurl = '';
			$commenttype = '';
			$editurl = "home.php?mod=spacecp&ac=comment&op=edit&cid=$comment[cid]&modcommentkey=$comment[modcommentkey]";
			switch($comment['idtype']) {
				case 'uid':
					$commenttype = lang('admincp', 'comment_uid');
					$viewurl = "home.php?mod=space&do=wall&uid=$comment[uid]#comment_anchor_$comment[cid]";
					break;
				case 'blogid':
					$commenttype = lang('admincp', 'comment_blogid');
					$viewurl = "home.php?mod=space&do=blog&uid=$comment[uid]&id=$comment[id]&modblogkey=$comment[modkey]#comment_anchor_$comment[cid]";
					break;
				case 'picid':
					$commenttype = lang('admincp', 'comment_picid');
					$viewurl = "home.php?mod=space&do=album&uid=$comment[uid]&picid=$comment[id]&modpickey=$comment[modkey]#comment_anchor_$comment[cid]";
					break;
				case 'sid':
					$commenttype = lang('admincp', 'comment_sid');
					$viewurl = "home.php?mod=space&do=share&uid=$comment[uid]&id=$comment[id]#comment_anchor_$comment[cid]";
					break;
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$comment[cid]_row1\"", array("id=\"mod_$comment[cid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_1\" value=\"validate\" onclick=\"mod_setbg($comment[cid], 'validate');\"><label for=\"mod_$comment[cid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_2\" value=\"delete\" onclick=\"mod_setbg($comment[cid], 'delete');\"><label for=\"mod_$comment[cid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_3\" value=\"ignore\" onclick=\"mod_setbg($comment[cid], 'ignore');\"><label for=\"mod_$comment[cid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$comment[cid]});\"> $short_desc $comment_censor_text</a></h3><p>$comment[ip]</p>",
				$commenttype,
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$comment[authorid]&submit=yes\">$comment[author]</a></p> <p>$comment[dateline]</p>",
				"<a target=\"_blank\" href=\"$viewurl\">$lang[view]</a>&nbsp;<a href=\"$editurl\" target=\"_blank\">$lang[edit]</a>",
			));



			showtablerow("id=\"mod_$comment[cid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$comment['message'].'</div>');



			showtablerow("id=\"mod_$comment[cid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();
	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $cid => $act) {
				$moderation[$act][] = $cid;
			}
		}

		if($validate_cids = dimplode($moderation['validate'])) {
			DB::update('home_comment', array('status' => '0'), "cid IN ($validate_cids)");
			$validates = DB::affected_rows();
		}
		if(!empty($moderation['delete'])) {
			require_once libfile('function/delete');
			$comments = deletecomments($moderation['delete']);
			$deletes = count($comments);
		}
		if($ignore_cids = dimplode($moderation['ignore'])) {
			DB::update('home_comment', array('status' => '2'), "cid IN ($ignore_cids)");
			$ignores = DB::affected_rows();
		}
		if($_G['gp_fast']) {
			echo callback_js($_G['gp_cid']);
			exit;
		} else {
			cpmsg('moderate_comments_succeed', "action=moderate&operation=comments&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&idtype={$_G['gp_idtype']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
		}
	}
} elseif($operation == 'articles') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_articles']);
		showsubmenu('nav_moderate_posts', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 1),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 0),
		));
		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$cat_select = '<option value="">'.$lang['all'].'</option>';
		$query = DB::query("SELECT catid, catname FROM ".DB::table('portal_category'));
		while($cat = DB::fetch($query)) {
			$selected = '';
			if($cat['catid'] == $_G['gp_catid']) {
				$selected = 'selected="selected"';
			}
			$cat_select .= "<option value=\"$cat[catid]\" $selected>$cat[catname]</option>";
		}
		$article_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$article_status = 2;
		}
		showformheader("moderate&operation=articles");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[moderate_article_category]: <select name=\"catid\">$cat_select</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "a.status='$article_status'";
		if(!empty($_G['gp_catid'])) {
			$sqlwhere .= " AND a.catid='{$_G['gp_catid']}'";
		}
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND a.username='{$_G['gp_username']}'";
		}
		if($dateline != 'all') {
			$sqlwhere .= " AND a.dateline>'".(TIMESTAMP - $dateline)."'";
		}

		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('portal_article_title')." a
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT a.aid, a.catid, a.uid, a.username, a.title, a.summary, a.dateline, cat.catname
				FROM ".DB::table('portal_article_title')." a
				LEFT JOIN ".DB::table('portal_category')." cat ON cat.catid=a.catid
				WHERE $sqlwhere
				ORDER BY a.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=articles&filter=$filter&catid={$_G['gp_catid']}&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp=$tpp");


		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';


		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		while($article = DB::fetch($query)) {
			$article['dateline'] = dgmdate($article['dateline']);
			$censor->check($article['title']);
			$censor->check($article['summary']);
			$article_censor_words = $censor->words_found;
			if(count($article_censor_words) > 3) {
				$article_censor_words = array_slice($article_censor_words, 0, 3);
			}
			$article['censorwords'] = implode(', ', $article_censor_words);
			$article['modarticlekey'] = modauthkey($article['aid']);

			if(count($article_censor_words)) {
				$article_censor_text = "<span style=\"color: red;\">({$article['censorwords']})</span>";
			} else {
				$article_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$article[aid]_row1\"", array("id=\"mod_$article[aid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$article[aid]]\" id=\"mod_$article[aid]_1\" value=\"validate\" onclick=\"mod_setbg($article[aid], 'validate');\"><label for=\"mod_$article[aid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$article[aid]]\" id=\"mod_$article[aid]_2\" value=\"delete\" onclick=\"mod_setbg($article[aid], 'delete');\"><label for=\"mod_$article[aid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$article[aid]]\" id=\"mod_$article[aid]_3\" value=\"ignore\" onclick=\"mod_setbg($article[aid], 'ignore');\"><label for=\"mod_$article[aid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$article[aid]});\">$article[title] $article_censor_text</a></h3>",
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$article[uid]&submit=yes\">$article[username]</a></p> <p>$article[dateline]</p>",
				"<a target=\"_blank\" href=\"portal.php?mod=view&aid=$article[aid]&modarticlekey=$article[modarticlekey]\">$lang[view]</a>&nbsp;<a href=\"portal.php?mod=portalcp&ac=article&op=edit&aid=$article[aid]&modarticlekey=$article[modarticlekey]\" target=\"_blank\">$lang[edit]</a>",
			));



			showtablerow("id=\"mod_$article[aid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$article['summary'].'</div>');



			showtablerow("id=\"mod_$article[aid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=articles&fast=1&aid=$article[aid]&moderate[$article[aid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=articles&fast=1&aid=$article[aid]&moderate[$article[aid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=articles&fast=1&aid=$article[aid]&moderate[$article[aid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();
	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $aid => $act) {
				$moderation[$act][] = $aid;
			}
		}

		if($validate_aids = dimplode($moderation['validate'])) {
			DB::update('portal_article_title', array('status' => '0'), "aid IN ($validate_aids)");
			$validates = DB::affected_rows();
		}
		if(!empty($moderation['delete'])) {
			require_once libfile('function/delete');
			$articles = deletearticle($moderation['delete']);
			$deletes = count($articles);
		}
		if($ignore_aids = dimplode($moderation['ignore'])) {
			DB::update('portal_article_title', array('status' => '2'), "aid IN ($ignore_aids)");
			$ignores = DB::affected_rows();
		}
		if($_G['gp_fast']) {
			echo callback_js($_G['gp_aid']);
			exit;
		} else {
			cpmsg('moderate_articles_succeed', "action=moderate&operation=articles&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&idtype={$_G['gp_idtype']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
		}
	}
} elseif($operation == 'articlecomments') {
	if(!submitcheck('modsubmit') && !$_G['gp_fast']) {
		shownav('topic', $lang['moderate_articlecomments']);
		showsubmenu('nav_moderate_articlecomments', array(
			array('nav_moderate_threads', 'moderate&operation=threads', 0),
			array('nav_moderate_replies', 'moderate&operation=replies', 0),
			array('nav_moderate_blogs', 'moderate&operation=blogs', 0),
			array('nav_moderate_pictures', 'moderate&operation=pictures', 0),
			array('nav_moderate_doings', 'moderate&operation=doings', 0),
			array('nav_moderate_shares', 'moderate&operation=shares', 0),
			array('nav_moderate_comments', 'moderate&operation=comments', 0),
			array('nav_moderate_articles', 'moderate&operation=articles', 0),
			array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', 1),
		));

		$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
		$tpp_options = "<option value='20' $select[20]>$lang[perpage_20]</option><option value='50' $select[50]>$lang[perpage_50]</option><option value='100' $select[100]>$lang[perpage_100]</option>";
		$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
		$start_limit = ($page - 1) * $ppp;
		$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
		$dateline_options = '';
		foreach(array('all', '604800', '2592000', '7776000') as $v) {
			$selected = '';
			if($dateline == $v) {
				$selected = "selected='selected'";
			}
			$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
		}
		$cat_select = '<option value="">'.$lang['all'].'</option>';
		$query = DB::query("SELECT catid, catname FROM ".DB::table('portal_category'));
		while($cat = DB::fetch($query)) {
			$selected = '';
			if($cat['catid'] == $_G['gp_catid']) {
				$selected = 'selected="selected"';
			}
			$cat_select .= "<option value=\"$cat[catid]\" $selected>$cat[catname]</option>";
		}
		$articlecomment_status = 1;
		if($_G['gp_filter'] == 'ignore') {
			$articlecomment_status = 2;
		}
		showformheader("moderate&operation=articlecomments");
		showtableheader('search');
		showtablerow('', array(''), array("<select name=\"filter\" style=\"margin: 0px;\">$filteroptions</select>
		$lang[moderate_article_category]: <select name=\"catid\">$cat_select</select>
		$lang[moderate_dateline]: <select name=\"dateline\" style=\"margin: 0px;\">$dateline_options</select>
		$lang[username]: <input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />
		$lang[moderate_content_keyword]: <input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />
		<select name=\"tpp\" style=\"margin: 0px;\">$tpp_options</select>
		<input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"));
		showtablefooter();

		$pagetmp = $page;
		$sqlwhere = "c.status='$articlecomment_status'";
		if(!empty($_G['gp_catid'])) {
			$sqlwhere .= " AND a.catid='{$_G['gp_catid']}'";
		}
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND c.username='{$_G['gp_username']}'";
		}
		if($dateline != 'all') {
			$sqlwhere .= " AND c.dateline>'".(TIMESTAMP - $dateline)."'";
		}
		if(!empty($_G['gp_keyword'])) {
			$sqlwhere .= " AND c.message LIKE '%{$_G['gp_keyword']}%'";
		}
		$modcount = DB::result_first("SELECT COUNT(*)
			FROM ".DB::table('portal_comment')." c
			LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=c.aid
			WHERE $sqlwhere");
		do {
			$start_limit = ($pagetmp - 1) * $tpp;
			$query = DB::query("SELECT c.cid, c.uid, c.username, c.aid, c.postip, c.dateline, c.message, a.title, cat.catname
				FROM ".DB::table('portal_comment')." c
				LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=c.aid
				LEFT JOIN ".DB::table('portal_category')." cat ON cat.catid=a.catid
				WHERE $sqlwhere
				ORDER BY c.dateline DESC
				LIMIT $start_limit, $tpp");
				$pagetmp = $pagetmp - 1;
		} while($pagetmp > 0 && DB::num_rows($query) == 0);
		$page = $pagetmp + 1;
		$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=articlecomments&filter=$filter&modfid=$modfid&ppp=$tpp");


		echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';


		showtableheader();
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		require_once libfile('function/misc');
		while($articlecomment = DB::fetch($query)) {
			$articlecomment['dateline'] = dgmdate($articlecomment['dateline']);
			$censor->check($articlecomment['title']);
			$censor->check($articlecomment['message']);
			$articlecomment_censor_words = $censor->words_found;
			if(count($articlecomment_censor_words) > 3) {
				$articlecomment_censor_words = array_slice($articlecomment_censor_words, 0, 3);
			}
			$articlecomment['censorwords'] = implode(', ', $articlecomment_censor_words);
			$articlecomment['modarticlekey'] = modauthkey($articlecomment['aid']);
			$articlecomment['modarticlecommentkey'] = modauthkey($articlecomment['cid']);

			if(count($articlecomment_censor_words)) {
				$articlecomment_censor_text = "<span style=\"color: red;\">({$articlecomment['censorwords']})</span>";
			} else {
				$articlecomment_censor_text = '';
			}
			showtagheader('tbody', '', true, 'hover');
			showtablerow("id=\"mod_$articlecomment[cid]_row1\"", array("id=\"mod_$articlecomment[cid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="55"'), array(
				"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_1\" value=\"validate\" onclick=\"mod_setbg($articlecomment[cid], 'validate');\"><label for=\"mod_$articlecomment[cid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_2\" value=\"delete\" onclick=\"mod_setbg($articlecomment[cid], 'delete');\"><label for=\"mod_$articlecomment[cid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_3\" value=\"ignore\" onclick=\"mod_setbg($articlecomment[cid], 'ignore');\"><label for=\"mod_$articlecomment[cid]_3\">$lang[ignore]</label></li></ul>",
				"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$articlecomment[cid]});\">$articlecomment[title] $articlecomment_censor_text</a></h3>",
				"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$articlecomment[uid]&submit=yes\">$articlecomment[username]</a></p> <p>$articlecomment[dateline]</p>",
				"<a target=\"_blank\" href=\"portal.php?mod=view&aid=$articlecomment[aid]&modarticlekey=$articlecomment[modarticlekey]#comment_anchor_{$articlecomment[cid]}\">$lang[view]</a>&nbsp;<a href=\"portal.php?mod=portalcp&ac=comment&op=edit&cid=$articlecomment[cid]&modarticlecommentkey=$articlecomment[modarticlecommentkey]\" target=\"_blank\">$lang[edit]</a>",
			));



			showtablerow("id=\"mod_$articlecomment[cid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$articlecomment['message'].'</div>');



			showtablerow("id=\"mod_$articlecomment[cid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=articlecomments&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=articlecomments&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=articlecomments&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
			showtagfooter('tbody');
		}

		showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
		showtablefooter();
		showformfooter();
	} else {
		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$validates = $deletes = $ignores = 0;
		if(is_array($moderate)) {
			foreach($moderate as $cid => $act) {
				$moderation[$act][] = $cid;
			}
		}

		if($validate_cids = dimplode($moderation['validate'])) {
			DB::update('portal_comment', array('status' => '0'), "cid IN ($validate_cids)");
			$validates = DB::affected_rows();
		}
		if($delete_cids = dimplode($moderation['delete'])) {
			DB::delete('portal_comment', "cid IN ($delete_cids)");
			$deletes = DB::affected_rows();
		}
		if($ignore_cids = dimplode($moderation['ignore'])) {
			DB::update('portal_comment', array('status' => '2'), "cid IN ($ignore_cids)");
			$ignores = DB::affected_rows();
		}

		if($_G['gp_fast']) {
			echo callback_js($_G['gp_cid']);
			exit;
		} else {
			cpmsg('moderate_articlecomments_succeed', "action=moderate&operation=articlecomments&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&catid={$_G['gp_catid']}&tpp={$_G['gp_tpp']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
		}
	}
}
echo '<iframe name="fasthandle" style="display: none;"></iframe>';
?>