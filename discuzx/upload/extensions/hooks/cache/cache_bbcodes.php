<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_init_regexp', '_eFunc_build_cache_bbcodes_Before_init_regexp');

function _eFunc_build_cache_bbcodes_Before_init_regexp(&$conf) {
	extract($conf, EXTR_REFS);

	discuz_core::$_cache_data['bbcodes']['regexp_ex'] = array(
		'([^\"\[]+?)'	// dz 預設
		,'(\w+)'		// 英文+數字
		,'(\d+)'		// 數字
		, '([a-zA-Z]+)'	// 英文
		, '(.+?)'		// 任何字
		, '(.+)'		// 任何字(非空)
	);
}

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_define3', '_eFunc_build_cache_bbcodes_Before_define3');

function _eFunc_build_cache_bbcodes_Before_define3(&$conf) {
	extract($conf, EXTR_REFS);

	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);
}

?>