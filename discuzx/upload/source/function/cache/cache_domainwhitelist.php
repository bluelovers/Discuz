<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_domainwhitelist.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_domainwhitelist() {
	$query = DB::query("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='domainwhitelist'");

	if($result = DB::result($query, 0)) {
		$data = explode("\r\n", $result);
	} else {
		$data = array();
	}

	save_syscache('domainwhitelist', $data);
}

?>