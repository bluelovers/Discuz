<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_define3', '_eFunc_build_cache_bbcodes_Before_define3');

function _eFunc_build_cache_bbcodes_Before_define3(&$conf) {
	extract($conf, EXTR_REFS);

	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);
}

?>