<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: theme.inc.php 4335 2010-09-06 03:59:41Z fanshengshuai $
 */

if(!defined('IN_STORE') && !defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$wheresql = ' itemid=\''.$_G['myshopid'].'\'';
$_BCACHE->deltype('detail', 'shop', $_G['myshopid']);
require_once(B_ROOT.'./source/admininc/theme.inc.php');

?>