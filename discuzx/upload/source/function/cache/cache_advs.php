<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_advs.php 16698 2010-09-13 05:22:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_advs() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_advertisement')." WHERE available>'0' AND starttime<='".TIMESTAMP."' ORDER BY displayorder");

	$data['code'] = $data['parameters'] = $data['evalcode'] = array();
	$advlist = array();
	while($adv = DB::fetch($query)) {
		foreach(explode("\t", $adv['targets']) as $target) {
			$data['code'][$target][$adv['type']][$adv['advid']] = $adv['code'];
		}
		$advtype_class = libfile('adv/'.$adv['type'], 'class');
		if(!file_exists($advtype_class)) {
			continue;
		}
		require_once $advtype_class;
		$advclass = 'adv_'.$adv['type'];
		$advclass = new $advclass;
		$adv['parameters'] = unserialize($adv['parameters']);
		unset($adv['parameters']['style'], $adv['parameters']['html'], $adv['parameters']['displayorder']);
		$data['parameters'][$adv['type']][$adv['advid']] = $adv['parameters'];
		if($adv['parameters']['extra']) {
			$data['parameters'][$adv['type']][$adv['advid']] = array_merge($data['parameters'][$adv['type']][$adv['advid']], $adv['parameters']['extra']);
			unset($data['parameters'][$adv['type']][$adv['advid']]['extra']);
		}
		$advlist[] = $adv;
		$data['evalcode'][$adv['type']] = $advclass->evalcode($adv);
	}
	updateadvtype();

	save_syscache('advs', $data);
}

function updateadvtype() {
	global $_G;

	$query = DB::query("SELECT type FROM ".DB::table('common_advertisement')." WHERE available>'0' AND starttime<='".TIMESTAMP."' ORDER BY displayorder");
	$advtype = array();
	while($row = DB::fetch($query)) {
		$advtype[$row['type']] = 1;
	}
	$_G['setting']['advtype'] = $advtype = array_keys($advtype);
	$advtype = addslashes(serialize($advtype));
	if(!DB::result_first("SELECT count(*) FROM ".DB::table('common_setting')." WHERE skey='advtype'")) {
		DB::query("INSERT INTO ".DB::table('common_setting')." SET skey='advtype', svalue='$advtype'");
	} else {
		DB::query("UPDATE ".DB::table('common_setting')." SET svalue='$advtype' WHERE skey='advtype'");
	}
}

?>