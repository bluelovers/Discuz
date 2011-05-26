<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_getip.php 20099 2011-02-15 01:55:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowviewip']) {
	showmessage('no_privilege_viewip');
}

$pid = $_G['gp_pid'];
$posttable = getposttablebytid($_G['tid']);
$member = DB::fetch_first("SELECT m.adminid, p.first, p.useip FROM ".DB::table($posttable)." p
			LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid
			WHERE p.pid='$pid' AND p.tid='$_G[tid]'");
if(!$member) {
	showmessage('thread_nonexistence', NULL);
} elseif(($member['adminid'] == 1 && $_G['adminid'] > 1) || ($member['adminid'] == 2 && $_G['adminid'] > 2)) {
	showmessage('admin_getip_nopermission', NULL);
}

$member['iplocation'] = convertip($member['useip']);

include template('forum/topicadmin_getip');

?>