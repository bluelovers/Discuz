<?php

/**
 *      [Discuz! X1.5] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $author : CongYuShuai(Max.Cong) Date:2010-09-09$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_groupfriend_group extends plugin_groupfriend {
	public function group_side_top() {
		global $_G;

		loadcache('plugin');
		$var = $_G['cache']['plugin']['groupfriend'];
		$var['timelimit'] = $var['timelimit'] > 0 ? intval($var['timelimit']) : 1 ;
		$var['numberlimit'] = $var['numberlimit'] > 0 ? $var['numberlimit'] : 8 ;
		if($_G['gp_groupfriend']) {
			if($_G['forum']['founderuid'] == $_G['uid']) {
				return;
			}
			$query = DB::query("SELECT f.*, ff.founderuid FROM ".DB::table('forum_forumfield')." ff LEFT JOIN ".DB::table('forum_forum')." f ON f.fid = ff.fid WHERE ff.founderuid = '{$_G['uid']}' ORDER BY ff.fid DESC");
			while($value = DB::fetch($query)) {
				$mygroupids[] = $value['fid'];
				$mygrouplist[$value['fid']] = $value;
			}
			if($mygrouplist[$_G['forum']['fid']]) {
				showmessage(lang('plugin/groupfriend', 'selfgroup'));
			}
			$query = DB::query("SELECT * FROM ".DB::table('plugin_groupfriend')." WHERE friendid = '{$_G['forum']['fid']}'");
			while($value = DB::fetch($query)) {
				if($mygrouplist[$value['groupid']]) {
					if(!$lastdateline) {
						$lastdateline = $value['dateline'];
					}
					$mygroupfriends[] = $value['groupid'];
				}
				$theforumfriends[$value['groupid']] = $value;
			}

			if($_G['timestamp'] - $lastdateline < ($var['timelimit'] * 10)) {
				showmessage(lang('plugin/groupfriend', 'timelimit'));
			}
			include_once libfile('function/group');
			$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');

			if(submitcheck('groupfriendsubmit')) {
				$friendid = $_POST['friendid'];
				if(is_array($_POST['groupids'])) {
					foreach($_POST['groupids'] AS $key => $val) {
						$sqlstr .= ($sqlstr ? ',' : '')."('$val', '{$_G['forum']['fid']}', '{$_G['timestamp']}')";
					}
				}
				DB::query("DELETE FROM ".DB::table('plugin_groupfriend')." WHERE groupid IN ('".implode("','", $mygroupids)."') AND friendid = '{$_G['forum']['fid']}'");
				if($sqlstr) {
					DB::query("INSERT INTO ".DB::table('plugin_groupfriend')." (groupid, friendid, dateline) VALUES $sqlstr");
					if($var['allowpm'] == 1) {
						foreach($_POST['groupids'] AS $key => $val) {
							$addgrouplist[] = "[b][url={$_G['siteurl']}forum.php?mod=group&fid={$mygrouplist[$val]['fid']}]".$mygrouplist[$val]['name']."[/url][/b]";
						}
						$addgrouplist_str = implode("、", $addgrouplist);
						loaducenter();

						uc_pm_send(0, $_G['forum']['founderuid'], lang('plugin/groupfriend', 'groupfriendaddpm_title'), $_G['forum']['name'].lang('plugin/groupfriend', 'groupfriendaddpm_message', array('username' => $_G['member']['username'], 'addgrouplist' => $addgrouplist_str)));
					}
				}
				showmessage(lang('plugin/groupfriend', 'groupfrienddone'), 'forum.php?mod=group&fid='.$_G['forum']['fid']);
			}
			if(!$mygrouplist) {
				showmessage(lang('plugin/groupfriend', 'nonegroups'));
			}
			include_once template('groupfriend:groupfriend_box');
			exit;
		} else {
			if($_G['forum']['founderuid'] != $_G['uid']) {
				$groups_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forumfield')." WHERE founderuid = '{$_G['uid']}' LIMIT 1");
				if($groups_count > 0 && $_G['forum']['founderuid'] != $_G['uid']) {
					$btn_str = "<div class='hm bn'><button class=\"pn vm\" type=\"submit\" onclick=\"showWindow('groupfriend', 'forum.php?mod=group&fid={$_G['forum']['fid']}&groupfriend=true');\"><strong>".lang('plugin/groupfriend', 'addgroupfriend')."</strong></button></div>";
				}
			} else {
				$btn_str = '';
			}

			//已添加的友情群組
			include_once libfile('function/group');
			$query = DB::query("SELECT f.*, ff.icon FROM ".DB::table('plugin_groupfriend')." gf LEFT JOIN ".DB::table('forum_forum')." f ON f.fid = gf.friendid LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid = gf.friendid WHERE gf.groupid = '{$_G['forum']['fid']}' ORDER BY gf.dateline DESC LIMIT {$var['numberlimit']}");
			while($value = DB::fetch($query)) {
				$value['icon'] = get_groupimg($value['icon'], 'icon');
				$friendlist_str .= "<li class='z' style='width:24.5%;text-algin:center;'><a href='forum.php?mod=group&fid={$value[fid]}' target = '_blank' title='{$value['name']}'><img src='{$value['icon']}' /></a></li>";
			}
			if($friendlist_str) {
				include template("groupfriend:groupfriend_rightside");
			}
			$friendlist = $btn_str.$friendlist;
		}
		return $friendlist;
	}
}

//友情群組的父類
class plugin_groupfriend {

}

?>