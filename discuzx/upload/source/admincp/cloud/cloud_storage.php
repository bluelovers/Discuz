<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_storage.php 29038 2012-03-23 06:22:39Z songlixin $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$_GET['anchor'] = in_array($_GET['anchor'], array('base')) ? $_GET['anchor'] : 'base';

shownav('navcloud', 'cloud_storage');

showsubmenu('cloud_storage');
showtips('cloud_storage_tips');