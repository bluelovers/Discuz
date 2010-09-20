<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_groupicon.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_groupicon() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_onlinelist')." ORDER BY displayorder");

	while($list = DB::fetch($query)) {
		$data[$list['groupid']] = STATICURL.'image/common/'.$list['url'];
	}

	save_syscache('groupicon', $data);
}

?>