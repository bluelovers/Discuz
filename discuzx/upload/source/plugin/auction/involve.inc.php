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
if(!in_array($operation, array('finish', 'view', 'message', 'admin_message')) && $auction['starttimeto'] < $_G['timestamp']) {
	showmessage(lang('plugin/auction', 'm_none_exist'), '', '', array('showdialog' => true));
}

//参与
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
		if($_G['cache']['plugin']['auction']['auc_reply']) {
			$reply_message = '';
			$reply_messages = array();
			if($auction['typeid'] == 1 && $auction['extra']) {
				$reply_messages = explode("\n", $_G['cache']['plugin']['auction']['auc_reply_message_1']);
			} elseif($auction['typeid'] == 1 && !$auction['extra']) {
				$reply_messages = explode("\n", $_G['cache']['plugin']['auction']['auc_reply_message_2']);
			} else {
				$reply_messages = explode("\n", $_G['cache']['plugin']['auction']['auc_reply_message_3']);
			}
			if($reply_messages) {
				$reply_message = $reply_messages[array_rand($reply_messages)];
			}
			$reply_message = str_replace(array('{name}', '{price}', '{priceunit}'), array($auction['name'], ($auction['typeid'] != 2 ? $auction['ext_price'] : ''), ($auction['typeid'] != 2 ? $_G['setting']['extcredits'][$auction['extid']]['title'] : '')), $reply_message);
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
			$ex_involve = DB::fetch_first("SELECT applyid,cur_price,status FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid' AND uid='{$_G[uid]}' ORDER BY cur_price DESC LIMIT 1");
			if(($ex_involve && $userext < $price - $ex_involve['cur_price']) || (!$ex_involve && $userext < $price)) {
				showmessage(lang('plugin/auction', 'm_insufficient',
						array(
							'price' => $price,
							'title' => $_G['setting']['extcredits'][$auction['extid']]['title'],
							'ext' => $userext,
							'unit' => $_G['setting']['extcredits'][$auction['extid']]['unit'],
							'title' => $_G['setting']['extcredits'][$auction['extid']]['title'], 
						), true), 'forum.php?mod=viewthread&tid='.$tid);
			}


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
				//	showmessage(lang('plugin/auction', 'm_type1_involved'), 'forum.php?mod=viewthread&tid='.$tid);
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
		//如果是虚拟物品的话且是兑换模式则自动给发消息,并更新卡密信息
		if($auction['extra'] == 1 && $auction['virtual'] == 1) {
			DB::query("UPDATE ".DB::table('plugin_auction_message')." SET uid='{$_G[uid]}' WHERE tid='{$thread[tid]}' AND uid='' LIMIT 1");
			notification_add(
				$_G['uid'],
				'system',
				lang('plugin/auction', 'n_auction_get'),
				array(
					'auctionname' => $auction['name'],
					'auctiontid' => $auction['tid'],
				),
				1
			);
			$status_top = 1;
		}

		if($auction['typeid'] == 1 || $type2_insert == 1) {
			$data = array(
				'applyid' => null,
				'tid' => $tid,
				'username' => $_G['username'],
				'uid' => $_G['uid'],
				'dateline' => $_G['timestamp'],
				'cur_price' => $price,
				'status' => ($auction['typeid'] == 1 && $auction['extra'] == 1) ? 1 : $status_top,
				'updated' => ($auction['typeid'] == 1 && $auction['extra'] == 1 && $auction['virtual']) ? 1 : 0,
				'mobile' => $memmobile,
				);
			DB::insert('plugin_auctionapply', $data);
			if($auction['extra']) {
				//先到先到模式如果人数已满则自动结算
				$now = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE tid='$tid'");
				if($now == $auction['number']) {
					//DB::query("UPDATE ".DB::table('plugin_auction')." SET status=1 WHERE tid='$tid'");
						require_once DISCUZ_ROOT.'./source/plugin/auction/finish.func.php';
						finish($auction);
					//}
				} elseif($now > $auction['number']) {
					showmessage('undefine_error', 'forum.php?mod=viewthread&tid='.$tid);
				}
			}
		}
		DB::query("UPDATE ".DB::table('plugin_auction')." SET hot=hot+1,top_price='$price',lastuser='$_G[username]' WHERE tid='$tid'");
		
		if($_G['cache']['plugin']['auction']['auc_reply']) {
			$auc_reply_message = dhtmlspecialchars(daddslashes(trim($_G['gp_auc_reply_message'])));
			if($auc_reply_message) {
				$thread = get_thread_by_tid($tid);
				include_once libfile('function/forum');
				$postid = insertpost(array(
					'fid' => $thread['fid'],
					'tid' => $tid,
					'first' => '0',
					'author' => $_G['username'],
					'authorid' => $_G['uid'],
					'subject' => '',
					'dateline' => $_G['timestamp'],
					'message' => $auc_reply_message,
					'useip' => $_G['clientip'],
					'invisible' => 0,
					'anonymous' => 0,
					'usesig' => 1,
					'htmlon' => 0,
					'bbcodeoff' => 0,
					'smileyoff' => -1,
					'parseurloff' => 0,
					'attachment' => '0',
				));
				if($postid) {
					DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies+1,lastpost='{$_G[timestamp]}',lastposter='{$_G[username]}' WHERE tid='$tid'");
					DB::query("UPDATE ".DB::table('common_member_count')." SET posts=posts+1 WHERE uid='{$_G[uid]}'");
					DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$tid\t$thread[subject]\t{$_G[timestamp]}\t{$_G[username]}',posts=posts+1,todayposts=todayposts+1 WHERE fid='{$thread[fid]}'");
				}
			}
		}

		showmessage(lang('plugin/auction', 'm_involved_succeed'), 'forum.php?mod=viewthread&tid='.$tid);
	}
//查看出价记录
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
	$multi = multi($list_count, $a_pp, $a_cp, 'plugin.php?id=auction:involve&operation=view&tid='.$tid.($_G['gp_top'] ? '&top=1' : ''));
	$multi = preg_replace("/<a\shref=\"([\s\S]*?)\"(.*?)>/ies", "aaa('\\1','\\2')", $multi);
	$multi = str_replace('\"', '"', $multi);
	include template('auction:viewthread_view');

//结算
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
//查看卡密
} elseif($operation == 'message') {
	if($auction['virtual']) {
		$apply = DB::fetch_first("SELECT * FROM ".DB::table('plugin_auction_message')." WHERE tid='$tid' AND uid='{$_G[uid]}'");
		if($apply) {
			include template('auction:viewthread_message');
			exit;
		} else {
			showmessage(lang('pluin/auction', 'm_no_message'), '', '', array('alert' => 'error'));
		}
	} else {
		showmessage(lang('plugin/auction', 'm_not_virtual'));
	}
} elseif($operation == 'admin_message') {
	if($_G['adminid'] != 1 && $_G['uid'] != $auction['uid']) {
		showmessage(lang('plugin/auction', 'm_no_perm'));
	}
	if($auction['virtual']) {
		$messages = $messageuids = $messageusers = array();
		$query = DB::query("SELECT * FROM ".DB::table('plugin_auction_message')." WHERE tid='{$tid}' LIMIT 100");
		while($message = DB::fetch($query)) {
			if($message['uid']) {
				$messageuids[] = $message['uid'];
			}
			$messages[] = $message;
		}
		if($messageuids) {
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($messageuids).")");
			while($username = DB::fetch($query)) {
				$messageusers[$username['uid']] = $username['username'];
			}
		}
		include template('auction:viewthread_message');
		exit;
	} else {
		showmessage(lang('plugin/auction', 'm_not_virtual'));	
	}
}

//修改出价记录的翻页
function aaa($aa,$bb) {
	return '<a href="javascript:;" onclick="ajaxget(\''.$aa.'\', \'list_ajax\');return false;"'.$bb.'>';
}
?>
