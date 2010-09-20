<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: card.php 4397 2010-09-10 10:07:01Z fanshengshuai $
 */
define("CURSCRIPT", "card");
require_once('./common.php');
require_once(B_ROOT.'./source/function/cache.func.php');

if(empty($_G['setting']['enablecard'])) {
	showmessage('visit_the_channel_does_not_exist', './');
}

updatebrandadscache(false, 86430);

$tpp = $_G['setting']['cardperpage'];

$active['card'] = ' class="active"';
$location['name'] = (empty($_GET['keyword'])?'' : $_GET['keyword'].' - ') . $_G['setting']['site_nav']['card']['name'];

//消費券列表
$_BCACHE->cachesql('cardsearch', 'SELECT itemid FROM '.tname('shopitems').' WHERE isdiscount=1 AND grade>2 ORDER BY displayorder ASC, itemid DESC', 0, 1, $tpp, 0, 'sitelist', 'shop');
$multipage = $_SBLOCK['cardsearch_multipage'];
$resultcount = $_SBLOCK['cardsearch_listcount'];
foreach($_SBLOCK['cardsearch'] as $result) {
	$result = $_BCACHE->getshopinfo($result['itemid']);
	$result['thumb'] = str_replace('static/image/nophoto.gif', 'static/image/shoplogo.gif', $result['thumb']);
	$result['subjectimage'] = str_replace('static/image/nophoto.gif', 'static/image/shoplogo.gif', $result['subjectimage']);
	$result['catname'] = $_SGLOBAL['shopcates'][$result['catid']]['name'];
	$list[] = $result;
}
$theurl = "card.php";

$seo_title = ($catid == 0 ? "" : $_G['categorylist'][$catid]['name'] . " - ") . $location['name'] . " - " . $seo_title;
include template('templates/site/default/card.html.php', 1);

ob_out();

?>