<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_sco_cpanel_setting_subject extends plugin_sco_cpanel {

	function _my_strlen($str) {
		if(strtolower(CHARSET) == 'utf-8') return self::_my_utf8_strlen($str);

		return dstrlen($str);
	}

	function _my_utf8_strlen($str) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
	}

	/**
	 * 預設行為
	 */
	function on_op_default() {
		/*
		$this->on_op_list_fups();
		*/

		return $this;
	}
}

?>