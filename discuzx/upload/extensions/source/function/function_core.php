<?php

/**
 * @author bluelovers
 **/

/**
 * load language file
 **/
function loadlang($file, $path = '', $source = 'source/language') {
	if (!is_array($path)) {
		$path = explode('/', $path);
	}

	array_push_array($path, explode('/', $file));
	$file = array_pop($path);

	$source = (is_array($source) ? implode('/', $source) : $source);
	$path = (is_array($path) ? implode('/', $path) : $path);

	$ret = '';
	if ($source) $ret .= $source.'/';
	if ($path) $ret .= $path.'/';
	$ret .= 'lang_'.$file.'.php';

	$_lang = include_file(DISCUZ_ROOT.'./'.$ret, true);
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
 *
 * @return array
 **/
function include_file() {
	if (is_file(func_get_arg(0))) {
		include func_get_arg(0);
		if (true === func_get_arg(1) || 1 === func_get_arg(1)) {
			return get_runtime_defined_vars(get_defined_vars());
		}
	} else {
		throw new Exception('PHP Warning: include_file(): Filename cannot be empty or not exists!!');
	}

	return array();
}

?>