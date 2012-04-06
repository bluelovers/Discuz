<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_delcomment.php 20099 2011-02-15 01:55:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowdelpost'] || empty($_G['gp_topiclist'])) {
	showmessage('no_privilege_delcomment');
}

if(!submitcheck('modsubmit')) {

	$commentid = $_G['gp_topiclist'][0];
	$pid = DB::result_first("SELECT pid FROM ".DB::table('forum_postcomment')." WHERE id='$commentid'");
	if(!$pid) {
		showmessage('postcomment_not_found');
	}
	$deleteid = '<input type="hidden" name="topiclist" value="'.$commentid.'" />';

	include template('forum/topicadmin_action');

} else {

	$reason = checkreasonpm();
	$modaction = 'DCM';

	$commentid = intval($_G['gp_topiclist']);
	$postcomment = DB::fetch_first("SELECT * FROM ".DB::table('forum_postcomment')." WHERE id='$commentid'");
	if(!$postcomment) {
		showmessage('postcomment_not_found');
	}
	DB::delete('forum_postcomment', "id='$commentid'");
	$result = DB::result_first("SELECT count(*) FROM ".DB::table('forum_postcomment')." WHERE pid='$postcomment[pid]'");
	if(!$result) {
		$posttable = $_G['forum_thread']['posttable'] ? $_G['forum_thread']['posttable'] : 'forum_post';
		DB::update($posttable, array('comment' => 0), "pid='$postcomment[pid]'");
	}
	if(!$postcomment['rpid']) {
		updatepostcredits('-', $postcomment['authorid'], 'reply', $_G['fid']);
	}

	$query = DB::query('SELECT comment FROM '.DB::table('forum_postcomment')." WHERE pid='$postcomment[pid]' AND score='1'");
	$totalcomment = array();
	while($comment = DB::fetch($query)) {
		if(strexists($comment['comment'], '<br />')) {
			if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
				foreach($a[1] as $k => $itemk) {
					$totalcomment[trim($itemk)][] = $a[2][$k];
				}
			}
		}
	}
	$totalv = '';
	foreach($totalcomment as $itemk => $itemv) {
		$totalv .= strip_tags(trim($itemk)).': <i>'.(sprintf('%1.1f', array_sum($itemv) / count($itemv))).'</i> ';
	}

	if($totalv) {
		DB::update('forum_postcomment', array('comment' => $totalv, 'dateline' => TIMESTAMP + 1), "pid='$postcomment[pid]' AND authorid='0'");
	} else {
		DB::delete('forum_postcomment', "pid='$postcomment[pid]' AND authorid='0'");
	}

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($postcomment), 'var' => 'post', 'item' => 'reason_delete_comment') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'pid' => $postcomment['pid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
	'modtids'	=> 0,
	'modlog'	=> $thread
	);

}

?>