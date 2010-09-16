<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal.php 16429 2010-09-06 09:51:21Z monkey $
 */

define('APPTYPEID', 4);
define('CURSCRIPT', 'portal');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();

$cachelist = array('userapp', 'blockclass', 'portalcategory');
$discuz->cachelist = $cachelist;
$discuz->init();

require DISCUZ_ROOT.'./source/function/function_home.php';
require DISCUZ_ROOT.'./source/function/function_portal.php';

if(empty($_GET['mod']) || !in_array($_GET['mod'], array('list', 'view', 'comment', 'portalcp', 'topic', 'attachment'))) $_GET['mod'] = 'index';

define('CURMODULE', $_GET['mod']);
runhooks();

$navtitle = $_G['setting']['seotitle']['portal'];

require_once libfile('portal/'.$_GET['mod'], 'module');

?>