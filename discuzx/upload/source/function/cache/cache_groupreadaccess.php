<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_groupreadaccess.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_groupreadaccess() {
	$data = array();
	$query = DB::query("SELECT g.groupid, g.grouptitle, gf.readaccess FROM ".DB::table('common_usergroup')." g
		LEFT JOIN ".DB::table('common_usergroup_field')." gf ON gf.groupid=g.groupid WHERE gf.readaccess>'0' ORDER BY gf.readaccess");

	while($datarow = DB::fetch($query)) {
		$data[] = $datarow;
	}

	save_syscache('groupreadaccess', $data);
}

?>