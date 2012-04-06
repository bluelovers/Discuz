<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_stamptypeid.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_stamptypeid() {
	$data = array();
	$query = DB::query("SELECT displayorder, typeid FROM ".DB::table('common_smiley')." WHERE type='stamp' AND typeid>'0'");

	while($stamp = DB::fetch($query)) {
		$data[$stamp['typeid']] = $stamp['displayorder'];
	}

	save_syscache('stamptypeid', $data);
}

?>