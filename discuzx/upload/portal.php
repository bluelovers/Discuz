<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal.php 16832 2010-09-15 07:38:31Z wangjinbo $
 */

define('APPTYPEID', 4);
define('CURSCRIPT', 'portal');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();

$cachelist = array('userapp', 'portalcategory');
$discuz->cachelist = $cachelist;
$discuz->init();

//require DISCUZ_ROOT.'./source/function/function_home.php';
//require DISCUZ_ROOT.'./source/function/function_portal.php';
require_once libfile('function/home');
require_once libfile('function/portal');

if(empty($_GET['mod']) || !in_array($_GET['mod'], array('list', 'view', 'comment', 'portalcp', 'topic', 'attachment'))) $_GET['mod'] = 'index';

define('CURMODULE', $_GET['mod']);
runhooks();

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['portal']);

require_once libfile('portal/'.$_GET['mod'], 'module');

?>