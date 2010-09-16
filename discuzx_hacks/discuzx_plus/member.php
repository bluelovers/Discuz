<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member.php 527 2010-08-30 05:57:14Z yexinhao $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'member');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('logging', 'clearcookies');

$mod = !in_array($discuz->var['mod'], $modarray) ? 'logging' : $discuz->var['mod'];

$discuz->cachelist = array('navlist', 'modulelist');
$discuz->init();

$_G['showmessage']['module'] = $module = $_G['gp_module'] ? dhtmlspecialchars(trim($_G['gp_module'])) : 'common';
$_G['showmessage']['tpl'] = $tpl = $_G['gp_tpl'] ? dhtmlspecialchars(trim($_G['gp_tpl'])) : '';
$_G['showmessage']['cssurl'] = 'template/'.$_G['showmessage']['module'].(!empty($_G['showmessage']['tpl']) ? '/'.$_G['showmessage']['tpl'].'/' : '').$_G['showmessage']['module'].'.css';


require DISCUZ_ROOT.'./source/module/member/member_'.$mod.'.php';

?>