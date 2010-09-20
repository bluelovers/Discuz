<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_creditrule.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_creditrule() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule'));

	while($rule = DB::fetch($query)) {
		if(strtoupper(CHARSET) != 'UTF-8') {
			$rule['rulenameuni'] = urlencode(diconv($rule['rulename'], CHARSET, 'UTF-8', true));
		}
		$data[$rule['action']] = $rule;
	}

	save_syscache('creditrule', $data);
}

?>