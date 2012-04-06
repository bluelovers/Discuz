<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member.php 20112 2011-02-15 07:10:53Z monkey $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'member');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('activate', 'clearcookies', 'emailverify', 'getpasswd',
	'groupexpiry', 'logging', 'lostpasswd',
	'register', 'regverify', 'switchstatus', 'connect');


$mod = !in_array($discuz->var['mod'], $modarray) ? 'register' : $discuz->var['mod'];

define('CURMODULE', $mod);

$discuz->init();
if($mod == 'register' && $discuz->var['mod'] != $_G['setting']['regname'] && !defined('IN_CONNECT')) {
	showmessage('undefined_action');
}


require libfile('function/member');
require libfile('class/member');
runhooks();


require DISCUZ_ROOT.'./source/module/member/member_'.$mod.'.php';

?>