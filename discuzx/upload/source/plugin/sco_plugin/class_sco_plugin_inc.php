<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class _sco_dx_plugin_inc extends _sco_dx_plugin {

	function &init($identifier) {
		$this->_init($identifier);

		$this->_this(&$this);

		$this->_fix_plugin_setting();

		return $this;
	}

	function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
		return submitcheck($var, $allowget, $seccodecheck, $secqaacheck);
	}

	function &mod($mod, $identifier = '') {
		if (empty($identifier)) $identifier = self::identifier;

		include_once libfile('mod/'.$mod, 'plugin/'.$identifier);

		$class = 'plugin_'.$identifier.'_'.$mod;
		$self = new $class();

		$self
			->init($identifier)
			->set(array(
				'mod' => $mod,
			))
		;

		return $self;
	}

}

?>