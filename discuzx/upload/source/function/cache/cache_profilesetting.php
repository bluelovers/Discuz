<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_profilesetting.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_profilesetting() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' ORDER BY displayorder DESC");

	while($field = DB::fetch($query)) {
		$data[$field['fieldid']] = $field;
	}

	save_syscache('profilesetting', $data);
}

?>