<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_printable.php 19920 2011-01-24 06:49:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$thisbg = '#FFFFFF';
$posttable = getposttablebytid($_G['tid']);
$addsql = getstatus($_G['forum_thread']['status'], 2) ? 'AND first=\'1\'' : 'ORDER BY dateline LIMIT 100';
$query = DB::query("SELECT * FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' $addsql");
$userinfo = $uids = $skipaids = array();
while($post = DB::fetch($query)) {

	$post['dateline'] = dgmdate($post['dateline'], 'u');
	if(preg_match("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", $post['message'], $hide)) {
		if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $hide[1], $matchaids)) {
			$skipaids = array_merge($skipaids, $matchaids[1]);
		}
		$post['message'] = preg_replace("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", '', $post['message']);
	}
	$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0));

	if($post['attachment']) {
		$attachment = 1;
	}
	$post['attachments'] = array();
	if($post['attachment'] && ($_G['group']['allowgetattach'] || $_G['group']['allowgetimage'])) {
		$_G['forum_attachpids'] .= ",$post[pid]";
		$post['attachment'] = 0;
		if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
			$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
		}
	}
	$uids[] = $post['authorid'];
	$postlist[$post['pid']] = $post;
}
if($uids) {
	$uids = array_unique($uids);
	$query = DB::query("SELECT uid, username, groupid FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($uids).")");
	while($user = DB::fetch($query)) {
		$userinfo[$user[uid]] = $user;
	}
}

if($_G['forum_attachpids']) {
	require_once libfile('function/attachment');
	parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}

include template('forum/viewthread_printable');

?>