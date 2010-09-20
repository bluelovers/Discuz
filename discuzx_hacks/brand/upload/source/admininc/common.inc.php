<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: common.inc.php 4359 2010-09-07 07:58:57Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Acess Denied');
}

//變量初始化
$query = $key = $value = NULL;
$wheresql = $querystr = $setsql = $showsidemenu = $showtopmenu = $mname = '';
$mid = 0;
$setsqlarr = $cacheinfo = $mlist = $categorylist = array();

$_G['uid'] = intval($_G['uid']);

//沒有登錄
if(empty($_G['uid'])) {
	setcookie('_refer', $_SERVER['SCRIPT_NAME'].'?action='.$_GET['action'].'&m='.$GET['m']);
	showmessage('admin_login', $b_url);
}
/*
//常見id處理
foreach(array('itemid', 'nid', 'uid', 'catid', 'shopid', 'albumid', 'groupid', 'upid', 'displayorder', 'cmid') as $value) {
	//$_GET[$value] = $_POST[$value] = $_REQUEST[$value] = intval(!empty($_POST[$value])?$_POST[$value]:(!empty($_GET[$value])?$_GET[$value]:0));
}
*/
//載入語言包
include_once (B_ROOT.'./language/admin.lang.php');
$lang = array_merge($lang, $alang);

//記錄log
$extralog = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formhash', 'submit', 'action'));
writelog(substr($BASESCRIPT, 0, -4).'log', implode("\t", clearlogstring(array($_G['timestamp'], $_G['username'], $_G['clientip'], $_REQUEST['action'], $extralog))));

if(!in_array($_GET['m'], $models)) {
	$_GET['m'] = 'shop';
}

//讀入緩存
if(!in_array($_GET['m'], array('album', 'photo', 'brandlinks'))) {
	$cacheinfo = getmodelinfoall('modelname', $_GET['m']);
	$mname = $cacheinfo['models']['modelname'];
	$mid = $cacheinfo['models']['mid'];
	$categorylist = $_G['categorylist'];
} else {
	$mname =$_GET['m'] ;
}

//審核等級對應關係
$_SGLOBAL['shopgrade'] = array(
	3 => $lang['grade_3'],
	//4 => $lang['grade_4'],
	1 => $lang['grade_1'],
	2 => $lang['grade_2'],
	0 => $lang['grade_0'],
	5 => $lang['grade_5'],
);

if($_GET['m']!='shop') {
	unset($_SGLOBAL['shopgrade'][4]);
	$_SGLOBAL['shopgrade'][3] = $lang['grade_3_other'];
	if(pkperm('isadmin')) {
		$_SGLOBAL['shopgrade'][5] = $lang['grade_5'];
	} elseif (($_G['myshopstatus'] == 'verified') && !$_SGLOBAL['panelinfo']['group']['verify'.$mname]) {
		$_SGLOBAL['shopgrade'][5] = $lang['grade_5'];
	}
}

?>