<?php

/**
 * @author bluelovers
 **/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * load language file
 **/
function loadlang($file = 'template', $path = '', $source = 'source/language') {
	//TODO:add i18n switch
	if (!is_array($path)) {
		$path = explode('/', $path);
	}

	array_push_array($path, explode('/', $file));
	$file = array_pop($path);

	$source = rtrim(is_array($source) ? implode('/', $source) : $source, '/');
	$path = rtrim(is_array($path) ? implode('/', $path) : $path, '/');

	$ret = '';
	if ($source) $ret .= $source.'/';
	if ($path) $ret .= $path.'/';
	$ret .= 'lang_'.$file.'.php';

	// 忽略找不到檔案時的錯誤
	$_lang = include_file(DISCUZ_ROOT.'./'.$ret, true, 1, 1);

	return $_lang;
}

/**
 * merge lang array with load language file
 **/
function lang_merge(&$lang, $loadlang, $index = 'lang') {
	$lang = empty($lang) ? array() : $lang;
	$loadlang = is_array($loadlang) ? $loadlang : array($loadlang);

	$_lang = call_user_func_array('loadlang', $loadlang);
 	// 修正 array_merge($lang, null) 會強制為空的問題
	if (!empty($_lang) && !empty($_lang[$index]))
		$lang = array_merge($lang, $_lang[$index]);

	return $lang;
}

/**
 * Push one or more array onto the end of array
 *
 * @param &$array
 * @param $args
 **/
function array_push_array(&$array, $args) {
	$args = func_get_args();
	array_shift($args);

	if (count($args)) {
	    foreach ($args as $v) {
	    	if (!empty($v)) {
				foreach ($v as $r) {
					array_push($array, $r);
				}
			}
		}
	}
}

/**
 * Returns an array of all runtime defined variables
 *
 * @param $varList
 * @param $excludeList
 * @example get_runtime_defined_vars(get_defined_vars(), array('b'));
 * @example get_runtime_defined_vars(get_defined_vars());
 **/
function get_runtime_defined_vars(array $varList, $excludeList = array()) {
	if ($varList) {
		$excludeList = array_merge((array )$excludeList, array('GLOBALS', '_FILES',
			'_COOKIE', '_POST', '_GET', '_SERVER'));
		$varList = array_diff_key((array )$varList, array_flip($excludeList));
	}
	return $varList;
}

/**
 * statement includes and evaluates the specified file.
 *
 * @param $filename
 * @param bool - return runtime_defined_vars
 * @param bool - show error
 *
 * @return array
 **/
function include_file() {
	if (is_file(func_get_arg(0))) {

		// for discuz use
		if (true === func_get_arg(3) || 1 === func_get_arg(3)) {
			// 防止模板檔中使用到 $_G 而造成錯誤
			global $_G;
		}

		include func_get_arg(0);
		if (true === func_get_arg(1) || 1 === func_get_arg(1)) {
			return get_runtime_defined_vars(get_defined_vars(), array(
				'_G',
			));
		}
	// 追加忽略找不到檔案時的錯誤訊息開關
	} elseif (!func_get_arg(2)) {
		throw new Exception('PHP Warning: include_file(): Filename cannot be empty or not exists!!');
	}

	return array();
}

/**
 * 推薦搭配用於載入 function library
 */
function include_file_once() {
	static $_cahce_include;

	if (isset($_cahce_include[func_get_arg(0)])) return $_cahce_include[func_get_arg(0)];

	if (is_file(func_get_arg(0))) {

		// for discuz use
		if (
			func_num_args() > 3
			&& is_true(func_get_arg(3))
		) {
			// 防止模板檔中使用到 $_G 而造成錯誤
			global $_G;
		}

		$_cahce_include[func_get_arg(0)] = include_once(func_get_arg(0));

		if (func_num_args() > 1 && is_true(func_get_arg(1))) {
			return get_runtime_defined_vars(get_defined_vars(), array(
				'_G',
				'_cahce_include',
			));
		}

		return $_cahce_include[func_get_arg(0)];

	// 追加忽略找不到檔案時的錯誤訊息開關
	} elseif (!func_get_arg(2)) {
		throw new Exception('PHP Warning: include_file(): Filename cannot be empty or not exists!!');
	}

	$_cahce_include[func_get_arg(0)] = false;

	return false;
}

/**
 * check user agent accept encoding gzip
 */
function getaccept_encoding_gzip() {
	if (!defined('HTTP_USER_AGENT_GZIP')) {
		$gzip_compress = false;
		if (strstr($_SERVER['HTTP_USER_AGENT'], 'compatible')) {
			if (extension_loaded('zlib')) {
				$gzip_compress = true;
			}
		} elseif (strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			if (extension_loaded('zlib')) {
				$gzip_compress = true;
			}
		}
		define('HTTP_USER_AGENT_GZIP', $gzip_compress);
	}
	return HTTP_USER_AGENT_GZIP;
}

function _html_fileplus($key = '', $iscss = 0, $rethtml = 0) {
	global $_G;

	static $_init;
	if (empty($_init) || empty($key)) {
		$_init = true;

		foreach(discuz_core::$plugin_support['jscache'] as $_k => $_v) {
			if (empty($_v['name'])) $_v['name'] = $_k;
			if (empty($_v['file'])) $_v['file'] = $_v['name'];
			if (empty($_v['file_develop'])) $_v['file_develop'] = $_v['file'];
			if (empty($_v['path'])) $_v['path'] = '';
			if (empty($_v['base'])) $_v['base'] = 'static/js/';

			if ($_G['setting']['jspath'] == 'data/cache/') {
				$_v['file_return'] = $_G['setting']['jspath'].$_v['name'].VERHASH_GZIP_JS;
			} else {
				$_v['file_return'] = $_v['base']
					.$_v['path']
					.(DISCUZ_DEBUG ? $_v['file_develop'] : $_v['file'])
				;
			}

			$_v['file_return'] .= '?'.VERHASH;

			$_v['file_source'] = $_v['base']
				.$_v['path']
				.(DISCUZ_DEBUG ? $_v['file_develop'] : $_v['file'])
			;

			discuz_core::$plugin_support['jscache'][$_k] = $_v;
		}
	}

	if (empty($key)) {
		return discuz_core::$plugin_support['jscache'];
	} else {
		if (empty(discuz_core::$plugin_support['jscache'][$key])) {

		} else {
			$ret = discuz_core::$plugin_support['jscache'][$key]['file_return'];
			if ($rethtml) $ret = '<script type="text/javascript" src="' . $ret . '"></script>';
			return $ret;
		}
	}
}

function is_true($var) {
	return ($var === true || $var === 1) ? true : false;
}

?>