<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_onlinelist.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_onlinelist() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_onlinelist')." ORDER BY displayorder");

	$data['legend'] = '';
	while($list = DB::fetch($query)) {
		$data[$list['groupid']] = $list['url'];
		$data['legend'] .= !empty($list['url']) ? "<img src=\"".STATICURL."image/common/$list[url]\" /> $list[title] &nbsp; &nbsp; &nbsp; " : '';
		if($list['groupid'] == 7) {
			$data['guest'] = $list['title'];
		}
	}

	save_syscache('onlinelist', $data);
}

?>