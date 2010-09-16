<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_medal_daily.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$medalnewarray = array();

$query = DB::query("SELECT me.id, me.uid, me.medalid, me.expiration, mf.medals
					FROM ".DB::table('forum_medallog')." me
					LEFT JOIN ".DB::table('common_member_field_forum')." mf USING (uid)
					WHERE me.status=1 AND me.expiration<".TIMESTAMP);

while($medalnew = DB::fetch($query)) {
	$medalsnew = array();
	$medalnew['medals'] = empty($medalnewarray[$medalnew['uid']]) ? explode("\t", $medalnew['medals']) : explode("\t", $medalnewarray[$medalnew['uid']]);

	foreach($medalnew['medals'] as $key => $medalnewid) {
		list($medalid, $medalexpiration) = explode("|", $medalnewid);
		if($medalnew['medalid'] == $medalid) {
			unset($medalnew['medals'][$key]);
		}
	}

	$medalnewarray[$medalnew['uid']] = implode("\t", $medalnew['medals']);
	DB::query("UPDATE ".DB::table('forum_medallog')." SET status='0' WHERE id='".$medalnew['id']."'");
	DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='".$medalnewarray[$medalnew['uid']]."' WHERE uid='".$medalnew['uid']."'");
}
?>