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

}

?>