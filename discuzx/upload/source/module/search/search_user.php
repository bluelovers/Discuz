<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_my.php 12937 2010-07-16 08:45:13Z zhouguoqiang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

$url = '';
if($_G['config']['app']['domain']['home'] || $_G['config']['app']['domain']['default']) {
	$port = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':'.$_SERVER['SERVER_PORT'];
	$appphp = '';
	if(!$_G['config']['app']['domain']['home']) {
		$appphp = 'home.php';
	}
	$url = 'http://'.$domain.$port.$_G['siteroot'].$appphp;
} else {
	$url = 'home.php';
}
$url .= '?mod=spacecp&ac=search';
if($_G['gp_srchtxt']) {
	$url .= '&username='.$_G['gp_srchtxt'].'&searchsubmit=yes';
}

header('Location: '.$url);

?>