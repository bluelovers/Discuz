<?php

class _sco_dx_plugin {

	var $identifier = null;
	var $attr = array();

	function _init($identifier) {
		global $_G;

		if(!isset($_G['cache']['plugin'])) {
			loadcache('plugin');
		}

		$this->identifier = $identifier;
		$this->attr['identifier'] = &$this->identifier;

		$this->_get_setting($this);
	}

	/**
	 * get identifier from __CLASS__
	 **/
	function _get_identifier($method) {
		$a = explode('::', $method);
		return array_pop($a);
	}

	function _get_setting($identifier) {
		global $_G;

		if(!isset($_G['cache']['plugin'])) {
			loadcache('plugin');
		}

		if (is_object($identifier) && is_a($identifier, '_sco_dx_plugin')) {
			$identifier->attr['setting_source'] = &$_G['cache']['plugin'][$identifier->identifier];
			$identifier->attr['setting'] = $identifier->attr['setting_source'];

			return true;
		} elseif (isset($_G['cache']['plugin'][$identifier])) {
			return $_G['cache']['plugin'][$identifier];
		}

		return false;
	}
}

?>