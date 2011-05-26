<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit_base.php 22426 2011-05-06 10:47:43Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(empty($_G['gp_op']))	$_G['gp_op'] = 'base';
if(in_array($_G['gp_op'], array('transfer', 'exchange'))) {
	$taxpercent = sprintf('%1.2f', $_G['setting']['creditstax'] * 100).'%';
}
if($_G['gp_op'] == 'base') {
	$loglist = $extcredits_exchange = array();
	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $key => $value) {
			if($value['allowexchangein'] || $value['allowexchangeout']) {
				$extcredits_exchange['extcredits'.$key] = array('title' => $value['title'], 'unit' => $value['unit']);
			}
		}
	}

	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]'");
	if($count) {
		loadcache(array('magics'));
		$query = DB::query("SELECT * FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' ORDER BY dateline DESC LIMIT 0,10");
		while($log = DB::fetch($query)) {
			$credits = array();
			$havecredit = false;
			$maxid = $minid = 0;
			foreach($_G['setting']['extcredits'] as $id => $credit) {
				if($log['extcredits'.$id]) {
					$havecredit = true;
					$credits[] = $credit['title'].' <span class="'.($log['extcredits'.$id] > 0 ? 'xi1' : 'xg1').'">'.($log['extcredits'.$id] > 0 ? '+' : '').$log['extcredits'.$id].'</span>';
					if($log['operation'] == 'CEC' && !empty($log['extcredits'.$id])) {
						if($log['extcredits'.$id] > 0) {
							$log['maxid'] = $id;
						} elseif($log['extcredits'.$id] < 0) {
							$log['minid'] = $id;
						}
					}

				}
			}
			if(!$havecredit) {
				continue;
			}
			$log['credit'] = implode('<br/>', $credits);
			if(in_array($log['operation'], array('RTC', 'RAC', 'STC', 'BTC', 'ACC', 'RCT', 'RCA', 'RCB'))) {
				$tids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('SAC', 'BAC'))) {
				$aids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('PRC', 'RSC'))) {
				$pids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('TFR', 'RCV'))) {
				$uids[$log['relatedid']] = $log['relatedid'];
			} elseif($log['operation'] == 'TRC') {
				$taskids[$log['relatedid']] = $log['relatedid'];
			}

			$loglist[] = $log;
		}
		$otherinfo = getotherinfo($aids, $pids, $tids, $taskids, $uids);

	}

	$navtitle = lang('core', 'title_credit');
	$creditsformulaexp = str_replace('*', 'X', $_G['setting']['creditsformulaexp']);

} elseif ($_G['gp_op'] == 'buy') {

	if((!$_G['setting']['ec_ratio'] || (!$_G['setting']['ec_tenpay_opentrans_chnid'] && !$_G['setting']['ec_tenpay_bargainor']  && !$_G['setting']['ec_account'])) && !$_G['setting']['card']['open'] ) {
		showmessage('action_closed', NULL);
	}

	if(submitcheck('addfundssubmit')) {
		$apitype = $_G['gp_apitype'];
		if($apitype == 'card') {
			if($_G['setting']['seccodestatus'] & 16) {
				if(!check_seccode($_G['gp_seccodeverify'], $_G['gp_sechash'])) {
					showmessage('submit_seccode_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
				}
			}

			if(!$_POST['cardid']) {
				showmessage('memcp_credits_card_msg_cardid_incorrect', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
			}
			if(!$card = DB::fetch_first("SELECT * FROM ".DB::table('common_card')." WHERE id = '{$_POST['cardid']}'")) {
				showmessage('memcp_credits_card_msg_card_unfined', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true, 'extrajs' => '<script type="text/javascript">updateseccode("'.$_G['gp_sechash'].'");</script>'));
			} else {
				if($card['status'] == 2) {
					showmessage('memcp_credits_card_msg_used', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
				}
				if($card['cleardateline'] < TIMESTAMP) {
					showmessage('memcp_credits_card_msg_cleardateline_early', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
				}
				DB::query("UPDATE ".DB::table('common_card')." SET status = 2, uid = '{$_G['uid']}', useddateline = '{$_G['timestamp']}' WHERE id = '{$card['id']}'");
				updatemembercount($_G[uid], array($card['extcreditskey'] => $card['extcreditsval']), true, 'CDC', 1);
				showmessage('memcp_credits_card_msg_succeed', 'home.php?mod=spacecp&ac=credit&op=base', array('extcreditstitle' => $_G['setting']['extcredits'][$card['extcreditskey']]['title'], 'extcreditsval' => $card['extcreditsval']), array('showdialog' => 1, 'alert' => 'right', 'showmsg' => true, 'locationtime' => true));
			}
		} else {
			$amount = intval($_G['gp_addfundamount']);
			if(!$amount) {
				showmessage('memcp_credits_addfunds_msg_incorrect', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
			}
			$language = lang('forum/misc');
			if(($_G['setting']['ec_mincredits'] && $amount < $_G['setting']['ec_mincredits']) || ($_G['setting']['ec_maxcredits'] && $amount > $_G['setting']['ec_maxcredits'])) {
				showmessage('credits_addfunds_amount_invalid', '', array('ec_maxcredits' => $_G['setting']['ec_maxcredits'], 'ec_mincredits' => $_G['setting']['ec_mincredits']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
			}

			if($apitype == 'card' && DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_order')." WHERE uid='$_G[uid]' AND submitdate>='$_G[timestamp]'-180 LIMIT 1")) {
				showmessage('credits_addfunds_ctrl', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
			}

			if($_G['setting']['ec_maxcreditspermonth']) {
				$query = DB::query("SELECT SUM(amount) FROM ".DB::table('forum_order')." WHERE uid='$_G[uid]' AND submitdate>='$_G[timestamp]'-2592000 AND status IN (2, 3)");
				if((DB::result($query, 0)) + $amount > $_G['setting']['ec_maxcreditspermonth']) {
					showmessage('credits_addfunds_toomuch', '', array('ec_maxcreditspermonth' => $_G['setting']['ec_maxcreditspermonth']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
				}
			}

			$price = round(($amount / $_G['setting']['ec_ratio'] * 100) / 100, 2);
			$orderid = '';

			require_once libfile('function/trade');
			$requesturl = credit_payurl($price, $orderid);

			$query = DB::query("SELECT orderid FROM ".DB::table('forum_order')." WHERE orderid='$orderid'");
			if(DB::num_rows($query)) {
				showmessage('credits_addfunds_order_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
			}

			DB::query("INSERT INTO ".DB::table('forum_order')." (orderid, status, uid, amount, price, submitdate)
				VALUES ('$orderid', '1', '$_G[uid]', '$amount', '$price', '$_G[timestamp]')");

			include template('common/header_ajax');
			echo '<form id="payform" action="'.$requesturl.'" method="post"></form><script type="text/javascript" reload="1">$(\'payform\').submit();</script>';
			include template('common/footer_ajax');
			dexit();
		}
	} else {
		if($_G['setting']['card']['open'] && $_G['setting']['seccodestatus'] & 16) {
			$seccodecheck = 1;
			$secqaacheck = 0;
		}
	}

} elseif ($_G['gp_op'] == 'transfer') {

	if(!($_G['setting']['transferstatus'] && $_G['group']['allowtransfer'])) {
		showmessage('action_closed', NULL);
	}

	if(submitcheck('transfersubmit')) {
		if($_G['gp_to'] == $_G['username']) {
			showmessage('memcp_credits_transfer_msg_self_incorrect', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}
		$amount = intval($_G['gp_transferamount']);
		if($amount <= 0) {
			showmessage('credits_transaction_amount_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		} elseif(getuserprofile('extcredits'.$_G['setting']['creditstransextra'][9]) - $amount < ($minbalance = $_G['setting']['transfermincredits'])) {
			showmessage('credits_transfer_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][9]]['title'], 'minbalance' => $minbalance), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		} elseif(!($netamount = floor($amount * (1 - $_G['setting']['creditstax'])))) {
			showmessage('credits_net_amount_iszero', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}
		$to = DB::fetch_first("SELECT username,uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_to]'");
		if(!$to) {
			showmessage('memcp_credits_transfer_msg_user_incorrect', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}

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
		showmessage('credits_transfer_succeed', 'home.php?mod=spacecp&ac=credit&op=transfer', array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true));
	}

} elseif ($_G['gp_op'] == 'exchange') {

	if(!$_G['setting']['exchangestatus']) {
		showmessage('action_closed', NULL);
	}
	$_CACHE['creditsettings'] = array();
	if(file_exists(DISCUZ_ROOT.'/uc_client/data/cache/creditsettings.php')) {
		include_once(DISCUZ_ROOT.'/uc_client/data/cache/creditsettings.php');
	}

	if(submitcheck('exchangesubmit')) {

		$tocredits = $_G['gp_tocredits'];
		$fromcredits = $_G['gp_fromcredits'];
		$exchangeamount = $_G['gp_exchangeamount'];
		$outexange = strexists($tocredits, '|');
		if($outexange && !empty($_G['gp_outi'])) {
			$fromcredits = $_G['gp_fromcredits_'.$_G['gp_outi']];
		}

		if($fromcredits == $tocredits) {
			showmessage('memcp_credits_exchange_msg_num_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
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
			showmessage('credits_exchange_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}
		if(!$outexange && !$_G['setting']['extcredits'][$fromcredits]['allowexchangeout']) {
			showmessage('extcredits_disallowexchangeout', '', array('credittitle' => $_G['setting']['extcredits'][$fromcredits]['title']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}
		if(!$outexange && !$_G['setting']['extcredits'][$tocredits]['allowexchangein']) {
			showmessage('extcredits_disallowexchangein', '', array('credittitle' => $_G['setting']['extcredits'][$tocredits]['title']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}
		if(!$netamount) {
			showmessage('memcp_credits_exchange_msg_balance_insufficient', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		} elseif($exchangeamount <= 0) {
			showmessage('credits_transaction_amount_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		} elseif(getuserprofile('extcredits'.$fromcredits) - $netamount < ($minbalance = $_G['setting']['exchangemincredits'])) {
			showmessage('credits_exchange_balance_insufficient', '', array('title' => $_G['setting']['extcredits'][$fromcredits]['title'], 'minbalance' => $minbalance), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		}

		loaducenter();
		$ucresult = uc_user_login($_G['username'], $_G['gp_password']);
		list($tmp['uid']) = daddslashes($ucresult);

		if($tmp['uid'] <= 0) {
			showmessage('credits_password_invalid', '', array(), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
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

		showmessage('credits_transaction_succeed', 'home.php?mod=spacecp&ac=credit&op=exchange', array(), array('showdialog' => 1, 'showmsg' => true, 'locationtime' => true));
	}

} else  {
	$wheresql = '';
	$list = array();
	if($_G['gp_rid']) {
		$rid = intval($_G['gp_rid']);
		$wheresql = " AND rid='$rid'";
	}
	require_once libfile('function/forumlist');
	$select = forumselect(false, 0, $_G['gp_fid']);
	$keys = array_keys($_G['setting']['extcredits']);
	if(!$_G['setting']['homestatus']) {
		foreach (array('doing', 'publishblog', 'guestbook', 'getguestbook', 'poke', 'visit') AS $val) {
			$wheresql .= " AND action <> '{$val}'";
		}
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_credit_rule')." WHERE 1 $wheresql ORDER BY rid DESC");
	while($value = DB::fetch($query)) {
		if(empty($_G['gp_fid']) || in_array($value['action'], array('digest', 'post', 'reply', 'getattach', 'postattach'))) {
			if(checkvalue($value, $keys)) {
				$list[$value['action']] = $value;
			}
		}
	}
	if(!empty($_G['gp_fid'])) {
		$_G['gp_fid'] = intval($_G['gp_fid']);
		$flist = unserialize(DB::result_first("SELECT creditspolicy FROM ".DB::table('forum_forumfield')." WHERE fid='$_G[gp_fid]'"));
		foreach($flist as $action => $value) {
			$list[$value['action']] = $value;
		}
	}
}
include_once template("home/spacecp_credit_base");

function checkvalue($value, $creditids) {
	$havevalue = false;
	foreach($creditids as $key) {
		if($value['extcredits'.$key]) {
			$havevalue = true;
			break;
		}
	}
	return $havevalue;
}
?>