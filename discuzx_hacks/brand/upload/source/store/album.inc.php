<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: album.inc.php 4267 2010-08-27 08:24:06Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$tpp = 16;

if(empty($_GET['xid'])) {
	//相冊列表
	$_BCACHE->cachesql('albumlist', 'SELECT i.itemid FROM '.tname('albumitems')." i WHERE i.shopid='$shop[itemid]' AND i.grade>2 ORDER BY i.displayorder_s ASC, i.itemid DESC", 0, 1, $tpp, 0, 'storelist', 'album', $shop['itemid']);
	$album_multipage = $_SBLOCK['albumlist_multipage'];
	foreach($_SBLOCK['albumlist'] as $result) {
		$result = $_BCACHE->getiteminfo('album', $result['itemid'], $shop['itemid']);
		$result['url'] = 'store.php?id='.$result['shopid'].'&action=album&xid='.$result['itemid'];
		$albumlist[] = $result;
	}
	$seo_title = $lang['albumscan'] . ' - ' . $seo_title;
} else {
	//驗證相冊存在
	$album = $_BCACHE->getiteminfo('album', $_GET['xid'], $shop['itemid']);
	if(!$album) {
		showmessage('not_found_msg', 'index.php');
	}
	//圖片列表
	$_BCACHE->cachesql('photolist', 'SELECT i.itemid FROM '.tname('photoitems')." i WHERE i.albumid='$_GET[xid]' AND i.shopid='$shop[itemid]' AND i.grade=3 ORDER BY i.displayorder_s ASC, i.itemid DESC", 0, 0, 100, 0, 'storelist', 'photo', $shop['itemid'], $_GET['xid']);
	foreach($_SBLOCK['photolist'] as $result) {
		$result = $_BCACHE->getiteminfo('photo', $result['itemid'], $shop['itemid']);
		$photolist[] = $result;
	}
	$seo_title = $album['subject'] . ' - ' . $seo_title;
}
?>