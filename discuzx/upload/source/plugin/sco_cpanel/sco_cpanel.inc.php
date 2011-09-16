<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//error_reporting(E_ALL ^E_NOTICE ^E_STRICT);

include_once dirname(__FILE__).'/./sco_cpanel.class.php';

if (empty($_G['gp_cpmod'])) {

	$_cpanel = new plugin_sco_cpanel();
	$_cpanel
		->init($plugin['identifier'])
		->set(array(
			'cpmod' => $_G['gp_cpmod'],
			'module' => &$module,
		))
		->run()
	;

} else {

	$_cpanel = plugin_sco_cpanel::mod('threadsorts', $plugin['identifier']);

	$_cpanel
		->set(array(
			'op' => $_G['gp_op'],
			'module' => &$module,
		))
		->run()
	;

}

?>