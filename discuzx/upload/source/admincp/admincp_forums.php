<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_forums.php 23399 2011-07-13 10:12:39Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$operation = empty($operation) ? 'admin' : $operation;
$fid = getgpc('fid');

if($operation == 'admin') {

	if(!submitcheck('editsubmit')) {
		shownav('forum', 'forums_admin');
		showsubmenu('forums_admin');
		showtips('forums_admin_tips');

		require_once libfile('function/forumlist');
		$forums = str_replace("'", "\'", forumselect(false, 0, 0, 1));

?>
<script type="text/JavaScript">
var forumselect = '<?php echo $forums;?>';
var rowtypedata = [
	[[1, ''], [1,'<input type="text" class="txt" name="newcatorder[]" value="0" />', 'td25'], [5, '<div><input name="newcat[]" value="<?php cplang('forums_admin_add_category_name', null, true);?>" size="20" type="text" class="txt" /><a href="javascript:;" class="deleterow" onClick="deleterow(this)"><?php cplang('delete', null, true);?></a></div>']],
	[[1, ''], [1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [5, '<div class="board"><input name="newforum[{1}][]" value="<?php cplang('forums_admin_add_forum_name', null, true);?>" size="20" type="text" class="txt" /><a href="javascript:;" class="deleterow" onClick="deleterow(this)"><?php cplang('delete', null, true);?></a><select name="newinherited[{1}][]"><option value=""><?php cplang('forums_edit_newinherited', null, true);?></option>' + forumselect + '</select></div>']],
	[[1, ''], [1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [5, '<div class="childboard"><input name="newforum[{1}][]" value="<?php cplang('forums_admin_add_forum_name', null, true);?>" size="20" type="text" class="txt" /><a href="javascript:;" class="deleterow" onClick="deleterow(this)"><?php cplang('delete', null, true);?></a>&nbsp;<label><input name="inherited[{1}][]" type="checkbox" class="checkbox" value="1">&nbsp;<?php cplang('forums_edit_inherited', null, true);?></label></div>']],
];
</script>
<?php
		showformheader('forums');
		echo '<div style="height:30px;line-height:30px;"><a href="javascript:;" onclick="show_all()">'.cplang('show_all').'</a> | <a href="javascript:;" onclick="hide_all()">'.cplang('hide_all').'</a> <input type="text" id="srchforumipt" class="txt" /> <input type="submit" class="btn" value="'.cplang('search').'" onclick="return srchforum()" /></div>';
		showtableheader('');
		showsubtitle(array('', 'display_order', 'forums_admin_name', '', 'forums_moderators', '<a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));

		$forumcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE `status`<>3");

		$query = DB::query("SELECT f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.inheritedmod, ff.moderators, ff.password, ff.redirect
			FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status<>'3'
			ORDER BY f.type<>'group', f.displayorder");

		$groups = $forums = $subs = $fids = $showed = array();
		while($forum = DB::fetch($query)) {
			if($forum['type'] == 'group') {
				$groups[$forum['fid']] = $forum;
			} elseif($forum['type'] == 'sub') {
				$subs[$forum['fup']][] = $forum;
			} else {
				$forums[$forum['fup']][] = $forum;
			}
			$fids[] = $forum['fid'];
		}

		foreach ($groups as $id => $gforum) {
			$toggle = $forumcount > 50 && count($forums[$id]) > 2;
			$showed[] = showforum($gforum, 'group', '', $toggle);
			if(!empty($forums[$id])) {
				foreach ($forums[$id] as $forum) {
					$showed[] = showforum($forum);
					$lastfid = 0;
					if(!empty($subs[$forum['fid']])) {
						foreach ($subs[$forum['fid']] as $sub) {
							$showed[] = showforum($sub, 'sub');
							$lastfid = $sub['fid'];
						}
					}
					showforum($forum, $lastfid, 'lastchildboard');
				}
			}
			showforum($gforum, '', 'lastboard');
		}

		if(count($fids) != count($showed)) {
			foreach($fids as $fid) {
				if(!in_array($fid, $showed)) {
					DB::update('forum_forum', array(
						'fup' => '0',
						'type' => 'forum',
					), "fid='$fid'");
				}
			}
		}

		showforum($gforum, '', 'last');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {
		$usergroups = array();
		$query = DB::query("SELECT groupid, type, creditshigher, creditslower FROM ".DB::table('common_usergroup')."");
		while($group = DB::fetch($query)) {
			$usergroups[$group['groupid']] = $group;
		}

		if(is_array($_G['gp_order'])) {
			foreach($_G['gp_order'] as $fid => $value) {
				DB::update('forum_forum', array(
					'name' => $_G['gp_name'][$fid],
					'displayorder' => $_G['gp_order'][$fid],
				), "fid='$fid'");
			}
		}

		if(is_array($_G['gp_newcat'])) {
			foreach($_G['gp_newcat'] as $key => $forumname) {
				if(empty($forumname)) {
					continue;
				}
				$fid = DB::insert('forum_forum', array('type' => 'group', 'name' => $forumname, 'status' => 1, 'displayorder' => $_G['gp_newcatorder'][$key]), 1);
				DB::insert('forum_forumfield', array('fid' => $fid));
			}
		}

		$table_forum_columns = array('fup', 'type', 'name', 'status', 'displayorder', 'styleid', 'allowsmilies',
			'allowhtml', 'allowbbcode', 'allowimgcode', 'allowanonymous', 'allowpostspecial', 'alloweditrules',
			'alloweditpost', 'modnewposts', 'recyclebin', 'jammer', 'forumcolumns', 'threadcaches', 'disablewatermark', 'disablethumb',
			'autoclose', 'simple', 'allowside', 'allowfeed');
		$table_forumfield_columns = array('fid', 'attachextensions', 'threadtypes', 'viewperm', 'postperm', 'replyperm',
			'getattachperm', 'postattachperm', 'postimageperm');

		if(is_array($_G['gp_newforum'])) {

			foreach($_G['gp_newforum'] as $fup => $forums) {

				$fupforum = get_forum_by_fid($fup);
				if(empty($fupforum)) continue;

				if($fupforum['fup']) {
					$groupforum = get_forum_by_fid($fupforum['fup']);
				} else {
					$groupforum = $fupforum;
				}

				foreach($forums as $key => $forumname) {

					if(empty($forumname)) continue;

					$forum = $forumfields = array();
					$inheritedid = !empty($_G['gp_inherited'][$fup]) ? $fup : (!empty($_G['gp_newinherited'][$fup][$key]) ? $_G['gp_newinherited'][$fup][$key] : '');

					if(!empty($inheritedid)) {

						$forum = get_forum_by_fid($inheritedid);
						$forumfield =  get_forum_by_fid($inheritedid, null, 'forumfield');

						foreach($table_forum_columns as $field) {
							$forumfields[$field] = $forum[$field];
						}

						foreach($table_forumfield_columns as $field) {
							$forumfields[$field] = $forumfield[$field];
						}

					} else {
						$forumfields['allowsmilies'] = $forumfields['allowbbcode'] = $forumfields['allowimgcode'] = 1;
						$forumfields['allowpostspecial'] = 1;
						$forumfields['allowside'] = 0;
						$forumfields['allowfeed'] = 0;
						$forumfields['recyclebin'] = 1;
					}

					$forumfields['fup'] = $fup ? $fup : 0;
					$forumfields['type'] = $fupforum['type'] == 'forum' ? 'sub' : 'forum';
					$forumfields['styleid'] = $groupforum['styleid'];
					$forumfields['name'] = $forumname;
					$forumfields['status'] = 1;
					$forumfields['displayorder'] = $_G['gp_neworder'][$fup][$key];

					$data = array();
					foreach($table_forum_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					$forumfields['fid'] = $fid = DB::insert('forum_forum', $data, 1);

					$data = array();
					$forumfields['threadtypes'] = copy_threadclasses($forumfields['threadtypes'], $fid);
					foreach($table_forumfield_columns as $field) {
						if(isset($forumfields[$field])) {
							$data[$field] = $forumfields[$field];
						}
					}

					DB::insert('forum_forumfield', $data);

					$query = DB::query("SELECT uid, inherited FROM ".DB::table('forum_moderator')." WHERE fid='$fup'");
					while($mod = DB::fetch($query)) {
						if($mod['inherited'] || $fupforum['inheritedmod']) {
							DB::insert('forum_moderator', array('uid' => $mod['uid'], 'fid' => $fid, 'inherited' => 1), 0, 1);
						}
					}
				}
			}
		}


		updatecache('forums');

		cpmsg('forums_update_succeed', 'action=forums', 'succeed');
	}

} elseif($operation == 'moderators' && $fid) {

	if(!submitcheck('modsubmit')) {

		$forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
		shownav('forum', 'forums_moderators_edit');
		showsubmenu(cplang('forums_moderators_edit').' - '.$forum['name']);
		showtips('forums_moderators_tips');
		showformheader("forums&operation=moderators&fid=$fid&");
		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'display_order', 'username', 'usergroups', 'forums_moderators_inherited'));

		$query = DB::query("SELECT a.admingid, u.radminid, u.grouptitle FROM ".DB::table('common_admingroup')." a
			INNER JOIN ".DB::table('common_usergroup')." u ON u.groupid=a.admingid
			WHERE u.radminid>'0'
			ORDER BY u.type, a.admingid");
		$modgroups = array();
		$groupselect = '<select name="newgroup">';
		while($modgroup = DB::fetch($query)) {
			if($modgroup['radminid'] == 3) {
				$groupselect .= '<option value="'.$modgroup['admingid'].'">'.$modgroup['grouptitle'].'</option>';
			}
			$modgroups[$modgroup['admingid']] = $modgroup['grouptitle'];
		}
		$groupselect .= '</select>';

		$query = DB::query("SELECT m.username, m.groupid, mo.* FROM ".DB::table('common_member')." m, ".DB::table('forum_moderator')." mo WHERE mo.fid='$fid' AND m.uid=mo.uid ORDER BY mo.inherited, mo.displayorder");
		while($mod = DB::fetch($query)) {
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				'<input type="checkbox" class="checkbox" name="delete[]" value="'.$mod[uid].'"'.($mod['inherited'] ? ' disabled' : '').' />',
				'<input type="text" class="txt" name="displayordernew['.$mod[uid].']" value="'.$mod[displayorder].'" size="2" />',
				"<a href=\"".ADMINSCRIPT."?mod=forum&action=members&operation=group&uid=$mod[uid]\" target=\"_blank\">$mod[username]</a>",
				$modgroups[$mod['groupid']],
				cplang($mod['inherited'] ? 'yes' : 'no'),
			));
		}

		if($forum['type'] == 'group' || $forum['type'] == 'sub') {
			$checked = $forum['type'] == 'group' ? 'checked' : '';
			$disabled = 'disabled';
		} else {
			$checked = $forum['inheritedmod'] ? 'checked' : '';
			$disabled = '';
		}

		showtablerow('', array('class="td25"', 'class="td28"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newdisplayorder" value="0" size="2" />',
			'<input type="text" class="txt" name="newmoderator" value="" size="20" />',
			$groupselect,
			''
		));

		showsubmit('modsubmit', 'submit', 'del', '<input class="checkbox" type="checkbox" name="inheritedmodnew" value="1" '.$checked.' '.$disabled.' id="inheritedmodnew" /><label for="inheritedmodnew">'.cplang('forums_moderators_inherit').'</label>');
		showtablefooter();
		showformfooter();

	} else {
		$forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
		$inheritedmodnew = $_G['gp_inheritedmodnew'];
		if($forum['type'] == 'group') {
			$inheritedmodnew = 1;
		} elseif($forum['type'] == 'sub') {
			$inheritedmodnew = 0;
		}

		if(!empty($_G['gp_delete']) || $_G['gp_newmoderator'] || (bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {

			$fidarray = $newmodarray = $origmodarray = array();

			if($forum['type'] == 'group') {
				$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type='forum' AND fup='$fid'");
				while($sub = DB::fetch($query)) {
					$fidarray[] = $sub['fid'];
				}
				$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type='sub' AND fup IN ('".implode('\',\'', $fidarray)."')");
				while($sub = DB::fetch($query)) {
					$fidarray[] = $sub['fid'];
				}
			} elseif($forum['type'] == 'forum') {
				$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type='sub' AND fup='$fid'");
				while($sub = DB::fetch($query)) {
					$fidarray[] = $sub['fid'];
				}
			}

			if(is_array($_G['gp_delete'])) {
				foreach($_G['gp_delete'] as $uid) {
					DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE uid='$uid' AND ((fid='$fid' AND inherited='0') OR (fid IN (".dimplode($fidarray).") AND inherited='1'))");
				}

				$excludeuids = 0;
				$deleteuids = '\''.implode('\',\'', $_G['gp_delete']).'\'';
				$query = DB::query("SELECT uid FROM ".DB::table('forum_moderator')." WHERE uid IN ($deleteuids)");
				while($mod = DB::fetch($query)) {
					$excludeuids .= ','.$mod['uid'];
				}

				$usergroups = array();
				$query = DB::query("SELECT groupid, type, radminid, creditshigher, creditslower FROM ".DB::table('common_usergroup')."");
				while($group = DB::fetch($query)) {
					$usergroups[$group['groupid']] = $group;
				}

				$query = DB::query("SELECT uid, groupid, credits FROM ".DB::table('common_member')." WHERE uid IN ($deleteuids) AND uid NOT IN ($excludeuids) AND adminid NOT IN (1,2)");
				while($member = DB::fetch($query)) {
					if($usergroups[$member['groupid']]['type'] == 'special' && $usergroups[$member['groupid']]['radminid'] != 3) {
						$adminidnew = -1;
						$groupidnew = $member['groupid'];
					} else {
						$adminidnew = 0;
						foreach($usergroups as $group) {
							if($group['type'] == 'member' && $member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) {
								$groupidnew = $group['groupid'];
								break;
							}
						}
					}
					DB::update('common_member', array(
						'adminid' => $adminidnew,
						'groupid' => $groupidnew,
					), "uid='$member[uid]'");
				}
			}

			if($_G['gp_newmoderator']) {
				$member = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_newmoderator]'");
				if(!$member) {
					cpmsg_error('members_edit_nonexistence');
				} else {
					$newmodarray[] = $member['uid'];
					DB::update('common_member', array(
						'groupid' => $_G['gp_newgroup'],
					), "uid='$member[uid]' AND adminid NOT IN (1,2,3,4,5,6,7,8,-1)");
					DB::update('common_member', array(
						'adminid' => '3',
					), "uid='$member[uid]' AND adminid NOT IN (1,2)");
					DB::insert('forum_moderator', array(
						'uid' => $member['uid'],
						'fid' => $fid,
						'displayorder' => $_G['gp_newdisplayorder'],
						'inherited' => '0',
					), false, true);
				}
			}

			if((bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {
				$query = DB::query("SELECT uid FROM ".DB::table('forum_moderator')." WHERE fid='$fid' AND inherited='0'");
				while($mod = DB::fetch($query)) {
					$origmodarray[] = $mod['uid'];
					if(!$forum['inheritedmod'] && $inheritedmodnew) {
						$newmodarray[] = $mod['uid'];
					}
				}
				if($forum['inheritedmod'] && !$inheritedmodnew) {
					DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE uid IN ('".implode('\',\'', $origmodarray)."') AND fid IN ('".implode('\',\'', $fidarray)."') AND inherited='1'");
				}
			}

			foreach($newmodarray as $uid) {
				DB::insert('forum_moderator', array(
					'uid' => $uid,
					'fid' => $fid,
					'displayorder' => $_G['gp_newdisplayorder'],
					'inherited' => '0',
				), false, true);

				if($inheritedmodnew) {
					foreach($fidarray as $ifid) {
						DB::insert('forum_moderator', array(
							'uid' => $uid,
							'fid' => $ifid,
							'inherited' => '1',
						), false, true);
					}
				}
			}

			if($forum['type'] == 'group') {
				$inheritedmodnew = 1;
			} elseif($forum['type'] == 'sub') {
				$inheritedmodnew = 0;
			}
			DB::update('forum_forum', array(
				'inheritedmod' => $inheritedmodnew,
			), "fid='$fid'");
		}

		if(is_array($_G['gp_displayordernew'])) {
			foreach($_G['gp_displayordernew'] as $uid => $order) {
				DB::update('forum_moderator', array(
					'displayorder' => $order,
				), "fid='$fid' AND uid='$uid'");
			}
		}

		$fidarray[] = $fid;
		foreach($fidarray as $fid) {
			$moderators = $tab = '';
			$query = DB::query("SELECT m.username FROM ".DB::table('common_member')." m, ".DB::table('forum_moderator')." mo WHERE mo.fid='$fid' AND mo.inherited='0' AND m.uid=mo.uid ORDER BY mo.displayorder");
			while($mod = DB::fetch($query)) {
				$moderators .= $tab.addslashes($mod['username']);
				$tab = "\t";
			}
			DB::update('forum_forumfield', array(
				'moderators' => $moderators,
			), "fid='$fid'");
		}
		cpmsg('forums_moderators_update_succeed', "mod=forum&action=forums&operation=moderators&fid=$fid", 'succeed');

	}

} elseif($operation == 'merge') {
	$source = $_G['gp_source'];
	$target = $_G['gp_target'];
	if(!submitcheck('mergesubmit') || $source == $target) {

		require_once libfile('function/forumlist');
		loadcache('forums');
		$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> ".cplang('select')."</option><option value=\"\">&nbsp;</option>".str_replace('%', '%%', forumselect(FALSE, 0, 0, TRUE)).'</select>';
		shownav('forum', 'forums_merge');
		showsubmenu('forums_merge');
		showformheader('forums&operation=merge');
		showtableheader();
		showsetting('forums_merge_source', '', '', sprintf($forumselect, 'source'));
		showsetting('forums_merge_target', '', '', sprintf($forumselect, 'target'));
		showsubmit('mergesubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE fid IN ('$source', '$target') AND type<>'group'") != 2) {
			cpmsg_error('forums_nonexistence');
		}

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE fup='$source'")) {
			cpmsg_error('forums_merge_source_sub_notnull');
		}

		DB::update('forum_thread', array(
			'fid' => $target,
		), "fid='$source'");
		updatepost(array('fid' => $target), "fid='$source'");

		$sourceforum = DB::fetch_first("SELECT f.threads, f.posts, ff.threadtypes FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.fid='$source'");
		$targetforum = DB::fetch_first("SELECT f.threads, f.posts, ff.threadtypes FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.fid='$target'");
		$sourcethreadtypes = (array)unserialize($sourceforum['threadtypes']);
		$targethreadtypes = (array)unserialize($targetforum['threadtypes']);
		$targethreadtypes['types'] = array_merge((array)$targethreadtypes['types'], (array)$sourcethreadtypes['types']);
		$targethreadtypes['icons'] = array_merge((array)$targethreadtypes['icons'], (array)$sourcethreadtypes['icons']);

		DB::update('forum_forum', array(
			'threads' => $targetforum['threads'] + $sourceforum['threads'],
			'posts' => $targetforum['posts'] + $sourceforum['posts'],
		), "fid='$target'");
		DB::update('forum_forumfield', array(
			'threadtypes' => addslashes(serialize($targethreadtypes))
		), "fid='$target'");
		DB::update('forum_threadclass', array('fid' => $target), "fid='$source'");
		DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid='$source'");
		DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid='$source'");
		DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE fid='$source'");

		my_thread_log('mergeforum', array('fid' => $source, 'otherid' => $target));

		$query = DB::query("SELECT * FROM ".DB::table('forum_access')." WHERE fid='$source'");
		while($access = DB::fetch($query)) {
			DB::insert('forum_access', array('uid' => $access['uid'], 'fid' => $target, 'allowview' => $access['allowview'], 'allowpost' => $access['allowpost'], 'allowreply' => $access['allowreply'], 'allowgetattach' => $access['allowgetattach']), 0, 0, 1);
		}
		DB::query("DELETE FROM ".DB::table('forum_access')." WHERE fid='$source'");

		updatecache('forums');

		cpmsg('forums_merge_succeed', 'action=forums', 'succeed');
	}

} elseif($operation == 'edit') {

	require_once libfile('function/forumlist');
	require_once libfile('function/domain');
	$highlight = getgpc('highlight');
	$anchor = getgpc('anchor');

	list($pluginsetting, $pluginvalue) = get_pluginsetting('forums');

	$multiset = 0;
	if(empty($_G['gp_multi'])) {
		$fids = $fid;
	} else {
		$multiset = 1;
		if(is_array($_G['gp_multi'])) {
			$fids = dimplode($_G['gp_multi']);
		} else {
			$_G['gp_multi'] = explode(',', $_G['gp_multi']);
			array_walk($_G['gp_multi'], 'intval');
			$fids = dimplode($_G['gp_multi']);
		}
	}
	if(count($_G['gp_multi']) == 1) {
		$fids = $_G['gp_multi'][0];
		$multiset = 0;
	}
	if(empty($fids)) {
		cpmsg('forums_edit_nonexistence', 'action=forums&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : '').(!empty($anchor) ? "&anchor=$anchor" : ''), 'form', array(), '<select name="fid">'.forumselect(FALSE, 0, 0, TRUE).'</select>');
	}
	$mforum = array();
	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm', 'postimageperm');

	$query = DB::query("SELECT *, f.fid AS fid FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table('forum_forumfield')." ff USING (fid)
		WHERE f.fid IN ($fids)");

	if(!DB::num_rows($query)) {
		cpmsg('forums_nonexistence', '', 'error');
	} else {
		while($forum = DB::fetch($query)) {
			if(isset($pluginvalue[$forum['fid']])) {
				$forum['plugin'] = $pluginvalue[$forum['fid']];
			}
			$mforum[] = $forum;
		}
	}

	$dactionarray = array();
	$allowthreadtypes = !in_array('threadtypes', $dactionarray);


	$query = DB::query("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='forumkeys'");
	$forumkeys = @unserialize(DB::result($query, 0));

	$rules = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE action IN('reply', 'post', 'digest', 'postattach', 'getattach')");
	while($value = DB::fetch($query)) {
		$rules[$value['rid']] = $value;
	}

	if(!submitcheck('detailsubmit')) {
		$anchor = in_array($_G['gp_anchor'], array('basic', 'extend', 'posts', 'credits', 'threadtypes', 'threadsorts', 'perm', 'plugin')) ? $_G['gp_anchor'] : 'basic';
		shownav('forum', 'forums_edit');

		loadcache('forums');
		$forumselect = '';
		$sgid = 0;
		foreach($_G['cache']['forums'] as $forums) {
			$checked = $fid == $forums['fid'] || in_array($forums['fid'], $_G['gp_multi']);
			if($forums['type'] == 'group') {
				$sgid = $forums['fid'];
				$forumselect .= '</div><em class="cl">'.
					'<span class="right"><input name="checkall_'.$forums['fid'].'" onclick="checkAll(\'value\', this.form, '.$forums['fid'].', \'checkall_'.$forums['fid'].'\')" type="checkbox" class="vmiddle checkbox" /></span>'.
					'<span class="pointer" onclick="sdisplay(\'g_'.$forums['fid'].'\', this)"><img src="static/image/admincp/desc.gif" class="vmiddle" /></span> <span class="pointer" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&switch=yes&fid='.$forums['fid'].'\'">'.$forums['name'].'</span></em><div id="g_'.$forums['fid'].'" style="display:">';
			} elseif($forums['type'] == 'forum') {
				$forumselect .= '<input class="left checkbox ck" chkvalue="'.$sgid.'" name="multi[]" value="'.$forums['fid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/><a class="f'.($checked ? ' current"' : '').'" href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&switch=yes&fid='.$forums['fid'].($mforum[0]['type'] != 'group' ? '&anchor=\'+currentAnchor' : '\'').'+\'&scrolltop=\'+scrollTopBody()">'.$forums['name'].'</a>';
			} elseif($forums['type'] == 'sub') {
				$forumselect .= '<input class="left checkbox ck" chkvalue="'.$sgid.'" name="multi[]" value="'.$forums['fid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/><a class="s'.($checked ? ' current"' : '').'" href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&switch=yes&fid='.$forums['fid'].($mforum[0]['type'] != 'group' ? '&anchor=\'+currentAnchor' : '\'').'+\'&scrolltop=\'+scrollTopBody()">'.$forums['name'].'</a>';
			}
		}
		$forumselect = '<span id="fselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'fselect_menu\').style.top=(parseInt($(\'fselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'fselect_menu\').style.left=(parseInt($(\'fselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.cplang('forums_edit_switch').'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="fselect_menu" class="popupmenu_popup" style="display:none"><div class="fsel"><div>'.$forumselect.'</div></div><div class="cl"><input type="button" class="btn right" onclick="$(\'menuform\').submit()" value="'.cplang('forums_multiedit').'" /></div></div>';

		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'forums', 'operation' => 'edit'));
		if(count($mforum) == 1 && $mforum[0]['type'] == 'group') {
			showsubmenu(cplang('forums_cat_detail').(count($mforum) == 1 ? ' - '.$mforum[0]['name'].'(gid:'.$mforum[0]['fid'].')' : ''), array(), $forumselect);
		} else {
			if($multiset && !in_array($anchor, array('basic', 'extend', 'posts', 'perm', 'plugin'))) {
				$anchor = 'basic';
			}
			showsubmenuanchors(cplang('forums_edit').(count($mforum) == 1 ? ' - '.$mforum[0]['name'].'(fid:'.$mforum[0]['fid'].')' : ''), array(
				array('forums_edit_basic', 'basic', $anchor == 'basic'),
				array('forums_edit_extend', 'extend', $anchor == 'extend'),
				array('forums_edit_posts', 'posts', $anchor == 'posts'),
				array('forums_edit_perm', 'perm', $anchor == 'perm'),
				!$multiset ? array('forums_edit_credits', 'credits', $anchor == 'credits') : array(),
				!$multiset ? array(array('menu' => 'usergroups_edit_other', 'submenu' => array(
					array('forums_edit_threadtypes', 'threadtypes', $anchor == 'threadtypes'),
					array('forums_edit_threadsorts', 'threadsorts', $anchor == 'threadsorts'),
					!$pluginsetting ? array() : array('forums_edit_plugin', 'plugin', $anchor == 'plugin'),
				))) : array(),
				$multiset && $pluginsetting ? array('forums_edit_plugin', 'plugin', $anchor == 'plugin') : array(),
			), $forumselect);
		}
		showformfooter();

		$groups = array();
		$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		while($group = DB::fetch($query)) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groups[$group['type']][] = $group;
		}

		$styleselect = "<select name=\"styleidnew\"><option value=\"0\">$lang[use_default]</option>";
		$query = DB::query("SELECT styleid, name FROM ".DB::table('common_style'));
		while($style = DB::fetch($query)) {
			$styleselect .= "<option value=\"$style[styleid]\" ".
				($style['styleid'] == $mforum[0]['styleid'] ? 'selected="selected"' : NULL).
				">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		if(!$multiset) {
			showtips('forums_edit_tips');
		} else {
			showtips('setting_multi_tips');
		}
		showformheader("forums&operation=edit&fid=$fid&", 'enctype');
		showhiddenfields(array('type' => $mforum[0]['type']));

		if(count($mforum) == 1 && $mforum[0]['type'] == 'group') {
			$mforum[0]['extra'] = unserialize($mforum[0]['extra']);
			showtableheader();
			showsetting('forums_edit_basic_cat_name', 'namenew', $mforum[0]['name'], 'text');
			showsetting('forums_edit_basic_cat_name_color', 'extranew[namecolor]', $mforum[0]['extra']['namecolor'], 'color');
			showsetting('forums_edit_basic_cat_style', '', '', $styleselect);
			showsetting('forums_edit_extend_forum_horizontal', 'forumcolumnsnew', $mforum[0]['forumcolumns'], 'text');
			showsetting('forums_edit_extend_cat_sub_horizontal', 'catforumcolumnsnew', $mforum[0]['catforumcolumns'], 'text');
			if(!empty($_G['setting']['domain']['root']['forum'])) {
				showsetting('forums_edit_extend_domain', '', '', 'http://<input type="text" name="domainnew" class="txt" value="'.$mforum[0]['domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['forum']);
			} else {
				showsetting('forums_edit_extend_domain', 'domainnew', '', 'text', 'disabled');
			}
			showsetting('forums_cat_display', 'statusnew', $mforum[0]['status'], 'radio');
			showtablefooter();
			showtips('setting_seo_forum_tips', 'seo_tips', true, 'setseotips');
			showtableheader();
			showsetting('forums_edit_basic_seotitle', 'seotitlenew', htmlspecialchars($mforum[0]['seotitle']), 'text');
			showsetting('forums_edit_basic_keyword', 'keywordsnew', htmlspecialchars($mforum[0]['keywords']), 'text');
			showsetting('forums_edit_basic_seodescription', 'seodescriptionnew', htmlspecialchars($mforum[0]['seodescription']), 'textarea');
			showsubmit('detailsubmit');
			showtablefooter();

		} else {

			require_once libfile('function/editor');

			if($multiset) {
				$_G['showsetting_multi'] = 0;
				$_G['showsetting_multicount'] = count($mforum);
				foreach($mforum as $forum) {
					$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$forum['name'].'(fid:'.$forum['fid'].')</a>';
				}
			}
			$mfids = array();
			foreach($mforum as $forum) {
				$fid = $forum['fid'];
				$mfids[] = $fid;
				if(!$multiset) {
					$fupselect = "<select name=\"fupnew\">\n";
					$query = DB::query("SELECT fid, type, name, fup FROM ".DB::table('forum_forum')." WHERE fid<>'$fid' AND type<>'sub' AND status<>'3' ORDER BY displayorder");
					while($fup = DB::fetch($query)) {
						$fups[] = $fup;
					}
					if(is_array($fups)) {
						foreach($fups as $forum1) {
							if($forum1['type'] == 'group') {
								$selected = $forum1['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
								$fupselect .= "<option value=\"$forum1[fid]\" $selected>$forum1[name]</option>\n";
								foreach($fups as $forum2) {
									if($forum2['type'] == 'forum' && $forum2['fup'] == $forum1['fid']) {
										$selected = $forum2['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
										$fupselect .= "<option value=\"$forum2[fid]\" $selected>&nbsp; &gt; $forum2[name]</option>\n";
									}
								}
							}
						}
						foreach($fups as $forum0) {
							if($forum0['type'] == 'forum' && $forum0['fup'] == 0) {
								$selected = $forum0['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
								$fupselect .= "<option value=\"$forum0[fid]\" $selected>$forum0[name]</option>\n";
							}
						}
					}
					$fupselect .= '</select>';

					if($forum['threadtypes']) {
						$forum['threadtypes'] = unserialize($forum['threadtypes']);
						$forum['threadtypes']['status'] = 1;
					} else {
						$forum['threadtypes'] = array('status' => 0, 'required' => 0, 'listable' => 0, 'prefix' => 0, 'options' => array());
					}

					if($forum['threadsorts']) {
						$forum['threadsorts'] = unserialize($forum['threadsorts']);
						$forum['threadsorts']['status'] = 1;
					} else {
						$forum['threadsorts'] = array('status' => 0, 'required' => 0, 'listable' => 0, 'prefix' => 0, 'options' => array());
					}

					$typeselect = $sortselect = '';

					$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." ORDER BY displayorder");
					$typeselect = getthreadclasses_html($fid);
					while($type = DB::fetch($query)) {
						$typeselected = array();
						$enablechecked = '';

						$keysort = $type['special'] ? 'threadsorts' : 'threadtypes';
						if(isset($forum[$keysort]['types'][$type['typeid']])) {
							$enablechecked = ' checked="checked"';
						}

						$showtype = TRUE;

						loadcache('threadsort_option_'.$type['typeid']);
						if($type['special'] && !$_G['cache']['threadsort_option_'.$type['typeid']]) {
							$showtype = FALSE;
						}
						if($type['special']) {
							$typeselected[3] = $forum['threadsorts']['show'][$type['typeid']] ? ' checked="checked"' : '';
							$sortselect .= $showtype ? showtablerow('', array('class="td25"'), array(
								'<input type="checkbox" name="threadsortsnew[options][enable]['.$type['typeid'].']" value="1" class="checkbox"'.$enablechecked.' />',
								$type['name'],
								$type['description'],
								"<input class=\"checkbox\" type=\"checkbox\" name=\"threadsortsnew[options][show][{$type[typeid]}]\" value=\"3\" $typeselected[3] />",
								"<input class=\"radio\" type=\"radio\" name=\"threadsortsnew[defaultshow]\" value=\"$type[typeid]\" ".($forum['threadsorts']['defaultshow'] == $type['typeid'] ? 'checked' : '')." />"
							), TRUE) : '';
						}
					}
					$forum['creditspolicy'] = $forum['creditspolicy'] ? unserialize($forum['creditspolicy']) : array();
				}

				if($forum['autoclose']) {
					$forum['autoclosetime'] = abs($forum['autoclose']);
					$forum['autoclose'] = $forum['autoclose'] / abs($forum['autoclose']);
				}

				if($forum['threadplugin']) {
					$forum['threadplugin'] = unserialize($forum['threadplugin']);
				}

				$simplebin = sprintf('%08b', $forum['simple']);
				$forum['defaultorderfield'] = bindec(substr($simplebin, 0, 2));
				$forum['defaultorder'] = ($forum['simple'] & 32) ? 1 : 0;
				$forum['subforumsindex'] = bindec(substr($simplebin, 3, 2));
				$forum['subforumsindex'] = $forum['subforumsindex'] == 0 ? -1 : ($forum['subforumsindex'] == 2 ? 0 : 1);
				$forum['simple'] = $forum['simple'] & 1;
				$forum['modrecommend'] = $forum['modrecommend'] ? unserialize($forum['modrecommend']) : '';
				$forum['formulaperm'] = unserialize($forum['formulaperm']);
				$forum['medal'] = $forum['formulaperm']['medal'];
				$forum['formulapermmessage'] = stripslashes($forum['formulaperm']['message']);
				$forum['formulapermusers'] = $forum['formulaperm']['users'];
				$forum['formulaperm'] = $forum['formulaperm'][0];
				$forum['extra'] = unserialize($forum['extra']);
				$forum['threadsorts']['default'] = $forum['threadsorts']['defaultshow'] ? 1 : 0;

				$_G['multisetting'] = $multiset ? 1 : 0;
				showtagheader('div', 'basic', $anchor == 'basic');
				showtableheader('forums_edit_basic', 'nobottom');
				showsetting('forums_edit_basic_name', 'namenew', $forum['name'], 'text');
				showsetting('forums_edit_base_name_color', 'extranew[namecolor]', $forum['extra']['namecolor'], 'color');
				if(!$multiset) {
					if($forum['icon']) {
						$valueparse = parse_url($forum['icon']);
						if(isset($valueparse['host'])) {
							$forumicon = $forum['icon'];
						} else {
							$forumicon = $_G['setting']['attachurl'].'common/'.$forum['icon'].'?'.random(6);
						}
						$forumiconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$forumicon.'" /><br />';
					}
					showsetting('forums_edit_basic_icon', 'iconnew', $forum['icon'], 'filetext', '', 0, $forumiconhtml);
					showsetting('forums_edit_basic_icon_width', 'extranew[iconwidth]', $forum['extra']['iconwidth'], 'text');
					if($forum['banner']) {
						$valueparse = parse_url($forum['banner']);
						if(isset($valueparse['host'])) {
							$forumbanner = $forum['banner'];
						} else {
							$forumbanner = $_G['setting']['attachurl'].'common/'.$forum['banner'].'?'.random(6);
						}
						$forumbannerhtml = '<label><input type="checkbox" class="checkbox" name="deletebanner" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$forumbanner.'" /><br />';
					}
					showsetting('forums_edit_basic_banner', 'bannernew', $forum['banner'], 'filetext', '', 0, $forumbannerhtml);
				}
				showsetting('forums_edit_basic_display', 'statusnew', $forum['status'], 'radio');
				if(!$multiset) {
					showsetting('forums_edit_basic_up', '', '', $fupselect);
				}
				showsetting('forums_edit_basic_redirect', 'redirectnew', $forum['redirect'], 'text');
				showmultititle();
				showsetting('forums_edit_basic_description', 'descriptionnew', str_replace('&amp;', '&', html2bbcode($forum['description'])), 'textarea');
				showsetting('forums_edit_basic_rules', 'rulesnew', str_replace('&amp;', '&', html2bbcode($forum['rules'])), 'textarea');
				showsetting('forums_edit_basic_keys', 'keysnew', $forumkeys[$fid], 'text');
				if(!empty($_G['setting']['domain']['root']['forum'])) {
					$iname = $multiset ? "multinew[{$_G[showsetting_multi]}][domainnew]" : 'domainnew';
					showsetting('forums_edit_extend_domain', '', '', 'http://<input type="text" name="'.$iname.'" class="txt" value="'.$forum['domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['forum']);
				} elseif(!$multiset) {
					showsetting('forums_edit_extend_domain', 'domainnew', '', 'text', 'disabled');
				}
				showtablefooter();
				if(!$multiset) {
					showtips('setting_seo_forum_tips', 'seo_tips', true, 'setseotips');
				}
				showtableheader();
				showsetting('forums_edit_basic_seotitle', 'seotitlenew', htmlspecialchars($forum['seotitle']), 'text');
				showsetting('forums_edit_basic_keyword', 'keywordsnew', htmlspecialchars($forum['keywords']), 'text');
				showsetting('forums_edit_basic_seodescription', 'seodescriptionnew', htmlspecialchars($forum['seodescription']), 'textarea');
				showtablefooter();
				showtagfooter('div');

				showtagheader('div', 'extend', $anchor == 'extend');
				showtableheader('forums_edit_extend', 'nobottom');
				showsetting('forums_edit_extend_style', '', '', $styleselect);
				if($forum['type'] != 'sub') {
					showsetting('forums_edit_extend_sub_horizontal', 'forumcolumnsnew', $forum['forumcolumns'], 'text');
					showsetting('forums_edit_extend_subforumsindex', array('subforumsindexnew', array(
						array(-1, cplang('default')),
						array(1, cplang('yes')),
						array(0, cplang('no'))
					), 1), $forum['subforumsindex'], 'mradio');
					showsetting('forums_edit_extend_simple', 'simplenew', $forum['simple'], 'radio');
				} else {
					if($_GET['multi']) {
						showsetting('forums_edit_extend_sub_horizontal', '', '', cplang('forums_edit_sub_multi_tips'));
						showsetting('forums_edit_extend_subforumsindex', '', '', cplang('forums_edit_sub_multi_tips'));
						showsetting('forums_edit_extend_simple', '', '', cplang('forums_edit_sub_multi_tips'));
					}
				}
				showsetting('forums_edit_extend_widthauto', array('widthautonew', array(
					array(0, cplang('default')),
					array(-1, cplang('forums_edit_extend_widthauto_-1')),
					array(1, cplang('forums_edit_extend_widthauto_1')),
				), 1), $forum['widthauto'], 'mradio');
				showsetting('forums_edit_extend_picstyle', 'picstylenew', $forum['picstyle'], 'radio');
				showmultititle();
				showsetting('forums_edit_extend_allowside', 'allowsidenew', $forum['allowside'], 'radio');
				showsetting('forums_edit_extend_recommend_top', 'allowglobalsticknew', $forum['allowglobalstick'], 'radio');
				showsetting('forums_edit_extend_defaultorderfield', array('defaultorderfieldnew', array(
					array(0, cplang('forums_edit_extend_order_lastpost')),
					array(1, cplang('forums_edit_extend_order_starttime')),
					array(2, cplang('forums_edit_extend_order_replies')),
					array(3, cplang('forums_edit_extend_order_views'))
				)), $forum['defaultorderfield'], 'mradio');
				showsetting('forums_edit_extend_defaultorder', array('defaultordernew', array(
					array(0, cplang('forums_edit_extend_order_desc')),
					array(1, cplang('forums_edit_extend_order_asc'))
				)), $forum['defaultorder'], 'mradio');
				showsetting('forums_edit_extend_threadcache', 'threadcachesnew', $forum['threadcaches'], 'text');
				showsetting('forums_edit_extend_relatedgroup', 'relatedgroupnew', $forum['relatedgroup'], 'text');
				showsetting('forums_edit_extend_edit_rules', 'alloweditrulesnew', $forum['alloweditrules'], 'radio');
				showmultititle();
				showsetting('forums_edit_extend_recommend', 'modrecommendnew[open]', $forum['modrecommend']['open'], 'radio', '', 1);
				showsetting('forums_edit_extend_recommend_sort', array('modrecommendnew[sort]', array(
					array(1, cplang('forums_edit_extend_recommend_sort_auto')),
					array(0, cplang('forums_edit_extend_recommend_sort_manual')),
					array(2, cplang('forums_edit_extend_recommend_sort_mix')))), $forum['modrecommend']['sort'], 'mradio');
				showsetting('forums_edit_extend_recommend_orderby', array('modrecommendnew[orderby]', array(
					array(0, cplang('forums_edit_extend_recommend_orderby_dateline')),
					array(1, cplang('forums_edit_extend_recommend_orderby_lastpost')),
					array(2, cplang('forums_edit_extend_recommend_orderby_views')),
					array(3, cplang('forums_edit_extend_recommend_orderby_replies')),
					array(4, cplang('forums_edit_extend_recommend_orderby_digest')),
					array(5, cplang('forums_edit_extend_recommend_orderby_recommend')),
					array(6, cplang('forums_edit_extend_recommend_orderby_heats')),
					)), $forum['modrecommend']['orderby'], 'mradio');
				showsetting('forums_edit_extend_recommend_num', 'modrecommendnew[num]', $forum['modrecommend']['num'], 'text');
				showsetting('forums_edit_extend_recommend_imagenum', 'modrecommendnew[imagenum]', $forum['modrecommend']['imagenum'], 'text');
				showsetting('forums_edit_extend_recommend_imagesize', array('modrecommendnew[imagewidth]', 'modrecommendnew[imageheight]'), array(intval($forum['modrecommend']['imagewidth']), intval($forum['modrecommend']['imageheight'])), 'multiply');
				showsetting('forums_edit_extend_recommend_maxlength', 'modrecommendnew[maxlength]', $forum['modrecommend']['maxlength'], 'text');
				showsetting('forums_edit_extend_recommend_cachelife', 'modrecommendnew[cachelife]', $forum['modrecommend']['cachelife'], 'text');
				showsetting('forums_edit_extend_recommend_dateline', 'modrecommendnew[dateline]', $forum['modrecommend']['dateline'], 'text');
				showtablefooter();
				showtagfooter('div');

				showtagheader('div', 'posts', $anchor == 'posts');
				showtableheader('forums_edit_posts', 'nobottom');
				showsetting('forums_edit_posts_modposts', array('modnewpostsnew', array(
					array(0, cplang('none')),
					array(1, cplang('forums_edit_posts_modposts_threads')),
					array(2, cplang('forums_edit_posts_modposts_posts'))
				)), $forum['modnewposts'], 'mradio');
				showsetting('forums_edit_posts_alloweditpost', 'alloweditpostnew', $forum['alloweditpost'], 'radio');
				showsetting('forums_edit_posts_allowappend', 'allowappendnew', $forum['allowappend'], 'radio');
				showsetting('forums_edit_posts_recyclebin', 'recyclebinnew', $forum['recyclebin'], 'radio');
				showmultititle();
				showsetting('forums_edit_posts_html', 'allowhtmlnew', $forum['allowhtml'], 'radio');
				showsetting('forums_edit_posts_bbcode', 'allowbbcodenew', $forum['allowbbcode'], 'radio');
				showsetting('forums_edit_posts_imgcode', 'allowimgcodenew', $forum['allowimgcode'], 'radio');
				showsetting('forums_edit_posts_mediacode', 'allowmediacodenew', $forum['allowmediacode'], 'radio');
				showsetting('forums_edit_posts_smilies', 'allowsmiliesnew', $forum['allowsmilies'], 'radio');
				showsetting('forums_edit_posts_jammer', 'jammernew', $forum['jammer'], 'radio');
				showsetting('forums_edit_posts_anonymous', 'allowanonymousnew', $forum['allowanonymous'], 'radio');
				showmultititle();
				showsetting('forums_edit_posts_disablethumb', 'disablethumbnew', $forum['disablethumb'], 'radio');
				showsetting('forums_edit_posts_disablewatermark', 'disablewatermarknew', $forum['disablewatermark'], 'radio');

				showsetting('forums_edit_posts_allowpostspecial', array('allowpostspecialnew', array(
					cplang('thread_poll'),
					cplang('thread_trade'),
					cplang('thread_reward'),
					cplang('thread_activity'),
					cplang('thread_debate')
				)), $forum['allowpostspecial'], 'binmcheckbox');
				$threadpluginarray = '';
				if(is_array($_G['setting']['threadplugins'])) foreach($_G['setting']['threadplugins'] as $tpid => $data) {
					$threadpluginarray[] = array($tpid, $data['name']);
				}
				if($threadpluginarray) {
					showsetting('forums_edit_posts_threadplugin', array('threadpluginnew', $threadpluginarray), $forum['threadplugin'], 'mcheckbox');
				}
				showsetting('forums_edit_posts_allowspecialonly', 'allowspecialonlynew', $forum['allowspecialonly'], 'radio');
				showmultititle();
				showsetting('forums_edit_posts_autoclose', array('autoclosenew', array(
					array(0, cplang('forums_edit_posts_autoclose_none'), array('autoclose_time' => 'none')),
					array(1, cplang('forums_edit_posts_autoclose_dateline'), array('autoclose_time' => '')),
					array(-1, cplang('forums_edit_posts_autoclose_lastpost'), array('autoclose_time' => ''))
				)), $forum['autoclose'], 'mradio');
				showtagheader('tbody', 'autoclose_time', $forum['autoclose'], 'sub');
				showsetting('forums_edit_posts_autoclose_time', 'autoclosetimenew', $forum['autoclosetime'], 'text');
				showtagfooter('tbody');
				showsetting('forums_edit_posts_attach_ext', 'attachextensionsnew', $forum['attachextensions'], 'text');
				showsetting('forums_edit_posts_allowfeed', 'allowfeednew', $forum['allowfeed'], 'radio');
				showsetting('forums_edit_posts_commentitem', 'commentitemnew', $forum['commentitem'], 'textarea');

				showtablefooter();
				showtagfooter('div');

				if(!$multiset) {
					showtagheader('div', 'credits', $anchor == 'credits');
					showtableheader('forums_edit_credits_policy', 'fixpadding');
					echo '<tr class="header"><th>'.cplang('credits_id').'</th><th>'.cplang('setting_credits_policy_cycletype').'</th><th>'.cplang('setting_credits_policy_rewardnum').'</th><th class="td25">'.cplang('custom').'</th>';
					foreach($_G['setting']['extcredits'] as $i => $extcredit) {
						echo '<th>'.$extcredit['title'].'</th>';
					}
					echo '<th>&nbsp;</th></tr>';

					if(is_array($_G['setting']['extcredits'])) {
						foreach($rules as $rid => $rule) {
							$globalrule = $rule;
							$readonly = $checked = '';
							if(isset($forum['creditspolicy'][$rule['action']])) {
								$rule = $forum['creditspolicy'][$rule['action']];
								$checked = ' checked="checked"';
							} else {
								for($i = 1; $i <= 8; $i++) {
									$rule['extcredits'.$i] = '';
								}
								$readonly = ' readonly="readonly" style="display:none;"';
							}
							$usecustom = '<input type="checkbox" name="usecustom['.$rule['rid'].']" onclick="modifystate(this);" value="1" class="checkbox" '.$checked.' />';
							$tdarr = array($rule['rulename'], $rule['rid'] ? cplang('setting_credits_policy_cycletype_'.$rule['cycletype']) : 'N/A', $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A', $usecustom);

							for($i = 1; $i <= 8; $i++) {
								if($_G['setting']['extcredits'][$i]) {
									array_push($tdarr, '<input type="text" name="creditnew['.$rule['rid'].']['.$i.']" class="txt smtxt" value="'.$rule['extcredits'.$i].'" '.$readonly.' /><span class="sml">('.($globalrule['extcredits'.$i]).')</span>');
								}
							}
							$opstr = '<a href="'.ADMINSCRIPT.'?action=credits&operation=edit&rid='.$rule['rid'].'&fid='.$fid.'" title="" class="act">'.cplang('edit').'</a>';
							array_push($tdarr, $opstr);
							showtablerow('', array_fill(4, count($_G['setting']['extcredits']) + 4, 'width="70"'), $tdarr);
						}

					}
					showtablerow('', 'class="lineheight" colspan="13"', cplang('forums_edit_credits_comment', array('fid' => $fid)));

					showtablefooter();
					print <<<EOF
					<script type="text/javascript">
						function modifystate(custom) {
							var trObj = custom.parentNode.parentNode;
							var inputsObj = trObj.getElementsByTagName('input');
							for(key in inputsObj) {
								var obj = inputsObj[key];
								if(typeof obj == 'object' && obj.type != 'checkbox') {
									obj.value = '';
									obj.readOnly = custom.checked ? false : true;
									obj.style.display = obj.readOnly ? 'none' : '';
								}
							}
						}
					</script>
EOF;
					showtagfooter('div');
				}

				if($allowthreadtypes && !$multiset) {
					$lang_forums_edit_threadtypes_use_cols = cplang('forums_edit_threadtypes_use_cols');
					$lang_forums_edit_threadtypes_use_choice = cplang('forums_edit_threadtypes_use_choice');
					echo <<<EOT
	<script type="text/JavaScript">
		var rowtypedata = [
			[
				[1,'', 'td25'],
				[1,'<input type="text" size="2" name="newdisplayorder[]" value="0" />'],
				[1,'<input type="text" name="newname[]" />'],
				[1,'<input type="text" name="newicon[]" />'],
				[1,'<input type="checkbox" class="checkbox" name="newenable[]" checked="checked" />'],
				[1,'<input type="checkbox" class="checkbox" name="newmoderators[]" value="1" />'],
				[1,'']
			],
		];
	</script>
EOT;
					showtagheader('div', 'threadtypes', $anchor == 'threadtypes');

					showtableheader('forums_edit_threadtypes_config', 'nobottom');
					showsetting('forums_edit_threadtypes_status', array('threadtypesnew[status]', array(
						array(1, cplang('yes'), array('threadtypes_config' => '', 'threadtypes_manage' => '')),
						array(0, cplang('no'), array('threadtypes_config' => 'none', 'threadtypes_manage' => 'none'))
					), TRUE), $forum['threadtypes']['status'], 'mradio');
					showtagheader('tbody', 'threadtypes_config', $forum['threadtypes']['status']);
					showsetting('forums_edit_threadtypes_required', 'threadtypesnew[required]', $forum['threadtypes']['required'], 'radio');
					showsetting('forums_edit_threadtypes_listable', 'threadtypesnew[listable]', $forum['threadtypes']['listable'], 'radio');
					showsetting('forums_edit_threadtypes_prefix',
						array(
							'threadtypesnew[prefix]',
							array(
								array(0, cplang('forums_edit_threadtypes_noprefix')),
								array(1, cplang('forums_edit_threadtypes_textonly')),
								array(2, cplang('forums_edit_threadtypes_icononly')),
							),
						),
						$forum['threadtypes']['prefix'], 'mradio'
					);
					showtagfooter('tbody');
					showtablefooter();

					showtagheader('div', 'threadtypes_manage', $forum['threadtypes']['status']);
					showtableheader('forums_edit_threadtypes', 'noborder fixpadding');
					showsubtitle(array('delete', 'display_order', 'forums_edit_threadtypes_name', 'forums_edit_threadtypes_icon', 'enable', 'forums_edit_threadtypes_moderators'));
					echo $typeselect;
					echo '<tr><td colspan="7"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('threadtype_infotypes_add').'</a></div></td></tr>';
					showtablefooter();
					showtagfooter('div');
					showtagfooter('div');

					showtagheader('div', 'threadsorts', $anchor == 'threadsorts');

					showtableheader('forums_edit_threadsorts', 'nobottom');
					showsetting('forums_edit_threadsorts_status', array('threadsortsnew[status]', array(
						array(1, cplang('yes'), array('threadsorts_config' => '', 'threadsorts_manage' => '')),
						array(0, cplang('no'), array('threadsorts_config' => 'none', 'threadsorts_manage' => 'none'))
					), TRUE), $forum['threadsorts']['status'], 'mradio');
					showtagheader('tbody', 'threadsorts_config', $forum['threadsorts']['status']);
					showsetting('forums_edit_threadtypes_required', 'threadsortsnew[required]', $forum['threadsorts']['required'], 'radio');
					showsetting('forums_edit_threadtypes_prefix', 'threadsortsnew[prefix]', $forum['threadsorts']['prefix'], 'radio');
					showsetting('forums_edit_threadsorts_default', 'threadsortsnew[default]', $forum['threadsorts']['default'], 'radio');
					showtagfooter('tbody');
					showtablefooter();

					showtagheader('div', 'threadsorts_manage', $forum['threadsorts']['status']);
					showtableheader('', 'noborder fixpadding');
					showsubtitle(array('enable', 'forums_edit_threadtypes_name', 'forums_edit_threadtypes_note', 'forums_edit_threadtypes_show', 'forums_edit_threadtypes_defaultshow'));
					echo $sortselect;
					showtablefooter();
					showtagfooter('div');
					showtagfooter('div');
				}

				showtagheader('div', 'perm', $anchor == 'perm');
				showtableheader('forums_edit_perm_forum', 'nobottom');
				showsetting('forums_edit_perm_passwd', 'passwordnew', $forum['password'], 'text');
				showsetting('forums_edit_perm_users', 'formulapermusersnew', dstripslashes($forum['formulapermusers']), 'textarea');
				$colums = array();
				loadcache('medals');
				foreach($_G['cache']['medals'] as $medalid => $medal) {
					$colums[] = array($medalid, $medal['name']);
				}
				showtagheader('tbody', '', $_G['setting']['medalstatus']);
				showsetting('forums_edit_perm_medal', array('medalnew', $colums), $forum['medal'], 'mcheckbox');
				showtagfooter('tbody');
				showtablefooter();

				if(!$multiset) {
					showtableheader('forums_edit_perm_forum', 'noborder fixpadding');
					showsubtitle(array(
						'',
						'<input class="checkbox" type="checkbox" name="chkall1" onclick="checkAll(\'prefix\', this.form, \'^viewperm\', \'chkall1\')" id="chkall1" /><label for="chkall1"><br />'.cplang('forums_edit_perm_view').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall2" onclick="checkAll(\'prefix\', this.form, \'^postperm\', \'chkall2\')" id="chkall2" /><label for="chkall2"><br />'.cplang('forums_edit_perm_post').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall3" onclick="checkAll(\'prefix\', this.form, \'^replyperm\', \'chkall3\')" id="chkall3" /><label for="chkall3"><br />'.cplang('forums_edit_perm_reply').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall4" onclick="checkAll(\'prefix\', this.form, \'^getattachperm\', \'chkall4\')" id="chkall4" /><label for="chkall4"><br />'.cplang('forums_edit_perm_getattach').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall5" onclick="checkAll(\'prefix\', this.form, \'^postattachperm\', \'chkall5\')" id="chkall5" /><label for="chkall5"><br />'.cplang('forums_edit_perm_postattach').'</label>',
						'<input class="checkbox" type="checkbox" name="chkall6" onclick="checkAll(\'prefix\', this.form, \'^postimageperm\', \'chkall6\')" id="chkall6" /><label for="chkall6"><br />'.cplang('forums_edit_perm_postimage').'</label>'
					));

					$spviewgroup = array();
					foreach(array('member', 'special', 'specialadmin', 'system') as $type) {
						$tgroups = is_array($groups[$type]) ? $groups[$type] : array();
						showtablerow('', '', array('<b>'.cplang('usergroups_'.$type).'</b>'));
						foreach($tgroups as $group) {
							if($group['groupid'] != 1) {
								$spviewgroup[] = array($group['groupid'], $group['grouptitle']);
							}
							$colums = array('<input class="checkbox" title="'.cplang('select_all').'" type="checkbox" name="chkallv'.$group['groupid'].'" onclick="checkAll(\'value\', this.form, '.$group['groupid'].', \'chkallv'.$group['groupid'].'\')" id="chkallv_'.$group['groupid'].'" /><label for="chkallv_'.$group['groupid'].'"> '.$group['grouptitle'].'</label>');
							foreach($perms as $perm) {
								$checked = strstr($forum[$perm], "\t$group[groupid]\t") ? 'checked="checked"' : NULL;
								$colums[] = '<input class="checkbox" type="checkbox" name="'.$perm.'[]" value="'.$group['groupid'].'" chkvalue="'.$group['groupid'].'" '.$checked.'>';
							}
							showtablerow('', array('width="21%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="16%"', 'width="13%"', 'width="13%"'), $colums);
						}
					}
					$showverify = true;
					foreach($_G['setting']['verify'] as $vid => $verify) {
						if($verify['available']) {
							if($showverify) {
								showtablerow('', '', array('<b>'.$lang['forums_edit_perm_verify'].'</b>'));
								$showverify = false;
							}

							$colums = array('<input class="checkbox" title="'.cplang('select_all').'" type="checkbox" name="chkallverify'.$vid.'" onclick="checkAll(\'value\', this.form, \'verify'.$vid.'\', \'chkallverify'.$vid.'\')" id="chkallverify_'.$vid.'" /><label for="chkallverify_'.$vid.'"> '.$verify['title'].'</label>');
							foreach($perms as $perm) {
								$checked = strstr($forum[$perm], "\tv$vid\t") ? 'checked="checked"' : NULL;
								$colums[] = '<input class="checkbox" type="checkbox" name="'.$perm.'[]" value="v'.$vid.'" chkvalue="verify'.$vid.'" '.$checked.'>';
							}
							showtablerow('', array('width="21%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="13%"', 'width="13%"'), $colums);
						}
					}
					showtablerow('', 'class="lineheight" colspan="6"', cplang('forums_edit_perm_forum_comment'));
					showtablefooter();

					showtableheader('forums_edit_perm_formula', 'fixpadding');
					$formulareplace .= '\'<u>'.cplang('setting_credits_formula_digestposts').'</u>\',\'<u>'.cplang('setting_credits_formula_posts').'</u>\'';

	?>
	<script type="text/JavaScript">

		function isUndefined(variable) {
			return typeof variable == 'undefined' ? true : false;
		}

		function insertunit(text, textend) {
			$('formulapermnew').focus();
			textend = isUndefined(textend) ? '' : textend;
			if(!isUndefined($('formulapermnew').selectionStart)) {
				var opn = $('formulapermnew').selectionStart + 0;
				if(textend != '') {
					text = text + $('formulapermnew').value.substring($('formulapermnew').selectionStart, $('formulapermnew').selectionEnd) + textend;
				}
				$('formulapermnew').value = $('formulapermnew').value.substr(0, $('formulapermnew').selectionStart) + text + $('formulapermnew').value.substr($('formulapermnew').selectionEnd);
			} else if(document.selection && document.selection.createRange) {
				var sel = document.selection.createRange();
				if(textend != '') {
					text = text + sel.text + textend;
				}
				sel.text = text.replace(/\r?\n/g, '\r\n');
				sel.moveStart('character', -strlen(text));
			} else {
				$('formulapermnew').value += text;
			}
			formulaexp();
		}

		var formulafind = new Array('digestposts', 'posts');
		var formulareplace = new Array(<?php echo $formulareplace?>);
		function formulaexp() {
			var result = $('formulapermnew').value;
	<?php

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : cplang('setting_credits_formula_extcredits').$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.str_replace("'", "\'", $extcredittitle).'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="insertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		$profilefields = '';
		$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND unchangeable='1'");
		while($profilefield = DB::fetch($query)) {
			echo 'result = result.replace(/'.$profilefield['fieldid'].'/g, \'<u>'.str_replace("'", "\'", $profilefield['title']).'</u>\');';
			$profilefields .= '<a href="###" onclick="insertunit(\' '.$profilefield['fieldid'].' \')">&nbsp;'.$profilefield['title'].'&nbsp;</a>&nbsp;';
		}

		echo 'result = result.replace(/regdate/g, \'<u>'.cplang('forums_edit_perm_formula_regdate').'</u>\');';
		echo 'result = result.replace(/regday/g, \'<u>'.cplang('forums_edit_perm_formula_regday').'</u>\');';
		echo 'result = result.replace(/regip/g, \'<u>'.cplang('forums_edit_perm_formula_regip').'</u>\');';
		echo 'result = result.replace(/lastip/g, \'<u>'.cplang('forums_edit_perm_formula_lastip').'</u>\');';
		echo 'result = result.replace(/buyercredit/g, \'<u>'.cplang('forums_edit_perm_formula_buyercredit').'</u>\');';
		echo 'result = result.replace(/sellercredit/g, \'<u>'.cplang('forums_edit_perm_formula_sellercredit').'</u>\');';
		echo 'result = result.replace(/digestposts/g, \'<u>'.cplang('setting_credits_formula_digestposts').'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.cplang('setting_credits_formula_posts').'</u>\');';
		echo 'result = result.replace(/threads/g, \'<u>'.cplang('setting_credits_formula_threads').'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.cplang('setting_credits_formula_oltime').'</u>\');';
		echo 'result = result.replace(/and/g, \'&nbsp;&nbsp;<b>'.cplang('forums_edit_perm_formula_and').'</b>&nbsp;&nbsp;\');';
		echo 'result = result.replace(/or/g, \'&nbsp;&nbsp;<b>'.cplang('forums_edit_perm_formula_or').'</b>&nbsp;&nbsp;\');';
		echo 'result = result.replace(/>=/g, \'&ge;\');';
		echo 'result = result.replace(/<=/g, \'&le;\');';
		echo 'result = result.replace(/==/g, \'=\');';

	?>
			$('formulapermexp').innerHTML = result;
		}
	</script>
	<tr><td colspan="2"><div class="extcredits">
	<?php echo $extcreditsbtn;?>
	<a href="###" onclick="insertunit(' regdate ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regdate')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' regday ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regday')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' regip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_regip')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' lastip ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_lastip')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' buyercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_buyercredit')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' sellercredit ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_sellercredit')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' digestposts ')"><?php echo cplang('forums_edit_perm_formula_digestposts')?></a>&nbsp;
	<a href="###" onclick="insertunit(' posts ')"><?php echo cplang('forums_edit_perm_formula_posts')?></a>&nbsp;
	<a href="###" onclick="insertunit(' threads ')"><?php echo cplang('forums_edit_perm_formula_threads')?></a>&nbsp;
	<a href="###" onclick="insertunit(' oltime ')"><?php echo cplang('forums_edit_perm_formula_oltime')?></a>&nbsp;
	<a href="###" onclick="insertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' > ')">&nbsp;>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' >= ')">&nbsp;>=&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' < ')">&nbsp;<&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' <= ')">&nbsp;<=&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' == ')">&nbsp;=&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' != ')">&nbsp;!=&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' and ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_and')?>&nbsp;</a>&nbsp;
	<a href="###" onclick="insertunit(' or ')">&nbsp;<?php echo cplang('forums_edit_perm_formula_or')?>&nbsp;</a>&nbsp;<br />
	<?php echo $profilefields;?>


	<div id="formulapermexp" class="margintop marginbot diffcolor2"><?php echo $formulapermexp?></div>
	</div>
	<textarea name="formulapermnew" id="formulapermnew" class="marginbot" style="width:80%" rows="3" onkeyup="formulaexp()"><?php echo dhtmlspecialchars($forum['formulaperm'])?></textarea>
	<script type="text/JavaScript">formulaexp()</script>
	<br /><span class="smalltxt"><?php cplang('forums_edit_perm_formula_comment', null, true);?></span>
	</td></tr>
	<?php

					showtablefooter();
					showtableheader('', 'noborder fixpadding');
					$forum['spviewperm'] = explode("\t", $forum['spviewperm']);
					showsetting('forums_edit_perm_spview', array('spviewpermnew', $spviewgroup), $forum['spviewperm'], 'mcheckbox');
					showsetting('forums_edit_perm_formulapermmessage', 'formulapermmessagenew', $forum['formulapermmessage'], 'textarea');
					showtablefooter();

				}
				if($pluginsetting) {
					showtagfooter('div');
					showtagheader('div', 'plugin', $anchor == 'plugin');
					showtableheader('', 'noborder fixpadding');
					foreach($pluginsetting as $setting) {
						showtitle($setting['name']);
						foreach($setting['setting'] as $varid => $var) {
							if($var['type'] != 'select') {
								showsetting($var['title'], 'pluginnew['.$varid.']', $forum['plugin'][$varid], $var['type'], '', 0, $var['description']);
							} else {
								showsetting($var['title'], array('pluginnew['.$varid.']', $var['select']), $forum['plugin'][$varid], $var['type'], '', 0, $var['description']);
							}
						}
					}
					showtablefooter();
				}

				showtagfooter('div');

				showtableheader('', 'notop');
				showsubmit('detailsubmit', 'submit');
				showtablefooter();
				$_G['showsetting_multi']++;
			}}

		if($_G['showsetting_multicount'] > 1) {
			showhiddenfields(array('multi' => implode(',', $mfids)));
			showmulti();
		}

		showformfooter();

	} else {

		if(!$multiset) {
			$_G['gp_multinew'] = array(0 => array('single' => 1));
		}
		$pluginvars = array();
		require_once libfile('function/delete');
		foreach($_G['gp_multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_G['gp_'.$key] = $value;
			}
			$fid = $_G['gp_multi'][$k];
		}
		$forum = $mforum[$k];

		if(strlen($_G['gp_namenew']) > 50) {
			cpmsg('forums_name_toolong', '', 'error');
		}

		if(!$multiset) {
			if(!checkformulaperm($_G['gp_formulapermnew'])) {
				cpmsg('forums_formulaperm_error', '', 'error');
			}

			$formulapermary[0] = $_G['gp_formulapermnew'];
			$formulapermary[1] = preg_replace(
				array("/(digestposts|posts|threads|oltime|extcredits[1-8])/", "/(regdate|regday|regip|lastip|buyercredit|sellercredit|field\d+)/"),
				array("getuserprofile('\\1')", "\$memberformula['\\1']"),
				$_G['gp_formulapermnew']);
			$formulapermary['message'] = $_G['gp_formulapermmessagenew'];
		} else {
			$formulapermary = unserialize($forum['formulaperm']);
		}
		$formulapermary['medal'] = $_G['gp_medalnew'];
		$formulapermary['users'] = $_G['gp_formulapermusersnew'];
		$_G['gp_formulapermnew'] = addslashes(serialize($formulapermary));

		$domain = '';
		if(!empty($_G['gp_domainnew']) && !empty($_G['setting']['domain']['root']['forum'])) {
			$domain = strtolower(trim($_G['gp_domainnew']));
		}
		require_once libfile('function/discuzcode');
		if($_G['gp_type'] == 'group') {
			if($_G['gp_namenew']) {
				$newstyleid = intval($_G['gp_styleidnew']);
				$forumcolumnsnew = $_G['gp_forumcolumnsnew'] > 1 ? intval($_G['gp_forumcolumnsnew']) : 0;
				$catforumcolumnsnew = $_G['gp_catforumcolumnsnew'] > 1 ? intval($_G['gp_catforumcolumnsnew']) : 0;
				$descriptionnew = addslashes(preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode(dstripslashes($_G['gp_descriptionnew']), 1, 0, 0, 0, 1, 1, 0, 0, 1)));
				if(!empty($_G['setting']['domain']['root']['forum'])) {
					deletedomain($fid, 'subarea');
					if(!empty($domain)) {
						domaincheck($domain, $_G['setting']['domain']['root']['forum'], 1, 0);
						DB::insert('common_domain', array('domain' => $domain, 'domainroot' => $_G['setting']['domain']['root']['forum'], 'id' => $fid, 'idtype' => 'subarea'));
					}
				}
				DB::update('forum_forum', array(
					'name' => $_G['gp_namenew'],
					'forumcolumns' => $forumcolumnsnew,
					'catforumcolumns' => $catforumcolumnsnew,
					'domain' => $domain,
					'status' => intval($_G['gp_statusnew']),
					'styleid' => $newstyleid,
				), "fid='$fid'");

				$extranew = is_array($_G['gp_extranew']) ? $_G['gp_extranew'] : array();
				$extranew = serialize($extranew);
				DB::update('forum_forumfield', array(
					'extra' => $extranew,
					'description' => $descriptionnew,
					'seotitle' => $_G['gp_seotitlenew'],
					'keywords' => $_G['gp_keywordsnew'],
					'seodescription' => $_G['gp_seodescriptionnew'],
				), "fid='$fid'");

				loadcache('forums');
				$subfids = array();
				get_subfids($fid);

				if($newstyleid != $mforum[0]['styleid'] && !empty($subfids)) {
					DB::update('forum_forum', array(
						'styleid' => $newstyleid,
					), "fid IN (".dimplode($subfids).")");
				}

				updatecache('forums');

				cpmsg('forums_edit_succeed', 'action=forums', 'succeed');
			} else {
				cpmsg('forums_edit_name_invalid', '', 'error');
			}

		} else {
			$extensionarray = array();
			foreach(explode(',', $_G['gp_attachextensionsnew']) as $extension) {
				if($extension = trim($extension)) {
					$extensionarray[] = $extension;
				}
			}
			$_G['gp_attachextensionsnew'] = implode(', ', $extensionarray);

			foreach($perms as $perm) {
				$_G['gp_'.$perm.'new'] = is_array($_G['gp_'.$perm]) && !empty($_G['gp_'.$perm]) ? "\t".implode("\t", $_G['gp_'.$perm])."\t" : '';
			}

			$fupadd = '';
			$forumdata = $forumfielddata = array();
			if($_G['gp_fupnew'] != $forum['fup'] && !$multiset) {
				$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE fup='$fid'");
				if(DB::num_rows($query)) {
					cpmsg('forums_edit_sub_notnull', '', 'error');
				}

				$fup = DB::fetch_first("SELECT fid, type, inheritedmod FROM ".DB::table('forum_forum')." WHERE fid='{$_G['gp_fupnew']}'");

				$fupadd = ", type='".($fup['type'] == 'forum' ? 'sub' : 'forum')."', fup='$fup[fid]'";
				$forumdata['type'] = $fup['type'] == 'forum' ? 'sub' : 'forum';
				$forumdata['fup'] = $fup['fid'];
				DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE fid='$fid' AND inherited='1'");
				$query = DB::query("SELECT * FROM ".DB::table('forum_moderator')." WHERE fid='{$_G['gp_fupnew']}' ".($fup['inheritedmod'] ? '' : "AND inherited='1'"));
				while($mod = DB::fetch($query)) {
					DB::query("REPLACE INTO ".DB::table('forum_moderator')." (uid, fid, displayorder, inherited)
						VALUES ('$mod[uid]', '$fid', '0', '1')");
				}

				$moderators = $tab = '';
				$query = DB::query("SELECT m.username FROM ".DB::table('common_member')." m, ".DB::table('forum_moderator')." mo WHERE mo.fid='$fid' AND mo.inherited='0' AND m.uid=mo.uid ORDER BY mo.displayorder");
				while($mod = DB::fetch($query)) {
					$moderators .= $tab.addslashes($mod['username']);
					$tab = "\t";
				}
				DB::update('forum_forumfield', array(
					'moderators' => $moderators,
				), "fid='$fid'");
			}

			$allowpostspecialtrade = intval($_G['gp_allowpostspecialnew'][2]);
			$_G['gp_allowpostspecialnew'] = bindec(intval($_G['gp_allowpostspecialnew'][6]).intval($_G['gp_allowpostspecialnew'][5]).intval($_G['gp_allowpostspecialnew'][4]).intval($_G['gp_allowpostspecialnew'][3]).intval($_G['gp_allowpostspecialnew'][2]).intval($_G['gp_allowpostspecialnew'][1]));
			$allowspecialonlynew = $_G['gp_allowpostspecialnew'] || $_G['setting']['threadplugins'] && $_G['gp_threadpluginnew'] ? $_G['gp_allowspecialonlynew'] : 0;
			$forumcolumnsnew = $_G['gp_forumcolumnsnew'] > 1 ? intval($_G['gp_forumcolumnsnew']) : 0;
			$threadcachesnew = max(0, min(100, intval($_G['gp_threadcachesnew'])));
			$subforumsindexnew = $_G['gp_subforumsindexnew'] == -1 ? 0 : ($_G['gp_subforumsindexnew'] == 0 ? 2 : 1);
			$_G['gp_simplenew'] = isset($_G['gp_simplenew']) ? $_G['gp_simplenew'] : 0;
			$simplenew = bindec(sprintf('%02d', decbin($_G['gp_defaultorderfieldnew'])).$_G['gp_defaultordernew'].sprintf('%02d', decbin($subforumsindexnew)).'00'.$_G['gp_simplenew']);
			$allowglobalsticknew = $_G['gp_allowglobalsticknew'] ? 1 : 0;

			if(!empty($_G['setting']['domain']['root']['forum'])) {
				deletedomain($fid, 'forum');
				if(!empty($domain)) {
					domaincheck($domain, $_G['setting']['domain']['root']['forum'], 1, 0);
					DB::insert('common_domain', array('domain' => $domain, 'domainroot' => addslashes($_G['setting']['domain']['root']['forum']), 'id' => $fid, 'idtype' => 'forum'));
				}
			}
			$forumdata = array_merge($forumdata, array(
				'status' => $_G['gp_statusnew'],
				'name' => $_G['gp_namenew'],
				'styleid' => $_G['gp_styleidnew'],
				'alloweditpost' => $_G['gp_alloweditpostnew'],
				'allowappend' => $_G['gp_allowappendnew'],
				'allowpostspecial' => $_G['gp_allowpostspecialnew'],
				'allowspecialonly' => $allowspecialonlynew,
				'allowhtml' => $_G['gp_allowhtmlnew'],
				'allowbbcode' => $_G['gp_allowbbcodenew'],
				'allowimgcode' => $_G['gp_allowimgcodenew'],
				'allowmediacode' => $_G['gp_allowmediacodenew'],
				'allowsmilies' => $_G['gp_allowsmiliesnew'],
				'alloweditrules' => $_G['gp_alloweditrulesnew'],
				'allowside' => $_G['gp_allowsidenew'],
				'modnewposts' => $_G['gp_modnewpostsnew'],
				'recyclebin' => $_G['gp_recyclebinnew'],
				'jammer' => $_G['gp_jammernew'],
				'allowanonymous' => $_G['gp_allowanonymousnew'],
				'forumcolumns' => $forumcolumnsnew,
				'catforumcolumns' => $catforumcolumnsnew,
				'threadcaches' => $threadcachesnew,
				'simple' => $simplenew,
				'allowglobalstick' => $allowglobalsticknew,
				'disablethumb' => $_G['gp_disablethumbnew'],
				'disablewatermark' => $_G['gp_disablewatermarknew'],
				'autoclose' => intval($_G['gp_autoclosenew'] * $_G['gp_autoclosetimenew']),
				'allowfeed' => $_G['gp_allowfeednew'],
				'domain' => $domain,
			));
			DB::update('forum_forum', $forumdata, "fid='$fid'");

			$query = DB::query("SELECT fid FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
			if(!(DB::num_rows($query))) {
				DB::insert('forum_forumfield', array('fid' => $fid));
			}

			if(!$multiset) {
				$creditspolicynew = array();
				$creditspolicy = $forum['creditspolicy'] ? unserialize($forum['creditspolicy']) : array();
				foreach($_G['gp_creditnew'] as $rid => $rule) {
					$creditspolicynew[$rules[$rid]['action']] = isset($creditspolicy[$rules[$rid]['action']]) ? $creditspolicy[$rules[$rid]['action']] : $rules[$rid];
					$usedefault = $_G['gp_usecustom'][$rid] ? false : true;

					if(!$usedefault) {
						foreach($rule as $i => $v) {
							$creditspolicynew[$rules[$rid]['action']]['extcredits'.$i] = is_numeric($v) ? intval($v) : 0;
						}
					}

					$cpfids = explode(',', $rules[$rid]['fids']);
					$cpfidsnew = array();
					foreach($cpfids as $cpfid) {
						if(!$cpfid) {
							continue;
						}
						if($cpfid != $fid) {
							$cpfidsnew[] = $cpfid;
						}
					}
					if(!$usedefault) {
						$cpfidsnew[] = $fid;
						$creditspolicynew[$rules[$rid]['action']]['fids'] = $rules[$rid]['fids'] = implode(',', $cpfidsnew);
					} else {
						$rules[$rid]['fids'] = implode(',', $cpfidsnew);
						unset($creditspolicynew[$rules[$rid]['action']]);
					}
					DB::update('common_credit_rule', array('fids' => $rules[$rid]['fids']), array('rid' => $rid));
				}
				$forumfielddata = array();
				$forumfielddata['creditspolicy'] = addslashes(serialize($creditspolicynew));

				$threadtypesnew = $_G['gp_threadtypesnew'];
				$threadtypesnew['types'] = $threadtypes['special'] = $threadtypes['show'] = array();
				$threadsortsnew['types'] = $threadsorts['special'] = $threadsorts['show'] = array();

				if($allowthreadtypes) {
					if(is_array($_G['gp_newname']) && $_G['gp_newname']) {
						$newname = array_unique($_G['gp_newname']);
						if($newname) {
							foreach($newname as $key => $val) {
								$newname[$key] = $val = strip_tags(trim(str_replace(array("'", "\""), array(), $val)), "<font><span><b><strong>");
								if($_G['gp_newenable'][$key] && $val) {
									$newtypeid = DB::result_first("SELECT typeid FROM ".DB::table('forum_threadclass')." WHERE fid='$fid' AND name='$val'");
									if(!$newtypeid) {
										$threadtypes_newdisplayorder = intval($_G['gp_newdisplayorder'][$key]);
										$threadtypes_newicon = trim($_G['gp_newicon'][$key]);
										$newtypeid = DB::insert('forum_threadclass', array('fid' => $fid, 'name' => $val, 'displayorder' => $threadtypes_newdisplayorder, 'icon' => $threadtypes_newicon, 'moderators' => intval($_G['gp_newmoderators'][$key])), 1);
									}
									$threadtypesnew['options']['name'][$newtypeid] = $val;
									$threadtypesnew['options']['icon'][$newtypeid] = $threadtypes_newicon;
									$threadtypesnew['options']['displayorder'][$newtypeid] = $threadtypes_newdisplayorder;
									$threadtypesnew['options']['enable'][$newtypeid] = 1;
									$threadtypesnew['options']['moderators'][$newtypeid] = $_G['gp_newmoderators'][$key];
								}
							}
						}
						$threadtypesnew['status'] = 1;
					} else {
						$newname = array();
					}
					if($threadtypesnew['status']) {
						if(is_array($threadtypesnew['options']) && $threadtypesnew['options']) {
							if(!empty($threadtypesnew['options']['enable'])) {
								$typeids = dimplode(array_keys($threadtypesnew['options']['enable']));
							} else {
								$typeids = '0';
							}
							$query = DB::query("SELECT * FROM ".DB::table('forum_threadclass')." WHERE typeid IN ($typeids) ORDER BY displayorder");
							while($type = DB::fetch($query)) {
								if($threadtypesnew['options']['name'][$type['typeid']] != $type['name'] ||
									$threadtypesnew['options']['displayorder'][$type['typeid']] != $type['displayorder'] ||
									$threadtypesnew['options']['icon'][$type['typeid']] != $type['icon'] ||
									$threadtypesnew['options']['moderators'][$type['typeid']] != $type['moderators']) {
									$threadtypesnew['options']['name'][$type['typeid']] = strip_tags(trim(str_replace(array("'", "\""), array(), $threadtypesnew['options']['name'][$type['typeid']])), "<font><span><b><strong>");
									DB::update('forum_threadclass', array(
										'name' => $threadtypesnew['options']['name'][$type['typeid']],
										'displayorder' => $threadtypesnew['options']['displayorder'][$type['typeid']],
										'icon' => $threadtypesnew['options']['icon'][$type['typeid']],
										'moderators' => $threadtypesnew['options']['moderators'][$type['typeid']],
									), "typeid='{$type['typeid']}'");
								}
							}
							if(!empty($threadtypesnew['options']['delete'])) {
								$threadtypes_deleteids = dimplode($threadtypesnew['options']['delete']);
								DB::query("DELETE FROM ".DB::table('forum_threadclass')." WHERE `typeid` IN ($threadtypes_deleteids)");
							}
						}
					} else {
						$threadtypesnew = '';
					}
					if($threadtypesnew && $typeids) {
						$query = DB::query("SELECT * FROM ".DB::table('forum_threadclass')." WHERE typeid IN ($typeids) ORDER BY displayorder");
						while($type = DB::fetch($query)) {
							if($threadtypesnew['options']['enable'][$type['typeid']]) {
								$threadtypesnew['types'][$type['typeid']] = $threadtypesnew['options']['name'][$type['typeid']];
							}
							$threadtypesnew['icons'][$type['typeid']] = trim($threadtypesnew['options']['icon'][$type['typeid']]);
							$threadtypesnew['moderators'][$type['typeid']] = $threadtypesnew['options']['moderators'][$type['typeid']];
						}
						$threadtypesnew = $threadtypesnew['types'] ? addslashes(serialize(array
							(
							'required' => (bool)$threadtypesnew['required'],
							'listable' => (bool)$threadtypesnew['listable'],
							'prefix' => $threadtypesnew['prefix'],
							'types' => $threadtypesnew['types'],
							'icons' => $threadtypesnew['icons'],
							'moderators' => $threadtypesnew['moderators'],
							))) : '';
					}
					$forumfielddata['threadtypes'] = $threadtypesnew;

					$threadsortsnew = $_G['gp_threadsortsnew'];
					if($threadsortsnew['status']) {
						if(is_array($threadsortsnew['options']) && $threadsortsnew['options']) {
							if(!empty($threadsortsnew['options']['enable'])) {
								$sortids = dimplode(array_keys($threadsortsnew['options']['enable']));
							} else {
								$sortids = '0';
							}

							$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." WHERE typeid IN ($sortids) ORDER BY displayorder");
							while($sort = DB::fetch($query)) {
								if($threadsortsnew['options']['enable'][$sort['typeid']]) {
									$threadsortsnew['types'][$sort['typeid']] = $sort['name'];
								}
								$threadsortsnew['expiration'][$sort['typeid']] = $sort['expiration'];
								$threadsortsnew['description'][$sort['typeid']] = $sort['description'];
								$threadsortsnew['show'][$sort['typeid']] = $threadsortsnew['options']['show'][$sort['typeid']] ? 1 : 0;
							}
						}

						if($threadsortsnew['default'] && !$threadsortsnew['defaultshow']) {
							cpmsg('forums_edit_threadsort_nonexistence', '', 'error');
						}

						$threadsortsnew = $threadsortsnew['types'] ? addslashes(serialize(array
							(
							'required' => (bool)$threadsortsnew['required'],
							'prefix' => (bool)$threadsortsnew['prefix'],
							'types' => $threadsortsnew['types'],
							'show' => $threadsortsnew['show'],
							'expiration' => $threadsortsnew['expiration'],
							'description' => $threadsortsnew['description'],
							'defaultshow' => $threadsortsnew['default'] ? $threadsortsnew['defaultshow'] : '',
							'templatelist' => $threadsortsnew['templatelist'],
							))) : '';
					} else {
						$threadsortsnew = '';
					}

					$forumfielddata['threadsorts'] = $threadsortsnew;

				}
			}

			$threadpluginnew = addslashes(serialize($_G['gp_threadpluginnew']));
			$modrecommendnew = $_G['gp_modrecommendnew'];
			$modrecommendnew['num'] = $modrecommendnew['num'] ? intval($modrecommendnew['num']) : 10;
			$modrecommendnew['cachelife'] = intval($modrecommendnew['cachelife']);
			$modrecommendnew['maxlength'] = $modrecommendnew['maxlength'] ? intval($modrecommendnew['maxlength']) : 0;
			$modrecommendnew['dateline'] = $modrecommendnew['dateline'] ? intval($modrecommendnew['dateline']) : 0;
			$modrecommendnew['imagenum'] = $modrecommendnew['imagenum'] ? intval($modrecommendnew['imagenum']) : 0;
			$modrecommendnew['imagewidth'] = $modrecommendnew['imagewidth'] ? intval($modrecommendnew['imagewidth']) : 300;
			$modrecommendnew['imageheight'] = $modrecommendnew['imageheight'] ? intval($modrecommendnew['imageheight']): 250;
			$modrecommendnew = $modrecommendnew && is_array($modrecommendnew) ? addslashes(serialize($modrecommendnew)) : '';
			$descriptionnew = addslashes(preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode(dstripslashes($_G['gp_descriptionnew']), 1, 0, 0, 0, 1, 1, 0, 0, 1)));
			$rulesnew = addslashes(preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode(dstripslashes($_G['gp_rulesnew']), 1, 0, 0, 0, 1, 1, 0, 0, 1)));
			$extranew = is_array($_G['gp_extranew']) ? $_G['gp_extranew'] : array();
			$forum['extra'] = unserialize($forum['extra']);
			$forum['extra']['namecolor'] = $extranew['namecolor'];

			if(!$multiset) {
				if($_FILES['bannernew']) {
					$bannernew = upload_icon_banner($forum, $_FILES['bannernew'], 'banner');
				} else {
					$bannernew = $_G['gp_bannernew'];
				}
				if($bannernew) {
					$forumfielddata['banner'] = $bannernew;
				}
				if($_G['gp_deletebanner'] && $forum['banner']) {
					$valueparse = parse_url($forum['banner']);
					if(!isset($valueparse['host'])) {
						@unlink($_G['setting']['attachurl'].'common/'.$forum['banner']);
					}
					$forumfielddata['banner'] = '';
				}

				if($_FILES['iconnew']) {
					$iconnew = upload_icon_banner($forum, $_FILES['iconnew'], 'icon');
				} else {
					$iconnew = $_G['gp_iconnew'];
				}
				if($iconnew) {
					$forumfielddata['icon'] = $iconnew;
					if(!$extranew['iconwidth']) {
						$valueparse = parse_url($forumfielddata['icon']);
						if(!isset($valueparse['host'])) {
							$iconnew = $_G['setting']['attachurl'].'common/'.$forumfielddata['icon'];
						}
						if($info = @getimagesize($iconnew)) {
							$extranew['iconwidth'] = $info[0];
						}
					}
					$forum['extra']['iconwidth'] = $extranew['iconwidth'];
				} else {
					$forum['extra']['iconwidth'] = '';
				}
				if($_G['gp_deleteicon']) {
					$valueparse = parse_url($forum['icon']);
					if(!isset($valueparse['host'])) {
						@unlink($_G['setting']['attachurl'].'common/'.$forum['icon']);
					}
					$forumfielddata['icon'] = '';
					$forum['extra']['iconwidth'] = '';
				}
			}

			$extranew = serialize($forum['extra']);

			$forumfielddata = array_merge($forumfielddata, array(
				'description' => $descriptionnew,
				'password' => $_G['gp_passwordnew'],
				'redirect' => $_G['gp_redirectnew'],
				'rules' => $rulesnew,
				'attachextensions' => $_G['gp_attachextensionsnew'],
				'modrecommend' => $modrecommendnew,
				'seotitle' => $_G['gp_seotitlenew'],
				'keywords' => $_G['gp_keywordsnew'],
				'seodescription' => $_G['gp_seodescriptionnew'],
				'threadplugin' => $threadpluginnew,
				'extra' => $extranew,
				'commentitem' => $_G['gp_commentitemnew'],
				'formulaperm' => $_G['gp_formulapermnew'],
				'picstyle' => $_G['gp_picstylenew'],
				'widthauto' => $_G['gp_widthautonew'],
			));
			if(!$multiset) {
				$forumfielddata = array_merge($forumfielddata, array(
					'viewperm' => $_G['gp_viewpermnew'],
					'postperm' => $_G['gp_postpermnew'],
					'replyperm' => $_G['gp_replypermnew'],
					'getattachperm' => $_G['gp_getattachpermnew'],
					'postattachperm' => $_G['gp_postattachpermnew'],
					'postimageperm' => $_G['gp_postimagepermnew'],
					'relatedgroup' => $_G['gp_relatedgroupnew'],
					'spviewperm' => implode("\t", $_G['gp_spviewpermnew']),
				));
			}
			if($forumfielddata) {
				DB::update('forum_forumfield', $forumfielddata, "fid='$fid'");
			}
			if($pluginsetting) {
				foreach($_G['gp_pluginnew'] as $pluginvarid => $value) {
					$pluginvars[$pluginvarid][$fid] = $value;
				}
			}

			if($modrecommendnew && !$modrecommendnew['sort']) {
				require_once libfile('function/forumlist');
				recommendupdate($fid, $modrecommendnew, '1');
			}

			if($forumkeys[$fid] != $_G['gp_keysnew'] && preg_match('/^\w*$/', $_G['gp_keysnew']) && !preg_match('/^\d+$/', $_G['gp_keysnew'])) {
				$forumkeys[$fid] = $_G['gp_keysnew'];
				DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('forumkeys', '".(addslashes(serialize($forumkeys)))."')");
			}

		}
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				unset($_G['gp_'.$key]);
			}
		}
		}

		if($pluginvars) {
			set_pluginsetting($pluginvars);
		}

		updatecache(array('forums', 'setting', 'creditrule'));
		cpmsg('forums_edit_succeed', "mod=forum&action=forums&operation=edit&".($multiset ? 'multi='.implode(',', $_G['gp_multi']) : "fid=$fid").($_G['gp_anchor'] ? "&anchor={$_G['gp_anchor']}" : ''), 'succeed');

	}

} elseif($operation == 'delete') {
	$ajax = $_G['gp_ajax'];
	$confirmed = $_G['gp_confirmed'];
	$finished = $_G['gp_finished'];
	$total = intval($_G['gp_total']);
	$pp = intval($_G['gp_pp']);
	$currow = intval($_G['gp_currow']);

	if($_G['gp_ajax']) {
		ob_end_clean();
		require_once libfile('function/post');
		$tids = array();

		$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid='$fid' LIMIT $pp");
		while($thread = DB::fetch($query)) {
			$tids[] = $thread['tid'];
		}
		require_once libfile('function/delete');
		deletethread($tids);
		deletedomain($fid, 'forum');
		deletedomain($fid, 'subarea');
		if($currow + $pp > $total) {
			my_thread_log('delforum', array('fid' => $fid));
			DB::query("DELETE FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE fid='$fid'");
			DB::query("DELETE FROM ".DB::table('forum_access')." WHERE fid='$fid'");
			echo 'TRUE';
			exit;
		}

		echo 'GO';
		exit;

	} else {

		if($_G['gp_finished']) {
			updatecache('forums');
			cpmsg('forums_delete_succeed', 'action=forums', 'succeed');

		}

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE fup='$fid'")) {
			cpmsg('forums_delete_sub_notnull', '', 'error');
		}

		if(!$_G['gp_confirmed']) {

			cpmsg('forums_delete_confirm', "mod=forum&action=forums&operation=delete&fid=$fid", 'form');

		} else {

			$threads = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE fid='$fid'");

			cpmsg('forums_delete_alarm', "mod=forum&action=forums&operation=delete&fid=$fid&confirmed=1", 'loadingform', '', '<div id="percent">0%</div>', FALSE);

			echo "
			<div id=\"statusid\" style=\"display:none\"></div>
			<script type=\"text/JavaScript\">
				var xml_http_building_link = '".cplang('xml_http_building_link')."';
				var xml_http_sending = '".cplang('xml_http_sending')."';
				var xml_http_loading = '".cplang('xml_http_loading')."';
				var xml_http_load_failed = '".cplang('xml_http_load_failed')."';
				var xml_http_data_in_processed = '".cplang('xml_http_data_in_processed')."';
				var adminfilename = '".ADMINSCRIPT."';
				function forumsdelete(url, total, pp, currow) {

					var x = new Ajax('HTML', 'statusid');
					x.get(url+'&ajax=1&pp='+pp+'&total='+total+'&currow='+currow, function(s) {
						if(s != 'GO') {
							location.href = adminfilename + '?action=forums&operation=delete&finished=1';
						}

						currow += pp;
						var percent = ((currow / total) * 100).toFixed(0);
						percent = percent > 100 ? 100 : percent;
						document.getElementById('percent').innerHTML = percent+'%';
						document.getElementById('percent').style.backgroundPosition = '-'+percent+'%';

						if(currow < total) {
							forumsdelete(url, total, pp, currow);
						}
					});
				}
				forumsdelete(adminfilename + '?action=forums&operation=delete&fid=$fid&confirmed=1', $threads, 2000, 0);
			</script>
			";
		}
	}

} elseif($operation == 'copy') {

	loadcache('forums');

	$source = intval($_G['gp_source']);
	$sourceforum = $_G['cache']['forums'][$source];

	if(empty($sourceforum) || $sourceforum['type'] == 'group') {
		cpmsg('forums_copy_source_invalid', '', 'error');
	}

	$delfields = array(
		'forums'	=> array('fid', 'fup', 'type', 'name', 'status', 'displayorder', 'threads', 'posts', 'todayposts', 'lastpost', 'modworks', 'icon', 'level', 'commoncredits', 'archive', 'recommend'),
		'forumfields'	=> array('description', 'password', 'redirect', 'moderators', 'rules', 'threadtypes', 'threadsorts', 'threadplugin', 'jointype', 'gviewperm', 'membernum', 'dateline', 'lastupdate', 'founderuid', 'foundername', 'banner', 'groupnum', 'activity'),
	);
	$fields = array(
		'forums' 	=> fetch_table_struct('forum_forum'),
		'forumfields'	=> fetch_table_struct('forum_forumfield'),
	);

	if(!submitcheck('copysubmit')) {

		require_once libfile('function/forumlist');

		$forumselect = '<select name="target[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
		$optselect = '<select name="options[]" size="10" multiple="multiple">';
		$fieldarray = array_merge($fields['forums'], $fields['forumfields']);
		$listfields = array_diff($fieldarray, array_merge($delfields['forums'], $delfields['forumfields']));
		foreach($listfields as $field) {
			$optselect .= '<option value="'.$field.'">'.($lang['project_option_forum_'.$field] ? $lang['project_option_forum_'.$field] : $field).'</option>';
		}
		$optselect .= '</select>';
		shownav('forum', 'forums_copy');
		showsubmenu('forums_copy');
		showtips('forums_copy_tips');
		showformheader('forums&operation=copy');
		showhiddenfields(array('source' => $source));
		showtableheader();
		showtitle('forums_copy');
		showsetting(cplang('forums_copy_source').':','','', $sourceforum['name']);
		showsetting('forums_copy_target', '', '', $forumselect);
		showsetting('forums_copy_options', '', '', $optselect);
		showsubmit('copysubmit');
		showtablefooter();
		showformfooter();

	} else {

		$fids = $comma = '';
		if(is_array($_G['gp_target']) && count($_G['gp_target'])) {
			foreach($_G['gp_target'] as $fid) {
				if(($fid = intval($fid)) && $fid != $source ) {
					$fids .= $comma.$fid;
					$comma = ',';
				}
			}
		}
		if(empty($fids)) {
			cpmsg('forums_copy_target_invalid', '', 'error');
		}

		$forumoptions = array();
		if(is_array($_G['gp_options']) && !empty($_G['gp_options'])) {
			foreach($_G['gp_options'] as $option) {
				if($option = trim($option)) {
					if(in_array($option, $fields['forums'])) {
						$forumoptions['forum_forum'][] = $option;
					} elseif(in_array($option, $fields['forumfields'])) {
						$forumoptions['forum_forumfield'][] = $option;
					}
				}
			}
		}

		if(empty($forumoptions)) {
			cpmsg('forums_copy_options_invalid', '', 'error');
		}
		foreach(array('forum_forum', 'forum_forumfield') as $table) {
			if(is_array($forumoptions[$table]) && !empty($forumoptions[$table])) {
				$sourceforum = DB::fetch_first("SELECT ".implode($forumoptions[$table],',')." FROM ".DB::table($table)." WHERE fid='$source'");
				if(!$sourceforum) {
					cpmsg('forums_copy_source_invalid', '', 'error');
				}
				$sourceforum = array_map('addslashes', $sourceforum);
				DB::update($table, $sourceforum, "fid IN ($fids)");
			}
		}

		updatecache('forums');
		cpmsg('forums_copy_succeed', 'action=forums', 'succeed');

	}

}

function showforum(&$forum, $type = '', $last = '', $toggle = false) {

	global $_G;

	if($last == '') {
		$return = '<tr class="hover">'.
			'<td class="td25"'.($type == 'group' ? ' onclick="toggle_group(\'group_'.$forum['fid'].'\', $(\'a_group_'.$forum['fid'].'\'))"' : '').'>'.($type == 'group' ? '<a href="javascript:;" id="a_group_'.$forum['fid'].'">'.($toggle ? '[+]' : '[-]').'</a>' : '').'</td>
			<td class="td25"><input type="text" class="txt" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" /></td><td>';
		if($type == 'group') {
			$return .= '<div class="parentboard">';
			$_G['fg'] = !empty($_G['fg']) ? intval($_G['fg']) : 0;
			$_G['fg']++;
		} elseif($type == '') {
			$return .= '<div class="board">';
		} elseif($type == 'sub') {
			$return .= '<div id="cb_'.$forum['fid'].'" class="childboard">';
		}

		$boardattr = '';
		if(!$forum['status']  || $forum['password'] || $forum['redirect']) {
			$boardattr = '<div class="boardattr">';
			$boardattr .= $forum['status'] ? '' : cplang('forums_admin_hidden');
			$boardattr .= !$forum['password'] ? '' : ' '.cplang('forums_admin_password');
			$boardattr .= !$forum['redirect'] ? '' : ' '.cplang('forums_admin_url');
			$boardattr .= '</div>';
		}

		$return .= '<input type="text" name="name['.$forum['fid'].']" value="'.htmlspecialchars($forum['name']).'" class="txt" />'.
			($type == '' ? '<a href="###" onclick="addrowdirect = 1;addrow(this, 2, '.$forum['fid'].')" class="addchildboard">'.cplang('forums_admin_add_sub').'</a>' : '').
			'</div>'.$boardattr.
			'</td><td class="td25 lightfont">('.($type == 'group' ? 'gid:' : 'fid:').$forum['fid'].')</td>'.
			'</td><td class="td23">'.showforum_moderators($forum).'</td>
			<td width="160"><input class="checkbox" value="'.$forum['fid'].'" type="checkbox"'.($type != 'group' ? ' chkvalue="g'.$_G['fg'].'" onclick="multiupdate(this, '.$forum['fid'].')"' : ' name="gc'.$_G['fg'].'" onclick="checkAll(\'value\', this.form, \'g'.$_G['fg'].'\', \'gc'.$_G['fg'].'\', 1)"').' />'.'
			<a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'" title="'.cplang('forums_edit_comment').'" class="act">'.cplang('edit').'</a>'.
			($type != 'group' ? '<a href="'.ADMINSCRIPT.'?action=forums&operation=copy&source='.$forum['fid'].'" title="'.cplang('usergroups_copy_comment').'" class="act">'.cplang('forums_copy').'</a>' : '').
			'<a href="'.ADMINSCRIPT.'?action=forums&operation=delete&fid='.$forum['fid'].'" title="'.cplang('forums_delete_comment').'" class="act">'.cplang('delete').'</a></td></tr>';
		if($type == 'group') $return .= '<tbody id="group_'.$forum['fid'].'"'.($toggle ? ' style="display:none;"' : '').'>';
	} else {
		if($last == 'lastboard') {
			$return = '</tbody><tr><td></td><td colspan="4"><div class="lastboard"><a href="###" onclick="addrow(this, 1, '.$forum['fid'].')" class="addtr">'.cplang('forums_admin_add_forum').'</a></div></td><td>&nbsp;</td></tr>';
		} elseif($last == 'lastchildboard' && $type) {
			$return = '<script type="text/JavaScript">$(\'cb_'.$type.'\').className = \'lastchildboard\';</script>';
		} elseif($last == 'last') {
			$return = '</tbody><tr><td></td><td colspan="4"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.cplang('forums_admin_add_category').'</a></div></td>'.
				'<td class="bold"><a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=forums&operation=edit&multi=\' + getmultiids();return false;">'.cplang('multiedit').'</a></td>'.
				'</tr>';
		}
	}

	echo  $return = isset($return) ? $return : '';

	return $forum['fid'];
}

function showforum_moderators($forum) {
	global $_G;
	if($forum['moderators']) {
		$moderators = explode("\t", $forum['moderators']);
		$count = count($moderators);
		$max = $count > 2 ? 2 : $count;
		$mods = array();
		for($i = 0;$i < $max;$i++) {
			$mods[] = $forum['inheritedmod'] ? '<b>'.$moderators[$i].'</b>' : $moderators[$i];
		}
		$r = implode(', ', $mods);
		if($count > 2) {
			$r = '<span onmouseover="showMenu({\'ctrlid\':this.id})" id="mods_'.$forum['fid'].'">'.$r.'</span>';
			$mods = array();
			foreach($moderators as $moderator) {
				$mods[] = $forum['inheritedmod'] ? '<b>'.$moderator.'</b>' : $moderator;
			}
			$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.$r.' &raquo;</a>';
			$r .= '<div class="dropmenu1" id="mods_'.$forum['fid'].'_menu" style="display: none">'.implode('<br />', $mods).'</div>';
		} else {
			$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.$r.' &raquo;</a>';
		}


	} else {
		$r = '<a href="'.ADMINSCRIPT.'?action=forums&operation=moderators&fid='.$forum['fid'].'" title="'.cplang('forums_moderators_comment').'">'.cplang('forums_admin_no_moderator').'</a>';
	}
	return $r;
}

function fetch_table_struct($tablename, $result = 'FIELD') {
	$datas = array();
	$query = DB::query("DESCRIBE ".DB::table($tablename));
	while($data = DB::fetch($query)) {
		$datas[$data['Field']] = $result == 'FIELD' ? $data['Field'] : $data;
	}
	return $datas;
}

function getthreadclasses_html($fid) {
	$threadtypes = DB::result_first("SELECT threadtypes FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
	$threadtypes = unserialize($threadtypes);

	$query = DB::query("SELECT * FROM ".DB::table('forum_threadclass')." WHERE fid='$fid' ORDER BY displayorder");
	while($type = DB::fetch($query)) {
		$enablechecked = $moderatorschecked = '';
		$typeselected = array();
		if(isset($threadtypes['types'][$type['typeid']])) {
			$enablechecked = ' checked="checked"';
		}
		if($type['moderators']) {
			$moderatorschecked = ' checked="checked"';
		}
		$typeselect .= showtablerow('', array('class="td25"'), array(
			"<input type=\"checkbox\" class=\"checkbox\" name=\"threadtypesnew[options][delete][]\" value=\"{$type['typeid']}\" />",
			"<input type=\"text\" size=\"2\" name=\"threadtypesnew[options][displayorder][{$type['typeid']}]\" value=\"{$type['displayorder']}\" />",
			"<input type=\"text\" name=\"threadtypesnew[options][name][{$type['typeid']}]\" value=\"".(str_replace(array("'", "\""), array(), $type['name']))."\" />",
			"<input type=\"text\" name=\"threadtypesnew[options][icon][{$type['typeid']}]\" value=\"{$type['icon']}\" />",
			'<input type="checkbox" name="threadtypesnew[options][enable]['.$type['typeid'].']" value="1" class="checkbox"'.$enablechecked.' />',
			"<input type=\"checkbox\" class=\"checkbox\" name=\"threadtypesnew[options][moderators][{$type['typeid']}]\" value=\"1\"{$moderatorschecked} />",
		), TRUE);
	}
	return $typeselect;
}

function get_forum_by_fid($fid, $field = '', $table = 'forum') {
	static $forumlist = array('forum' => array(), 'forumfield' => array());
	$table = $table != 'forum' ? 'forumfield' : 'forum';
	$return = array();
	if(!array_key_exists($fid, $forumlist[$table])) {
		$forumlist[$table][$fid] = DB::fetch_first("SELECT * FROM ".DB::table('forum_'.$table)." WHERE fid='$fid'");
		if(!is_array($forumlist[$table][$fid])) {
			$forumlist[$table][$fid] = array();
		}
	}

	if(!empty($field)) {
		$return = isset($forumlist[$table][$fid][$field]) ? $forumlist[$table][$fid][$field] : null;
	} else {
		$return = $forumlist[$table][$fid];
	}
	return $return;
}

function get_subfids($fid) {
	global $subfids, $_G;
	$subfids[] = $fid;
	foreach($_G['cache']['forums'] as $key => $value) {
		if($value['fup'] == $fid) {
			get_subfids($value['fid']);
		}
	}
}

function copy_threadclasses($threadtypes, $fid) {
	global $_G;
	if($threadtypes) {
		$threadtypes = unserialize($threadtypes);
		$i = 0;
		$data = array();
		foreach($threadtypes['types'] as $key => $val) {
			$data = array('fid' => $fid, 'name' => addslashes($val), 'displayorder' => $i++, 'icon' => addslashes($threadtypes['icons'][$key]), 'moderators' => $threadtypes['moderators'][$key]);
			$newtypeid = DB::insert('forum_threadclass', $data, 1);
			$newtypes[$newtypeid] = $val;
			$newicons[$newtypeid] = $threadtypes['icons'][$key];
			$newmoderators[$newtypeid] = $threadtypes['moderators'][$key];
		}
		$threadtypes['types'] = $newtypes;
		$threadtypes['icons'] = $newicons;
		$threadtypes['moderators'] = $newmoderators;
		return serialize($threadtypes);
	}
	return '';
}
?>