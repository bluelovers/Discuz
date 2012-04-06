<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_credit.php 23735 2011-08-08 08:24:08Z zhengqingpeng $
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
$notifydata = trade_notifycheck('credit');

if($notifydata['validator']) {

	$orderid = $notifydata['order_no'];
	$postprice = $notifydata['price'];
	$order = DB::fetch_first("SELECT o.*, m.username FROM ".DB::table('forum_order')." o LEFT JOIN ".DB::table('common_member')." m USING (uid) WHERE o.orderid='$orderid'");
	if($order && floatval($postprice) == floatval($order['price']) && ($apitype == 'tenpay' || strtolower($_G['setting']['ec_account']) == strtolower($_REQUEST['seller_email']))) {

		if($order['status'] == 1) {
			DB::query("UPDATE ".DB::table('forum_order')." SET status='2', buyer='$notifydata[trade_no]\t$apitype', confirmdate='$_G[timestamp]' WHERE orderid='$orderid'");
			updatemembercount($order['uid'], array($_G['setting']['creditstrans'] => $order['amount']), 1, 'AFD', $order['uid']);
			updatecreditbyaction($action, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0);
			DB::query("DELETE FROM ".DB::table('forum_order')." WHERE submitdate<'$_G[timestamp]'-60*86400");

			$submitdate = dgmdate($order['submitdate']);
			$confirmdate = dgmdate(TIMESTAMP);

			notification_add($order['uid'], 'credit', 'addfunds', array(
				'orderid' => $order['orderid'],
				'price' => $order['price'],
				'value' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'].' '.$order['amount'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit']
			), 1);
		}

	}

}

if($notifydata['location']) {
	$url = rawurlencode('home.php?mod=spacecp&ac=credit');
	if($apitype == 'tenpay') {
		echo <<<EOS
<meta name="TENCENT_ONLINE_PAYMENT" content="China TENCENT">
<html>
<body>
<script language="javascript" type="text/javascript">
window.location.href='$_G[siteurl]forum.php?mod=misc&action=paysucceed';
</script>
</body>
</html>
EOS;
	} else {
		dheader('location: '.$_G['siteurl'].'forum.php?mod=misc&action=paysucceed');
	}
} else {
	exit($notifydata['notify']);
}

?>