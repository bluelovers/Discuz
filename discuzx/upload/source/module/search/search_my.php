<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_my.php 22878 2011-05-28 09:53:00Z zhouguoqiang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if (!$_G['setting']['my_siteid']) {
	dheader('Location: index.php');
}

require_once DISCUZ_ROOT . './api/manyou/Manyou.php';
require_once libfile('function/cloud');

if (getcloudappstatus('connect')) {
	require_once libfile('function/connect');
	connect_merge_member();
}

$my_forums = SearchHelper::getForums();

$my_extgroupids = array();
$_extgroupids = explode("\t", $_G['member']['extgroupids']);
foreach($_extgroupids as $v) {
	if ($v) {
		$my_extgroupids[] = $v;
	}
}
$my_extgroupids_str = implode(',', $my_extgroupids);

$params = array(
				'cuName' => $_G['username'],
				'gId' => $_G['groupid'],
				'agId' => $_G['adminid'],
				'egIds' => $my_extgroupids_str,
				'fmSign' => substr($my_forums['sign'], -4),
			   );

$groupIds = explode(',', $_G['groupid']);
if ($_G['adminid']) {
	$groupIds[] = $_G['adminid'];
}
if ($my_extgroupids) {
	$groupIds = array_merge($groupIds, $my_extgroupids);
}

$groupIds = array_unique($groupIds);
$userGroups = SearchHelper::getUserGroupPermissions($groupIds);
foreach($groupIds as $k => $v) {
	$value =  substr($userGroups[$v]['sign'], -4);
	if ($value) {
		$params['ugSign' . $v] = $value;
	}
}
$params['charset'] = $_G['charset'];
if ($_G['member']['conopenid']) {
	$params['openid'] = $_G['member']['conopenid'];
}

$extra = array('q', 'fId', 'author', 'scope', 'source', 'module', 'isAdv');
foreach($extra as $v) {
	if ($_GET[$v]) {
		$params[$v] = $_GET[$v];
	}
}
$mySearchData = unserialize($_G['setting']['my_search_data']);
if ($mySearchData['domain']) {
	$domain = $mySearchData['domain'];
} else {
	$domain = 'search.discuz.qq.com';
}

$url = 'http://' . $domain . '/f/discuz?' . generateSiteSignUrl($params, true, true);

dheader('Location: ' . $url);

?>