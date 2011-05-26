<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_focus.php 17523 2010-10-21 00:58:00Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_focus() {
	$data = array();
	$focus = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='focus'");

	$focus = unserialize($focus);
	$data['title'] = $focus['title'];
	$data['cookie'] = intval($focus['cookie']);
	$data['data'] = array();
	if(is_array($focus['data'])) foreach($focus['data'] as $k => $v) {
		if($v['available']) {
			$data['data'][$k] = $v;
		}
	}

	save_syscache('focus', $data);
}

?>