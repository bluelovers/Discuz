<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_menu.php 646 2010-09-13 03:37:40Z yexinhao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$isfounder = isset($isfounder) ? $isfounder : isfounder();

$menu = array();

if(file_exists($menudir = DISCUZ_ROOT.'./source/admincp/menu')) {
	$adminextend = $adminextendnew = $modulelist = array();
	if(file_exists($adminextendfile = DISCUZ_ROOT.'./data/cache/cache_adminextend.php')) {
		@include $adminextendfile;
	}
	$menudirhandle = dir($menudir);
	while($entry = $menudirhandle->read()) {
		if(!in_array($entry, array('.', '..')) && preg_match("/^menu\_([\w\.]+)$/", $entry, $entryr) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($menudir.'/'.$entry)) {
			@include_once $menudir.'/'.$entry;
			$adminextendnew[] = $entryr[1];
		}
	}

	if($adminextend != $adminextendnew) {
		@unlink($adminextendfile);
		if($adminextendnew) {
			require_once libfile('function/cache');
			writetocache('adminextend', '', getcachevars(array('adminextend' => $adminextendnew)));
		}
		unset($_G['lang']['admincp']);
	}
}

$topmenu = array('index' => $lang['home']);
$query = DB::query("SELECT identifier, name FROM ".DB::table('common_module')." WHERE available='1' ORDER BY displayorder");
while($module = DB::fetch($query)) {
	$topmenu[$module['identifier']] = $module['name'];
}

if(!isfounder() && !isset($GLOBALS['admincp']->perms['all'])) {
	$menunew = $menu;
	foreach($menu as $topkey => $datas) {
		if($topkey == 'index') {
			continue;
		}
		$itemexists = 0;
		foreach($datas as $key => $data) {
			if(array_key_exists($data[1], $GLOBALS['admincp']->perms)) {
				$itemexists = 1;
			} else {
				unset($menunew[$topkey][$key]);
			}
		}
		if(!$itemexists) {
			unset($topmenu[$topkey]);
			unset($menunew[$topkey]);
		}
	}
	$menu = $menunew;
}

?>