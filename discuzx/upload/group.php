<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group.php 21671 2011-04-07 06:21:13Z zhangguosheng $
 */

define('APPTYPEID', 3);
define('CURSCRIPT', 'group');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$cachelist = array('grouptype', 'groupindex', 'diytemplatenamegroup');
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

require DISCUZ_ROOT.'./source/module/group/group_'.$mod.'.php';
?>