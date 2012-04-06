<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_sendmail.php 25626 2011-11-16 08:37:30Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pernum = 1;

dsetcookie('sendmail', '1', 300);
$lockfile = DISCUZ_ROOT.'./data/sendmail.lock';
@$filemtime = filemtime($lockfile);

if($_G['timestamp'] - $filemtime < 5) exit();

touch($lockfile);

@set_time_limit(0);

$list = $sublist = $cids = $touids = array();
$query = DB::query("SELECT * FROM ".DB::table('common_mailcron')." WHERE sendtime<='$_G[timestamp]' ORDER BY sendtime LIMIT 0,$pernum");
while ($value = DB::fetch($query)) {
	if($value['touid']) $touids[$value['touid']] = $value['touid'];
	$cids[] = $value['cid'];
	$list[$value['cid']] = $value;
}

if(empty($cids)) exit();

$query = DB::query("SELECT * FROM ".DB::table('common_mailqueue')." WHERE cid IN (".dimplode($cids).")");
while ($value = DB::fetch($query)) {
	$sublist[$value['cid']][] = $value;
}

if($touids) {
	DB::query("UPDATE ".DB::table('common_member_status')." SET lastsendmail='$_G[timestamp]' WHERE uid IN (".dimplode($touids).")");
}

DB::query("DELETE FROM ".DB::table('common_mailcron')." WHERE cid IN (".dimplode($cids).")");
DB::query("DELETE FROM ".DB::table('common_mailqueue')." WHERE cid IN (".dimplode($cids).")");

require_once libfile('function/mail');

foreach ($list as $cid => $value) {
	$mlist = $sublist[$cid];
	if($value['email'] && $mlist) {
		$subject = getstr($mlist[0]['subject'], 80, 0, 0, 0, -1);
		$message = '';
		if(count($mlist) == 1) {
			$message = '<br>'.$mlist[0]['message'];
		} else {
			foreach ($mlist as $subvalue) {
				if($subvalue['message']) {
					$message .= "<br><strong>$subvalue[subject]</strong><br>$subvalue[message]<br>";
				} else {
					$message .= $subvalue['subject'].'<br>';
				}
			}
		}
		if(!sendmail($value['email'], $subject, $message)) {
			runlog('sendmail', "$value[email] sendmail failed.");
		}
	}
}

?>