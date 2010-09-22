<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_cache.php 12096 2010-06-29 01:35:04Z monkey $
 */

/**
* 更新緩存
* @param $cachename - 緩存文件名稱
* @return 返回緩存$_G數組
*/
function updatecache($cachename = '', $modulename = '') {

	global $_G;

	include_once DISCUZ_ROOT.'./source/discuzxplus_version.php';

	//note 定義緩存文件名和緩存內容，
	//note 注意：獨立型緩存（緩存文件中只有一種內容）位置要靠前
	//note 綜合型緩存（緩存文件含有多個緩存內容）位置要靠後，否則可能引起功能異常

	if(!empty($modulename)) {
		require_once libfile('cache/'.$modulename, 'include');
	} else {
		static $cachelist = array('setting', 'modulelist', 'template');

		$updatelist = empty($cachename) ? $cachelist : (is_array($cachename) ? $cachename : array($cachename));
		foreach($updatelist as $value) {
			getcachearray($value);
		}
	}

}

/**
* 更新配置
*/
function updatesettings() {
	global $_G;
	loadcache('setting', true);
	save_syscache('setting', $_G['setting']);
}

/**
* 書寫緩存
* @param $script - 腳本名稱
* @param $cachenames - 緩存類型
* @param $cachedata - 緩存數據，如果存在緩存數據則不需要chachenames
* @param $prefix - 緩存前綴
*/
function writetocache($script, $cachenames, $cachedata = '', $prefix = 'cache_') {
	global $_G;
	if(is_array($cachenames) && !$cachedata) {
		foreach($cachenames as $name) {
			$cachedata .= getcachearray($name, $script);
		}
	}

	$dir = DISCUZ_ROOT.'./data/cache/';
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if($fp = @fopen("$dir$prefix$script.php", 'wb')) {
		fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!".
			"\n//Created: ".date("M j, Y, G:i").
			"\n//Identify: ".md5($prefix.$script.'.php'.$cachedata.$_G['config']['security']['authkey'])."\n\n$cachedata?>");
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
	}
}

/**
* 填充緩存數據
* @param $cachename - 緩存類型
*/
function getcachearray($cachename, $script = '') {
	global $_G;

	$cols = '*';
	$conditions = '';
	$timestamp = TIMESTAMP;
	switch($cachename) {
		case 'setting':
			$table = 'common_setting';
			$conditions = "WHERE skey NOT IN ('siteuniqueid', 'mastermobile', 'bbrules', 'bbrulestxt', 'closedreason', 'creditsnotify', 'backupdir', 'custombackup', 'jswizard', 'maxonlines', 'modreasons', 'newsletter', 'welcomemsg', 'welcomemsgtxt', 'postno', 'postnocustom', 'customauthorinfo', 'domainwhitelist', 'ipregctrl', 'ipverifywhite', 'fastsmiley')";
			break;
		case 'modulelist':
			$table = 'common_module';
			$cols = 'mid, available, identifier';
			break;
		case 'template';
			$table = 'common_template t';
			$cols = 't.name, t.directory, t.mid, t.templateid, m.identifier';
			$conditions = " LEFT JOIN ".DB::table('common_module')." m ON m.mid = t.mid WHERE t.available='1'";
			break;
		case 'navlist';
			$table = 'common_nav';
			$cols = 'id, title, url, target, highlight, type';
			$conditions = "WHERE available='1'";
			break;
	}

	$data = array();
	if($cols && $table) {
		$query = DB::query("SELECT $cols FROM ".DB::table($table)." $conditions");
	}

	switch($cachename) {
		case 'setting':
			while($setting = DB::fetch($query)) {
				if($setting['skey'] == 'attachdir') {
					$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
					$setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? DISCUZ_ROOT.$setting['svalue'] : $setting['svalue']);
					$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
				} elseif($setting['skey'] == 'attachurl') {
					$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
				}

				$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
			}

			$data['cronnextrun'] = DB::result_first("SELECT nextrun FROM ".DB::table('common_cron')." WHERE available>'0' AND nextrun>'0' ORDER BY nextrun LIMIT 1");

			include DISCUZ_ROOT.'./config/config_ucenter.php';
			$data['ucenterurl'] = UC_API;
			//note 驗證碼
			$data['seccodedata'] = $data['seccodedata'] ? unserialize($data['seccodedata']) : array();
			if($data['seccodedata']['type'] == 2) {
				if(extension_loaded('ming')) {
					unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
						$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
						$data['seccodedata']['color'], $data['seccodedata']['size'],
						$data['seccodedata']['animator']);
				} else {
					$data['seccodedata']['animator'] = 0;
				}
			} elseif($data['seccodedata']['type'] == 99) {
				$data['seccodedata']['width'] = 32;
				$data['seccodedata']['height'] = 24;
			}

			//note 圖片水印
			$data['watermarktype'] = !empty($data['watermarktype']) ? unserialize($data['watermarktype']) : array();
			$data['watermarktext'] = !empty($data['watermarktext']) ? unserialize($data['watermarktext']) : array();
			$_G['setting']['version'] = $data['version'] = XPLUS_VERSION;
			foreach($data['watermarktype'] as $k => $v) {
				if($data['watermarktype'][$k] == 'text' && $data['watermarktext']['text'][$k]) {
					if($data['watermarktext']['text'][$k] && strtoupper(CHARSET) != 'UTF-8') {
						require_once libfile('class/chinese');
						$c = new Chinese(CHARSET, 'utf8');
						$data['watermarktext']['text'][$k] = $c->Convert($data['watermarktext']['text'][$k]);
					}
					$data['watermarktext']['text'][$k] = bin2hex($data['watermarktext']['text'][$k]);
					if(file_exists('static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k])) {
						$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k];
					} elseif(file_exists('static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k])) {
						$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k];
					} else {
						$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/'.$data['watermarktext']['fontpath'][$k];
					}
					$data['watermarktext']['color'][$k] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $data['watermarktext']['color'][$k]);
					$data['watermarktext']['shadowcolor'][$k] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $data['watermarktext']['shadowcolor'][$k]);
				} else {
					$data['watermarktext']['text'][$k] = '';
					$data['watermarktext']['fontpath'][$k] = '';
					$data['watermarktext']['color'][$k] = '';
					$data['watermarktext']['shadowcolor'][$k] = '';
				}
			}

			break;
		case 'modulelist':
			while($module = DB::fetch($query)) {
				$data[$module['identifier']]['available'] = intval($module['available']);
				$data[$module['identifier']]['mid'] = intval($module['mid']);
			}

			break;
		case 'template';
			while($template = DB::fetch($query)) {
				$data[$template['identifier']][$template['templateid']]['name'] = $template['name'];
				$data[$template['identifier']][$template['templateid']]['directory'] = $template['directory'];
			}
			break;
		case 'navlist';
			while($nav = DB::fetch($query)) {
				if($nav['type'] == '1') {
					$location = 'top';
				} elseif($nav['type'] == '2') {
					$location = 'bottom';
				}
				$data[$location][$nav['id']]['title'] = $nav['title'];
				$data[$location][$nav['id']]['url'] = $nav['url'];
				$data[$location][$nav['id']]['target'] = $nav['target'];
				$data[$location][$nav['id']]['highlight'] = $nav['highlight'];
			}

			break;
		default:
			while($datarow = DB::fetch($query)) {
				$data[] = $datarow;
			}
	}

	save_syscache($cachename, $data);
	return true;
}

/**
* 獲取緩存中的變量
* @param $data - 原始數據
* @param $type - 類型 VAR 表示變量 否則是常量
* @return 返回變量序列
*/
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

/**
* PHP數組轉換成代碼
* @param $array	- 數組
* @param $level	- 縮進用幾個製表符
* @return 返回數組的文本字符串
*/
function arrayeval($array, $level = 0) {
	if(!is_array($array)) {
		return "'".$array."'";
	}
	//note use defined function to export array
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

?>
