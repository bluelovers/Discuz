<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

/*
class _bbcode_ {
}
*/

Scorpio_Hook::add('Func_discuzcode:Before_bbcodes', '_eFunc_discuzcode_Before_bbcodes');

function _eFunc_discuzcode_Before_bbcodes($_EVENT, $_conf) {
	$find = $replace = array();

	$find[]		= '/\s*\[h([1-6])\]((?:[^\[]|\[(?!\/h\1\]))*)\[\/h\1\]\n*/is';
	$replace[]	= "<h\\1 class=\"bbcode_headline\">\\2</h\\1>";

	$find[]		= '/\s*\[(seo)(?:=([\w,]+))?\]((?:[^\[]|\[(?!\/\\1\])).*)\[\/\\1\]\s*/iesU';
	$replace[]	= '';

	if ($find && $replace) {
		$_conf['message'] = preg_replace($find, $replace, $_conf['message']);
	}

	$find = $replace = array();

	if ($find && $replace) {
		$_conf['message'] = str_replace($find, $replace, $_conf['message']);
	}
}

?>