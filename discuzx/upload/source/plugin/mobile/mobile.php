<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile.php 30858 2012-06-26 10:30:21Z congyushuai $
 */

define('IN_MOBILE_API', 1);

chdir('../../../');

require_once 'source/plugin/mobile/mobile.class.php';

$_GET['mobile'] = 'no';

$_GET['version'] = !empty($_GET['version']) ? $_GET['version'] : 1;
$_GET['version'] = $_GET['version'] > MOBILE_PLUGIN_VERSION ? MOBILE_PLUGIN_VERSION : $_GET['version'];

if(empty($_GET['module']) || empty($_GET['version']) || !preg_match('/^[\w\.]+$/', $_GET['module']) || !preg_match('/^[\d\.]+$/', $_GET['version'])) {
	mobile_core::result(array('error' => 'param_error'));
}

$apifile = 'source/plugin/mobile/api/'.$_GET['version'].'/'.$_GET['module'].'.php';

if(file_exists($apifile)) {
	require_once $apifile;
} else {
	if(intval($_GET['version']) > 1) {
		for($i = $_GET['version']; $i >= 1; $i--) {
			$apifile = 'source/plugin/mobile/api/'.$i.'/'.$_GET['module'].'.php';
			if(file_exists($apifile)) {
				$_GET['version'] = $i;
				require_once $apifile;
				break;
			} elseif($i==1 && !file_exists($apifile)) {
				mobile_core::result(array('error' => 'module_not_exists'));
			}
		}
	} else {
		mobile_core::result(array('error' => 'module_not_exists'));
	}
}

?>