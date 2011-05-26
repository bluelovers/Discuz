<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_buyinvitecode.php 11620 2010-11-16 16:46:59Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(submitcheck('buysubmit')) {
	if($_G['setting']['ec_tenpay_bargainor'] || $_G['setting']['ec_tenpay_opentrans_chnid'] || $_G['setting']['ec_account']) {
		$language = lang('forum/misc');
		$amount = intval($_G['gp_amount']);
		$email = dhtmlspecialchars($_G['gp_email']);
		if(empty($amount)) {
			showmessage('buyinvitecode_no_count');
		}
		if(strlen($email) < 6 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) {
			showmessage('buyinvitecode_email_error');
		}

		$price = round($amount * $_G['setting']['inviteconfig']['invitecodeprice'], 2);
		$orderid = '';

		$apitype = $_G['gp_apitype'];
		if(empty($apitype)) {
			showmessage('parameters_error');
		}
		require_once libfile('function/trade');
		$requesturl = invite_payurl($amount, $price, $orderid);

		$query = DB::query("SELECT orderid FROM ".DB::table('forum_order')." WHERE orderid='$orderid'");
		if(DB::num_rows($query)) {
			showmessage('credits_addfunds_order_invalid');
		}
		DB::query("INSERT INTO ".DB::table('forum_order')." (orderid, status, uid, amount, price, submitdate, email, ip)
			VALUES ('$orderid', '1', '0', '$amount', '$price', '$_G[timestamp]', '$email', '$_G[clientip]')");
		include template('common/header_ajax');
		echo '<form id="payform" action="'.$requesturl.'" method="post"></form><script type="text/javascript" reload="1">$(\'payform\').submit();</script>';
		include template('common/footer_ajax');
		dexit();
	} else {
		showmessage('action_closed', NULL);
	}

}
if($_G['gp_action'] == 'paysucceed' && $_G['gp_orderid']) {
	$orderid = $_G['gp_orderid'];
	$order = DB::fetch_first("SELECT * FROM ".DB::table('forum_order')."  WHERE orderid='$orderid'");
	if(!$order) {
		showmessage('parameters_error');
	}
	$codes = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE orderid='$orderid'");
	while($code = DB::fetch($query)) {
		$codes[] = $code['code'];
	}
	if(empty($codes)) {
		showmessage('buyinvitecode_no_id');
	}
	$codetext = implode("\r\n", $codes);
}

if($_G['group']['maxinviteday']) {
	$maxinviteday = time() + 86400 * $_G['group']['maxinviteday'];
} else {
	$maxinviteday = time() + 86400 * 10;
}
$maxinviteday = dgmdate($maxinviteday, 'Y-m-d H:i');
$_G['setting']['inviteconfig']['invitecodeprompt'] = stripslashes(nl2br($_G['setting']['inviteconfig']['invitecodeprompt']));

include template('common/buyinvitecode');
?>