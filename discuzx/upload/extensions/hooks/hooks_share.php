<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.
	This is NOT a freeware, use is subject to license terms

	$HeadURL: svn://localhost/trunk/discuz_x/upload/extensions/hooks/hooks_core.php $
	$Revision: 109 $
	$Author: bluelovers$
	$Date: 2010-08-02 06:22:26 +0800 (Mon, 02 Aug 2010) $
	$Id: hooks_core.php 109 2010-08-01 22:22:26Z user $
*/


function _share_add($share) {
	$_lang_template = array();
	list($share['title_template'], $_lang_template['title_template']) = _mkshare($share['title_template']);
	list($share['body_template'], $_lang_template['body_template']) = _mkshare($share['body_template']);
	list($share['body_general'], $_lang_template['body_general']) = _mkshare($share['body_general']);

	if ($_lang_template = array_filter($_lang_template)) {
		$share['lang_template'] = $_lang_template ? serialize($_lang_template) : '';
	}

	return daddslashes($share);
}

function _mkshare($langkey) {
	$_lang_template = '';

	if (is_array($langkey) && count($langkey) >= 2) {
		if (lang($langkey[0], $langkey[1], null, null, true)) {
			$_lang_template = $langkey;
		}
		$langkey = call_user_func_array('lang', $langkey);
	} else {
		if (lang('feed', $langkey, null, null, true)) {
			$_lang_template = $langkey;

			$langkey = $langkey ? lang('feed', $langkey) : '';
		}
	}

	return array($langkey, $_lang_template);
}

Scorpio_Hook::add('Func_mkshare:Before', '_eFunc_mkshare_Before');

function _eFunc_mkshare_Before($share = array()) {
	$_lang_template = empty($share['lang_template']) ? array() : unserialize($share['lang_template']);
	if (is_array($_lang_template)) {
		foreach ($_lang_template as $_k_ => $_v_) {
			$share[$_k_] = is_array($_v_) ? call_user_func_array('lang', $_v_) : lang('feed', $_v_);
		}
	}
}

?>