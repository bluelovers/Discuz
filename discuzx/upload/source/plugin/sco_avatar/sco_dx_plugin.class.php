<?php

class _sco_dx_plugin {

	var $identifier = null;
	var $attr = array();

	function _init($identifier) {
		$this->identifier = $identifier;
		$this->attr['identifier'] = &$this->identifier;

		$this->_get_setting($this);
	}

	/**
	 * get identifier from __CLASS__
	 **/
	function _get_identifier($method) {
		$a = explode('::', $method);
		$k = array_pop($a);

		// remove plugin_ from identifier
		if (strpos($k, 'plugin_') === 0) {
			$k = substr($k, strlen('plugin_'));
		}

		return $k;
	}

	function _get_setting($identifier) {
		global $_G;

		if(!isset($_G['cache']['plugin'])) {
			loadcache('plugin');
		}

		if (is_object($identifier) && is_a($identifier, '_sco_dx_plugin')) {
			$identifier->attr['setting_source'] = &$_G['cache']['plugin'][$identifier->identifier];
			$identifier->attr['setting'] = $identifier->attr['setting_source'];

			// 載入語言包
			foreach(array('script', 'template', 'install') as $type) {
				loadcache('pluginlanguage_'.$type);
				$identifier->attr['lang'][$type] = &$_G['pluginlanguage_'.$type][$identifier->identifier];
			}

			return true;
		} elseif (isset($_G['cache']['plugin'][$identifier])) {
			return $_G['cache']['plugin'][$identifier];
		}

		return false;
	}

	function template($file) {
		$args = func_get_args();

		if (strpos($file, ':') === false) {
			$args[0] = $this->identifier.':'.$file;
		}

		return call_user_func_array('template', $args);
	}
}

?>