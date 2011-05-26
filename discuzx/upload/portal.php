<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal.php 21671 2011-04-07 06:21:13Z zhangguosheng $
 */

define('APPTYPEID', 4);
define('CURSCRIPT', 'portal');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();

$cachelist = array('userapp', 'portalcategory', 'diytemplatenameportal');
$discuz->cachelist = $cachelist;
$discuz->init();

require DISCUZ_ROOT.'./source/function/function_home.php';
require DISCUZ_ROOT.'./source/function/function_portal.php';

if(empty($_GET['mod']) || !in_array($_GET['mod'], array('list', 'view', 'comment', 'portalcp', 'topic', 'attachment', 'rss'))) $_GET['mod'] = 'index';

if($_GET['mod'] != 'portalcp' && !$_G['setting']['portalstatus']) {
	showmessage('portal_status_off');
}

define('CURMODULE', $_GET['mod']);
runhooks();

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['portal']);

require_once libfile('portal/'.$_GET['mod'], 'module');

?>