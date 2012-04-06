<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_relatedlink.php 19948 2011-01-25 06:39:26Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_relatedlink() {
	global $_G;

	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_relatedlink'));
	while($link = DB::fetch($query)) {
		if(substr($link['url'], 0, 7) != 'http://') {
			$link['url'] = 'http://'.$link['url'];
		}
		$data[] = $link;
	}
	save_syscache('relatedlink', $data);
}

?>