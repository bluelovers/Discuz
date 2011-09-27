<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-14 13:51
 *	Filename: block_xml.inc.php
 *	Description: 第三方调用
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$clientid = intval($_G['gp_clientid']);
$sign = daddslashes(trim($_G['gp_sign']));
$clientcharset = strtolower(trim($_G['gp_charset']));
$clientcharset = str_replace('-', '', $clientcharset);
$servercharset = CHARSET;
$clientcharset = in_array($clientcharset, array('gbk', 'utf8')) ? $clientcharset : $servercharset;
$auc = $auclists = array();

if(empty($clientid) || empty($sign)) {
	exit('CLIENT_SIGN_ERROR');
}

$client_check = 0;
$client_check = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction_xml')." WHERE clientid='$clientid' AND sign='$sign'");
$servercharset = CHARSET;

if($client_check) {
	if($_G['gp_op'] == 'getconfig') {
		$xml = file_get_contents(DISCUZ_ROOT.'./source/plugin/auction/xml/block_xml.setting.'.$clientcharset.'.xml');
	} elseif($_G['gp_op'] == 'getdata') {
//	charset	=>gbk
//	clientid	=>1
//	items	=>10  		条数
//	op	=>getdata  	
//	order	=>1 		顺序 1=>发布时间倒叙 0=>人气倒叙
//	sold_status	=>0,1,2 当前状态 0=>正在进行 1=>即将开始 2=>已经结束
//	start	=>2 		limit里的start
//	tids	=>12,123  	tid
//	uids	=>12,2,2,2,2,2, 发布者id
//	version	=>X2
//	virtual	=>2,1 		是否虚拟 0=>实物 1=>虚拟
//	sign	=>123
		$sqladd 	= array();
		$uids 		= get_int_from_string($_G['gp_uids']);
		$sold_status 	= get_int_from_string($_G['gp_sold_status'], array(0, 1, 2));
		$tids 		= get_int_from_string($_G['gp_tids']);
		$virtual 	= get_int_from_string($_G['gp_virtual'], array(0, 1));
		$bannedids 	= get_int_from_string($_G['gp_bannedids']);
		$start 		= max(0, intval($_G['gp_start']));
		$order 		= intval($_G['gp_order']);
		$num 		= max(1, intval($_G['gp_items']));
		$num 		= $num > 10 ? 10 : $num;
		$width 		= intval($_G['gp_width']);
		$height 	= intval($_G['gp_height']);
		$typeid 	= get_int_from_string($_G['gp_type'], array(1, 2, 3));

		if(!empty($uids)) {
			$sqladd[] = 'uid IN ('.dimplode($uids).')';
		}
		if(!empty($tids)) {
			$sqladd[] = 'tid IN ('.dimplode($tids).')';
		}
		if(!empty($bannedids)) {
			$sqladd[] = 'tid NOT IN ('.dimplode($bannedids).')';
		}
		$is_real = in_array(0, $virtual);
		$is_virtual = in_array(1, $virtual);
		if($is_real && !$is_virtual) {
			$sqladd[] = 'virtual=0';
		} elseif($is_virtual && !$is_real) {
			$sqladd[] = 'virtual=1';
		}
		if(count($sold_status) < 3) {
			$sql_tmp = '';
			foreach($sold_status as $status) {
				if($status == 0) {
					$sql_tmp[] = "starttimeto>{$_G[timestamp]}";
				}
				if($status == 1) {
					$sql_tmp[] = "starttimefrom>{$_G[timestamp]}";
				}
				if($status == 2) {
					$sql_tmp[] = "starttimeto<{$_G[timestamp]}";
				}
			}
			$sql_tmp = implode(' OR ', $sql_tmp);
			$sqladd[] = '('.$sql_tmp.')';
		}
		if($typeid) {
			$typeid_count = count($typeid);
			if($typeid_count == 2) {
				if(!in_array(3, $typeid)) {
					$sqladd[] = 'typeid=1';
				} elseif(!in_array(1, $typeid)) {
					$sqladd[] = '(typeid=2 OR (typeid=1 AND extra=0))';
				} else {
					$sqladd[] = '(typeid=2 OR (typeid=1 AND extra=1))';
				}
			} elseif($typeid_count == 1) {
				if($typeid[0] == 3) {
					$sqladd[] = 'typeid=2';
				} else {
					$sqladd[] = 'typeid=1 AND extra='.($typeid[0] == 1 ? 1 : 0);
				}
			}
		}

		$query = DB::query("SELECT * FROM ".DB::table('plugin_auction')." ".($sqladd ? 'WHERE ' : '').implode(' AND ', $sqladd).' ORDER BY '.($order == 0 ? 'hot DESC' : 'tid DESC')." LIMIT $start,$num");

		while($auction = DB::fetch($query)) {
			$auc['id'] = $auction['tid'];
			$auc['url'] = str_replace(array('{tid}', '{page}', '{prevpage}'), array($auction['tid'], 1, 1), ($_G['setting']['domain']['app']['forum'] ? ('http://'.$_G['setting']['domain']['app']['forum'].'/') : $_G['siteurl']).(($_G['setting']['rewriterule']['forum_viewthread'] && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) ? $_G['setting']['rewriterule']['forum_viewthread'] : 'forum.php?mod=viewthread&tid='.$auction['tid']));
			$auc['title'] = $auction['name'];
			$auc['pic'] = $_G['siteurl'].'plugin.php?id=auction:getcover&tid='.$auction['tid'].'&size='.$width.'x'.$height.'&code='.md5($auction['tid'].'|'.$width.'|'.$height.$_G['config']['security']['authkey']);
			$auc['fields']['author'] = $auction['username'];
			$auc['fields']['authorid'] = $auction['uid'];
			$auc['fields']['type'] = ($auction['typeid'] == 2) ? lang('plugin/auction', 'auc_type3') : ($auction['extra'] == 0 ? lang('plugin/auction', 'auc_type2') : lang('plugin/auction', 'auc_type1'));
			$auc['fields']['type'] = str_replace('&nbsp;', '', $auc['fields']['type']);
			$auc['fields']['virtual'] = $auction['virtual'] ? lang('plugin/auction', 'auc_virtual_1') : lang('plugin/auction', 'auc_virtual_0');
			$auc['fields']['hot'] = $auction['hot'];
			$auc['fields']['price'] = ($auction['typeid'] == 2 ? $auction['base_price'] : $auction['ext_price']);
			$auc['fields']['priceunit'] = $_G['setting']['extcredits'][$auction['extid']]['title'];
			$auc['fields']['starttimefrom'] = dgmdate($auction['starttimefrom']);
			$auc['fields']['starttimeto'] = dgmdate($auction['starttimeto']);
			$auclists[] = $auc;
		}

		require_once libfile('class/xml');
		$xmlarray = array('html' => '', 'data' => $auclists);
		$xml = array2xml($xmlarray);

	} else {
		exit('OPERATION_ERROR');
	}
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	header("Content-type: text/xml;charset=".$clientcharset.';');
	echo $xml;
	exit();
} else {
	exit('CLIENT_SIGN_ERROR');
}


function get_int_from_string($str, $array = null) {
	$str = trim($str);
	$ids_tmp = explode(',', $str);
	$ids = array();
	foreach($ids_tmp as $key => $id) {
		$id = intval($id);
		if($array) {
			in_array($id, $array) && $ids[] = $id;
		} elseif(!empty($id)){
			$ids[] = $id;
		}
	}
	return $ids;
}
?>
