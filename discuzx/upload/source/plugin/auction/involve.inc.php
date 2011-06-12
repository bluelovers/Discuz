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


$operation = trim(getgpc('operation'));
if(!$_G['uid'] && $operation != 'view') {
	showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
}

$tid = intval(getgpc('tid'));
include libfile('function/forum');
$thread = get_thread_by_tid($tid, '*', "AND special='127'");

if($thread) {
	$auction = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
	$auction['typeid'] == 2 && $auction['top_price'] = !empty($auction['top_price']) ? $auction['top_price'] : $auction['base_price'];
}
if(!$thread || !$auction) {
	showmessage(lang('plugin/auction', 'm_none_exist'), '', '', array('showdialog' => true));
}
if(!in_array($operation, array('finish', 'view')) && $auction['starttimeto'] < $_G['timestamp']) {
	showmessage(lang('plugin/auction', 'm_none_exist'), '', '', array('showdialog' => true));
}

if($operation == 'join') {
	if($auction['uid'] == $_G['uid']) {
		showmessage(lang('plugin/auction', 'm_owner'), '', '', array('showdialog' => true, 'closetime' => 3));
	}
	if($auction['starttimefrom'] > $_G['timestamp']) {
		showmessage(lang('plugin/auction', 'm_starttime_error'), '', '', array('showdialog' => true, 'closetime' => 3));
	}
	if($auction['status']) {
		showmessage(lang('plugin/auction', 'm_end'), '', '', array('showdialog' => true, 'closetime' => 3));
	}
	if(!submitcheck('confirmsubmit')) {
		$limit = $auction['number'] - 1;


		if($_G['cache']['plugin']['auction']['auc_mobile']) {
			$mobile = getuserprofile('mobile');
		}
		if($auction['typeid'] == 1) {
			$involved = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid' AND uid='{$_G[uid]}'");
			if($involved) {
				showmessage(lang('plugin/auction', 'm_type1_involved'), '', '', array('showdialog' => true, 'closetime' => 3));
			}
		}
		$the_last_one = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid' AND status='2' ORDER BY cur_price DESC LIMIT $limit,1");
		$memext = getuserprofile('extcredits'.($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit']));
		if($auction['typeid'] == 1 && $memext < $auction['ext_price']) {
			
			if($_G['setting']['creditstrans'] == $auction['extid']) {
				$m_insufficient = lang('plugin/auction', 'm_insufficient',
					array(
						'price' => $auction['ext_price'],
						'title' => $_G['setting']['extcredits'][$auction['extid']]['title'],
						'ext' => $memext,
						'unit' => $_G['setting']['extcredits'][$auction['extid']]['unit'],
						'title' => $_G['setting']['extcredits'][$auction['extid']]['title'], 
					), true).(($_G['setting']['ec_account'] || $_G['setting']['ec_tenpay_opentrans_chnid']) ? lang('plugin/auction', 'm_charge') : '');
				 
			}

		//	showmessage(lang('plugin/auction', 'm_insufficient'), '', '', array('showdialog' => true, 'closetime' => 300000000000));
		} elseif($auction['typeid'] == 1) {
			$m_type1_tips_p = lang('plugin/auction', 'm_type1_tips_p',
					array(
						'ext' => $memext,
						'title' => $_G['setting']['extcredits'][$auction['extid']]['title'],
						'price' => $auction['ext_price'],
						)
					);
		} elseif($auction['typeid'] == 2) {
			$m_type2_tips_p_pre = lang('plugin/auction', 'm_type2_tips_p_pre',
					array(
						'ext' => $memext,
						'title' => $_G['setting']['extcredits'][$auction['extid']]['title'],
					));
			$m_type2_tips_p_beh = lang('plugin/auction', 'm_type2_tips_p_beh');
		}
		include template('auction:involve');
		exit;
	} else {

		$userext = getuserprofile('extcredits'.($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit']));
		$status_top = $type2_insert = 0;
		if($_G['cache']['plugin']['auction']['auc_mobile']) {
			$mobile = $memmobile = 0;
			$mobile = trim($_G['gp_mobile']);
			$iscont = preg_match("/^(\+)?(86)?0?1\d{10}$/", $mobile);
			if(!$iscont) {
				$mobile = '';
			}
			$memmobile = getuserprofile('mobile');

			if(!$memmobile && $mobile) {
				DB::query("UPDATE ".DB::table('common_member_profile')." SET mobile='$mobile' WHERE uid='$_G[uid]'");
				$memmobile = $mobile;
			} elseif(!$memmobile && !$mobile) {
				showmessage(lang('plugin/auction', 'm_mobile_error'));
			}
		}

		if($auction['typeid'] == 1) {
			if($userext < $auction['ext_price']) {
				showmessage(lang('plugin/auction', 'm_insufficient',
						array(
							'price' => $auction['ext_price'],
							'title' => $_G['setting']['extcredits'][$auction['extid']]['title'],
							'ext' => $userext,
							'unit' => $_G['setting']['extcredits'][$auction['extid']]['unit'],
							'title' => $_G['setting']['extcredits'][$auction['extid']]['title'], 
						), true), 'forum.php?mod=viewthread&tid='.$tid);
			}
			$price = $auction['ext_price'];

			$involved = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid' AND uid='{$_G[uid]}'");
			if($involved) {
				showmessage(lang('plugin/auction', 'm_type1_involved'), 'forum.php?mod=viewthread&tid='.$tid);
			} else {
				$delta_price = $price;
			}

		} elseif($auction['typeid'] == 2) {
			$price = intval(getgpc('price'));
			if(($price - $auction['base_price']) % $auction['delta_price'] != 0) {
				showmessage(lang('plugin/auction', 'm_delta_error'), 'forum.php?mod=viewthread&tid='.$tid);
			}

			$limit = $auction['number'] - 1;

			$the_last_one = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid' AND status='2' ORDER BY cur_price DESC,dateline ASC LIMIT $limit,1");
			if(empty($price) || (!empty($the_last_one) && $price < $the_last_one['cur_pirce'] + $auction['delta_price'])) {
				showmessage(lang('plugin/auction', 'm_too_low'), 'forum.php?mod=viewthread&tid='.$tid);
			}
			if($userext < $price) {
				showmessage(lang('plugin/auction', 'm_insufficient',
						array(
							'price' => $price,
							'title' => $_G['setting']['extcredits'][$auction['extid']]['title'],
							'ext' => $userext,
							'unit' => $_G['setting']['extcredits'][$auction['extid']]['unit'],
							'title' => $_G['setting']['extcredits'][$auction['extid']]['title'], 
						), true), 'forum.php?mod=viewthread&tid='.$tid);
			}

			$ex_involve = DB::fetch_first("SELECT applyid,cur_price,status FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid' AND uid='{$_G[uid]}' ORDER BY cur_price DESC LIMIT 1");

			if($ex_involve) {
				if($ex_involve['cur_price'] >= $price) {
					showmessage(lang('plugin/auction', 'm_too_low'), 'forum.php?mod=viewthread&tid='.$tid);
				}
				//第N次竞拍
				$delta_price = $price - $ex_involve['cur_price'];
			} else {
				//第一次竞拍
				$delta_price = $price;
			}
			if($the_last_one && !$ex_involve) {
				if($price < $the_last_one['cur_price'] + $auction['delta_price']) {
					showmessage(lang('plugin/auction', 'm_too_low'), '', '', array('showdialog' => true, 'closetime' => 3));
				}
				DB::query("UPDATE ".DB::table('plugin_auctionapply')." SET status='0' WHERE applyid='$the_last_one[applyid]'");
				$type2_insert = 1;
			} elseif($ex_involve && the_last_one) {
				
				if($price < $the_last_one['cur_price'] + $auction['delta_price']) {
					showmessage(lang('plugin/auction', 'm_too_low'), '', '', array('showdialog' => true, 'closetime' => 3));
				}
				if($ex_involve['status'] == 2) {
					if($price <= $ex_involve['cur_price']) {
						showmessage(lang('plugin/auction', 'm_too_low'), '', '', array('showdialog' => true, 'closetime' => 3));
					} else {
						DB::query("UPDATE ".DB::table('plugin_auctionapply')." SET cur_price='$price',dateline='$_G[timestamp]',mobile='$memmobile' WHERE applyid='$ex_involve[applyid]'");
						$delta_price = $price - $ex_involve['cur_price'];
					}
//					showmessage(lang('plugin/auction', 'm_type1_involved'), 'forum.php?mod=viewthread&tid='.$tid);
				} elseif($ex_involve['status'] == 0) {
					if($price < $the_last_one['cur_price']) {
						showmessage(lang('plugin/auction', 'm_too_low'), '', '', array('showdialog' => true, 'closetime' => 3));
					}
					DB::query("UPDATE ".DB::table('plugin_auctionapply')." SET status='0' WHERE applyid='$the_last_one[applyid]'");
					DB::query("UPDATE ".DB::table('plugin_auctionapply')." SET status='2',cur_price='$price',dateline='$_G[timestamp]',mobile='$memmobile' WHERE applyid='$ex_involve[applyid]'");
				}
			} elseif(empty($the_last_one) && $ex_involve) {
				showmessage(lang('plugin/auction', 'm_type1_involved'), 'forum.php?mod=viewthread&tid='.$tid);
			} elseif(empty($the_last_one) && empty($ex_involve)) {
				if($price < $auction['base_price']) {
					showmessage(lang('plugin/auction', 'm_too_low'), '', '', array('showdialog' => true, 'closetime' => 3));
				}
				$type2_insert = 1;
			}
			$status_top = 2;
	
		}


		updatemembercount($_G['uid'], array('extcredits'.($auction['extid'] ? $auction['extid'] : $_G['cache']['plugin']['auction']['auc_extcredit']) => -$delta_price), false, 'AUC', $tid);

		if($auction['typeid'] == 1 || $type2_insert == 1) {
			$data = array(
				'applyid' => null,
				'tid' => $tid,
				'username' => $_G['username'],
				'uid' => $_G['uid'],
				'dateline' => $_G['timestamp'],
				'cur_price' => $price,
				'status' => $status_top,
				'updated' => 0,
				'mobile' => $memmobile,
				);
			DB::insert('plugin_auctionapply', $data);
			if($auction['extra']) {
				$now = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid'");
				if($now == $auction['number']) {
					//结算
					require_once DISCUZ_ROOT.'./source/plugin/auction/finish.func.php';
					finish($auction);
				} elseif($now > $auction['number']) {
					showmessage('undefined_error', 'forum.php?mod=viewthread&tid='.$tid);
				}
			}
		}
		DB::query("UPDATE ".DB::table('plugin_auction')." SET hot=hot+1,top_price='$price',lastuser='$_G[username]' WHERE tid='$tid'");
		showmessage(lang('plugin/auction', 'm_involved_succeed'), 'forum.php?mod=viewthread&tid='.$tid);
	}
} elseif($operation == 'view') {
	
	$a_pp = 10;
	$a_cp = intval($_G['gp_page']);
	$a_cp = $a_cp ? $a_cp : 1;
	$a_s = ($a_cp-1)*$a_pp;
	
	$sqladd = $_G['gp_top'] ? " AND status='1'" : '';
	$list = array();
	$auc = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction')." WHERE tid='$tid'");
	$showmobile = ($_G['uid'] == $auc['uid'] || in_array($_G['adminid'], array(1,2)));
	$list_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid'$sqladd");
	if($list_count) {
		$query = DB::query("SELECT * FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid'$sqladd ORDER BY cur_price DESC,".($auc['typeid'] == 1 ? 'dateline' : 'status')." DESC LIMIT $a_s,$a_pp");
		while($list_1 = DB::fetch($query)) {
			$list_1['dateline'] = dgmdate($list_1['dateline'], 'Y-m-d H:i:s');
			$list[] = $list_1;
		}
	}
	$_G['gp_ajaxtarget'] = '';
	$multi = multi($list_count, $a_pp, $a_cp, 'plugin.php?id=auction:involve&operation=view&tid='.$tid);
	include template('auction:viewthread_view');
} elseif($operation == 'finish') {
	if($auction['uid'] != $_G['uid']) {
		showmessage(lang('plugin/auction', 'm_not_owner'), '', '', array('showdialog' => true));
	}
	if($auction['status']) {
		showmessage(lang('plugin/auction', 'm_finished_alrd'), '', '', array('showdialog' => true));
	}
	if($auction['starttimeto'] <= $_G['timestamp']) {
		require_once DISCUZ_ROOT.'./source/plugin/auction/finish.func.php';
		$finished = finish($auction);
		if($finished) {
			DB::query("UPDATE ".DB::table('plugin_auction')." SET status=1 WHERE tid='$tid'");
			showmessage(lang('plugin/auction', 'm_finish_succeed'), 'forum.php?mod=viewthread&tid='.$tid, '', array('showdialog' => true, 'locationtime' => 3));
		} else {
			DB::query("UPDATE ".DB::table('plugin_auction')." SET status=1 WHERE tid='$tid'");
			showmessage(lang('plugin/auction', 'm_finish_error'), 'forum.php?mod=viewthread&tid='.$tid, '', array('showdialog' => true, 'locationtime' => 3));
		}
	} else {
		showmessage(lang('plugin/auction', 'm_not_deadline'), '', '', array('showdialog' => true));
	}
}


?>
