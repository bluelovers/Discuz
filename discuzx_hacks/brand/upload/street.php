<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: street.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

define("CURSCRIPT", "street");
include_once('./common.php');
require_once(B_ROOT.'./source/function/cache.func.php');
updatebrandadscache(false, 86430);
$tagarrs = $where = $shoplist = $value = array();
$tagids = $joinsql = $wheresql = '';
$query = NULL;

//常见id处理
foreach(array('itemid', 'nid', 'uid', 'catid', 'tagid') as $value) {
	$_GET[$value] = $_POST[$value] = intval(!empty($_POST[$value])?$_POST[$value]:$_GET[$value]);
}
$_GET['keyword'] = trim(addslashes(rawurldecode($_REQUEST['keyword'])));

$catid = $_GET['catid'] ? $_GET['catid'] : 0;
$region = $_GET['region'] ? $_GET['region'] : 0;
$categorylist_select = $regionlist_select = '';
if(!empty($catid)) {
    foreach($_G['categorylist'] as $key=>$category) {
        if($category['upid'] == $catid)
            $categorylist_select .= '<a href="street.php?catid='.$key.(!empty($region)?'&region='.$region:'').'">'.$category['name'].'</a>&nbsp;&nbsp;';
    }
    if($categorylist_select == '') {
        foreach($_G['categorylist'] as $key=>$category) {
            if($category['upid'] == $_G['categorylist'][$catid]['upid'])
                $categorylist_select .= '<a'.($key==$catid?' style="color:red;"':'').' href="street.php?catid='.$key.(!empty($region)?'&region='.$region:'').'">'.$category['name'].'</a>&nbsp;&nbsp;';
        }
    }
} else {
    foreach($_G['categorylist'] as $key=>$category) {
        if($category['upid'] == 0)
            $categorylist_select .= '<a href="street.php?catid='.$key.(!empty($region)?'&region='.$region:'').'">'.$category['name'].'</a>&nbsp;&nbsp;';
    }
}
$regionlist = getmodelcategory('region');
if(!empty($region)) {
    $location['region'] = $regionlist[$region]['name'];
    foreach($regionlist as $key=>$category) {
        if($category['upid'] == $region)
            $regionlist_select .= '<a href="street.php?region='.$key.(!empty($catid)?'&catid='.$catid:'').'">'.$category['name'].'</a>&nbsp;&nbsp;';
    }
    if($regionlist_select == '') {
        foreach($regionlist as $key=>$category) {
            if($category['upid'] == $regionlist[$region]['upid'])
                $regionlist_select .= '<a'.($key==$region?' style="color:red;"':'').' href="street.php?region='.$key.(!empty($catid)?'&catid='.$catid:'').'">'.$category['name'].'</a>&nbsp;&nbsp;';
        }
    }
} else {
    foreach($regionlist as $key=>$category) {
        if($category['upid'] == 0)
            $regionlist_select .= '<a href="street.php?region='.$key.(!empty($catid)?'&catid='.$catid:'').'">'.$category['name'].'</a>&nbsp;&nbsp;';
    }
}
$tagids = !empty($_GET['tagid']) && $_G['categorylist'][$_GET['tagid']]['upid']==$_GET['catid']?$_GET['tagid']:$_G['categorylist'][$_GET['catid']]['subcatid']; //搜索分类id拼合
!empty($region) ? $where[] = 'i.region IN ('.$regionlist[$region]['subcatid'].')':'';
$tagarrs = !empty($_G['categorylist'][$_GET['catid']]['subcatid']) ? explode(', ', $_G['categorylist'][$_GET['catid']]['subcatid'])
: ''; //二级分类的显示
array_shift($tagarrs);
//if($_GET['range'] != 'all' && !$_GET['keyword'] && !$tagids) { showmessage('no_tagids');} //二级分类不存在
$all = $thisstreet = '';
$_GET['range'] == 'all' ? $all = 'checked' : $thisstreet = 'checked';

//条件拼合
$joinsql = ' FROM '.tname('shopitems').' s LEFT JOIN '.tname('scorestats').' ss ON ss.itemid=s.itemid';

//提交了搜索数据
$_GET['range'] != 'all' && !empty($tagids) && $where[] = 'i.catid IN ('.$tagids.')';
$where[] = 'i.grade>2';
$_GET['keyword'] && $where[] = 'i.subject LIKE \'%'.$_GET['keyword'].'%\'';
$wheresql = implode(' AND ', $where);

//分页处理
$tpp = $_G['setting']['shopsearchperpage'];

//数据查询，拆分SQL，分片缓存
$_BCACHE->cachesql('shopsearch', 'SELECT i.itemid FROM '.tname('shopitems').' i WHERE '.$wheresql.' ORDER BY i.displayorder ASC, i.isdiscount DESC, i.replynum DESC', 0, 1, $tpp, 0, 'sitelist', 'shop');
$multipage = $_SBLOCK['shopsearch_multipage'];
$resultcount = $_SBLOCK['shopsearch_listcount'];
foreach($_SBLOCK['shopsearch'] as $value) {
	$shoplist[] = $_BCACHE->getshopinfo($value['itemid']);
}
$active['street'] = ' class="active"';
$location['name'] = (empty($_GET['keyword'])?'' : $_GET['keyword'].' - ') . $_G['setting']['site_nav']['street']['name'];
$location['tagname'] = $_G['categorylist'][$_GET['tagid']]['name'];

$seo_title = ($catid == 0 ? "" : $_G['categorylist'][$catid]['name'] . " - ") . $location['name'] . " - " . $seo_title;
include template('templates/site/default/street.html.php', 1);

ob_out(); //正则处理url/模板

?>