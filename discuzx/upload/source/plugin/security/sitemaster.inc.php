<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: sitemaster.inc.php 27071 2012-01-04 05:56:10Z songlixin $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_G['adminid'] <= 0) {
	exit('Access Denied');
}

if ($_G['gp_formhash'] != formhash()) {
	exit('Access Denied');
}

require_once libfile('function/sec');
require_once libfile('class/sec');

$typeArray = array('1' => 'post', '2' => 'member');

$sec = Sec::getInstance();
$operateType = 'member';
$operateData = getOperateData($operateType);
if (count($operateData) == 0) {
	$operateType = 'post';
	$operateData = getOperateData($operateType);
}
if (count($operateData) == 0 || !is_array($operateData)) {
    exit('clear');
}

$operateThreadData = array();
$operatePostData = array();

if ($operateType == 'post') {
	foreach ($operateData as $tempData) {
		if ($tempData['operateType'] == 'thread') {
			$operateThreadData[] = $tempData;
		} else {
			$operatePostData[] = $tempData;
		}
	}
	if (count($operateThreadData)) {
		$res = $sec->reportOperate('thread', $operateThreadData);
	} elseif(count($operatePostData)) {
		$res = $sec->reportOperate('post', $operatePostData);
	}
} elseif(count($operateData)) {
	$res = $sec->reportOperate($operateType, $operateData);
}
markasreported($operateType, $operateData);
?>