<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_stat.php 12242 2010-07-01 08:09:23Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updatestat($type, $primary=0) {
	global $_G;

	if(empty($_G['uid']) || empty($_G['setting']['updatestat'])) return false;

	$nowdaytime = dgmdate($_G['timestamp'], 'Ymd');
	if($primary) {
		$setarr = array(
			'uid' => $_G['uid'],
			'daytime' => $nowdaytime,
			'type' => $type
		);
		if(getcount('common_statuser', $setarr)) {
			return false;
		} else {
			DB::insert('common_statuser', $setarr);
		}
	}
	if(getcount('common_stat', array('daytime'=>$nowdaytime))) {
		DB::query("UPDATE ".DB::table('common_stat')." SET `$type`=`$type`+1 WHERE daytime='$nowdaytime'");
	} else {
		DB::query("DELETE FROM ".DB::table('common_statuser')." WHERE daytime != '$nowdaytime'");
		DB::insert('common_stat', array('daytime'=>$nowdaytime, $type=>'1'));
	}
}

?>