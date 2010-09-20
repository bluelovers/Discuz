<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_announcements.php 16698 2010-09-13 05:22:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_announcements() {
	$data = array();
	$query = DB::query("SELECT id, subject, type, starttime, endtime, displayorder, groups, message FROM ".DB::table('forum_announcement')."
		WHERE starttime<='".TIMESTAMP."' AND (endtime>='".TIMESTAMP."' OR endtime='0') ORDER BY displayorder, starttime DESC, id DESC");

	while($datarow = DB::fetch($query)) {
		if($datarow['type'] == 2) {
			$datarow['pmid'] = $datarow['id'];
			unset($datarow['id']);
			unset($datarow['message']);
			$datarow['subject'] = cutstr($datarow['subject'], 60);
		}
		$datarow['groups'] = empty($datarow['groups']) ? array() : explode(',', $datarow['groups']);
		$data[] = $datarow;
	}

	save_syscache('announcements', $data);
}

?>