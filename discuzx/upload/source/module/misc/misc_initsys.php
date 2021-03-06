<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_initsys.php 24733 2011-10-10 01:52:31Z zhouguoqiang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// bluelovers
// 用於處理安裝後初始化 cache 來解決安裝後某些緩存沒有執行過的問題
if ($_G['gp_op'] == 'install') {
	require_once libfile('function/cache');

	updatecache('founder');

	_______initsys_cache();

	// 初始化預設安裝的插件
	_______initsys_plugins();

	echo 1;
	exit();
}
// bluelovers

if($_G['adminid'] != 1 && $_G['setting']) {
	exit('Access Denied');
}

// bluelovers
DB::query("TRUNCATE TABLE ".DB::table('common_syscache'));
DB::query("TRUNCATE TABLE ".DB::table('common_admincp_session'));

DB::query("DELETE FROM ".DB::table('forum_spacecache')." WHERE `variable` = 'birthday'");

_______initsys_cache();

function _______initsys_cache() {
	global $_G;
// bluelovers

require_once libfile('function/cache');
updatecache();

require_once libfile('function/block');
blockclass_cache();

if($_G['config']['output']['tplrefresh']) {
	$tpl = dir(DISCUZ_ROOT.'./data/template');
	while($entry = $tpl->read()) {
		if(preg_match("/\.tpl\.php$/", $entry)) {
			@unlink(DISCUZ_ROOT.'./data/template/'.$entry);
		}
	}
	$tpl->close();
}

// bluelovers
}

function _______initsys_plugins() {
	global $_G;

	/**
	 * bugfix when $data = $GLOBALS['importtxt'];
	 */
	global $importtxt;

// bluelovers
$plugins = array('qqconnect', 'cloudstat', 'soso_smilies', 'cloudsearch');

require_once libfile('function/plugin');
require_once libfile('function/admincp');

foreach($plugins as $pluginid) {
	$importfile = DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/discuz_plugin_'.$pluginid.'.xml';
	if(!file_exists($importfile)) {
		continue;
	}
	$plugin = DB::fetch_first("SELECT identifier, modules FROM ".DB::table('common_plugin')." WHERE identifier='$pluginid' LIMIT 1");
	if($plugin) {
		$modules = unserialize($plugin['modules']);
		if($modules['system'] == 2) {
			continue;
		}
		DB::delete('common_plugin', "identifier='$pluginid'");
	}
	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin', $importtxt);
	$pluginarray['plugin']['modules'] = unserialize(dstripslashes($pluginarray['plugin']['modules']));
	$pluginarray['plugin']['modules']['system'] = 2;
	$pluginarray['plugin']['modules'] = addslashes(serialize($pluginarray['plugin']['modules']));
	plugininstall($pluginarray);

	if($pluginarray['installfile']) {
		$plugindir = DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['directory'];
		if(file_exists($plugindir.'/'.$pluginarray['installfile'])) {
			@include_once $plugindir.'/'.$pluginarray['installfile'];
		}
	}
}
// bluelovers
}
// bluelovers

?>