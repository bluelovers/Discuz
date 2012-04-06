<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_stamps.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_stamps() {
	$data = array();
	$query = DB::query("SELECT id, url, displayorder FROM ".DB::table('common_smiley')." WHERE type IN ('stamp','stamplist') ORDER BY displayorder");

	$fillarray = range(0, 99);
	$count = 0;
	$repeats = $stampicon = array();
	while($stamp = DB::fetch($query)) {
		if(isset($fillarray[$stamp['displayorder']])) {
			unset($fillarray[$stamp['displayorder']]);
		} else {
			$repeats[] = $stamp['id'];
		}
		$count++;
	}
	foreach($repeats as $id) {
		reset($fillarray);
		$displayorder = current($fillarray);
		unset($fillarray[$displayorder]);
		DB::query("UPDATE ".DB::table('common_smiley')." SET displayorder='$displayorder' WHERE id='$id'");
	}
	$query = DB::query("SELECT typeid, displayorder FROM ".DB::table('common_smiley')." WHERE type='stamplist' AND typeid>'0' ORDER BY displayorder");
	while($stamp = DB::fetch($query)) {
		$stamporder = DB::result_first("SELECT displayorder FROM ".DB::table('common_smiley')." WHERE id='$stamp[typeid]' AND type='stamp'");
		$stampicon[$stamporder] = $stamp['displayorder'];
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_smiley')." WHERE type IN ('stamp','stamplist') ORDER BY displayorder");
	while($stamp = DB::fetch($query)) {
		$icon = $stamp['type'] == 'stamp' ? (isset($stampicon[$stamp['displayorder']]) ? $stampicon[$stamp['displayorder']] : 0) :
			($stamp['type'] == 'stamplist' && !in_array($stamp['displayorder'], $stampicon) ? 1 : 0);
		$data[$stamp['displayorder']] = array('url' => $stamp['url'], 'text' => $stamp['code'], 'type' => $stamp['type'], 'icon' => $icon);
	}

	save_syscache('stamps', $data);
}

?>