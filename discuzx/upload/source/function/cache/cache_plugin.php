<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_plugin.php 21704 2011-04-11 01:26:46Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_plugin() {
	global $importtxt;
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_plugin')." WHERE available='1'");

	$pluginsetting = array();
	while($plugin = DB::fetch($query)) {
		$dir = substr($plugin['directory'], 0, -1);
		$plugin['modules'] = unserialize($plugin['modules']);
		if($plugin['modules']['extra']['langexists']) {
			require_once libfile('function/plugin');
			require_once libfile('function/admincp');
			$file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($plugin['modules']['extra']['installtype'] ? '_'.$plugin['modules']['extra']['installtype'] : '').'.xml';
			$importtxt = @implode('', file($file));
			$pluginarray = getimportdata('Discuz! Plugin', 1, 1);
			if($pluginarray) {
				updatepluginlanguage($pluginarray);
			}
		}

		$queryvars = DB::query("SELECT * FROM ".DB::table('common_pluginvar')." WHERE pluginid='$plugin[pluginid]'");
		while($var = DB::fetch($queryvars)) {
			$data[$plugin['identifier']][$var['variable']] = $var['value'];
			if(in_array(substr($var['type'], 0, 6), array('group_', 'forum_'))) {
				$stype = substr($var['type'], 0, 5).'s';
				$type = substr($var['type'], 6);
				if($type == 'select') {
					foreach(explode("\n", $var['extra']) as $key => $option) {
						$option = trim($option);
						if(strpos($option, '=') === FALSE) {
							$key = $option;
						} else {
							$item = explode('=', $option);
							$key = trim($item[0]);
							$option = trim($item[1]);
						}
						$var['select'][] = array($key, $option);
					}
				}
				$pluginsetting[$stype][$plugin['identifier']]['name'] = $plugin['name'];
				$pluginsetting[$stype][$plugin['identifier']]['setting'][$var['pluginvarid']] = array('title' => $var['title'], 'description' => $var['description'], 'type' => $type, 'select' => $var['select']);
			}
		}
	}


	writetocache('pluginsetting', getcachevars(array('pluginsetting' => $pluginsetting)));

	save_syscache('plugin', $data);
}

?>