<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_comment.php 15477 2010-08-24 07:36:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$cid = intval($_GET['cid']);
$comment = array();
if($cid) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_comment')." WHERE cid='$cid'");
	$comment = DB::fetch($query);
}
if($_GET['op'] == 'requote') {

	if(!empty($comment['message'])) {

		include_once libfile('class/bbcode');
		$bbcode = & bbcode::instance();
		$comment['message'] = $bbcode->html2bbcode($comment['message']);
		$comment['message'] = preg_replace("/\[quote\].*?\[\/quote\]/is", '', $comment['message']);
		$comment['message'] = getstr($comment['message'], 150, 0, 0, 2, -1);
	}

} elseif($_GET['op'] == 'edit') {

	if(empty($comment)) {
		showmessage('comment_edit_noexist');
	}

	if(!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid'] && $_G['adminid'] != 1 && $_G['gp_modarticlecommentkey'] != modauthkey($comment['cid'])) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(submitcheck('editsubmit')) {
		$message = getstr($_POST['message'], 0, 1, 1, 2);
		if(strlen($message) < 2) showmessage('content_is_too_short');
		$message = censor($message);
		if(censormod($message)) {
			$comment_status = 1;
		} else {
			$comment_status = 0;
		}

		DB::update('portal_comment', array('message' => $message, 'status' => $comment_status), array('cid' => $comment['cid']));

		showmessage('do_success', dreferer());
	}

	include_once libfile('class/bbcode');
	$bbcode = & bbcode::instance();
	$comment['message'] = $bbcode->html2bbcode($comment['message']);

} elseif($_GET['op'] == 'delete') {

	if(empty($comment)) {
		showmessage('comment_delete_noexist');
	}

	if(!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	if(submitcheck('deletesubmit')) {
		DB::query("DELETE FROM ".DB::table('portal_comment')." WHERE cid='$cid'");
		DB::query("UPDATE ".DB::table('portal_article_count')." SET commentnum=commentnum+'-1' WHERE aid='$comment[aid]'");
		showmessage('do_success', dreferer());
	}

}
$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;

if(submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(!checkperm('allowcommentarticle')) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	$aid = intval($_POST['aid']);

	$article = DB::fetch_first("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$aid'");
	if(empty($article)) {
		showmessage("comment_comment_noexist");
	}
	if($article['allowcomment'] != 1) {
		showmessage("comment_comment_notallowed");
	}

	require_once libfile('function/spacecp');
	ckrealname('comment');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}
	$message = getstr($_POST['message'], $_G['group']['allowcommentarticle'], 1, 1, 1, 0);
	if(strlen($message) < 2) showmessage('content_is_too_short');
	$message = censor($message);
	if(censormod($message)) {
		$comment_status = 1;
	} else {
		$comment_status = 0;
	}

	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'aid' => $aid,
		'postip' => $_G['onlineip'],
		'dateline' => $_G['timestamp'],
		'status' => $comment_status,
		'message' => $message
	);

	DB::insert('portal_comment', $setarr);

	DB::query("UPDATE ".DB::table('portal_article_count')." SET commentnum=commentnum+1 WHERE aid='$aid'");
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	showmessage('do_success', $_POST['referer'] ? $_POST['referer'] : "portal.php?mod=comment&quickforward=1&aid=$aid");
}

include_once template("portal/portalcp_comment");

?>