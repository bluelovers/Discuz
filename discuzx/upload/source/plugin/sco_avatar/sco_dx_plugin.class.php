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

		$this->attr['setting_source'] = &$_G['cache']['plugin'][$this->identifier];
		$this->attr['setting'] = $this->attr['setting_source'];
	}

	/**
	 * get identifier from __CLASS__
	 **/
	function _get_identifier($method) {
		$a = explode('::', $method);
		return array_pop($a);
	}
}

?>