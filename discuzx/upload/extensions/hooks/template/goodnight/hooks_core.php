<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Class_discuz_core::init:After', '_eClass_discuz_core_init_After');

/**
 * Event:
 * 		Class_discuz_core::init:After
 */
function _eClass_discuz_core_init_After($_EVENT, $discuz) {
	static $initated = false;

	if(!$initated) {

		/**
		 * 如果是訪客 或者 搜尋引擎時
		 */
		if (!$discuz->var['uid'] || IS_ROBOT) {
			// 隱藏 LOGO (因為是 flash 減少流量)
			discuz_core::$tpl['header_logo_hide'] = true;
		}

	}

	$initated = true;
}

?>