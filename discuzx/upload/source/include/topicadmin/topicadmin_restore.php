<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_restore.php 16938 2010-09-17 04:37:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['adminid'] != '1') {
	showmessage('undefined_action', NULL);
}

if(!submitcheck('modsubmit')) {
	$archiveid = intval($_G['gp_archiveid']);
	include template('forum/topicadmin_action');
} else {
	$archiveid = $_G['gp_archiveid'];
	if(!in_array($archiveid, $threadtableids)) {
		$archiveid = 0;
	}
	$threadtable = $archiveid ? "forum_thread_$archiveid" : 'forum_thread';
	DB::query("INSERT INTO ".DB::table('forum_thread')." SELECT * FROM ".DB::table($threadtable)." WHERE tid='{$_G['tid']}'");
	DB::delete($threadtable, "tid='{$_G['tid']}'");

	$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." WHERE fid='{$_G['fid']}'");
	if($threadcount) {
		DB::update('forum_forum_threadtable', array('threads' => $threadcount), "fid='{$_G['fid']}' AND threadtableid='$archiveid'");
	} else {
		DB::delete('forum_forum_threadtable', "fid='{$_G['fid']}' AND threadtableid='$archiveid'");
	}
	if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum_threadtable')." WHERE fid='{$_G['fid']}'")) {
		DB::update('forum_forum', array('archive' => '0'), "fid='{$_G['fid']}'");
	}
	$modaction = 'RST';
	$reason = checkreasonpm();
	$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
		'modaction'	=> $modaction,
		'modlog'	=> $thread
	);
}

?>