<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	Discuz! [SC] (C)2000-2010 Bluelovers Net.
	This is NOT a freeware, use is subject to license terms

	$HeadURL: https://user-windows:8443/svn/discuz_sc/trunk/upload/extensions/default_hooks_cache.php $
	$Revision: 54 $
	$Author: bluelovers$
	$Date: 2009-11-20 07:31:29 +0800 (星期五, 20 十一月 2009) $
	$Id: default_hooks_cache.php 54 2009-11-19 23:31:29Z bluelovers $
*/

scAddHooks('UpdateCacheBefore', 'eUpdateCacheBefore');

function eUpdateCacheBefore (&$cachename, &$script, &$setting, &$data, &$dbcachename) {
	global $db, $tablepre;

	switch ($dbcachename) {
		case 'settings':
			require_once DISCUZ_ROOT.'./uc_client/client.php';

			$GLOBALS['version'] = $data['version'] = $data['version'].' [SC]';
			$GLOBALS['release'] = $data['release'] = DISCUZ_KERNEL_RELEASE;

			$data['totalmembers_gender'] = array();
			$data['totalmembers_gender'][0] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE gender='0'");
			$data['totalmembers_gender'][1] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE gender='1'");
			$data['totalmembers_gender'][2] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE gender='2'");

			$GLOBALS['totalmembers_gender'] = $data['totalmembers_gender'];

			$GLOBALS['doublee'] = $data['doublee'] = uc_api_call('sc', 'get_setting', array('fields'=>'doublee')) ? 1 : 0;

			if (!$data['post_subject_maxsize'] || $data['post_subject_maxsize'] < 80) {
				$GLOBALS['post_subject_maxsize'] = $data['post_subject_maxsize'] = 80;
			}

			break;
	}
}

scAddHooks('UpdateCacheBefore_bbcode_Before_init_regexp', 'eUpdateCacheBefore_bbcode_Before_init_regexp');

function eUpdateCacheBefore_bbcode_Before_init_regexp(&$conf) {

//	$regexp = array	(
//		1 => "/\[{bbtag}]([^\"\[]+?)\[\/{bbtag}\]/is",
//		2 => "/\[{bbtag}=(['\"]?)([^\"\[]+?)(['\"]?)\]([^\"\[]+?)\[\/{bbtag}\]/is",
//		3 => "/\[{bbtag}=(['\"]?)([^\"\[]+?)(['\"]?),(['\"]?)([^\"\[]+?)(['\"]?)\]([^\"\[]+?)\[\/{bbtag}\]/is"
//		);

//	$conf['regexp'][-1] = '/\[{bbtag}\](.+)\[\/{bbtag}\]/is';
////	$conf['regexp'][-2] = "/\[{bbtag}=(['\"]?)([^\"]+?)(['\"]?)\](.+?)\[\/{bbtag}\]/is";
//	$conf['regexp'][-2] = "/\[{bbtag}=(['\"]?)([^\"]+?)\\1\](.+?)\[\/{bbtag}\]/is";

	$conf['regexp'] = array	(
		1 => "/\[{bbtag}\]{1}\[\/{bbtag}\]/is",
		2 => "/\[{bbtag}=(['\"]?){1}(['\"]?)\]{2}\[\/{bbtag}\]/is",
		3 => "/\[{bbtag}=(['\"]?){1}(['\"]?),(['\"]?){2}(['\"]?)\]{3}\[\/{bbtag}\]/is",

		-1 => "/\[{bbtag}\]{1}\[\/{bbtag}\]/is",
		-2 => "/\[{bbtag}=(['\"]?){1}(['\"]?)\]{2}\[\/{bbtag}\]/is",
	);

	$GLOBALS['regexp_ex'] = array(
		'([^\"\[]+?)'	// dz 預設
		,'(\w+)'		// 英文+數字
		,'(\d+)'		// 數字
		, '([a-zA-Z]+)'	// 英文
		, '(.+?)'		// 任何字
		, '(.+)'		// 任何字(非空)
	);

}

scAddHooks('UpdateCacheBefore_bbcode_Before_define', 'eUpdateCacheBefore_bbcode_Before_define');

function eUpdateCacheBefore_bbcode_Before_define(&$conf) {
	extract($conf, EXTR_REFS);

	$search = str_replace('{bbtag}', $bbcode['tag'], $regexp[$bbcode['params']]);
	$bbcode['replacement'] = preg_replace("/([\t\r\n]+)/", '', $bbcode['replacement']);

	$bbcode['replacement'] = str_replace('{bbtag}', $bbcode['tag'], $bbcode['replacement']);

	if (!is_array($bbcode['pattern'])) $bbcode['pattern'] = $bbcode['pattern'] ? split("\t", $bbcode['pattern']) : array(0, 0, 0);

	for ($_i = 0; $_i < $bbcode['params']; $_i++) {
		$_j = $_i + 1;

		$search = str_replace('{'.$_j.'}', $GLOBALS['regexp_ex']["{$bbcode[pattern][$_i]}"], $search);
	}

	if (in_array($bbcode['tag'], array('msgbox'))) {
		$bbcode['params'] = 0 - abs($bbcode['params']);
	}

	$switchstop = 1;
}

scAddHooks('UpdateCacheBefore_bbcode_Before_switch', 'eUpdateCacheBefore_bbcode_Before_switch');

function eUpdateCacheBefore_bbcode_Before_switch(&$conf) {
	extract($conf, EXTR_REFS);

	$switchstop = 1;

	if ($bbcode['replacementtype'] > 0 && $bbcode['params'] > 0) {
		$bbcode['params'] = 0 - abs($bbcode['params']);
	}

	switch ($bbcode['params']) {
		case -2:
			$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);

			$replace = $bbcode['replacement'];

//			$search = str_replace('{bbtag}', $bbcode['tag'], $search);

			$search = str_replace("\[{$bbcode[tag]}", "\s*\[{$bbcode[tag]}", $search);
			$search = str_replace('/is', '\s*/ies', $search);

			if ($bbcode['replacementtype'] == 2) {
				$replace = '\''.str_replace('{2}', '\'.dhtmlspecialchars(\'\\4\',ENT_QUOTES).\'', $bbcode['replacement']).'\'';
			} else {
				$replace = '\''.str_replace('{2}', '\'.codedisp2(\'\\4\').\'', $bbcode['replacement']).'\'';
			}

			break;
		case -3:
			$bbcode['replacement'] = str_replace('{1}', '\\2', $bbcode['replacement']);
			$bbcode['replacement'] = str_replace('{2}', '\\5', $bbcode['replacement']);
			$bbcode['replacement'] = str_replace('{3}', '\\7', $bbcode['replacement']);

			$replace = $bbcode['replacement'];

			break;
		default:
			if ($bbcode['replacementtype'] > 0) {
//				$search = str_replace('{bbtag}', $bbcode['tag'], $search);

				$search = str_replace("\[{$bbcode[tag]}", "\s*\[{$bbcode[tag]}", $search);
				$search = str_replace('/is', '\s*/ies', $search);

				if ($bbcode['replacementtype'] == 2) {
					$replace = '\''.str_replace('{1}', '\'.dhtmlspecialchars(\'\\1\',ENT_QUOTES).\'', $bbcode['replacement']).'\'';
				} else {
					$replace = '\''.str_replace('{1}', '\'.codedisp2(\'\\1\').\'', $bbcode['replacement']).'\'';
				}
			} else {
				$switchstop = 0;
				$bbcode['params'] = abs($bbcode['params']);
			}

			break;
	}
}

scAddHooks('UpdateCacheBefore_bbcode_Before_define2', 'eUpdateCacheBefore_bbcode_Before_define2');

function eUpdateCacheBefore_bbcode_Before_define2(&$conf) {
	extract($conf, EXTR_REFS);

	if ($bbcode['replacementtype'] > 0) {
		$switchstop = 1;
	}
}

scAddHooks('UpdateCacheBefore_getcachearray_Switch_In_Else', 'eUpdateCacheBefore_getcachearray_Switch_In_Else');

function eUpdateCacheBefore_getcachearray_Switch_In_Else(&$cachename, &$script, &$table, &$cols, &$conditions) {
	global $timestamp, $tablepre;

	switch($cachename) {
		case 'newtopic':
			$fids ="58,83,67";
			//填上不需要顯示發新帖區域的 fid 編號, 以逗號作分格, 最後一個不用加上逗號 例如 "1,2,3";

			$table = "threads t LEFT JOIN {$tablepre}forums f ON t.fid=f.fid";
//			$cols = "t.fid, t.tid, t.subject, t.author, t.dateline, t.views, t.replies, t.lastpost, t.lastposter, t.highlight, f.name as forumname";
			$cols = "t.*, f.name as forumname";

			$conditions = "WHERE t.closed <= 1 AND t.displayorder >= 0 AND t.rate >= 0 AND NOT (f.fid IN ($fids)) ORDER BY dateline DESC LIMIT 10";
			break;
		case 'newreply':
			$fids ="58,83,67";
			//填上不需要顯示回文區域的 fid 編號, 以逗號作分格, 最後一個不用加上逗號 例如 "1,2,3";

			$table = "threads t LEFT JOIN {$tablepre}forums f ON t.fid=f.fid";
//			$cols = "t.fid, t.tid, t.subject, t.author, t.dateline, t.views, t.replies, t.lastpost, t.lastposter, t.highlight, f.name as forumname";
			$cols = "t.*, f.name as forumname";

			$conditions = "WHERE t.closed <= 1 AND t.displayorder >= 0 AND t.rate >= 0 AND NOT (f.fid IN ($fids)) AND t.replies > 0 ORDER BY lastpost DESC LIMIT 10";
			break;
		case 'hotreply':
			$fids ="58,83,67";
			//填上不需要顯示回文區域的 fid 編號, 以逗號作分格, 最後一個不用加上逗號 例如 "1,2,3";

			$ctime = $timestamp-3600*24*7;	//最後7是天數為本周

			$table = "threads t LEFT JOIN {$tablepre}forums f ON t.fid=f.fid";
			$cols = "t.*, f.name as forumname";
//			$conditions = "WHERE t.displayorder >= 0 AND t.replies > 0 AND t.dateline>$ctime ORDER BY t.replies DESC, t.lastpost DESC LIMIT 10";
			$conditions = "WHERE t.closed <= 1 AND t.displayorder >= 0 AND t.rate >= 0 AND NOT (f.fid IN ($fids)) AND t.views >= 0 AND t.dateline>$ctime ORDER BY t.views DESC, t.replies DESC, t.lastpost DESC LIMIT 10";
			break;
		case 'hotposter':

			$ctime = $timestamp-3600*24*7;	//最後7是天數為本周

			$table = "posts";
			$cols = "count(pid) as num,authorid,author";
			$conditions = "WHERE dateline>=$ctime GROUP BY authorid ORDER BY num DESC, dateline DESC, author LIMIT 10";
			break;
	}
}

?>