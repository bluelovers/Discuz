<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_topicadmin.php 22043 2011-04-20 08:56:19Z zhengqingpeng $
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
	$_G['gp_archiveid'] = intval($_G['gp_archiveid']);
	if(!empty($_G['gp_archiveid']) && in_array($_G['gp_archiveid'], $threadtableids)) {
		$threadtable = "forum_thread_{$_G['gp_archiveid']}";
	} else {
		$threadtable = 'forum_thread';
	}

	$thread = DB::fetch_first("SELECT * FROM ".DB::table($threadtable)." WHERE tid='$_G[tid]' AND fid='$_G[fid]'".(!$_G['forum_auditstatuson'] ? "  AND displayorder>='0'" : ''));
	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	$navigation .= " &raquo; <a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a> ";
	$navtitle .= ' - '.$thread['subject'].' - ';

	if($thread['special'] && in_array($_G['gp_action'], array('copy', 'split', 'merge'))) {
		showmessage('special_noaction');
	}
}
if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_G['gp_sendreasonpm'])) {
	$forumname = strip_tags($_G['forum']['name']);
	$sendreasonpm = 1;
} else {
	$sendreasonpm = 0;
}

$_G['gp_handlekey'] = 'mods';


if(preg_match('/^\w+$/', $_G['gp_action']) && file_exists($topicadminfile = libfile('topicadmin/'.$_G['gp_action'], 'include'))) {
	require_once $topicadminfile;
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