<?php

/**
 * @author bluelovers
 */

if (!discuz_core::instance()->plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_libfile', '_eFunc_libfile');

function _eFunc_libfile($_EVENT, &$ret, $root, $force = 0) {
//	$root	= Scorpio_File::path($root);
//	$ret	= Scorpio_File::file($ret);
//
//	if (strpos($ret, $root) === 0) $file = substr($ret, strlen($root));

	static $__func;

	if (!discuz_core::instance()->plugin_support['Scorpio_File'] && class_exists('Scorpio_File')) {
		discuz_core::instance()->plugin_support['Scorpio_File'] = true;
	} elseif (!$__func) {
		$__func = create_function('$fn, $base', '
			$base = str_replace(array(\'\\\\\', \'//\'), \'/\', $base);
			$fn = str_replace(array(\'\\\\\', \'//\'), \'/\', $fn);

			if (stripos($fn, $base) === 0) return substr($fn, strlen($base));

			return $fn;
		');
	}
	$file = Scorpio_File::remove_root(&$ret, $root);

	static $list;

	if ($force || !isset($list[$file])) {
		if (!$force) $list[$file] = $ret;

		switch($file) {
			case 'source/function/function_cache.php':
			case 'source/function/cache/cache_styles.php':
			case 'source/class/class_template.php':
				@include_once libfile('hooks/cache', '', 'extensions/');
				break;
			case 'source/function/function_share.php':
			case 'source/include/spacecp/spacecp_share.php':

			case 'source/function/function_feed.php':
				@include_once libfile('hooks/share', '', 'extensions/');
				break;
			case 'source/function/function_discuzcode.php':
				@include_once libfile('hooks/discuzcode', '', 'extensions/');
				break;
			case 'source/function/function_home.php':
				@include_once libfile('hooks/home', '', 'extensions/');
				break;
			case 'forum.php':
			case 'source/module/forum/forum_viewthread.php':
				@include_once libfile('hooks/forum', '', 'extensions/');
				break;
			case 'group.php':
			case 'source/module/group/group_index.php':
				@include_once libfile('hooks/group', '', 'extensions/');
				break;
			default:
//				dexit($file);

				break;
		}
	}
}

?>