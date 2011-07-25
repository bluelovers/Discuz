<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_init_regexp', '_eFunc_build_cache_bbcodes_Before_init_regexp');

function _eFunc_build_cache_bbcodes_Before_init_regexp(&$conf) {
	// 供 discuz_core::$_cache_data['bbcodes']['regexp_ex'] 替換參數用的原始字串
	discuz_core::$_cache_data['bbcodes']['regexp_base'] = array(
		1 => "/\[{bbtag}\]{1}\[\/{bbtag}\]/is",
		2 => "/\[{bbtag}=(['\"]?){1}(['\"]?)\]{2}\[\/{bbtag}\]/is",
		3 => "/\[{bbtag}=(['\"]?){1}(['\"]?),(['\"]?){2}(['\"]?)\]{3}\[\/{bbtag}\]/is",
	);

	discuz_core::$_cache_data['bbcodes']['regexp_ex'] = array(
		// dz 預設
		0 => '([^\"\[]+?)',
		// 英文+數字
		1 => '(\w+)',
		// 數字
		2 => '(\d+)',
		// 英文
		3 => '([a-zA-Z]+)',
		// 任何字
		4 => '(.+?)',
		// 任何字(非空)
		5 => '(.+)',
	);
}

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_define1', '_eFunc_build_cache_bbcodes_Before_define1');

function _eFunc_build_cache_bbcodes_Before_define1(&$conf) {
	extract($conf, EXTR_REFS);

	//TODO:使用 discuz_core::$_cache_data['bbcodes']['regexp_base']
	$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
	// 追加清除 \t
	$bbcode['replacement'] = preg_replace("/([\t\r\n])/", '', $bbcode['replacement']);

	// 替換 {bbtag} 為 bbcode 名稱
	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);

	// 處理參數設定依照 discuz_core::$_cache_data['bbcodes']['regexp_ex'] 內的定義
	if (!empty(discuz_core::$_cache_data['bbcodes']['regexp_ex'])) {
		for ($_i = 0; $_i < $bbcode['params']; $_i++) {
			$_j = $_i + 1;

			//TODO:增加可設定使用哪一個設定值
			$_k = 0;

			$search = str_replace('{'.$_j.'}', discuz_core::$_cache_data['bbcodes']['regexp_ex'][$_k], $search);
		}
	}

	$switchstop = 1;
}

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_define3', '_eFunc_build_cache_bbcodes_Before_define3');

function _eFunc_build_cache_bbcodes_Before_define3(&$conf) {
	extract($conf, EXTR_REFS);

	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);
}

?>