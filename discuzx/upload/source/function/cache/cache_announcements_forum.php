<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_announcements_forum.php 19983 2011-01-26 07:43:52Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_announcements_forum() {
	$data = array();
	$query = DB::query("SELECT a.id, a.author, m.uid AS authorid, a.subject, a.message, a.type, a.starttime, a.endtime, a.displayorder FROM ".DB::table('forum_announcement')."
		a LEFT JOIN ".DB::table('common_member')." m ON m.username=a.author WHERE a.type!=2 AND a.groups = '' AND a.starttime<='".TIMESTAMP."' AND (endtime>='".TIMESTAMP."' OR endtime='0') ORDER BY a.displayorder, a.starttime DESC, a.id DESC LIMIT 1");

	if($data = DB::fetch($query)) {
		$data['authorid'] = intval($data['authorid']);
		if(empty($data['type'])) {
			unset($data['message']);
		}
	} else {
		$data = array();
	}
	save_syscache('announcements_forum', $data);
}

?>