<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.php 16805 2010-09-15 03:56:11Z zhangguosheng $
 */

define('APPTYPEID', 100);
define('CURSCRIPT', 'misc');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('seccode', 'secqaa', 'initsys', 'invite', 'faq', 'report', 'swfupload', 'manyou', 'stat', 'ranklist');

$modcachelist = array(
	'ranklist' => array('forums'),
);

$mod = getgpc('mod');
$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;

$cachelist = array();
if(isset($modcachelist[$mod])) {
	$cachelist = $modcachelist[$mod];
}

$discuz->cachelist = $cachelist;

switch ($mod) {
	case 'secqaa':
	case 'manyou':
	case 'seccode':
		$discuz->init_cron = false;
		$discuz->init_session = false;
		break;
	case 'updatecache':
		$discuz->init_cron = false;
		$discuz->init_session = false;
	case 'ranklist':
		define('CLOSEBANNED', 1);
	default:
		break;
}

$discuz->init();

define('CURMODULE', $mod);
require DISCUZ_ROOT.'./source/module/misc/misc_'.$mod.'.php';

?>