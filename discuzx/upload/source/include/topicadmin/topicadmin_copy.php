<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_copy.php 23127 2011-06-21 01:23:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowcopythread'] || !$thread) {
	showmessage('no_privilege_copythread');
}

if(!submitcheck('modsubmit')) {
	require_once libfile('function/forumlist');
	$forumselect = forumselect();
	include template('forum/topicadmin_action');

} else {

	$modaction = 'CPY';
	$reason = checkreasonpm();
	$copyto = $_G['gp_copyto'];
	$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, ff.threadsorts FROM ".DB::table('forum_forum')." f
								LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
								WHERE f.fid='$copyto' AND f.status='1' AND f.type<>'group'");
	if(!$toforum) {
		showmessage('admin_copy_invalid');
	} else {
		$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
		$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
		if($modnewthreads || $modnewreplies) {
			showmessage('admin_copy_hava_mod');
		}
	}
	$toforum['threadsorts_arr'] = unserialize($toforum['threadsorts']);

	if($thread['sortid'] != 0 && $toforum['threadsorts_arr']['types'][$thread['sortid']]) {
		$query = DB::query("SELECT * FROM ".DB::table('forum_typeoptionvar')." WHERE sortid = '{$thread['sortid']}' AND tid = '{$thread['tid']}'");
		while ($result = DB::fetch($query)) {
			$typeoptionvar[] = $result;
		}
	} else {
		$thread['sortid'] = '';
	}

	unset($thread['tid']);
	$thread['fid'] = $copyto;
	$thread['dateline'] = $thread['lastpost'] = TIMESTAMP;
	$thread['lastposter'] = $thread['author'];
	$thread['views'] = $thread['replies'] = $thread['highlight'] = $thread['digest'] = 0;
	$thread['rate'] = $thread['displayorder'] = $thread['attachment'] = 0;
	$thread['typeid'] = $_G['gp_threadtypeid'];
	$thread = daddslashes($thread);

	$thread['posttableid'] = 0;
	$threadid = DB::insert('forum_thread', $thread, true);
	$posttable = getposttablebytid($_G['tid']);
	if($post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first=1 LIMIT 1")) {
		$post['pid'] = '';
		$post['tid'] = $threadid;
		$post['fid'] = $copyto;
		$post['dateline'] = TIMESTAMP;
		$post['attachment'] = 0;
		$post['invisible'] = $post['rate'] = $post['ratetimes'] = 0;
		$post = daddslashes($post);
		$pid = insertpost($post);
	}

	if($typeoptionvar) {
		foreach($typeoptionvar AS $key => $value) {
			$value['tid'] = $threadid;
			$value['fid'] = $toforum['fid'];
			DB::insert('forum_typeoptionvar', $value);
		}
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