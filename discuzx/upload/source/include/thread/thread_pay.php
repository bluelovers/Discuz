<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_pay.php 13933 2010-08-03 08:15:01Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
	showmessage('credits_transaction_disabled');
}
$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
$payment = DB::fetch_first("SELECT COUNT(*) AS payers, SUM($extcredit) AS income
	FROM ".DB::table('common_credit_log')."
	WHERE relatedid='$_G[tid]' AND operation='STC'");

$thread['payers'] = $payment['payers'];
$thread['netprice'] = !$_G['setting']['maxincperthread'] || ($_G['setting']['maxincperthread'] && $payment['income'] < $_G['setting']['maxincperthread']) ? floor($thread['price'] * (1 - $_G['setting']['creditstax'])) : 0;
$thread['creditstax'] = sprintf('%1.2f', $_G['setting']['creditstax'] * 100).'%';
$thread['endtime'] = $_G['setting']['maxchargespan'] ? dgmdate($_G['forum_thread']['dateline'] + $_G['setting']['maxchargespan'] * 3600, 'u') : 0;
$thread['price'] = $_G['forum_thread']['price'];
$posttable = getposttablebytid($_G['tid']);
$firstpost = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1' LIMIT 1");
$pid = $firstpost['pid'];
$freemessage = array();
$freemessage[$pid]['message'] = '';
if(preg_match_all("/\[free\](.+?)\[\/free\]/is", $firstpost['message'], $matches)) {
	foreach($matches[1] AS $match) {
		$freemessage[$pid]['message'] .= discuzcode($match, $firstpost['smileyoff'], $firstpost['bbcodeoff'], sprintf('%00b', $firstpost['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], 0).'<br />';
	}
}

$attachtags = array();
if($_G['group']['allowgetattach']) {
	if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $freemessage[$pid]['message'], $matchaids)) {
		$attachtags[$pid] = $matchaids[1];
	}
}

if($attachtags) {
	require_once libfile('function/attachment');
	parseattach($pid, $attachtags, $freemessage);
}

$thread['freemessage'] = $freemessage[$pid]['message'];
unset($freemessage);

?>