<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_videophoto.php 15410 2010-08-24 02:55:14Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['setting']['videophoto'])) {
	showmessage('no_open_videophoto');
}

if($space['videophotostatus']) {
	space_merge($space, 'field_home');
	$videophoto = getvideophoto($space['videophoto']);
} else {
	$videophoto = '';
}
$actives = array('verify' =>' class="a"');
$opactives = array('videophoto' =>' class="a"');

$operation = 'verify';
$opactives = array('videophoto' =>' class="a"');
include template("home/spacecp_videophoto");
?>