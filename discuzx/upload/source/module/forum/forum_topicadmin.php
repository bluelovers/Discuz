<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_topicadmin.php 16518 2010-09-08 02:00:07Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

$_G['inajax'] = 1;
$_G['gp_topiclist'] = !empty($_G['gp_topiclist']) ? (is_array($_G['gp_topiclist']) ? array_unique($_G['gp_topiclist']) : $_G['gp_topiclist']) : array();

loadcache(array('modreasons', 'stamptypeid', 'threadtableids'));

require_once libfile('function/post');
require_once libfile('function/misc');

$modpostsnum = 0;
$resultarray = $thread = array();

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();

if(!$_G['uid'] || !$_G['forum']['ismoderator']) {
	showmessage('admin_nopermission', NULL);
}

$frommodcp = !empty($_G['gp_frommodcp']) ? intval($_G['gp_frommodcp']) : 0;


$navigation = $navtitle = '';

if(!empty($_G['tid'])) {
	if(!empty($_G['gp_archiveid']) && in_array($_G['gp_archiveid'], $threadtableids)) {
		$threadtable = "forum_thread_{$_G['gp_archiveid']}";
	} else {
		$threadtable = 'forum_thread';
	}

	$thread = DB::fetch_first("SELECT * FROM ".DB::table($threadtable)." WHERE tid='$_G[tid]' AND fid='$_G[fid]' AND displayorder>='0'");
	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	$navigation .= " &raquo; <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
	$navtitle .= ' - '.$thread['subject'].' - ';

	if(($thread['special'] && in_array($_G['gp_action'], array('copy', 'split', 'merge'))) || ($thread['digest'] == '-1' && !in_array($_G['gp_action'], array('delpost', 'banpost', 'getip')))) {
		showmessage('special_noaction');
	}
}
if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_G['gp_sendreasonpm'])) {
	$forumname = strip_tags($_G['forum']['name']);
	$sendreasonpm = 1;
} else {
	$sendreasonpm = 0;
}

$postcredits = $_G['forum']['postcredits'] ? $_G['forum']['postcredits'] : $_G['setting']['creditspolicy']['post'];
$replycredits = $_G['forum']['replycredits'] ? $_G['forum']['replycredits'] : $_G['setting']['creditspolicy']['reply'];
$digestcredits = $_G['forum']['digestcredits'] ? $_G['forum']['digestcredits'] : $_G['setting']['creditspolicy']['digest'];
$postattachcredits = $_G['forum']['postattachcredits'] ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];
$_G['gp_handlekey'] = 'mods';


if($_G['gp_action'] == 'moderate') {

	require_once libfile('topicadmin/moderation', 'include');

} elseif($_G['gp_action'] == 'delpost' && $_G['group']['allowdelpost']) {

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
				if($post['dateline'] < $losslessdel) {
					if($post['first']) {
						updatemembercount($post['authorid'], array('threads' => -1, 'post' => -1), false);
					} else {
						updatemembercount($post['authorid'], array('posts' => -1), false);
					}
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

} elseif($_G['gp_action'] == 'delcomment' && $_G['group']['allowdelpost'] && !empty($_G['gp_topiclist'])) {

	if(!submitcheck('modsubmit')) {

		$commentid = $_G['gp_topiclist'][0];
		$pid = DB::result_first("SELECT pid FROM ".DB::table('forum_postcomment')." WHERE id='$commentid'");
		if(!$pid) {
			showmessage('undefined_action', NULL);
		}
		$deleteid = '<input type="hidden" name="topiclist" value="'.$commentid.'" />';

		include template('forum/topicadmin_action');

	} else {

		$reason = checkreasonpm();
		$modaction = 'DCM';

		$commentid = intval($_G['gp_topiclist']);
		$postcomment = DB::fetch_first("SELECT * FROM ".DB::table('forum_postcomment')." WHERE id='$commentid'");
		if(!$postcomment) {
			showmessage('undefined_action', NULL);
		}
		DB::delete('forum_postcomment', "id='$commentid'");
		if(!DB::result_first("SELECT count(*) FROM ".DB::table('forum_postcomment')." WHERE pid='$postcomment[pid]'")) {
			DB::update('forum_post', array('comment' => 0), "pid='$postcomment[pid]'");
		}
		updatepostcredits('-', $postcomment['authorid'], 'reply', $_G['fid']);

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

} elseif($_G['gp_action'] == 'refund' && $_G['group']['allowrefund'] && $thread['price'] > 0) {

	if(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
		showmessage('credits_transaction_disabled');
	}

	if($thread['special'] != 0) {
		showmessage('special_refundment_invalid');
	}

	if(!submitcheck('modsubmit')) {

		$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
		$payment = DB::fetch_first("SELECT COUNT(*) AS payers, SUM($extcredit) AS netincome FROM ".DB::table('common_credit_log')." WHERE operation='STC' AND relatedid='$_G[tid]'");
		$payment['payers'] = intval($payment['payers']);
		$payment['netincome'] = intval($payment['netincome']);

		include template('forum/topicadmin_action');

	} else {

		$modaction = 'RFD';
		$modpostsnum ++;

		$reason = checkreasonpm();

		$totalamount = 0;
		$amountarray = array();

		$logarray = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_credit_log')." WHERE operation='BTC' AND relatedid='$_G[tid]'");
		while($log = DB::fetch($query)) {
			$totalamount += $log['amount'];
			$amountarray[$log['amount']][] = $log['uid'];
		}

		updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][1] => -$totalamount));
		DB::query("UPDATE ".DB::table('forum_thread')." SET price='-1', moderated='1' WHERE tid='$_G[tid]'");

		foreach($amountarray as $amount => $uidarray) {
			updatemembercount($uidarray, array($_G['setting']['creditstransextra'][1] => $amount));
		}

		DB::delete('common_credit_log',  "relatedid='$_G[tid]' AND operation IN('BTC', 'STC')");

		$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_moderate') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
		'modtids'	=> $thread['tid'],
		'modlog'	=> $thread
		);

	}

} elseif($_G['gp_action'] == 'repair' && $_G['group']['allowrepairthread']) {
	$posttable = getposttablebytid($_G['tid']);

	$replies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'") - 1;

	$query = DB::query("SELECT a.aid FROM ".DB::table($posttable)." p, ".DB::table('forum_attachment')." a WHERE a.tid='$_G[tid]' AND a.pid=p.pid AND p.invisible='0' LIMIT 1");
	$attachment = DB::num_rows($query) ? 1 : 0;

	$firstpost  = DB::fetch_first("SELECT pid, subject, rate FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline LIMIT 1");
	$firstpost['subject'] = addslashes(cutstr($firstpost['subject'], 79));
	@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);

	$lastpost  = DB::fetch_first("SELECT author, dateline FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline DESC LIMIT 1");

	DB::query("UPDATE ".DB::table('forum_thread')." SET subject='$firstpost[subject]', replies='$replies', lastpost='$lastpost[dateline]', lastposter='".addslashes($lastpost['author'])."', rate='$firstpost[rate]', attachment='$attachment' WHERE tid='$_G[tid]'", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table($posttable)." SET first='1', subject='$firstpost[subject]' WHERE pid='$firstpost[pid]'", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table($posttable)." SET first='0' WHERE tid='$_G[tid]' AND pid<>'$firstpost[pid]'", 'UNBUFFERED');
	showmessage('admin_repair_succeed');

} elseif($_G['gp_action'] == 'getip' && $_G['group']['allowviewip']) {
	$pid = $_G['gp_pid'];
	$posttable = getposttablebytid($_G['tid']);
	$member = DB::fetch_first("SELECT m.adminid, p.first, p.useip FROM ".DB::table($posttable)." p
				LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid
				WHERE p.pid='$pid' AND p.tid='$_G[tid]'");
	if(!$member) {
		showmessage('thread_nonexistence', NULL);
	} elseif(($member['adminid'] == 1 && $_G['adminid'] > 1) || ($member['adminid'] == 2 && $_G['adminid'] > 2)) {
		showmessage('admin_getip_nopermission', NULL);
	} elseif($member['first'] && $thread['digest'] == '-1') {
		showmessage('special_noaction');
	}

	$member['iplocation'] = convertip($member['useip']);

	include template('forum/topicadmin_getip');

} elseif($_G['gp_action'] == 'split' && $_G['group']['allowsplitthread']) {

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
		DB::query("UPDATE ".DB::table('forum_attachment')." SET tid='$newtid' WHERE pid IN ($pids)");

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

} elseif($_G['gp_action'] == 'merge' && $_G['group']['allowmergethread']) {

	if(!submitcheck('modsubmit')) {

		include template('forum/topicadmin_action');

	} else {
		$posttable = getposttablebytid($_G['tid']);
		$modaction = 'MRG';

		$reason = checkreasonpm();
		$othertid = intval($_G['gp_othertid']);

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

		$firstpost = DB::fetch_first("SELECT pid, fid, authorid, author, subject, dateline FROM ".DB::table($posttable)." WHERE tid IN ('$_G[tid]', '$othertid') AND invisible='0' ORDER BY dateline LIMIT 1");

		DB::query("UPDATE ".DB::table($posttable)." SET tid='$_G[tid]' WHERE tid='$othertid'");
		$postsmerged = DB::affected_rows();

		DB::query("UPDATE ".DB::table('forum_attachment')." SET tid='$_G[tid]' WHERE tid='$othertid'");
		DB::query("DELETE FROM ".DB::table('forum_thread')." WHERE tid='$othertid'");
		DB::query("DELETE FROM ".DB::table('forum_threadmod')." WHERE tid='$othertid'");

		DB::query("UPDATE ".DB::table($posttable)." SET first=(pid='$firstpost[pid]'), fid='$firstpost[fid]' WHERE tid='$_G[tid]'");
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

} elseif($_G['gp_action'] == 'copy' && $_G['group']['allowcopythread'] && $thread) {

	if(!submitcheck('modsubmit')) {
		require_once libfile('function/forumlist');
		$forumselect = forumselect();
		include template('forum/topicadmin_action');

	} else {

		$modaction = 'CPY';
		$reason = checkreasonpm();
		$copyto = $_G['gp_copyto'];
		$toforum = DB::fetch_first("SELECT fid, name, modnewposts FROM ".DB::table('forum_forum')." WHERE fid='$copyto' AND status='1' AND type<>'group'");
		if(!$toforum) {
			showmessage('admin_copy_invalid');
		} else {
			$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
			$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
			if($modnewthreads || $modnewreplies) {
				showmessage('admin_copy_hava_mod');
			}
		}


		unset($thread['tid']);
		$thread['fid'] = $copyto;
		$thread['dateline'] = $thread['lastpost'] = TIMESTAMP;
		$thread['lastposter'] = $thread['author'];
		$thread['views'] = $thread['replies'] = $thread['highlight'] = $thread['digest'] = 0;
		$thread['rate'] = $thread['displayorder'] = $thread['attachment'] = 0;

		$posttableid = getposttableid('p');
		$thread['posttableid'] = $posttableid;
		$threadid = DB::insert('forum_thread', $thread, true);
		$posttable = getposttablebytid($_G['tid']);
		if($post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first=1 LIMIT 1")) {
			$post['pid'] = '';
			$post['tid'] = $threadid;
			$post['fid'] = $copyto;
			$post['dateline'] = TIMESTAMP;
			$post['attachment'] = 0;
			$post['invisible'] = $post['rate'] = $post['ratetimes'] = 0;
			$pid = insertpost($post);
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

} elseif($_G['gp_action'] == 'removereward' && $_G['group']['allowremovereward']) {

	if(!submitcheck('modsubmit')) {
		include template('forum/topicadmin_action');
	} else {
		if(!is_array($thread) || $thread['special'] != '3') {
			showmessage('reward_end');
		}

		$modaction = 'RMR';
		$reason = checkreasonpm();
		$answererid = DB::result_first("SELECT uid FROM ".DB::table('common_credit_log')." WHERE operation='RAC' AND relatedid='$thread[tid]'");

		if($thread['price'] < 0) {
			updatemembercount($answererid, array($_G['setting']['creditstransextra'][2] => -$thread['price']));
		}

		updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][2] => $thread['price']));
		DB::query("UPDATE ".DB::table('forum_thread')." SET special='0', price='0' WHERE tid='$thread[tid]'", 'UNBUFFERED');
		DB::delete('common_credit_log', "relatedid='$thread[tid]' AND operation IN('RTC', 'RAC')");
		$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$thread[tid]",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_remove_reward') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason), 'threadid' => $thread[tid]),
		'modtids'	=> $thread['tid']
		);
	}

} elseif($_G['gp_action'] == 'banpost' && $_G['group']['allowbanpost']) {

	$topiclist = $_G['gp_topiclist'];
	$modpostsnum = count($topiclist);
	if(!($banpids = dimplode($topiclist))) {
		showmessage('admin_banpost_invalid');
	} elseif(!$_G['group']['allowbanpost'] || !$_G['tid']) {
		showmessage('admin_nopermission', NULL);
	}

	$posts = array();
	$banstatus = 0;
	$posttable = getposttablebytid($_G['tid']);
	$query = DB::query("SELECT pid, first, authorid, status, dateline, message FROM ".DB::table($posttable)." WHERE pid IN ($banpids) AND tid='$_G[tid]'");
	while($post = DB::fetch($query)) {
		if($post['first'] && $thread['digest'] == '-1') {
			showmessage('special_noaction');
		}
		$banstatus = ($post['status'] & 1) || $banstatus;
		$posts[] = $post;
	}

	if(!submitcheck('modsubmit')) {

		$banid = $checkunban = $checkban = '';
		foreach($topiclist as $id) {
			$banid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
		}

		$banstatus ? $checkunban = 'checked="checked"' : $checkban = 'checked="checked"';

		include template('forum/topicadmin_action');

	} else {

		$banned = intval($_G['gp_banned']);
		$modaction = $banned ? 'BNP' : 'UBN';

		$reason = checkreasonpm();

		$pids = $comma = '';
		foreach($posts as $k => $post) {
			if($banned) {
				my_post_log('ban', array('pid' => $post['pid'], 'uid' => $post['authorid']));
				DB::query("UPDATE ".DB::table($posttable)." SET status=status|1 WHERE pid='$post[pid]'", 'UNBUFFERED');
			} else {
				my_post_log('unban', array('pid' => $post['pid'], 'uid' => $post['authorid']));
				DB::query("UPDATE ".DB::table($posttable)." SET status=status^1 WHERE pid='$post[pid]' AND status=status|1", 'UNBUFFERED');
			}
			$pids .= $comma.$post['pid'];
			$comma = ',';
		}

		$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_ban_post') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
		'modtids'	=> 0,
		'modlog'	=> $thread
		);

		procreportlog('', $pids);

	}

} elseif($_G['gp_action'] == 'warn' && $_G['group']['allowwarnpost']) {

	$topiclist = $_G['gp_topiclist'];
	if(!($warnpids = dimplode($topiclist))) {
		showmessage('admin_warn_invalid');
	} elseif(!$_G['group']['allowbanpost'] || !$_G['tid']) {
		showmessage('admin_nopermission', NULL);
	}

	$posts = $authors = array();
	$authorwarnings = $warningauthor = $warnstatus = '';
	$posttable = getposttablebytid($_G['tid']);
	$query = DB::query("SELECT p.pid, p.authorid, p.author, p.status, p.dateline, p.message, m.adminid FROM ".DB::table($posttable)." p
		LEFT JOIN ".DB::table('common_member')." m ON p.authorid=m.uid WHERE p.pid IN ($warnpids) AND p.tid='$_G[tid]'");
	while($post = DB::fetch($query)) {
		if($_G['adminid'] == 1 && $post['adminid'] != 1 ||
			$_G['adminid'] == 2 && !in_array($post['adminid'], array(1, 2)) ||
			$_G['adminid'] == 3 && in_array($post['adminid'], array(0, -1))) {
			$warnstatus = ($post['status'] & 2) || $warnstatus;
			$authors[$post['authorid']] = 1;
			$posts[] = $post;
		}
	}

	if(!$posts) {
		showmessage('admin_warn_nopermission');
	}
	$authorcount = count(array_keys($authors));
	$modpostsnum = count($posts);

	if($modpostsnum == 1 || $authorcount == 1) {
		$authorwarnings = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_warning')." WHERE authorid='{$posts[0][authorid]}'");
		$warningauthor = $posts[0]['author'];
	}

	if(!submitcheck('modsubmit')) {

		$warnpid = $checkunwarn = $checkwarn = '';
		foreach($topiclist as $id) {
			$warnpid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
		}

		$warnstatus ? $checkunwarn = 'checked="checked"' : $checkwarn = 'checked="checked"';

		include template('forum/topicadmin_action');

	} else {

		$warned = intval($_G['gp_warned']);
		$modaction = $warned ? 'WRN' : 'UWN';

		$reason = checkreasonpm();

		$pids = $comma = '';
		foreach($posts as $k => $post) {
			if($post['adminid'] == 0) {
				if($warned && !($post['status'] & 2)) {
					my_post_log('warn', array('pid' => $post['pid'], 'uid' => $post['authorid']));
					DB::query("UPDATE ".DB::table($posttable)." SET status=status|2 WHERE pid='$post[pid]'", 'UNBUFFERED');
					$reason = cutstr(dhtmlspecialchars($_G['gp_reason']), 40);
					DB::query("INSERT INTO ".DB::table('forum_warning')." (pid, operatorid, operator, authorid, author, dateline, reason) VALUES ('$post[pid]', '$_G[uid]', '$_G[username]', '$post[authorid]', '".addslashes($post['author'])."', '$_G[timestamp]', '$reason')", 'UNBUFFERED');
					$authorwarnings = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_warning')." WHERE authorid='$post[authorid]' AND dateline>=$_G[timestamp]-".$_G[setting][warningexpiration]*86400);
					if($authorwarnings >= $_G['setting']['warninglimit']) {
						$member = DB::fetch_first("SELECT adminid, groupid FROM ".DB::table('common_member')." WHERE uid='$post[authorid]'");
						if($member && $member['groupid'] != 4) {
							$banexpiry = TIMESTAMP + $_G['setting']['warningexpiration'] * 86400;
							$groupterms = array();
							$groupterms['main'] = array('time' => $banexpiry, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
							$groupterms['ext'][4] = $banexpiry;
					  		DB::query("UPDATE ".DB::table('common_member')." SET groupid='4', groupexpiry='".groupexpiry($groupterms)."' WHERE uid='$post[authorid]'");
					  		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".addslashes(serialize($groupterms))."' WHERE uid='$post[authorid]'");
					  	}
					}
					$pids .= $comma.$post['pid'];
					$comma = ',';
				} elseif(!$warned && ($post['status'] & 2)) {
					my_post_log('unwarn', array('pid' => $post['pid'], 'uid' => $post['authorid']));
					DB::query("UPDATE ".DB::table($posttable)." SET status=status^2 WHERE pid='$post[pid]' AND status=status|2", 'UNBUFFERED');
					DB::query("DELETE FROM ".DB::table('forum_warning')." WHERE pid='$post[pid]'", 'UNBUFFERED');
					$pids .= $comma.$post['pid'];
					$comma = ',';
				}
			}
		}

		$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_warn_post') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason),
				'warningexpiration' => $_G['setting']['warningexpiration'], 'warninglimit' => $_G['setting']['warninglimit'], 'warningexpiration' => $_G['setting']['warningexpiration'],
				'authorwarnings' => $authorwarnings),
		'modtids'	=> 0,
		'modlog'	=> $thread
		);

		procreportlog('', $pids);

	}

} elseif($_G['gp_action'] == 'stamp' && $_G['group']['allowstampthread']) {

	loadcache('stamps');

	if(!submitcheck('modsubmit')) {

		include template('forum/topicadmin_action');

	} else {

		$modaction = $_G['gp_stamp'] !== '' ? 'SPA' : 'SPD';
		$_G['gp_stamp'] = $_G['gp_stamp'] !== '' ? $_G['gp_stamp'] : -1;
		$reason = checkreasonpm();

		DB::query("UPDATE ".DB::table('forum_thread')." SET moderated='1', stamp='$_G[gp_stamp]' WHERE tid='$_G[tid]'");
		if($modaction == 'SPA' && $_G['cache']['stamps'][$_G['gp_stamp']]['icon']) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET icon='".$_G['cache']['stamps'][$_G['gp_stamp']]['icon']."' WHERE tid='$_G[tid]'");
		} elseif($modaction == 'SPD' && $_G['cache']['stamps'][$thread['stamp']]['icon'] == $thread['icon']) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET icon='-1' WHERE tid='$_G[tid]'");
		}

		$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => $_G['gp_stamp'] !== '' ? 'reason_stamp_update' : 'reason_stamp_delete') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason), 'stamp' => $_G['cache']['stamps'][$stamp]['text']),
		'modaction'	=> $modaction,
		'modlog'	=> $thread
		);
		$modpostsnum = 1;

		updatemodlog($_G['tid'], $modaction, 0, 0, '', $modaction == 'SPA' ? $_G['gp_stamp'] : 0);

	}

} elseif($_G['gp_action'] == 'stamplist' && $_G['group']['allowstamplist']) {

	loadcache('stamps');

	if(!submitcheck('modsubmit')) {

		include template('forum/topicadmin_action');

	} else {

		$_G['gp_stamplist'] = $_G['gp_stamplist'] !== '' ? $_G['gp_stamplist'] : -1;
		$modaction = $_G['gp_stamplist'] >= 0 ? 'L'.sprintf('%02d', $_G['gp_stamplist']) : 'SLD';
		$reason = checkreasonpm();

		DB::query("UPDATE ".DB::table('forum_thread')." SET moderated='1', icon='$_G[gp_stamplist]' WHERE tid='$_G[tid]'");

		$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => $_G['gp_stamplist'] !== '' ? 'reason_stamplist_update' : 'reason_stamplist_delete') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason), 'stamp' => $_G['cache']['stamps'][$_G['gp_stamplist']]['text']),
		'modaction'	=> $modaction,
		'modlog'	=> $thread
		);
		$modpostsnum = 1;

		updatemodlog($_G['tid'], $modaction);

	}


} elseif($_G['gp_action'] == 'stickreply' && $_G['group']['allowstickreply']) {

	$topiclist = $_G['gp_topiclist'];
	if(empty($topiclist)) {
		showmessage('admin_stickreply_invalid');
	} elseif(!$_G['tid']) {
		showmessage('admin_nopermission', NULL);
	}
	$posttable = getposttablebytid($_G['tid']);
	$sticktopiclist = $posts = array();
	foreach($topiclist as $pid) {
		$post = DB::fetch_first("SELECT p.tid, p.authorid, p.dateline, p.first, t.special FROM ".DB::table($posttable)." p
			LEFT JOIN ".DB::table('forum_thread')." t USING(tid) WHERE p.pid='$pid'");
		$posts[]['authorid'] = $post['authorid'];
		$sqladd = $post['special'] ? "AND first=0" : '';
		$posttable = getposttablebytid($post['tid']);
		$curpostnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$post[tid]' AND dateline<='$post[dateline]' $sqladd");
		if(empty($post['first'])) {
			$sticktopiclist[$pid] = $curpostnum;
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

} elseif($_G['gp_action'] == 'restore' && $_G['adminid'] == '1') {
	if(!submitcheck('modsubmit')) {
		$archiveid = intval($_G['gp_archiveid']);
		include template('forum/topicadmin_action');
	} else {
		$archiveid = $_G['gp_archiveid'];
		if(!in_array($archiveid, $threadtableids)) {
			$archiveid = 0;
		}
		$threadtable = $archiveid ? "forum_thread_$archiveid" : 'forum_thread';
		DB::query("INSERT INTO ".DB::table('forum_thread')." SELECT * FROM ".DB::table($threadtable)." WHERE tid='{$_G['tid']}'");
		DB::delete($threadtable, "tid='{$_G['tid']}'");

		$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." WHERE fid='{$_G['fid']}'");
		if($threadcount) {
			DB::update('forum_forum_threadtable', array('threads' => $threadcount), "fid='{$_G['fid']}' AND threadtableid='$archiveid'");
		} else {
			DB::delete('forum_forum_threadtable', "fid='{$_G['fid']}' AND threadtableid='$archiveid'");
		}
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum_threadtable')." WHERE fid='{$_G['fid']}'")) {
			DB::update('forum_forum', array('archive' => '0'), "fid='{$_G['fid']}'");
		}
		$modaction = 'RST';
		$reason = checkreasonpm();
		$resultarray = array(
			'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
			'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread') : array()),
			'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
			'modaction'	=> $modaction,
			'modlog'	=> $thread
		);
	}
} else {

	showmessage('undefined_action', NULL);

}

if($resultarray) {

	if($resultarray['modtids']) {
		updatemodlog($resultarray['modtids'], $modaction, $resultarray['expiration']);
	}

	updatemodworks($modaction, $modpostsnum);
	if(is_array($resultarray['modlog'])) {
		if(isset($resultarray['modlog']['tid'])) {
			modlog($resultarray['modlog'], $modaction);
		} else {
			foreach($resultarray['modlog'] as $thread) {
				modlog($thread, $modaction);
			}
		}
	}

	if($resultarray['reasonpm']) {
		$modactioncode = lang('forum/modaction');
		$modaction = $modactioncode[$modaction];
		foreach($resultarray['reasonpm']['data'] as $var) {
			sendreasonpm($var, $resultarray['reasonpm']['item'], $resultarray['reasonvar']);
		}
	}

	showmessage((isset($resultarray['message']) ? $resultarray['message'] : 'admin_succeed'), $resultarray['redirect']);

}

?>