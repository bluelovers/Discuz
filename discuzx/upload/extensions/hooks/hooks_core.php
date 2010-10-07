<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.
	This is NOT a freeware, use is subject to license terms

	$HeadURL: svn://localhost/trunk/discuz_x/upload/extensions/hooks/hooks_core.php $
	$Revision: 109 $
	$Author: bluelovers$
	$Date: 2010-08-02 06:22:26 +0800 (Mon, 02 Aug 2010) $
	$Id: hooks_core.php 109 2010-08-01 22:22:26Z user $
*/

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
			/*
			case 'source/function/function_cache.php':

				@include_once libfile('hooks/cache', '', 'extensions/');

				break;
			*/
			case 'source/function/function_share.php':
				@include_once libfile('hooks/share', '', 'extensions/');
				break;
			case 'source/function/function_discuzcode.php':
				@include_once libfile('hooks/discuzcode', '', 'extensions/');
				break;
			default:
//				dexit($file);

				break;
		}
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
	if (in_array($agv['message'], array('login_succeed', 'login_succeed_inactive_member', 'login_activation'))) {
		if ($agv['values']['uid'] > 0) {
			$user = DB::query_first("SELECT avatarstatus, uid FROM ".DB::table('common_member')." WHERE uid='{$agv['values']['uid']}' LIMIT 1");

			if(!empty($user) && $user['uid'] && empty($user['avatarstatus']) && uc_check_avatar($user['uid'], 'middle')) {
				DB::update('common_member', array('avatarstatus'=>'1'), array('uid'=>$_G['uid']));

				updatecreditbyaction('setavatar');

				if($_G['setting']['my_app_status']) manyoulog('user', $user['uid'], 'update');
			}
		}
	}
}

Scorpio_Hook::add('Func_output:Before_rewrite_content_echo', '_eFunc_output_Before_rewrite_content_echo');

function _eFunc_output_Before_rewrite_content_echo(&$content, &$in_ajax) {
	global $_G;

	if ($_G['setting']['rewritestatus'] && !defined('IN_MODCP') && !defined('IN_ADMINCP')) {
		$content = preg_replace("/<a\s+href=\"(#+|javascript\:\s*;?|\s*)\"/i", "<a href=\"javascript:void(0);\"", $content);
	}
}

Scorpio_Hook::add('Func_output:Before_rewrite_domain_app', '_eFunc_output_Before_rewrite_domain_app');

function _eFunc_output_Before_rewrite_domain_app(&$content, &$in_ajax) {
	global $_G;

	if ($_G['setting']['rewritestatus'] && !defined('IN_MODCP') && !defined('IN_ADMINCP')) {
		if (1 || $_G['setting']['domain']['app']['default']) {
//			$content = preg_replace("/<a(\s+(?:[a-z]+)=\"[a-z0-9]+\")+\s+href=\"([^\"]+)\"/i", "<a href=\"\\2\"\\1", $content);
			$content = preg_replace("/<a(\s+(?:[a-z]+)=\"[a-z0-9,\.;\(\)]+\")+\s+href=\"([^\"]+)\"/i", "<a href=\"\\2\"\\1", $content);
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

	if ($hookid == 'viewthread_bottom' && $_G['extensions']['SyntaxHighlighter']['brush']) {
		$ss = '';

//		foreach((array)$_G['extensions']['SyntaxHighlighter']['brush'] as $brush) {
//
//		}

		$path = 'extensions/js/SyntaxHighlighter/';

		$ss = <<<EOF
<!-- Include required JS files -->
<script type="text/javascript" src="{$path}src/shCore.js"></script>
<script type="text/javascript" src="{$path}src/shAutoloader.js"></script>

<script type="text/javascript">
SyntaxHighlighter.autoloader.apply(null, [
	'applescript            {$path}scripts/shBrushAppleScript.js',
	'actionscript3 as3      {$path}scripts/shBrushAS3.js',
	'bash shell             {$path}scripts/shBrushBash.js',
	'coldfusion cf          {$path}scripts/shBrushColdFusion.js',
	'cpp c                  {$path}scripts/shBrushCpp.js',
	'c# c-sharp csharp      {$path}scripts/shBrushCSharp.js',
	'css                    {$path}scripts/shBrushCss.js',
	'delphi pascal          {$path}scripts/shBrushDelphi.js',
	'diff patch pas         {$path}scripts/shBrushDiff.js',
	'erl erlang             {$path}scripts/shBrushErlang.js',
	'groovy                 {$path}scripts/shBrushGroovy.js',
	'java                   {$path}scripts/shBrushJava.js',
	'jfx javafx             {$path}scripts/shBrushJavaFX.js',
	'js jscript javascript  {$path}scripts/shBrushJScript.js',
	'perl pl                {$path}scripts/shBrushPerl.js',
	'php                    {$path}scripts/shBrushPhp.js',
	'text plain             {$path}scripts/shBrushPlain.js',
	'py python              {$path}scripts/shBrushPython.js',
	'ruby rails ror rb      {$path}scripts/shBrushRuby.js',
	'sass scss              {$path}scripts/shBrushSass.js',
	'scala                  {$path}scripts/shBrushScala.js',
	'sql                    {$path}scripts/shBrushSql.js',
	'vb vbnet               {$path}scripts/shBrushVb.js',
	'xml xhtml xslt html    {$path}scripts/shBrushXml.js',
]);

SyntaxHighlighter.config.clipboardSwf = '{$path}/scripts/clipboard.swf';
//SyntaxHighlighter.defaults['gutter'] = false;
SyntaxHighlighter.defaults['smart-tabs'] = true;
//SyntaxHighlighter.defaults['collapse'] = true;
//SyntaxHighlighter.defaults['highlight'] = true;

SyntaxHighlighter.all();
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
		$hook_data .= '<div style="padding: 10px 10px;"><span style="width: 100px;height: 32px;padding: 10px 20px 10px 35px;background: url(static/image/plus/common/refresh.gif) no-repeat 0px 0px;"><a onclick="window.location.reload();" href="#">刷新</a></span></div>';
	}

}

?>