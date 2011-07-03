<?php

/**
 * @author bluelovers
 */

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Class_template::parse_template:Before_addon_tpl', '_eClass_template_parse_template_Before_addon_tpl');

function _eClass_template_parse_template_Before_addon_tpl($_EVENT, $ret) {

	$_EVENT['event.data']['find'][] = "/[\n\r\t]*\{js(?:\:|\s+)(.+?)\}[\n\r\t]*/ies";
	$_EVENT['event.data']['replace'][] = "\$this->stripvtags('<script src=\"<? echo \$_G[\'setting\'][\'jspath\']; ?>\\1?<?=VERHASH?>\" type=\"text/javascript\"></script>')";

	return Scorpio_Hook::RET_SUCCESS;
}

?>