<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_sco_cpanel_setting_subject extends plugin_sco_cpanel {

	function strlen($str) {
		if(strtolower(CHARSET) == 'utf-8') return self::utf8_strlen($str);

		return dstrlen($str);
	}

	function utf8_strlen($str) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
	}
}

?>