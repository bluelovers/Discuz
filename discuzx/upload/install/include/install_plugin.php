<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install_plugin.php 29038 2012-03-23 06:22:39Z songlixin $
 */

define('IN_COMSENZ', TRUE);
define('IN_ADMINCP', TRUE);

chdir('../../');

require_once './source/class/class_core.php';

$discuz = & discuz_core::instance();
$discuz->init_cron = false;
$discuz->init_session = false;
$discuz->init();

if($_G['gp_key'] !== md5($_G['setting']['authkey'].$_SERVER['REMOTE_ADDR'])) {
	exit;
}

$plugins = array('qqconnect', 'cloudstat', 'soso_smilies', 'cloudsearch', 'security', 'xf_storage');

require_once libfile('function/plugin');
require_once libfile('function/admincp');
require_once libfile('function/cache');

foreach($plugins as $pluginid) {
	$importfile = DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/discuz_plugin_'.$pluginid.'.xml';
	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin', $importtxt);
	if(plugininstall($pluginarray)) {
		if(!empty($pluginarray['installfile']) && file_exists(DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/'.$pluginarray['installfile'])) {
			@include_once DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/'.$pluginarray['installfile'];
		}
	}
}

?>