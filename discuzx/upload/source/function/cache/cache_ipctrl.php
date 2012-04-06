<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_ipctrl.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_ipctrl() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_setting')." WHERE skey IN ('ipregctrl', 'ipverifywhite')");

	while($setting = DB::fetch($query)) {
		$data[$setting['skey']] = $setting['svalue'];
	}

	save_syscache('ipctrl', $data);
}

?>