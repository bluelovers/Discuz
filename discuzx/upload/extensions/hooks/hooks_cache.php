<?php

/**
 * @author bluelovers
 **/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Class_template::parse_template:Before_addon_tpl', '_eClass_template_parse_template_Before_addon_tpl');

function _eClass_template_parse_template_Before_addon_tpl($_EVENT, $ret) {

	$data = &$_EVENT['event.data'];

	$var_regexp_ex = $ret['var_regexp_ex'];
	$var_regexp = $ret['var_regexp'];

	$find = &$data['find'];
	$replace = &$data['replace'];

	/**
	 * replace #|javascript: => javascript:void(0)
	 **/
	$find[] = "/\s+href=(\"|\')(?:(?:javascript\:;)|\#+)\\1/is";
	$replace[] = " href=\\1javascript:void(0);\\1";

	/**
	 * {for_option 數組變量 值鍵 值變量 選中的值}
	 **/
	$find[] = "/[\n\r\t]*\{for_option(:|\s+)(\S+?)\s+(\S+?)\s+(\S+?)\s+(\S+?)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/for_option\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<? if(is_array(\\2)) foreach (\\2 as \$_k_ => \\4) { \$_s_ = ((\\4[\\3] == \\5 || @in_array(\\4[\\3], \\5)) ? \' selected class=\"tpl_select\"\':\'\'); ?>','\\6<? } ?>')";

	/**
	 * {option 數組變量 選中的值}
	 **/
	$find[] = "/[\n\r\t]*\{option(:|\s+)(\S+?)\s+(\S+?)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/option\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<? if(is_array(\\2)) foreach (\\2 as \$_k_ => \$_v_) { \$_s_ = ((\$_k_ == \\3 || @in_array(\$_k_, \\3)) ? \' selected class=\"tpl_select\"\':\'\'); ?>','\\4<? } ?>')";

	/**
	 * {for 數組變量 鍵變量 值變量}
	 **/
	$find[] = "/[\n\r\t]*\{for(:|\s+)(\S+?)\s+(\S+?)\s+(\S+?)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/for\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<? for (\$_v_=\\2;\$_v_\\3;\$_v_\\4) { ?>','\\5<? } ?>')";

	/**
	 * {變量:default 默認值}
	 **/
	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:default\s+([^\{\}].*?)\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<?= ((!isset(\\1) || empty(\\1)) ? \\5 : \\1) ?>')";

	/**
	 * {變量:float 格式}
	 *
	 * 按照指定的格式顯示浮點數
	 * 對於浮點數，本語法可以將變量按照格式所指定的位數設置進行顯示。
	 * 格式寫法為「M.D」，M 代表整數位，D 代表小數位。
	 * 格式允許用變量代替。
	 **/
	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:float\s+(.+?)\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<?= sprintf(\'%\\5f\', \\1);?>')";

	/**
	 * {js uri}
	 **/
	$find[] = "/[\n\r\t]*\{js(?:\:|\s+)(.+?)\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<script src=\"<?= \$_G[\'setting\'][\'jspath\']; ?>\\1?<?=VERHASH?>\" type=\"text/javascript\"></script>')";

	/**
	 * {變量:html}
	 **/
	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:html\s*\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<?= dhtmlspecialchars(\\1);?>')";

	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:html\s+(.+?)\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<?= dhtmlspecialchars(\\1, \\5);?>')";

	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:htmlchar\s*\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<?= dhtmlspecialchars(\\1);?>')";

	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:htmlstrip\s*\}[\n\r\t]*/ies";
	$replace[] = "template::stripvtags('<?= dhtmlspecialchars(strip_tags(\\1));?>')";

	/**
	 * {變量:userfunc(參數)}
	 *
	 * {$metakeywords:strip_tags() ''}
	 * <\?=($metakeywords ? strip_tags($metakeywords) :  ''); ?\>
	 **/
	$find[] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:(\S+?)\((.*?)\)(\s([^\{\}].*?))?\}[\n\r\t]*/ies";
	$replace[] = "template::_tpl_func('\\5', '\\1', '\\6', '\\7')";

	return Scorpio_Hook::RET_SUCCESS;
}

Scorpio_Hook::add('Func_writetocsscache:Before_minify', '_eFunc_writetocsscache_Before_minify');
Scorpio_Hook::add('Class_template::loadcsstemplate:Before_minify', '_eFunc_writetocsscache_Before_minify');

function _eFunc_writetocsscache_Before_minify($_EVENT, $conf) {
	extract($conf, EXTR_REFS);

	if($entry != 'module.css') {
		// 清除 css 註解
		$cssdata = preg_replace('/\/\*((?:[^\*]*|\*(?!\/)).*)\*\//sU', "\n", $cssdata);
	}

	// 轉換分行
	$cssdata = str_replace("\r\n", "\n", $cssdata);

	// 壓縮 css
	$cssdata = preg_replace(array(
		// 清除多餘空白
		'/[ \t]*([,;:\{\}]+|\n)[ \t]*/s',
		'/\t+/',

		// 清除多餘分行
		'/(\n| ){2,}/s',

		// 清除 BOM
		'/^(\xef\xbb\xbf)?\s+|\s+$/sU',
		'/(?!^)\xef\xbb\xbf/U',
	), array(
		'\\1',
		' ',
		'\\1',
		'\\1',
		'',
	), $cssdata);

	if (defined('DISCUZ_DEBUG') && !DISCUZ_DEBUG) {
		// 如果不是 debug 模式時則清除分行
		$cssdata = str_replace("\n", '', $cssdata);
	}

	// 清理
	$cssdata = trim($cssdata);

	// 跳過原有的處理
	$switchstop = true;
}

Scorpio_Hook::add('Func_writetocsscache:Before_fwrite', '_eFunc_writetocsscache_Before_fwrite');
Scorpio_Hook::add('Class_template::loadcsstemplate:Before_fwrite', '_eFunc_writetocsscache_Before_fwrite');

function _eFunc_writetocsscache_Before_fwrite($_EVENT, $conf) {
	extract($conf, EXTR_REFS);

}

?>