<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: groupbuy.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

define("CURSCRIPT", "groupbuy");
include_once('./common.php');

$active['groupbuy'] = ' class="active"';

$tagarrs = $groupbuylist = $where = $value = array();
$tagids = $joinsql = $wheresql = '';
$query = NULL;

//常見id處理
foreach(array('itemid', 'nid', 'uid', 'catid', 'tagid', 'shopid') as $value) {
	$_GET[$value] = $_POST[$value] = intval(!empty($_POST[$value])?$_POST[$value]:$_GET[$value]);
}
$_GET['keyword'] = trim(addslashes(rawurldecode($_REQUEST['keyword'])));


// 修改團購搜索
$catid = empty($_GET['catid']) ? 0 : $_GET['catid'];

$categorylist = getmodelcategory('groupbuy'); //讀團購分類

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
$attr_in = getattr_in($attrvalues);

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
$tpp = $_G['setting']['groupbuysearchperpage'];

//查詢分類結果
if(!($catid && $categorylist[$catid]['havechild'] == 0)) {
	$_BCACHE->cachesql('catnums', 'SELECT COUNT(i.itemid) as count, i.catid FROM '.tname('groupbuyitems').' i WHERE '.$wheresql.' GROUP BY i.catid', 0, 0, 100, 0, 'sitelist', 'groupbuy');
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
$_BCACHE->cachesql('groupbuysearch', 'SELECT i.itemid, i.shopid FROM '.tname('groupbuyitems').' i WHERE '.$wheresql.' ORDER BY i.displayorder ASC, i.itemid DESC', 0, 1, $tpp, 0, 'sitelist', 'groupbuy');
$multipage = $_SBLOCK['groupbuysearch_multipage'];
$resultcount = $_SBLOCK['groupbuysearch_listcount'];
foreach($_SBLOCK['groupbuysearch'] as $value) {
	$value = $_BCACHE->getiteminfo('groupbuy', $value['itemid'], $value['shopid']);
	$value['shopinfo'] = $_BCACHE->getshopinfo($value['shopid']);
	$value['groupbuytime'] = date('Y-m-d', $value['validity_start']).' '.$lang['groupbuyto'].' '.date('Y-m-d', $value['validity_end']);
	$value['groupbuydiscount'] = round(($value['groupbuypriceo'] / $value['groupbuyprice']), 2) * 10;
	$value['groupbuysave'] = round($value['groupbuyprice'] - $value['groupbuypriceo']);
	$value['groupbuypriceo'] = round($value['groupbuypriceo']);
	$value['groupbuyprice'] = round($value['groupbuyprice']);
	$groupbuylist[] = $value;
}

$active['cat'] = ' class="active"';
$location['name'] = (empty($_GET['keyword'])?'' : $_GET['keyword'].' - ') . $_G['setting']['site_nav']['groupbuy']['name'];

$seo_title = ($catid == 0 ? "" : $_G['categorylist'][$catid]['name'] . " - ") . $location['name'] . " - " . $seo_title;
include template('templates/site/default/groupbuy.html.php', 1);

ob_out(); //正則處理url/模板

?>