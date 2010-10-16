<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.

	$HeadURL: $
	$Revision: $
	$Author: bluelovers$
	$Date: $
	$Id: $
*/

Scorpio_Hook::add('Class_discuz_core::_init_input:After_MAGIC_QUOTES_GPC', '_eClass_discuz_core__init_input_After_MAGIC_QUOTES_GPC');

function _eClass_discuz_core__init_input_After_MAGIC_QUOTES_GPC() {
	$_GET = scoarray::map_all('scotext::lf', $_GET);
	$_POST = scoarray::map_all('scotext::lf', $_POST);

	if (isset($_POST['message']) && !scotext::is_ascii($_POST['message'])) {
		global $_G;

		$ER = error_reporting( ~ E_NOTICE);
		$_POST['message'] = iconv($_G['config']['output']['charset'], $_G['config']['output']['charset'] . '//IGNORE', $_POST['message']);
		error_reporting($ER);
	}
}

Scorpio_Hook::add('Class_discuz_core::_init_setting:After', '_eClass_discuz_core__init_setting_After');

function _eClass_discuz_core__init_setting_After(&$discuz_core) {
	if (!$discuz_core->var['setting']['maxpostsize_subject']) $discuz_core->var['setting']['maxpostsize_subject'] = 80;
}

Scorpio_Hook::add('Func_libfile', '_eFunc_libfile');

function _eFunc_libfile(&$ret, $root, $force = 0) {
//	$root	= Scorpio_File::path($root);
//	$ret	= Scorpio_File::file($ret);
//
//	if (strpos($ret, $root) === 0) $file = substr($ret, strlen($root));

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

Scorpio_Hook::add('Func_cachedata:After', '_eFunc_cachedata_After');

function _eFunc_cachedata_After($conf) {
	extract($conf, EXTR_REFS);

	static $loadedcache = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	$caches = array();
	foreach ($cachenames as $k) {
		if(!isset($loadedcache[$k])) {
			$k2 = $k;

			if (preg_match('/^usergroup_\d+$/', $k)) {
				$k2 = 'usergroups';
			} elseif ($k == 'style_default') {
				$k2 = 'styles';
			}

			$caches[] = $k2;
			$caches_load[] = $k;
			$loadedcache[$k] = true;
		}
	}

	if(!empty($caches)) {
		@include_once libfile('function/cache');

		updatecache($caches);
		loadcache($caches_load, true);

		$cachedata = cachedata($caches_load);
		foreach($cachedata as $_k => $_v) {
			$data[$_k] = $_v;
		}

//		static $c;
//		$c++;
//
//		($c > 0) && dexit(array($caches, $caches_load, $cachenames));
	}
}

Scorpio_Hook::add('Func_showmessage:Before_custom', '_eFunc_showmessage_Before_custom');

/*
 * 登入時檢查更新用戶是否有設定上傳頭像並更新狀態
 *
 * 由 http://www.discuz.net/thread-1908234-1-1.html 得知此BUG
 * 由於DiscuzX的版本中防灌水中引入了強制用戶上傳頭像
 * 結果造成論壇轉換過來的用戶標誌位有問題
 */
function _eFunc_showmessage_Before_custom($agv = array()) {
	if ($agv['values']['uid'] > 0 && in_array($agv['message'], array('login_succeed', 'login_succeed_inactive_member', 'login_activation'))) {
		global $_G;
		$user = DB::query_first("SELECT avatarstatus, uid FROM ".DB::table('common_member')." WHERE uid='{$agv['values']['uid']}' LIMIT 1");

		if(!empty($user) && $user['uid'] && empty($user['avatarstatus']) && uc_check_avatar($user['uid'], 'middle')) {
			DB::update('common_member', array('avatarstatus'=>'1'), array('uid'=>$_G['uid']));

			updatecreditbyaction('setavatar');

			if($_G['setting']['my_app_status']) manyoulog('user', $user['uid'], 'update');
		}
	}
}

Scorpio_Hook::add('Func_output:Before_rewrite_content_echo', '_eFunc_output_Before_rewrite_content_echo');

function _eFunc_output_Before_rewrite_content_echo(&$content, &$in_ajax) {
	if (!defined('IN_MODCP') && !defined('IN_ADMINCP')) {
		$content = preg_replace("/<a\s+href=\"(#+|javascript\:\s*;?|\s*)\"/i", "<a href=\"javascript:void(0);\"", $content);
	}
}

Scorpio_Hook::add('Func_output:Before_rewrite_domain_app', '_eFunc_output_Before_rewrite_domain_app');

function _eFunc_output_Before_rewrite_domain_app(&$content, &$in_ajax) {
	if (!defined('IN_MODCP') && !defined('IN_ADMINCP')) {

		$safepreg = array();
		$safepreg[0] = '[a-z0-9,\/\.;\(\)\s_\:\-\+\[\]]+';
//		$safepreg[1] = '[^"]+';
//		$safepreg[3] = '[a-z0-9,\/\.;\(\)\s_\:]';
		$safepreg[3] = '\w';
		$safepreg[1] = '(?:[^\'">]|' .$safepreg[3]. '(?!\\4|>))+';
		$safepreg[2] = '[a-z]+';

//		$content = '/<(a|link|base)(\s+(?:' + $safepreg[2] + ')=("|\')' + $safepreg[0] + '\\2)+\s+href=("|\')(' + $safepreg[1] + ')\\3/i';

		$content = preg_replace(array(
//			"/<a(\s+(?:[a-z]+)=\"[a-z0-9,\.;\(\)\s]+\")+\s+href=\"([^\"]+)\"/i",
//			"/<link(\s+(?:[a-z]+)=\"[a-z0-9,\.;\(\)\s]+\")+\s+href=\"([^\"]+)\"/i",
//			'/<(a|link)(\s+(?:' + $safepreg[2] + ')=("|\')' + $safepreg[0] + '\\3)+\s+href=("|\')(' + $safepreg[1] + ')\\4/i',
//			"/<(link|a)((?:\s+(?:[a-z]+)=(\"|')[a-z0-9,\/\.;\(\)\s]+\\3)+)\s+href=(\"|')([^\"]+)\\4/i",
			'/<(link|a)((?:\s+(?:' . $safepreg[2] . ')=("|\')' . $safepreg[0] . '\\3)+)\s+href=("|\')(' . $safepreg[1] . ')\\4/i',
			'/<(img|script)((?:\s+(?:' . $safepreg[2] . ')=("|\')' . $safepreg[0] . '\\3)+)\s+src=("|\')(' . $safepreg[1] . ')\\4/i',
		), array(
//			"<a href=\"\\2\"\\1",
//			"<link href=\"\\2\"\\1",
			"<\\1 href=\"\\5\"\\2",
			"<\\1 src=\"\\5\"\\2",
		), $content);

		global $_G;
		$content = preg_replace("/<(base|link|script|img) (href|src)=\"([^\"]+)\"/e", "'<\\1 \\2=\"'._rewriteoutput('site_default', -1, '".$_G['siteurl']."', '\\3').'\"'", $content);

//		$data = rewritedata();
//		$content = preg_replace(str_replace(array('/<a ', '\>/e'), array('/<(base|link) ', '\/?\>/e'), $data['search']['forum_viewthread']), "_rewriteoutput('\\1', 'forum_viewthread', 0, '".$_G['setting']['domain']['app']['default'].$port.$_G['siteroot']."', '\\2')", $content);

//		dexit(str_replace(array('/<a ', '\>/e'), array('/<(base|link) ', '\/?\>/e'), $data['search']['forum_viewthread']));


	}
}

function _rewriteoutput($type, $returntype, $host) {
	$args = func_get_args();
//	array_shift($args);

	list(,,, $url) = func_get_args();
	if (!preg_match('/https?:\/\//i', $url)
//		&& preg_match('/\w+\.(js|css|ico|png|jpg|gif|swf)/i', $url)
		&& in_array(Scorpio_File_Core::fileext($url), array('js', 'css', 'ico', 'png', 'jpg', 'gif', 'swf'))
	) {
		$ret = call_user_func_array('rewriteoutput', $args);
	} else {
		$ret = $url;
	}

	return $ret;
}

Scorpio_Hook::add('Func_rewriteoutput:Before_rewrite_href', '_eFunc_rewriteoutput_Before_rewrite_href');

function _eFunc_rewriteoutput_Before_rewrite_href(&$type, &$returntype, &$host, &$r, &$fextra, &$extra, &$rewriterule) {
	if (!defined('IN_MODCP') && !defined('IN_ADMINCP')) {
		if(($type == 'forum_forumdisplay' || $type == 'group_group' || $type == 'portal_article') && ($r['{page}'] == 1 || $r['{page}'] == 0)) {
			$rewriterule = str_replace('/{page}', '', $rewriterule);
		} elseif($type == 'forum_viewthread' && ($r['{prevpage}'] == 1 || $r['{prevpage}'] == 0)) {
			$rewriterule = str_replace('/{prevpage}', '', $rewriterule);
			if (($r['{page}'] == 1 || $r['{page}'] == 0)) $rewriterule = str_replace('/{page}', '', $rewriterule);
		}
	}
}

Scorpio_Hook::add('Func_dheader:Before', '_eFunc_dheader_Before');

function _eFunc_dheader_Before($string, $replace, $http_response_code) {
	global $_G;

	if (preg_match('/^\s*location:\s*(http:\/\/)?(.*)\s*$/is', $string, $matches)) {
		if ($matches[1] == 'http://') {

		} else {
			$string = 'location: '.$_G['siteurl'].$matches[2];
		}
	}

}

Scorpio_Hook::add('Tpl_Func_hooktags:Before', '_eTpl_Func_hooktags_Before');

function _eTpl_Func_hooktags_Before(&$hook_data, $hookid, $key) {
	global $_G;

	if (
		(
			$hookid == 'viewthread_bottom'
			|| ($hookid == 'viewthread_endline' && (!empty($_G['gp_viewpid']) || $_G['inajax']))
		) && $_G['extensions']['SyntaxHighlighter']['brush']
	) {
		$ss = '';
		$_G['extensions']['SyntaxHighlighter']['brush'] = null;

//		dexit($_G['extensions']['SyntaxHighlighter']['brush']);

//		foreach((array)$_G['extensions']['SyntaxHighlighter']['brush'] as $brush) {
//
//		}

		$path = $_G['siteurl'].'extensions/js/SyntaxHighlighter/';
//		$js_run = (empty($_G['gp_viewpid']) || $_G['inajax']) ? 'highlight' : 'all';

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
EOF
;
/*
?><?
*/

		$hook_data .= $ss;
	} elseif ($hookid == 'forumdisplay_postbutton_bottom' || $hookid == 'forumdisplay_postbutton_top') {
		$hook_data .= '<div style="padding: 10px 10px;"><span style="width: 100px;height: 32px;padding: 10px 20px 10px 35px;background: url(static/image/plus/common/refresh.gif) no-repeat 0px 0px;"><a href="#" onclick="window.location.reload();">刷新</a></span></div>';
	} elseif ($hookid == 'global_header_seohead') {

		$ss = <<<EOF
<!--script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script-->
<script type="text/javascript" src="/libs_js/jquery.js"></script>
<script type="text/javascript">jQuery.noConflict();</script>
EOF
;
/*
?><?
*/

		$hook_data .= $ss;
	} elseif ($hookid == 'global_header_javascript') {
		$ss = <<<EOF
<script type="text/javascript">var maxpostsize_subject = {$_G['setting']['maxpostsize_subject']};</script>
EOF
;
/*
?><?
*/

		$hook_data .= $ss;
	}

}

Scorpio_Hook::add('Func_dgmdate:Before_format', '_eFunc_dgmdate_Before_format');

function _eFunc_dgmdate_Before_format($conf) {
	if ($conf['format'] == 'n-j H:i') $conf['format'] = 'n-j H:i:s';
}

?>