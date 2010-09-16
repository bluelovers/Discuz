<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_activate.php 13866 2010-08-02 07:17:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if($_G['gp_uid'] && $_G['gp_id']) {

	$query = DB::query("SELECT m.uid, m.username, m.credits, mf.authstr FROM ".DB::table('common_member')." m, ".DB::table('common_member_field_forum')." mf
		WHERE m.uid='$_G[gp_uid]' AND mf.uid=m.uid AND m.groupid='8'");

	$member = DB::fetch($query);
	list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);

	if($operation == 2 && $idstring == $_G['gp_id']) {
		$query = DB::query("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND '$member[credits]'>=creditshigher AND '$member[credits]'<creditslower LIMIT 1");
		DB::query("UPDATE ".DB::table('common_member')." SET groupid='".DB::result($query, 0)."', emailstatus='1' WHERE uid='$member[uid]'");
		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET authstr='' WHERE uid='$member[uid]'");
		showmessage('activate_succeed', 'index.php', array('username' => $member['username']));
	} else {
		showmessage('activate_illegal', 'index.php');
	}

}
?>