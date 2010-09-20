<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: consume.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

define("CURSCRIPT", "consume");
require_once('./common.php');


$tpp = $_G['setting']['consumesearchperpage'];

$wheresql = '';
$where = array();
$orderby = 'i.displayorder ASC, ';

$_GET['keyword'] = trim(addslashes(rawurldecode($_REQUEST['keyword'])));

$catid = $_REQUEST['catid'];
$tagids = '';

!empty($_REQUEST['catid']) && $tagids = $_SGLOBAL['consumecates'][$_REQUEST['catid']]['subcatid'];
$catid>0 && $tagids && $where[] = 'i.catid IN ('.$tagids.')';
$where[] = 'i.grade_s>2 AND i.grade>2';

if(empty($_REQUEST['range']) || $_REQUEST['range'] == 'available') {
	$where[] = "i.validity_end>" . ($_G['timestamp'] - $_G['timestamp']%86400);
	$select['range'] = ' selected="selected"';
}
$_GET['keyword'] && $where[] = 'i.subject LIKE \'%'.$_GET['keyword'].'%\'';
if(!empty($_REQUEST['orderBy']) && $_REQUEST['orderBy'] != 'dateline') {
	$select['byview'] = ' selected="selected"';
	$orderby .= 'i.viewnum';
} else {
	$select['bytime'] = ' selected="selected"';
	$orderby .= 'i.itemid';
}

$wheresql = implode(' AND ', $where);

$_BCACHE->cachesql('consumesearch', 'SELECT i.itemid, i.shopid FROM '.tname('consumeitems')." i WHERE $wheresql ORDER BY $orderby DESC", 0, 1, $tpp, 0, 'sitelist', 'consume');
$multipage = $_SBLOCK['consumesearch_multipage'];
$resultcount = $_SBLOCK['consumesearch_listcount'];
foreach($_SBLOCK['consumesearch'] as $result) {
	$result = $_BCACHE->getiteminfo('consume', $result['itemid'], $result['shopid']);
	$result['shopinfo'] = $_BCACHE->getshopinfo($result['shopid']);
	$list[] = $result;
}

$active['consume'] = ' class="active"';
$location['name'] = (empty($_GET['keyword'])?'' : $_GET['keyword'].' - ') . $_G['setting']['site_nav']['consume']['name'];

$seo_title = ($catid == 0 ? "" : $categorylist[$catid]['name'] . " - ") . $location['name'] . " - " . $seo_title;
include template('templates/site/default/consumesearch.html.php', 1);

ob_out();

?>