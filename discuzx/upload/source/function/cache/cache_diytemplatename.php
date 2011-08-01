<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_diytemplatename.php 21671 2011-04-07 06:21:13Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_diytemplatename() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_diy_data'));
	$apps = array('portal', 'forum', 'group', 'home');
	$scriptarr = array();

	while($datarow = DB::fetch($query)){
		$langtplname = lang('portalcp', $datarow['targettplname'], '', lang('portalcp', 'diytemplate_name_null'));
		$datarow['name'] = $datarow['name'] ? $datarow['name'] : $langtplname;
		$data[$datarow['targettplname']] = dhtmlspecialchars($datarow['name']);
		$curscript = substr($datarow['targettplname'], 0, strpos($datarow['targettplname'], '/'));
		if(in_array($curscript, $apps)) {
			$scriptarr[$curscript][$datarow['targettplname']] = true;
		}
	}

	save_syscache('diytemplatename', $data);
	/*
	foreach($scriptarr as $curscript => $value) {
		save_syscache('diytemplatename'.$curscript, $value);
	}
	*/
	/**
	 * diytemplatename 強制寫入 save_syscache
	 * 來解決 loadcache 自動補充 cachedata 時
	 * 因為部分的 diytemplatename 沒有寫入緩存的問題而造成每次都更新緩存
	 *
	 * 降低多餘重複的 SQL 查詢
	 */
	foreach($apps as $curscript) {
		$value = $curscript[$curscript];
		save_syscache('diytemplatename'.$curscript, (array)$value);
	}
}

?>