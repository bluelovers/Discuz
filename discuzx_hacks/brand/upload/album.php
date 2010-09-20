<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: album.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

define("CURSCRIPT", "album");
include_once('./common.php');

$active['album'] = ' class="active"';

$tagarrs = $albumlist = $where = $value = array();
$tagids = $joinsql = $wheresql = '';
$query = NULL;
$resultcount = 0;
$action = trim($_GET['action']);

foreach(array('itemid', 'nid', 'uid', 'catid', 'tagid', 'shopid') as $value) {
	$_GET[$value] = $_POST[$value] = intval(!empty($_POST[$value])?$_POST[$value]:$_GET[$value]);
}

//Ajax请求的单一商家相册
if($action == 'getalbumlist') {

	$shopid = $_GET['shopid'];
	if(!empty($shopid)) {
		$albumlist = array();
		$sql = 'SELECT i.itemid, i.shopid FROM '.tname('albumitems')." i WHERE i.shopid='$shopid' AND i.grade_s>2 AND i.grade>2 ORDER BY i.displayorder_s ASC, i.itemid DESC";
		$_BCACHE->cachesql('albumitems', $sql, 0, 0, 100, 0, 'storelist', 'album', $shopid, 0);
		foreach($_SBLOCK['albumitems'] as $result) {
			$result = $_BCACHE->getiteminfo('album', $result['itemid'], $result['shopid']);
			$albumlist[] = $result;
		}
		$shopinfo = $_BCACHE->getshopinfo($shopid);
		include template('templates/site/default/shopalbum.html.php', 1);
	}
	ob_out();
	exit;
}

$_GET['keyword'] = trim(addslashes(rawurldecode($_REQUEST['keyword'])));
$catid = empty($_GET['catid']) ? 0 : $_GET['catid'];

$categorylist = getmodelcategory('album');

include_once('./batch.attribute.php');

$searchcats = getsearchcats($categorylist, $catid);
$tagid[] = $catid;
if(is_array($searchcats)) {
	foreach($searchcats as $key=>$value) {
		$tagid[] = $value['catid'];
	}

}
$tagids = implode(",", $tagid);

//属性筛选器
$attrvalues = empty($_GET['params']) ? array() : getattrvalues($_GET['params']);
if($catid && $categorylist[$catid]['havechild'] == 0) {
	$attform = formatattrs($catid, $attrvalues, $_GET['keyword'], 'album.php');
}

//属性搜索
$attr_in = getattr_in($attrvalues);

//条件拼合
$attr_in!==NULL && $where[] = $attr_in;
$where[] = 'i.grade_s>2 AND i.grade>2';
$_GET['keyword'] && $where[] = 'i.subject LIKE \'%'.$_GET['keyword'].'%\'';
if($catid>0 && $tagids) {
	$where[] = 'i.catid IN ('.$tagids.')';
} else {
	$where[] = 'i.catid>0';
}
$wheresql = implode(' AND ', $where);

//分页处理
$tpp = $_G['setting']['albumsearchperpage'];

//查询分类结果
if(!($catid && $categorylist[$catid]['havechild'] == 0)) {
	$_BCACHE->cachesql('catnums', 'SELECT COUNT(i.itemid) as count, i.catid FROM '.tname('albumitems').' i WHERE '.$wheresql.' GROUP BY i.catid', 0, 0, 100, 0, 'sitelist', 'album');
	foreach($_SBLOCK['catnums'] as $value) {
		$catnums[$value['catid']]=$value['count'];
	}
	if(is_array($searchcats)) {
		foreach($searchcats as $key=>$value) {
			$catnums = getcatcount($value['catid'], $catnums);
		}
		$catsarr = array();
		foreach($searchcats as $cat) {
			if($cat['upid'] == $catid && $catnums[$cat['catid']]) {
				$catsarr[] = $cat;
			}
		}
	}
}

//数据查询，拆分SQL，分片缓存
$_BCACHE->cachesql('albumsearch', 'SELECT i.itemid, i.shopid FROM '.tname('albumitems').' i WHERE '.$wheresql.' ORDER BY i.displayorder ASC, i.itemid DESC', 0, 1, $tpp, 0, 'sitelist', 'album');
$multipage = $_SBLOCK['albumsearch_multipage'];
$resultcount = $_SBLOCK['albumsearch_listcount'];
foreach($_SBLOCK['albumsearch'] as $value) {
	$value = $_BCACHE->getiteminfo('album', $value['itemid'], $value['shopid']);
	$value['shopinfo'] = $_BCACHE->getshopinfo($value['shopid']);
	$albumlist[] = $value;
}
$active['cat'] = ' class="active"';
$location['name'] = (empty($_GET['keyword'])?'' : $_GET['keyword'].' - ') . $_G['setting']['site_nav']['album']['name'];

$seo_title = ($catid == 0 ? "" : $_G['categorylist'][$catid]['name'] . " - ") . $location['name'] . " - " . $seo_title;
include template('templates/site/default/album.html.php', 1);

ob_out();

?>