<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once dirname(__FILE__).'/./class_sco_dx_plugin_inc.php';

class plugin_sco_plugin_inc extends _sco_dx_plugin_inc {

	function &run() {
		$this->_setglobal('mod_lists', $this->_get_mod_list());

		parent::run();

		return $this;
	}

}

?>