<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notice.inc.php 4360 2010-09-07 08:03:59Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

if(empty($_GET['xid'])) {

	$tpp = $_G['setting']['noticeperpage'];

	// 公告列表
	$_BCACHE->cachesql('noticelist', 'SELECT i.itemid FROM '.tname('noticeitems')." i WHERE i.shopid = '$shop[itemid]' AND i.grade>2 ORDER BY i.displayorder_s ASC, i.itemid DESC", 0, 1, $tpp, 0, 'storelist', 'notice', $shop['itemid']);
	$noticelist_multipage = $_SBLOCK['noticelist_multipage'];
	$resultcount = $_SBLOCK['noticelist_listcount'];
	foreach($_SBLOCK['noticelist'] as $result) {
		$result = $_BCACHE->getiteminfo('notice', $result['itemid'], $shop['itemid']);
		$result['time'] = date('Y-m-d', $result['dateline']);
	    $noticelist[] = $result;
	}
	$seo_title = $lang['noticelistpage'] . ' - ' . $seo_title;
	$theurl = "store.php?id=$shop[itemid]&action=notice";

} else {

	//公告详情
	$notice = $_BCACHE->getiteminfo('notice', $_GET['xid'], $_GET['id']);
	$notice['message'] = bbcode2html($notice['message']);
	if(!$notice) {
		showmessage('not_found_msg', 'index.php');
	}
	$allowreply = ($shop['allowreply'] && $notice['allowreply']) ? 1 : 0;
	$notice['time'] = date('Y-m-d', $notice['dateline']);
	//更新统计数
	$isupdate = freshcookie($action,$notice['itemid']);
	if($isupdate || !$_G['setting']['updateview']) updateviewnum($action, $notice['itemid']);
	if(!empty($notice['jumpurl'])) {

		$notice['jumpurl'] = str_replace('&amp;', '&', $notice['jumpurl']);
		header("Location:$notice[jumpurl]");
		exit();
	}
	//评论
	$listcount = $notice['replynum'];
	$_G['setting']['viewspace_pernum'] = intval($_G['setting']['viewspace_pernum']);
	$type = 'notice';
	$seo_title = $notice['subject'] . ' - ' . $seo_title;
	$seo_description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($notice['message'])), 200));

}
?>