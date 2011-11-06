<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_attach extends _sco_dx_plugin {

	function __construct() {
		$this->identifier = $this->_get_identifier(__CLASS__);
	}

}

class plugin_sco_attach_forum extends plugin_sco_attach {



}

?>