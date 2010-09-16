<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_report.php 16152 2010-09-01 02:57:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(empty($_G['uid'])) {
	showmessage('not_loggedin', null, array(), array('login' => 1));
}
$rtype = $_G['gp_rtype'];
$rid = $_G['gp_rid'];
$default_url = array(
	'user' => 'home.php?mod=space&uid=',
	'post' => 'forum.php?mod=redirect&goto=findpost&pid=',
	'thread' => 'forum.php?mod=viewthread&tid=',
	'group' => 'forum.php?mod=group&fid=',
	'album' => 'home.php?mod=space&do=album&id=',
	'blog' => 'home.php?mod=space&do=blog&id=',
	'pic' => 'home.php?mod=space&do=album&picid='
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
		DB::query("INSERT INTO ".DB::table('common_report')."(url, urlkey, uid, username, message, dateline) VALUES ('$url', '$urlkey', '$_G[uid]', '$_G[username]', '$message', '".TIMESTAMP."')");
		if($_G['setting']['report_receive']) {
			$report_receive = explode(',', $_G['setting']['report_receive']);
			foreach($report_receive as $touid) {
				notification_add($touid, 'report', 'new_report', array('from_id' => 1, 'from_idtype' => 'newreport'), 1);
			}
		}
	}
	showmessage('report_succeed', '', array(), array('closetime' => true, 'showdialog' => 1));
}
require_once libfile('function/misc');
include template('common/report');
?>