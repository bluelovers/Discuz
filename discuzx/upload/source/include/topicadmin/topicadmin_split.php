<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_split.php 20099 2011-02-15 01:55:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowsplitthread']) {
	showmessage('no_privilege_splitthread');
}

$posttable = getposttablebytid($_G['tid']);
$posttableid = DB::result_first("SELECT posttableid FROM ".DB::table('forum_thread')." WHERE tid='{$_G['tid']}'");
if(!submitcheck('modsubmit')) {

	require_once libfile('function/discuzcode');

	$replies = $thread['replies'];
	if($replies <= 0) {
		showmessage('admin_split_invalid');
	}

	$postlist = array();
	$query = DB::query("SELECT * FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' ORDER BY dateline");
	while($post = DB::fetch($query)) {
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml']);
		$postlist[] = $post;
	}
	include template('forum/topicadmin_action');

} else {

	if(!trim($_G['gp_subject'])) {
		showmessage('admin_split_subject_invalid');
	} elseif(!($nos = explode(',', $_G['gp_split']))) {
		showmessage('admin_split_new_invalid');
	}

	sort($nos);
	$maxno = $nos[count($nos) - 1];
	$maxno = $maxno > $thread['replies'] + 1 ? $thread['replies'] + 1 : $maxno;
	$maxno = max(1, intval($maxno));
	$query = DB::query("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline LIMIT $maxno");
	$i = 1;
	$pids = array();
	while($post = DB::fetch($query)) {
		if(in_array($i, $nos)) {
			$pids[] = $post['pid'];
		}
		$i++;
	}
	if(!($pids = implode(',',$pids))) {
		showmessage('admin_split_new_invalid');
	}

	$modaction = 'SPL';

	$reason = checkreasonpm();

	$subject = dhtmlspecialchars($_G['gp_subject']);
	DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, subject) VALUES ('$_G[fid]', '$posttableid', '$subject')");
	$newtid = DB::insert_id();

	my_thread_log('split', array('tid' => $_G['tid']));

	foreach((array)explode(',', $pids) as $pid) {
		my_post_log('split', array('pid' => $pid));
	}

	DB::query("UPDATE ".DB::table($posttable)." SET tid='$newtid' WHERE pid IN ($pids)");
	updateattachtid("pid IN ($pids)", $_G['tid'], $newtid);

	$splitauthors = array();
	$query = DB::query("SELECT pid, tid, authorid, subject, dateline FROM ".DB::table($posttable)." WHERE tid='$newtid' AND invisible='0' GROUP BY authorid ORDER BY dateline");
	while($splitauthor = DB::fetch($query)) {
		$splitauthor['subject'] = $subject;
		$splitauthors[] = $splitauthor;
	}

	DB::query("UPDATE ".DB::table($posttable)." SET first='1', subject='$subject' WHERE pid='".$splitauthors[0]['pid']."'", 'UNBUFFERED');

	$fpost = DB::fetch_first("SELECT pid, author, authorid, dateline FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' ORDER BY dateline LIMIT 1");
	DB::query("UPDATE ".DB::table('forum_thread')." SET author='".addslashes($fpost['author'])."', authorid='$fpost[authorid]', dateline='$fpost[dateline]', moderated='1' WHERE tid='$_G[tid]'");
	DB::query("UPDATE ".DB::table($posttable)." SET subject='".addslashes($thread['subject'])."' WHERE pid='$fpost[pid]'");

	$fpost = DB::fetch_first("SELECT author, authorid, dateline, rate FROM ".DB::table($posttable)." WHERE tid='$newtid' ORDER BY dateline ASC LIMIT 1");
	DB::query("UPDATE ".DB::table('forum_thread')." SET author='".addslashes($fpost['author'])."', authorid='$fpost[authorid]', dateline='$fpost[dateline]', rate='".intval(@($fpost['rate'] / abs($fpost['rate'])))."', moderated='1' WHERE tid='$newtid'");

	updatethreadcount($_G['tid']);
	updatethreadcount($newtid);
	updateforumcount($_G['fid']);

	$_G['forum']['threadcaches'] && deletethreadcaches($thread['tid']);

	$modpostsnum++;
	$resultarray = array(
	'redirect'	=> "forum.php?mod=forumdisplay&fid=$_G[fid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => $splitauthors, 'var' => 'thread', 'item' => 'reason_moderate') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
	'modtids'	=> $thread['tid'].','.$newtid,
	'modlog'	=> array($thread, array('tid' => $newtid, 'subject' => $subject))
	);

}

?>