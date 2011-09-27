<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-03-17 10:36:18  zhouxingming Comsenz Inc.
 *
 * */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//第一步确定是否中奖
function finish($auction) {
	global $_G;
	$applynum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE tid='{$auction[tid]}'");
	$sqlorder = '';
	if($applynum <= $auction['number']) {
		$sqlorder = '';
	}
	if($auction['typeid'] == 1) {
		if($auction['extra'] == 1) {
			$sqlorder = 'ORDER BY dateline ASC';
		} else {
			$sqlorder = 'ORDER BY rand()';
		}
	} elseif($auction['typeid'] == 2) {
		$sqlorder = ' AND status=2';
	}
	//设置竞拍状态为成功
	DB::query("UPDATE ".DB::table('plugin_auctionapply')." SET status=1 WHERE tid='{$auction[tid]}' $sqlorder LIMIT {$auction[number]}");

	$rows = DB::affected_rows();
	finishend($auction);

	//设置竞拍状态为结束
	DB::query("UPDATE ".DB::table('plugin_auction')." SET status=1 WHERE tid='{$auction[tid]}'");
	return 1;
}

//第二步积分操作发送&通知
function finishend($auction) {
	global $_G;

	$query = DB::query("SELECT * FROM ".DB::table('plugin_auctionapply')." WHERE tid='{$auction[tid]}' ORDER BY applyid DESC");
	$credit_back_tmp = array();
	$credit_go_tmp = array();
	while($apply = DB::fetch($query)) {
		if($apply['updated']) { //已经操作过
			continue;
		}
		//虚拟物品结算时设置卡密uid
		if($auction['virtual'] == 1 && !$apply['updated'] && $apply['status']) {
			DB::query("UPDATE ".DB::table('plugin_auction_message')." SET uid='{$apply[uid]}' WHERE tid='{$auction[tid]}' AND uid='' LIMIT 1");
		}

		//生成需要操作的积分数组
		//$credit_go_tmp = array(uid => price) 结算的积分需要加给发起者
		//$credit_back_tmp = array(uid => price) 退还的积分
		if($apply['status'] == 1) {
			if(!empty($credit_go_tmp[$apply['uid']])) {
				$credit_go_tmp[$apply['uid']]['price'] < $apply['cur_price'] && $credit_go_tmp[$apply['uid']]['price'] = $apply['cur_price'];
			} else {
				$credit_go_tmp[$apply['uid']]['price'] = $apply['cur_price'];
				$credit_go_tmp[$apply['uid']]['username'] = $apply['username'];
			}

		} else {
			if(!empty($credit_back_tmp[$apply['uid']])) {
				$credit_back_tmp[$apply['uid']]['price'] < $apply['cur_price'] && $credit_back_tmp[$apply['uid']]['price'] = $apply['cur_price'];
			} else {
				$credit_back_tmp[$apply['uid']]['price'] = $apply['cur_price'];
				$credit_back_tmp[$apply['uid']]['username'] = $apply['username'];
			}

		}
	}

	if($credit_back_tmp) {
		foreach($credit_back_tmp as $uid => $v){
			updatemembercount($uid, array('extcredits'.($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit']) => $v['price']), false, 'AUC', $auction['tid']);
			notification_add(
				$uid,
				'system',
				lang('plugin/auction', 'n_auction_lose'),
				array(
					'auctionname' => $auction['name'],
					'auctiontid' => $auction['tid'],
				),
				1
			);
		}
	}

	$owner_get = 0;
	if($credit_go_tmp) {
		foreach($credit_go_tmp as $uid => $v) {
			notification_add(
				$uid,
				'system',
				lang('plugin/auction', 'n_auction_get'),
				array(
					'auctionname' => $auction['name'],
					'auctiontid' => $auction['tid'],
					'auctioncredit' => $_G['setting']['extcredits'][($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit'])]['title'],
					'price' => $v['price'],
				),
				1
			);

			$owner_get += $v['price'];
		}

		DB::query("UPDATE ".DB::table('plugin_auctionapply')." SET updated='1' WHERE tid='$auction[tid]'");
	}
	notification_add(
		$auction['uid'],
		'system',
		lang('plugin/auction', 'n_auction_get_owner'),
		array(
			'auctionname' => $auction['name'],
			'auctiontid' => $auction['tid'],
			'auctioncredit' => $_G['setting']['extcredits'][($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit'])]['title'],
			'num' => count($credit_go_tmp),
			'price' => $owner_get,
		),
		1
	);
	if($owner_get) {
		updatemembercount($auction['uid'], array('extcredits'.($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit']) => $owner_get), false, 'AUC', $auction['tid']);
	}
}
?>
