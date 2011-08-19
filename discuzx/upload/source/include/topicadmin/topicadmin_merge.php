<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_merge.php 23560 2011-07-26 02:45:31Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowmergethread']) {
	showmessage('no_privilege_mergethread');
}

if(!submitcheck('modsubmit')) {

	include template('forum/topicadmin_action');

} else {

	$posttable = getposttablebytid($_G['tid']);
	$othertid = intval($_G['gp_othertid']);
	$otherposttable = getposttablebytid($othertid);
	$modaction = 'MRG';

	$reason = checkreasonpm();

	$other = DB::fetch_first("SELECT tid, fid, authorid, subject, views, replies, dateline, special FROM ".DB::table('forum_thread')." WHERE tid='$othertid' AND displayorder>='0'");
	if(!$other) {
		showmessage('admin_merge_nonexistence');
	} elseif($other['special']) {
		showmessage('special_noaction');
	}
	if($othertid == $_G['tid'] || ($_G['adminid'] == 3 && $other['fid'] != $_G['forum']['fid'])) {
		showmessage('admin_merge_invalid');
	}

	$other['views'] = intval($other['views']);
	$other['replies']++;

	if($posttable != $otherposttable) {
		$query = DB::query("SELECT * FROM ".DB::table($otherposttable)." WHERE tid='$othertid'");
		while($row = DB::fetch($query)) {
			$row = daddslashes($row);
			DB::insert($posttable, $row);
		}
		DB::delete($otherposttable, "tid='$othertid'");
	}

	$firstpost = DB::fetch_first("SELECT pid, fid, authorid, author, subject, dateline FROM ".DB::table($posttable)." WHERE tid IN ('$_G[tid]', '$othertid') AND invisible='0' ORDER BY dateline LIMIT 1");

	DB::query("UPDATE ".DB::table($posttable)." SET tid='$_G[tid]' WHERE tid='$othertid'");
	$postsmerged = DB::affected_rows();

	updateattachtid("tid='$othertid'", $othertid, $_G['tid']);
	DB::query("DELETE FROM ".DB::table('forum_thread')." WHERE tid='$othertid'");
	DB::query("DELETE FROM ".DB::table('forum_threadmod')." WHERE tid='$othertid'");

	DB::query("UPDATE ".DB::table($posttable)." SET first=(pid='$firstpost[pid]'), fid='".$_G['forum']['fid']."' WHERE tid='$_G[tid]'");
	DB::query("UPDATE ".DB::table('forum_thread')." SET authorid='$firstpost[authorid]', author='".addslashes($firstpost['author'])."', subject='".addslashes($firstpost['subject'])."', dateline='$firstpost[dateline]', views=views+$other[views], replies=replies+$other[replies], moderated='1' WHERE tid='$_G[tid]'");

	my_thread_log('merge', array('tid' => $othertid, 'otherid' => $_G['tid'], 'fid' => $thread['fid']));

	updateforumcount($other['fid']);
	updateforumcount($_G['fid']);

	$_G['forum']['threadcaches'] && deletethreadcaches($thread['tid']);

	$modpostsnum ++;
	$resultarray = array(
	'redirect'	=> "forum.php?mod=forumdisplay&fid=$_G[fid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_merge') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
	'modtids'	=> $thread['tid'],
	'modlog'	=> array($thread, $other)
	);

}

?>