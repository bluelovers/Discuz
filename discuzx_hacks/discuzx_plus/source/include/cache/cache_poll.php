<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_poll.php 646 2010-09-13 03:37:40Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

static $cachelist = array();

$updatelist = empty($cachename) ? $cachelist : (is_array($cachename) ? $cachename : array($cachename));
foreach($updatelist as $value) {
	poll_getcachearray($value);
}

function poll_getcachearray($cachename, $script = '') {
	global $_G;

	$cols = '*';
	$conditions = '';
	$timestamp = TIMESTAMP;
	switch($cachename) {
		case 'poll_setting':
			$table = 'poll_setting';
			$conditions = '';
			break;
	}

	$data = array();
	if($cols && $table) {
		$query = DB::query("SELECT $cols FROM ".DB::table($table)." $conditions");
	}

	switch($cachename) {
		case 'poll_setting':
			while($datarow = DB::fetch($query)) {
				$data[$datarow['skey']] = $datarow['svalue'];
			}
		default:
			while($datarow = DB::fetch($query)) {
				$data[] = $datarow;
			}
	}

	save_syscache($cachename, $data);
	return true;
}

?>