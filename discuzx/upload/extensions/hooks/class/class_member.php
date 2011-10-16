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
}

?>