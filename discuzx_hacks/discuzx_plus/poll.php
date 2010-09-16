<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: poll.php 646 2010-09-13 03:37:40Z yexinhao $
 */

define('CURSCRIPT', 'poll');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$cachelist = array('template', 'navlist', 'modulelist', 'poll_setting');
$discuz->cachelist = $cachelist;
$discuz->init();

$_G['showmessage']['module'] = $module = 'poll';
$_G['showmessage']['tpl'] = $tpl = 'default';
$_G['showmessage']['cssurl'] = 'template/'.$_G['showmessage']['module'].'/'.$_G['showmessage']['tpl'].'/'.$_G['showmessage']['module'].'.css';

$iframe = !empty($_G['gp_iframe']) ? intval($_G['gp_iframe']) : '';
$bgcolor = !empty($iframe) && (strlen($_G['gp_bgcolor']) == 3 || strlen($_G['gp_bgcolor']) == 6) ? dhtmlspecialchars($_G['gp_bgcolor']) : '';

if(empty($_G['cache']['modulelist'][$module]['available'])) {
	showmessage('module_unavailable');
}

$modarray = array('index');
$mod = !in_array($_G['mod'], $modarray) ? 'index' : $_G['mod'];

define('CURMODULE', $mod);
require DISCUZ_ROOT.'./source/module/poll/poll_'.$mod.'.php';

?>