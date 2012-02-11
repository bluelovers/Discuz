<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumdisplay.php 27451 2012-02-01 05:48:47Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		$_G['forum']['allowglobalstick'] = false;
	}

	function output() {
		global $_G;
		$variable = array(
			'forum' => mobile_core::getvalues($_G['forum'], array('fid', 'fup', 'name', 'threads', 'posts', 'rules', 'autoclose', 'password')),
			'group' => mobile_core::getvalues($_G['group'], array('groupid', 'grouptitle')),
			'threadtypes' => $_G['forum']['threadtypes'],
			'threadsorts' => $_G['forum']['threadsorts'],
			'forum_threadlist' => mobile_core::getvalues($_G['forum_threadlist'], array('/^\d+$/'), array('tid', 'author', 'authorid', 'subject', 'subject', 'dbdateline', 'dateline', 'dblastpost', 'lastpost', 'lastposter', 'attachment', 'replies', 'views')),
			'sublist' => mobile_core::getvalues($GLOBALS['sublist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'todayposts', 'posts')),
			'tpp' => $_G['tpp'],
			'page' => $GLOBALS['page'],
		);
		$variable['forum']['password'] = $variable['forum']['password'] ? 1 : 0;
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>