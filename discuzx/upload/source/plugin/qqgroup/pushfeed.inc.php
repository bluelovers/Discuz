<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: pushfeed.inc.php 28558 2012-03-05 02:59:09Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$service = Cloud::loadClass('Service_QQGroup');
$tid = intval($_GET['tid']);
$title = trim($_GET['title']);
$content = trim($_GET['content']);
if (!$tid) {
	showmessage('undefined_action');
}
$iframeUrl = $service->iframeUrl($tid, $title, $content);
include template('qqgroup:pushfeed');