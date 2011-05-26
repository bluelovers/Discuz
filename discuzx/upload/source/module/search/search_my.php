<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_my.php 22037 2011-04-20 08:34:44Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

function my_http_build_query ($data, $key = '', $isEncode = true) {
	$ret = array();
	foreach ($data as $k => $v) {
		if ($isEncode) {
			$k = urlencode($k);
		}

		if ($key) {
			$k = $key . "[" . $k . "]";
		}

		if (is_array($v)) {
			array_push($ret, my_http_build_query($v, $k, $isEncode));
		} else {
			if ($isEncode) {
				$v = urlencode($v);
			}
			array_push($ret, $k . "=" . $v);
		}
	}

	return join('&', $ret);
}

if (!$_G['setting']['my_siteid']) {
	dheader('Location: index.php');
}

require_once DISCUZ_ROOT . './api/manyou/Manyou.php';

$my_forums = SearchHelper::getForums();

$my_extgroupids = array();
$_extgroupids = explode("\t", $_G['member']['extgroupids']);
foreach($_extgroupids as $v) {
	if ($v) {
		$my_extgroupids[] = $v;
	}
}
$my_extgroupids_str = implode(',', $my_extgroupids);

$params = array('sId' => $_G['setting']['my_siteid'],
				'ts' => time(),
				'cuId' => $_G['uid'],
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

$params['sign'] = md5(implode('|', $params) . '|' . $_G['setting']['my_sitekey']);

$extra = array('q', 'fId', 'author', 'scope', 'source', 'module', 'isAdv');
foreach($extra as $v) {
	if ($_GET[$v]) {
		$params[$v] = $_GET[$v];
	}
}
$params['charset'] = $_G['charset'];
$mySearchData = unserialize($_G['setting']['my_search_data']);
if ($mySearchData['domain']) {
	$domain = $mySearchData['domain'];
} else {
	$domain = 'search.discuz.qq.com';
}
$url = 'http://' . $domain . '/f/discuz?' . my_http_build_query($params);

dheader('Location: ' . $url);

?>