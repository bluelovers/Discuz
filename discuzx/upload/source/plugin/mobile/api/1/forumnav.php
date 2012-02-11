<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumnav.php 27451 2012-02-01 05:48:47Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		loadcache('forums');
		$forums = array();
		foreach($_G['cache']['forums'] as $forum) {
			if(!$forum['status'] || $forum['status'] == 2) {
				continue;
			}
			if(!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t")) {
				$forums[] = $forum;
			}
		}
		$variable['forums'] = $forums;
		mobile_core::result(mobile_core::variable($variable));
	}

	function output() {}

}

?>