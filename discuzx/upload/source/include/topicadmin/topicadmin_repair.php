<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_repair.php 20522 2011-02-25 04:03:05Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowrepairthread']) {
	showmessage('no_privilege_repairthread');
}

$posttable = getposttablebytid($_G['tid']);

$replies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'") - 1;

$attachcount = DB::result_first("SELECT count(*) FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE tid='$_G[tid]'");
$attachment = $attachcount ? (DB::result_first("SELECT COUNT(*) FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE tid='$_G[tid]' AND isimage != 0") ? 2 : 1) : 0;

$firstpost  = DB::fetch_first("SELECT pid, subject, rate FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline LIMIT 1");
$firstpost['subject'] = addslashes(cutstr($firstpost['subject'], 79));
@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);

$lastpost  = DB::fetch_first("SELECT author, dateline FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline DESC LIMIT 1");

DB::query("UPDATE ".DB::table('forum_thread')." SET subject='$firstpost[subject]', replies='$replies', lastpost='$lastpost[dateline]', lastposter='".addslashes($lastpost['author'])."', rate='$firstpost[rate]', attachment='$attachment' WHERE tid='$_G[tid]'", 'UNBUFFERED');
DB::query("UPDATE ".DB::table($posttable)." SET first='1', subject='$firstpost[subject]' WHERE pid='$firstpost[pid]'", 'UNBUFFERED');
DB::query("UPDATE ".DB::table($posttable)." SET first='0' WHERE tid='$_G[tid]' AND pid<>'$firstpost[pid]'", 'UNBUFFERED');

showmessage('admin_repair_succeed', '', array(), array('alert' => 'right'));

?>