<?php

/**
 * @author bluelovers
 */

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_libfile', '_eFunc_libfile');

function _eFunc_libfile($_EVENT, &$ret, $root, $force = 0) {
	static $__func;

	// 檢查是否支援 Scorpio_File，如不支援時則產生替代函數
	if (!discuz_core::$plugin_support['scofile'] && class_exists('scofile')) {
		discuz_core::$plugin_support['scofile'] = true;
	} elseif (!$__func) {
		$__func = create_function('$fn, $base', '
			$base = str_replace(array(\'\\\\\', \'//\'), \'/\', $base);
			$fn = str_replace(array(\'\\\\\', \'//\'), \'/\', $fn);

			if (stripos($fn, $base) === 0) return substr($fn, strlen($base));

			return $fn;
		');
	}

	// 整理路徑
	if (discuz_core::$plugin_support['scofile']) {
		$file = scofile::remove_root(&$ret, $root);
	} else {
		$file = $__func(&$ret, $root);
	}

	// 緩存是否執行過(每個檔案只執行一次)
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

Scorpio_Hook::add('Tpl_Func_hooktags:Before', '_eTpl_Func_hooktags_Before');

function _eTpl_Func_hooktags_Before($_EVENT, &$hook_data, $hookid, $key) {
	global $_G;

	$_varhash = VERHASH;

	if ($hookid == 'global_header_seohead') {
		$ss = <<<EOF
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.pack.js?{$_varhash}"></script>
<script type="text/javascript">jQuery.noConflict();</script>
EOF
;
/*
?><?
*/

		$hook_data .= $ss;
	} elseif ($hookid == 'global_header_javascript') {
		$ss = <<<EOF
<script type="text/javascript" src="{$path}extensions/js/common.js?{$_varhash}"></script>
EOF
;
/*
?><?
*/

		$hook_data .= $ss;
	}
}

Scorpio_Hook::add('Func_cachedata:After', '_eFunc_cachedata_After');

/**
 * 修正當清空快取目錄 與 SQL 快取時 就會變成除非進入後台更新緩存 否則將無法產生緩存的 BUG
 **/
function _eFunc_cachedata_After($_EVENT, $conf) {

	// 停止呼叫事件
	Scorpio_Event::instance('Func_cachedata:Before_get_syscache')->stop();

	extract($conf, EXTR_REFS);

	static $_loadedcache = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	$caches = array();
	foreach ($cachenames as $k) {
		if(!isset($_loadedcache[$k])) {
			$k2 = $k;

			// 防止造成無法取得緩存
			if (preg_match('/^(usergroup|threadsort|admingroup|style)_/', $k, $m)) {
				$k2 = $m[1].'s';
			} elseif (preg_match('/^(diytemplatename)/', $k, $m)) {
				$k2 = $m[1];

			// modreasons, userreasons 皆由 modreasons 控制
			} elseif ($k == 'modreasons' || $k == 'userreasons') {
				$k2 = 'modreasons';

			// pluginsetting 由 plugin 控制
			} elseif ($k == 'pluginsetting') {
				$k2 = 'plugin';

			// domain 由 setting 控制
			} elseif ($k == 'domain') {
				$k2 = 'setting';

			// array('threadtableids', 'threadtable_info', 'posttable_info', 'posttableids') 由 split 控制
			} elseif (in_array($k, array('threadtableids', 'threadtable_info', 'posttable_info', 'posttableids'))) {
				$k2 = 'split';
			}

			$caches[] = $k2;
			$caches_load[] = $k;
			$_loadedcache[$k] = true;
		}
	}

	// 整理過濾處理過的 Array
	$caches = array_unique($caches);
	$caches_load = array_unique($caches_load);

	if(!empty($caches)) {
		@include_once libfile('function/cache');

		updatecache($caches);
		loadcache($caches_load, true);

		$cachedata = cachedata($caches_load);
		foreach($cachedata as $_k => $_v) {
			$data[$_k] = $_v;
		}
	}

	// 啟用呼叫事件
	Scorpio_Event::instance('Func_cachedata:Before_get_syscache')->play();
}

Scorpio_Hook::add('Func_cachedata:Before_get_syscache', '_eFunc_cachedata_Before_get_syscache');

/**
 * 如果在 ./data/cache 中沒有緩存的項目，則自動更新 SQL 快取
 * 達到只要刪除 ./data/cache 中的緩存就能夠更新緩存的效果
 **/
function _eFunc_cachedata_Before_get_syscache($_EVENT, $conf) {
	extract($conf, EXTR_REFS);

	static $_del_cache = array();

	if($isfilecache && $cachenames) {
		/*
		@include_once libfile('function/cache');
		updatecache($cachenames);
		*/

		// 略過不清除的緩存
		static $_skips;
		if (!isset($_skips)) {
			$_skips = array(
				'founder',

				'plugin', 'pluginsetting',

				'threadsort',

				'usergroup', 'admingroup',

				'threadsort',
				'style',

				'diytemplatename',

				'modreasons', 'userreasons', 'modreasons',

				'domain',

				'split', 'threadtableids', 'threadtable_info', 'posttable_info', 'posttableids',
			);

			$_skips = implode('|', $_skips);
			$_skips = '/^('.$_skips.')/';
		}

		foreach ($cachenames as $k) {
			if(!isset($_del_cache[$k])
				&& !preg_match($_skips, $k)
			) {
				DB::query("DELETE FROM ".DB::table('common_syscache')." WHERE cname = '$k' LIMIT 1");
			}

			$_del_cache[$k] = true;
		}
	}
}

Scorpio_Hook::add('Class_discuz_core::_init_input:After', '_eClass_discuz_core__init_input_After');

function _eClass_discuz_core__init_input_After($_EVENT, $discuz) {
	/**
	 * 如果 mod=post&action=albumphoto 則 inajax = 1
	 *
	 * @example
	 * forum.php?mod=post&action=albumphoto&aid=1&inajax=1&ajaxtarget=albumphoto
	 * forum.php?mod=post&action=albumphoto&aid=1&ajaxtarget=albumphoto
	 **/
	if (!$discuz->var['inajax']
		&& $discuz->var['gp_mod'] == 'post'
		&& $discuz->var['gp_action'] == 'albumphoto'
	) {
		$discuz->var['inajax'] = 1;
	}
}

?>