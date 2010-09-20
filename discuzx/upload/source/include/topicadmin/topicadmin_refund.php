<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_refund.php 16938 2010-09-17 04:37:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowrefund'] || $thread['price'] <= 0) {
	showmessage('undefined_action', NULL);
}

if(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
	showmessage('credits_transaction_disabled');
}

if($thread['special'] != 0) {
	showmessage('special_refundment_invalid');
}

if(!submitcheck('modsubmit')) {

	$extcredit = 'extcredits'.$_G['setting']['creditstransextra'][1];
	$payment = DB::fetch_first("SELECT COUNT(*) AS payers, SUM($extcredit) AS netincome FROM ".DB::table('common_credit_log')." WHERE operation='STC' AND relatedid='$_G[tid]'");
	$payment['payers'] = intval($payment['payers']);
	$payment['netincome'] = intval($payment['netincome']);

	include template('forum/topicadmin_action');

} else {

	$modaction = 'RFD';
	$modpostsnum ++;

	$reason = checkreasonpm();

	$totalamount = 0;
	$amountarray = array();

	$logarray = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_log')." WHERE operation='BTC' AND relatedid='$_G[tid]'");
	while($log = DB::fetch($query)) {
		$totalamount += $log['amount'];
		$amountarray[$log['amount']][] = $log['uid'];
	}

	updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][1] => -$totalamount));
	DB::query("UPDATE ".DB::table('forum_thread')." SET price='-1', moderated='1' WHERE tid='$_G[tid]'");

	foreach($amountarray as $amount => $uidarray) {
		updatemembercount($uidarray, array($_G['setting']['creditstransextra'][1] => $amount));
	}

	DB::delete('common_credit_log',  "relatedid='$_G[tid]' AND operation IN('BTC', 'STC')");

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_moderate') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)),
	'modtids'	=> $thread['tid'],
	'modlog'	=> $thread
	);

}

?>