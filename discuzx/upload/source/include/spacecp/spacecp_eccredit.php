<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_eccredit.php 17427 2010-10-19 02:49:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/ec_credit');

if($_G['gp_op'] == 'list') {

	$from = !empty($_G['gp_from']) && in_array($_G['gp_from'], array('buyer', 'seller', 'myself')) ? $_G['gp_from'] : '';
	$uid = !empty($_G['gp_uid']) ? intval($_G['gp_uid']) : $_G['uid'];

	$sql = $from == 'myself' ? "tc.raterid='$uid'" : "tc.rateeid='$uid'";
	$sql .= $from == 'buyer' ? ' AND tc.type=0' : ($from == 'seller' ? ' AND tc.type=1' : '');

	$filter = !empty($_G['gp_filter']) ? $_G['gp_filter'] : '';
	switch($filter) {
		case 'thisweek':
			$dateline = intval($_G[timestamp] - 604800);
			$sql .= " AND tc.dateline>='$dateline'";
			break;
		case 'thismonth':
			$dateline = intval($_G[timestamp] - 2592000);
			$sql .= " AND tc.dateline>='$dateline'";
			break;
		case 'halfyear':
			$dateline = intval($_G[timestamp] - 15552000);
			$sql .= " AND tc.dateline>='$dateline'";
			break;
		case 'before':
			$dateline = intval($_G[timestamp] - 15552000);
			$sql .= " AND tc.dateline<'$dateline'";
			break;
		default:
			$filter = '';
	}

	$level = !empty($level) ? $level : '';
	switch($level) {
		case 'good':
			$sql .= ' AND tc.score=1';
			break;
		case 'soso':
			$sql .= ' AND tc.score=0';
			break;
		case 'bad':
			$sql .= ' AND tc.score=-1';
			break;
		default:
			$level = '';
	}

	$page = max(1, intval($_G['gp_page']));
	$start_limit = ($page - 1) * 10;
	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_tradecomment')." tc WHERE $sql");
	$multipage = multi($num, 10, $page, "home.php?mod=spacecp&ac=list&uid=$uid".($from ? "&from=$from" : NULL).($filter ? "&filter=$filter" : NULL).($level ? "&level=$level" : NULL));

	$comments = array();
	$query = DB::query("SELECT tc.*, tl.subject, tl.price, tl.credit FROM ".DB::table('forum_tradecomment')." tc LEFT JOIN ".DB::table('forum_tradelog')." tl USING(orderid) WHERE $sql ORDER BY tc.dateline DESC LIMIT $start_limit, 10");

	while($comment = DB::fetch($query)) {
		$comment['expiration'] = dgmdate($comment['dateline'] + 30 * 86400, 'u');
		$comment['dbdateline'] = $comment['dateline'];
		$comment['dateline'] = dgmdate($comment['dateline'], 'u');
		$comment['baseprice'] = sprintf('%0.2f', $comment['baseprice']);
		$comments[] = $comment;
	}

	include template('home/spacecp_ec_list');

} elseif($_G['gp_op'] == 'rate' && ($orderid = $_G['gp_orderid']) && isset($_G['gp_type'])) {

	require_once libfile('function/trade');

	$type = intval($_G['gp_type']);
	if(!$type) {
		$raterid = 'buyerid';
		$ratee = 'seller';
		$rateeid = 'sellerid';
	} else {
		$raterid = 'sellerid';
		$ratee = 'buyer';
		$rateeid = 'buyerid';
	}
	$order = DB::fetch_first("SELECT * FROM ".DB::table('forum_tradelog')." WHERE orderid='$orderid' AND $raterid='$_G[uid]'");
	if(!$order) {
		showmessage('eccredit_order_notfound');
	} elseif($order['ratestatus'] == 3 || ($type == 0 && $order['ratestatus'] == 1) || ($type == 1 && $order['ratestatus'] == 2)) {
		showmessage('eccredit_rate_repeat');
	} elseif(!trade_typestatus('successtrades', $order['status']) && !trade_typestatus('refundsuccess', $order['status'])) {
		showmessage('eccredit_nofound');
	}

	$uid = $_G['uid'] == $order['buyerid'] ? $order['sellerid'] : $order['buyerid'];

	if(!submitcheck('ratesubmit')) {

		include template('home/spacecp_ec_rate');

	} else {

		$score = intval($_G['gp_score']);
		$message = cutstr(dhtmlspecialchars($_G['gp_message']), 200);
		$level = $score == 1 ? 'good' : ($score == 0 ? 'soso' : 'bad');
		$pid = intval($order['pid']);
		$order = daddslashes($order, 1);

		DB::query("INSERT INTO ".DB::table('forum_tradecomment')." (pid, orderid, type, raterid, rater, ratee, rateeid, score, message, dateline) VALUES ('$pid', '$orderid', '$type', '$_G[uid]', '$_G[username]', '$order[$ratee]', '$order[$rateeid]', '$score', '$message', '$_G[timestamp]')");

		if(!$order['offline'] || $order['credit']) {
			$monthfirstday = mktime(0, 0, 0, date('m', TIMESTAMP), 1, date('Y', TIMESTAMP));
			if(DB::result_first("SELECT COUNT(score) FROM ".DB::table('forum_tradecomment')." WHERE raterid='$_G[uid]' AND type='$type' AND dateline>='$monthfirstday' AND rateeid='$order[$rateeid]'") < $_G['setting']['ec_credit']['maxcreditspermonth']) {
				updateusercredit($uid, $type ? 'sellercredit' : 'buyercredit', $level);
			}
		}

		if($type == 0) {
			$ratestatus = $order['ratestatus'] == 2 ? 3 : 1;
		} else {
			$ratestatus = $order['ratestatus'] == 1 ? 3 : 2;
		}

		DB::query("UPDATE ".DB::table('forum_tradelog')." SET ratestatus='$ratestatus' WHERE orderid='$order[orderid]'");

		if($ratestatus != 3) {
			notification_add($order[$rateeid], 'goods', 'eccredit', array(
				'orderid' => $orderid,
			), 1);
		}

		showmessage('eccredit_succeed', 'home.php?mod=space&uid='.$_G['uid'].'&do=trade&view=eccredit');

	}

} elseif($_G['gp_op'] == 'explain' && $_G['gp_id']) {

	$id = intval($_G['gp_id']);
	$ajaxmenuid = $_G['gp_ajaxmenuid'];
	if(!submitcheck('explainsubmit', 1)) {
		include template('home/spacecp_ec_explain');
	} else {
		$comment = DB::fetch_first("SELECT explanation, dateline FROM ".DB::table('forum_tradecomment')." WHERE id='$id' AND rateeid='$_G[uid]'");
		if(!$comment) {
			showmessage('eccredit_nofound');
		} elseif($comment['explanation']) {
			showmessage('eccredit_reexplanation_repeat');
		} elseif($comment['dateline'] < TIMESTAMP - 30 * 86400) {
			showmessage('eccredit_reexplanation_closed');
		}

		$explanation = cutstr(dhtmlspecialchars($_G['gp_explanation']), 200);

		DB::query("UPDATE ".DB::table('forum_tradecomment')." SET explanation='$explanation' WHERE id='$id'");

		$language = lang('forum/misc');
		showmessage($language['eccredit_explain'].'&#58; '.$explanation, '', array(), array('msgtype' => 3, 'showmsg' => 1));
	}

}
?>