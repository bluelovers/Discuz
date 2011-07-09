<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$ac = in_array($_GET['ac'], array('newnovel', 'managenovel', 'comment', 'deletecomment', 'managecomment'))?$_GET['ac']:'index';

$novelid = $_G['gp_novelid'];
$message = $_G['gp_message'];

if($ac=='index'){

	showmessage($pdlang['novelcp_error']);
	
}elseif($ac=='comment'){
	
	$cid = intval($_GET['cid']);
	$comment = array();
	if($cid) {
		$query = DB::query("SELECT * FROM ".DB::table('pdnovel_comment')." WHERE cid='$cid'");
		$comment = DB::fetch($query);
	}

	if($_GET['op'] == 'requote'){

		if(!empty($comment['message'])) {
			include_once libfile('class/bbcode');
			$bbcode = & bbcode::instance();
			$comment['message'] = $bbcode->html2bbcode($comment['message']);
			$comment['message'] = preg_replace("/\[quote\].*?\[\/quote\]/is", '', $comment['message']);
			$comment['message'] = getstr($comment['message'], 150, 0, 0, 2, -1);
		}
	
	}
	
	$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
	$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;
	
	if(submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {	
		if(!checkperm('allownewcomment')) {
			//showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		$novelid = intval($_POST['novelid']);
		$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid='$novelid'");
		if(empty($novel)) {
			showmessage("comment_comment_noexist");
		}
	
		require_once libfile('function/spacecp');
		ckrealname('comment');
		cknewuser();
	
		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
		}
		$message = getstr($_POST['message'], 0, 1, 1, 1, 0);
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
			'novelid' => $novelid,
			'postip' => $_G['onlineip'],
			'dateline' => $_G['timestamp'],
			'status' => $comment_status,
			'message' => $message
		);
	
		DB::insert('pdnovel_comment', $setarr);
		DB::query("UPDATE ".DB::table('pdnovel_view')." SET comments=comments+1 WHERE novelid=$novelid");
		DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
		updatecreditbyaction('pdnovelcomment', $_G['uid']);
		showmessage('do_success', $_POST['referer'] ? $_POST['referer'] : "novel.php?mod=view&novelid=$novelid#comment");
	}

}

include_once template("pdnovel/novelcp_comment");


?>