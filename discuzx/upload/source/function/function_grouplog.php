<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_grouplog.php 16644 2010-09-11 03:33:30Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updategroupcreditlog($fid, $uid) {
	global $_G;
	if(empty($fid) || empty($uid)) {
		return false;
	}
	$today = date('Ymd', TIMESTAMP);
	$updategroupcredit = getcookie('groupcredit_'.$fid);
	if($updategroupcredit < $today) {
		$status = DB::result_first("SELECT logdate FROM ".DB::table('forum_groupcreditslog')." WHERE fid='$fid' AND uid='$uid' AND logdate='$today'");
		if(empty($status)) {
			DB::query("UPDATE ".DB::table('forum_forum')." SET commoncredits=commoncredits+1 WHERE fid='$fid'");
			DB::query("REPLACE INTO ".DB::table('forum_groupcreditslog')." (fid, uid, logdate) VALUES ('$fid', '$uid', '$today')");
			if(empty($_G['forum']) || empty($_G['forum']['level'])) {
				$forum = DB::fetch_first("SELECT name, level, commoncredits FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
			} else {
				$_G['forum']['commoncredits'] ++;
				$forum = &$_G['forum'];
			}
			if(empty($_G['grouplevels'])) {
				loadcache('grouplevels');
			}
			$grouplevel = $_G['grouplevels'][$forum['level']];

			if($grouplevel['type'] == 'default' && !($forum['commoncredits'] >= $grouplevel['creditshigher'] && $forum['commoncredits'] < $grouplevel['creditslower'])) {
				$levelid = DB::result_first("SELECT levelid FROM ".DB::table('forum_grouplevel')." WHERE type='default' AND creditshigher<='$forum[commoncredits]' AND creditslower>'$forum[commoncredits]' LIMIT 1");
				if(!empty($levelid)) {
					DB::query("UPDATE ".DB::table('forum_forum')." SET level='$levelid' WHERE fid='$fid'");
					$groupfounderuid = DB::result_first("SELECT founderuid FROM ".DB::table('forum_forumfield')." WHERE fid='$fid' LIMIT 1");
					notification_add($groupfounderuid, 'system', 'grouplevel_update', array(
						'groupname' => '<a href="forum.php?mod=group&fid='.$fid.'">'.$forum['name'].'</a>',
						'newlevel' => $_G['grouplevels'][$levelid]['leveltitle']
					));
				}
			}
		}
		dsetcookie('groupcredit_'.$fid, $today, 86400);
	}
}