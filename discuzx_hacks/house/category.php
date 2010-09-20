<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: category.php 7024 2010-03-28 06:39:41Z cnteacher $
 */

// 定義應用 ID
define('APPTYPEID', 99);
define('CURSCRIPT', 'category');

//====================================
// 基礎文件引入， 其他程序引導文件可能不需要
// class_forum.php 和 function_forum.php
// 請根據實際需要確定是否引入
//====================================

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

//====================================
// 加載核心處理,各程序入口文件代碼相同
//====================================
$discuz->init();

//=======================
//加載 mod
//===================================
$modarray = array('index', 'list', 'view', 'post', 'misc', 'my', 'threadmod', 'usergroup');
// 判斷 $mod 的合法性
$mod = !in_array($_G['mod'], $modarray) ? 'index' : $_G['mod'];

require DISCUZ_ROOT.'./source/module/category/category_'.$mod.'.php';

?>