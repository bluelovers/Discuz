<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_search.php 23827 2011-08-11 04:33:02Z zhouguoqiang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function searchkey($keyword, $field, $returnsrchtxt = 0) {
	$srchtxt = '';
	if($field && $keyword) {
		if(preg_match("(AND|\+|&|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
			$andor = ' AND ';
			$keywordsrch = '1';
			$keyword = preg_replace("/( AND |&| )/is", "+", $keyword);
		} else {
			$andor = ' OR ';
			$keywordsrch = '0';
			$keyword = preg_replace("/( OR |\|)/is", "+", $keyword);
		}
		$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
		$srchtxt = $returnsrchtxt ? $keyword : '';
		foreach(explode('+', $keyword) as $text) {
			$text = trim($text);
			if($text) {
				$keywordsrch .= $andor;
				$keywordsrch .= str_replace('{text}', $text, $field);
			}
		}
		$keyword = " AND ($keywordsrch)";
	}
	return $returnsrchtxt ? array($srchtxt, $keyword) : $keyword;
}

function highlight($text, $words, $prepend) {
	$text = str_replace('\"', '"', $text);
	foreach($words AS $key => $replaceword) {
		$text = str_replace($replaceword, '<highlight>'.$replaceword.'</highlight>', $text);
	}
	return "$prepend$text";
}

function bat_highlight($message, $words, $color = '#ff0000') {
	if(!empty($words)) {
		$highlightarray = explode(' ', $words);
		$sppos = strrpos($message, chr(0).chr(0).chr(0));
		if($sppos !== FALSE) {
			$specialextra = substr($message, $sppos + 3);
			$message = substr($message, 0, $sppos);
		}
		$message = preg_replace(array("/(^|>)([^<]+)(?=<|$)/sUe", "/<highlight>(.*)<\/highlight>/siU"), array("highlight('\\2', \$highlightarray, '\\1')", "<strong><font color=\"$color\">\\1</font></strong>"), $message);
		if($sppos !== FALSE) {
			$message = $message.chr(0).chr(0).chr(0).$specialextra;
		}
	}
	return $message;
}

function search_get_forum_hash() {
	global $_G;
	require_once DISCUZ_ROOT . './api/manyou/Manyou.php';
	loadcache('search_forum_hash');
	if (TIMESTAMP - $_G['cache']['search_forum_hash']['ts'] > 21600) {
		$my_forums = SearchHelper::getForums();
		$data = array('ts' => TIMESTAMP, 'sign' => $my_forums['sign']);
		save_syscache('search_forum_hash', $data);
	} else {
		$data = $_G['cache']['search_forum_hash'];

	}
	return $data['sign'];
}

function search_get_usergroups($groupIds) {
	global $_G;

	require_once DISCUZ_ROOT . './api/manyou/Manyou.php';

	$missGroupIds = array();
	$res = array();
	foreach($groupIds as $groupId) {
		$kname = 'search_group_hash_' . $groupId;
		loadcache($kname);
		if (TIMESTAMP - $_G['cache'][$kname]['ts'] > 21600) {
			$missGroupIds[] = $groupId;
		} else {
			$res[$groupId]['sign'] = $_G['cache'][$kname]['sign'];
		}
	}

	if ($missGroupIds) {
		$userGroups = SearchHelper::getUserGroupPermissions($missGroupIds);
		foreach($userGroups as $groupId => $userGroup) {
			$kname = $kname = 'search_group_hash_' . $groupId;
			$data = array('ts' => TIMESTAMP, 'sign' => $userGroup['sign']);
			save_syscache($kname, $data);
			$res[$groupId]['sign'] = $userGroup['sign'];
		}
	}
	return $res;
}

?>