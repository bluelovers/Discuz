<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_index.php 16573 2010-09-09 05:40:43Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = $_G['setting']['seotitle']['portal'];
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][1]['navname'];
}

$metakeywords = $_G['setting']['seokeywords']['portal'];
if(!$metakeywords) {
	$metakeywords = $_G['setting']['navs'][1]['navname'];
}

$metadescription = $_G['setting']['seodescription']['portal'];
if(!$metadescription) {
	$metadescription = $_G['setting']['navs'][1]['navname'];
}
include_once template('diy:portal/index');
?>