<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forums.php 16980 2010-09-17 09:56:45Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_forums() {
	$data = array();
	$query = DB::query("SELECT f.fid, f.type, f.name, f.fup, f.simple, f.status, f.allowpostspecial, ff.viewperm, ff.formulaperm, ff.viewperm, ff.postperm, ff.replyperm, ff.getattachperm, ff.postattachperm, ff.extra, ff.commentitem, ff.hidemenu, a.uid FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid LEFT JOIN ".DB::table('forum_access')." a ON a.fid=f.fid AND a.allowview>'0' WHERE f.status<>'3' ORDER BY f.type, f.displayorder");

	$usergroups = $nopermgroup = $pluginvalue = $forumlist = array();
	$nopermdefault = array(
		'viewperm' => array(),
		'getattachperm' => array(),
		'postperm' => array(7),
		'replyperm' => array(7),
		'postattachperm' => array(7),
	);
	$pluginvalue = pluginsettingvalue('forums');

	$squery = DB::query("SELECT groupid, type FROM ".DB::table('common_usergroup')."");
	while($usergroup = DB::fetch($squery)) {
		$usergroups[$usergroup['groupid']] = $usergroup['type'];
		$type = $usergroup['type'] == 'member' ? 0 : 1;
		$nopermgroup[$type][] = $usergroup['groupid'];
	}
	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');
	$forumnoperms = array();
	while($forum = DB::fetch($query)) {
		foreach($perms as $perm) {
			$permgroups = explode("\t", $forum[$perm]);
			$membertype = $forum[$perm] ? array_intersect($nopermgroup[0], $permgroups) : TRUE;
			$forumnoperm = $forum[$perm] ? array_diff(array_keys($usergroups), $permgroups) : $nopermdefault[$perm];
			foreach($forumnoperm as $groupid) {
				$nopermtype = $membertype && $groupid == 7 ? 'login' : ($usergroups[$groupid] == 'system' || $usergroups[$groupid] == 'special' ? 'none' : ($membertype ? 'upgrade' : 'none'));
				$forumnoperms[$forum['fid']][$perm][$groupid] = array($nopermtype, $permgroups);
			}
		}

		$forum['orderby'] = bindec((($forum['simple'] & 128) ? 1 : 0).(($forum['simple'] & 64) ? 1 : 0));
		$forum['ascdesc'] = ($forum['simple'] & 32) ? 'ASC' : 'DESC';
		$forum['extra'] = unserialize($forum['extra']);
		if(!is_array($forum['extra'])) {
			$forum['extra'] = array();
		}

		if(!isset($forumlist[$forum['fid']])) {
			$forum['name'] = strip_tags($forum['name']);
			if($forum['uid']) {
				$forum['users'] = "\t$forum[uid]\t";
			}
			unset($forum['uid']);
			if($forum['fup']) {
				$forumlist[$forum['fup']]['count']++;
			}
			$forumlist[$forum['fid']] = $forum;
		} elseif($forum['uid']) {
			if(!$forumlist[$forum['fid']]['users']) {
				$forumlist[$forum['fid']]['users'] = "\t";
			}
			$forumlist[$forum['fid']]['users'] .= "$forum[uid]\t";
		}
	}
	save_syscache('nopermission', $forumnoperms);

	$data = array();
	if(!empty($forumlist)) {
		foreach($forumlist as $fid1 => $forum1) {
			if(($forum1['type'] == 'group' && $forum1['count'])) {
				$data[$fid1] = formatforumdata($forum1, $pluginvalue);
				unset($data[$fid1]['users'], $data[$fid1]['allowpostspecial'], $data[$fid1]['commentitem']);
				foreach($forumlist as $fid2 => $forum2) {
					if($forum2['fup'] == $fid1 && $forum2['type'] == 'forum') {
						$data[$fid2] = formatforumdata($forum2, $pluginvalue);
						foreach($forumlist as $fid3 => $forum3) {
							if($forum3['fup'] == $fid2 && $forum3['type'] == 'sub') {
								$data[$fid3] = formatforumdata($forum3, $pluginvalue);
							}
						}
					}
				}
			}
		}
	}
	save_syscache('forums', $data);
}

function formatforumdata($forum, &$pluginvalue) {
	static $keys = array('fid', 'type', 'name', 'fup', 'viewperm', 'postperm', 'orderby', 'ascdesc', 'users', 'status',
		'hidemenu', 'extra', 'plugin', 'allowpostspecial', 'commentitem');
	static $orders = array('lastpost', 'dateline', 'replies', 'views');

	$data = array();
	foreach ($keys as $key) {
		switch ($key) {
			case 'orderby': $data[$key] = $orders[$forum['orderby']]; break;
			case 'plugin': $data[$key] = $pluginvalue[$forum['fid']]; break;
			case 'allowpostspecial': $data[$key] = sprintf('%06b', $forum['allowpostspecial']); break;
			default: $data[$key] = $forum[$key];
		}
	}
	return $data;
}

?>