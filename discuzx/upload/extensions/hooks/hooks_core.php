<?php

/**
 * @author bluelovers
 **/

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Func_libfile', '_eFunc_libfile');

function _eFunc_libfile($_EVENT, &$ret, $root, $force = 0) {
	static $__func;

	// 檢查是否支援 Scorpio_File，如不支援時則產生替代函數
	if (!discuz_core::$plugin_support['scofile'] && class_exists('scofile')) {
		discuz_core::$plugin_support['scofile'] = true;
	} elseif (!discuz_core::$plugin_support['scofile'] && !$__func) {
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
			case 'source/function/function_core.php':
				@include_once libfile('function/core', 'source', 'extensions/');
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
			} elseif ($k == 'domain'
		 		|| $k == 'adminmenu'
			) {
				$k2 = 'setting';

			// array('threadtableids', 'threadtable_info', 'posttable_info', 'posttableids') 由 split 控制
			} elseif (in_array($k, array('threadtableids', 'threadtable_info', 'posttable_info', 'posttableids'))) {
				$k2 = 'split';
			}

			// 如果執行過 $k2 直接跳過處理
			if (0 && isset($_loadedcache[$k2])) {
				continue;
			}

			$caches[] = $k2;
			$caches_load[] = $k;
			$_loadedcache[$k] = true;

			$_loadedcache[$k2] = true;
		}
	}

	/**
	 * 預先載入 $_G['setting']
	 * 防止 $_G['setting'] 為空
	 */
	if (empty($GLOBALS['_G']['setting'])) {
		$GLOBALS['_G']['setting'] = $data['setting'];
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

	if(!empty($GLOBALS['_G']['setting']) && $isfilecache && $cachenames) {
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

				'setting',

				'split', 'threadtableids', 'threadtable_info', 'posttable_info', 'posttableids',
			);

			$_skips = implode('|', $_skips);
			$_skips = '/^('.$_skips.')/';
		}

		// 只刪除指定時間以前的緩存
		$cache_dateline = 24;
		$cache_dateline = TIMESTAMP - $cache_dateline * 3600;

		foreach ($cachenames as $k) {
			if(!isset($_del_cache[$k])
				&& !preg_match($_skips, $k)
			) {
				DB::query("DELETE FROM ".DB::table('common_syscache')." WHERE cname = '$k' AND dateline < {$cache_dateline} LIMIT 1");
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

Scorpio_Hook::add('Func_output:Before_rewrite_content_echo', '_eFunc_output_Before_rewrite_content_echo');
Scorpio_Hook::add('Func_output_ajax:Before_rewrite_content_echo', '_eFunc_output_Before_rewrite_content_echo');

/**
 * 輸出時將帳號名稱轉為暱稱
 **/
function _eFunc_output_Before_rewrite_content_echo($_EVENT, $_conf) {
	if(defined('IN_MODCP') || defined('IN_ADMINCP')) return;

	extract($_conf, EXTR_REFS);

	$_func = __FUNCTION__.'_callback';

	$regex_showname = '[^<\>\'"]+';

	$content = preg_replace_callback('/<a href\="(?<href>()home.php\?mod=space&(?:amp;)?(?:uid\=(?<uid>\d+)|username\=(?<username>[^&]+?)))"(?<extra>[^\>]*)\>(?<showname>'.$regex_showname.')<\/a/', $_func, $content);
}

function _eFunc_output_Before_rewrite_content_echo_callback($m) {
/*
Array
(
    [0] => <a href="home.php?mod=space&amp;uid=1">
    [1] =>
    [uid] => 1
    [2] => 1
    [username] =>
    [3] =>
    [extra] =>
    [4] =>
)
*/

	// 緩存資訊
	static $_user;

	// 初始化 $_uid
	$_uid = 0;

	// 將 href 內的 username 解碼
	$m['username'] = daddslashes(rawurldecode($m['username']));
	$m['uid'] = intval($m['uid']);

	// 判斷是否分析過 $m['uid']
	if ($m['uid'] && isset($_user['uid'][$m['uid']])) {
		$_uid = $m['uid'];

	// 判斷是否分析過 $m['username']
	} elseif (!empty($m['username']) && isset($_user['username'][$m['username']])) {
		$_uid = $_user['username'][$m['username']];

	// 如果存在 $m['uid']
	} elseif ($m['uid'] || !empty($m['username'])) {

		$_sql = $m['uid'] ? "uid='".$m['uid']."'" : "username='".$m['username']."'";

		$user = DB::fetch_first("SELECT mp.uid, mp.realname, mp.nickname, m.username FROM ".DB::table('common_member_profile')." mp, ".DB::table('common_member')." m WHERE mp.uid=m.uid AND m.{$_sql} LIMIT 1");

		if ($_uid = $user['uid']) {

			// 預先處理要顯示的名稱
			$user['showname'] = $user['showname'] ? $user['showname'] : (
				$user['nickname'] ? $user['nickname'] : ''
			);
			$user['showname'] = dhtmlspecialchars($user['showname'], ENT_QUOTES);

			// 如果一個以上的人使用相同名稱
			/*
			if (!empty($user['showname'])) {
				$_user['counter'][$user['showname']] += 1;
				//if ($_user['counter'][$user['showname']] > 1) $user['showname'] .= ($user['username'] != $user['showname']) ? '@'.$user['username'] : '#'.$_user['counter'][$user['showname']];
			}
			*/

			$_user['uid'][$_uid] = $user['showname'];
			$_user['username'][$user['username']] = $_uid;
			if (!empty($m['username']) && $user['username'] != $m['username']) $_user['username'][$m['username']] = $_uid;
		} else {
			// 失敗時緩存為 0
			if ($m['uid']) $_user['uid'][$m['uid']] = '';
			if (!empty($m['username'])) $_user['username'][$m['username']] = 0;
		}
	}

	// 如果成功查詢到 $_uid
	if ($_uid) {
		// 取得緩存
		$user = $_user['uid'][$_uid];

		$s = '';
		$s .= '<a href="'.$m['href'].'"';

		if (!empty($user)) {
			// 提示帳號名稱
			$s .= ' title="'.strip_tags($m['showname']).' ( '.strip_tags($user).' )"';
		}

		$s .= ''.$m['extra'].'>';

		if (!empty($user)) {
			$s .= $user;
			//if ($_user['counter'][$user] > 1) $s .= '@'.$m['showname'];
		} else {
			$s .= $m['showname'];
		}

		$s .= '</a';
	} else {
		// 失敗時回傳原有字串
		$s = $m[0];
	}

	return $s;
}

Scorpio_Hook::add('Class_discuz_core::_init_env:After', '_eClass_discuz_core__init_env_After');

function _eClass_discuz_core__init_env_After($_EVENT, $discuz) {
	if (defined('SUB_DIR')) return Scorpio_Hook::RET_SUCCESS;

	static $__func;

	if (!discuz_core::$plugin_support['scofile'] && class_exists('scofile')) {
		discuz_core::$plugin_support['scofile'] = true;
	} elseif (!discuz_core::$plugin_support['scofile']) {
		return Scorpio_Hook::RET_SUCCESS;
	}

	$_G = &$discuz->var;

	$doc_root = scofile::path($_SERVER["DOCUMENT_ROOT"]);
	$base = scofile::path(DISCUZ_ROOT);

	$root = scofile::path('/'.scofile::remove_root($base, $doc_root));

	$sub_path = scofile::path(scofile::remove_root($_G['siteroot'], $root));

	if ($root && $sub_path
		&& ($_G['siteroot'] == $root.$sub_path)
	) {
		$urlbase = preg_replace('/'.preg_quote($_G['siteroot'], '/').'$/', '', $_G['siteurl']);

		$_G['siteurl'] = $urlbase.$root;
		$_G['siteroot'] = $root;
	}
/*
	echo '<pre>';
	print_r(array(
		$base,
		$doc_root,
		$_G['siteurl'],
		$_G['siteroot'],
		$root,
		$sub_path,
		$urlbase,
		$root.$sub_path,
		($_G['siteroot'] == $root.$sub_path) ? 1 : 0,
		'/'.preg_quote($_G['siteroot'], '/').'$/',
	));

	exit();
*/
}

/* func */

function &htmldom($content) {
	//TODO:simple_html_dom
	@include_once libfile('simple_html_dom', 'libs/simple_html_dom', 'extensions/');

	$dom = new simple_html_dom;
	$dom->load($content, true);

	return $dom;
}

function curl($url) {
	//TODO:Scorpio cURL
	include_once libfile('Curl', 'libs/scophp/Scorpio/libs/Helper/', 'extensions/');
	if (!class_exists('scocurl')) eval("class scocurl extends Scorpio_Helper_Curl_Core {}");

//	scocurl::instance($url)->setopt(CURLOPT_FOLLOWLOCATION, true)->setopt(CURLOPT_HEADER, true)->setopt(CURLOPT_COOKIEJAR, true)->exec();
//	$c = scocurl::_self()->getExec(true);
//	scocurl::_self()->close();

//echo $url;

	scocurl::instance($url)->setopt(array(
//		CURLOPT_FOLLOWLOCATION => true,
//		CURLOPT_HEADER => true,
//		CURLOPT_COOKIEJAR => true,
	))->exec();
	$c = scocurl::_self()->close()->getExec(1);

//	echo '<pre>';
//	print_r($c);
//
//	dexit();

	return $c;
}

?>