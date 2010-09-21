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

/**
 * 各版塊的推薦群組
 * hack:
 *		單一群組可推薦多個版塊
 *		版塊也可進行推薦
 */
function build_cache_forumrecommend() {
	/*
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
	*/

/*
$data['forumrecommend'] = array (
  55 =>
  array (
    0 =>
    array (
      'fid' => '221',
      'name' => '断罪の楽園',
      'icon' => 'static/image/common/groupicon.gif',
    ),
  ),
  75 =>
  array (
    0 =>
    array (
      'fid' => '227',
      'name' => 'fhgfh',
      'icon' => 'data/attachment/group/70/group_227_icon.jpg',
    ),
  ),
);
*/

	// bluelovers
	require_once libfile('function/group');

	$data = array();
	$query = DB::query("SELECT f.fid, f.name, ff.icon, f.fup, f.recommend, f.status FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.recommend <> '' AND f.recommend <> '0'");
	while($row = DB::fetch($query)) {
		if ($recommend = array_unique(explode(',', $row['recommend']))) {
			$row['icon'] = get_groupimg($row['icon'], 'icon');

			unset($row['recommend']);

			foreach($recommend as $fid) {
				if ($fid = intval($fid)) {
					$data[$fid][] = $row;
				}
			}

		};
	}
	// bluelovers

	save_syscache('forumrecommend', $data);
}

?>