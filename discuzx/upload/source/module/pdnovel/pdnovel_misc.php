<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if($_G['gp_ac'] == 'vote'){

	if(empty($_G['uid'])) {
		showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
	}
	$novelid = $_G['gp_novelid'];
	$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0 LIMIT 1");
	if(!$novel){
		showmessage($lang['pdnovel_error']);
	}
	if($novel['authorid'] == $_G['uid']) {
		showmessage($lang['vote_self_error']);
	}
	$find = DB::result_first("SELECT uid FROM ".DB::table('pdnovel_vote')." WHERE uid='$_G[uid]' AND novelid='$novelid'");
	if($find) {
		showmessage($lang['vote_haven']);
	}
	$setarr = array(
		'novelid' => $novelid,
		'uid' => $_G['uid'],
		'dateline' => $_G['timestamp']
	);
	DB::insert('pdnovel_vote', $setarr);
	$now = time(); //当前时间
	$voteupdate = "lastvote=$now,allvote=allvote+1";
	if(date("d",$novel['lastvote'])==date("d",$now)){//同一天
		$voteupdate = $voteupdate.",dayvote = dayvote+1";
	}else{
		$voteupdate = $voteupdate.",dayvote = 1";
	}
	if(date("W",$novel['lastvote'])==date("W",$now)){//同一周
		$voteupdate = $voteupdate.",weekvote = weekvote+1";
	}else{
		$voteupdate = $voteupdate.",weekvote = 1";
	}
	if(date("m",$novel['lastvote'])==date("m",$now)){//同一月
		$voteupdate = $voteupdate.",monthvote = monthvote+1";
	}else{
		$voteupdate = $voteupdate.",monthvote = 1";
	}
	DB::query("UPDATE LOW_PRIORITY ".DB::table('pdnovel_view')." SET $voteupdate WHERE novelid=$novelid", 'UNBUFFERED');
	updatecreditbyaction('pdnovelvote', $_G['uid']);
	showmessage($lang['vote_succeed']);

}elseif($_G['gp_ac'] == 'mark'){

	if(empty($_G['uid'])) {
		showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
	}
	$novelid = $_G['gp_novelid'];
	$chapterid = $_G['gp_chapterid'] ? $_G['gp_chapterid'] : 0;
	$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0 LIMIT 1");
	if(!$novel){
		showmessage($lang['pdnovel_error']);
	}
	$find = DB::result_first("SELECT uid FROM ".DB::table('pdnovel_mark')." WHERE uid='$_G[uid]' AND novelid='$novelid'");
	if($find) {
		if($chapterid){
			DB::query("UPDATE ".DB::table('pdnovel_mark')." SET chapterid=$chapterid WHERE uid='$_G[uid]' AND novelid='$novelid'");
		}else{
			showmessage($lang['mark_haven']);
		}
	}else{
		$setarr = array(
			'novelid' => $novelid,
			'uid' => $_G['uid'],
			'chapterid' => $chapterid,
			'dateline' => $_G['timestamp']
		);
		DB::insert('pdnovel_mark', $setarr);
		DB::query("UPDATE LOW_PRIORITY ".DB::table('pdnovel_view')." SET allmark=allmark+1 WHERE novelid=$novelid", 'UNBUFFERED');
		updatecreditbyaction('pdnovelmark', $_G['uid']);
	}
	showmessage($lang['mark_succeed']);
	
}elseif($_G['gp_ac'] == 'rate'){
	$balance = getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]);
	if(!submitcheck('ratesubmit')) {
		$novelid = $_G['gp_novelid'];
		if($balance==0){
			showmessage($lang['rate_no_credit']);
		}
		$leftbalance = $balance - 1;
		include_once template('pdnovel/rate');
	} else {
		$credits = $_G['gp_credits'];
		if ($balance<$credits){
			showmessage($lang['rate_not_enough']);
		}
		$novelid = $_G['gp_novelid'];
		$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0;");
		if(!$novel){
			showmessage('undefined_action', NULL);
		}elseif($novel['authorid'] == $_G['uid']){
			showmessage('thread_rate_member_invalid', NULL);
		}
		DB::query("INSERT INTO ".DB::table('pdnovel_rate')." (novelid, uid, username, credits, dateline) VALUES ($novelid, $_G[uid], '$_G[username]', $credits, $_G[timestamp])", 'UNBUFFERED');
		DB::query("UPDATE LOW_PRIORITY ".DB::table('pdnovel_view')." SET rate=rate+$credits WHERE novelid=$novelid", 'UNBUFFERED');
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$credits), 1, 'BAC', $novelid);
		if($_POST['message']){
			$message = getstr($_POST['message'], 600, 1, 1, 1, 0);
			$message = '<font color=red>'.$message.'</font>';
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
		}
		showmessage('do_success', "novel.php?mod=view&novelid=$novelid");
	}
	
}elseif($_G['gp_ac'] == 'star'){
	if(empty($_G['uid'])) {
		showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
	}
	$novelid = $_G['gp_novelid'];
	$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0 LIMIT 1");
	if(!$novel){
		showmessage($lang['pdnovel_error']);
	}
	if($novel['authorid'] == $_G['uid']) {
		showmessage('click_no_self');
	}
	if($_G['gp_op'] == 'add'){
		$find = DB::result_first("SELECT uid FROM ".DB::table('pdnovel_star')." WHERE uid='$_G[uid]' AND novelid='$novelid'");
		if($find) {
			showmessage($lang['star_haven']);
		}
		$clickid = $_G['gp_clickid'];
		$setarr = array(
			'novelid' => $novelid,
			'clickid' => $clickid,
			'uid' => $_G['uid'],
			'dateline' => $_G['timestamp']
		);
		DB::insert('pdnovel_star', $setarr);
		DB::query("UPDATE ".DB::table('pdnovel_view')." SET click=click+1, click{$clickid}=click{$clickid}+1 WHERE novelid=$novelid");
		updatecreditbyaction('pdnovelstar', $_G['uid']);
		showmessage($lang['star_succeed']);
	}elseif($_G['gp_op'] == 'show'){
		$percentage = $width = array();
		for($i=1;$i<6;$i++){
			$percentage[$i] = round($novel['click'.$i]*100/$novel[click],1);
			$width[$i] = ceil($percentage[$i]*0.7)+1;
			$sum_score += $novel['click'.$i]*$i;
		}
		$novel_score = round($sum_score*2/$novel[click],1);
		$current_rating = $novel_score*10;
		$current_i = ceil($novel_score/2);
		$current_text = array($lang['star_0'], $lang['star_2'], $lang['star_4'], $lang['star_6'], $lang['star_8'], $lang['star_10']);
		include_once template('common/header_ajax');
		include_once template('pdnovel/ajax_star');
		include_once template('common/footer_ajax');
	}
}elseif($_G['gp_ac'] == 'upload'){
	
	if($_G['gp_inajax']!='yes'){
		$imgexts = 'jpg, jpeg, gif, png, bmp';
		include template('pdnovel/upload');
	}else{
		require_once libfile('class/upload');
		$upload = new discuz_upload();
		$upload->init($_FILES['file']);
		$attach = $upload->attach;
	
		if(!$upload->error()) {
			$upload->save();
		}
		if($upload->error()) {
			novel_upload_error($upload->error());
		}
		if($attach) {
			echo 'data/attachment/temp/'.$attach['attachment'];
		}

	}
}
?>