<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_userstats.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_userstats() {
	$totalmembers = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member'));
	$newsetuser = DB::result_first("SELECT username FROM ".DB::table('common_member')." ORDER BY regdate DESC LIMIT 1");
	save_syscache('userstats', array('totalmembers' => $totalmembers, 'newsetuser' => $newsetuser));
}

?>