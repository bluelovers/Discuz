<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Class_logging_ctl::on_login:After_setloginstatus', '_eClass_logging_ctl__on_login_After_setloginstatus');

function _eClass_logging_ctl__on_login_After_setloginstatus($_EVENT, $_conf) {
	extract($_conf, EXTR_REFS);

	$_member = $result['member'];

	space_merge($_member, 'profile');

	include_once libfile('function/profile');
	$_space = &$_member;

	if ($_space['birthmonth'] && $_space['birthday']) {
		/**
		 * 登入時自動更新星座資訊
		 */
		$_setarr['constellation'] = get_constellation($_space['birthmonth'], $_space['birthday']);
	}

	if ($_space['birthyear']) {
		$_setarr['zodiac'] = get_zodiac($_space['birthyear']);
	}

	if($_setarr) {
		DB::update('common_member_profile', $_setarr, array('uid' => $_space['uid']));
	}
}

?>