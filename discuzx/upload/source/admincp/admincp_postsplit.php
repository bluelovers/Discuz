<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_postsplit.php 26253 2011-12-07 06:35:01Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IN_DEBUG', false);

@set_time_limit(0);
define('MAX_POSTS_MOVE', 100000);
cpheader();
$topicperpage = 50;

if(empty($operation)) {
	$operation = 'manage';
}

$query = DB::query("SELECT skey, svalue FROM ".DB::table('common_setting')." WHERE skey IN ('posttable_info', 'posttableids', 'threadtableids')");
while($var = DB::fetch($query)) {
	switch($var['skey']) {
		case 'posttable_info':
			$posttable_info = $var['svalue'];
			break;
		case 'posttableids':
			$posttableids = $var['svalue'];
			break;
		case 'threadtableids':
			$threadtableids = $var['svalue'];
			break;
	}
}

if(empty($posttable_info)) {
	$posttable_info = array();
	$posttable_info[0]['type'] = 'primary';
} else {
	$posttable_info = unserialize($posttable_info);
}

if(empty($posttableids)) {
	$posttableids = array();
} else {
	$posttableids = unserialize($posttableids);
}

if($operation == 'manage') {
	shownav('founder', 'nav_postsplit');
	if(!submitcheck('postsplit_manage')) {

		showtips('postsplit_manage_tips');
		showformheader('postsplit&operation=manage');
		showtableheader();

		showsubtitle(array('postsplit_manage_tablename', 'postsplit_manage_datalength', 'postsplit_manage_table_memo', ''));


		$tablename = DB::table('forum_post');
		$tableid = 0;
		$tablestatus = gettablestatus($tablename);
		$postcount = $tablestatus['Rows'];
		$data_length = $tablestatus['Data_length'];
		$index_length = $tablestatus['Index_length'];



		$opstr = '<a href="'.ADMINSCRIPT.'?action=postsplit&operation=split&tableid=0">'.cplang('postsplit_name').'</a>';
		showtablerow('', array('', '', '', 'class="td25"'), array($tablename, $data_length, "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$posttable_info[0]['memo']}\" />", $opstr));

		$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
		while($table = DB::fetch($query)) {
			list($tempkey, $tablename) = each($table);
			$tableid = gettableid($tablename);
			if(!preg_match('/^\d+$/', $tableid)) {
				continue;
			}
			$tablestatus = gettablestatus($tablename);

			$opstr = '<a href="'.ADMINSCRIPT.'?action=postsplit&operation=split&tableid='.$tableid.'">'.cplang('postsplit_name').'</a>';
			showtablerow('', array('', '', '', 'class="td25"'), array($tablename, $tablestatus['Data_length'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$posttable_info[$tableid]['memo']}\" />", $opstr));
		}
		showsubmit('postsplit_manage', 'postsplit_manage_update_memo_submit');
		showtablefooter();
		showformfooter();
	} else {
		$posttable_info = array();
		foreach($_G['gp_memo'] as $key => $value) {
			$key = intval($key);
			$posttable_info[$key]['memo'] = $value;
		}

		DB::insert('common_setting', array(
			'skey' => 'posttable_info',
			'svalue' => daddslashes(serialize($posttable_info)),
		), false, true);
		save_syscache('posttable_info', $posttable_info);
		update_posttableids();
		updatecache('setting');

		cpmsg('postsplit_table_memo_update_succeed', 'action=postsplit&operation=manage', 'succeed');
	}
} elseif($operation == 'split') {

	if(!$_G['setting']['bbclosed']) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}

	$tableid = intval($_G['gp_tableid']);
	$tablename = getposttable($tableid);
	if($tableid && $tablename != 'forum_post' || !$tableid) {
		$status = gettablestatus(DB::table($tablename), false);
		$allowsplit = false;

		if($status && ((!$tableid && $status['Data_length'] > 400 * 1048576) || ($tableid && $status['Data_length']))) {

			if(!submitcheck('splitsubmit')) {

				showtips('postsplit_manage_tips');
				showformheader('postsplit&operation=split&tableid='.$tableid);
				showtableheader();
				showsetting('postsplit_from', '', '', getposttable($tableid).(!empty($posttable_info[$tableid]['memo']) ? '('.$posttable_info[$tableid]['memo'].')' : ''));
				$tablelist = '<option value="-1">'.cplang('postsplit_create').'</option>';
				foreach($posttable_info as $tid => $info) {
					if($tableid != $tid) {
						$tablestatus = gettablestatus(DB::table(getposttable($tid)));
						$tablelist .= '<option value="'.$tid.'">'.($info['memo'] ? $info['memo'] : 'forum_post'.($tid ? '_'.$tid : '')).'('.$tablestatus['Data_length'].')'.'</option>';
					}
				}
				showsetting('postsplit_to', '', '', '<select onchange="if(this.value >= 0) {$(\'tableinfo\').style.display = \'none\';} else {$(\'tableinfo\').style.display = \'\';}" name="targettable">'.$tablelist.'</select>');
				showtagheader('tbody', 'tableinfo', true, 'sub');
				showsetting('postsplit_manage_table_memo', "memo", '', 'text');
				showtagfooter('tbody');

				$datasize = round($status['Data_length'] / 1048576);
				$maxsize = round(($datasize - ($tableid ? 0 : 300)) / 100);
				$maxi = $maxsize > 10 ? 10 : ($maxsize < 1 ? 1 : $maxsize);
				for($i = 1; $i <= $maxi; $i++) {
					$movesize = $i == 10 ? 1024 : $i * 100;
					$maxsizestr .= '<option value="'.$movesize.'">'.($i == 10 ? sizecount($movesize * 1048576) : $movesize.'MB').'</option>';
				}
				showsetting('postsplit_move_size', '', '', '<select name="movesize">'.$maxsizestr.'</select>');

				showsubmit('splitsubmit', 'postsplit_manage_submit');
				showtablefooter();
				showformfooter();
			} else {

				$targettable = intval($_G['gp_targettable']);
				$createtable = false;
				if($targettable == -1) {
					$maxtableid = getmaxposttableid();
					DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
					$tableinfo = DB::fetch_first("SHOW CREATE TABLE ".DB::table(getposttable()));
					$createsql = $tableinfo['Create Table'];
					$targettable = $maxtableid + 1;
					$newtable = 'forum_post_'.$targettable;
					$createsql = str_replace(getposttable(), $newtable, $createsql);
					DB::query($createsql);

					$posttable_info[$targettable]['memo'] = $_G['gp_memo'];
					DB::insert('common_setting', array(
						'skey' => 'posttable_info',
						'svalue' => daddslashes(serialize($posttable_info)),
					), false, true);
					save_syscache('posttable_info', $posttable_info);
					update_posttableids();
					$createtable = true;
				}
				$sourcetablearr = gettablefields(getposttable($tableid));
				$targettablearr = gettablefields(getposttable($targettable));
				$fields = array_diff(array_keys($sourcetablearr), array_keys($targettablearr));
				if(!empty($fields)) {
					cpmsg('postsplit_do_error', '', '', array('tableid' => DB::table(getposttable($targettable)), 'fields' => implode(',', $fields)));
				}

				$movesize = intval($_G['gp_movesize']);
				$movesize = $movesize >= 100 && $movesize <= 1024 ? $movesize : 100;
				$targetstatus = gettablestatus(DB::table(getposttable($targettable)), false);
				$hash = urlencode(authcode("$tableid\t$movesize\t$targettable\t$targetstatus[Data_length]", 'ENCODE'));
				if($createtable) {
					cpmsg('postsplit_table_create_succeed', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettable.'&hash='.$hash, 'loadingform');
				} else {
					cpmsg('postsplit_finish', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettable.'&hash='.$hash, 'loadingform');
				}

			}
		} else {
			cpmsg('postsplit_unallow', 'action=postsplit');
		}
	}

} elseif($operation == 'movepost') {

	if(!$_G['setting']['bbclosed']) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}
	list($tableid, $movesize, $targettableid, $sourcesize) = explode("\t", urldecode(authcode($_G['gp_hash'])));
	$hash = urlencode($_G['gp_hash']);

	if($tableid == $_G['gp_fromtable'] && $movesize == $_G['gp_movesize'] && $targettableid == $_G['gp_targettable']) {
		$fromtableid = intval($_G['gp_fromtable']);
		$movesize = intval($_G['gp_movesize']);
		$targettableid = intval($_G['gp_targettable']);

		$targettable = gettablefields(getposttable($targettableid));
		$fieldstr = '`'.implode('`, `', array_keys($targettable)).'`';

		loadcache('threadtableids');
		$threadtableids = array(0);
		if(!empty($_G['cache']['threadtableids'])) {
			$threadtableids = array_merge($threadtableids, $_G['cache']['threadtableids']);
		}
		$tableindex = intval(!empty($_G['gp_tindex']) ? $_G['gp_tindex'] : 0);
		if(isset($threadtableids[$tableindex])) {

			if(!$fromtableid) {
				$threadtableid = $threadtableids[$tableindex];
				$table = $threadtableid > 0 ? "forum_thread_{$threadtableid}" : 'forum_thread';

				$count = DB::result_first("SELECT count(*) FROM ".DB::table($table)." WHERE  posttableid='0' AND displayorder>='0'");
				if($count) {
					$query = DB::query("SELECT tid FROM ".DB::table($table)." WHERE posttableid='0' AND displayorder>='0' ORDER BY lastpost LIMIT 0, 1000");
					movedate($query);
				}
				if($tableindex+1 < count($threadtableids)) {
					$tableindex++;
					$status = gettablestatus(DB::table(getposttable($targettableid)), false);
					$targetsize = $sourcesize + $movesize * 1048576;
					$nowdatasize = $targetsize - $status['Data_length'];

					cpmsg('postsplit_doing', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettableid.'&hash='.$hash.'&tindex='.$tableindex, 'loadingform', array('datalength' => sizecount($status['Data_length']), 'nowdatalength' => sizecount($nowdatasize)));
				}

			} else {
				$count = DB::result_first("SELECT count(*) FROM ".DB::table(getposttable($fromtableid))." WHERE `first`='1'");
				if($count) {
					$query = DB::query("SELECT tid FROM ".DB::table(getposttable($fromtableid))." WHERE `first`='1' LIMIT 0, 1000");
					movedate($query);
				} else {
					cpmsg('postsplit_done', 'action=postsplit&operation=optimize&tableid='.$fromtableid, 'form');
				}

			}
		}


	} else {
		cpmsg('postsplit_abnormal', 'action=postsplit', 'succeed');
	}
} elseif($operation == 'optimize') {

	if(!$_G['setting']['bbclosed']) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}

	$fromtableid = intval($_G['gp_tableid']);
	$optimize = true;
	$tablename = getposttable($fromtableid);
	if($fromtableid && $tablename != 'forum_post') {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table($tablename));
		if(!$count) {
			DB::query("DROP TABLE ".DB::table($tablename));

			unset($posttable_info[$fromtableid]);
			DB::insert('common_setting', array(
				'skey' => 'posttable_info',
				'svalue' => daddslashes(serialize($posttable_info)),
			), false, true);
			save_syscache('posttable_info', $posttable_info);
			update_posttableids();
			$optimize = false;
		}

	}
	if($optimize) {
		DB::query("OPTIMIZE TABLE ".DB::table($tablename), 'SILENT');
	}
	cpmsg('postsplit_do_succeed', 'action=postsplit', 'succeed');

} elseif($operation == 'pidreset') {
	loadcache('posttableids');
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	$pidmax = 0;
	foreach($posttableids as $id) {
		if($id == 0) {
			$pidtmp = DB::result_first("SELECT MAX(pid) FROM ".DB::table('forum_post'));
		} else {
			$pidtmp = DB::result_first("SELECT MAX(pid) FROM ".DB::table("forum_post_$id"));
		}
		if($pidtmp > $pidmax) {
			$pidmax = $pidtmp;
		}
	}
	$auto_increment = $pidmax + 1;
	DB::query("ALTER TABLE ".DB::table('forum_post_tableid')." AUTO_INCREMENT=$auto_increment");
	cpmsg('postsplit_resetpid_succeed', 'action=postsplit&operation=manage', 'succeed');
}

function gettableid($tablename) {
	$tableid = substr($tablename, strrpos($tablename, '_') + 1);
	return $tableid;
}

function getmaxposttableid() {
	$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
	$maxtableid = 0;
	while($table = DB::fetch($query)) {
		list($tempkey, $tablename) = each($table);
		$tableid = intval(gettableid($tablename));
		if($tableid > $maxtableid) {
			$maxtableid = $tableid;
		}
	}
	return $maxtableid;
}

function update_posttableids() {
	$tableids = get_posttableids();
	DB::insert('common_setting', array(
		'skey' => 'posttableids',
		'svalue' => serialize($tableids),
	), false, true);
	save_syscache('posttableids', $tableids);
}

function get_posttableids() {
	$tableids = array(0);
	$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
	while($table = DB::fetch($query)) {
		list($tempkey, $tablename) = each($table);
		$tableid = gettableid($tablename);
		if(!preg_match('/^\d+$/', $tableid)) {
			continue;
		}
		$tableid = intval($tableid);
		if(!$tableid) {
			continue;
		}
		$tableids[] = $tableid;
	}
	return $tableids;
}

function gettablestatus($tablename, $formatsize = true) {
	$status = DB::fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', $tablename)."'");

	if($formatsize) {
		$status['Data_length'] = sizecount($status['Data_length']);
		$status['Index_length'] = sizecount($status['Index_length']);
	}

	return $status;
}

function gettablefields($table) {
	static $tables = array();

	if(!isset($tables[$table])) {
		$tables[$table] = array();
		$db = DB::object();
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM ".DB::table($table), 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM ".DB::table($table), 'SILENT');
		}
		while($field = @DB::fetch($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}

function movedate($query) {
	global $sourcesize, $tableid, $movesize, $targettableid, $hash, $tableindex, $threadtableids, $fieldstr, $fromtableid, $posttable_info;
	$tids = array();
	while($value = DB::fetch($query)) {
		$tids[$value['tid']] = $value['tid'];
	}
	$fromtable = getposttable($fromtableid, true);
	$condition = " tid IN(".dimplode($tids).")";
	DB::query("INSERT INTO ".DB::table(getposttable($targettableid))." ($fieldstr) SELECT $fieldstr FROM $fromtable WHERE $condition", 'SILENT');
	if(DB::errno()) {
		DB::delete(getposttable($targettableid), $condition);
	} else {
		foreach($threadtableids as $threadtableid) {
			$table = $threadtableid ? "forum_thread_$threadtableid" : 'forum_thread';
			DB::update($table, array(
				'posttableid' => $targettableid,
			), $condition);
			if(DB::affected_rows() == count($tids)) {
				break;
			}
		}
		DB::delete(getposttable($fromtableid), $condition);

	}
	$status = gettablestatus(DB::table(getposttable($targettableid)), false);
	$targetsize = $sourcesize + $movesize * 1048576;
	$nowdatasize = $targetsize - $status['Data_length'];

	if($status['Data_length'] >= $targetsize) {
		cpmsg('postsplit_done', 'action=postsplit&operation=optimize&tableid='.$fromtableid, 'form');
	}

	cpmsg('postsplit_doing', 'action=postsplit&operation=movepost&fromtable='.$tableid.'&movesize='.$movesize.'&targettable='.$targettableid.'&hash='.$hash.'&tindex='.$tableindex, 'loadingform', array('datalength' => sizecount($status['Data_length']), 'nowdatalength' => sizecount($nowdatasize)));
}

?>