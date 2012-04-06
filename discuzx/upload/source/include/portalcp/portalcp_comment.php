<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_comment.php 19018 2010-12-13 10:06:27Z zhangguosheng $
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

	if((!$_G['group']['allowmanagearticle'] && $_G['uid'] != $comment['uid'] && $_G['adminid'] != 1 && $_G['gp_modarticlecommentkey'] != modauthkey($comment['cid'])) || $_G['groupid'] == '7') {
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
		$idtype = in_array($comment['idtype'], array('aid' ,'topicid')) ? $comment['idtype'] : 'aid';
		$tablename = $idtype == 'aid' ? 'portal_article_count' : 'portal_topic';
		DB::query("UPDATE ".DB::table($tablename)." SET commentnum=commentnum+'-1' WHERE $idtype='$comment[id]'");
		showmessage('do_success', dreferer());
	}

}
$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;

if(submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(!checkperm('allowcommentarticle')) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	$id = 0;
	$idtype = '';
	if(!empty($_POST['aid'])) {
		$id = intval($_POST['aid']);
		$idtype = 'aid';
	} elseif(!empty($_POST['topicid'])) {
		$id = intval($_POST['topicid']);
		$idtype = 'topicid';
	}


	$message = $_POST['message'];

	require_once libfile('function/spacecp');

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	$retmessage = addportalarticlecomment($id, $message, $idtype);
	if($retmessage == 'do_success') {
		showmessage('do_success', $_POST['referer'] ? $_POST['referer'] : "portal.php?mod=comment&id=$id&idtype=$idtype");
	} else {
		showmessage($retmessage, dreferer("portal.php?mod=comment&id=$id&idtype=$idtype"));
	}
}

include_once template("portal/portalcp_comment");

?>