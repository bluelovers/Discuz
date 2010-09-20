<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_grouptype.php 16698 2010-09-13 05:22:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_grouptype() {
	$data = array();
	$query = DB::query("SELECT f.fid, f.fup, f.name, f.forumcolumns, ff.membernum, ff.groupnum FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.type IN('group', 'forum') AND f.status='3' ORDER BY f.type, f.displayorder");

	$data['second'] = $data['first'] = array();
	while($group = DB::fetch($query)) {
		if($group['fup']) {
			$data['second'][$group['fid']] = $group;
		} else {
			$data['first'][$group['fid']] = $group;
		}
	}
	foreach($data['second'] as $fid => $secondgroup) {
		$data['first'][$secondgroup['fup']]['groupnum'] += $secondgroup['groupnum'];
		$data['first'][$secondgroup['fup']]['secondlist'][] = $secondgroup['fid'];
	}

	save_syscache('grouptype', $data);
}

?>