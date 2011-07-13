<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_share.php 6741 2010-03-25 07:36:01Z cnteacher $
 */

function mkshare($share) {
	$share['body_data'] = unserialize($share['body_data']);

	// bluelovers
	if (discuz_core::$plugin_support['Scorpio_Event']) {
		Scorpio_Event::instance('Func_'.__FUNCTION__.':Before')->run(array(&$share));
	}
	// bluelovers

	$searchs = $replaces = array();
	if($share['body_data']) {
		foreach (array_keys($share['body_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $share['body_data'][$key];
		}
	}

	// bluelovers
	if (discuz_core::$plugin_support['Scorpio_Event']) {
		Scorpio_Event::instance('Func_'.__FUNCTION__.':After')->run(array(&$share, &$searchs, &$replaces));
	}
	// bluelovers

	$share['body_template'] = str_replace($searchs, $replaces, $share['body_template']);

	return $share;
}
?>