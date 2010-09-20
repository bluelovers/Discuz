<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: theme.inc.php 3775 2010-07-16 07:46:52Z yexinhao $
 */

if(!defined('IN_STORE') && !defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$wheresql = ' itemid=\''.$_GET['itemid'].'\'';
$_BCACHE->deltype('detail', 'shop', $_GET['itemid']);
require_once(B_ROOT.'./source/admininc/theme.inc.php');

?>