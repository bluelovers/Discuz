<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_plugin.php 20818 2011-03-04 08:21:11Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pluginkey = 'space_'.$_G['gp_op'];
$navtitle = $_G['setting']['plugins'][$pluginkey][$_G['gp_id']]['name'];

include pluginmodule($_G['gp_id'], $pluginkey);
include template('home/space_plugin');

?>