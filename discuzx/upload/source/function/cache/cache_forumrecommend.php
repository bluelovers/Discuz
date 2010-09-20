<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forumrecommend.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_forumrecommend() {
	$data = array();
	$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type<>'group' AND status<>3");

	while($row = DB::fetch($query)) {
		require_once libfile('function/group');
		$squery = DB::query("SELECT f.fid, f.name, ff.icon FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE recommend='$row[fid]'");
		while($group = DB::fetch($squery)) {
			$group['icon'] = get_groupimg($group['icon'], 'icon');
			$data[$row['fid']][] = $group;
		}
	}

	save_syscache('forumrecommend', $data);
}

?>