<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Class_discuz_core::_init:After', '_eClass_discuz_core__init_After');

/**
 * Event:
 * 		Class_discuz_core::_init:After
 */
function _eClass_discuz_core__init_After($_EVENT, $discuz) {



}

?>