<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('plugin_sco_cpanel', 'source/plugin/sco_cpanel');

$_cpanel = new plugin_sco_cpanel_threadsorts();


class plugin_sco_cpanel_threadsorts extends plugin_sco_cpanel {
	var $attr = array();

	function plugin_sco_cpanel_threadsorts() {

	}

	function set($attr) {
		$this->attr = $attr;

		return $this;
	}

	function run() {
		$method = 'op_'.$this->attr['operation'];
		$this->$method();
	}
}

?>