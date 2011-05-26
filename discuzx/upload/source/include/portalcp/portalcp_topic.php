<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_topic.php 20732 2011-03-02 08:33:43Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$allowmanage = $allowadd = 0;
if($_G['group']['allowaddtopic'] || $_G['group']['allowmanagetopic']) {
	$allowadd = 1;
}

$op = in_array($_GET['op'], array('edit')) ? $_GET['op'] : 'add';

$topicid = $_GET['topicid'] ? intval($_GET['topicid']) : 0;
$topic = '';
if($topicid) {
	$topic = DB::fetch_first('SELECT * FROM '.DB::table('portal_topic')." WHERE topicid = '$topicid'");
	if(empty($topic)) {
		showmessage('topic_not_exist');
	}
	if($_G['group']['allowmanagetopic'] || ($_G['group']['allowaddtopic'] && $topic['uid'] == $_G['uid'])) {
		$allowmanage = 1;
	}
	$coverpath = $topic['picflag'] == '0' ? $topic['cover'] : '';

	if($topic['cover']) {
		if($topic['picflag'] == '1') {
			$topic['cover'] = $_G['setting']['attachurl'].$topic['cover'];
		} elseif ($topic['picflag'] == '2') {
			$topic['cover'] = $_G['setting']['ftp']['attachurl'].$topic['cover'];
		}
	}
}

if(($topicid && !$allowmanage) || (!$topicid && !$allowadd)) {
	showmessage('topic_edit_nopermission', dreferer());
}

$tpls = array();

if (($dh = opendir(DISCUZ_ROOT.'./template/default/portal'))) {
	while(($file = readdir($dh)) !== false) {
		$file = strtolower($file);
		if (fileext($file) == 'htm' && substr($file, 0, 13) == 'portal_topic_') {
			$tpls[str_replace('.htm','',$file)] = $file;
		}
	}
	closedir($dh);
}

if (empty($tpls)) showmessage('topic_has_on_template', dreferer());

if(submitcheck('editsubmit')) {
	include_once libfile('function/portalcp');
	if(is_numeric($topicid = updatetopic($topic))){
		showmessage('do_success', 'portal.php?mod=topic&topicid='.$topicid);
	} else {
		showmessage($topicid, dreferer());
	}
}

include_once template("portal/portalcp_topic");


?>