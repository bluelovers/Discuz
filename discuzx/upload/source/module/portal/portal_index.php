<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_index.php 16700 2010-09-13 05:46:20Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['portal']);
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][1]['navname'];
} else {
	$nobbname = true;
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