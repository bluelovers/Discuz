<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_stickreply.php 20100 2011-02-15 02:03:26Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowstickreply']) {
	showmessage('no_privilege_stickreply');
}

$topiclist = $_G['gp_topiclist'];
$modpostsnum = count($topiclist);
if(empty($topiclist)) {
	showmessage('admin_stickreply_invalid');
} elseif(!$_G['tid']) {
	showmessage('admin_nopermission', NULL);
}
$posttable = getposttablebytid($_G['tid']);
$sticktopiclist = $posts = array();
foreach($topiclist as $pid) {
	$position = DB::result_first("SELECT position FROM ".DB::table('forum_postposition')." WHERE pid='$pid'");
	if($position) {
		$sticktopiclist[$pid] = $position;
	} else {
		$post = DB::fetch_first("SELECT p.tid, p.authorid, p.dateline, p.first, t.special FROM ".DB::table($posttable)." p
			LEFT JOIN ".DB::table('forum_thread')." t USING(tid) WHERE p.pid='$pid'");
		$posts[]['authorid'] = $post['authorid'];
		$posttable = getposttablebytid($post['tid']);
		$curpostnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$post[tid]' AND dateline<='$post[dateline]'");
		if(empty($post['first'])) {
			$sticktopiclist[$pid] = $curpostnum;
		}
	}
}

if(!submitcheck('modsubmit')) {

	$stickpid = '';
	foreach($sticktopiclist as $id => $postnum) {
		$stickpid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
	}

	include template('forum/topicadmin_action');

} else {

	if($_G['gp_stickreply']) {
		foreach($sticktopiclist as $pid => $postnum) {
			DB::query("REPLACE INTO ".DB::table('forum_poststick')." SET tid='$_G[tid]', pid='$pid', position='$postnum', dateline='$_G[timestamp]'");
		}
	} else {
		foreach($sticktopiclist as $pid => $postnum) {
			DB::delete('forum_poststick', "tid='$_G[tid]' AND pid='$pid'");
		}
	}

	$sticknum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_poststick')." WHERE tid='$_G[tid]'");
	$stickreply = intval($_G['gp_stickreply']);

	if($sticknum == 0 || $stickreply == 1) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET moderated='1', stickreply='$stickreply' WHERE tid='$_G[tid]'");
	}

	$modaction = $_G['gp_stickreply'] ? 'SRE' : 'USR';
	$reason = checkreasonpm();

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => $_G['gp_stickreply'] ? 'reason_stickreply': 'reason_stickdeletereply') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
	'modlog'	=> $thread
	);

}

?>