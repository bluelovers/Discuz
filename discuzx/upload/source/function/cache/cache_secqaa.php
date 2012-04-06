<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_secqaa.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_secqaa() {
	$data = array();
	$secqaanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_secquestion'));

	$start_limit = $secqaanum <= 10 ? 0 : mt_rand(0, $secqaanum - 10);
	$query = DB::query("SELECT question, answer, type FROM ".DB::table('common_secquestion')." LIMIT $start_limit, 10");
	$i = 1;
	while($secqaa = DB::fetch($query)) {
		if(!$secqaa['type'])  {
			$secqaa['answer'] = md5($secqaa['answer']);
		}
		$data[$i] = $secqaa;
		$i++;
	}
	while(($secqaas = count($data)) < 9) {
		$data[$secqaas + 1] = $data[array_rand($data)];
	}
	save_syscache('secqaa', $data);
}

?>