<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_diytemplatename.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_diytemplatename() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_diy_data'));

	while($datarow = DB::fetch($query)){
		$langtplname = lang('portalcp', $datarow['targettplname'], '', lang('portalcp', 'diytemplate_name_null'));
		$datarow['name'] = $datarow['name'] ? $datarow['name'] : $langtplname;
		$data[$datarow['targettplname']] = dhtmlspecialchars($datarow['name']);
	}

	save_syscache('diytemplatename', $data);
}

?>