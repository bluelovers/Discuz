<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forumstick.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_forumstick() {
	$data = array();
	$forumstickthreads = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='forumstickthreads'");

	$forumstickthreads = unserialize($forumstickthreads);
	$forumstickcached = array();
	if($forumstickthreads) {
		foreach($forumstickthreads as $forumstickthread) {
			foreach($forumstickthread['forums'] as $fid) {
				$forumstickcached[$fid][] = $forumstickthread;
			}
		}
		foreach($forumstickcached as $fid => $threadlist) {
			foreach($forumstickcached[$fid] as $k => $v) {
				unset($forumstickcached[$fid][$k]['forums']);
			}
		}
		$data = $forumstickcached;
	} else {
		$data = array();
	}

	save_syscache('forumstick', $data);
}

?>