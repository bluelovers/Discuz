<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_grouplevels.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_grouplevels() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_grouplevel'));

	while($level = DB::fetch($query)) {
		$level['creditspolicy'] = unserialize($level['creditspolicy']);
		$level['postpolicy'] = unserialize($level['postpolicy']);
		$level['specialswitch'] = unserialize($level['specialswitch']);
		$data[$level['levelid']] = $level;
	}

	save_syscache('grouplevels', $data);
}

?>