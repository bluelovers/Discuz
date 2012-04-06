<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_modreasons.php 19647 2011-01-12 09:25:33Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_modreasons() {
	foreach(array('modreasons', 'userreasons') AS $key) {
		$data = array();
		$reasons = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='$key'");
		$reasons = str_replace(array("\r\n", "\r"), array("\n", "\n"), $reasons);
		$data = explode("\n", trim($reasons));
		save_syscache($key, $data);
	}
}

?>