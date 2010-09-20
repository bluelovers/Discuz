<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_topic.php 16820 2010-09-15 06:29:30Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['diy']=='yes' && !$_G['group']['allowaddtopic'] && !$_G['group']['allowmanagetopic']) {
	$_GET['diy'] = '';
	showmessage('topic_edit_nopermission');
}

$topicid = $_GET['topicid'] ? intval($_GET['topicid']) : 0;

if($topicid) {
	$where = "topicid = '$topicid' $where";
} elseif($_GET['topic']) {
	$where = "name = '$_GET[topic]' $where";
} else {
	$where = '0';
}
$topic = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE $where");

if(empty($topic)) {
	showmessage('topic_not_exist');
}

if($topic['closed'] && !$_G['group']['allowmanagetopic'] && !($topic['uid'] == $_G['uid'] && $_G['group']['allowaddtopic'])) {
	showmessage('topic_is_closed');
}

if($_GET['diy'] == 'yes' && $topic['uid'] != $_G['uid'] && !$_G['group']['allowmanagetopic']) {
	$_GET['diy'] = '';
	showmessage('topic_edit_nopermission');
}

$topicid = intval($topic['topicid']);

DB::query("UPDATE ".DB::table('portal_topic')." SET viewnum=viewnum+1 WHERE topicid='$topicid'");

$navtitle = $topic['title'];
$metadescription = empty($topic['summary']) ? $topic['title'] : $topic['summary'];
$metakeywords =  empty($topic['keyword']) ? $topic['title'] : $topic['keyword'];

$file = 'portal/portal_topic_content:'.$topicid;
include template('diy:'.$file, NULL, NULL, NULL, $topic['primaltplname']);

?>