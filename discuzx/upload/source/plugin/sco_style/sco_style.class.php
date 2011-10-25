<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_style extends _sco_dx_plugin {

	public function __construct() {
		$this->_init($this->_get_identifier(__CLASS__));

		$this->_this(&$this);
	}

}

class plugin_sco_style_home extends plugin_sco_style {

	function space_header_output() {
		if (!$this->_my_check_in_space_style()) {
			return;
		}
	}

	function space_header_diy_style_output() {
		return '<style id="diy_style_plugin">body { color: red; }</style>';
	}

	function space_header_diy_style() {
		return $this->space_header_diy_style_output();
	}

	function _my_check_in_space_style() {
		global $_G;

		$ret = false;

		if (
			(
				CURMODULE == 'space'
				&& $_G['setting']['homestatus']
			)
		) {
			$ret = true;
		}

		return $ret;
	}

}

?>