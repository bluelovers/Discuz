<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: common.php 4401 2010-09-13 02:44:25Z fanshengshuai $
 */

define('IN_BRAND', true);
define('B_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('B_VER', '1.2');
define('B_RELEASE', '20100915');
define('D_BUG', '0');

D_BUG?error_reporting(E_ERROR):error_reporting(0);
$_SGLOBAL = $_SBLOCK = $_SHTML = $_DCACHE = $_SGET = array();


require_once(B_ROOT.'./source/function/common.func.php');
require_once(B_ROOT.'./source/adminfunc/brandpost.func.php');
require_once(B_ROOT.'./source/class/brand.class.php');

brand::init();

include_once(B_ROOT.'./language/brand.lang.php');

// 檢查關閉站點
if (!ckfounder($_G['uid']) && $_G['setting']['siteclosed']) {
	if (ACTION != 'auth' && ACTION != 'seccod') {
		showmessage($_G['setting']['siteclosed_reason']);
	}
}


if(!empty($_G['setting']['gzipcompress']) && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}
@header('Content-Type: text/html; charset='.$_G['charset']);
$newsiteurl = B_URL;
if(strpos($newsiteurl, '://') === false) {
	$newsiteurl = 'http://'.(empty($_SERVER['HTTP_HOST'])?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST']).$newsiteurl;
}
define('B_URL_ALL', $newsiteurl);

if(file_exists(B_ROOT.'./index.html')) {
	define('S_ISPHP', '1');
}

// config.cache不存在將刷新頁面
refreshbrandsetting();

// 讀取用戶組
if(!@include_once(B_ROOT.'./data/system/shopgroup.cache.php')) {
	include_once(B_ROOT.'./source/function/cache.func.php');
	updateshopgroupcache();
}

// 讀取分類
$cattypes = array('region', 'shop', 'good', 'notice', 'consume', 'album', 'groupbuy');
foreach($cattypes as $cattype) {
	if(!@include_once(B_ROOT.'./data/system/'.$cattype.'category.cache.php')) {
		include_once(B_ROOT.'./source/function/cache.func.php');
		updatecategorysingle($cattype);
	}
}

// 讀取分類
$categorylist = $cats = $cats_plus = array();
$_G['categorylist'] = getmodelcategory('shop');

// 讀取消息
if (exists_discuz()) {
	$pm = loadClass('pm');
	$pm_new = $pm->pm_new;
}

// 初始化 SEO 標題
$seo_title = $_G['setting']['sitename'] . ' - ' . $_G['setting']['wwwname'];
$seo_keywords = $_G['setting']['seokeywords'];
$seo_description = $_G['setting']['seodescription'];

?>