<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_trade.php 27353 2012-01-17 08:03:54Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);
$apitype = $_G['gp_apitype'];

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$page = max(1, intval($_G['gp_page']));
$orderid = $_G['gp_orderid'];
if(!empty($orderid) && empty($_G['gp_apitype'])) {
	$paytype = DB::result_first("SELECT paytype FROM ".DB::table('forum_tradelog')." WHERE orderid='$orderid'");
	if($paytype == 1) {
		$apitype = 'alipay';
	}
	if($paytype == 2) {
		$apitype = 'tenpay';
	}
}

require_once libfile('function/trade');
if(!empty($orderid)) {

	$language = lang('forum/misc');

	$tradelog = daddslashes(DB::fetch_first("SELECT * FROM ".DB::table('forum_tradelog')." WHERE orderid='$orderid'"), 1);
	if(!$_G['forum_auditstatuson'] && (empty($tradelog) || $_G['uid'] != $tradelog['sellerid'] && $_G['uid'] != $tradelog['buyerid'])) {
		showmessage('undefined_action', NULL);
	}

	$limit = 6;
	$query = DB::query("SELECT t.tid, t.pid, t.aid, t.subject, t.price, t.credit, t.displayorder FROM ".DB::table('forum_trade')." t
		LEFT JOIN ".DB::table(getattachtablebytid($tradelog['tid']))." a ON t.aid=a.aid
		WHERE t.sellerid='$tradelog[sellerid]' ORDER BY t.displayorder DESC LIMIT $limit");
	$usertrades = array();
	$usertradecount = 0;
	while($usertrade = DB::fetch($query)) {
		$usertradecount++;
		$usertrades[] = $usertrade;
	}

	$trade_message = '';
	$currentcredit = $_G['setting']['creditstrans'] ? getuserprofile('extcredits'.$_G['setting']['creditstrans']) : 0;
	$discountprice = $tradelog['baseprice'] * $tradelog['number'];

	if(!empty($_G['gp_pay']) && !$tradelog['offline'] && $tradelog['status'] == 0 && $tradelog['buyerid'] == $_G['uid']) {
		if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['credit']) {
			if($tradelog['credit'] > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][5])) {
				showmessage('trade_credit_lack');
			}
			updatemembercount($tradelog['buyerid'], array($_G['setting']['creditstransextra'][5] => -$tradelog['credit']));
		}
		$trade = DB::fetch_first("SELECT * FROM ".DB::table('forum_trade')." WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'");

		if($_G['uid'] && $currentcredit < $discountcredit && $tradelog['discount']) {
			showmessage('trade_credits_no_enough', '', array('credittitle' => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title']));
		}
		$pay = array();
		$pay['commision'] = 0;
		$transport = $tradelog['transport'];
		$transportfee = 0;
		trade_setprice(array('fee' => $fee, 'trade' => $trade, 'transport' => $transport), $price, $pay, $transportfee);
		$payurl = trade_payurl($pay, $trade, $tradelog);
		$paytype = 0;
		if($apitype == 'alipay') {
			$paytype = 1;
		} elseif($apitype == 'tenpay') {
			$paytype = 2;
		}
		DB::update('forum_tradelog', array('paytype' => $paytype), "orderid='$orderid'");
		showmessage('trade_directtopay', $payurl);
	}

	if(submitcheck('offlinesubmit') && in_array($_G['gp_offlinestatus'], trade_offline($tradelog, 0))) {

		loaducenter();
		$ucresult = uc_user_login($_G['username'], $_G['gp_password']);
		list($tmp['uid']) = daddslashes($ucresult);

		if($tmp['uid'] <= 0) {
			showmessage('trade_password_error', 'forum.php?mod=trade&orderid='.$orderid);
		}
		if($_G['gp_offlinestatus'] == 4) {
			if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['credit']) {
				if($tradelog['credit'] > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][5])) {
					showmessage('trade_credit_lack');
				}
				updatemembercount($tradelog['buyerid'], array($_G['setting']['creditstransextra'][5] => -$tradelog['credit']));
			}
			$trade = DB::fetch_first("SELECT amount FROM ".DB::table('forum_trade')." WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'");
			notification_add($tradelog['sellerid'], 'goods', 'trade_seller_send', array(
				'buyerid' => $tradelog['buyerid'],
				'buyer' => $tradelog['buyer'],
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
		} elseif($_G['gp_offlinestatus'] == 5) {
			notification_add($tradelog['buyerid'], 'goods', 'trade_buyer_confirm', array(
				'sellerid' => $tradelog['sellerid'],
				'seller' => $tradelog['seller'],
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
		} elseif($_G['gp_offlinestatus'] == 7) {
			if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['basecredit']) {
				$netcredit = round($tradelog['number'] * $tradelog['basecredit'] * (1 - $_G['setting']['creditstax']));
				updatemembercount($tradelog['sellerid'], array($_G['setting']['creditstransextra'][5] => $netcredit));
			} else {
				$netcredit = 0;
			}
			DB::query("UPDATE ".DB::table('forum_trade')." SET lastbuyer='$tradelog[buyer]', lastupdate='$_G[timestamp]', totalitems=totalitems+'$tradelog[number]', tradesum=tradesum+'$tradelog[price]', credittradesum=credittradesum+'$netcredit' WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'", 'UNBUFFERED');
			notification_add($tradelog['sellerid'], 'goods', 'trade_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
			notification_add($tradelog['buyerid'], 'goods', 'trade_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
		} elseif($_G['gp_offlinestatus'] == 17) {
			DB::query("UPDATE ".DB::table('forum_trade')." SET amount=amount+'$tradelog[number]' WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'", 'UNBUFFERED');
			notification_add($tradelog['sellerid'], 'goods', 'trade_fefund_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
			notification_add($tradelog['buyerid'], 'goods', 'trade_fefund_success', array(
				'orderid' => $orderid,
				'subject' => $tradelog['subject']
			));
			if($_G['setting']['creditstransextra'][5] != -1 && $tradelog['basecredit']) {
				updatemembercount($tradelog['buyerid'], array($_G['setting']['creditstransextra'][5] => $tradelog['number'] * $tradelog['basecredit']));
			}
		}

		$_G['gp_message'] = trim($_G['gp_message']);
		if($_G['gp_message']) {
			$_G['gp_message'] = daddslashes(dstripslashes($tradelog['message'])."\t\t\t".$_G['uid']."\t".$_G['member']['username']."\t".TIMESTAMP."\t".nl2br(strip_tags(substr($_G['gp_message'], 0, 200))), 1);
		} else {
			$_G['gp_message'] = daddslashes($tradelog['message'], 1);
		}

		DB::query("UPDATE ".DB::table('forum_tradelog')." SET status='$_G[gp_offlinestatus]', lastupdate='$_G[timestamp]', message='$_G[gp_message]' WHERE orderid='$orderid'");
		showmessage('trade_orderstatus_updated', 'forum.php?mod=trade&orderid='.$orderid);
	}

	if(submitcheck('tradesubmit')) {

		if($tradelog['status'] == 0) {

			$update = array();
			$oldbasecredit = $tradelog['basecredit'];
			$oldnumber = $tradelog['number'];
			if($tradelog['sellerid'] == $_G['uid']) {
				$tradelog['baseprice'] = floatval($_G['gp_newprice']);
				$tradelog['basecredit'] = intval($_G['gp_newcredit']);
				if(!$tradelog['baseprice'] < 0 || $tradelog['basecredit'] < 0) {
					showmessage('trade_pricecredit_error');
				}
				$tradelog['transportfee'] = intval($_G['gp_newfee']);
				$newnumber = $tradelog['number'];
				$update = array(
					"baseprice='$tradelog[baseprice]'",
					"basecredit='$tradelog[basecredit]'",
					"transportfee='$tradelog[transportfee]'"
				);
				notification_add($tradelog['buyerid'], 'goods', 'trade_order_update_sellerid', array(
					'seller' => $tradelog['seller'],
					'sellerid' => $tradelog['sellerid'],
					'orderid' => $orderid,
					'subject' => $tradelog['subject']
				));
			}
			if($tradelog['buyerid'] == $_G['uid']) {
				$newnumber = intval($_G['gp_newnumber']);
				if($newnumber <= 0) {
					showmessage('trade_input_no');
				}
				$trade = DB::fetch_first("SELECT amount FROM ".DB::table('forum_trade')." WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'");
				if($newnumber > $trade['amount'] + $tradelog['number']) {
					showmessage('trade_lack');
				}
				$amount = $trade['amount'] + $tradelog['number'] - $newnumber;
				DB::query("UPDATE ".DB::table('forum_trade')." SET amount='$amount' WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'", 'UNBUFFERED');
				$tradelog['number'] = $newnumber;

				$update = array(
					"number='$tradelog[number]'",
					"discount=0",
					"buyername='".dhtmlspecialchars($_G['gp_newbuyername'])."'",
					"buyercontact='".dhtmlspecialchars($_G['gp_newbuyercontact'])."'",
					"buyerzip='".dhtmlspecialchars($_G['gp_newbuyerzip'])."'",
					"buyerphone='".dhtmlspecialchars($_G['gp_newbuyerphone'])."'",
					"buyermobile='".dhtmlspecialchars($_G['gp_newbuyermobile'])."'",
					"buyermsg='".dhtmlspecialchars($_G['gp_newbuyermsg'])."'",
				);
				notification_add($tradelog['sellerid'], 'goods', 'trade_order_update_buyerid', array(
					'buyer' => $tradelog['buyer'],
					'buyerid' => $tradelog['buyerid'],
					'orderid' => $orderid,
					'subject' => $tradelog['subject']
				));
			}
			if($update) {
				if($tradelog['discount']) {
					$tradelog['baseprice'] = $tradelog['baseprice'] - $tax;
					$price = $tradelog['baseprice'] * $tradelog['number'];
				} else {
					$price = $tradelog['baseprice'] * $tradelog['number'];
				}
				if($_G['setting']['creditstransextra'][5] != -1 && ($oldnumber != $newnumber || $oldbasecredit != $tradelog['basecredit'])) {
					$tradelog['credit'] = $newnumber * $tradelog['basecredit'];
					$update[] = "credit='$tradelog[credit]'";
				}

				$update[] = "price='".($price + ($tradelog['transport'] == 2 ? $tradelog['transportfee'] : 0))."'";
				DB::query("UPDATE ".DB::table('forum_tradelog')." SET ".implode(',', $update)." WHERE orderid='$orderid'");
				$tradelog = DB::fetch_first("SELECT * FROM ".DB::table('forum_tradelog')." WHERE orderid='$orderid'");
			}
		}

	}

	$tradelog['lastupdate'] = dgmdate($tradelog['lastupdate'], 'u');
	$tradelog['statusview'] = trade_getstatus($tradelog['status']);

	$messagelist = array();
	if($tradelog['offline']) {
		$offlinenext = trade_offline($tradelog, 1, $trade_message);
		$message = explode("\t\t\t", dstripslashes($tradelog['message']));
		foreach($message as $row) {
			$row = explode("\t", $row);
			$row[2] = dgmdate($row[2], 'u');
			$row[0] && $messagelist[] = $row;
		}
	} else {
		$loginurl = trade_getorderurl($tradelog['tradeno']);
	}
	$tradelog['buyer'] = dstripslashes($tradelog['buyer']);
	$tradelog['seller'] = dstripslashes($tradelog['seller']);

	$trade = DB::fetch_first("SELECT * FROM ".DB::table('forum_trade')." WHERE tid='$tradelog[tid]' AND pid='$tradelog[pid]'");

	include template('forum/trade_view');

} else {

	if(empty($_G['gp_pid'])) {
		$posttable = getposttablebytid($_G['tid']);
		$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1' LIMIT 1");
	} else {
		$pid = $_G['gp_pid'];
	}

	if(DB::result_first("SELECT closed FROM ".DB::table('forum_thread')." WHERE tid='$_G[tid]'")) {
		showmessage('trade_closed', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}
	$trade = DB::fetch_first("SELECT * FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]' AND pid='$pid'");
	if(empty($trade)) {
		showmessage('trade_not_found');
	}
	$fromcode = false;

	if($trade['closed']) {
		showmessage('trade_closed', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}

	if($trade['price'] <= 0 && $trade['credit'] <= 0) {
		showmessage('trade_invalid', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}
	if($trade['credit'] > 0 && $_G['setting']['creditstransextra'][5] == -1) {
		showmessage('trade_credit_invalid', 'forum.php?mod=viewthread&tid='.$_G['tid'].'&page='.$page);
	}

	$limit = 6;
	$query = DB::query("SELECT t.tid, t.pid, t.aid, t.subject, t.price, t.credit, t.displayorder FROM ".DB::table('forum_trade')." t
		LEFT JOIN ".DB::table(getattachtablebytid($_G['tid']))." a ON t.aid=a.aid
		WHERE t.sellerid='$trade[sellerid]' ORDER BY t.displayorder DESC LIMIT $limit");
	$usertrades = array();
	$usertradecount = 0;
	while($usertrade = DB::fetch($query)) {
		$usertradecount++;
		$usertrades[] = $usertrade;
	}

	if($_G['gp_action'] != 'trade' && !submitcheck('tradesubmit')) {
		$lastbuyerinfo = dhtmlspecialchars(DB::fetch_first("SELECT buyername,buyercontact,buyerzip,buyerphone,buyermobile FROM ".DB::table('forum_tradelog')." WHERE buyerid='$_G[uid]' AND status!=0 AND buyername!='' ORDER BY lastupdate DESC LIMIT 1"));
		$extra = rawurlencode($extra);
		include template('forum/trade');
	} else {

		if($trade['sellerid'] == $_G['uid']) {
			showmessage('trade_by_myself');
		} elseif($_G['gp_number'] <= 0) {
			showmessage('trade_input_no');
		} elseif(!$fromcode && $_G['gp_number'] > $trade['amount']) {
			showmessage('trade_lack');
		}

		$pay['number'] = $_G['gp_number'];
		$pay['price'] = $trade['price'];
		$credit = 0;
		if($_G['setting']['creditstransextra'][5] != -1 && $trade['credit']) {
			$credit = $_G['gp_number'] * $trade['credit'];
		}

		$price = $pay['price'] * $pay['number'];
		$buyercredits = 0;
		$pay['commision'] = 0;

		$orderid = $pay['orderid'] = dgmdate(TIMESTAMP, 'YmdHis').random(18);
		$transportfee = 0;
		trade_setprice(array('fee' => $fee, 'trade' => $trade, 'transport' => $_G['gp_transport']), $price, $pay, $transportfee);

		$buyerid = $_G['uid'] ? $_G['uid'] : 0;
		$_G['username'] = $_G['username'] ? $_G['username'] : $guestuser;
		$trade = daddslashes($trade, 1);
		$buyermsg = dhtmlspecialchars($_G['gp_buyermsg']);
		$buyerzip = dhtmlspecialchars($_G['gp_buyerzip']);
		$buyerphone = dhtmlspecialchars($_G['gp_buyerphone']);
		$buyermobile = dhtmlspecialchars($_G['gp_buyermobile']);
		$buyername = dhtmlspecialchars($_G['gp_buyername']);
		$buyercontact = dhtmlspecialchars($_G['gp_buyercontact']);

		$offline = !empty($_G['gp_offline']) ? 1 : 0;
		DB::query("INSERT INTO ".DB::table('forum_tradelog')."
			(tid, pid, orderid, subject, price, quality, itemtype, number, tax, locus, sellerid, seller, selleraccount, tenpayaccount, buyerid, buyer, buyercontact, buyercredits, buyermsg, lastupdate, offline, buyerzip, buyerphone, buyermobile, buyername, transport, transportfee, baseprice, discount, credit, basecredit) VALUES
			('$trade[tid]', '$trade[pid]', '$orderid', '$trade[subject]', '$price', '$trade[quality]', '$trade[itemtype]', '$_G[gp_number]', '$tax',
			 '$trade[locus]', '$trade[sellerid]', '$trade[seller]', '$trade[account]', '$trade[tenpayaccount]', '$_G[uid]', '$_G[username]', '$buyercontact', 0, '$buyermsg', '$_G[timestamp]', '$offline', '$buyerzip', '$buyerphone', '$buyermobile', '$buyername', '$_G[gp_transport]', '$transportfee', '$trade[price]', 0, '$credit', '$trade[credit]')");

		DB::query("UPDATE ".DB::table('forum_trade')." SET amount=amount-'$_G[gp_number]' WHERE tid='$trade[tid]' AND pid='$trade[pid]'", 'UNBUFFERED');
		showmessage('trade_order_created', 'forum.php?mod=trade&orderid='.$orderid);
	}

}

?>