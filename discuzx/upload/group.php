<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group.php 16429 2010-09-06 09:51:21Z monkey $
 */

define('APPTYPEID', 3);
define('CURSCRIPT', 'group');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$cachelist = array('grouptype', 'groupindex', 'blockclass');
$discuz->cachelist = $cachelist;
$discuz->init();

if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
$modarray = array('index', 'my', 'attentiongroup');
$mod = !in_array($_G['mod'], $modarray) ? 'index' : $_G['mod'];

define('CURMODULE', $mod);

runhooks();

$navtitle = $_G['setting']['seotitle']['group'];

require DISCUZ_ROOT.'./source/module/group/group_'.$mod.'.php';
?>