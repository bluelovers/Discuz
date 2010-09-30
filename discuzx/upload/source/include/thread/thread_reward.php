<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_reward.php 16706 2010-09-13 06:37:44Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$bapid = 0;
$rewardprice = abs($_G['forum_thread']['price']);
$dateline = $_G['forum_thread']['dateline'] + 1;
$bestpost = array();
//if($_G['forum_thread']['price'] < 0 && $page == 1) {
//	foreach($postlist as $key => $post) {
//		if($post['dbdateline'] == $dateline) {
//			$bapid = $key;
//			break;
//		}
//	}
//}

// bluelovers
$balog = array();

if ($_G['forum_thread']['price'] < 0 && $balog = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread_rewardlog')." WHERE tid='{$_G[forum_thread][tid]}' AND answererpid > 0")) {
	$bapid = $balog['answererpid'];

	if (!$balog['setreward_uid']) {
		$balog['setreward_uid'] = $thread['authorid'];
		$balog['setreward_author'] = $thread['author'];
	}
} else {
// bluelovers

	if($_G['forum_thread']['price'] < 0 && $page == 1) {
		// bluelovers
		$bapid = -1;
		// bluelovers

		foreach($postlist as $key => $post) {
			if($post['dbdateline'] == $dateline || ($bapid < 0 && $post['dbdateline'] >= $dateline)) {
				$bapid = $key;
				break;
			}
		}
		// bluelovers
		if ($bapid < 0) $bapid = 0;
		// bluelovers
	}
// bluelovers
}
// bluelovers

if($bapid) {
	$bestpost = DB::fetch_first("SELECT p.* FROM ".DB::table($posttable)." p WHERE p.pid='$bapid'");
	$bestpost['message'] = messagecutstr($bestpost['message'], 400);
	$bestpost['avatar'] = avatar($bestpost['authorid'], 'small');

	// bluelovers
	$bestpost['balog'] = $balog;
	// bluelovers
}

//print_r(array($bapid, $balog, $bestpost, $_G['forum_thread']['price'], $page));
//dexit();

?>