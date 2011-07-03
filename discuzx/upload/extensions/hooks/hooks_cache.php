<?php

/**
 * @author bluelovers
 */

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Class_template::parse_template:Before_addon_tpl', '_eClass_template_parse_template_Before_addon_tpl');

function _eClass_template_parse_template_Before_addon_tpl($_EVENT, $ret) {

	$data = &$_EVENT['event.data'];

	// replace #|javascript: => javascript:void(0)
	$data['find'][] = "/\s+href=(\"|\')(?:(?:javascript\:;)|\#+)\\1/is";
	$data['replace'][] = " href=\\1javascript:void(0);\\1";

	// {變量:default 默認值}
	$data['find'][] = "/[\n\r\t]*\{\<\?\=$var_regexp\?\>\:default\s+([^\{\}].*?)\}[\n\r\t]*/ies";
	$data['replace'][] = "\$this->addquote('<?= ((!isset(\\1) || empty(\\1)) ? \\5 : \\1) ?>')";

	// {js uri}
	$data['find'][] = "/[\n\r\t]*\{js(?:\:|\s+)(.+?)\}[\n\r\t]*/ies";
	$data['replace'][] = "\$this->stripvtags('<script src=\"<? echo \$_G[\'setting\'][\'jspath\']; ?>\\1?<?=VERHASH?>\" type=\"text/javascript\"></script>')";

	return Scorpio_Hook::RET_SUCCESS;
}

?>