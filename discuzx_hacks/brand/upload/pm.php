<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: pm.php 4407 2010-09-13 08:17:51Z fanshengshuai $
 */

require_once('./common.php');

$send_result = '';
$act = $_GET['act'];
$filter = $_GET['filter'];

// 跳轉到查看短信得頁面
if(!empty($filter) && in_array($filter,array('systempm','privatepm','announcepm'))) {
	$pm->view_pm($filter);
	exit;
}

if($act == 'list') {
	@include_once B_ROOT.'./uc_client/client.php';
	$page = intval($_GET['page']);
	if($page == 0) $page = 1;
	$pm_notices = uc_pm_list($_G['uid'], $page , 5, '' , 'systempm', 0);
	$url = 'pm.php?act=list&msgtype=systempm';
	if($_GET['inajax'] == 1) {
		$url .= '&inajax=1';
	}
	$multi = multi($pm_notices['count'], 5, $page, $url);
} elseif ($act == 'view') {
	@include_once B_ROOT.'./uc_client/client.php';
	$pm = uc_pm_viewnode($_G['uid'], 'systempm' , intval($_GET['pmid']));
	uc_pm_readstatus($_G['uid'],null,intval($_GET['pmid']),0);
	
	if(!empty($_SERVER['HTTP_REFERER'])) {
		$return_url = $_SERVER['HTTP_REFERER'];
	} else {
		$return_url = 'pm.php?act=list&msgtype=systempm';
		if($_GET['inajax'] == 1) {
			$return_url .= '&inajax=1';
		}
	}
	$url = 'pm.php?act=view&msgtype=systempm';
	if($_GET['inajax'] == 1) {
		$url .= '&inajax=1';
	}
} else if ($act == 'del') {
	if(intval($_GET['pmid']) != 0) {
		$pmids = array($_GET['pmid']);
		@include_once B_ROOT.'./uc_client/client.php';
		$num = uc_pm_delete($_G['uid'], 'inbox',  $pmids);
		header("location: ".$_SERVER['HTTP_REFERER']);
	}

} else {
	$msgto = intval($_REQUEST['msgto']);
	if(intval($_G['uid']) == $msgto) {
		// 不能給自己發消息
		$send_result = 'notallowtomyself';
	} elseif($_G['uid'] < 1) {
		// 沒有 LOGIN
		$send_result = 'notlogin';
		// 發送窗口
	} elseif($act == 'sendbox') {
		if(empty($send_result)) {
			$user = DB::fetch(DB::query('SELECT uid,username FROM '.tname('members')." WHERE uid='$msgto'"));
			$uid = $user['uid'];
			$username = $user['username'];
		}
		// 發送操作
	} elseif($act == 'send') {
		if(submitcheck('pmsubmit')) {
			$subject = $_POST['subject'];
			$message = $_POST['message'];
			@include_once B_ROOT.'./uc_client/client.php';
			$send_result = uc_pm_send($_G['uid'], $msgto, $subject, $message);
		} else {
			showmessage('FORMHASH ERROR', 'index.php');
		}
	}
}
include template('templates/site/default/pm.html.php', 1);

?>