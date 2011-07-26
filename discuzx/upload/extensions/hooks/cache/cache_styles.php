<?php

/**
 * @author bluelovers
 **/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_writetocsscache:Before_minify', '_eFunc_writetocsscache_Before_minify');

function _eFunc_writetocsscache_Before_minify($_EVENT, $conf) {
	extract($conf, EXTR_REFS);

	if($entry != 'module.css') {
		// 清除 css 註解
		$cssdata = preg_replace('/\/\*((?:[^\*]*|\*(?!\/)).*)\*\//sU', "\n", $cssdata);
	}

	// 轉換分行
	$cssdata = str_replace("\r\n", "\n", $cssdata);
}

?>