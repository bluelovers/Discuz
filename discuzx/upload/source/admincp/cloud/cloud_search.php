<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_search.php 22747 2011-05-19 04:11:31Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$op = $_G['gp_op'];

$signUrl = generateSiteSignUrl();

$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('setting', 'api')) ? $_G['gp_anchor'] : 'setting';
$current = array($_G['gp_anchor'] => 1);

$searchnav = array();

$searchnav[0] = array('search_menu_setting', 'cloud&operation=search&anchor=setting', $current['setting']);
$searchnav[1] = array('search_menu_api', 'cloud&operation=search&anchor=api', $current['api']);

if (!$_G['inajax']) {
	cpheader();
	shownav('navcloud', 'menu_cloud_search');
	showsubmenu('menu_cloud_search', $searchnav);
}

if($_G['gp_anchor'] == 'setting') {
	headerLocation($cloudDomain.'/search/setting/?'.$signUrl);

} elseif($_G['gp_anchor'] == 'api') {
	headerLocation($cloudDomain.'/search/api/?'.$signUrl);

}

?>