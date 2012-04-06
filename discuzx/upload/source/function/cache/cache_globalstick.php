<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_globalstick.php 22908 2011-05-31 02:49:40Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_globalstick() {
	$data = array();
	$query = DB::query("SELECT fid, type, fup FROM ".DB::table('forum_forum')." WHERE status='1' AND type IN ('forum', 'sub') ORDER BY type");

	$fuparray = $threadarray = array();
	while($forum = DB::fetch($query)) {
		switch($forum['type']) {
			case 'forum':
				$fuparray[$forum['fid']] = $forum['fup'];
				break;
			case 'sub':
				$fuparray[$forum['fid']] = $fuparray[$forum['fup']];
				break;
		}
	}
	$query = DB::query("SELECT tid, fid, displayorder FROM ".DB::table('forum_thread')." WHERE fid>'0' AND displayorder IN (2, 3)");
	while($thread = DB::fetch($query)) {
		switch($thread['displayorder']) {
			case 2:
				$threadarray[$fuparray[$thread['fid']]][] = $thread['tid'];
				break;
			case 3:
				$threadarray['global'][] = $thread['tid'];
				break;
		}
	}
	foreach(array_unique($fuparray) as $gid) {
		if(!empty($threadarray[$gid])) {
			$data['categories'][$gid] = array(
				'tids'	=> dimplode($threadarray[$gid]),
				'count'	=> intval(@count($threadarray[$gid]))
			);
		}
	}
	$data['global'] = array(
		'tids'	=> empty($threadarray['global']) ? '' : dimplode($threadarray['global']),
		'count'	=> intval(@count($threadarray['global']))
	);

	save_syscache('globalstick', $data);
}

?>