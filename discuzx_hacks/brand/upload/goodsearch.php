<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: goodsearch.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

define("CURSCRIPT", "goods");
include_once('./common.php');

$active['goods'] = ' class="active"';

$tagarrs = $goodlist = $where = $value = array();
$tagids = $joinsql = $wheresql = '';
$query = NULL;

//常見id處理
foreach(array('itemid', 'nid', 'uid', 'catid', 'tagid', 'shopid') as $value) {
	$_GET[$value] = $_POST[$value] = intval(!empty($_POST[$value])?$_POST[$value]:$_GET[$value]);
}
$_GET['keyword'] = trim(addslashes(rawurldecode($_REQUEST['keyword'])));

$catid = empty($_GET['catid']) ? 0 : $_GET['catid'];

$categorylist = getmodelcategory('good'); //讀商品分類

include_once('./batch.attribute.php');
$searchcats = getsearchcats($categorylist, $catid);

$tagid[] = $catid;
if(is_array($searchcats)) {
	foreach($searchcats as $key=>$value) {
		$tagid[] = $value['catid'];
	}

}
$tagids = implode(',', $tagid);//搜索分類id拼合

//屬性篩選器
$attrvalues = empty($_GET['params'])?array():getattrvalues($_GET['params']);
if($catid && $categorylist[$catid]['havechild'] == 0) {
	$attform = formatattrs($catid, $attrvalues, $_GET['keyword']);
}
//屬性搜索
$attr_in = getattr_in($catid,$attrvalues);

//條件拼合
$attr_in!==NULL && $where[] = $attr_in;
$where[] = 'i.grade_s>2 AND i.grade>2';
$_GET['keyword'] && $where[] = 'i.subject LIKE \'%'.$_GET['keyword'].'%\'';
if($catid>0 && $tagids) {
	$where[] = 'i.catid IN ('.$tagids.')';
} else {
	$where[] = 'i.catid>0';
}
$wheresql = implode(' AND ', $where);

//分頁處理
$tpp = $_G['setting']['goodsearchperpage'];

//查詢分類結果
if(!($catid && $categorylist[$catid]['havechild'] == 0)) {
	$_BCACHE->cachesql('catnums', 'SELECT COUNT(i.itemid) as count, i.catid FROM '.tname('gooditems').' i WHERE '.$wheresql.' GROUP BY i.catid', 0, 0, 100, 0, 'sitelist', 'good');
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

//數據查詢，拆分SQL，分片緩存
$_BCACHE->cachesql('goodsearch', 'SELECT i.itemid, i.shopid FROM '.tname('gooditems').' i WHERE '.$wheresql.' ORDER BY i.displayorder ASC, i.itemid DESC', 0, 1, $tpp, 0, 'sitelist', 'good');
$multipage = $_SBLOCK['goodsearch_multipage'];
$resultcount = $_SBLOCK['goodsearch_listcount'];
foreach($_SBLOCK['goodsearch'] as $value) {
	$value = $_BCACHE->getiteminfo('good', $value['itemid'], $value['shopid']);
	$value['shopinfo'] = $_BCACHE->getshopinfo($value['shopid']);
	$value['intro'] = cutstr($value['intro'], 130, true);
	$goodlist[] = $value;
}

$active['cat'] = ' class="active"';
$location['name'] = (empty($_GET['keyword'])?'' : $_GET['keyword'].' - ') . $_G['setting']['site_nav']['goods']['name'];

$seo_title = ($catid == 0 ? "" : $categorylist[$catid]['name'] . " - ") . $location['name'] . " - " . $seo_title;
include template('templates/site/default/goodsearch.html.php', 1);

ob_out();

?>