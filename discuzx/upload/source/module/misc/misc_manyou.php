<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_manyou.php 14863 2010-08-16 08:56:21Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if($_G['gp_action'] == 'inviteCode') {
	$my_register_url = 'http://api.manyou.com/uchome.php';
	$response = dfsockopen($my_register_url, 0, 'action=inviteCode&app=search');
	showmessage($response, '', array(), array('msgtype' => 3, 'handle' => false));
}