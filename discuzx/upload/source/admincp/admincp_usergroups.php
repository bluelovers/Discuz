<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_usergroups.php 22662 2011-05-17 02:02:57Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation) {

	if(!submitcheck('groupsubmit')) {

		$sgroups = $smembers = $specialgroup = array();
		$sgroupids = '0';
		$smembernum = $membergroup = $sysgroup = $membergroupoption = $specialgroupoption = '';

		$query = DB::query("SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower, stars, color, icon, system FROM ".DB::table('common_usergroup')." ORDER BY creditshigher");
		while($group = DB::fetch($query)) {
			if($group['type'] == 'member') {

				$membergroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

				$membergroup .= showtablerow('', array('class="td25"', '', 'class="td23 lightfont"', 'class="td28"', 'class=td28'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"groupnew[$group[groupid]][grouptitle]\" value=\"$group[grouptitle]\">",
					"(groupid:$group[groupid])",
					"<input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditshigher]\" value=\"$group[creditshigher]\" /> ~ <input type=\"text\" class=\"txt\" size=\"6\" name=\"groupnew[$group[groupid]][creditslower]\" value=\"$group[creditslower]\" disabled />",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"groupnew[$group[groupid]][stars]\" value=\"$group[stars]\">",
					"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\" name=\"groupnew[$group[groupid]][color]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
					"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gmember\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
						"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source=$group[groupid]\" title=\"$lang[usergroups_copy_comment]\" class=\"act\">$lang[usergroups_copy]</a>"
				), TRUE);
			} elseif($group['type'] == 'system') {
				$sysgroup .= showtablerow('', array('', 'class="td23 lightfont"', '', 'class="td28"'), array(
					"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
					"(groupid:$group[groupid])",
					$lang['usergroups_system_'.$group['groupid']],
					"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
					"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
					"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gsystem\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
						"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source=$group[groupid]\" title=\"$lang[usergroups_copy_comment]\" class=\"act\">$lang[usergroups_copy]</a>"
				), TRUE);
			} elseif($group['type'] == 'special' && $group['radminid'] == '0') {

				$specialgroupoption .= "<option value=\"g{$group[groupid]}\">".addslashes($group['grouptitle'])."</option>";

				$sgroups[] = $group;
				$sgroupids .= ','.$group['groupid'];
			}
		}

		foreach($sgroups as $group) {
			if(is_array($smembers[$group['groupid']])) {
				$num = count($smembers[$group['groupid']]);
				$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br/>');
				unset($smembers[$group['groupid']]);
			} else {
				$specifiedusers = '';
				$num = 0;
			}
			$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";

			$sg = showtablerow('', array('class="td25"', '', 'class="td23 lightfont"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\">",
				"<input type=\"text\" class=\"txt\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\">",
				"(groupid:$group[groupid])",
				"<input type=\"text\" class=\"txt\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\">",
				"<input type=\"text\" id=\"group_color_$group[groupid]_v\" class=\"left txt\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\" onchange=\"updatecolorpreview('group_color_$group[groupid]')\"><input type=\"button\" id=\"group_color_$group[groupid]\"  class=\"colorwd\" onclick=\"group_color_$group[groupid]_frame.location='static/image/admincp/getcolor.htm?group_color_$group[groupid]|group_color_$group[groupid]_v';showMenu({'ctrlid':'group_color_$group[groupid]'})\" /><span id=\"group_color_$group[groupid]_menu\" style=\"display: none\"><iframe name=\"group_color_$group[groupid]_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>",
				"<input class=\"checkbox\" type=\"checkbox\" chkvalue=\"gspecial\" value=\"$group[groupid]\" onclick=\"multiupdate(this)\" /><a href=\"".ADMINSCRIPT."?action=usergroups&operation=edit&id=$group[groupid]\" class=\"act\">$lang[edit]</a>".
					"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=copy&source=$group[groupid]\" title=\"$lang[usergroups_copy_comment]\" class=\"act\">$lang[usergroups_copy]</a>".
					"<a href=\"".ADMINSCRIPT."?action=usergroups&operation=viewsgroup&sgroupid=$group[groupid]\" onclick=\"ajaxget(this.href, 'sgroup_$group[groupid]', 'sgroup_$group[groupid]');doane(event);\" class=\"act\">$lang[view]</a> &nbsp;"
			), TRUE);
			$sg .= showtablerow('', array('colspan="5" id="sgroup_'.$group['groupid'].'" style="display: none"'), array(''), TRUE);

			if($group['system'] == 'private') {
				$st = 'private';
			} else {
				list($dailyprice) = explode("\t", $group['system']);
				$st = $dailyprice > 0 ? 'buy' : 'free';
			}
			$specialgroup[$st] .= $sg;

		}

		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1,'', 'td25'],
		[2,'<input type="text" class="txt" size="12" name="groupnewadd[grouptitle][]"><select name="groupnewadd[projectid][]"><option value="">$lang[usergroups_project]</option><option value="0">------------</option>$membergroupoption</select>'],
		[1,'<input type="text" class="txt" size="6" name="groupnewadd[creditshigher][]">', 'td28'],
		[1,'<input type="text" class="txt" size="2" name="groupnewadd[stars][]">', 'td28'],
		[2,'<input type="text" class="txt" size="6" name="groupnewadd[color][]">']
	],
	[
		[1,'', 'td25'],
		[2,'<input type="text" class="txt" size="12" name="grouptitlenewadd[]"><select name="groupnewaddproject[]"><option value="">$lang[usergroups_project]</option><option value="0">------------</option>$specialgroupoption</select>'],
		[1,'<input type="text" class="txt" size="2" name="starsnewadd[]">', 'td28'],
		[2,'<input type="text" class="txt" size="6" name="colornewadd[]">']
	]
];
</script>
EOT;
		shownav('user', 'nav_usergroups');
		showsubmenuanchors('nav_usergroups', array(
			array('usergroups_member', 'membergroups', !$_G['gp_type'] || $_G['gp_type'] == 'member'),
			array('usergroups_special', 'specialgroups', $_G['gp_type'] == 'special'),
			array('usergroups_system', 'systemgroups', $_G['gp_type'] == 'system')
		));
		showtips('usergroups_tips');

		showformheader('usergroups&type=member');
		showtableheader('usergroups_member', 'fixpadding', 'id="membergroups"'.($_G['gp_type'] && $_G['gp_type'] != 'member' ? ' style="display: none"' : ''));
		showsubtitle(array('', 'usergroups_title', '', 'usergroups_creditsrange', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcmember" onclick="checkAll(\'value\', this.form, \'gmember\', \'gcmember\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $membergroup;
		echo '<tr><td>&nbsp;</td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['usergroups_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		showformheader('usergroups&type=special');
		showtableheader('usergroups_special', 'fixpadding', 'id="specialgroups"'.($_G['gp_type'] != 'special' ? ' style="display: none"' : ''));
		showsubtitle(array('', 'usergroups_title', '', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcspecial" onclick="checkAll(\'value\', this.form, \'gspecial\', \'gcspecial\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		if($specialgroup['private']) {
			echo $specialgroup['private'];
		}
		if($specialgroup['buy']) {
			showsubtitle(array('', 'usergroups_edit_system_buy'));
			echo $specialgroup['buy'];
		}
		if($specialgroup['free']) {
			showsubtitle(array('', 'usergroups_edit_system_free'));
			echo $specialgroup['free'];
		}
		echo '<tr><td>&nbsp;</td><td colspan="5"><div><a href="###" onclick="addrow(this, 1)" class="addtr">'.$lang['usergroups_sepcial_add'].'</a></div></td></tr>';
		showsubmit('groupsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

		showformheader('usergroups&type=system');
		showtableheader('usergroups_system', 'fixpadding', 'id="systemgroups"'.($_G['gp_type'] != 'system' ? ' style="display: none"' : ''));
		showsubtitle(array('usergroups_title', '', 'usergroups_status', 'usergroups_stars', 'usergroups_color', '<input class="checkbox" type="checkbox" name="gcsystem" onclick="checkAll(\'value\', this.form, \'gsystem\', \'gcsystem\', 1)" /> <a href="javascript:;" onclick="if(getmultiids()) location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&multi=\' + getmultiids();return false;">'.$lang['multiedit'].'</a>'));
		echo $sysgroup;
		showsubmit('groupsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(empty($_G['gp_type']) || !in_array($_G['gp_type'], array('member', 'special', 'system'))) {
			cpmsg('usergroups_type_nonexistence');
		}

		$oldgroups = $extadd = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')." WHERE `type`='{$_G['gp_type']}'");
		while ($gp = DB::fetch($query)) {
			$oldgroups[$gp['groupid']] = $gp;
		}

		foreach($oldgroups as $id => $vals) {
			$data = array();
			foreach($vals as $k => $v) {
				$v = addslashes($v);
				if(!in_array($k, array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'icon'))) {
					$data[$k] = $v;
				}
			}
			$extadd['g'.$id] = $data;
		}

		if($_G['gp_type'] == 'member') {
			$groupnewadd = array_flip_keys($_G['gp_groupnewadd']);
			foreach($groupnewadd as $k => $v) {
				if(!$v['grouptitle']) {
					unset($groupnewadd[$k]);
				} elseif(!$v['creditshigher']) {
					cpmsg('usergroups_update_creditshigher_invalid', '', 'error');
				}
			}
			$groupnewkeys = array_keys($_G['gp_groupnew']);
			$maxgroupid = max($groupnewkeys);
			foreach($groupnewadd as $k=>$v) {
				$_G['gp_groupnew'][$k+$maxgroupid+1] = $v;
			}
			$orderarray = array();
			if(is_array($_G['gp_groupnew'])) {
				foreach($_G['gp_groupnew'] as $id => $group) {
					if((is_array($_G['gp_delete']) && in_array($id, $_G['gp_delete'])) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
						unset($_G['gp_groupnew'][$id]);
					} else {
						$orderarray[$group['creditshigher']] = $id;
					}
				}
			}

			if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
				cpmsg('usergroups_update_credits_invalid', '', 'error');
			}

			ksort($orderarray);
			$rangearray = array();
			$lowerlimit = array_keys($orderarray);
			for($i = 0; $i < count($lowerlimit); $i++) {
				$rangearray[$orderarray[$lowerlimit[$i]]] = array(
					'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
					'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
				);
			}

			foreach($_G['gp_groupnew'] as $id => $group) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('usergroups_update_credits_duplicate', '', 'error');
				}
				if(in_array($id, $groupnewkeys)) {
					DB::query("UPDATE ".DB::table('common_usergroup')." SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]', color='$group[color]' WHERE groupid='$id' AND type='member'");
					DB::update('forum_onlinelist', array(
						'title' => $group['grouptitle'],
					), "groupid='$id'");

				} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
					$data = array(
						'grouptitle' => $group['grouptitle'],
						'creditshigher' => $creditshighernew,
						'creditslower' => $creditslowernew,
						'stars' => $group['stars'],
						'color' => $group['color'],
					);
					if(!empty($group['projectid']) && !empty($extadd[$group['projectid']])) {
						$data = array_merge($data, $extadd[$group['projectid']]);
					}

					$newgid = DB::insert('common_usergroup', $data, 1);

					$datafield = array(
						'groupid' => $newgid,
						'allowsearch' => 2,
					);

					DB::insert('common_usergroup_field', $datafield);
					DB::insert('forum_onlinelist', array(
						'groupid' => $newgid,
						'title' => $data['grouptitle'],
						'displayorder' => '0',
						'url' => '',
					), false, true);

					$sqladd = !empty($group['projectid']) && !empty($extadd[$group['projectid']]) ? $extadd[$group['projectid']] : '';
					if($sqladd) {
						$projectid = substr($group['projectid'], 1);
						$group_fields = DB::fetch_first("SELECT * FROM ".DB::table('common_usergroup_field')." WHERE groupid='$projectid'");
						unset($group_fields['groupid']);
						DB::update('common_usergroup_field', $group_fields, "groupid='$newgid'");
						$query = DB::query("SELECT fid, viewperm, postperm, replyperm, getattachperm, postattachperm, postimageperm FROM ".DB::table('forum_forumfield')."");
						while($row = DB::fetch($query)) {
							$upforumperm = array();
							if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
								$upforumperm[] = "viewperm='$row[viewperm]$newgid\t'";
							}
							if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
								$upforumperm[] = "postperm='$row[postperm]$newgid\t'";
							}
							if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
								$upforumperm[] = "replyperm='$row[replyperm]$newgid\t'";
							}
							if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
								$upforumperm[] = "getattachperm='$row[getattachperm]$newgid\t'";
							}
							if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
								$upforumperm[] = "postattachperm='$row[postattachperm]$newgid\t'";
							}
							if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
								$upforumperm[] = "postimageperm='$row[postimageperm]$newgid\t'";
							}
							if($upforumperm) {
								DB::query("UPDATE ".DB::table('forum_forumfield')." SET ".implode(',', $upforumperm)." WHERE fid='$row[fid]'");
							}
						}
					}
				}
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids) AND type='member'");
				DB::query("DELETE FROM ".DB::table('common_usergroup_field')." WHERE groupid IN ($ids)");
				DB::delete('forum_onlinelist', "groupid IN ($ids)");
				deletegroupcache($_G['gp_delete']);
			}

		} elseif($_G['gp_type'] == 'special') {
			if(is_array($_G['gp_grouptitlenewadd'])) {
				foreach($_G['gp_grouptitlenewadd'] as $k => $v) {
					if($v) {
						$data = array(
							'type' => 'special',
							'grouptitle' => $_G['gp_grouptitlenewadd'][$k],
							'color' => $_G['gp_colornewadd'][$k],
							'stars' => $_G['gp_starsnewadd'][$k],
						);
						if(!empty($_G['gp_groupnewaddproject'][$k]) && !empty($extadd[$_G['gp_groupnewaddproject'][$k]])) {
							$data = array_merge($data, $extadd[$_G['gp_groupnewaddproject'][$k]]);
						}
						$newgid = DB::insert('common_usergroup', $data, true);

						$datafield = array(
							'groupid' => $newgid,
							'allowsearch' => 2,
						);

						DB::insert('common_usergroup_field', $datafield);
						DB::insert('forum_onlinelist', array(
							'groupid' => $newgid,
							'title' => $data['grouptitle'],
							'url' => '',
						), false, true);

						$sqladd = !empty($_G['gp_groupnewaddproject'][$k]) && !empty($extadd[$_G['gp_groupnewaddproject'][$k]]) ? $extadd[$_G['gp_groupnewaddproject'][$k]] : '';
						if($sqladd) {
							$projectid = substr($_G['gp_groupnewaddproject'][$k], 1);
							$group_fields = DB::fetch_first("SELECT * FROM ".DB::table('common_usergroup_field')." WHERE groupid='$projectid'");
							unset($group_fields['groupid']);
							DB::update('common_usergroup_field', $group_fields, "groupid='$newgid'");
							$query = DB::query("SELECT fid, viewperm, postperm, replyperm, getattachperm, postattachperm, postimageperm FROM ".DB::table('forum_forumfield')."");
							while($row = DB::fetch($query)) {
								$upforumperm = array();
								if($row['viewperm'] && in_array($projectid, explode("\t", $row['viewperm']))) {
									$upforumperm[] = "viewperm='$row[viewperm]$newgid\t'";
								}
								if($row['postperm'] && in_array($projectid, explode("\t", $row['postperm']))) {
									$upforumperm[] = "postperm='$row[postperm]$newgid\t'";
								}
								if($row['replyperm'] && in_array($projectid, explode("\t", $row['replyperm']))) {
									$upforumperm[] = "replyperm='$row[replyperm]$newgid\t'";
								}
								if($row['getattachperm'] && in_array($projectid, explode("\t", $row['getattachperm']))) {
									$upforumperm[] = "getattachperm='$row[getattachperm]$newgid\t'";
								}
								if($row['postattachperm'] && in_array($projectid, explode("\t", $row['postattachperm']))) {
									$upforumperm[] = "postattachperm='$row[postattachperm]$newgid\t'";
								}
								if($row['postimageperm'] && in_array($projectid, explode("\t", $row['postimageperm']))) {
									$upforumperm[] = "postimageperm='$row[postimageperm]$newgid\t'";
								}
								if($upforumperm) {
									DB::query("UPDATE ".DB::table('forum_forumfield')." SET ".implode(',', $upforumperm)." WHERE fid='$row[fid]'");
								}
							}
						}
					}
				}
			}

			if(is_array($_G['gp_group_title'])) {
				foreach($_G['gp_group_title'] as $id => $title) {
					if(!$_G['gp_delete'][$id]) {
						DB::query("UPDATE ".DB::table('common_usergroup')." SET grouptitle='{$_G['gp_group_title'][$id]}', stars='{$_G['gp_group_stars'][$id]}', color='{$_G['gp_group_color'][$id]}' WHERE groupid='$id'");
						DB::update('forum_onlinelist', array(
							'title' => $_G['gp_group_title'][$id],
						), "groupid='$id'");
					}
				}
			}

			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('common_usergroup')." WHERE groupid IN ($ids) AND type='special'");
				DB::delete('forum_onlinelist', "groupid IN ($ids)");
				DB::query("DELETE FROM ".DB::table('common_admingroup')." WHERE admingid IN ($ids)");
				$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
				DB::query("UPDATE ".DB::table('common_member')." SET groupid='$newgroupid', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
				deletegroupcache($_G['gp_delete']);
			}

		} elseif($_G['gp_type'] == 'system') {
			if(is_array($_G['gp_group_title'])) {
				foreach($_G['gp_group_title'] as $id => $title) {
					DB::query("UPDATE ".DB::table('common_usergroup')." SET grouptitle='{$_G['gp_group_title'][$id]}', stars='{$_G['gp_group_stars'][$id]}', color='{$_G['gp_group_color'][$id]}', icon='{$_G['gp_group_icon'][$id]}' WHERE groupid='$id'");
					DB::update('forum_onlinelist', array(
						'title' => $_G['gp_group_title'][$id],
					), "groupid='$id'");
				}
			}
		}

		updatecache(array('usergroups', 'onlinelist', 'groupreadaccess'));
		cpmsg('usergroups_update_succeed', 'action=usergroups&type='.$_G['gp_type'], 'succeed');
	}

} elseif($operation == 'viewsgroup') {

	$sgroupid = $_G['gp_sgroupid'];
	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE groupid='$sgroupid'");
	$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE groupid='$sgroupid' LIMIT 80");
	$sgroups = '';
	while($member = DB::fetch($query)) {
		$sgroups .= '<li><a href="home.php?mod=space&uid='.$member['uid'].'" target="_blank">'.$member['username'].'</a></li>';
	}
	ajaxshowheader();
	echo '<ul class="userlist"><li class="unum">'.$lang['usernum'].$num.($num > 80 ? '&nbsp;<a href="'.ADMINSCRIPT.'?action=members&submit=yes&usergroupid[]='.$sgroupid.'">'.$lang['more'].'&raquo;</a>' : '').'</li>'.$sgroups.'</ul>';
	ajaxshowfooter();

} elseif($operation == 'edit') {

	$return = isset($_G['gp_return']) && $_G['gp_return'] ? 'admin' : '';

	list($pluginsetting, $pluginvalue) = get_pluginsetting('groups');

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

	if(empty($gids)) {
		$grouplist = "<select name=\"id\" style=\"width:150px\">\n";
		$conditions = !empty($_G['gp_anchor']) && $_G['gp_anchor'] == 'system' ? "WHERE type='special'" : '';
		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." $conditions");
		while($group = DB::fetch($query)) {
			$grouplist .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$grouplist .= '</select>';
		cpmsg('usergroups_edit_nonexistence', 'action=usergroups&operation=edit'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : '').(!empty($_G['gp_highlight']) ? "&anchor={$_G['gp_anchor']}" : ''), 'form', array(), $grouplist);
	}

	$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')." u
		LEFT JOIN ".DB::table('common_usergroup_field')." uf USING(groupid)
		WHERE u.groupid IN ($gids)");
	if(!DB::num_rows($query)) {
		cpmsg('usergroups_nonexistence', '', 'error');
	} else {
		while($group = DB::fetch($query)) {
			if(isset($pluginvalue[$group['groupid']])) {
				$group['plugin'] = $pluginvalue[$group['groupid']];
			}
			$mgroup[] = $group;
		}
	}

	$allowthreadplugin = $_G['setting']['threadplugins'] ? unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='allowthreadplugin'")) : array();

	if(!submitcheck('detailsubmit')) {

		$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		$grouplist = $groupcount = array();
		while($ggroup = DB::fetch($query)) {
			$checked = $_G['gp_id'] == $ggroup['groupid'] || in_array($ggroup['groupid'], $_G['gp_multi']);
			$ggroup['type'] = $ggroup['type'] == 'special' && $ggroup['radminid'] ? 'specialadmin' : $ggroup['type'];
			$groupcount[$ggroup['type']]++;
			$grouplist[$ggroup['type']] .= '<input class="left checkbox ck" chkvalue="'.$ggroup['type'].'" name="multi[]" value="'.$ggroup['groupid'].'" type="checkbox" '.($checked ? 'checked="checked" ' : '').'/>'.
				'<a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&switch=yes&id='.$ggroup['groupid'].'&anchor=\'+currentAnchor+\'&scrolltop=\'+scrollTopBody()"'.($checked ? ' class="current"' : '').'>'.$ggroup['grouptitle'].'</a>';
			if(!($groupcount[$ggroup['type']] % 3)) {
				$grouplist[$ggroup['type']] .= '<br style="clear:both" />';
			}
		}
		$gselect = '<span id="ugselect" class="right popupmenu_dropmenu" onmouseover="showMenu({\'ctrlid\':this.id,\'pos\':\'34\'});$(\'ugselect_menu\').style.top=(parseInt($(\'ugselect_menu\').style.top)-scrollTopBody())+\'px\';$(\'ugselect_menu\').style.left=(parseInt($(\'ugselect_menu\').style.left)-document.documentElement.scrollLeft-20)+\'px\'">'.$lang['usergroups_switch'].'<em>&nbsp;&nbsp;</em></span>'.
			'<div id="ugselect_menu" class="popupmenu_popup" style="display:none">'.
			'<em class="cl"><span class="right"><input name="checkall_member" onclick="checkAll(\'value\', this.form, \'member\', \'checkall_member\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_member'].'</em>'.$grouplist['member'].'<br />'.
			($grouplist['special'] ? '<em class="cl"><span class="right"><input name="checkall_special" onclick="checkAll(\'value\', this.form, \'special\', \'checkall_special\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_special'].'</em>'.$grouplist['special'].'<br />' : '').
			($grouplist['specialadmin'] ? '<em class="cl"><span class="right"><input name="checkall_specialadmin" onclick="checkAll(\'value\', this.form, \'specialadmin\', \'checkall_specialadmin\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_specialadmin'].'</em>'.$grouplist['specialadmin'].'<br />' : '').
			'<em class="cl"><span class="right"><input name="checkall_system" onclick="checkAll(\'value\', this.form, \'system\', \'checkall_system\')" type="checkbox" class="vmiddle checkbox" /></span>'.$lang['usergroups_system'].'</em>'.$grouplist['system'].
			'<br style="clear:both" /><div class="cl"><input type="button" class="btn right" onclick="$(\'menuform\').submit()" value="'.cplang('usergroups_multiedit').'" /></div>'.
			'</div>';
		$anchor = in_array($_G['gp_anchor'], array('basic', 'system', 'special', 'post', 'attach', 'magic', 'invite', 'pm', 'credit', 'home', 'group', 'portal', 'plugin')) ? $_G['gp_anchor'] : 'basic';
		showformheader('', '', 'menuform', 'get');
		showhiddenfields(array('action' => 'usergroups', 'operation' => 'edit'));
		showsubmenuanchors(cplang('usergroups_edit').(count($mgroup) == 1 ? ' - '.$mgroup[0]['grouptitle'].'(groupid:'.$mgroup[0]['groupid'].')' : ''), array(
			array('usergroups_edit_basic', 'basic', $anchor == 'basic'),
			count($mgroup) == 1 && $mgroup[0]['type'] == 'special' && $mgroup[0]['radminid'] < 1 ? array('usergroups_edit_system', 'system', $anchor == 'system') : array(),
			array(array('menu' => 'usergroups_edit_forum', 'submenu' => array(
				array('usergroups_edit_post', 'post', $anchor == 'post'),
				array('usergroups_edit_attach', 'attach', $anchor == 'attach'),
				array('usergroups_edit_special', 'special', $anchor == 'special')
			))),
			array('usergroups_edit_group', 'group', $anchor == 'group'),
			array('usergroups_edit_portal', 'portal', $anchor == 'portal'),
			array('usergroups_edit_home', 'home', $anchor == 'home'),
			array(array('menu' => 'usergroups_edit_other', 'submenu' => array(
				array('usergroups_edit_credit', 'credit', $anchor == 'credit'),
				array('usergroups_edit_magic', 'magic', $anchor == 'magic'),
				array('usergroups_edit_invite', 'invite', $anchor == 'invite'),
				array('usergroups_edit_pm', 'pm', $anchor == 'pm'),
				!$pluginsetting ? array() : array('usergroups_edit_plugin', 'plugin', $anchor == 'plugin'),
			))),
		), $gselect);
		showformfooter();

		if(count($mgroup) == 1 && $mgroup[0]['type'] == 'special' && $mgroup[0]['radminid'] < 1) {
			showtips('usergroups_edit_system_tips', 'system_tips', $anchor == 'system');
		}
		if($multiset) {
			showtips('setting_multi_tips');
		}

		showtips('usergroups_edit_magic_tips', 'magic_tips', $anchor == 'magic');
		showtips('usergroups_edit_invite_tips', 'invite_tips', $anchor == 'invite');
		if($_GET['id'] == 7) {
			showtips('usergroups_edit_system_guest_portal_tips', 'portal_tips', $anchor == 'portal');
			showtips('usergroups_edit_system_guest_home_tips', 'home_tips', $anchor == 'home');
		}
		showformheader("usergroups&operation=edit&id={$_G['gp_id']}&return=$return", 'enctype');

		if($multiset) {
			$_G['showsetting_multi'] = 0;
			$_G['showsetting_multicount'] = count($mgroup);
			foreach($mgroup as $group) {
				$_G['showtableheader_multi'][] = '<a href="javascript:;" onclick="location.href=\''.ADMINSCRIPT.'?action=usergroups&operation=edit&id='.$group['groupid'].'&anchor=\'+$(\'cpform\').anchor.value;return false">'.$group['grouptitle'].'(groupid:'.$group['groupid'].')</a>';
			}
		}
		$mgids = array();
		foreach($mgroup as $group) {
		$_G['gp_id'] = $gid = $group['groupid'];
		$mgids[] = $gid;

		if(!$multiset && $group['type'] == 'special' && $group['radminid'] < 1) {
			showtagheader('div', 'system', $anchor == 'system');
			showtableheader();
			if($group['system'] == 'private') {
				$system = array('public' => 0, 'dailyprice' => 0, 'minspan' => 0);
			} else {
				$system = array('public' => 1, 'dailyprice' => 0, 'minspan' => 0);
				list($system['dailyprice'], $system['minspan']) = explode("\t", $group['system']);
			}
			showsetting('usergroups_edit_system_public', 'system_publicnew', $system['public'], 'radio', 0, 1);
			showsetting('usergroups_edit_system_dailyprice', 'system_dailypricenew', $system['dailyprice'], 'text');
			showsetting('usergroups_edit_system_minspan', 'system_minspannew', $system['minspan'], 'text');
			showtablefooter();
			showtagfooter('div');
		}

		showtagheader('div', 'basic', $anchor == 'basic');
		showtableheader();
		showtitle('usergroups_edit_basic');
		showsetting('usergroups_edit_basic_title', 'grouptitlenew', $group['grouptitle'], 'text');
		$group['exempt'] = strrev(sprintf('%0'.strlen($group['exempt']).'b', $group['exempt']));
		if(!$multiset) {
			if($group['icon']) {
				$valueparse = parse_url($group['icon']);
				if(isset($valueparse['host'])) {
					$groupicon = $group['icon'];
				} else {
					$groupicon = $_G['setting']['attachurl'].'common/'.$group['icon'].'?'.random(6);
				}
				$groupiconhtml = '<label><input type="checkbox" class="checkbox" name="deleteicon[$group[groupid]]" value="yes" /> '.$lang['delete'].'</label><br /><img src="'.$groupicon.'" />';
			}
			showsetting('usergroups_icon', 'iconnew', $group['icon'], 'filetext', '', 0, $groupiconhtml);
		}


		$group['allowvisit'] = $group['groupid'] == 1 ? 2 : $group['allowvisit'];

		showsetting('usergroups_edit_basic_visit', array('allowvisitnew', array(
			array(0, cplang('usergroups_edit_basic_visit_none')),
			array(1, cplang('usergroups_edit_basic_visit_normal')),
			array(2, cplang('usergroups_edit_basic_visit_super')),
		)), $group['allowvisit'], 'mradio');

		showsetting('usergroups_edit_basic_read_access', 'readaccessnew', $group['readaccess'], 'text');
		showsetting('usergroups_edit_basic_max_friend_number', 'maxfriendnumnew', $group['maxfriendnum'], 'text');
		showsetting('usergroups_edit_basic_domain_length', 'domainlengthnew', $group['domainlength'], 'text');
		showmultititle();
		showsetting('usergroups_edit_basic_invisible', 'allowinvisiblenew', $group['allowinvisible'], 'radio');
		showsetting('usergroups_edit_basic_allowtransfer', 'allowtransfernew', $group['allowtransfer'], 'radio');
		showsetting('usergroups_edit_basic_allowsendpm', 'allowsendpmnew', $group['allowsendpm'], 'radio');
		showsetting('usergroups_edit_post_html', 'allowhtmlnew', $group['allowhtml'], 'radio');
		showsetting('usergroups_edit_post_url', array('allowposturlnew', array(
			array(0, $lang['usergroups_edit_post_url_banned']),
			array(1, $lang['usergroups_edit_post_url_mod']),
			array(2, $lang['usergroups_edit_post_url_unhandle']),
			array(3, $lang['usergroups_edit_post_url_enable'])
		)), $group['allowposturl'], 'mradio');
		showsetting('usergroups_edit_basic_allow_statdata', 'allowstatdatanew', $group['allowstatdata'], 'radio');
		showmultititle();
		showsetting('usergroups_edit_basic_search_post', 'allowfulltextnew', $group['allowsearch'] & 32, 'radio');
		$group['allowsearch'] = $group['allowsearch'] > 32 ? $group['allowsearch'] - 32 : $group['allowsearch'];
		showsetting('usergroups_edit_basic_search', array('allowsearchnew', array(
			cplang('setting_search_status_portal'),
			cplang('setting_search_status_forum'),
			cplang('setting_search_status_blog'),
			cplang('setting_search_status_album'),
			cplang('setting_search_status_group')
		)), $group['allowsearch'], 'binmcheckbox');
		showsetting('usergroups_edit_basic_reasonpm', array('reasonpmnew', array(
			array(0, $lang['usergroups_edit_basic_reasonpm_none']),
			array(1, $lang['usergroups_edit_basic_reasonpm_reason']),
			array(2, $lang['usergroups_edit_basic_reasonpm_pm']),
			array(3, $lang['usergroups_edit_basic_reasonpm_both'])
		)), $group['reasonpm'], 'mradio');
		showmultititle();
		showsetting('usergroups_edit_basic_cstatus', 'allowcstatusnew', $group['allowcstatus'], 'radio');
		showsetting('usergroups_edit_basic_disable_periodctrl', 'disableperiodctrlnew', $group['disableperiodctrl'], 'radio');
		showsetting('usergroups_edit_basic_hour_posts', 'maxpostsperhournew', $group['maxpostsperhour'], 'text');
		showsetting('usergroups_edit_basic_seccode', 'seccodenew', $group['seccode'], 'radio');
		showsetting('usergroups_edit_basic_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
		showsetting('usergroups_edit_basic_ignore_censor', 'ignorecensornew', $group['ignorecensor'], 'radio');
		showsetting('usergroups_edit_post_tag', 'allowposttagnew', $group['allowposttag'], 'radio');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'special', $anchor == 'special');
		showtableheader();
		showtitle('usergroups_edit_special');
		showsetting('usergroups_edit_special_activity', 'allowpostactivitynew', $group['allowpostactivity'], 'radio');
		showsetting('usergroups_edit_special_poll', 'allowpostpollnew', $group['allowpostpoll'], 'radio');
		showsetting('usergroups_edit_special_vote', 'allowvotenew', $group['allowvote'], 'radio');
		showsetting('usergroups_edit_special_reward', 'allowpostrewardnew', $group['allowpostreward'], 'radio');
		showmultititle();
		showsetting('usergroups_edit_special_reward_min', 'minrewardpricenew', $group['minrewardprice'], "text");
		showsetting('usergroups_edit_special_reward_max', 'maxrewardpricenew', $group['maxrewardprice'], "text");
		showsetting('usergroups_edit_special_trade', 'allowposttradenew', $group['allowposttrade'], 'radio');
		showsetting('usergroups_edit_special_trade_min', 'mintradepricenew', $group['mintradeprice'], "text");
		showsetting('usergroups_edit_special_trade_max', 'maxtradepricenew', $group['maxtradeprice'], "text");
		showsetting('usergroups_edit_special_trade_stick', 'tradesticknew', $group['tradestick'], "text");
		showsetting('usergroups_edit_special_debate', 'allowpostdebatenew', $group['allowpostdebate'], "radio");
		showsetting('usergroups_edit_special_rushreply', 'allowpostrushreplynew', $group['allowpostrushreply'], "radio");
		$threadpluginselect = array();
		if(is_array($_G['setting']['threadplugins'])) foreach($_G['setting']['threadplugins'] as $tpid => $data) {
			$threadpluginselect[] = array($tpid, $data['name']);
		}
		if($threadpluginselect) {
			showsetting('usergroups_edit_special_allowthreadplugin', array('allowthreadpluginnew', $threadpluginselect), $allowthreadplugin[$_G['gp_id']], 'mcheckbox');
		}
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'post', $anchor == 'post');
		showtableheader();
		showtitle('usergroups_edit_post');
		showsetting('usergroups_edit_post_new', 'allowpostnew', $group['allowpost'], 'radio');
		showsetting('usergroups_edit_post_reply', 'allowreplynew', $group['allowreply'], 'radio');
		showsetting('usergroups_edit_post_direct', array('allowdirectpostnew', array(
			array(0, $lang['usergroups_edit_post_direct_none']),
			array(1, $lang['usergroups_edit_post_direct_reply']),
			array(2, $lang['usergroups_edit_post_direct_thread']),
			array(3, $lang['usergroups_edit_post_direct_all'])
		)), $group['allowdirectpost'], 'mradio');
		showsetting('usergroups_edit_post_allow_down_remote_img', 'allowdownremoteimgnew', $group['allowdownremoteimg'], 'radio');
		showsetting('usergroups_edit_post_anonymous', 'allowanonymousnew', $group['allowanonymous'], 'radio');
		showsetting('usergroups_edit_post_set_read_perm', 'allowsetreadpermnew', $group['allowsetreadperm'], 'radio');
		showsetting('usergroups_edit_post_maxprice', 'maxpricenew', $group['maxprice'], 'text');
		showmultititle();
		showsetting('usergroups_edit_post_hide_code', 'allowhidecodenew', $group['allowhidecode'], 'radio');
		showsetting('usergroups_edit_post_mediacode', 'allowmediacodenew', $group['allowmediacode'], 'radio');
		showsetting('usergroups_edit_post_sig_bbcode', 'allowsigbbcodenew', $group['allowsigbbcode'], 'radio');
		showsetting('usergroups_edit_post_sig_img_code', 'allowsigimgcodenew', $group['allowsigimgcode'], 'radio');
		showsetting('usergroups_edit_post_max_sig_size', 'maxsigsizenew', $group['maxsigsize'], 'text');
		if($group['groupid'] != 7) {
			showsetting('usergroups_edit_post_recommend', 'allowrecommendnew', $group['allowrecommend'], 'text');
		}
		showsetting('usergroups_edit_post_edit_time_limit', 'edittimelimitnew', intval($group['edittimelimit']), 'text');
		showsetting('usergroups_edit_post_allowreplycredit', 'allowreplycreditnew', $group['allowreplycredit'], 'radio');
		showsetting('usergroups_edit_post_allowcommentpost', array('allowcommentpostnew', array(
			$lang['usergroups_edit_post_allowcommentpost_firstpost'],
			$lang['usergroups_edit_post_allowcommentpost_reply'],
		)), $group['allowcommentpost'], 'binmcheckbox', !in_array(1, $_G['setting']['allowpostcomment']));
		showsetting('usergroups_edit_post_allowcommentreply', 'allowcommentreplynew', $group['allowcommentreply'], 'radio', !in_array(2, $_G['setting']['allowpostcomment']));
		showsetting('usergroups_edit_post_allowcommentitem', 'allowcommentitemnew', $group['allowcommentitem'], 'radio', !in_array(1, $_G['setting']['allowpostcomment']));
		showtablefooter();
		showtagfooter('div');

		$group['maxattachsize'] = intval($group['maxattachsize'] / 1024);
		$group['maxsizeperday'] = intval($group['maxsizeperday'] / 1024);
		$group['maximagesize'] = intval($group['maximagesize'] / 1024);

		showtagheader('div', 'attach', $anchor == 'attach');
		showtableheader();
		showtitle('usergroups_edit_attach');
		showsetting('usergroups_edit_attach_get', 'allowgetattachnew', $group['allowgetattach'], 'radio');
		showsetting('usergroups_edit_attach_getimage', 'allowgetimagenew', $group['allowgetimage'], 'radio');
		showsetting('usergroups_edit_attach_post', 'allowpostattachnew', $group['allowpostattach'], 'radio');
		showsetting('usergroups_edit_attach_set_perm', 'allowsetattachpermnew', $group['allowsetattachperm'], 'radio');
		showsetting('usergroups_edit_image_post', 'allowpostimagenew', $group['allowpostimage'], 'radio');
		showmultititle();
		showsetting('usergroups_edit_attach_max_size', 'maxattachsizenew', $group['maxattachsize'], 'text');
		showsetting('usergroups_edit_attach_max_size_per_day', 'maxsizeperdaynew', $group['maxsizeperday'], 'text');
		showsetting('usergroups_edit_attach_max_number_per_day', 'maxattachnumnew', $group['maxattachnum'], 'text');
		showsetting('usergroups_edit_attach_ext', 'attachextensionsnew', $group['attachextensions'], 'text');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'magic', $anchor == 'magic');
		showtableheader();
		showtitle('usergroups_edit_magic');
		showsetting('usergroups_edit_magic_permission', array('allowmagicsnew', array(
			array(0, $lang['usergroups_edit_magic_unallowed']),
			array(1, $lang['usergroups_edit_magic_allow']),
			array(2, $lang['usergroups_edit_magic_allow_and_pass'])
		)), $group['allowmagics'], 'mradio');
		showsetting('usergroups_edit_magic_discount', 'magicsdiscountnew', $group['magicsdiscount'], 'text');
		showsetting('usergroups_edit_magic_max', 'maxmagicsweightnew', $group['maxmagicsweight'], 'text');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'invite', $anchor == 'invite');
		showtableheader();
		showtitle('usergroups_edit_invite');
		showsetting('usergroups_edit_invite_permission', 'allowinvitenew', $group['allowinvite'], 'radio');
		showsetting('usergroups_edit_invite_send_permission', 'allowmailinvitenew', $group['allowmailinvite'], 'radio');
		showsetting('usergroups_edit_invite_price', 'invitepricenew', $group['inviteprice'], 'text');
		showsetting('usergroups_edit_invite_buynum', 'maxinvitenumnew', $group['maxinvitenum'], 'text');
		showsetting('usergroups_edit_invite_maxinviteday', 'maxinvitedaynew', $group['maxinviteday'], 'text');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'pm', $anchor == 'pm');
		showtableheader();
		showtitle('usergroups_edit_pm');
		showsetting('usergroups_edit_pm_sendallpm', 'allowsendallpmnew', $group['allowsendallpm'], 'radio');
		showtablefooter();
		showtagfooter('div');

		$raterangearray = array();
		foreach(explode("\n", $group['raterange']) as $range) {
			$range = explode("\t", $range);
			$raterangearray[$range[0]] = array('isself' => $range[1], 'min' => $range[2], 'max' => $range[3], 'mrpd' => $range[4]);
		}

		if($multiset) {
			showtagheader('div', 'credit', $anchor == 'credit');
			showtableheader();
			showtitle('usergroups_edit_credit');
			showsetting('usergroups_edit_credit_exempt_sendpm', 'exemptnew[0]', $group['exempt'][0], 'radio');
			showsetting('usergroups_edit_credit_exempt_search', 'exemptnew[1]', $group['exempt'][1], 'radio');
			$exempttype = $group['radminid'] ? ($group['radminid'] == 3 ? 1 : 2) : 3;
			showsetting(($group['radminid'] ? $lang['usergroups_edit_credit_exempt_outperm'] : '').$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[2]', $group['exempt'][2], 'radio', $exempttype == 2 ? 'readonly' : 0, '', '', '', 'm_getattch');
			showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[5]', $group['exempt'][5], 'radio', $exempttype == 1 ? 0 : 'readonly');
			showsetting(($group['radminid'] ? $lang['usergroups_edit_credit_exempt_outperm'] : '').$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[3]', $group['exempt'][3], 'radio', $exempttype == 2 ? 'readonly' : 0, '', '', '', 'm_attachpay');
			showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[6]', $group['exempt'][6], 'radio', $exempttype == 1 ? 0 : 'readonly');
			showsetting(($group['radminid'] ? $lang['usergroups_edit_credit_exempt_outperm'] : '').$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[4]', $group['exempt'][4], 'radio', $exempttype == 2 ? 'readonly' : 0, '', '', '', 'm_threadpay');
			showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[7]', $group['exempt'][7], 'radio', $exempttype == 1 ? 0 : 'readonly');

			showtitle('usergroups_edit_credit_allowrate', '', 0);
			for($i = 1; $i <= 8; $i++) {
				showmultititle();
				if(isset($_G['setting']['extcredits'][$i])) {
					showsetting($_G['setting']['extcredits'][$i]['title'], 'raterangenew['.$i.'][allowrate]', $raterangearray[$i], 'radio');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_isself'], 'raterangenew['.$i.'][isself]', $raterangearray[$i]['isself'], 'radio');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_min'], 'raterangenew['.$i.'][min]', $raterangearray[$i]['min'], 'text');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_max'], 'raterangenew['.$i.'][max]', $raterangearray[$i]['max'], 'text');
					showsetting($_G['setting']['extcredits'][$i]['title'].' '.$lang['usergroups_edit_credit_rate_mrpd'], 'raterangenew['.$i.'][mrpd]', $raterangearray[$i]['mrpd'], 'text');
				}
			}
			showtablefooter();
			showtagfooter('div');
		} else {
			showtagheader('div', 'credit', $anchor == 'credit');
			showtableheader();
			showtitle('usergroups_edit_credit');
			showsetting('usergroups_edit_credit_exempt_sendpm', 'exemptnew[0]', $group['exempt'][0], 'radio');
			showsetting('usergroups_edit_credit_exempt_search', 'exemptnew[1]', $group['exempt'][1], 'radio');
			if($group['radminid']) {
				if($group['radminid'] == 3) {
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[2]', $group['exempt'][2], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_getattch'], 'exemptnew[5]', $group['exempt'][5], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[3]', $group['exempt'][3], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_attachpay'], 'exemptnew[6]', $group['exempt'][6], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_outperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[4]', $group['exempt'][4], 'radio');
					showsetting($lang['usergroups_edit_credit_exempt_inperm'].$lang['usergroups_edit_credit_exempt_threadpay'], 'exemptnew[7]', $group['exempt'][7], 'radio');
				} else {
					echo '<input name="exemptnew[2]" type="hidden" value="1" /><input name="exemptnew[3]" type="hidden" value="1" /><input name="exemptnew[4]" type="hidden" value="1" />'.
						'<input name="exemptnew[5]" type="hidden" value="1" /><input name="exemptnew[6]" type="hidden" value="1" /><input name="exemptnew[7]" type="hidden" value="1" />';
				}
			} else {
				showsetting('usergroups_edit_credit_exempt_getattch', 'exemptnew[2]', $group['exempt'][2], 'radio');
				showsetting('usergroups_edit_credit_exempt_attachpay', 'exemptnew[3]', $group['exempt'][3], 'radio');
				showsetting('usergroups_edit_credit_exempt_threadpay', 'exemptnew[4]', $group['exempt'][4], 'radio');
			}

			echo '<tr><td colspan="2">'.$lang['usergroups_edit_credit_exempt_comment'].'</td></tr>';

			echo '<tr><td colspan="2">';
			showtablefooter();
			showtableheader('usergroups_edit_credit_allowrate', '');

			$titlecolumn[0] = $lang['name'];
			for($i = 1; $i <= 8; $i++) {
				if(isset($_G['setting']['extcredits'][$i])) {
					$titlecolumn[$i] = $_G['setting']['extcredits'][$i]['title'];
				}
			}
			showsubtitle($titlecolumn);
			$leftcolumn = array('enable', 'usergroups_edit_credit_rate_isself', 'usergroups_edit_credit_rate_min', 'usergroups_edit_credit_rate_max', 'usergroups_edit_credit_rate_mrpd');
			foreach($leftcolumn as $value) {
				echo '<tr><td>'.$lang[$value].'</td>';
				foreach($titlecolumn as $subkey => $subvalue) {
					if(!$subkey) continue;
					if($value == 'enable') {
						echo '<td><input type="checkbox" class="checkbox" name="raterangenew['.$subkey.'][allowrate]" value="1" '.(empty($raterangearray[$subkey]) ? '' : 'checked').'></td>';
					} elseif($value == 'usergroups_edit_credit_rate_isself') {
						echo '<td><input type="checkbox" class="checkbox" name="raterangenew['.$subkey.'][isself]" value="1" '.(empty($raterangearray[$subkey]['isself']) ? '' : 'checked').'></td>';
					} elseif($value == 'usergroups_edit_credit_rate_min') {
						echo '<td class="td28"><input type="text" class="txt" name="raterangenew['.$subkey.'][min]" size="3" value="'.$raterangearray[$subkey]['min'].'"></td>';
					} elseif($value == 'usergroups_edit_credit_rate_max') {
						echo '<td class="td28"><input type="text" class="txt" name="raterangenew['.$subkey.'][max]" size="3" value="'.$raterangearray[$subkey]['max'].'"></td>';
					} elseif($value == 'usergroups_edit_credit_rate_mrpd') {
						echo '<td class="td28"><input type="text" class="txt" name="raterangenew['.$subkey.'][mrpd]" size="3" value="'.$raterangearray[$subkey]['mrpd'].'"></td>';
					}
				}
				echo '</tr>';
			}
			echo '<tr><td class="lineheight" colspan="9">'.$lang['usergroups_edit_credit_rate_tips'].'</td></tr>';
			showtablefooter();
			showtagfooter('div');
		}

		showtagheader('div', 'home', $anchor == 'home');
		showtableheader();
		showtitle('usergroups_edit_home');
		showsetting('usergroups_edit_attach_max_space_size', 'maxspacesizenew', $group['maxspacesize'], 'text');
		showsetting('usergroups_edit_home_allow_blog', 'allowblognew', $group['allowblog'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_blog_mod', 'allowblogmodnew', $group['allowblogmod'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_doing', 'allowdoingnew', $group['allowdoing'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_doing_mod', 'allowdoingmodnew', $group['allowdoingmod'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_upload', 'allowuploadnew', $group['allowupload'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_upload_mod', 'allowuploadmodnew', $group['allowuploadmod'], 'radio');
		showmultititle();
		showsetting('usergroups_edit_home_image_max_size', 'maximagesizenew', $group['maximagesize'], 'text');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_share', 'allowsharenew', $group['allowshare'], 'radio', '', 1);
		showsetting('usergroups_edit_home_allow_share_mod', 'allowsharemodnew', $group['allowsharemod'], 'radio');
		showtagfooter('tbody');
		showsetting('usergroups_edit_home_allow_poke', 'allowpokenew', $group['allowpoke'], 'radio');
		showsetting('usergroups_edit_home_allow_friend', 'allowfriendnew', $group['allowfriend'], 'radio');
		showsetting('usergroups_edit_home_allow_click', 'allowclicknew', $group['allowclick'], 'radio');
		showsetting('usergroups_edit_home_allow_comment', 'allowcommentnew', $group['allowcomment'], 'radio');
		showmultititle();
		showsetting('usergroups_edit_home_allow_myop', 'allowmyopnew', $group['allowmyop'], 'radio');
		showsetting('usergroups_edit_home_allow_video_photo_ignore', 'videophotoignorenew', $group['videophotoignore'], 'radio');
		showsetting('usergroups_edit_home_allow_view_video_photo', 'allowviewvideophotonew', $group['allowviewvideophoto'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_html', 'allowspacediyhtmlnew', $group['allowspacediyhtml'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_bbcode', 'allowspacediybbcodenew', $group['allowspacediybbcode'], 'radio');
		showsetting('usergroups_edit_home_allow_space_diy_imgcode', 'allowspacediyimgcodenew', $group['allowspacediyimgcode'], 'radio');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'group', $anchor == 'group');
		showtableheader();
		showtitle('usergroups_edit_group');
		showsetting('usergroups_edit_group_build', 'allowbuildgroupnew', $group['allowbuildgroup'], 'text');
		showsetting('usergroups_edit_post_direct_group', array('allowgroupdirectpostnew', array(
			array(0, $lang['usergroups_edit_post_direct_none']),
			array(1, $lang['usergroups_edit_post_direct_reply']),
			array(2, $lang['usergroups_edit_post_direct_thread']),
			array(3, $lang['usergroups_edit_post_direct_all'])
		)), $group['allowgroupdirectpost'], 'mradio');
		showsetting('usergroups_edit_post_url_group', array('allowgroupposturlnew', array(
			array(0, $lang['usergroups_edit_post_url_banned']),
			array(1, $lang['usergroups_edit_post_url_mod']),
			array(2, $lang['usergroups_edit_post_url_unhandle']),
			array(3, $lang['usergroups_edit_post_url_enable'])
		)), $group['allowgroupposturl'], 'mradio');
		showtablefooter();
		showtagfooter('div');

		showtagheader('div', 'portal', $anchor == 'portal');
		showtableheader();
		showtitle('usergroups_edit_portal');
		showsetting('usergroups_edit_portal_allow_comment_article', 'allowcommentarticlenew', $group['allowcommentarticle'], 'text');
		showsetting('usergroups_edit_portal_allow_post_article', 'allowpostarticlenew', $group['allowpostarticle'], 'radio', '', 1);
		showsetting('usergroups_edit_portal_allow_down_local_img', 'allowdownlocalimgnew', $group['allowdownlocalimg'], 'radio');
		showsetting('usergroups_edit_portal_allow_post_article_moderate', 'allowpostarticlemodnew', $group['allowpostarticlemod'], 'radio');
		showtablefooter();
		showtagfooter('div');

		if($pluginsetting) {
			showtagheader('div', 'plugin', $anchor == 'plugin');
			showtableheader();
			foreach($pluginsetting as $setting) {
				showtitle($setting['name']);
				foreach($setting['setting'] as $varid => $var) {
					if($var['type'] != 'select') {
						showsetting($var['title'], 'pluginnew['.$varid.']', $group['plugin'][$varid], $var['type'], '', 0, $var['description']);
					} else {
						showsetting($var['title'], array('pluginnew['.$varid.']', $var['select']), $group['plugin'][$varid], $var['type'], '', 0, $var['description']);
					}
				}
			}
			showtablefooter();
			showtagfooter('div');
		}

		showtableheader();
		showsubmit('detailsubmit', 'submit');
		showtablefooter();
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
		$pluginvars = array();
		foreach($_G['gp_multinew'] as $k => $row) {
		if(empty($row['single'])) {
			foreach($row as $key => $value) {
				$_G['gp_'.$key] = $value;
			}
			$_G['gp_id'] = $_G['gp_multi'][$k];
		}
		$group = $mgroup[$k];

		$systemnew = 'private';

		if($group['type'] == 'special' && $group['radminid'] > 0) {

			$radminidnew = $group['radminid'];

		} elseif($group['type'] == 'special') {

			$radminidnew = '0';
			if(!$multiset && $_G['gp_system_publicnew']) {
				if($_G['gp_system_dailypricenew'] > 0) {
					if(!$_G['setting']['creditstrans']) {
						cpmsg('usergroups_edit_creditstrans_disabled', '', 'error');
					} else {
						$system_minspannew = $_G['gp_system_minspannew'] <= 0 ? 1 : $_G['gp_system_minspannew'];
						$systemnew = intval($_G['gp_system_dailypricenew'])."\t".intval($system_minspannew);
					}
				} else {
					$systemnew = "0\t0";
				}
			}

		} else {
			$radminidnew = in_array($group['groupid'], array(1, 2, 3)) ? $group['groupid'] : 0;
		}

		if(is_array($_G['gp_raterangenew'])) {
			foreach($_G['gp_raterangenew'] as $key => $rate) {
				if($key >= 1 && $key <= 8 && $rate['allowrate']) {
					if(!$rate['mrpd'] || $rate['max'] <= $rate['min'] || $rate['mrpd'] < max(abs($rate['min']), abs($rate['max']))) {
						cpmsg('usergroups_edit_rate_invalid', '', 'error');
					} else {
						$_G['gp_raterangenew'][$key] = implode("\t", array($key, ($rate['isself'] ? $rate['isself'] : 0), $rate['min'], $rate['max'], $rate['mrpd']));
					}
				} else {
					unset($_G['gp_raterangenew'][$key]);
				}
			}
		}

		if(in_array($group['groupid'], array(1))) {
			$_G['gp_allowvisitnew'] = 2;
		}

		$raterangenew = $_G['gp_raterangenew'] ? implode("\n", $_G['gp_raterangenew']) : '';
		$maxpricenew = $_G['gp_maxpricenew'] < 0 ? 0 : intval($_G['gp_maxpricenew']);
		$maxpostsperhournew = $_G['gp_maxpostsperhournew'] > 255 ? 255 : intval($_G['gp_maxpostsperhournew']);

		$extensionarray = array();
		foreach(explode(',', $_G['gp_attachextensionsnew']) as $extension) {
			if($extension = trim($extension)) {
				$extensionarray[] = $extension;
			}
		}
		$attachextensionsnew = implode(', ', $extensionarray);

		if($_G['gp_maxtradepricenew'] == $_G['gp_mintradepricenew'] || $_G['gp_maxtradepricenew'] < 0 || $_G['gp_mintradepricenew'] <= 0 || ($_G['gp_maxtradepricenew'] && $_G['gp_maxtradepricenew'] < $_G['gp_mintradepricenew'])) {
			cpmsg('trade_fee_error', '', 'error');
		} elseif(($_G['gp_maxrewardpricenew'] != 0 && $_G['gp_minrewardpricenew'] >= $_G['gp_maxrewardpricenew']) || $_G['gp_minrewardpricenew'] < 1 || $_G['gp_minrewardpricenew'] < 0 || $_G['gp_maxrewardpricenew'] < 0) {
			cpmsg('reward_credits_error', '', 'error');
		}

		$exemptnewbin = '';
		for($i = 0;$i < 8;$i++) {
			$exemptnewbin = intval($_G['gp_exemptnew'][$i]).$exemptnewbin;
		}
		$exemptnew = bindec($exemptnewbin);

		$tradesticknew = $_G['gp_tradesticknew'] > 0 ? intval($_G['gp_tradesticknew']) : 0;
		$maxinvitedaynew = $_G['gp_maxinvitedaynew'] > 0 ? intval($_G['gp_maxinvitedaynew']) : 10;
		$maxattachsizenew = $_G['gp_maxattachsizenew'] > 0 ? intval($_G['gp_maxattachsizenew'] * 1024) : 0;
		$maximagesizenew = $_G['gp_maximagesizenew'] > 0 ? intval($_G['gp_maximagesizenew'] * 1024) : 0;
		$maxsizeperdaynew = $_G['gp_maxsizeperdaynew'] > 0 ? intval($_G['gp_maxsizeperdaynew'] * 1024) : 0;
		$maxattachnumnew = $_G['gp_maxattachnumnew'] > 0 ? intval($_G['gp_maxattachnumnew']) : 0;
		$allowrecommendnew = $_G['gp_allowrecommendnew'] > 0 ? intval($_G['gp_allowrecommendnew']) : 0;
		$dataarr = array(
			'grouptitle' => $_G['gp_grouptitlenew'],
			'radminid' => $radminidnew,
			'allowvisit' => $_G['gp_allowvisitnew'],
			'allowsendpm' => $_G['gp_allowsendpmnew'],
			'maxinvitenum' => $_G['gp_maxinvitenumnew'],
			'maxinviteday' => $maxinvitedaynew,
			'allowinvite' => $_G['gp_allowinvitenew'],
			'allowmailinvite' => $_G['gp_allowmailinvitenew'],
			'inviteprice' => $_G['gp_invitepricenew']
		);
		if(!$multiset) {
			$dataarr['system'] = $systemnew;
			if($_FILES['iconnew']) {
				$data = array('extid' => "$_G[gp_id]");
				$iconnew = upload_icon_banner($data, $_FILES['iconnew'], 'usergroup_icon');
			} else {
				$iconnew = $_G['gp_iconnew'];
			}
			if($iconnew) {
				$dataarr['icon'] = $iconnew;
			}
			if($_G['gp_deleteicon']) {
				$valueparse = parse_url($group['icon']);
				if(!isset($valueparse['host'])) {
					@unlink($_G['setting']['attachurl'].'common/'.$group['icon']);
				}
				$dataarr['icon'] = '';
			}
		}
		DB::update('common_usergroup', $dataarr, array('groupid' => $_G['gp_id']));

		if($pluginsetting) {
			foreach($_G['gp_pluginnew'] as $pluginvarid => $value) {
				$pluginvars[$pluginvarid][$_G['gp_id']] = $value;
			}
		}

		DB::update('forum_onlinelist', array(
			'title' => $_G['gp_grouptitlenew'],
		), "groupid='{$_G['gp_id']}'");

		$dataarr = array(
			'readaccess' => $_G['gp_readaccessnew'],
			'allowpost' => $_G['gp_allowpostnew'],
			'allowreply' => $_G['gp_allowreplynew'],
			'allowpostpoll' => $_G['gp_allowpostpollnew'],
			'allowpostreward' => $_G['gp_allowpostrewardnew'],
			'allowposttrade' => $_G['gp_allowposttradenew'],
			'allowpostactivity' => $_G['gp_allowpostactivitynew'],
			'allowdirectpost' => $_G['gp_allowdirectpostnew'],
			'allowgetattach' => $_G['gp_allowgetattachnew'],
			'allowgetimage' => $_G['gp_allowgetimagenew'],
			'allowpostattach' => $_G['gp_allowpostattachnew'],
			'allowvote' => $_G['gp_allowvotenew'],
			'allowsearch' => bindec(intval($_G['gp_allowfulltextnew']).intval($_G['gp_allowsearchnew'][5]).intval($_G['gp_allowsearchnew'][4]).intval($_G['gp_allowsearchnew'][3]).intval($_G['gp_allowsearchnew'][2]).intval($_G['gp_allowsearchnew'][1])),
			'allowcstatus' => $_G['gp_allowcstatusnew'],
			'allowinvisible' => $_G['gp_allowinvisiblenew'],
			'allowtransfer' => $_G['gp_allowtransfernew'],
			'allowsetreadperm' => $_G['gp_allowsetreadpermnew'],
			'allowsetattachperm' => $_G['gp_allowsetattachpermnew'],
			'allowpostimage' => $_G['gp_allowpostimagenew'],
			'allowposttag' => $_G['gp_allowposttagnew'],
			'allowhidecode' => $_G['gp_allowhidecodenew'],
			'allowmediacode' => $_G['gp_allowmediacodenew'],
			'allowhtml' => $_G['gp_allowhtmlnew'],
			'allowanonymous' => $_G['gp_allowanonymousnew'],
			'allowsigbbcode' => $_G['gp_allowsigbbcodenew'],
			'allowsigimgcode' => $_G['gp_allowsigimgcodenew'],
			'allowmagics' => $_G['gp_allowmagicsnew'],
			'disableperiodctrl' => $_G['gp_disableperiodctrlnew'],
			'reasonpm' => $_G['gp_reasonpmnew'],
			'maxprice' => $maxpricenew,
			'maxsigsize' => $_G['gp_maxsigsizenew'],
			'maxspacesize' => $_G['gp_maxspacesizenew'],
			'maxattachsize' => $maxattachsizenew,
			'maximagesize' => $maximagesizenew,
			'maxsizeperday' => $maxsizeperdaynew,
			'maxpostsperhour' => $maxpostsperhournew,
			'attachextensions' => $attachextensionsnew,
			'mintradeprice' => $_G['gp_mintradepricenew'],
			'maxtradeprice' => $_G['gp_maxtradepricenew'],
			'minrewardprice' => $_G['gp_minrewardpricenew'],
			'maxrewardprice' => $_G['gp_maxrewardpricenew'],
			'magicsdiscount' => $_G['gp_magicsdiscountnew'] >= 0 && $_G['gp_magicsdiscountnew'] < 10 ? $_G['gp_magicsdiscountnew'] : 0,
			'maxmagicsweight' => $_G['gp_maxmagicsweightnew'] >= 0 && $_G['gp_maxmagicsweightnew'] <= 60000 ? $_G['gp_maxmagicsweightnew'] : 1,
			'allowpostdebate' => $_G['gp_allowpostdebatenew'],
			'tradestick' => $tradesticknew,
			'maxattachnum' => $maxattachnumnew,
			'allowposturl' => $_G['gp_allowposturlnew'],
			'allowrecommend' => $allowrecommendnew,
			'allowpostrushreply' => $_G['gp_allowpostrushreplynew'],
			'maxfriendnum' => $_G['gp_maxfriendnumnew'],
			'seccode' => $_G['gp_seccodenew'],
			'domainlength' => $_G['gp_domainlengthnew'],
			'disablepostctrl' => $_G['gp_disablepostctrlnew'],
			'allowblog' => $_G['gp_allowblognew'],
			'allowdoing' => $_G['gp_allowdoingnew'],
			'allowupload' => $_G['gp_allowuploadnew'],
			'allowshare' => $_G['gp_allowsharenew'],
			'allowblogmod' => $_G['gp_allowblogmodnew'],
			'allowdoingmod' => $_G['gp_allowdoingmodnew'],
			'allowuploadmod' => $_G['gp_allowuploadmodnew'],
			'allowsharemod' => $_G['gp_allowsharemodnew'],
			'allowpoke' => $_G['gp_allowpokenew'],
			'allowfriend' => $_G['gp_allowfriendnew'],
			'allowclick' => $_G['gp_allowclicknew'],
			'allowcomment' => $_G['gp_allowcommentnew'],
			'allowcommentarticle' => intval($_G['gp_allowcommentarticlenew']),
			'allowmyop' => $_G['gp_allowmyopnew'],
			'allowcommentpost' => bindec(intval($_G['gp_allowcommentpostnew'][2]).intval($_G['gp_allowcommentpostnew'][1])),
			'videophotoignore' => $_G['gp_videophotoignorenew'],
			'allowviewvideophoto' => $_G['gp_allowviewvideophotonew'],
			'allowspacediyhtml' => $_G['gp_allowspacediyhtmlnew'],
			'allowspacediybbcode' => $_G['gp_allowspacediybbcodenew'],
			'allowspacediyimgcode' => $_G['gp_allowspacediyimgcodenew'],
			'allowstatdata' => $_G['gp_allowstatdatanew'],
			'allowpostarticle' => $_G['gp_allowpostarticlenew'],
			'allowpostarticlemod' => $_G['gp_allowpostarticlemodnew'],
			'allowbuildgroup' => $_G['gp_allowbuildgroupnew'],
			'allowgroupdirectpost' => intval($_G['gp_allowgroupdirectpostnew']),
			'allowgroupposturl' => intval($_G['gp_allowgroupposturlnew']),
			'edittimelimit' => intval($_G['gp_edittimelimitnew']),
			'allowcommentreply' => intval($_G['gp_allowcommentreplynew']),
			'allowdownlocalimg' => intval($_G['gp_allowdownlocalimgnew']),
			'allowdownremoteimg' => intval($_G['gp_allowdownremoteimgnew']),
			'allowcommentitem' => intval($_G['gp_allowcommentitemnew']),
			'allowreplycredit' => intval($_G['gp_allowreplycreditnew']),
			'exempt' => $exemptnew,
			'raterange' => $raterangenew,
			'ignorecensor' => intval($_G['gp_ignorecensornew']),
			'allowsendallpm' => intval($_G['gp_allowsendallpmnew']),
		);
		DB::update('common_usergroup_field', $dataarr, array('groupid' => $_G['gp_id']));

		if($_G['setting']['threadplugins']) {
			$allowthreadplugin = unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='allowthreadplugin'"));
			$allowthreadplugin[$_G['gp_id']] = $_G['gp_allowthreadpluginnew'];
			$allowthreadpluginnew = addslashes(serialize($allowthreadplugin));
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('allowthreadplugin', '$allowthreadpluginnew')");
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

		updatecache(array('usergroups', 'onlinelist', 'groupreadaccess'));

		cpmsg('usergroups_edit_succeed', 'action=usergroups&operation=edit&'.($multiset ? 'multi='.implode(',', $_G['gp_multi']) : 'id='.$_G['gp_id']).'&anchor='.$_G['gp_anchor'], 'succeed');
	}

} elseif($operation == 'copy') {

	loadcache('usergroups');

	$source = intval($_G['gp_source']);
	$sourceusergroup = $_G['cache']['usergroups'][$source];

	if(empty($sourceusergroup)) {
		cpmsg('usergroups_copy_source_invalid', '', 'error');
	}

	$delfields = array(
		'usergroups'	=> array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'icon', 'groupavatar'),
	);
	$fields = array(
		'usergroups'		=> fetch_table_struct('common_usergroup'),
		'usergroupfields'	=> fetch_table_struct('common_usergroup_field'),
	);

	if(!submitcheck('copysubmit')) {

		$groupselect = array();
		$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." WHERE groupid NOT IN ('6', '7') ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		while($group = DB::fetch($query)) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}
		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';

		$usergroupselect = '<select name="target[]" size="10" multiple="multiple">'.$groupselect.'</select>';
		$optselect = '<select name="options[]" size="10" multiple="multiple">';
		$fieldarray = array_merge($fields['usergroups'], $fields['usergroupfields']);
		$listfields = array_diff($fieldarray, $delfields['usergroups']);
		foreach($listfields as $field) {
			$optselect .= '<option value="'.$field.'">'.($lang['project_option_group_'.$field] ? $lang['project_option_group_'.$field] : $field).'</option>';
		}
		$optselect .= '</select>';
		shownav('group', 'usergroups_copy');
		showsubmenu('usergroups_copy');
		showtips('usergroups_copy_tips');
		showformheader('usergroups&operation=copy');
		showhiddenfields(array('source' => $source));
		showtableheader();
		showtitle('usergroups_copy');
		showsetting(cplang('usergroups_copy_source').':','','', $sourceusergroup['grouptitle']);
		showsetting('usergroups_copy_target', '', '', $usergroupselect);
		showsetting('usergroups_copy_options', '', '', $optselect);
		showsubmit('copysubmit');
		showtablefooter();
		showformfooter();

	} else {

		$gids = $comma = '';
		if(is_array($_G['gp_target']) && count($_G['gp_target'])) {
			foreach($_G['gp_target'] as $gid) {
				if(($fid = intval($gid)) && $gid != $source ) {
					$gids .= $comma.$gid;
					$comma = ',';
				}
			}
		}
		if(empty($gids)) {
			cpmsg('usergroups_copy_target_invalid', '', 'error');
		}

		$groupoptions = array();
		if(is_array($_G['gp_options']) && !empty($_G['gp_options'])) {
			foreach($_G['gp_options'] as $option) {
				if($option = trim($option)) {
					if(in_array($option, $fields['usergroups'])) {
						$groupoptions['common_usergroup'][] = $option;
					} elseif(in_array($option, $fields['usergroupfields'])) {
						$groupoptions['common_usergroup_field'][] = $option;
					}
				}
			}
		}

		if(empty($groupoptions)) {
			cpmsg('usergroups_copy_options_invalid', '', 'error');
		}
		foreach(array('common_usergroup', 'common_usergroup_field') as $table) {
			if(is_array($groupoptions[$table]) && !empty($groupoptions[$table])) {
				$sourceusergroup = DB::fetch_first("SELECT ".implode($groupoptions[$table],',')." FROM ".DB::table($table)." WHERE groupid='$source'");
				if(!$sourceusergroup) {
					cpmsg('usergroups_copy_source_invalid', '', 'error');
				}
				$sourceusergroup = array_map('addslashes', $sourceusergroup);
				DB::update($table, $sourceusergroup, "groupid IN ($gids)");
			}
		}

		updatecache('usergroups');
		cpmsg('usergroups_copy_succeed', 'action=usergroups', 'succeed');

	}

}

function array_flip_keys($arr) {
	$arr2 = array();
	$arrkeys = @array_keys($arr);
	list(, $first) = @each(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k=>$v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
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

function fetch_table_struct($tablename, $result = 'FIELD') {
	$datas = array();
	$query = DB::query("DESCRIBE ".DB::table($tablename));
	while($data = DB::fetch($query)) {
		$datas[$data['Field']] = $result == 'FIELD' ? $data['Field'] : $data;
	}
	return $datas;
}

?>