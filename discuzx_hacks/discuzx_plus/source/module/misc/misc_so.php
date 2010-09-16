<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_so.php 588 2010-09-06 05:21:38Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

ob_end_clean();
ob_start();
@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");
@header("Content-type: application/xml; charset=UTF-8");

$module = $_G['gp_module'];
$pollid = intval($_G['gp_pollid']);

if($module == 'poll') {
	if($pollid) {
		$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).'_'.$_G['siteurl'].'_'.$pollid.'_'.$_G['timestamp']);
		$config_cookie = getglobal('config/cookie');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><parameter><config><hash><![CDATA[$swfhash]]></hash><pollid>{$pollid}</pollid><dateline>{$_G['timestamp']}</dateline></config><cookie><cookiepre><![CDATA[{$config_cookie['cookiepre']}]]></cookiepre><cookiedomain><![CDATA[{$config_cookie['cookiedomain']}]]></cookiedomain><cookiepath><![CDATA[{$config_cookie['cookiepath']}]]></cookiepath></cookie></parameter>";
	}
}

?>