<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: api.php 10110 2010-05-07 01:05:11Z monkey $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

$modarray = array('js' => 'javascript/javascript', 'ad' => 'javascript/advertisement');

$mod = !empty($_GET['mod']) ? $_GET['mod'] : '';
if(empty($mod) || !array_key_exists($mod, $modarray)) {
	exit('Access Denied');
}

require_once './api/'.$modarray[$mod].'.php';

function loadcore() {
	global $_G;
	require_once './source/class/class_core.php';

	$discuz = & discuz_core::instance();
	$discuz->init_cron = false;
	$discuz->init_session = false;
	$discuz->init();
}

?>