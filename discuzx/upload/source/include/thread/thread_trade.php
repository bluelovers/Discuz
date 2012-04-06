<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_trade.php 19369 2010-12-29 04:29:52Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['gp_do']) || $_G['gp_do'] == 'tradeinfo') {

	if($_G['gp_do'] == 'tradeinfo') {
		$_G['gp_pid'] = intval($_G['gp_pid']);
		$tradelistadd = "AND pid = '$_G[gp_pid]'";
	} else {
		$tradelistadd = '';
		!$tradenum && $allowpostreply = FALSE;
	}

	$query = DB::query("SELECT * FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]' $tradelistadd ORDER BY displayorder");
	$trades = $tradesstick = array();$tradelist = 0;
	if(empty($_G['gp_do'])) {
		$sellerid = 0;
		$listcount = DB::num_rows($query);
		$tradelist = $tradenum - $listcount;
	}

	$tradesaids = $tradespids = array();
	while($trade = DB::fetch($query)) {
		if($trade['expiration']) {
			$trade['expiration'] = ($trade['expiration'] - TIMESTAMP) / 86400;
			if($trade['expiration'] > 0) {
				$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
				$trade['expiration'] = floor($trade['expiration']);
			} else {
				$trade['expiration'] = -1;
			}
		}
		$tradesaids[] = $trade['aid'];
		$tradespids[] = $trade['pid'];
		if($trade['displayorder'] < 0) {
			$trades[$trade['pid']] = $trade;
		} else {
			$tradesstick[$trade['pid']] = $trade;
		}
	}
	$trades = $tradesstick + $trades;
	$tradespids = dimplode($tradespids);
	unset($trade);

	if($tradespids) {
		$query = DB::query("SELECT * FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE pid IN ($tradespids)");
		while($attach = DB::fetch($query)) {
			if($attach['isimage'] && is_array($tradesaids) && in_array($attach['aid'], $tradesaids)) {
				$trades[$attach['pid']]['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
				$trades[$attach['pid']]['thumb'] = $attach['thumb'] ? getimgthumbname($trades[$attach['pid']]['attachurl']) : $trades[$attach['pid']]['attachurl'];
				$trades[$attach['pid']]['width'] = $attach['thumb'] && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
			}
		}
	}

	if($_G['gp_do'] == 'tradeinfo') {
		$verifyadd = '';
		if($_G['setting']['verify']['enabled']) {
			$verifyadd = "LEFT JOIN ".DB::table('common_member_verify')." mv USING(uid)";
			$fieldsadd .= ', mv.verify1, mv.verify2, mv.verify3, mv.verify4, mv.verify5';
		}
		$trade = $trades[$_G['gp_pid']];
		unset($trades);
		$posttable = getposttablebytid($_G['tid']);
		$post = DB::fetch_first("SELECT p.*, m.uid, mp.realname, m.username, m.groupid, m.adminid, m.regdate, ms.lastactivity,
			m.credits, m.email, mp.gender, mp.site,	mp.icq, mp.qq, mp.yahoo, mp.msn, mp.taobao, mp.alipay,
			ms.buyercredit, ms.sellercredit $fieldsadd
			FROM ".DB::table($posttable)." p
			LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid
			LEFT JOIN ".DB::table('common_member_status')." ms USING(uid)
			LEFT JOIN ".DB::table('common_member_profile')." mp USING(uid)
			$verifyadd
			WHERE pid='$_G[gp_pid]'");

		$postlist[$post['pid']] = viewthread_procpost($post, $lastvisit, $ordertype);

		$usertrades = $userthreads = array();
		if(!$_G['inajax']) {
			$limit = 6;
			$query = DB::query("SELECT t.tid, t.pid, t.aid, t.subject, t.price, t.credit, t.displayorder FROM ".DB::table('forum_trade')." t
				LEFT JOIN ".DB::table(getattachtablebytid($_G['tid']))." a ON t.aid=a.aid
				WHERE t.sellerid='".$_G['forum_thread']['authorid']."' AND t.tid='$_G[tid]' ORDER BY t.displayorder DESC LIMIT ".($limit + 1));
			$usertradecount = 0;
			while($usertrade = DB::fetch($query)) {
				if($usertrade['pid'] == $post['pid']) {
					continue;
				}
				$usertradecount++;
				$usertrades[] = $usertrade;
				if($usertradecount == $limit) {
					break;
				}
			}

		}

		if($_G['forum_attachpids']) {
			require_once libfile('function/attachment');
			parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, array($trade['aid']));
		}

		$post = $postlist[$_G['gp_pid']];

		$post['buyerrank'] = 0;
		if($post['buyercredit']){
			foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
				if($post['buyercredit'] <= $credit) {
					$post['buyerrank'] = $level;
					break;
				}
			}
		}
		$post['sellerrank'] = 0;
		if($post['sellercredit']){
			foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
				if($post['sellercredit'] <= $credit) {
					$post['sellerrank'] = $level;
					break;
				}
			}
		}

		$navtitle = $trade['subject'];

		if($post['authorid']) {
			$online = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_session')." WHERE uid IN($post[authorid]) AND invisible=0");
		}

		include template('forum/trade_info');
		exit;

	}
}

?>