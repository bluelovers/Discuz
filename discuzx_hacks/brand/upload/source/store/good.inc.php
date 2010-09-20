<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: good.inc.php 4360 2010-09-07 08:03:59Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

if(empty($_GET['xid'])) {
	$tpp = $_G['setting']['goodperpage'];
	//商品列表
	$_BCACHE->cachesql('goodlist', 'SELECT i.itemid FROM '.tname('gooditems')." i WHERE i.shopid='$shop[itemid]' AND i.grade>2 ORDER BY i.displayorder_s ASC, i.itemid DESC", 0, 1, $tpp, 0, 'storelist', 'good', $shop['itemid']);
	$goodlist_multipage = $_SBLOCK['goodlist_multipage'];
	$resultcount = $_SBLOCK['goodlist_listcount'];
	foreach($_SBLOCK['goodlist'] as $result) {
		$result = $_BCACHE->getiteminfo('good', $result['itemid'], $shop['itemid']);
		$result['time'] = date('Y-m-d', $result['dateline']);
		$result['thumb'] = str_replace('static/image/nophoto.gif', 'static/image/noimg.gif', $result['thumb']);
		$result['message'] = trim(strip_tags($result['message']));
		$result['intro'] = cutstr($result['intro'], 130, true);
		$goodlist[] = $result;
	}
	$seo_title = $lang['goodlist'] . ' - ' . $seo_title;
	
	$theurl = "store.php?id=$shop[itemid]&action=good";
} else {
	//商品詳情
	$good = $_BCACHE->getiteminfo('good', $_GET['xid'], $_GET['id']);
	$good['message'] = bbcode2html($good['message']);
	if(!$good) {
		showmessage('not_found_msg', 'index.php');
	}
	$allowreply = ($shop['allowreply'] && $good['allowreply']) ? 1 : 0;
	$good['time'] = date('Y-m-d H:i', $good['dateline']);
	$relatedarr = array();
	$relatedarr = getrelatedinfo('good', $good['itemid'], $shop['itemid']);
	//更新統計數
	$isupdate = freshcookie($action,$good['itemid']);
	if($isupdate || !$_G['setting']['updateview']) updateviewnum($action,$good['itemid']);
	//評論
	$listcount = $good['replynum'];
	$_G['setting']['viewspace_pernum'] = intval($_G['setting']['viewspace_pernum']);
	$type = 'good';
	$seo_title = $good['subject'] . ' - ' . $seo_title;
	$seo_description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($good['message'])), 200));

}
?>