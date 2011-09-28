<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//error_reporting(E_ALL ^E_NOTICE ^E_STRICT);

include_once dirname(__FILE__).'/./class_sco_plugin_inc.php';

if (empty($_G['gp_cpmod'])) {

	$_cpanel = new plugin_sco_plugin_inc();
	$_cpanel
		->init(CURMODULE)
		->set(array(
			'module' => &$module,
		))
		->run()
	;

} else {

	$_cpanel = plugin_sco_plugin_inc::mod($_G['gp_cpmod'], CURMODULE);

	$_cpanel
		->set(array(
			'op' => $_G['gp_op'],
			'module' => &$module,
		))
		->run()
	;

}

?>