<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_init_regexp', '_eFunc_build_cache_bbcodes_Before_init_regexp');

function _eFunc_build_cache_bbcodes_Before_init_regexp($_EVENT, $_conf) {
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

function _eFunc_build_cache_bbcodes_Before_define1($_EVENT, $_conf) {
	extract($_conf, EXTR_REFS);

	//TODO:使用 discuz_core::$_cache_data['bbcodes']['regexp_base']
	if (!empty(discuz_core::$_cache_data['bbcodes']['regexp_ex'])) {
		$search = str_replace('{bbtag}', $bbcode['tag'], discuz_core::$_cache_data['bbcodes']['regexp_base'][$bbcode['params']]);
	} else {
		$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
	}
	// 追加清除 \t
	$bbcode['replacement'] = preg_replace("/([\t\r\n])/", '', $bbcode['replacement']);

	// 替換 {bbtag} 為 bbcode 名稱
	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);

	// 處理參數設定依照 discuz_core::$_cache_data['bbcodes']['regexp_ex'] 內的定義
	if (!empty(discuz_core::$_cache_data['bbcodes']['regexp_ex'])) {

		$_pattern = explode("\t", $bbcode['pattern']);

		for ($_i = 0; $_i < $bbcode['params']; $_i++) {
			$_j = $_i + 1;

			//TODO:增加可設定使用哪一個設定值
			$_k = 0;

			$_pattern[$_i] = intval($_pattern[$_i]);
			if ($_pattern[$_i] > 0) {
				$_k = $_pattern[$_i];
			}

			$search = str_replace('{'.$_j.'}', discuz_core::$_cache_data['bbcodes']['regexp_ex'][$_k], $search);
		}
	}

	$switchstop = 1;
}

Scorpio_Hook::add('Func_build_cache_bbcodes:Before_define3', '_eFunc_build_cache_bbcodes_Before_define3');

function _eFunc_build_cache_bbcodes_Before_define3($_EVENT, $_conf) {
	extract($_conf, EXTR_REFS);

	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);
}

Scorpio_Hook::add('Func_build_cache_bbcodes_display:Before_fixvalue', '_eFunc_build_cache_bbcodes_display_Before_fixvalue');

function _eFunc_build_cache_bbcodes_display_Before_fixvalue($_EVENT, $_conf) {
	extract($_conf, EXTR_REFS);

	if ($bbcode['icon']) {
		//STATICURL
		//DISCUZ_ROOT
		$_def_path = 'image/common/';
		$_sco_path_ref = '../plus/bbcode/';

		// 由於是經過 admincp 設定的所以不做嚴格的檢查
		$_icon_isurl = strpos($bbcode['icon'], 'http://') === 0;

		if (!$_icon_isurl && file_exists(DISCUZ_ROOT.'./static/'.$_def_path.$bbcode['icon'])) {
		} elseif ($_icon_isurl) {
			$bbcode['icon_url'] = $bbcode['icon'];
			unset($bbcode['icon']);
		} else {
			// image/plus/bbcode/bb_default.gif
			$bbcode['icon'] = $_sco_path_ref.'bb_default.gif';
		}
	}
}

?>