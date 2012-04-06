<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_stamp.php 20099 2011-02-15 01:55:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowstampthread']) {
	showmessage('no_privilege_stampthread');
}

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

?>