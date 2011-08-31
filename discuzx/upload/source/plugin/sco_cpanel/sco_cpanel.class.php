<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_cpanel extends _sco_dx_plugin {

	function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
		return submitcheck($var, $allowget, $seccodecheck, $secqaacheck);
	}

	function cpheader() {
		cpheader();
	}

	function cpfooter() {
		cpfooter();
	}

}

?>