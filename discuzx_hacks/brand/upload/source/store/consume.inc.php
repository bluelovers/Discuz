<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: consume.inc.php 4406 2010-09-13 07:48:43Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

if(empty($_GET['xid'])) {

	$tpp = $_G['setting']['consumeperpage'];

	//消費卷列表
	$_BCACHE->cachesql('consumelist', 'SELECT i.itemid FROM '.tname('consumeitems')." i WHERE i.shopid='$shop[itemid]' AND i.grade=3 ORDER BY i.displayorder_s ASC, i.itemid DESC", 0, 1, $tpp, 0, 'storelist', 'consume', $_GET['id']);
	$consumelist_multipage = $_SBLOCK['consumelist_multipage'];
	foreach($_SBLOCK['consumelist'] as $result) {
		$result = $_BCACHE->getiteminfo('consume', $result['itemid'], $_GET['id']);
		$consumelist[] = $result;
	}
	$theurl = "store.php?id=$shop[itemid]&action=consume";
	$seo_description = strip_tags($consume['message']);

} else {
	$consume = $_BCACHE->getiteminfo('consume', $_GET['xid'], $_GET['id']);
	$consume['message'] = bbcode2html($consume['message']);
	if(!$consume) {
		showmessage('not_found_msg', 'index.php');
	}
	$allowreply = ($shop['allowreply'] && $consume['allowreply']) ? 1 : 0;
	if($_GET['do']=='print') {
		DB::query('UPDATE '.tname('consumeitems').' SET downnum=downnum+1 WHERE itemid=\''.$_GET['xid'].'\'');
		echo '<body onload="window.print()"><img src="'.$consume['subjectimage'].'"></body>';
		exit();

	}
	//更新統計數
	$isupdate = freshcookie($action,$consume['itemid']);
	if($isupdate || !$_G['setting']['updateview']) updateviewnum($action,$consume['itemid']);

	$consume['time'] = date('Y-m-d H:i', $consume['dateline']);
	$consume['starttime'] = date('Y-m-d', $consume['validity_start']);
	$consume['endtime'] = date('Y-m-d', $consume['validity_end']);
	//評論
	$listcount = $consume['replynum'];
	$_G['setting']['viewspace_pernum'] = intval($_G['setting']['viewspace_pernum']);
	$type = 'consume';
	if($_G['setting']['urltype']==3) {
		$consumeurl = B_URL . "/store-{$shop['itemid']}-consume-{$consume['itemid']}.html";
	} else {
		$consumeurl = B_URL . "/store.php?id={$shop['itemid']}&action=consume&xid={$consume['itemid']}";
	}
	$seo_title = $consume['subject'] . ' - ' . $seo_title;
	$seo_description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($consume['message'])), 200));
}
?>