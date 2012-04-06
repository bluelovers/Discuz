<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: plugin.php 24651 2011-09-29 08:49:11Z monkey $
 */

define('APPTYPEID', 127);
define('CURSCRIPT', 'plugin');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$mod = htmlspecialchars(!empty($_GET['mod']) ? $_GET['mod'] : (!empty($_POST['mod']) ? $_POST['mod'] : ''));

$cachelist = array('plugin');

$discuz->cachelist = $cachelist;
$discuz->init();

if(!empty($_G['gp_id'])) {
	list($identifier, $module) = explode(':', $_G['gp_id']);
	$module = $module !== NULL ? $module : $identifier;
}
$mnid = 'plugin_'.$identifier.'_'.$module;
$pluginmodule = isset($_G['setting']['pluginlinks'][$identifier][$module]) ? $_G['setting']['pluginlinks'][$identifier][$module] : (isset($_G['setting']['plugins']['script'][$identifier][$module]) ? $_G['setting']['plugins']['script'][$identifier][$module] : array('adminid' => 0, 'directory' => preg_match("/^[a-z]+[a-z0-9_]*$/i", $identifier) ? $identifier.'/' : ''));

if(empty($identifier) || !preg_match("/^[a-z0-9_\-]+$/i", $module) || !in_array($identifier, $_G['setting']['plugins']['available'])) {
	showmessage('plugin_nonexistence');
} elseif($pluginmodule['adminid'] && ($_G['adminid'] < 1 || ($_G['adminid'] > 0 && $pluginmodule['adminid'] < $_G['adminid']))) {
	showmessage('plugin_nopermission');
} elseif(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$pluginmodule['directory'].$module.'.inc.php'))) {
	showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
}

define('CURMODULE', $identifier);
runhooks();

include DISCUZ_ROOT.$modfile;

?>