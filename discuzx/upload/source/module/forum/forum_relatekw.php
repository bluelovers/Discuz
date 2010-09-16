<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_relatekw.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['tagstatus']) {
	exit;
}

if($tid = @intval($_GET['tid'])) {
	$posttable = getposttablebytid($tid);
	$query = DB::query("SELECT pid, subject, message FROM ".DB::table($posttable)." WHERE tid='$tid' AND first='1'");
	$data = DB::fetch($query);
	$subject = $data['subject'];
	$message = cutstr($data['message'], 500, '');
	$pid = $data['pid'];
} else {
	$subject = $_GET['subjectenc'];
	$message = $_GET['messageenc'];
}

$subjectenc = rawurlencode(strip_tags($subject));
$messageenc = rawurlencode(strip_tags(preg_replace("/\[.+?\]/U", '', $message)));
$data = @implode('', file("http://keyword.discuz.com/related_kw.html?ics=".CHARSET."&ocs=".CHARSET."&title=$subjectenc&content=$messageenc"));

if($data) {

	if(PHP_VERSION > '5' && CHARSET != 'utf-8') {
		require_once libfile('class/chinese');
		$chs = new Chinese('utf-8', CHARSET);
	}

	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $index);
	xml_parser_free($parser);

	$kws = array();

	foreach($values as $valuearray) {
		if($valuearray['tag'] == 'kw' || $valuearray['tag'] == 'ekw') {
			$kws[] = !empty($chs) ? $chs->convert(trim($valuearray['value'])) : trim($valuearray['value']);
		}
	}

	$return = '';
	if($kws) {
		foreach($kws as $kw) {
			$kw = htmlspecialchars($kw);
			$return .= $kw.' ';
		}
		$return = htmlspecialchars($return);
	}

	if(!$tid) {
		$_G['inajax'] = 1;
		include template('forum/relatekw');
	} elseif($_G['setting']['tagstatus'] && $kws) {
		loadcache('censor');
		$posttable = getposttablebytid($_G['tid']);
		DB::query("UPDATE ".DB::table($posttable)." SET tags='".implode(',', $kws)."' WHERE pid='$pid'");
	}
}

?>