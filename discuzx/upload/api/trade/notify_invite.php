<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_credit.php 10986 2010-05-19 05:41:21Z monkey $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');

require '../../source/class/class_core.php';
require '../../source/function/function_forum.php';

$discuz = & discuz_core::instance();
$discuz->init();

$apitype = empty($_G['gp_attach']) || !preg_match('/^[a-z0-9]+$/i', $_G['gp_attach']) ? 'alipay' : $_G['gp_attach'];
require_once DISCUZ_ROOT.'./api/trade/api_'.$apitype.'.php';
$PHP_SELF = $_SERVER['PHP_SELF'];
$_G['siteurl'] = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api\/trade)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/');
$notifydata = trade_notifycheck('invite');
if($notifydata['validator']) {
	$orderid = $notifydata['order_no'];
	$postprice = $notifydata['price'];
	$order = DB::fetch_first("SELECT * FROM ".DB::table('forum_order')."  WHERE orderid='$orderid'");
	if($order && floatval($postprice) == floatval($order['price']) && ($apitype == 'tenpay' || $_G['setting']['ec_account'] == $_REQUEST['seller_email'])) {

		if($order['status'] == 1) {
			DB::query("UPDATE ".DB::table('forum_order')." SET status='2', buyer='$notifydata[trade_no]\t$apitype', confirmdate='$_G[timestamp]' WHERE orderid='$orderid'");
			$codes = $codetext = array();
			$dateline = TIMESTAMP;
			for($i=0; $i<$order['amount']; $i++) {
				$code = strtolower(random(6));
				$codetext[] = $code;
				$codes[] = "('0', '$code', '$dateline', '".($_G['group']['maxinviteday']?($_G['timestamp']+$_G['group']['maxinviteday']*24*3600):$_G['timestamp']+86400*10)."', '$order[email]', '$_G[clientip]', '$orderid')";
			}
			if($codes) {
				DB::query("INSERT INTO ".DB::table('common_invite')." (uid, code, dateline, endtime, email, inviteip, orderid) VALUES ".implode(',', $codes));
			}
			DB::query("DELETE FROM ".DB::table('forum_order')." WHERE submitdate<'$_G[timestamp]'-60*86400");

			$submitdate = dgmdate($order['submitdate']);
			$confirmdate = dgmdate(TIMESTAMP);
			if(!function_exists('sendmail')) {
				include libfile('function/mail');
			}
			$add_member_subject = $_G['setting']['bbname'].' - '.lang('forum/misc', 'invite_payment');
			$add_member_message = lang('email', 'invite_payment_email_message', array(
				'orderid' => $order['orderid'],
				'codetext' => implode('<br />', $codetext),
				'siteurl' => $_G['siteurl'],
				'bbname' => $_G['setting']['bbname'],
			));
			sendmail($order['email'], $add_member_subject, $add_member_message);
		}

	}
}
if($notifydata['location']) {
	if($apitype == 'tenpay') {
		echo <<<EOS
<meta name="TENCENT_ONLINE_PAYMENT" content="China TENCENT">
<html>
<body>
<script language="javascript" type="text/javascript">
window.location.href='$_G[siteurl]misc.php?mod=buyinvitecode&action=paysucceed&orderid=$orderid';
</script>
</body>
</html>
EOS;
	} else {
		dheader('location: '.$_G['siteurl'].'misc.php?mod=buyinvitecode&action=paysucceed&orderid='.$orderid);
	}
} else {
	exit($notifydata['notify']);
}

?>