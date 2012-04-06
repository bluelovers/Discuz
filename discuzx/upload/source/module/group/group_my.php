<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_my.php 14805 2010-08-20 06:06:24Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['mnid'] = 'mn_group';
if(!$_G['uid']) {
	showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
}
if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
require_once libfile('function/group');

$view = $_G['gp_view'] && in_array($_G['gp_view'], array('manager', 'join', 'groupthread', 'mythread')) ? $_G['gp_view'] : 'groupthread';
$actives = array('manager' => '', 'join' => '', 'groupthread' => '', 'mythread' => '');
$actives[$view] = ' class="a"';

$perpage = 20;
$page = intval($_G['gp_page']) ? intval($_G['gp_page']) : 1;
$start = ($page - 1) * $perpage;

if($view == 'groupthread' || $view == 'mythread') {
	$typeid = intval($_G['gp_typeid']);
	$attentiongroups = $usergroups = array();
	$usergroups = getuserprofile('groups');
	if(!empty($usergroups)) {
		$usergroups = unserialize($usergroups);
	} else {
		$usergroups = update_usergroups($_G['uid']);
	}
	if($view == 'groupthread' && empty($typeid) && !empty($usergroups['grouptype'])) {
		$attentiongroup = $_G['member']['attentiongroup'];
		if(empty($attentiongroup)) {
			$attentiongroups = array_slice(array_keys($usergroups['groups']), 0, 1);
		} else {
			$attentiongroups = explode(',', $attentiongroup);
		}
		$attentionthread = $attentiongroup_icon = array();
		$attentiongroupfid = '';
		$query = DB::query("SELECT fid, icon FROM ".DB::table('forum_forumfield')." WHERE fid IN (".dimplode($attentiongroups).")");
		while($row = DB::fetch($query)) {
			$attentiongroup_icon[$row[fid]] = get_groupimg($row['icon'], 'icon');
		}
		foreach($attentiongroups as $groupid) {
			$attentiongroupfid .= $attentiongroupfid ? ','.$groupid : $groupid;
			if($page == 1) {
				$query = DB::query("SELECT fid, tid, subject, lastpost, lastposter, views, replies FROM ".DB::table('forum_thread')." WHERE fid='$groupid' AND displayorder='0' ORDER BY lastpost DESC LIMIT 5");
				while($thread = DB::fetch($query)) {
					$attentionthread[$groupid][$thread['tid']]['fid'] = $thread['fid'];
					$attentionthread[$groupid][$thread['tid']]['subject'] = $thread['subject'];
					$attentionthread[$groupid][$thread['tid']]['groupname'] = $usergroups['groups'][$thread['fid']];
					$attentionthread[$groupid][$thread['tid']]['views'] =  $thread['views'];
					$attentionthread[$groupid][$thread['tid']]['replies'] =  $thread['replies'];
					$attentionthread[$groupid][$thread['tid']]['lastposter'] =  $thread['lastposter'];
					$attentionthread[$groupid][$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
					$attentionthread[$groupid][$thread['tid']]['folder'] = 'common';
					if(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
						$attentionthread[$groupid][$thread['tid']]['folder'] = 'new';
					}
				}
			}
		}
	}

	$mygrouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 50);
	if($mygrouplist) {
		$managegroup = $commongroup = $groupthreadlist = array();
		foreach($mygrouplist as $fid => $group) {
			if($group['level'] == 1 || $group['level'] == 2) {
				if(count($managegroup) == 8) {
					continue;
				}
				$managegroup[$fid]['name'] = $group['name'];
				$managegroup[$fid]['icon'] = $group['icon'];
			} else {
				if(count($commongroup) == 8) {
					continue;
				}
				$commongroup[$fid]['name'] = $group['name'];
				$commongroup[$fid]['icon'] = $group['icon'];
			}
		}

		$mygroupfid = array_keys($mygrouplist);
		if($typeid && !empty($usergroups['grouptype'][$typeid]['groups'])) {
			$mygroupfid = explode(',', $usergroups['grouptype'][$typeid]['groups']);
			$typeurl = '&typeid='.$typeid;
		} else {
			$typeid = 0;
		}
		$mythreadsql = $view == 'mythread' ? " AND authorid='$_G[uid]'": '';
		if(!empty($attentiongroupfid) && !empty($mygroupfid)) {
			$mygroupfid = array_diff($mygroupfid, explode(',', $attentiongroupfid));
		}
		if($mygroupfid) {
			if($view != 'mythread') {
				$query = DB::query("SELECT fid, tid, subject, lastpost, lastposter, views, replies FROM ".DB::table('forum_thread')." FORCE INDEX(isgroup) WHERE isgroup=1 AND lastpost>".(time()-86400*30)." AND fid IN (".dimplode($mygroupfid).") AND displayorder='0' $mythreadsql ORDER BY lastpost DESC LIMIT $start, $perpage");
			} else {
				$query = DB::query("SELECT fid, tid, subject, lastpost, lastposter, views, replies FROM ".DB::table('forum_thread')." WHERE authorid='$_G[uid]' AND fid IN (".dimplode($mygroupfid).") ORDER BY lastpost DESC LIMIT $start, $perpage");
			}

			while($thread = DB::fetch($query)) {
				$groupthreadlist[$thread['tid']]['fid'] = $thread['fid'];
				$groupthreadlist[$thread['tid']]['subject'] = $thread['subject'];
				$groupthreadlist[$thread['tid']]['groupname'] = $mygrouplist[$thread['fid']]['name'];
				$groupthreadlist[$thread['tid']]['views'] =  $thread['views'];
				$groupthreadlist[$thread['tid']]['replies'] =  $thread['replies'];
				$groupthreadlist[$thread['tid']]['lastposter'] =  $thread['lastposter'];
				$groupthreadlist[$thread['tid']]['lastpost'] = dgmdate($thread['lastpost'], 'u');
				$groupthreadlist[$thread['tid']]['folder'] = 'common';
				if(empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE) {
					$groupthreadlist[$thread['tid']]['folder'] = 'new';
				}
			}
			if($view == 'mythread') {
				$multipage = simplepage(count($groupthreadlist), $perpage, $page, 'group.php?mod=my&view='.$view.$typeurl);
			}
		}
	}
} elseif($view == 'manager' || $view == 'join') {
	$perpage = 40;
	$start = ($page - 1) * $perpage;
	$ismanager = $view == 'manager' ? 1 : 2;
	$num = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), 0, 0, $ismanager, 1);
	$multipage = multi($num, $perpage, $page, 'group.php?mod=my&view='.$view);
	$grouplist = mygrouplist($_G['uid'], 'lastupdate', array('f.name', 'ff.icon'), $perpage, $start, $ismanager);
}

$frienduidarray = $friendgrouplist = $randgroupdata = $randgrouplist = $randgroup = array();
loadcache('groupindex');
$randgroupdata = $_G['cache']['groupindex']['randgroupdata'];
if($randgroupdata) {
	foreach($randgroupdata as $groupid => $rgroup) {
		if($rgroup['iconstatus']) {
			$randgrouplist[$groupid] = $rgroup;
		}
	}
}

if(count($randgrouplist) > 9) {
	foreach(array_rand($randgrouplist, 9) as $fid) {
		$randgroup[] = $randgrouplist[$fid];
	}
} elseif (count($randgrouplist)) {
	$randgroup = $randgrouplist;
}

require_once libfile('function/friend');
$frienduid = friend_list($_G['uid'], 50);
if($frienduid && is_array($frienduid)) {
	foreach($frienduid as $friend) {
		$frienduidarray[] = $friend['fuid'];
	}

	$query = DB::query("SELECT f.fid, f.name, ff.icon
		FROM ".DB::table('forum_groupuser')." g
		LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=g.fid
		LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid
		WHERE g.uid IN (".dimplode($frienduidarray).") LIMIT 0, 9");
	while($group = DB::fetch($query)) {
		$group['icon'] = get_groupimg($group['icon'], 'icon');
		$friendgrouplist[$group['fid']] = $group;
	}
}

$navtitle = $_G['username'].lang('core', 'title_of').$_G['setting']['navs'][3]['navname'];

include_once template("diy:group/group_my");

?>