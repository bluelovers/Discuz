<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_ipbanned.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_ipbanned() {
	DB::query("DELETE FROM ".DB::table('common_banned')." WHERE expiration<'".TIMESTAMP."'");
	$data = array();
	$query = DB::query("SELECT ip1, ip2, ip3, ip4, expiration FROM ".DB::table('common_banned'));

	if(DB::num_rows($query)) {
		$data['expiration'] = 0;
		$data['regexp'] = $separator = '';
	}
	while($banned = DB::fetch($query)) {
		$data['expiration'] = !$data['expiration'] || $banned['expiration'] < $data['expiration'] ? $banned['expiration'] : $data['expiration'];
		$data['regexp'] .= $separator.
			($banned['ip1'] == '-1' ? '\\d+\\.' : $banned['ip1'].'\\.').
			($banned['ip2'] == '-1' ? '\\d+\\.' : $banned['ip2'].'\\.').
			($banned['ip3'] == '-1' ? '\\d+\\.' : $banned['ip3'].'\\.').
			($banned['ip4'] == '-1' ? '\\d+' : $banned['ip4']);
		$separator = '|';
	}

	save_syscache('ipbanned', $data);
}

?>