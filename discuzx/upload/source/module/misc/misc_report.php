<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_report.php 22830 2011-05-25 01:38:56Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(empty($_G['uid'])) {
	showmessage('not_loggedin', null, array(), array('login' => 1));
}
$rtype = $_G['gp_rtype'];
$rid = intval($_G['gp_rid']);
$tid = intval($_G['gp_tid']);
$fid = intval($_G['gp_fid']);
$uid = intval($_G['gp_uid']);
$default_url = array(
	'user' => 'home.php?mod=space&uid=',
	'post' => 'forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid=',
	'thread' => 'forum.php?mod=viewthread&tid=',
	'group' => 'forum.php?mod=group&fid=',
	'album' => 'home.php?mod=space&do=album&uid='.$uid.'&id=',
	'blog' => 'home.php?mod=space&do=blog&uid='.$uid.'&id=',
	'pic' => 'home.php?mod=space&do=album&uid='.$uid.'&picid='
);
$url = '';
if($rid && !empty($default_url[$rtype])) {
	$url = $default_url[$rtype].intval($rid);
} else {
	$url = addslashes(dhtmlspecialchars(base64_decode($_G['gp_url'])));
	$url = preg_match("/^http[s]?:\/\/[^\[\"']+$/i", trim($url)) ? trim($url) : '';
}
if(empty($url) || empty($_G['inajax'])) {
	showmessage('report_parameters_invalid');
}
$urlkey = md5($url);
if(submitcheck('reportsubmit')) {
	$message = censor(cutstr(dhtmlspecialchars(trim($_G['gp_message'])), 200, ''));
	$message = $_G['username'].'&nbsp;:&nbsp;'.rtrim($message, "\\");
	if($reportid = DB::result_first("SELECT id FROM ".DB::table('common_report')." WHERE urlkey='$urlkey' AND opuid='0'")) {
		DB::query("UPDATE ".DB::table('common_report')." SET message=CONCAT_WS('<br>', message, '$message'), num=num+1 WHERE id='$reportid'");
	} else {
		DB::query("INSERT INTO ".DB::table('common_report')."(url, urlkey, uid, username, message, dateline".($fid ? ', fid' : '').") VALUES ('$url', '$urlkey', '$_G[uid]', '$_G[username]', '$message', '".TIMESTAMP."'".($fid ? ", '$fid'" : '').")");
		$report_receive = unserialize($_G['setting']['report_receive']);
		$moderators = array();
		if($report_receive['adminuser']) {
			foreach($report_receive['adminuser'] as $touid) {
				notification_add($touid, 'report', 'new_report', array('from_id' => 1, 'from_idtype' => 'newreport'), 1);
			}
		}
		if($fid && $rtype == 'post') {
			$query = DB::query("SELECT uid FROM ".DB::table('forum_moderator')." WHERE fid='$fid'");
			while($row = DB::fetch($query)) {
				$moderators[] = $row['uid'];
			}
			if($report_receive['supmoderator']) {
				$moderators = array_unique(array_merge($moderators, $report_receive['supmoderator']));
			}
			foreach($moderators as $touid) {
				$touid != $_G['uid'] && !in_array($touid, $report_receive) && notification_add($touid, 'report', 'new_post_report', array('fid' => $fid, 'from_id' => 1, 'from_idtype' => 'newreport'), 1);
			}
		}
	}
	showmessage('report_succeed', '', array(), array('closetime' => true, 'showdialog' => 1));
}
require_once libfile('function/misc');
include template('common/report');
?>