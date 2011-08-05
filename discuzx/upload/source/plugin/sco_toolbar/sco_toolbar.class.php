<?php

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_toolbar extends _sco_dx_plugin {
	function plugin_sco_toolbar() {
		$this->_init($this->_get_identifier(__METHOD__));

		// set instance = $this
		$this->_this(&$this);
	}
}

?>