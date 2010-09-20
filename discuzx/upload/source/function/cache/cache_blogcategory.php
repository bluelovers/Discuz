<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_blogcategory.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_blogcategory() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_blog_category')." ORDER BY displayorder");

	while($value = DB::fetch($query)) {
		$value['catname'] = dhtmlspecialchars($value['catname']);
		$data[$value['catid']] = $value;
	}
	foreach($data as $key => $value) {
		$upid = $value['upid'];
		$data[$key]['level'] = 0;
		if($upid && isset($data[$upid])) {
			$data[$upid]['children'][] = $key;
			while($upid && isset($data[$upid])) {
				$data[$key]['level'] += 1;
				$upid = $data[$upid]['upid'];
			}
		}
	}

	save_syscache('blogcategory', $data);
}

?>