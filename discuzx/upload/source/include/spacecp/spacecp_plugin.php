<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_plugin.php 22435 2011-05-09 02:09:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pluginkey = 'spacecp'.($op ? '_'.$op : '');
$navtitle = $_G['setting']['plugins'][$pluginkey][$_G['gp_id']]['name'];

include pluginmodule($_G['gp_id'], $pluginkey);
if(!$op || $op == 'credit') {
	include template('home/spacecp_plugin');
} elseif($op == 'profile') {
	$result = DB::fetch_first("SELECT * FROM ".DB::table('common_setting')." WHERE skey='profilegroup'");
	$defaultop = '';
	if(!empty($result['svalue'])) {
	    $profilegroup = unserialize($result['svalue']);
	}
	$operation = 'plugin';
	include template('home/spacecp_profile');
}

?>