<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home.php 21671 2011-04-07 06:21:13Z zhangguosheng $
 */

define('APPTYPEID', 1);
define('CURSCRIPT', 'home');

if(!empty($_GET['mod']) && ($_GET['mod'] == 'misc' || $_GET['mod'] == 'invite')) {
	define('ALLOWGUEST', 1);
}

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = & discuz_core::instance();

$cachelist = array('magic','userapp','usergroups', 'diytemplatenamehome');
$discuz->cachelist = $cachelist;
$discuz->init();

$space = array();

$mod = getgpc('mod');
if(!in_array($mod, array('space', 'spacecp', 'misc', 'magic', 'editor', 'invite', 'task', 'medal', 'rss'))) {
	$mod = 'space';
	$_GET['do'] = 'home';
}

define('CURMODULE', $mod);
runhooks();

require_once libfile('home/'.$mod, 'module');


?>