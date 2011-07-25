<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_bbcodes_display.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_bbcodes_display() {
	$data = array();
	$query = DB::query("SELECT tag, icon, explanation, params, prompt, perm FROM ".DB::table('forum_bbcode')." WHERE available='2' AND icon!='' ORDER BY displayorder");

	$i = 0;
	while($bbcode = DB::fetch($query)) {

		// bluelovers
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			//Event: Func_build_cache_bbcodes_display:Before_perm
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_perm')
				->run(array(array(
					'data' => &$data,
					'i' => &$i,
					'bbcode' => &$bbcode,
			)));
		}
		// bluelovers

		$bbcode['perm'] = explode("\t", $bbcode['perm']);
		if(in_array('', $bbcode['perm']) || !$bbcode['perm']) {
			continue;
		}
		$i++;
		$tag = $bbcode['tag'];
		$bbcode['i'] = $i;

		// bluelovers
		$switchstop = 0;

		if (discuz_core::$plugin_support['Scorpio_Event']) {
			//Event: Func_build_cache_bbcodes_display:Before_fixvalue
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_fixvalue')
				->run(array(array(
					'data' => &$data,
					'i' => &$i,
					'bbcode' => &$bbcode,
					'tag' => &$tag,
					'switchstop' => &$switchstop,
			)));
		}

		if (!$switchstop) {
		// bluelovers

			$bbcode['explanation'] = dhtmlspecialchars(trim($bbcode['explanation']));
			$bbcode['prompt'] = addcslashes($bbcode['prompt'], '\\\'');

		// bluelovers
		}

		$switchstop = 0;
		// bluelovers

		// bluelovers
		if (discuz_core::$plugin_support['Scorpio_Event']) {
			//Event: Func_build_cache_bbcodes_display:Before_save_perm_groupid
			Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_save_perm_groupid')
				->run(array(array(
					'data' => &$data,
					'i' => &$i,
					'bbcode' => &$bbcode,
					'tag' => &$tag,
			)));
		}
		// bluelovers

		unset($bbcode['tag']);
		foreach($bbcode['perm'] as $groupid) {
			$data[$groupid][$tag] = $bbcode;
		}
	}

	// bluelovers
	if (discuz_core::$plugin_support['Scorpio_Event']) {
		//Event: Func_build_cache_bbcodes_display:Before_save_syscache
		Scorpio_Event::instance('Func_' . __FUNCTION__ . ':Before_save_syscache')
			->run(array(array(
				'data' => &$data,
				'i' => &$i,
		)));
	}
	// bluelovers

	save_syscache('bbcodes_display', $data);
}

?>