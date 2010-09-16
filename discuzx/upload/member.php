<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member.php 16388 2010-09-06 04:08:21Z liulanbo $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'member');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('activate', 'clearcookies', 'emailverify', 'getpasswd',
					'groupexpiry', 'logging', 'lostpasswd',
					'register', 'regverify', 'switchstatus');


$mod = !in_array($discuz->var['mod'], $modarray) ? 'register' : $discuz->var['mod'];

define('CURMODULE', $mod);

$modcachelist = array('register' => array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional', 'fields_register', 'ipctrl'));

$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

$discuz->cachelist = $cachelist;
$discuz->init();
if($mod == 'register' && $discuz->var['mod'] != $_G['setting']['regname']) {
	showmessage('undefined_action');
}


runhooks();

require libfile('function/member');
require DISCUZ_ROOT.'./source/module/member/member_'.$mod.'.php';


?>