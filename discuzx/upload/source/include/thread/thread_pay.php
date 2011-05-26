<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_pay.php 19710 2011-01-17 02:03:39Z monkey $
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
$firstpost = DB::fetch_first("SELECT p.*,m.groupid FROM ".DB::table($posttable)." p LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid WHERE tid='$_G[tid]' AND first='1' LIMIT 1");
$pid = $firstpost['pid'];
$freemessage = array();
$freemessage[$pid]['message'] = '';
if(preg_match_all("/\[free\](.+?)\[\/free\]/is", $firstpost['message'], $matches)) {
	foreach($matches[1] AS $match) {
		$freemessage[$pid]['message'] .= discuzcode($match, $firstpost['smileyoff'], $firstpost['bbcodeoff'], sprintf('%00b', $firstpost['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'] ? -$firstpost['groupid'] : 0, $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['forum']['allowmediacode'], $pid).'<br />';
	}
}

$attachtags = array();
if($_G['group']['allowgetattach'] || $_G['group']['allowgetimage']) {
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