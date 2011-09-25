<?php

/**
 * @author bluelovers
 **/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

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

	static $include_once;

	if (
		!isset($include_once['extensions/source/function/function_core.php'])
	) {
		$include_once['extensions/source/function/function_core.php'] = true;

		include_once libfile('function/core', 'source', 'extensions/');
	}

	if ($force || !isset($list[$file])) {
		if (!$force) $list[$file] = $ret;

		switch($file) {
			case 'source/function/cache/cache_bbcodes.php':
			case 'source/function/cache/cache_bbcodes_display.php':
				include_file_once(libfile('cache/bbcodes', 'hooks', 'extensions/'), 0, 1);
			case 'source/class/class_template.php':
			case 'source/function/cache/cache_styles.php':
			case 'source/function/function_cache.php':
			case 'source/function/cache/cache_styles.php':
				include_file_once(libfile('hooks/cache', '', 'extensions/'), 0, 1);
				break;
			case 'source/function/function_share.php':
			case 'source/include/spacecp/spacecp_share.php':

			case 'source/function/function_feed.php':
				include_file_once(libfile('hooks/share', '', 'extensions/'), 0, 1);
				break;
			case 'source/function/function_discuzcode.php':
				include_file_once(libfile('hooks/discuzcode', '', 'extensions/'), 0, 1);
				break;
			case 'source/function/function_home.php':
				include_file_once(libfile('hooks/home', '', 'extensions/'), 0, 1);
				break;
			case 'forum.php':
			case 'source/module/forum/forum_viewthread.php':
				include_file_once(libfile('hooks/forum', '', 'extensions/'), 0, 1);
				break;
			case 'group.php':
			case 'source/module/group/group_index.php':
				include_file_once(libfile('hooks/group', '', 'extensions/'), 0, 1);
				break;
			case 'source/function/function_core.php':
				include_file_once(libfile('function/core', 'source', 'extensions/'), 0, 1);
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
	$ss = '';

	if ($hookid == 'global_header_seohead') {

		$ss .= "<base href=\"{$_G[siteurl]}\" />";

		if (!DISCUZ_DEBUG) {
			$_add = '.pack';
		} else {
			$_add = '';
		}

		$ss .= <<<EOF
<script type="text/javascript" src="http://code.jquery.com/jquery-latest{$_add}.js?{$_varhash}"></script>
<script type="text/javascript">jQuery.noConflict();</script>
EOF
;
/*
?><?
*/

		$ss .= _html_fileplus('jquery.lazy.js', 0, 1);

		$hook_data .= $ss;
	} elseif ($hookid == 'global_header_javascript') {
		$ss .= _html_fileplus('common_extensions.js', 0, 1);

		$ss .= '<script type="text/javascript">';
		$ss .= "var VERHASH_GZIP = '".VERHASH_GZIP."', VERHASH_GZIP_JS = '".VERHASH_GZIP_JS."';";

		$_G['setting']['post_subject_maxsize'] = max(80, intval($_G['setting']['post_subject_maxsize']));
		$_G['setting']['post_subject_maxsize_blog'] = max(80, intval($_G['setting']['post_subject_maxsize_blog']));

		$ss .= "var post_subject_maxsize = '".$_G['setting']['post_subject_maxsize']."', post_subject_maxsize_blog = '".$_G['setting']['post_subject_maxsize']."';";

		$ss .= '</script>';

		$hook_data .= $ss;
	} elseif (
		(
			// 帖子底部
			$hookid == 'viewthread_bottom'
			// AJAX 時
			|| ($hookid == 'viewthread_endline' && (!empty($_G['gp_viewpid']) || $_G['inajax']))
		)
		&& discuz_core::$plugin_support['SyntaxHighlighter']['brush']
	) {
		$ss = '';

		discuz_core::$plugin_support['SyntaxHighlighter']['brush'] = null;

		$path = $_G['siteurl'].'extensions/js/SyntaxHighlighter/';

		$ss = <<<EOF
<!-- Include required JS files -->
<script src="{$path}src/shCore.js" type="text/javascript" _reload="1"></script>
<script src="{$path}src/shAutoloader.js" type="text/javascript" _reload="1"></script>

<script type="text/javascript" reload="1">
SyntaxHighlighter.autoloader.apply(null, [
	'applescript			{$path}scripts/shBrushAppleScript.js',
	'actionscript3 as3		{$path}scripts/shBrushAS3.js',
	'bash shell				{$path}scripts/shBrushBash.js',
	'coldfusion cf			{$path}scripts/shBrushColdFusion.js',
	'cpp c					{$path}scripts/shBrushCpp.js',
	'c# c-sharp csharp		{$path}scripts/shBrushCSharp.js',
	'css					{$path}scripts/shBrushCss.js',
	'delphi pascal			{$path}scripts/shBrushDelphi.js',
	'diff patch pas			{$path}scripts/shBrushDiff.js',
	'erl erlang				{$path}scripts/shBrushErlang.js',
	'groovy					{$path}scripts/shBrushGroovy.js',
	'java					{$path}scripts/shBrushJava.js',
	'jfx javafx				{$path}scripts/shBrushJavaFX.js',
	'js jscript javascript	{$path}scripts/shBrushJScript.js',
	'perl pl				{$path}scripts/shBrushPerl.js',
	'php php5 php3 php4		{$path}scripts/shBrushPhp.js',
	'text plain txt			{$path}scripts/shBrushPlain.js',
	'py python				{$path}scripts/shBrushPython.js',
	'ruby rails ror rb		{$path}scripts/shBrushRuby.js',
	'sass scss				{$path}scripts/shBrushSass.js',
	'scala					{$path}scripts/shBrushScala.js',
	'sql mysql				{$path}scripts/shBrushSql.js',
	'vb vbnet				{$path}scripts/shBrushVb.js',
	'xml xhtml xslt html	{$path}scripts/shBrushXml.js',
]);

//SyntaxHighlighter.config.clipboardSwf = '{$path}/scripts/clipboard.swf';
//SyntaxHighlighter.defaults['gutter'] = false;
SyntaxHighlighter.defaults['smart-tabs'] = true;
//SyntaxHighlighter.defaults['collapse'] = true;
//SyntaxHighlighter.defaults['highlight'] = true;
SyntaxHighlighter.defaults['toolbar'] = false;

SyntaxHighlighter.all();
//SyntaxHighlighter.highlight();
</script>

<!-- Include *at least* the core style and default theme -->
<!--link href="{$path}styles/shCore.css" rel="stylesheet" type="text/css" /-->
<link href="{$path}styles/shCoreMidnight.css" rel="stylesheet" type="text/css" />
<!--link href="{$path}styles/shThemeMidnight.css" rel="stylesheet" type="text/css" /-->

<style>
/* 使 pre, code 可以斷行 */
.syntaxhighlighter pre, .syntaxhighlighter code {
	width:inherit;
	word-break: break-all;
	white-space: pre-wrap;       /* css-3 */
	white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
	white-space: -pre-wrap;      /* Opera 4-6 */
	white-space: -o-pre-wrap;    /* Opera 7 */
	*white-space: pre;           /* IE */
	word-wrap: break-word;       /* Internet Explorer 5.5+ */
}
</style>

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

			$_do_skip = 0;

			// 防止造成無法取得緩存
			if (preg_match('/^(usergroup|threadsort|admingroup|style)_/', $k, $m)) {
				$k2 = $m[1].'s';
			} elseif (preg_match('/^(diytemplatename)/', $k, $m)) {
				$k2 = $m[1];

			} elseif (
				in_array($k, array(
					// cronnextrun 由 discuz_cron 控制
					'cronnextrun',

					// source\include\cron\cron_todaypost_daily.php
					'historyposts',

					'groupindex',
				))
			) {
				$_do_skip = 1;

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

			if ($_do_skip) {
				$k && $_loadedcache[$k] = true;
				$k2 && $_loadedcache[$k2] = true;
				continue;
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

	//TODO:需要改良修正解決緩存的意外錯誤

	// 整理過濾處理過的 Array
	$caches = array_unique((array)$caches);
	$caches_load = array_unique((array)$caches_load);

	if(!empty($caches)) {
		include_file_once(libfile('function/cache'), 0, 1);

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

0 && Scorpio_Hook::add('Func_cachedata:Before_get_syscache', '_eFunc_cachedata_Before_get_syscache');

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

				// cronnextrun 由 discuz_cron 控制
				'cronnextrun',

				// source\include\cron\cron_todaypost_daily.php
				'historyposts',

				'groupindex',

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

Scorpio_Hook::add('Class_discuz_core::_init_setting:After', '_eClass_discuz_core__init_setting_After');

function _eClass_discuz_core__init_setting_After($_EVENT, $discuz) {
	$discuz->var['varhash_gzip'] = $discuz->var['varhash_gzip_js'] = '';

	// 檢測使用者的瀏覽器是否支援 gzip 如果支援則 js, css 改為使用 js.gz, css.gz
	if ($discuz->config['output']['gzip']
		&& function_exists('getaccept_encoding_gzip')
		&& getaccept_encoding_gzip()
	) {
		$discuz->var['varhash_gzip'] = '.gz';

		if ($discuz->var['setting']['jspath'] == 'data/cache/') {
			$discuz->var['varhash_gzip_js'] = $discuz->var['varhash_gzip'];
		}
	}

	define('VERHASH_GZIP', $discuz->var['varhash_gzip']);
	define('VERHASH_GZIP_JS', $discuz->var['varhash_gzip_js']);

	// js setting

	discuz_core::$plugin_support['jscache']['jquery.lazy.js'] = array(
		/*
		'name' => 'jquery.lazy.js',
		'file' => 'jquery.lazy.js',
		*/
		'path' => 'jquery.lazy/',
		'base' => 'extensions/js/',

		'file_develop' => 'jquery.lazy.source.js',
	);

	discuz_core::$plugin_support['jscache']['clearbox_jquery.js'] = array(
		'file' => 'clearbox_jquery.js',
		'path' => 'clearbox/js/',
		'base' => 'extensions/js/',
	);

	discuz_core::$plugin_support['jscache']['common_extensions.js'] = array(
		'base' => 'extensions/js/',
	);
}

Scorpio_Hook::add('Class_discuz_core::_init_style:After', '_eClass_discuz_core__init_style_After');

/**
 * Event:
 * 		Class_discuz_core::_init_style:After
 */
function _eClass_discuz_core__init_style_After($_EVENT, $discuz) {

	/**
	 * 載入風格專用的 hook
	 *
	 * @example extensions/hooks/template/default/hooks_core.php
	 * @example extensions/hooks/template/goodnight/hooks_core.php
	 */
	@include_once libfile('hooks_core', 'hooks/'.$discuz->var['style']['tpldir'], 'extensions/');

}

Scorpio_Hook::add('Func_output:Before_rewrite_content_echo', '_eFunc_output_Before_rewrite_content_echo');
Scorpio_Hook::add('Func_output_ajax:Before_rewrite_content_echo', '_eFunc_output_Before_rewrite_content_echo');
Scorpio_Hook::add('Func_mobileoutput:Before_output_replace', '_eFunc_output_Before_rewrite_content_echo');
// 修正開啟 rewritestatus 後造成無效的 BUG
Scorpio_Hook::add('Func_output_replace:Before_replace_str', '_eFunc_output_Before_rewrite_content_echo');

/**
 * 輸出時將帳號名稱轉為暱稱
 *
 * Event:
 * 		Func_output_replace:Before_replace_str
 *
 * 		Func_output:Before_rewrite_content_echo
 * 		Func_output_ajax:Before_rewrite_content_echo
 * 		Func_mobileoutput:Before_output_replace
 **/
function _eFunc_output_Before_rewrite_content_echo($_EVENT, $_conf) {
	if(defined('IN_MODCP') || defined('IN_ADMINCP')) return;

	extract($_conf, EXTR_REFS);

	$_func = __FUNCTION__.'_callback';

	$regex_showname = '[^<\>\'"]+';

	$_file = libfile('cache_output_user', 'cache/extensions', 'data/');

	if (empty(discuz_core::$_cache_data['output']['users'])) {
		$data = array();
		@include $_file;

		discuz_core::$_cache_data['output']['users'] = (array)discuz_core::$_cache_data['output']['users'];

		if ($data['output_user']['timestamp'] > TIMESTAMP - 3600 * 5) {
			discuz_core::$_cache_data['output']['users'] = array_merge(
				(array)discuz_core::$_cache_data['output']['users']
				, (array)$data['output_user']
			);
		}
	}

	$content = preg_replace_callback('/<a href\="(?<href>()home.php\?mod=space&(?:amp;)?(?:uid\=(?<uid>\d+)|username\=(?<username>[^&"]+?)))"(?<extra>[^\>]*)\>(?<tag1>\<(?:strong|b)\>)?(?<showname>'.$regex_showname.')(?<tag2>\<\/(?:strong|b)\>)?<\/a/', $_func, $content);

	if (
		discuz_core::$_cache_data['output']['users']['updated']
		&& (TIMESTAMP > discuz_core::$_cache_data['output']['users']['timestamp'] + 60)
	) {
		unset(discuz_core::$_cache_data['output']['users']['updated']);

		include_once libfile('function/cache');

		if (discuz_core::$_cache_data['output']['users']['timestamp'] <= TIMESTAMP - 3600 * 5 + 60) {
			discuz_core::$_cache_data['output']['users']['timestamp'] = TIMESTAMP;
		}

		discuz_core::$_cache_data['output']['users']['dateline'] = dgmdate(discuz_core::$_cache_data['output']['users']['timestamp']);

		$cachename = 'output_user';
		$cachedata = '$data[\''.$cachename.'\'] = '.var_export(discuz_core::$_cache_data['output']['users'], true).";\n\n";

		writetocache($cachename, $cachedata, 'cache_', 'extensions/');
	}
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

	if (!isset($_user)) {
		$_user = &discuz_core::$_cache_data['output']['users'];
	}

	// 初始化 $_uid
	$_uid = 0;

	// 將 href 內的 username 解碼
	$m['username'] = daddslashes(rawurldecode($m['username']));
	$m['uid'] = intval($m['uid']);

	// 判斷是否分析過 $m['uid']
	if ($m['uid'] && isset($_user['uid'][$m['uid']])) {
		$_uid = $m['uid'];

	// 判斷是否分析過 $m['username']
	} elseif (!empty($m['username']) && isset($_user['username'][(string)$m['username']])) {
		$_uid = $_user['username'][(string)$m['username']];

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
			$_user['username'][(string)$user['username']] = $_uid;
			if (!empty($m['username']) && $user['username'] != $m['username']) $_user['username'][(string)$m['username']] = $_uid;

			$_user['updated'] = true;

		} else {
			// 失敗時緩存為 0
			if ($m['uid']) $_user['uid'][$m['uid']] = '';
			if (!empty($m['username'])) $_user['username'][$m['username']] = 0;
		}
	}

	// 如果成功查詢到 $_uid
	if ($_uid
		&& !empty($m['showname'])
		// 改良只取代帳號名
		&& $_uid == $_user['username'][(string)$m['showname']]
	) {
		// 取得緩存
		$user = $_user['uid'][$_uid];

		$s = '';
		$s .= '<a href="'.$m['href'].'"';

		if (!empty($user) && strpos($m['extra'], ' title=') === false) {
			// 提示帳號名稱
			$s .= ' title="'.strip_tags($m['showname']).' ( '.strip_tags($user).' )"';
		}

		$s .= ''.$m['extra'].'>';

		$s .= $m['tag1'];

		if (!empty($user)) {
			$s .= $user;
			//if ($_user['counter'][$user] > 1) $s .= '@'.$m['showname'];
		} else {
			$s .= $m['showname'];
		}

		$s .= $m['tag2'];

		$s .= '</a';
	} else {
		// 失敗時回傳原有字串
		$s = $m[0];
	}

	return $s;
}

Scorpio_Hook::add('Func_mobileoutput:Before_rewrite_content_echo', '_eFunc_mobileoutput_Before_rewrite_content_echo');

function _eFunc_mobileoutput_Before_rewrite_content_echo($_EVENT, $_conf) {
	extract($_conf, EXTR_REFS);

	// 手機模式下取消所有開新視窗
	$content = preg_replace('/ target=([\'"])([^\'"]+)\\1/', '', $content);
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

	if ($_G['siteroot'] == '/') return Scorpio_Hook::RET_SUCCESS;

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