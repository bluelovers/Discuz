<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_bbcodes.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_bbcodes() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_bbcode')." WHERE available>'0'");
	$regexp = array	(
		1 => "/\[{bbtag}]([^\"\[]+?)\[\/{bbtag}\]/is",
		2 => "/\[{bbtag}=(['\"]?)([^\"\[]+?)(['\"]?)\]([^\"\[]+?)\[\/{bbtag}\]/is",
		3 => "/\[{bbtag}=(['\"]?)([^\"\[]+?)(['\"]?),(['\"]?)([^\"\[]+?)(['\"]?)\]([^\"\[]+?)\[\/{bbtag}\]/is"
	);

	// bluelovers
	if (discuz_core::$plugin_support['Scorpio_Event']) {
		//Event: Func_build_cache_bbcodes:Before_init_regexp
		Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_init_regexp')
			->run(array(array(
				'regexp' => &$regexp,
		)));
	}
	// bluelovers

	while($bbcode = DB::fetch($query)) {

		// bluelovers
		$switchstop = 0;

		//TODO:Event: Func_build_cache_bbcodes:Before_perm
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_perm')
				->run(array(array('regexp' => &$regexp,
					'bbcode' => &$bbcode,
					'search' => &$search,
					'switchstop' => &$switchstop,
					'replace' => &$replace,
					'data' => &$data,
			)));
		}

		if (!$switchstop) {
		// bluelovers

			// 允許使用此代碼的用戶組
			$bbcode['perm'] = explode("\t", $bbcode['perm']);
			if(in_array('', $bbcode['perm']) || !$bbcode['perm']) {
				continue;
			}

		// bluelovers
		}

		$switchstop = 0;

		//TODO:Event: Func_build_cache_bbcodes:Before_define1
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_define1')
				->run(array(array('regexp' => &$regexp,
					'bbcode' => &$bbcode,
					'search' => &$search,
					'switchstop' => &$switchstop,
					'replace' => &$replace,
					'data' => &$data,
			)));
		}

		if (!$switchstop) {
		// bluelovers

			$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
			$bbcode['replacement'] = preg_replace("/([\r\n])/", '', $bbcode['replacement']);

		// bluelovers
		}

		$switchstop = 0;

		//TODO:Event: Func_build_cache_bbcodes:Before_switch
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_switch')
				->run(array(array('regexp' => &$regexp,
					'bbcode' => &$bbcode,
					'search' => &$search,
					'switchstop' => &$switchstop,
					'replace' => &$replace,
					'data' => &$data,
			)));
		}

		if (!$switchstop) {
		// bluelovers

			// 依照 參數個數 來做個別處理
			switch($bbcode['params']) {
				case 2:
					$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
					$bbcode['replacement'] = str_replace('{2}', '\\4', $bbcode['replacement']);
					break;
				case 3:
					$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
					$bbcode['replacement'] = str_replace('{2}', '\\5', $bbcode['replacement']);
					$bbcode['replacement'] = str_replace('{3}', '\\7', $bbcode['replacement']);
					break;
				default:
					$bbcode['replacement'] = str_replace('{1}', '\\1', $bbcode['replacement']);
					break;
			}

		// bluelovers
		}

		$switchstop = 0;

		//TODO:Event: Func_build_cache_bbcodes:Before_define2
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_define2')
				->run(array(array('regexp' => &$regexp,
					'bbcode' => &$bbcode,
					'search' => &$search,
					'switchstop' => &$switchstop,
					'replace' => &$replace,
					'data' => &$data,
			)));
		}

		if (!$switchstop) {
		// bluelovers

			if(preg_match("/\{(RANDOM|MD5)\}/", $bbcode['replacement'])) {
				$search = str_replace('is', 'ies', $search);
				$replace = '\''.str_replace('{RANDOM}', '_\'.random(6).\'', str_replace('{MD5}', '_\'.md5(\'\\1\').\'', $bbcode['replacement'])).'\'';
			} else {
				$replace = $bbcode['replacement'];
			}

		// bluelovers
		}

		$switchstop = 0;

		//TODO:Event: Func_build_cache_bbcodes:Before_define3
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_define3')
				->run(array(array('regexp' => &$regexp,
					'bbcode' => &$bbcode,
					'search' => &$search,
					'switchstop' => &$switchstop,
					'replace' => &$replace,
					'data' => &$data,
			)));
		}

		if (!$switchstop) {
		// bluelovers

			foreach($bbcode['perm'] as $groupid) {
				for($i = 0; $i < $bbcode['nest']; $i++) {
					$data[$groupid]['searcharray'][] = $search;
					$data[$groupid]['replacearray'][] = $replace;
				}
			}

		// bluelovers
		}
		// bluelovers
	}

	save_syscache('bbcodes', $data);
}

?>