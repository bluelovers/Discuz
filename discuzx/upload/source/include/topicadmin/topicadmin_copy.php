<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_copy.php 16938 2010-09-17 04:37:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowcopythread'] || !$thread) {
	showmessage('undefined_action', NULL);
}

if(!submitcheck('modsubmit')) {
	require_once libfile('function/forumlist');
	$forumselect = forumselect();
	include template('forum/topicadmin_action');

} else {

	$modaction = 'CPY';
	$reason = checkreasonpm();
	$copyto = $_G['gp_copyto'];
	$toforum = DB::fetch_first("SELECT fid, name, modnewposts FROM ".DB::table('forum_forum')." WHERE fid='$copyto' AND status='1' AND type<>'group'");
	if(!$toforum) {
		showmessage('admin_copy_invalid');
	} else {
		$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
		$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
		if($modnewthreads || $modnewreplies) {
			showmessage('admin_copy_hava_mod');
		}
	}


	unset($thread['tid']);
	$thread['fid'] = $copyto;
	$thread['dateline'] = $thread['lastpost'] = TIMESTAMP;
	$thread['lastposter'] = $thread['author'];
	$thread['views'] = $thread['replies'] = $thread['highlight'] = $thread['digest'] = 0;
	$thread['rate'] = $thread['displayorder'] = $thread['attachment'] = 0;

	$posttableid = getposttableid('p');
	$thread['posttableid'] = $posttableid;
	$threadid = DB::insert('forum_thread', $thread, true);
	$posttable = getposttablebytid($_G['tid']);
	if($post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first=1 LIMIT 1")) {
		$post['pid'] = '';
		$post['tid'] = $threadid;
		$post['fid'] = $copyto;
		$post['dateline'] = TIMESTAMP;
		$post['attachment'] = 0;
		$post['invisible'] = $post['rate'] = $post['ratetimes'] = 0;
		$pid = insertpost($post);
	}

	updatepostcredits('+', $post['authorid'], 'post', $_G['fid']);

	updateforumcount($copyto);
	updateforumcount($_G['fid']);

	$modpostsnum ++;
	$resultarray = array(
	'redirect'	=> "forum.php?mod=forumdisplay&fid=$_G[fid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_copy') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason), 'threadid' => $threadid),
	'modtids'	=> $thread['tid'],
	'modlog'	=> array($thread, $other)
	);
}

?>