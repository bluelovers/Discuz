<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_delpost.php 16938 2010-09-17 04:37:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowdelpost']) {
	showmessage('undefined_action', NULL);
}

$topiclist = $_G['gp_topiclist'];
$modpostsnum = count($topiclist);
if(!($deletepids = dimplode($topiclist))) {
	showmessage('admin_delpost_invalid');
} elseif(!$_G['group']['allowdelpost'] || !$_G['tid']) {
	showmessage('admin_nopermission', NULL);
}  else {
	$posttable = getposttablebytid($_G['tid']);
	$query = DB::query("SELECT pid FROM ".DB::table($posttable)." WHERE pid IN ($deletepids) AND first='1'");
	if(DB::num_rows($query)) {
		dheader("location: $_G[siteurl]forum.php?mod=topicadmin&action=moderate&operation=delete&optgroup=3&fid=$_G[fid]&moderate[]=$thread[tid]&inajax=yes".($_G['gp_infloat'] ? "&infloat=yes&handlekey={$_G['gp_handlekey']}" : ''));
	}
}

if(!submitcheck('modsubmit')) {

	$deleteid = '';
	foreach($topiclist as $id) {
		$deleteid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
	}

	include template('forum/topicadmin_action');

} else {

	$reason = checkreasonpm();

	$pids = $comma = '';
	$posts = $uidarray = $puidarray = $auidarray = array();
	$losslessdel = $_G['setting']['losslessdel'] > 0 ? TIMESTAMP - $_G['setting']['losslessdel'] * 86400 : 0;
	$query = DB::query("SELECT pid, authorid, dateline, message, first FROM ".DB::table($posttable)." WHERE pid IN ($deletepids) AND tid='$_G[tid]'");
	while($post = DB::fetch($query)) {
		if(!$post['first']) {
			$posts[] = $post;
			$pids .= $comma.$post['pid'];
			if($post['dateline'] > $losslessdel) {
				updatemembercount($post['authorid'], array('posts' => -1), false);
			} else {
				$puidarray[] = $post['authorid'];
			}
			$modpostsnum++;
			$comma = ',';
		}
	}

	if($puidarray) {
		updatepostcredits('-', $puidarray, 'reply', $_G['fid']);
	}
	if($pids) {
		$query = DB::query("SELECT uid, attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE pid IN ($pids)");
	}
	while($attach = DB::fetch($query)) {
		if(in_array($attach['uid'], $puidarray)) {
			$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
		}
		dunlink($attach);
	}
	if($auidarray) {
		updateattachcredits('-', $auidarray, $postattachcredits);
	}

	$logs = array();
	if($pids) {
		$query = DB::query("SELECT r.extcredits, r.score, p.authorid, p.author FROM ".DB::table('forum_ratelog')." r LEFT JOIN ".DB::table($posttable)." p ON r.pid=p.pid WHERE r.pid IN ($pids)");
		while($author = DB::fetch($query)) {
			if($author['score'] > 0) {
				updatemembercount($author['authorid'], array($author['extcredits'] => -$author['score']));
				$author['score'] = $_G['setting']['extcredits'][$id]['title'].' '.-$author['score'].' '.$_G['setting']['extcredits'][$id]['unit'];
				$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$author[author]\t$author[extcredits]\t$author[score]\t$thread[tid]\t$thread[subject]\t$delpostsubmit");
			}
		}
	}
	if(!empty($logs)) {
		writelog('ratelog', $logs);
		unset($logs);
	}

	DB::delete('common_credit_log', "operation='PRC' AND relatedid IN($pids)");
	DB::query("DELETE FROM ".DB::table('forum_ratelog')." WHERE pid IN ($pids)");
	DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE pid IN ($pids)");
	DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE pid IN ($pids)");
	DB::query("DELETE FROM ".DB::table('forum_postcomment')." WHERE pid IN ($pids)");
	DB::query("DELETE FROM ".DB::table($posttable)." WHERE pid IN ($pids)");
	getstatus($thread['status'], 1) && DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE pid IN ($pids)");
	$thread['stickreply'] && DB::query("DELETE FROM ".DB::table('forum_poststick')." WHERE tid='$thread[tid]' AND pid IN ($pids)");

	foreach(explode(',', $pids) as $pid) {
		my_post_log('delete', array('pid' => $pid));
	}

	if($thread['special']) {
		DB::query("DELETE FROM ".DB::table('forum_trade')." WHERE pid IN ($pids)");
	}

	updatethreadcount($_G['tid'], 1);
	updateforumcount($_G['fid']);

	$_G['forum']['threadcaches'] && deletethreadcaches($thread['tid']);

	$modaction = 'DLP';

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_delete_post') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
	'modtids'	=> 0,
	'modlog'	=> $thread
	);

	procreportlog('', $pids, TRUE);

}

?>