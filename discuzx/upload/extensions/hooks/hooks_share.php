<?php

/**
 * @author bluelovers
 */

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