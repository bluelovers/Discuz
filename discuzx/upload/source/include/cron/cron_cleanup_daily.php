<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanup_daily.php 15286 2010-08-23 02:24:18Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE endtime>'0' AND endtime<='$_G[timestamp]'", 'UNBUFFERED');
if(DB::affected_rows()) {
	require_once libfile('function/cache');
	updatecache(array('setting', 'advs'));
}
DB::query("TRUNCATE ".DB::table('common_searchindex')."");
DB::query("DELETE FROM ".DB::table('forum_threadmod')." WHERE tid>0 AND dateline<'$_G[timestamp]'-31536000", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('forum_forumrecommend')." WHERE expiration>0 AND expiration<'$_G[timestamp]'", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('forum_tradelog')." WHERE buyerid>0 AND status=0 AND lastupdate<'$_G[timestamp]'-432000", 'UNBUFFERED');
DB::query("UPDATE ".DB::table('forum_trade')." SET closed='1' WHERE expiration>0 AND expiration<'$_G[timestamp]'", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE uid>0 AND dateline<'$_G[timestamp]'-7776000", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('home_visitor')." WHERE uid>0 AND dateline<'$_G[timestamp]'-7776000", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('home_notification')." WHERE uid>0 AND new=0 AND dateline<'$_G[timestamp]'-172800", 'UNBUFFERED');

if($_G['setting']['cachethreadon']) {
	removedir($_G['setting']['cachethreaddir'], TRUE);
}

$delaids = array();
$query = DB::query("SELECT aid, attachment, thumb FROM ".DB::table('forum_attachment')." WHERE tid='0' AND dateline<'$_G[timestamp]'-86400");
while($attach = DB::fetch($query)) {
	dunlink($attach);
	$delaids[] = $attach['aid'];
}
if($delaids) {
	DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($delaids).")", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE aid IN (".dimplode($delaids).")", 'UNBUFFERED');
}

$uids = $members = array();
$query = DB::query("SELECT uid, groupid, credits FROM ".DB::table('common_member')." WHERE groupid IN ('4', '5') AND groupexpiry>'0' AND groupexpiry<'$_G[timestamp]'");
while($row = DB::fetch($query)) {
	$uids[] = $row['uid'];
	$members[$row['uid']] = $row;
}
if($uids) {
	$query = DB::query("SELECT uid, groupterms FROM ".DB::table('common_member_field_forum')." WHERE uid IN (".dimplode($uids).")");
	while($member = DB::fetch($query)) {
		$sql = 'uid=uid';
		$member['groupterms'] = unserialize($member['groupterms']);
		$member['groupid'] = $members[$member['uid']]['groupid'];
		$member['credits'] = $members[$member['uid']]['credits'];

		if(!empty($member['groupterms']['main']['groupid'])) {
			$groupidnew = $member['groupterms']['main']['groupid'];
			$adminidnew = $member['groupterms']['main']['adminid'];
			unset($member['groupterms']['main']);
			unset($member['groupterms']['ext'][$member['groupid']]);
			$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
		} else {
			$query = DB::query("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
			$groupidnew = DB::result($query, 0);
			$adminidnew = 0;
		}
		$sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
		DB::query("UPDATE ".DB::table('common_member')." SET $sql WHERE uid='$member[uid]'");
		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");
	}
}

include_once libfile('function/block');
block_clear();

function removedir($dirname, $keepdir = FALSE) {
	$dirname = wipespecial($dirname);

	if(!is_dir($dirname)) {
		return FALSE;
	}
	$handle = opendir($dirname);
	while(($file = readdir($handle)) !== FALSE) {
		if($file != '.' && $file != '..') {
			$dir = $dirname . DIRECTORY_SEPARATOR . $file;
			is_dir($dir) ? removedir($dir) : unlink($dir);
		}
	}
	closedir($handle);
	return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}

?>