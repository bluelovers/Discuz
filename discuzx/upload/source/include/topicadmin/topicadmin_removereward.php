<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_removereward.php 20099 2011-02-15 01:55:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowremovereward']) {
	showmessage('no_privilege_removereward');
}

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

?>