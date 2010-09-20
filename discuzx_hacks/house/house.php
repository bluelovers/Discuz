<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: category.php 7024 2010-03-28 06:39:41Z cnteacher $
 */

// 定義應用 ID
define('APPTYPEID', 99);
define('CURSCRIPT', 'category');

//====================================
// 基礎文件引入， 其他程序引導文件可能不需要
// class_forum.php 和 function_forum.php
// 請根據實際需要確定是否引入
//====================================

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modidentifier = 'house';
$modurl = 'house.php';

$cachelist = array('category_sortlist_'.$modidentifier, 'category_arealist_'.$modidentifier, 'category_channellist', 'category_usergrouplist_'.$modidentifier, 'blockclass');
//====================================
// 加載核心處理,各程序入口文件代碼相同
//====================================
$discuz->cachelist = $cachelist;
$discuz->init();

//=======================
//加載 mod
//===================================
$modarray = array('index', 'list', 'view', 'post', 'misc', 'my', 'threadmod', 'usergroup');
// 判斷 $mod 的合法性
$mod = !in_array($_G['mod'], $modarray) ? 'index' : $_G['mod'];

$sortlist = $_G['cache']['category_sortlist_'.$modidentifier];
$arealist = $_G['cache']['category_arealist_'.$modidentifier];

$channel = $_G['cache']['category_channellist'][$modidentifier];
$_G['category_usergrouplist'] = $usergrouplist = $_G['cache']['category_usergrouplist_'.$modidentifier];

$sortid = intval($_G['gp_sortid']);
$tid = intval($_G['gp_tid']);
$cityid = intval($_G['gp_cityid']);
$category_usergroup = array();

if(in_array($mod, array('index', 'list', 'view', 'my'))) {
	if(empty($channel['status']) && $_G['adminid'] != 1) {
		showmessage(lang('category/template', 'house_status_close'));
	}
}

$usergroupid = 0;
if($_G['uid'] && !in_array($mod, array('misc')) && $_G['groupid'] != 8) {
	$_G['category_member'] = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_member')." WHERE uid='$_G[uid]'");
	if(empty($_G['category_member']['uid'])) {
		$housegroupid = DB::result_first("SELECT gid FROM ".DB::table('category_'.$modidentifier.'_usergroup')." WHERE type='personal'");
		DB::insert('category_'.$modidentifier.'_member', array('uid' => $_G['uid'], 'groupid' => $housegroupid));
	}
	$usergroupid = $_G['category_member']['groupid'];

	if($usergroupid) {
		loadcache('category_group_'.$modidentifier.'_'.$usergroupid);
		$category_usergroup = $_G['cache']['category_group_'.$modidentifier.'_'.$usergroupid];
	}
}

require DISCUZ_ROOT.'./source/module/category/'.$modidentifier.'/'.$mod.'.php';

?>