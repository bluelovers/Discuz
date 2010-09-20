<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp.php 17082 2010-09-20 09:56:42Z zhengqingpeng $
 */

define('APPTYPEID', 5);
define('CURSCRIPT', 'userapp');

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = & discuz_core::instance();

$modarray = array('index', 'app', 'manage');
$cachelist = array('userapp','usergroups', 'myapp');

$mod = !in_array($discuz->var['mod'], $modarray) ? 'index' : $discuz->var['mod'];

$appid = empty($_G['gp_id']) ? '': intval($_G['gp_id']);
if($appid) {
	$mod = 'app';
}

$discuz->cachelist = $cachelist;
$discuz->init();

if(empty($_G['uid']) && $mod != 'index') {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		dsetcookie('_refer', rawurlencode('userapp.php'));
	}
	showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
}

if(empty($_G['setting']['my_app_status'])) {
	showmessage('no_privilege_my_app_status', '', array(), array('return' => true));
}

if($mod != 'index' && !checkperm('allowmyop')) {
	showmessage('no_privilege', '', array(), array('return' => true));
}
$space = $_G['uid']? getspace($_G['uid']) : array();

define('CURMODULE', 'userapp');
runhooks();

getuserapp();

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['userapp']);
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][5]['navname'];
} else {
	$nobbname = true;
}

require_once libfile('userapp/'.$mod, 'module');

?>