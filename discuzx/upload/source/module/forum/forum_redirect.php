<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_redirect.php 23301 2011-07-04 06:26:23Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

foreach(array('pid', 'ptid', 'authorid', 'ordertype', 'postno') as $k) {
	$$k = !empty($_GET[$k]) ? intval($_GET[$k]) : 0;
}

if(empty($_G['gp_goto']) && $ptid) {
	$_G['gp_goto'] = 'findpost';
}

if($_G['gp_goto'] == 'findpost') {

	$post = $thread = array();

	if($ptid) {
		$thread = get_thread_by_tid($ptid);
	}

	if($pid) {

		if($thread) {
			$post = get_post_by_pid($pid, '*', '', $thread['posttable']);
		} else {
			$post = get_post_by_pid($pid);
		}

		if($post && empty($thread)) {
			$thread = get_thread_by_tid($post['tid']);
		}
	}

	if(empty($thread)) {
		showmessage('thread_nonexistence');
	} else {
		$tid = $thread['tid'];
	}

	if(empty($pid)) {

		if($postno) {
			if(getstatus($thread['status'], 3)) {
				$pid = DB::result_first("SELECT pid FROM ".DB::table('forum_postposition')." WHERE tid='$ptid' AND position='$postno'");
			}

			if($pid) {
				$post = DB::fetch_first("SELECT * FROM ".DB::table($thread['posttable'])." WHERE pid='$pid' AND invisible='0'");
			} else {
				$postno = $postno > 1 ? $postno - 1 : 0;
				$post = DB::fetch_first("SELECT * FROM ".DB::table($thread['posttable'])." WHERE tid='$ptid' AND invisible='0' ORDER BY dateline LIMIT $postno, 1");
			}
		}

	}

	if(empty($post)) {
		if($ptid) {
			header("HTTP/1.1 301 Moved Permanently");
			dheader("Location: forum.php?mod=viewthread&tid=$ptid");
		} else {
			showmessage('post_check', NULL, array('tid' => $ptid));
		}
	} else {
		$pid = $post['pid'];
	}

	$ordertype = !isset($_GET['ordertype']) && getstatus($thread['status'], 4) ? 1 : $ordertype;
	$curpostnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table($thread['posttable'])." WHERE tid='$tid' AND invisible='0' AND dateline<='$post[dateline]'");

	if($ordertype != 1) {
		$page = ceil($curpostnum / $_G['ppp']);
	} elseif($curpostnum > 1) {
		$page = ceil(($thread['replies'] - $curpostnum + 3) / $_G['ppp']);
	} else {
		$page = 1;
	}

	if($thread['special'] == 2 && DB::result_first("SELECT count(*) FROM ".DB::table('forum_trade')." WHERE pid='$pid'")) {
		header("HTTP/1.1 301 Moved Permanently");
		dheader("Location: forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
	}

	$authoridurl = $authorid ? '&authorid='.$authorid : '';
	$ordertypeurl = $ordertype ? '&ordertype='.$ordertype : '';
	header("HTTP/1.1 301 Moved Permanently");
	dheader("Location: forum.php?mod=viewthread&tid=$tid&page=$page$authoridurl$ordertypeurl".(isset($_G['gp_modthreadkey']) && ($modthreadkey = modauthkey($tid)) ? "&modthreadkey=$modthreadkey": '')."#pid$pid");
}


if(empty($_G['thread'])) {
	showmessage('thread_nonexistence');
}

if($_G['gp_goto'] == 'lastpost') {

	$pageadd = '';
	if(!getstatus($_G['thread'], 4)) {
		$page = ceil(($_G['thread']['special'] ? $_G['thread']['replies'] : $_G['thread']['replies'] + 1) / $_G['ppp']);
		$pageadd = $page > 1 ? '&page='.$page : '';
	}

	dheader('Location: forum.php?mod=viewthread&tid='.$_G['tid'].$pageadd.'#lastpost');

} elseif($_G['gp_goto'] == 'nextnewset' || $_G['gp_goto'] == 'nextoldset') {

	$lastpost = $_G['thread']['lastpost'];

	$query = "SELECT tid FROM ".DB::table($_G['thread']['threadtable'])." WHERE fid='$_G[fid]' AND displayorder>='0' AND closed='0' AND lastpost";
	if($_G['gp_goto'] == 'nextnewset') {
		$query .= ">'$lastpost' ORDER BY lastpost ASC LIMIT 1";
	} else {
		$query .= "<'$lastpost' ORDER BY lastpost DESC LIMIT 1";
	}
	$next = DB::result_first($query);
	if($next) {
		dheader("Location: forum.php?mod=viewthread&tid=$next");
	} elseif($_G['gp_goto'] == 'nextnewset') {
		showmessage('redirect_nextnewset_nonexistence');
	} else {
		showmessage('redirect_nextoldset_nonexistence');
	}

} else {
	showmessage('undefined_action', NULL);
}

?>