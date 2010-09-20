<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_admingroups.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_admingroups() {
	$query = DB::query("SELECT * FROM ".DB::table('common_admingroup'));
	while($data = DB::fetch($query)) {
		save_syscache('admingroup_'.$data['admingid'], $data);
	}
}

?>