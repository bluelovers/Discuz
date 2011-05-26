<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: menu_cloud.php 21848 2011-04-14 03:03:23Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($isfounder) {
	$topmenu['cloud'] = '';

	require_once libfile('function/cloud');
	$cloudstatus = checkcloudstatus(false);

	if ($cloudstatus == 'cloud') {
		$menu['cloud'] = array(
			array('menu_cloud_applist', 'cloud_applist'),
			array('menu_cloud_siteinfo', 'cloud_siteinfo')
		);
		$apps = unserialize($_G['setting']['cloud_apps']);
		if(is_array($apps) && $apps) {
			foreach($apps as $app) {
				if($app['status'] != 'close') {
					array_push($menu['cloud'], array("menu_cloud_{$app['name']}", "cloud_{$app['name']}"));
				}
			}
		}

	} else {
		if ($cloudstatus == 'upgrade') {
			$menuitem = 'menu_cloud_upgrade';
		} else {
			$menuitem = 'menu_cloud_open';
		}

		$menu['cloud'] = array(
			array($menuitem, 'cloud_open')
		);
	}
}

?>