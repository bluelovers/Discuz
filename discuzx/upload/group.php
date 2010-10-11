<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group.php 16832 2010-09-15 07:38:31Z wangjinbo $
 */

define('APPTYPEID', 3);
define('CURSCRIPT', 'group');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$cachelist = array('grouptype', 'groupindex');
$discuz->cachelist = $cachelist;
$discuz->init();

if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
$modarray = array('index', 'my', 'attentiongroup');
$mod = !in_array($_G['mod'], $modarray) ? 'index' : $_G['mod'];

define('CURMODULE', $mod);

runhooks();

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['group']);

//require DISCUZ_ROOT.'./source/module/group/group_'.$mod.'.php';
include libfile('group/'.$mod, 'module');

?>