<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit_base.php 16400 2010-09-06 06:12:47Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(empty($_G['gp_op']))	$_G['gp_op'] = 'base';
if($_G['gp_op'] == 'base') {
	$extcredits_exchange = array();
	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $key => $value) {
			if($value['allowexchangein'] || $value['allowexchangeout']) {
				$extcredits_exchange['extcredits'.$key] = array('title' => $value['title'], 'unit' => $value['unit']);
			}
		}
	}

	$taxpercent = sprintf('%1.2f', $_G['setting']['creditstax'] * 100).'%';
	$_CACHE['creditsettings'] = array();
	if(file_exists(DISCUZ_ROOT.'/uc_client/data/cache/creditsettings.php')) {
		include_once(DISCUZ_ROOT.'/uc_client/data/cache/creditsettings.php');
	}

	if(submitcheck('transfersubmit')) {

		if($_G['setting']['transferstatus'] && $_G['group']['allowtransfer']) {

			if($_G['gp_to'] == $_G['username']) {
				showmessage('memcp_credits_transfer_msg_self_incorrect', 'home.php?mod=spacecp&ac=credit&op=base', array(), array('showdialog' => 1, 'showmsg' => true));
			}
			$amount = intval($_G['gp_transferamount']);
			$msgparam = !submitcheck('confirm') ? array('msgtype' => 2) : array();
			if($amount <= 0) {
				showmessage('credits_transaction_amount_invalid', '', array(), $msgparam);
			} elseif(getuserprofile('extcredits'.$_G['setting']['creditstransextra'][9]) - $amount < ($minbalance = $_G['setting']['transfermincredits'])) {
				showmessage('credits_transfer_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][9]]['title'], 'minbalance' => $minbalance), $msgparam);
			} elseif(!($netamount = floor($amount * (1 - $_G['setting']['creditstax'])))) {
				showmessage('credits_net_amount_iszero', '', array(), $msgparam);
			}
			$to = DB::fetch_first("SELECT username,uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_to]'");
			if(!$to) {
				showmessage('memcp_credits_transfer_msg_user_incorrect', '', array(), $msgparam);
			}

			if(!submitcheck('confirm')) {

				include_once template("home/spacecp_credit_action");

			} else {

				loaducenter();
				$ucresult = uc_user_login($_G['username'], $_G['gp_password']);
				list($tmp['uid']) = daddslashes($ucresult);

				if($tmp['uid'] <= 0) {
					showmessage('credits_password_invalid');
				}

				updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][9] => -$amount), 1, 'TFR', $to['uid']);
				updatemembercount($to['uid'], array($_G['setting']['creditstransextra'][9] => $netamount), 1, 'RCV', $_G['uid']);

				if(!empty($_G['gp_transfermessage'])) {
					$transfermessage = dstripslashes($_G['gp_transfermessage']);
					notification_add($to['uid'], 'credit', 'transfer', array('credit' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][9]]['title'].' '.$netamount.' '.$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][9]]['unit'], 'transfermessage' => $transfermessage));
				}

				showmessage('credits_transaction_succeed', 'home.php?mod=spacecp&ac=credit&op=base', array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true));
			}
		} else {
			showmessage('action_closed', NULL);
		}

	} elseif(submitcheck('exchangesubmit')) {

		$tocredits = $_G['gp_tocredits'];
		$fromcredits = $_G['gp_fromcredits'];
		$exchangeamount = $_G['gp_exchangeamount'];
		$outexange = strexists($tocredits, '|');
		if($outexange && !empty($_G['gp_outi'])) {
			$fromcredits = $_G['gp_fromcredits_'.$_G['gp_outi']];
		}
		$msgparam = !submitcheck('confirm') ? array('msgtype' => 2) : array();
		if($fromcredits == $tocredits) {
			showmessage('memcp_credits_exchange_msg_num_invalid', '', array(), $msgparam);
		}
		if($outexange) {
			$netamount = floor($exchangeamount * $_CACHE['creditsettings'][$tocredits]['ratiosrc'][$fromcredits] / $_CACHE['creditsettings'][$tocredits]['ratiodesc'][$fromcredits]);
		} else {
			if($_G['setting']['extcredits'][$tocredits]['ratio'] < $_G['setting']['extcredits'][$fromcredits]['ratio']) {
				$netamount = ceil($exchangeamount * $_G['setting']['extcredits'][$tocredits]['ratio'] / $_G['setting']['extcredits'][$fromcredits]['ratio'] * (1 + $_G['setting']['creditstax']));
			} else {
				$netamount = floor($exchangeamount * $_G['setting']['extcredits'][$tocredits]['ratio'] / $_G['setting']['extcredits'][$fromcredits]['ratio'] * (1 + $_G['setting']['creditstax']));
			}
		}
		if(!$outexange && !$_G['setting']['extcredits'][$tocredits]['ratio']) {
			showmessage('credits_exchange_invalid', '', array(), $msgparam);
		}
		if(!$outexange && !$_G['setting']['extcredits'][$fromcredits]['allowexchangeout']) {
			showmessage('extcredits_disallowexchangeout', '', array('credittitle' => $_G['setting']['extcredits'][$fromcredits]['title']), $msgparam);
		}
		if(!$outexange && !$_G['setting']['extcredits'][$tocredits]['allowexchangein']) {
			showmessage('extcredits_disallowexchangein', '', array('credittitle' => $_G['setting']['extcredits'][$tocredits]['title']), $msgparam);
		}
		if(!$netamount) {
			showmessage('memcp_credits_exchange_msg_balance_insufficient', '', array(), $msgparam);
		} elseif($exchangeamount <= 0) {
			showmessage('credits_transaction_amount_invalid', '', array(), $msgparam);
		} elseif(getuserprofile('extcredits'.$fromcredits) - $netamount < ($minbalance = $_G['setting']['exchangemincredits'])) {
			showmessage('credits_exchange_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$fromcredits]['title'], 'minbalance' => $minbalance), $msgparam);
		}

		if(($_G['setting']['exchangestatus'] || $_CACHE['creditsettings']) && $_CACHE['creditsettings'][$tocredits] || $_G['setting']['extcredits'][$fromcredits]['ratio'] && $_G['setting']['extcredits'][$tocredits]['ratio']) {
			if(!submitcheck('confirm')) {

				if($fromcredits == $tocredits) {
					showmessage('memcp_credits_exchange_msg_num_invalid', 'home.php?mod=spacecp&ac=credit&op=base', array(), array('showdialog' => 1, 'showmsg' => true));
				}

				if(!$netamount) {
					showmessage('memcp_credits_exchange_msg_balance_insufficient', 'home.php?mod=spacecp&ac=credit&op=base', array(), array('showdialog' => 1, 'showmsg' => true));
				}
				include_once template("home/spacecp_credit_action");

			} else {

				loaducenter();
				$ucresult = uc_user_login($_G['username'], $_G['gp_password']);
				list($tmp['uid']) = daddslashes($ucresult);

				if($tmp['uid'] <= 0) {
					showmessage('credits_password_invalid');
				}

				if(!$outexange) {
					updatemembercount($_G['uid'], array($fromcredits => -$netamount, $tocredits => $exchangeamount), 1, 'CEC', $_G['uid']);
				} else {
					if(!array_key_exists($fromcredits, $_CACHE['creditsettings'][$tocredits]['creditsrc'])) {
						showmessage('extcredits_dataerror', NULL);
					}
					list($toappid, $tocredits) = explode('|', $tocredits);
					$ucresult = uc_credit_exchange_request($_G['uid'], $fromcredits, $tocredits, $toappid, $exchangeamount);
					if(!$ucresult) {
						showmessage('extcredits_dataerror', NULL);
					}
					updatemembercount($_G['uid'], array($fromcredits => -$netamount), 1, 'ECU', $_G['uid']);
					$netamount = $amount;
					$amount = $tocredits = 0;
				}

				showmessage('credits_transaction_succeed', 'home.php?mod=spacecp&ac=credit&op=base', array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true));
			}
		} else {
			showmessage('action_closed', NULL);
		}

	} elseif(submitcheck('addfundssubmit')) {

		if($_G['setting']['ec_ratio']) {
			if(!submitcheck('confirm')) {
				if(!$_G['gp_addfundamount']) {
					showmessage('memcp_credits_addfunds_msg_incorrect', 'home.php?mod=spacecp&ac=credit&op=base', array(), array('showdialog' => 1, 'showmsg' => true));
				}
				$price = round(($_G['gp_addfundamount'] / $_G['setting']['ec_ratio'] * 100) / 100, 2);
				include_once template("home/spacecp_credit_action");

			} else {
				$language = lang('forum/misc');
				$amount = intval($_G['gp_amount']);
				if(!$amount || ($_G['setting']['ec_mincredits'] && $amount < $_G['setting']['ec_mincredits']) || ($_G['setting']['ec_maxcredits'] && $amount > $_G['setting']['ec_maxcredits'])) {
					showmessage('credits_addfunds_amount_invalid', '', array('ec_maxcredits' => $_G['setting']['ec_maxcredits'], 'ec_mincredits' => $_G['setting']['ec_mincredits']));
				}

				if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_order')." WHERE uid='$_G[uid]' AND submitdate>='$_G[timestamp]'-180 LIMIT 1")) {
					showmessage('credits_addfunds_ctrl');
				}

				if($_G['setting']['ec_maxcreditspermonth']) {
					$query = DB::query("SELECT SUM(amount) FROM ".DB::table('forum_order')." WHERE uid='$_G[uid]' AND submitdate>='$_G[timestamp]'-2592000 AND status IN (2, 3)");
					if((DB::result($query, 0)) + $amount > $_G['setting']['ec_maxcreditspermonth']) {
						showmessage('credits_addfunds_toomuch', '', array('ec_maxcreditspermonth' => $_G['setting']['ec_maxcreditspermonth']));
					}
				}

				$price = round(($amount / $_G['setting']['ec_ratio'] * 100) / 100, 2);
				$orderid = '';

				$apitype = $_G['gp_apitype'];
				require_once libfile('function/trade');
				$requesturl = credit_payurl($price, $orderid);

				$query = DB::query("SELECT orderid FROM ".DB::table('forum_order')." WHERE orderid='$orderid'");
				if(DB::num_rows($query)) {
					showmessage('credits_addfunds_order_invalid');
				}

				DB::query("INSERT INTO ".DB::table('forum_order')." (orderid, status, uid, amount, price, submitdate)
					VALUES ('$orderid', '1', '$_G[uid]', '$amount', '$price', '$_G[timestamp]')");

				showmessage('credits_addfunds_succeed', $requesturl, array(), array('showdialog' => 1, 'locationtime' => true));

			}
		} else {
			showmessage('action_closed', NULL);
		}

	}

	$extcredits_exchange = array();

	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $key => $value) {
			if($value['allowexchangein'] || $value['allowexchangeout']) {
				$extcredits_exchange['extcredits'.$key] = array('title' => $value['title'], 'unit' => $value['unit']);
			}
		}
	}
	$navtitle = lang('core', 'title_credit');
} else  {
	$wheresql = '';
	$list = array();
	if($_G['gp_rid']) {
		$rid = intval($_G['gp_rid']);
		$wheresql = " WHERE rid='$rid'";
	}
	require_once libfile('function/forumlist');
	$select = forumselect(false, 0, $_G['gp_fid']);
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." $wheresql ORDER BY rid DESC");
	while($value = DB::fetch($query)) {
		if(empty($_G['gp_fid']) || in_array($value['action'], array('digest', 'post', 'reply', 'getattach', 'postattach'))) {
			$list[$value['action']] = $value;
		}
	}
	if(!empty($_G['gp_fid'])) {
		$_G['gp_fid'] = intval($_G['gp_fid']);
		$flist = unserialize(DB::result_first("SELECT creditspolicy FROM ".DB::table('forum_forumfield')." WHERE fid='$_G[gp_fid]'"));

		// bluelovers
		if (is_array($flist)){
		// bluelovers

			foreach($flist as $action => $value) {
				$list[$value['action']] = $value;
			}

		// bluelovers
		}
		// bluelovers

//		echo '<pre>';
//		print_r($list);
//		exit();
	}
}
include_once template("home/spacecp_credit_base");

?>