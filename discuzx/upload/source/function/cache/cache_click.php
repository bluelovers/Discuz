<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_click.php 20982 2011-03-09 10:02:57Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_click() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_click')." WHERE available='1' ORDER BY displayorder DESC");

	$data = $keys = array();
	while($value = DB::fetch($query)) {
		if(count($data[$value['idtype']]) < 8) {
			$keys[$value['idtype']] = $keys[$value['idtype']] ? ++$keys[$value['idtype']] : 1;
			$data[$value['idtype']][$keys[$value['idtype']]] = $value;
		}
	}

	save_syscache('click', $data);
}

?>