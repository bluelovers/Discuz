<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_cache.php 22398 2011-05-05 11:16:36Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updatecache($cachename = '') {

	$updatelist = empty($cachename) ? array() : (is_array($cachename) ? $cachename : array($cachename));

	// bluelovers
	$lostcaches = array();
	// bluelovers

	if(!$updatelist) {

		// bluelovers
		// 初始化 $updatelist
		$updatelist = array();
		// bluelovers

		// 獨立執行 setting
		@include_once libfile('cache/setting', 'function');
		build_cache_setting();
		$cachedir = DISCUZ_ROOT.'./source/function/cache';
		$cachedirhandle = dir($cachedir);
		while($entry = $cachedirhandle->read()) {
			// 尋找 $cachedir 下所有的 cache script 但是略過 setting
			if(!in_array($entry, array('.', '..')) && preg_match("/^cache\_([\_\w]+)\.php$/", $entry, $entryr) && $entryr[1] != 'setting' && substr($entry, -4) == '.php' && is_file($cachedir.'/'.$entry)) {
				// 簡化重複代碼
				$updatelist[] = $entryr[1];
			}
		}
	}

	if ($updatelist) {

		// bluelovers
		/**
		 * 將 setting 推送到最前面
		 * 避免同時更新緩存時，嘗試讀取 setting 卻尚未載入的問題
		 **/
		if (in_array('setting', $updatelist) && count($updatelist) > 1) {
			$updatelist = array_diff($updatelist, array('setting'));
			array_unshift($updatelist, 'setting');
		}
		// bluelovers

		foreach($updatelist as $entry) {
			@include_once libfile('cache/'.$entry, 'function');
			// bluelovers
			if (function_exists('build_cache_'.$entry)) {
			// bluelovers
				call_user_func('build_cache_'.$entry);
			// bluelovers
			} else {
				$lostcaches[] = $entry;
			}
			// bluelovers
		}
	}

	// bluelovers
	// 處理缺漏的 cache script
	if ($lostcaches && discuz_core::$plugin_support['Scorpio_Event']) {
		Scorpio_Event::instance('Func_' . __FUNCTION__ . ':After_lostcaches')
			->run(array(array(
				'cachenames'	=> &$lostcaches,
		)));
	}
	// bluelovers

}

function writetocache($script, $cachedata, $prefix = 'cache_', $dir = '') {
	global $_G;

	$dir = empty($dir) ? $dir : DISCUZ_ROOT.'./data/cache/';
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if($fp = @fopen("$dir$prefix$script.php", 'wb')) {

		// bluelovers
		// 附加 cache 檔的註解
		static $_timeoffset;
		($_timeoffset === null) && $_timeoffset = getglobal('setting/timeoffset');
		$_now = time();
		$_head_add = "\n//Date: ".date('Y-m-d\TH:i:sO', $_now). ' ('.dgmdate($_now, 'Y-m-d h:i:s', $_timeoffset).')';
		// bluelvoers

		fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n//Identify: ".md5($prefix.$script.'.php'.$cachedata.$_G['config']['security']['authkey']).$_head_add."\n\n$cachedata?>");
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
	}
}


function getcachevars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= "\$$key = ".arrayeval($val).";\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function smthumb($size, $smthumb = 50) {
	if($size[0] <= $smthumb && $size[1] <= $smthumb) {
		return array('w' => $size[0], 'h' => $size[1]);
	}
	$sm = array();
	$x_ratio = $smthumb / $size[0];
	$y_ratio = $smthumb / $size[1];
	if(($x_ratio * $size[1]) < $smthumb) {
		$sm['h'] = ceil($x_ratio * $size[1]);
		$sm['w'] = $smthumb;
	} else {
		$sm['w'] = ceil($y_ratio * $size[0]);
		$sm['h'] = $smthumb;
	}
	return $sm;
}

function arrayeval($array, $level = 0) {
	if(!is_array($array)) {
		return "'".$array."'";
	}
	if(is_array($array) && function_exists('var_export')) {
		return var_export($array, true);
	}

	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	if(is_array($array)) {
		foreach($array as $key => $val) {
			$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
			$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			if(is_array($val)) {
				$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
			} else {
				$evaluate .= "$comma$key => $val";
			}
			$comma = ",\n$space";
		}
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

function pluginsettingvalue($type) {
	$pluginsetting = $pluginvalue = array();
	@include DISCUZ_ROOT.'./data/cache/cache_pluginsetting.php';
	$pluginsetting = isset($pluginsetting[$type]) ? $pluginsetting[$type] : array();

	$varids = $pluginids = array();
	foreach($pluginsetting as $pluginid => $v) {
		foreach($v['setting'] as $varid => $var) {
			$varids[] = $varid;
			$pluginids[$varid] = $pluginid;
		}
	}
	if($varids) {
		$query = DB::query("SELECT pluginvarid, variable, value FROM ".DB::table('common_pluginvar')." WHERE pluginvarid IN (".dimplode($varids).")");
		while($plugin = DB::fetch($query)) {
			$values = (array)unserialize($plugin['value']);
			foreach($values as $id => $value) {
				$pluginvalue[$id][$pluginids[$plugin['pluginvarid']]][$plugin['variable']] = $value;
			}
		}
	}

	return $pluginvalue;
}

?>