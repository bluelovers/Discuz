<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_admingroup.php 22488 2011-05-10 05:20:15Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(submitcheck('groupsubmit') && $ids = dimplode($_G['gp_delete'])) {
		$gids = array();
		$query = DB::query("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids) AND type='special' AND radminid>'0'");
		while($g = DB::fetch($query)) {
			$gids[] = $g['groupid'];
		}
		if($ids = dimplode($gids)) {
			DB::query("DELETE FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('common_usergroup_field')." WHERE groupid IN ($ids)");
			DB::query("DELETE FROM ".DB::table('common_admingroup')." WHERE admingid IN ($ids)");
			$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
			DB::query("UPDATE ".DB::table('common_member')." SET groupid='$newgroupid', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
			deletegroupcache($gids);
		}
	}

	$grouplist = array();
	$query = DB::query("SELECT a.*, u.groupid, u.radminid, u.grouptitle, u.stars, u.color, u.icon, u.type FROM ".DB::table('common_admingroup')." a
			LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid
			ORDER BY u.type, u.radminid, a.admingid");
	while ($group = DB::fetch($query)) {
		$grouplist[$group['groupid']] = $group;
	}

	if(!submitcheck('groupsubmit')) {

		shownav('user', 'nav_admingroups');
		showsubmenu('nav_admingroups');
		showtips('admingroup_tips');

		showformheader('admingroup');
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'usergroups_title', '', 'type', 'admingroup_level', 'usergroups_stars', 'usergroups_color',
		    '<input class="checkbox" type="checkbox" name="gbcmember" onclick="checkAll(\'value\', this.form, \'gbmember\', \'gbcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>',
		    '<input class="checkbox" type="checkbox" name="gpcmember" onclick="checkAll(\'value\', this.form, \'gpmember\', \'gpcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>',
		));

		foreach($grouplist as $gid => $group) {
			$adminidselect = '<select name="newradminid['.$group['groupid'].']">';
			for($i = 1;$i <= 3;$i++) {
				$adminidselect .= '<option value="'.$i.'"'.($i == $group['radminid'] ? ' selected="selected"' : '').'>'.$lang['usergroups_system_'.$i].'</option>';
			}
			$adminidselect .= '</select>';

			showtablerow('', array('', '', 'class="td23 lightfont"', 'class="td25"', '', 'class="td25"'), array(
				$group['type'] == 'system' ? '<input type="checkbox" class="checkbox" disabled="disabled" />' : "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$group[groupid]\">",
				'<span style="color:'.$group[color].'">'.$group['grouptitle'].'</span>',
				"(groupid:$group[groupid])",
				$group['type'] == 'system' ? cplang('inbuilt') : cplang('custom'),
				$group['type'] == 'system' ? $lang['usergroups_system_'.$group['radminid']] : $adminidselect,
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
				"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\" name=\"group_color[$group[groupid]]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gbmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id={$group[admingid]}\" class=\"act\">$lang[admingroup_setting_user]</a>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gpmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=admingroup&operation=edit&id=$group[admingid]\" class=\"act\">$lang[admingroup_setting_admin]</a>"
			));
		}
		showtablerow('', array('class="td25"', '', '', '', 'colspan="6"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="12" name="grouptitlenew">',
			'',
			cplang('custom'),
			"<select name=\"radminidnew\"><option value=\"1\">$lang[usergroups_system_1]</option><option value=\"2\">$lang[usergroups_system_2]</option><option value=\"3\" selected=\"selected\">$lang[usergroups_system_3]</option>",
		));
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		foreach($grouplist as $gid => $group) {
			$stars = intval($_G['gp_group_stars'][$gid]);
			$color = dhtmlspecialchars($_G['gp_group_color'][$gid]);
			if($group['color'] != $color || $group['stars'] != $stars || $group['icon'] != $avatar) {
				DB::query("UPDATE ".DB::table('common_usergroup')." SET stars='$stars', color='$color' WHERE groupid='$gid'");
			}
		}

		$grouptitlenew = dhtmlspecialchars(trim($_G['gp_grouptitlenew']));
		$radminidnew = intval($_G['gp_radminidnew']);

		foreach($_G['gp_newradminid'] as $groupid => $newradminid) {
			DB::update('common_usergroup', array('radminid' => $newradminid), "groupid='$groupid'");
		}

		if($grouptitlenew && in_array($radminidnew, array(1, 2, 3))) {

			$data = array();
			$usergroup = DB::fetch_first("SELECT * FROM ".DB::table('common_usergroup')." WHERE groupid='$radminidnew'");
			foreach ($usergroup as $key => $val) {
				if(!in_array($key, array('groupid', 'radminid', 'type', 'system', 'grouptitle'))) {
					$val = addslashes($val);
					$data[$key] = $val;
				}
			}
			$fielddata = array();
			$usergroup = DB::fetch_first("SELECT * FROM ".DB::table('common_usergroup_field')." WHERE groupid='$radminidnew'");
			foreach ($usergroup as $key => $val) {
				if(!in_array($key, array('groupid'))) {
					$val = addslashes($val);
					$fielddata[$key] = $val;
				}
			}

			$adata = array();
			$admingroup = DB::fetch_first("SELECT * FROM ".DB::table('common_admingroup')." WHERE admingid='$radminidnew'");
			foreach ($admingroup as $key => $val) {
				if(!in_array($key, array('admingid'))) {
					$val = addslashes($val);
					$adata[$key] = $val;
				}
			}

			$data['radminid'] = $radminidnew;
			$data['type'] = 'special';
			$data['grouptitle'] = $grouptitlenew;
			$newgroupid = DB::insert('common_usergroup', $data, 1);
			if($newgroupid) {
				$adata['admingid'] = $newgroupid;
				$fielddata['groupid'] = $newgroupid;
				DB::insert('common_admingroup', $adata);
				DB::insert('common_usergroup_field', $fielddata);
			}
		}

		updatecache(array('usergroups', 'groupreadaccess', 'admingroups'));

		cpmsg('admingroups_edit_succeed', 'action=admingroup', 'succeed');

	}

} elseif($operation == 'edit') {

	$submitcheck = submitcheck('groupsubmit');

	$multiset = 0;
	if(empty($_G['gp_multi'])) {
		$gids = $_G['gp_id'];
	} else {
		$multiset = 1;
		if(is_array($_G['gp_multi'])) {
			$gids = dimplode($_G['gp_multi']);
		} else {
			$_G['gp_multi'] = explode(',', $_G['gp_multi']);
			array_walk($_G['gp_multi'], 'intval');
			$gids = dimplode($_G['gp_multi']);
		}
	}
	if(count($_G['gp_multi']) == 1) {
		$gids = $_G['gp_multi'][0];
		$multiset = 0;
	}

	if(!$submitcheck) {
		if(empty($gids)) {
			$grouplist = "<select name=\"id\" style=\"width: 150px\">\n";
			$query = DB::query("SELECT u.groupid, u.grouptitle FROM ".DB::table('common_admingroup')." a LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid ORDER BY u.type, u.radminid, a.admingid");
			while($group = DB::fetch($query)) {
				$grouplist .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
			$grouplist .= '</select>';
			cpmsg('admingroups_edit_nonexistence', 'action=admingroup&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : ''), 'form', array(), $grouplist);
		}

		$query = DB::query("SELECT a.*, u.radminid, u.grouptitle, u.groupid FROM ".DB::table('common_admingroup')." a
			LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid
			WHERE a.admingid IN ($gids)");
		if(!DB::num_rows($query)) {
			cpmsg('usergroups_nonexistence', '', 'error');
		} else {
			while($group = DB::fetch($query)) {
				$mgroup[] = $group;
			}
		}

		$query = DB::query("SELECT u.radminid, u.groupid, u.grouptitle FROM ".DB::table('common_admingroup')." a LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid ORDER BY u.radminid, a.admingid");
		$grouplist = $gutype = '';
		while($ggroup = DB::fetch($query)) {
			$checked = $_G['gp_id'] == $ggroup['groupid'] || in_array($ggroup['groupid'], $_G['gp_multi']);
			if($gutype != $ggroup['radminid']) {
				$grouplist .= '<em><span class="right"><input name="checkall_'.$ggroup['radminid'].'" onclick="checkAll(\'value\', this.form, \'g'.$ggroup['radminid'].'\', \'checkall_'.$ggroup['radminid'].'\')" type="checkbox" class="vmiddle checkbox" /></span>'.
					($ggroup['radminid'] == 1 ? $lang['usergroups_system_1'] : ($ggroup['radminid'] == 2 ? $lang['usergroups_system_2'] : $lang['usergroups_system_3'])).'</em>';
				$gutype = $ggroup['radminid'];
			}
			$grouplist .= '<input class="left checkbox ck" chkvalue="g'.$ggroup['radminid'].'" name="multi[]" value="'.$ggroup['groupid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/>'.
				'<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&switch=yes&id='.$ggroup['groupid'].'&anchor=\'+currentAnchor+\'&scrolltop=\'+document.documentElement.scrollTop"'.($checked ? ' class="current"' : '').'>'.$ggroup['grouptitle'].'</a>';
		}
		$gselect = '<span id="ugselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'ugselect_menu\').style.top=(parseInt($(\'ugselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'ugselect_menu\').style.left=(parseInt($(\'ugselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.$lang['usergroups_switch'].'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="ugselect_menu" class="popupmenu_popup" style="display:none">'.
			$grouplist.
			'<br style="clear:both" /><div class="cl"><input type="button" class="btn right" onclick="$(\'menuform\').submit()" value="'.cplang('admingroups_multiedit').'" /></div>'.
			'</div>';

		$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('threadperm', 'postperm', 'modcpperm', 'portalperm', 'otherperm', 'spaceperm')) ? $_G['gp_anchor'] : 'threadperm';
		$anchorarray = array(
			array('admingroup_edit_threadperm', 'threadperm', $_G['gp_anchor'] == 'threadperm'),
			array('admingroup_edit_postperm', 'postperm', $_G['gp_anchor'] == 'postperm'),
			array('admingroup_edit_modcpperm', 'modcpperm', $_G['gp_anchor'] == 'modcpperm'),
			array('admingroup_edit_spaceperm', 'spaceperm', $_G['gp_anchor'] == 'spaceperm'),
			array('admingroup_edit_portalperm', 'portalperm', $_G['gp_anchor'] == 'portalperm'),
			array('admingroup_edit_otherperm', 'otherperm', $_G['gp_anchor'] == 'otherperm'),
		);

		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'admingroup', 'operation' => 'edit'));
		showsubmenuanchors($lang['admingroup_edit'].(count($mgroup) == 1 ? ' - '.$mgroup[0]['grouptitle'].'(groupid:'.$mgroup[0]['groupid'].')' : ''), $anchorarray, $gselect);
		showformfooter();

		if($multiset) {
			showtips('setting_multi_tips');
		}

		showformheader("admingroup&operation=edit&id={$_G['gp_id']}");
		if($multiset) {
			$_G['showsetting_multi'] = 0;
			$_G['showsetting_multicount'] = count($mgroup);
			foreach($mgroup as $group) {
				$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=admingroup&operation=edit&id='.$group['groupid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$group['grouptitle'].'(groupid:'.$group['groupid'].')</a>';
			}
		}
		$mgids = array();
		foreach($mgroup as $group) {
		$_G['gp_id'] = $gid = $group['groupid'];
		$mgids[] = $gid;

		showtableheader();
		showtagheader('tbody', 'threadperm', $_G['gp_anchor'] == 'threadperm');
		showtitle('admingroup_edit_threadperm');
		showsetting('admingroup_edit_stick_thread', array('allowstickthreadnew', array(
			array(0, $lang['admingroup_edit_stick_thread_none']),
			array(1, $lang['admingroup_edit_stick_thread_1']),
			array(2, $lang['admingroup_edit_stick_thread_2']),
			array(3, $lang['admingroup_edit_stick_thread_3'])
		)), $group['allowstickthread'], 'mradio');
		showsetting('admingroup_edit_digest_thread', array('allowdigestthreadnew', array(
			array(0, $lang['admingroup_edit_digest_thread_none']),
			array(1, $lang['admingroup_edit_digest_thread_1']),
			array(2, $lang['admingroup_edit_digest_thread_2']),
			array(3, $lang['admingroup_edit_digest_thread_3'])
		)), $group['allowdigestthread'], 'mradio');
		showsetting('admingroup_edit_bump_thread', 'allowbumpthreadnew', $group['allowbumpthread'], 'radio');
		showsetting('admingroup_edit_highlight_thread', 'allowhighlightthreadnew', $group['allowhighlightthread'], 'radio');
		showsetting('admingroup_edit_recommend_thread', 'allowrecommendthreadnew', $group['allowrecommendthread'], 'radio');
		showmultititle();
		showsetting('admingroup_edit_stamp_thread', 'allowstampthreadnew', $group['allowstampthread'], 'radio');
		showsetting('admingroup_edit_stamp_list', 'allowstamplistnew', $group['allowstamplist'], 'radio');
		showsetting('admingroup_edit_close_thread', 'allowclosethreadnew', $group['allowclosethread'], 'radio');
		showsetting('admingroup_edit_move_thread', 'allowmovethreadnew', $group['allowmovethread'], 'radio');
		showsetting('admingroup_edit_edittype_thread', 'allowedittypethreadnew', $group['allowedittypethread'], 'radio');
		showsetting('admingroup_edit_copy_thread', 'allowcopythreadnew', $group['allowcopythread'], 'radio');
		showmultititle();
		showsetting('admingroup_edit_merge_thread', 'allowmergethreadnew', $group['allowmergethread'], 'radio');
		showsetting('admingroup_edit_split_thread', 'allowsplitthreadnew', $group['allowsplitthread'], 'radio');
		showsetting('admingroup_edit_repair_thread', 'allowrepairthreadnew', $group['allowrepairthread'], 'radio');
		showsetting('admingroup_edit_refund', 'allowrefundnew', $group['allowrefund'], 'radio');
		showsetting('admingroup_edit_edit_poll', 'alloweditpollnew', $group['alloweditpoll'], 'radio');
		showsetting('admingroup_edit_remove_reward', 'allowremoverewardnew', $group['allowremovereward'], 'radio');
		showsetting('admingroup_edit_edit_activity', 'alloweditactivitynew', $group['alloweditactivity'], 'radio');
		showsetting('admingroup_edit_edit_trade', 'allowedittradenew', $group['allowedittrade'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'postperm', $_G['gp_anchor'] == 'postperm');
		showtitle('admingroup_edit_postperm');
		showsetting('admingroup_edit_edit_post', 'alloweditpostnew', $group['alloweditpost'], 'radio');
		showsetting('admingroup_edit_warn_post', 'allowwarnpostnew', $group['allowwarnpost'], 'radio');
		showsetting('admingroup_edit_ban_post', 'allowbanpostnew', $group['allowbanpost'], 'radio');
		showsetting('admingroup_edit_del_post', 'allowdelpostnew', $group['allowdelpost'], 'radio');
		showsetting('admingroup_edit_stick_post', 'allowstickreplynew', $group['allowstickreply'], 'radio');
		showsetting('admingroup_edit_manage_tag', 'allowmanagetagnew', $group['allowmanagetag'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'modcpperm', $_G['gp_anchor'] == 'modcpperm');
		showtitle('admingroup_edit_modcpperm');
		showsetting('admingroup_edit_mod_post', 'allowmodpostnew', $group['allowmodpost'], 'radio');
		showsetting('admingroup_edit_mod_user', 'allowmodusernew', $group['allowmoduser'], 'radio');
		showsetting('admingroup_edit_ban_user', 'allowbanusernew', $group['allowbanuser'], 'radio');
		showsetting('admingroup_edit_ban_user_visit', 'allowbanvisitusernew', $group['allowbanvisituser'], 'radio');
		showsetting('admingroup_edit_ban_ip', 'allowbanipnew', $group['allowbanip'], 'radio');
		showsetting('admingroup_edit_edit_user', 'alloweditusernew', $group['allowedituser'], 'radio');
		showmultititle();
		showsetting('admingroup_edit_mass_prune', 'allowmassprunenew', $group['allowmassprune'], 'radio');
		showsetting('admingroup_edit_edit_forum', 'alloweditforumnew', $group['alloweditforum'], 'radio');
		showsetting('admingroup_edit_post_announce', 'allowpostannouncenew', $group['allowpostannounce'], 'radio');
		showsetting('admingroup_edit_clear_recycle', 'allowclearrecyclenew', $group['allowclearrecycle'], 'radio');
		showsetting('admingroup_edit_view_log', 'allowviewlognew', $group['allowviewlog'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'spaceperm', $_G['gp_anchor'] == 'spaceperm');
		showtitle('admingroup_edit_spaceperm');
		showsetting('admingroup_edit_manage_feed', 'managefeednew', $group['managefeed'], 'radio');
		showsetting('admingroup_edit_manage_doing', 'managedoingnew', $group['managedoing'], 'radio');
		showsetting('admingroup_edit_manage_share', 'managesharenew', $group['manageshare'], 'radio');
		showsetting('admingroup_edit_manage_blog', 'manageblognew', $group['manageblog'], 'radio');
		showsetting('admingroup_edit_manage_album', 'managealbumnew', $group['managealbum'], 'radio');
		showsetting('admingroup_edit_manage_comment', 'managecommentnew', $group['managecomment'], 'radio');
		showmultititle();
		showsetting('admingroup_edit_manage_magiclog', 'managemagiclognew', $group['managemagiclog'], 'radio');
		showsetting('admingroup_edit_manage_report', 'managereportnew', $group['managereport'], 'radio');
		showsetting('admingroup_edit_manage_hotuser', 'managehotusernew', $group['managehotuser'], 'radio');
		showsetting('admingroup_edit_manage_defaultuser', 'managedefaultusernew', $group['managedefaultuser'], 'radio');
		showsetting('admingroup_edit_manage_videophoto', 'managevideophotonew', $group['managevideophoto'], 'radio');
		showsetting('admingroup_edit_manage_magic', 'managemagicnew', $group['managemagic'], 'radio');
		showsetting('admingroup_edit_manage_click', 'manageclicknew', $group['manageclick'], 'radio');
		showtagfooter('tbody');

		showtagheader('tbody', 'otherperm', $_G['gp_anchor'] == 'otherperm');
		showtitle('admingroup_edit_otherperm');
		showsetting('admingroup_edit_view_ip', 'allowviewipnew', $group['allowviewip'], 'radio');
		showtagfooter('tbody');
		showtablefooter();

		showtagheader('div', 'portalperm', $_G['gp_anchor'] == 'portalperm');
		showtableheader();
		showtagheader('tbody', '', true);
		showtitle('admingroup_edit_portalperm');
		showsetting('admingroup_edit_manage_article', 'allowmanagearticlenew', $group['allowmanagearticle'], 'radio');
		showtagfooter('tbody');
		showtagheader('tbody', '', true);
		showsetting('admingroup_edit_add_topic', 'allowaddtopicnew', $group['allowaddtopic'], 'radio');
		showsetting('admingroup_edit_manage_topic', 'allowmanagetopicnew', $group['allowmanagetopic'], 'radio');
		showsetting('admingroup_edit_diy', 'allowdiynew', $group['allowdiy'], 'radio');
		showtagfooter('tbody');
		showtablefooter();
		showtagfooter('div');

		showsubmit('groupsubmit');

		$_G['showsetting_multi']++;
		}

		if($_G['showsetting_multicount'] > 1) {
			showhiddenfields(array('multi' => implode(',', $mgids)));
			showmulti();
		}
		showformfooter();

	} else {

		if(!$multiset) {
			$_G['gp_multinew'] = array(0 => array('single' => 1));
		}
		foreach($_G['gp_multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_G['gp_'.$key] = $value;
			}
			$_G['gp_id'] = $_G['gp_multi'][$k];
		}
		$group = $mgroup[$k];

		DB::update('common_admingroup', array(
			'alloweditpost' => $_G['gp_alloweditpostnew'],
			'alloweditpoll' => $_G['gp_alloweditpollnew'],
			'allowedittrade' => $_G['gp_allowedittradenew'],
			'allowremovereward' => $_G['gp_allowremoverewardnew'],
			'alloweditactivity' => $_G['gp_alloweditactivitynew'],
			'allowstickthread' => $_G['gp_allowstickthreadnew'],
			'allowmodpost' => $_G['gp_allowmodpostnew'],
			'allowbanpost' => $_G['gp_allowbanpostnew'],
			'allowdelpost' => $_G['gp_allowdelpostnew'],
			'allowmassprune' => $_G['gp_allowmassprunenew'],
			'allowrefund' => $_G['gp_allowrefundnew'],
			'allowcensorword' => $_G['gp_allowcensorwordnew'],
			'allowviewip' => $_G['gp_allowviewipnew'],
			'allowbanip' => $_G['gp_allowbanipnew'],
			'allowedituser' => $_G['gp_alloweditusernew'],
			'allowbanuser' => $_G['gp_allowbanusernew'],
			'allowbanvisituser' => $_G['gp_allowbanvisitusernew'],
			'allowmoduser' => $_G['gp_allowmodusernew'],
			'allowpostannounce' => $_G['gp_allowpostannouncenew'],
			'allowclearrecycle' => $_G['gp_allowclearrecyclenew'],
			'allowhighlightthread' => $_G['gp_allowhighlightthreadnew'],
			'allowdigestthread' => $_G['gp_allowdigestthreadnew'],
			'allowrecommendthread' => $_G['gp_allowrecommendthreadnew'],
			'allowbumpthread' => $_G['gp_allowbumpthreadnew'],
			'allowclosethread' => $_G['gp_allowclosethreadnew'],
			'allowmovethread' => $_G['gp_allowmovethreadnew'],
			'allowedittypethread' => $_G['gp_allowedittypethreadnew'],
			'allowstampthread' => $_G['gp_allowstampthreadnew'],
			'allowstamplist' => $_G['gp_allowstamplistnew'],
			'allowcopythread' => $_G['gp_allowcopythreadnew'],
			'allowmergethread' => $_G['gp_allowmergethreadnew'],
			'allowsplitthread' => $_G['gp_allowsplitthreadnew'],
			'allowrepairthread' => $_G['gp_allowrepairthreadnew'],
			'allowwarnpost' => $_G['gp_allowwarnpostnew'],
			'alloweditforum' => $_G['gp_alloweditforumnew'],
			'allowviewlog' => $_G['gp_allowviewlognew'],
			'allowmanagearticle' => $_G['gp_allowmanagearticlenew'],
			'allowaddtopic' => $_G['gp_allowaddtopicnew'],
			'allowmanagetopic' => $_G['gp_allowmanagetopicnew'],
			'allowdiy' => $_G['gp_allowdiynew'],
			'allowstickreply' => $_G['gp_allowstickreplynew'],
			'allowmanagetag' => $_G['gp_allowmanagetagnew'],
			'managefeed' => $_G['gp_managefeednew'],
			'managedoing' => $_G['gp_managedoingnew'],
			'manageshare' => $_G['gp_managesharenew'],
			'manageblog' => $_G['gp_manageblognew'],
			'managealbum' => $_G['gp_managealbumnew'],
			'managecomment' => $_G['gp_managecommentnew'],
			'managemagiclog' => $_G['gp_managemagiclognew'],
			'managereport' => $_G['gp_managereportnew'],
			'managehotuser' => $_G['gp_managehotusernew'],
			'managedefaultuser' => $_G['gp_managedefaultusernew'],
			'managevideophoto' => $_G['gp_managevideophotonew'],
			'managemagic' => $_G['gp_managemagicnew'],
			'manageclick' => $_G['gp_manageclicknew'],
		), "admingid='$_G[gp_id]'");
		}

		updatecache(array('usergroups', 'groupreadaccess', 'admingroups'));

		cpmsg('admingroups_edit_succeed', 'action=admingroup&operation=edit&'.($multiset ? 'multi='.implode(',', $_G['gp_multi']) : 'id='.$_G['gp_id']).'&anchor='.$_G['gp_anchor'], 'succeed');
	}
}

function deletegroupcache($groupidarray) {
	if(!empty($groupidarray) && is_array($groupidarray)) {
		foreach ($groupidarray as $id) {
			if(is_numeric($id) && $id = intval($id)) {
				DB::query("DELETE FROM ".DB::table('common_syscache')." WHERE cname='usergroup_$id'");
				DB::query("DELETE FROM ".DB::table('common_syscache')." WHERE cname='admingroup_$id'");
				@unlink(DISCUZ_ROOT.'./data/cache/cache_usergroup_'.$id.'.php');
				@unlink(DISCUZ_ROOT.'./data/cache/cache_admingroup_'.$id.'.php');
			}
		}
	}
}

?>