<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_misc.php 14433 2010-08-11 09:42:50Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$ac = empty($_G['gp_ac']) ? '' : $_G['gp_ac'];
$acs = array('lostpasswd', 'swfupload', 'inputpwd', 'ajax', 'seccode', 'sendmail', 'emailcheck');
if(empty($ac) || !in_array($ac, $acs)) {
	showmessage('enter_the_space', 'home.php?mod=space');
}

$theurl = 'home.php?mod=misc&ac='.$ac;
require_once libfile('misc/'.$ac, 'include');

?>