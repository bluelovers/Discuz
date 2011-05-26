<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_plugin.php 22486 2011-05-10 03:41:02Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pluginkey = 'portalcp';
$navtitle = $_G['setting']['plugins'][$pluginkey][$_G['gp_id']]['name'];

include pluginmodule($_G['gp_id'], $pluginkey);

include template('portal/portalcp_plugin');

?>