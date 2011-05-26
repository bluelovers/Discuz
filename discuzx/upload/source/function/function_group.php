<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_group.php 21655 2011-04-07 01:48:39Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function delgroupcache($fid = 0, $cachearray) {
	$addfid = $fid ? "AND fid='$fid'" : '';
	DB::query("DELETE FROM ".DB::table('forum_groupfield')." WHERE type IN (".dimplode($cachearray).") $addfid");
}

function groupperm(&$forum, $uid, $action = '', $isgroupuser = '') {
	if($forum['status'] != 3 || $forum['type'] != 'sub') {
		return -1;
	}
	if(!empty($forum['founderuid']) && $forum['founderuid'] == $uid) {
		return 'isgroupuser';
	}
	$isgroupuser = empty($isgroupuser) && $isgroupuser !== false ? DB::fetch_first("SELECT * FROM ".DB::table('forum_groupuser')." WHERE fid='$forum[fid]' AND uid='$uid'") : $isgroupuser;
	if($forum['ismoderator'] && !$isgroupuser) {
		return '';
	}
	if($forum['jointype'] < 0 && !$forum['ismoderator']) {
		return 1;
	}
	if(!$forum['gviewperm'] && !$isgroupuser) {
		return 2;
	}
	if($forum['jointype'] == 2 && !$forum['gviewperm'] && !empty($isgroupuser['uid']) && $isgroupuser['level'] == 0) {
		return 3;
	}
	if($action == 'post' && !$isgroupuser) {
		return 4;
	}
	if(is_array($isgroupuser) && $isgroupuser['level'] == 0) {
		return 5;
	}
	return $isgroupuser ? 'isgroupuser' : '';
}

function groupuserlist($fid, $orderby = '', $num = 0, $start = 0, $addwhere = '', $fieldarray = array(), $onlinemember = array()) {

	$fid = intval($fid);
	if($fieldarray && is_array($fieldarray)) {
		$fieldadd = 'uid';
		foreach($fieldarray as $field) {
			$fieldadd .= ' ,'.$field;
		}
	} else {
		$fieldadd = '*';
	}

	$sqladd = $levelwhere = '';
	if($addwhere) {
		if(is_array($addwhere)) {
			foreach($addwhere as $field => $value) {
				if(is_array($value)) {
					$levelwhere = "AND level>'0' ";
					$sqladd .= "AND $field IN (".dimplode($value).") ";
				} else {
					$sqladd .= is_numeric($field) ? "AND $value " : "AND $field='$value' ";
				}
			}
			if(!empty($addwhere['level'])) $levelwhere = '';
		} else {
			$sqladd = $addwhere;
		}
	}

	$orderbyarray = array('level_join' => 'level ASC, joindateline ASC', 'joindateline' => 'joindateline DESC', 'lastupdate' => 'lastupdate DESC', 'threads' => 'threads DESC', 'replies' => 'replies DESC');
	$orderby = !empty($orderbyarray[$orderby]) ? "ORDER BY $orderbyarray[$orderby]" : '';
	$limitsql = $num ? "LIMIT ".($start ? intval($start) : 0).", $num" : '';

	$groupuserlist = array();
	$query = DB::query("SELECT $fieldadd FROM ".DB::table('forum_groupuser')." WHERE fid='$fid' $levelwhere $sqladd $orderby $limitsql");
	while($groupuser = DB::fetch($query)) {
		$groupuserlist[$groupuser['uid']] = $groupuser;
		$groupuserlist[$groupuser['uid']]['online'] = !empty($onlinemember) && is_array($onlinemember) && !empty($onlinemember[$groupuser['uid']]) ? 1 : 0;
	}

	return $groupuserlist;

}

function grouplist($orderby = 'displayorder', $fieldarray = array(), $num = 1, $fids = array(), $sort = 0, $getcount = 0, $grouplevel = array()) {

	if($fieldarray && is_array($fieldarray)) {
		$fieldadd = '';
		foreach($fieldarray as $field) {
			$fieldadd .= ' ,'.$field;
		}
	} else {
		$fieldadd = ' ,ff.*';
	}
	$start = 0;
	if(is_array($num)) {
		list($start, $snum) = $num;
	} else {
		$snum = $num;
	}
	$orderbyarray = array('displayorder' => 'f.displayorder DESC', 'dateline' => 'ff.dateline DESC', 'lastupdate' => 'ff.lastupdate DESC', 'membernum' => 'ff.membernum DESC', 'thread' => 'f.threads DESC', 'activity' => 'f.commoncredits DESC');
	$useindex = $orderby == 'displayorder' ? 'USE INDEX(fup_type)' : '';
	$orderby = !empty($orderby) && $orderbyarray[$orderby] ? "ORDER BY ".$orderbyarray[$orderby] : '';
	$limitsql = $num ? "LIMIT $start, $snum " : '';
	$field = $sort ? 'fup' : 'fid';
	$fids = $fids && is_array($fids) ? 'f.'.$field.' IN ('.dimplode($fids).')' : '';

	$grouplist = array();
	if(empty($getcount)) {
		$fieldsql = 'f.fid, f.name, f.threads, f.posts, f.todayposts '.$fieldadd;
	} else {
		$fieldsql = 'count(*)';
		$orderby  = $limitsql = '';
	}
	$query = DB::query("SELECT $fieldsql FROM ".DB::table('forum_forum')." f $useindex ".(empty($getcount) ? " LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid" : '' )." WHERE".($fids ? " $fids AND " : '')." f.type='sub' AND f.status=3 $orderby $limitsql");
	$orderid = 0;
	if($getcount) {
		return DB::result($query, 0);
	}
	while($group = DB::fetch($query)) {
		$group['iconstatus'] = $group['icon'] ? 1 : 0;
		isset($group['icon']) && $group['icon'] = get_groupimg($group['icon'], 'icon');
		isset($group['banner']) && $group['banner'] = get_groupimg($group['banner']);
		$group['orderid'] = $orderid ? intval($orderid) : '';
		isset($group['dateline']) && $group['dateline'] = $group['dateline'] ? dgmdate($group['dateline'], 'd') : '';
		isset($group['lastupdate']) && $group['lastupdate'] = $group['lastupdate'] ? dgmdate($group['lastupdate'], 'd') : '';
		$group['level'] = !empty($grouplevel) ? intval($grouplevel[$group['fid']]) : 0;
		isset($group['description']) && $group['description'] = cutstr($group['description'], 130);
		$grouplist[$group['fid']] = $group;
		$orderid ++;
	}

	return $grouplist;
}

function mygrouplist($uid, $orderby = '', $fieldarray = array(), $num = 0, $start = 0, $ismanager = 0, $count = 0) {
	$uid = intval($uid);
	if(empty($uid)) {
		return array();
	}
	if(empty($ismanager)) {
		$levelsql = '';
	} elseif($ismanager == 1) {
		$levelsql = ' AND level IN(1,2)';
	} elseif($ismanager == 2) {
		$levelsql = ' AND level IN(3,4)';
	}
	if($count == 1) {
		return DB::result_first("SELECT count(*) FROM ".DB::table('forum_groupuser')." WHERE uid='$uid' $levelsql");
	}
	empty($start) && $start = 0;
	if(!empty($num)) {
		$limitsql = "LIMIT $start, $num";
	} else {
		$limitsql = "LIMIT $start, 100";
	}
	$groupfids = $grouplevel = array();
	$query = DB::query("SELECT fid, level FROM ".DB::table('forum_groupuser')." WHERE uid='$uid' $levelsql ORDER BY lastupdate DESC $limitsql");
	while($group = DB::fetch($query)) {
		$groupfids[] = $group['fid'];
		$grouplevel[$group['fid']] = $group['level'];
	}
	if(empty($groupfids)) {
		return false;
	}
	$mygrouplist = grouplist($orderby, $fieldarray, $num, $groupfids, 0, 0, $grouplevel);

	return $mygrouplist;
}

function get_groupimg($imgname, $imgtype = '') {
	global $_G;
	$imgpath = $_G['setting']['attachurl'].'group/'.$imgname;
	if($imgname) {
		return $imgpath;
	} else {
		if($imgtype == 'icon') {
			return 'static/image/common/groupicon.gif';
		} else {
			return '';
		}
	}
}

function get_groupselect($fup = 0, $groupid = 0, $ajax = 1) {
	global $_G;
	loadcache('grouptype');
	$firstgroup = $_G['cache']['grouptype']['first'];
	$secondgroup = $_G['cache']['grouptype']['second'];
	$grouptypeselect = array('first' => '', 'second' => '');
	if($ajax) {
		$fup = intval($fup);
		$groupid = intval($groupid);
		foreach($firstgroup as $gid => $group) {
			$selected = $fup == $gid ? 'selected="selected"' : '';
			$grouptypeselect['first'] .= $group['secondlist'] ? '<option value="'.$gid.'" '.$selected.'>'.$group['name'].'</option>' : '';
		}

		if($fup) {
			foreach($firstgroup[$fup]['secondlist'] as $sgid) {
				$selected = $sgid == $groupid ? 'selected="selected"' : '';
				$grouptypeselect['second'] .= '<option value="'.$sgid.'" '.$selected.'>'.$secondgroup[$sgid]['name'].'</option>';
			}
		}
	} else {
		foreach($firstgroup as $gid => $group) {
			$grouptypeselect .= '<optgroup label="'.$group['name'].'">';
			if(is_array($group['secondlist'])) {
				foreach($group['secondlist'] as $secondid) {
					$selected = $groupid == $secondid ? 'selected="selected"' : '';
					$grouptypeselect .= '<option value="'.$secondid.'" '.$selected.'>'.$secondgroup[$secondid]['name'].'</option>';
				}
			}
			$grouptypeselect .= '</optgroup>';
		}
	}

	return $grouptypeselect;
}

function get_groupnav($forum) {
	global $_G;
	if(empty($forum) || empty($forum['fid']) || empty($forum['name'])) {
		return '';
	}
	loadcache('grouptype');
	$groupnav = '';
	$groupsecond = $_G['cache']['grouptype']['second'];
	if($forum['type'] == 'sub') {
		$secondtype = !empty($groupsecond[$forum['fup']]) ? $groupsecond[$forum['fup']] : array();
	} else {
		$secondtype = !empty($groupsecond[$forum['fid']]) ? $groupsecond[$forum['fid']] : array();
	}
	$firstid = !empty($secondtype) ? $secondtype['fup'] : (!empty($forum['fup']) ? $forum['fup'] : $forum['fid']);
	$firsttype = $_G['cache']['grouptype']['first'][$firstid];
	if($firsttype) {
		$groupnav = ' <em>&rsaquo;</em> <a href="group.php?gid='.$firsttype['fid'].'">'.$firsttype['name'].'</a>';
	}
	if($secondtype) {
		$groupnav .= ' <em>&rsaquo;</em> <a href="group.php?sgid='.$secondtype['fid'].'">'.$secondtype['name'].'</a>';
	}
	if($forum['type'] == 'sub') {
		$mod_action = $_G['gp_mod'] == 'forumdisplay' || $_G['gp_mod'] == 'viewthread' ? 'mod=forumdisplay&action=list' : 'mod=group';
		$groupnav .= ($groupnav ? ' <em>&rsaquo;</em> ' : '').'<a href="forum.php?'.$mod_action.'&fid='.$forum['fid'].'">'.$forum['name'].'</a>';
	}
	return array('nav' => $groupnav, 'first' => $firsttype, 'second' => $secondtype);
}

function get_viewedgroup() {
	$groupviewed_list = $list = array();
	$groupviewed = getcookie('groupviewed');
	$groupviewed = $groupviewed ? explode(',', $groupviewed) : array();
	if($groupviewed) {
		$query = DB::query("SELECT f.fid, f.name, ff.icon, ff.membernum FROM ".DB::table('forum_forum')." as f LEFT JOIN ".DB::table('forum_forumfield')." as ff ON ff.fid=f.fid WHERE f.fid IN(".dimplode($groupviewed).")");
		while($row = DB::fetch($query)) {
			$row['icon'] = get_groupimg($row['icon'], 'icon');
			$list[$row['fid']] = $row;
		}
	}
	foreach($groupviewed as $fid) {
		$groupviewed_list[$fid] = $list[$fid];
	}
	return $groupviewed_list;
}

function getgroupthread($fid, $type, $timestamp = 0, $num = 10, $privacy = 0) {
	$typearray = array('replies', 'views', 'dateline', 'lastpost', 'digest');
	$type = in_array($type, $typearray) ? $type : '';

	$groupthreadlist = array();
	if($type) {
		$sqltimeadd = $timestamp ? "AND $type>='".(TIMESTAMP - $timestamp)."'" : '';
		$sqladd = $type == 'digest' ? "AND digest>'0' ORDER BY dateline DESC" : "ORDER BY $type DESC";
		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE fid='$fid' AND displayorder>='0' $sqltimeadd $sqladd LIMIT 0, $num");
		while($thread = DB::fetch($query)) {
			$groupthreadlist[$thread['tid']]['tid'] = $thread['tid'];
			$groupthreadlist[$thread['tid']]['subject'] = $thread['subject'];
			$groupthreadlist[$thread['tid']]['special'] = $thread['special'];
			$groupthreadlist[$thread['tid']]['closed'] = $thread['closed'];
			$groupthreadlist[$thread['tid']]['dateline'] = dgmdate($thread['dateline'], 'd');
			$groupthreadlist[$thread['tid']]['author'] = $thread['author'];
			$groupthreadlist[$thread['tid']]['authorid'] = $thread['authorid'];
			$groupthreadlist[$thread['tid']]['views'] = $thread['views'];
			$groupthreadlist[$thread['tid']]['replies'] = $thread['replies'];
			$groupthreadlist[$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
			$groupthreadlist[$thread['tid']]['lastposter'] = $thread['lastposter'];
			$groupthreadlist[$thread['tid']]['lastposterenc'] = rawurlencode($thread['lastposter']);
		}
	}

	return $groupthreadlist;
}

function getgroupcache($fid, $typearray = array(), $timestamp = 0, $num = 10, $privacy = 0, $force = 0) {
	$groupcache = array();
	$typeadd = $typearray && is_array($typearray) ? "AND type IN(".dimplode($typearray).")" : '';

	if(!$force) {
		$query = DB::query("SELECT fid, dateline, type, data FROM ".DB::table('forum_groupfield')." WHERE fid='$fid' AND privacy='$privacy' $typeadd");
		while($group = DB::fetch($query)) {
			$groupcache[$group['type']] = unserialize($group['data']);
			$groupcache[$group['type']]['dateline'] = $group['dateline'];
		}
	}

	$cachetimearray = array('replies' => 3600, 'views' => 3600, 'dateline' => 900, 'lastpost' => 3600, 'digest' => 86400, 'ranking' => 86400, 'activityuser' => 3600);
	$userdataarray = array('activityuser' => 'lastupdate', 'newuserlist' => 'joindateline');
	foreach($typearray as $type) {
		if(empty($groupcache[$type]) || (!empty($cachetimearray[$type]) && TIMESTAMP - $groupcache[$type]['dateline'] > $cachetimearray[$type])) {
			if($type == 'ranking') {
				$groupcache[$type]['data'] = getgroupranking($fid, $groupcache[$type]['data']['today']);
			} elseif(in_array($type, array('activityuser', 'newuserlist'))) {
				$num = $type == 'activityuser' ? 50 : 8;
				$groupcache[$type]['data'] = groupuserlist($fid, $userdataarray[$type], $num, '', "AND level>'0'");
			} else {
				$groupcache[$type]['data'] = getgroupthread($fid, $type, $timestamp, $num, $privacy);
			}
			if(!$force && $fid) {
				DB::query("REPLACE INTO ".DB::table('forum_groupfield')." (fid, dateline, type, data) VALUES ('$fid', '".TIMESTAMP."', '$type', '".addslashes(serialize($groupcache[$type]))."')", 'UNBUFFERED');
			}
		}
	}

	return $groupcache;
}

function getgroupranking($fid = '', $nowranking = '', $num = 100) {
	$topgroup = $rankingdata = $topyesterday = array();
	if($fid) {
		updateactivity($fid);
	}

	$ranking = 1;
	$query = DB::query("SELECT f.fid FROM ".DB::table('forum_forum')." as f LEFT JOIN ".DB::table('forum_forumfield')." as ff ON ff.fid=f.fid WHERE f.type='sub' AND f.status='3' ORDER BY ff.activity DESC LIMIT 0, 1000");
	while($group = DB::fetch($query)) {
		$topgroup[$group['fid']] = $ranking++;
	}

	if($fid && $topgroup) {
		$rankingdata['yesterday'] = intval($nowranking);
		$rankingdata['today'] = intval($topgroup[$fid]);
		$rankingdata['trend'] = $rankingdata['yesterday'] ? grouptrend($rankingdata['yesterday'], $rankingdata['today']) : 0;
		$topgroup = $rankingdata;
	} else {
		$query = DB::query("SELECT * FROM ".DB::table('forum_groupranking')." ORDER BY today LIMIT 0, $num");
		while($top = DB::fetch($query)) {
			$topyesterday[$top['fid']] = $top;
		}

		foreach($topgroup as $forumid => $today) {
			$yesterday = intval($topyesterday[$forumid]);
			$trend = $yesterday ? grouptrend($yesterday, $today) : 0;
				DB::query("REPLACE INTO ".DB::table('forum_groupranking')." (fid, yesterday, today, trend) VALUES ('$forumid', '$yesterday', '$today', '$trend')", 'UNBUFFERED');
		}
		$topgroup = $topyesterday;
	}

	return $topgroup;
}

function grouponline($fid, $getlist = '') {
	$fid = intval($fid);
	if(empty($getlist)) {
		$onlinemember = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_session')." WHERE fid='$fid'");
		$onlinemember['count'] = $onlinemember ? intval($onlinemember) : 0;
	} else {
		$onlinemember = array('count' => 0, 'list' => array());
		$query = DB::query("SELECT uid FROM ".DB::table('common_session')." WHERE fid='$fid'");
		while($member = DB::fetch($query)) {
			if($member['uid']) {
				$onlinemember['list'][$member['uid']] = $member['uid'];
			}
			$onlinemember['count']++;
		}
	}
	return $onlinemember;
}

function grouptrend($yesterday, $today) {
	$trend = $yesterday - $today;
	return $trend;
}

function write_groupviewed($fid) {
	$fid = intval($fid);
	if($fid) {
		$groupviewed_limit = 8;
		$groupviewed = getcookie('groupviewed');
		if(!strexists(",$groupviewed,", ",$fid,")) {
			$groupviewed = $groupviewed ? explode(',', $groupviewed) : array();
			$groupviewed[] = $fid;
			if(count($groupviewed) > $groupviewed_limit) {
				array_shift($groupviewed);
			}
			dsetcookie('groupviewed', implode(',', $groupviewed), 86400);
		}
	}
}

function updateactivity($fid, $activity = 1) {
	$fid = $fid ? intval($fid) : intval($_G['fid']);
	if($activity) {
		$forumdata = DB::fetch_first("SELECT f.threads, f.posts, ff.dateline, ff.membernum, ff.activity FROM ".DB::table('forum_forum')." as f LEFT JOIN ".DB::table('forum_forumfield')." as ff ON ff.fid=f.fid WHERE f.fid='$fid'");
		if(!$forumdata['activity']) {
			$perpost = intval(($forumdata['threads'] + $forumdata['posts']) / ((TIMESTAMP - $forumdata['dateline']) / 86400));
			$activity = intval($forumdata['threads'] / 2 + $forumdata['posts'] / 5 + $forumdata['membernum'] / 10 + $perpost * 2);
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET activity='$activity' WHERE fid='$fid'");
		}
	}
	DB::query("UPDATE ".DB::table('forum_forumfield')." SET lastupdate='".TIMESTAMP."' WHERE fid='$fid'");
}

function update_groupmoderators($fid) {
	global $_G;
	if(empty($fid)) return false;
	$moderators = groupuserlist($fid, 'level_join', 0, 0, array('level' => array('1', '2')), array('username', 'level'));
	if(!empty($moderators)) {
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET moderators='".addslashes(serialize($moderators))."' WHERE fid='$fid'");
		return $moderators;
	} else {
		return array();
	}
}

function update_usergroups($uids) {
	global $_G;
	if(empty($uids)) return '';
	if(!is_array($uids)) $uids = array($uids);
	foreach($uids as $uid) {
		$groups = $grouptype = $usergroups = array();
		$query = DB::query("SELECT f.fid, f.fup, f.name FROM ".DB::table('forum_groupuser')." g LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=g.fid WHERE g.uid='$uid' AND g.level>0 ORDER BY g.lastupdate DESC");
		while($group = DB::fetch($query)) {
			$groups[$group['fid']] = $group['name'];
			$typegroup[$group['fup']][] = $group['fid'];
		}
		if(!empty($typegroup)) {
			$fups = array_keys($typegroup);
			$query = DB::query("SELECT fid, fup, name FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($fups).")");
			while($fup = DB::fetch($query)) {
				$grouptype[$fup['fid']] = $fup;
				$grouptype[$fup['fid']]['groups'] = implode(',', $typegroup[$fup['fid']]);
			}
			$usergroups = array('groups' => $groups, 'grouptype' => $grouptype);
			if(!empty($usergroups)) {
				DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groups='".addslashes(serialize($usergroups))."' WHERE uid='$uid'");
				$attentiongroups = DB::result_first("SELECT attentiongroup FROM ".DB::table('common_member_field_forum')." WHERE uid='$uid'");
				if($attentiongroups) {
					$attentiongroups = explode(',', $attentiongroups);
					$updateattention = 0;
					foreach($attentiongroups as $key => $val) {
						if(empty($usergroups['groups'][$val])) {
							unset($attentiongroups[$key]);
							$updateattention = 1;
						}
					}
					if($updateattention) {
						DB::query("UPDATE ".DB::table('common_member_field_forum')." SET attentiongroup='".implode(',', $attentiongroups)."' WHERE uid='$uid'");
						$_G['member']['attentiongroup'] = implode(',', $attentiongroups);
					}
				}
			}
		} else {
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groups='', attentiongroup='' WHERE uid='$uid'");
		}
	}
	return $usergroups;
}
?>