<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_stamplist.php 20099 2011-02-15 01:55:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowstamplist']) {
	showmessage('no_privilege_stamplist');
}

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

?>