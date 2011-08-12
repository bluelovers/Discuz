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

function _eFunc_discuzcode_Before_bbcodes($_EVENT, $conf) {
	$find = $replace = array();

	$find[]		= '/\s*\[h([1-6])\]((?:[^\[]|\[(?!\/h\1\]))*)\[\/h\1\]\n*/is';
	$replace[]	= "<h\\1 class=\"bbcode_headline\">\\2</h\\1>";

	$find[]		= '/\s*\[(seo)(?:=([\w,]+))?\]((?:[^\[]|\[(?!\/\\1\])).*)\[\/\\1\]\s*/iesU';
	$replace[]	= '';

	if ($find && $replace) {
		$conf['message'] = preg_replace($find, $replace, $conf['message']);
	}
}

Scorpio_Hook::add('Tpl_Func_hooktags:Before', '_discuzcode_eTpl_Func_hooktags_Before');

function _discuzcode_eTpl_Func_hooktags_Before($_EVENT, &$hook_data, $hookid, $key) {
	global $_G;

	$_varhash = VERHASH;

	if (
		(
			// 帖子底部
			$hookid == 'viewthread_bottom'
			// AJAX 時
			|| ($hookid == 'viewthread_endline' && (!empty($_G['gp_viewpid']) || $_G['inajax']))
		)
	) {
		$hook_data .= '<script src="extensions/js/clearbox/clearbox.js?dir=extensions/js/clearbox/clearbox" type="text/javascript"></script>';
	}
}

?>