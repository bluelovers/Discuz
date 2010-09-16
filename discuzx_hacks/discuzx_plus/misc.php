<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc.php 569 2010-09-03 09:33:26Z yexinhao $
 */

define('APPTYPEID', 100);
define('CURSCRIPT', 'misc');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('swfupload', 'getcode', 'so', 'initsys');

$modcachelist = array();

$mod = getgpc('mod');
$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;

$cachelist = array();
if(isset($modcachelist[$mod])) {
	$cachelist = $modcachelist[$mod];
}

$discuz->cachelist = $cachelist;
$discuz->init();

define('CURMODULE', $mod);
require DISCUZ_ROOT.'./source/module/misc/misc_'.$mod.'.php';

?>