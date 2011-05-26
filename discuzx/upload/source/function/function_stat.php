<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_stat.php 17420 2010-10-18 12:13:39Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updatestat($type, $primary=0, $num=1) {
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
	$num = abs(intval($num));
	if(getcount('common_stat', array('daytime'=>$nowdaytime))) {
		DB::query("UPDATE ".DB::table('common_stat')." SET `$type`=`$type`+$num WHERE daytime='$nowdaytime'");
	} else {
		DB::query("DELETE FROM ".DB::table('common_statuser')." WHERE daytime != '$nowdaytime'");
		DB::insert('common_stat', array('daytime'=>$nowdaytime, $type=>$num));
	}
}

?>