<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_logging.php 20126 2011-02-16 02:42:26Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if(!in_array($_G['gp_action'], array('login', 'logout'))) {
	showmessage('undefined_action');
}

$ctl_obj = new logging_ctl();
$ctl_obj->setting = $_G['setting'];
$method = 'on_'.$_G['gp_action'];
$ctl_obj->template = 'member/login';
$ctl_obj->$method();

?>