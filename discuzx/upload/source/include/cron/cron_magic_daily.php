<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_magic_daily.php 19669 2011-01-13 06:48:56Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!empty($_G['setting']['magicstatus'])) {
	$magicarray = array();
	$query = DB::query("SELECT magicid, supplytype, supplynum, num FROM ".DB::table('common_magic')." WHERE available='1'");
	while($magic = DB::fetch($query)) {
		if($magic['supplytype'] && $magic['supplynum']) {
			$magicarray[$magic['magicid']]['supplytype'] = $magic['supplytype'];
			$magicarray[$magic['magicid']]['supplynum'] = $magic['supplynum'];
		}
	}

	list($daynow, $weekdaynow) = explode('-', dgmdate(TIMESTAMP, 'd-w', $_G['setting']['timeoffset']));

	foreach($magicarray as $id => $magic) {
		$autosupply = 0;
		if($magic['supplytype'] == 1) {
			$autosupply = 1;
		} elseif($magic['supplytype'] == 2 && $weekdaynow == 1) {
			$autosupply = 1;
		} elseif($magic['supplytype'] == 3 && $daynow == 1) {
			$autosupply = 1;
		}

		if(!empty($autosupply)) {
			DB::query("UPDATE ".DB::table('common_magic')." SET num='$magic[supplynum]' WHERE magicid='$id'");
		}
	}
}

?>