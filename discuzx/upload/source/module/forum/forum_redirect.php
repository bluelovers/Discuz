<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_redirect.php 17070 2010-09-20 04:39:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
loadcache('threadtableids');
$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
if(!in_array(0, $threadtableids)) {
	$threadtableids = array_merge(array(0), $threadtableids);
}

$pid = isset($_G['gp_pid']) ? intval($_G['gp_pid']) : 0;
$ptid = isset($_G['gp_ptid']) ? intval($_G['gp_ptid']) : 0;
$authoridurl = isset($_G['gp_authorid']) ? '&authorid='.intval($_G['gp_authorid']) : '';
$ordertypeurl = isset($_G['gp_ordertype']) ? '&ordertype='.intval($_G['gp_ordertype']) : '';
if(!empty($_G['tid'])) {
	$posttable = getposttablebytid($_G['tid']);
} else {
	$posttable = 'forum_post';
}
if(!empty($ptid)) {
	$posttable = getposttablebytid($ptid);
}

if(empty($_G['gp_goto']) && !empty($ptid)) {
	$postno = intval($_G['gp_postno']);
	$status = DB::result_first("SELECT status FROM ".DB::table('forum_thread')." WHERE tid='$ptid'");
	if(getstatus($post['status'], 3)) {
		$pid = DB::result_first("SELECT pid FROM ".DB::table('forum_postposition')." WHERE tid='$ptid' AND position='$postno'");
	} else {
		if($_G['gp_ordertype'] != 1) {
			$postno = $postno > 0 ? $postno - 1 : 0;
			$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$ptid' AND invisible='0' ORDER BY dateline LIMIT $postno, 1");
		} else {
			$postno = $postno > 1 ? $postno - 1 : 0;
			if($postno) {
				$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$ptid' AND invisible='0' ORDER BY dateline LIMIT $postno, 1");
			} else {
				$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$ptid' AND first='1' LIMIT 1");
			}
		}
	}
	$_G['gp_goto'] = 'findpost';
}

if($_G['gp_goto'] == 'findpost') {
	foreach($threadtableids as $tableid) {
		$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
		$post = getallwithposts(array(
			'select' => 'p.tid, p.dateline, t.status, t.special, t.replies',
			'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table($threadtable)." t USING(tid)",
			'where' => "p.pid='$pid'",
		));
		if(!empty($post)) {
			$post = $post[0];
			break;
		}
	}
	if($post) {
		$ordertype = !isset($_GET['ordertype']) && getstatus($post['status'], 4) ? 1 : intval($ordertype);
		$sqladd = $post['special'] ? "AND first=0" : '';
		$curpostnum = DB::result_first("SELECT count(*) FROM ".DB::table($posttable)." WHERE tid='$post[tid]' AND dateline<='$post[dateline]' $sqladd");
		if($ordertype != 1) {
			$page = ceil($curpostnum / $_G['ppp']);
		} else {
			if($curpostnum > 1) {
				$page = ceil(($post['replies'] - $curpostnum + 3) / $_G['ppp']);
			} else {
				$page = 1;
			}
		}
		if(!empty($special) && $special == 'trade') {
			dheader("Location: forum.php?mod=viewthread&do=tradeinfo&tid=$post[tid]&pid=$pid$authoridurl$ordertypeurl");
		} else {/*noteX
			$extra = '';
			if($_G['uid'] && empty($postno)) {
				if(DB::result_first("SELECT count(*) FROM ".DB::table('favoritethreads')." WHERE tid='$post[tid]' AND uid='$_G[uid]'")) {
					DB::query("UPDATE ".DB::table('favoritethreads')." SET newreplies=0 WHERE tid='$post[tid]' AND uid='$_G[uid]'", 'UNBUFFERED');
					DB::query("DELETE FROM ".DB::table('promptmsgs')." WHERE uid='$_G[uid]' AND typeid='".$prompts['threads']['id']."' AND extraid='$post[tid]'", 'UNBUFFERED');
					$extra = '&fav=yes';
				}
			}*/
			dheader("Location: forum.php?mod=viewthread&tid={$post['tid']}&page=$page$authoridurl$ordertypeurl".(isset($_G['gp_modthreadkey']) && ($modthreadkey = modauthkey($post['tid'])) ? "&modthreadkey=$modthreadkey": '')."#pid$pid");
		}
	} else {
		$ptid = !empty($ptid) ? intval($ptid) : 0;
		if($ptid) {
			dheader("location: forum.php?mod=viewthread&tid=$ptid$authoridurl$ordertypeurl");
		}
		showmessage('post_check', NULL, array('tid' => $ptid));
	}
}

$_G['tid'] = $_G['forum']['closed'] < 2 || $_G['forum']['status'] == 3 ? $_G['tid'] : $_G['forum']['closed'];

if(empty($_G['tid'])) {
	showmessage('thread_nonexistence');
}

if(isset($_G['fid']) && empty($_G['forum'])) {
	showmessage('forum_nonexistence', NULL);
}

loadcache(array('smilies', 'smileytypes', 'forums', 'usergroups', 'stamps', 'bbcodes', 'smilies', 'advs_viewthread', 'custominfo', 'groupicon', 'focus', 'stamps'));

if($_G['gp_goto'] == 'lastpost') {
	foreach($threadtableids as $tableid) {
		$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
		if($_G['tid']) {
			$query = DB::query("SELECT tid, replies, special, status FROM ".DB::table($threadtable)." WHERE tid='$_G[tid]' AND displayorder>='0'");
		} else {
			$query = DB::query("SELECT tid, replies, special, status FROM ".DB::table($threadtable)." WHERE fid='$_G[fid]' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");
		}
		if(DB::num_rows($query)) {
			break;
		}
	}
	if(!$thread = DB::fetch($query)) {
		showmessage('thread_nonexistence');
	}
	if(!getstatus($thread['status'], 4)) {
		$_G['page'] = ceil(($thread['special'] ? $thread['replies'] : $thread['replies'] + 1) / $_G['ppp']);
	} else {
		$_G['page'] = 1;
	}

	$_G['tid'] = $thread['tid'];

	require_once DISCUZ_ROOT.'./source/module/forum/forum_viewthread.php';
	exit();

} elseif($_G['gp_goto'] == 'nextnewset') {

	if($_G['fid'] && $_G['tid']) {
		foreach($threadtableids as $tableid) {
			$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
			$this_lastpost = DB::result_first("SELECT lastpost FROM ".DB::table($threadtable)." WHERE tid='$_G[tid]' AND displayorder>='0'");
			if(!empty($this_lastpost)) {
				break;
			}
		}
		if($next = DB::fetch_first("SELECT tid FROM ".DB::table($threadtable)." WHERE fid='$_G[fid]' AND displayorder>='0' AND lastpost>'$this_lastpost' ORDER BY lastpost ASC LIMIT 1")) {
			dheader("Location: forum.php?mod=viewthread&tid=$next[tid]");
		} else {
			showmessage('redirect_nextnewset_nonexistence');
		}
	} else {
		showmessage('undefined_action', NULL);
	}

} elseif($_G['gp_goto'] == 'nextoldset') {

	if($_G['fid'] && $_G['tid']) {
		foreach($threadtableids as $tableid) {
			$threadtable = $tableid ? "fourm_thread_$tableid" : 'forum_thread';
			$this_lastpost = DB::result_first("SELECT lastpost FROM ".DB::table($threadtable)." WHERE tid='$_G[tid]' AND displayorder>='0'");
			if(!empty($this_lastpost)) {
				break;
			}
		}
		if($last = DB::fetch_first("SELECT tid FROM ".DB::table($threadtable)." WHERE fid='$_G[fid]' AND displayorder>='0' AND lastpost<'$this_lastpost' ORDER BY lastpost DESC LIMIT 1")) {
			dheader("Location: forum.php?mod=viewthread&tid=$last[tid]");
		} else {
			showmessage('redirect_nextoldset_nonexistence');
		}
	} else {
		showmessage('undefined_action', NULL);
	}

} else {
	showmessage('undefined_action', NULL);
}

?>