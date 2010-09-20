<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_repair.php 16938 2010-09-17 04:37:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowrepairthread']) {
	showmessage('undefined_action', NULL);
}

$posttable = getposttablebytid($_G['tid']);

$replies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'") - 1;

$query = DB::query("SELECT a.aid FROM ".DB::table($posttable)." p, ".DB::table('forum_attachment')." a WHERE a.tid='$_G[tid]' AND a.pid=p.pid AND p.invisible='0' LIMIT 1");
$attachment = DB::num_rows($query) ? 1 : 0;

$firstpost  = DB::fetch_first("SELECT pid, subject, rate FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline LIMIT 1");
$firstpost['subject'] = addslashes(cutstr($firstpost['subject'], 79));
@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);

$lastpost  = DB::fetch_first("SELECT author, dateline FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline DESC LIMIT 1");

DB::query("UPDATE ".DB::table('forum_thread')." SET subject='$firstpost[subject]', replies='$replies', lastpost='$lastpost[dateline]', lastposter='".addslashes($lastpost['author'])."', rate='$firstpost[rate]', attachment='$attachment' WHERE tid='$_G[tid]'", 'UNBUFFERED');
DB::query("UPDATE ".DB::table($posttable)." SET first='1', subject='$firstpost[subject]' WHERE pid='$firstpost[pid]'", 'UNBUFFERED');
DB::query("UPDATE ".DB::table($posttable)." SET first='0' WHERE tid='$_G[tid]' AND pid<>'$firstpost[pid]'", 'UNBUFFERED');
showmessage('admin_repair_succeed');

?>