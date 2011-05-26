<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_split.php 19949 2011-01-25 06:39:51Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_split() {
	global $_G;
	$splitcaches = array('threadtableids', 'threadtable_info', 'posttable_info', 'posttableids');
	foreach($splitcaches as $splitcache) {
		loadcache($splitcache);
		if(empty($_G['cache'][$splitcache])) {
			save_syscache($splitcache, '');
		}
	}
}

?>