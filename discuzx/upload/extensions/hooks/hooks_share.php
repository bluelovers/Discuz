<?php

/**
 * @author bluelovers
 */

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

/* function_share.php */

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

/* function_feed.php */

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

/* spacecp_share.php */

Scorpio_Hook::add('Dz_module_spacecp_share:Before_share', '_eDz_module_spacecp_share_Before_share');

function _eDz_module_spacecp_share_Before_share($conf) {
	$conf['arr'] = _share_add($conf['arr']);
}

Scorpio_Hook::add('Dz_module_spacecp_share:Before_share_insert', '_eDz_module_spacecp_share_Before_share_insert');

function _eDz_module_spacecp_share_Before_share_insert($conf) {
	$conf['setarr'] = DB::table_field_value('home_share', $conf['setarr']);
}

/* func */

function _share_add($share) {
	$_lang_template = array();
	list($share['title_template'], $_lang_template['title_template']) = _mkshare($share['title_template']);
	list($share['body_template'], $_lang_template['body_template']) = _mkshare($share['body_template']);
	list($share['body_general'], $_lang_template['body_general']) = _mkshare($share['body_general']);

	if ($_lang_template = array_filter($_lang_template)) {
		$share['lang_template'] = $_lang_template ? serialize($_lang_template) : '';
	}

	$share['image'] = !empty($share['image_1']) ? $share['image_1'] : (is_array($share['image']) ? $share['image'][0] : $share['image']);

	return $share;
}

?>