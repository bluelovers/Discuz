<?php

include_once 'sco_dx_plugin.class.php';

class plugin_sco_avatar extends _sco_dx_plugin {
	function plugin_sco_avatar() {
		$this->_init($this->_get_identifier(__METHOD__));
	}
}

class plugin_sco_avatar_home extends plugin_sco_avatar {
	function spacecp_avatar() {
		echo $this->identifier;
		dexit();
	}
}

?>