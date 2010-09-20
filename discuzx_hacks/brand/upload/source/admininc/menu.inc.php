<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: menu.inc.php 4360 2010-09-07 08:03:59Z fanshengshuai $
 */

if(!defined('IN_ADMIN') && !defined('IN_STORE')) {
	exit('Access Denied');
}

//二级菜单

if(pkperm('isadmin')) {
	require_once(B_ROOT.'./source/admininc/perm.inc.php');
	$menu['paneladd'] = array(
		array('menu_list_addgood', 'add&m=good'),
		array('menu_list_addnotice', 'add&m=notice'),
		array('menu_list_addalbum', 'add&m=album'),
		array('menu_list_addconsume', 'add&m=consume'),
		array('menu_list_addgroupbuy', 'add&m=groupbuy'),
		array('menu_list_addbrandlinks', 'add&m=brandlinks&op=add')
	);
	foreach($menu as $key=>$menus) {
		showmenu($key, $menus);
	}
	/*
	showmenu('index', array(
		array('menu_home', 'index'),
		array('menu_shop', 'list&m=shop'),
		array('menu_good', 'list&m=good'),
		array('menu_notice', 'list&m=notice'),
		array('menu_album', 'list&m=album'),
		array('menu_consume', 'list&m=consume'),
		array('menu_groupbuy', 'list&m=groupbuy'),
		array('menu_home_waitmod', 'list&m=shop&grade=0&optpass=1&filtersubmit=GO')
	));
	showmenu('paneladd', array(
		array('menu_list_addgood', 'add&m=good'),
		array('menu_list_addnotice', 'add&m=notice'),
		array('menu_list_addalbum', 'add&m=album'),
		array('menu_list_addconsume', 'add&m=consume'),
		array('menu_list_addgroupbuy', 'add&m=groupbuy'),
		array('menu_list_addbrandlinks', 'add&m=brandlinks&op=add')
	));
	showmenu('global', array(
		array('menu_global_basic', 'global'),
		array('menu_ads', 'ads'),
		array('menu_adv', 'adv'),
		array('menu_commentmodel', 'commentmodel'),
		array('menu_nav','nav'),
		array('menu_attr','attr'),
		array('menu_global_censor', 'censor'),
		array('menu_tool_updatecache', 'tool&operation=updatecache'),
		array('menu_tool_cron', 'tool&operation=cron'),
		array('menu_uc_discuz', 'discuz'),
		array('menu_block', 'block')
	));
	showmenu('catmanage', array(

		array('menu_category_shop', 'category&type=shop'),
		array('menu_category_region', 'category&type=region'),
		array('menu_category_good', 'category&type=good'),
		array('menu_category_album', 'category&type=album'),
		array('menu_category_consume', 'category&type=consume'),
		array('menu_category_groupbuy', 'category&type=groupbuy'),
		array('menu_category_notice', 'category&type=notice')
	));
	showmenu('shop', array(
		array('menu_shop', 'list&m=shop'),
		array('menu_home_waitmod', 'list&m=shop&grade=0&optpass=1&filtersubmit=GO'),
		array('menu_list_addshop', 'add&m=shop'),
		array('menu_group','group'),
	));
	showmenu('infomanage', array(
		array('menu_good','list&m=good'),
		array('menu_album', 'list&m=album'),
		array('menu_consume', 'list&m=consume'),
		array('menu_notice', 'list&m=notice'),
		array('menu_comment', 'comment'),
		array('menu_report', 'report'),
		array('menu_groupbuy', 'list&m=groupbuy'),
		array('menu_brandlinks', 'brandlinks')
	));
	showmenu('admintools', array(
		array('menu_logs', 'logs&operation=admin'),
		array('menu_db', 'db&operation=export'),
		array('menu_perm', 'perm')
	));
	showmenu('uc', array());
	*/
} else {
	if($shop->status == 'new') {
		showmenu('index', array(array('menu_home', 'index')));
		$shopmenu = array(
			array('menu_shop_my', 'edit&m=shop')
		);
		if($_G['setting']['enablemap'] == 1) {
			array_push($shopmenu, array('menu_map', 'map'));
		}
		showmenu('shop', $shopmenu);
	}elseif($shop->status == 'normal') {
		$shopmenu = array(
			array('menu_shop_my', 'edit&m=shop'),
			array('menu_theme', 'theme'),
			array('menu_modifypasswd', 'modifypasswd'),
			array('menu_nav','nav')
		);
		if($_G['setting']['enablemap'] == 1) {
			array_push($shopmenu, array('menu_map', 'map'));
		}
		showmenu('index', $menuindex);
		showmenu('shop', $shopmenu);
		showmenu('infomanage', $menuinfomanage);
	}
}
?>