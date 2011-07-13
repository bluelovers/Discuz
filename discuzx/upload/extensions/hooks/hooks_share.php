<?php

/**
 * @author bluelovers
 */

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_mkshare:Before', '_eFunc_mkshare_Before');

function _eFunc_mkshare_Before($share = array()) {
	$_lang_template = empty($share['lang_template']) ? array() : unserialize($share['lang_template']);
	if (is_array($_lang_template)) {
		foreach ($_lang_template as $_k_ => $_v_) {
			$share[$_k_] = is_array($_v_) ? call_user_func_array('lang', $_v_) : lang('feed', $_v_);
		}
	}
}

Scorpio_Hook::add('Func_mkshare:After', '_eFunc_mkshare_After');

function _eFunc_mkshare_After(&$share, &$searchs, &$replaces) {
	$share['title_template'] = str_replace($searchs, $replaces, $share['title_template']);
}

Scorpio_Hook::add('Func_mkfeed:Before', '_eFunc_mkfeed_Before');

function _eFunc_mkfeed_Before(&$feed) {

	$_lang_template = empty($feed['lang_template']) ? array() : unserialize($feed['lang_template']);
	if (is_array($_lang_template)) {
		foreach ($_lang_template as $_k_ => $_v_) {
			$feed[$_k_] = is_array($_v_) ? call_user_func_array('lang', $_v_) : lang('feed', $_v_);

			if ($feed['icon'] == 'share' && !strexists($feed[$_k_], '{actor}')) {
				$feed[$_k_] = '{actor} '.$feed[$_k_];
			}
		}
	}

	if ($feed['icon'] == 'share') {
		$feed['title_data'] = $feed['body_data'];
	}

//	dexit($feed);
}

?>