<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_trade.php 20981 2011-03-09 09:55:55Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_G['gp_page'])?1:intval($_G['gp_page']);
if($page<1) $page=1;
$id = empty($_G['gp_id'])?0:intval($_G['gp_id']);
$opactives['trade'] = 'class="a"';

if(empty($_GET['view'])) $_GET['view'] = 'we';

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$count = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'trade',
	'view' => $_GET['view'],
	'order' => $_G['gp_order'],
	'type' => $_G['gp_type'],
	'status' => $_G['gp_status'],
	'fuid' => $_G['gp_fuid'],
	'searchkey' => $_G['gp_searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = '1';
$apply_sql = '';

$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;

if($_GET['view'] == 'all') {

	$start = 0;
	$perpage = 100;
	if($_G['gp_order'] == 'hot') {
		$wheresql .= " AND t.tradesum>='$minhot'";
	}
	$alltype = $ordertype = in_array($_G['gp_order'], array('new', 'hot')) ? $_G['gp_order'] : 'new';
	$orderactives = array($ordertype => ' class="a"');
	loadcache('space_trade');
} elseif($_GET['view'] == 'me') {
	$viewtype = in_array($_G['gp_type'], array('sell', 'buylog')) ? $_G['gp_type'] : 'sell';
	if(!in_array($_G['gp_status'], array('attention'))) {
		$_G['gp_status'] = '';
	}

	if($_G['gp_status']) {
		$buyer_array = array('buyerid'=>$space['uid'], 'status'=>$_G['gp_status']);
		$seller_array = array('sellerid'=>$space['uid'], 'status'=>$_G['gp_status']);
		$status_sql = " AND status='$_GET[status]'";
	} else {
		$buyer_array = array('buyerid'=>$space['uid']);
		$seller_array = array('sellerid'=>$space['uid']);
		$status_sql = '';
	}

	switch ($_G['gp_type']) {
		case 'buylog':
			$need_count = false;

			$count = getcount('forum_tradelog', $buyer_array);
			$query = DB::query("SELECT * FROM ".DB::table('forum_tradelog')."
				WHERE buyerid='$space[uid]' $status_sql
				ORDER BY lastupdate DESC
				LIMIT $start,$perpage");

			break;
		case 'selllog':

			$count = getcount('forum_tradelog', $seller_array);
			$query = DB::query("SELECT * FROM ".DB::table('forum_tradelog')."
				WHERE sellerid='$space[uid]' $status_sql
				ORDER BY lastupdate DESC
				LIMIT $start,$perpage");

			$need_count = false;
			break;
		case 'eccredit':

			$need_count = false;
			break;
		default:
			$wheresql = "t.sellerid = '$space[uid]'";
			break;
	}


} elseif($_GET['view'] == 'tradelog') {

	$viewtype = in_array($_G['gp_type'], array('sell', 'buy')) ? $_G['gp_type'] : 'sell';
	$filter = $_G['gp_filter'] ? $_G['gp_filter'] : 'all';
	$sqlfield = $viewtype == 'sell' ? 'sellerid' : 'buyerid';
	$sqlfilter = '';
	$item = $viewtype == 'sell' ? 'selltrades' : 'buytrades';

	switch($filter) {
		case 'attention':
			$typestatus = $item; break;
		case 'eccredit'	:
			$typestatus = 'eccredittrades';
			$sqlfilter .= $item == 'selltrades' ? 'AND (tl.ratestatus=0 OR tl.ratestatus=1) ' : 'AND (tl.ratestatus=0 OR tl.ratestatus=2) ';
			break;
		case 'all':
			$typestatus = ''; break;
		case 'success':
			$typestatus = 'successtrades'; break;
		case 'closed'	:
			$typestatus = 'closedtrades'; break;
		case 'refund'	:
			$typestatus = 'refundtrades'; break;
		case 'unstart'	:
			$typestatus = 'unstarttrades'; break;
		default:
			$typestatus = 'tradingtrades';
			break;
	}
	require_once libfile('function/trade');

	$sqlfilter .= $typestatus ? 'AND tl.status IN (\''.trade_typestatus($typestatus).'\')' : '';

	$srchkey = stripsearchkey($_G['gp_searchkey']);

	if(!empty($srchkey)) {
		$sqlkey = 'AND tl.subject like \'%'.str_replace('*', '%', addcslashes($srchkey, '%_')).'%\'';
		$extrasrchkey = '&srchkey='.rawurlencode($srchkey);
		$srchkey = dhtmlspecialchars($srchkey);
	} else {
		$sqlkey = $extrasrchkey = $srchkey = '';
	}

	$tid = intval($_GET['tid']);
	$pid = intval($_GET['pid']);
	$sqltid = $tid ? 'tl.tid=\''.$tid.'\' AND '.($pid ? 'tl.pid=\''.$pid.'\' AND ' : '') : '';
	$extra .= $srchfid ? '&amp;filter='.$filter : '';
	$extratid = $tid ? "&amp;tid=$tid".($pid ? "&amp;pid=$pid" : '') : '';
	$num = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_tradelog')." tl, ".DB::table('forum_thread')." t WHERE $sqltid tl.$sqlfield='$_G[uid]' $sqlfilter $sqlkey AND tl.tid=t.tid"),0);

	$multi = multi($num, $perpage, $page, $theurl);
	$query = DB::query("SELECT tl.*, tr.aid, t.subject AS threadsubject
			FROM ".DB::table('forum_tradelog')." tl, ".DB::table('forum_thread')." t, ".DB::table('forum_trade')." tr
			WHERE $sqltid tl.$sqlfield='$_G[uid]' $sqlfilter $sqlkey
			AND tl.tid=t.tid AND tr.pid=tl.pid AND tr.tid=tl.tid
			ORDER BY tl.lastupdate DESC LIMIT $start,$perpage");

	$tradeloglist = array();
	while($tradelog = DB::fetch($query)) {
		$tradelog['lastupdate'] = dgmdate($tradelog['lastupdate'], 'u', 1);
		$tradelog['attend'] = trade_typestatus($item, $tradelog['status']);
		$tradelog['status'] = trade_getstatus($tradelog['status']);
		$tradeloglist[] = $tradelog;
	}
	$creditid = 0;
	if($_G['setting']['creditstransextra'][5]) {
		$creditid = intval($_G['setting']['creditstransextra'][5]);
	} elseif ($_G['setting']['creditstrans']) {
		$creditid = intval($_G['setting']['creditstrans']);
	}
	$extcredits = $_G['setting']['extcredits'];
	$orderactives = array($viewtype => ' class="a"');
	$need_count = false;

} elseif($_GET['view'] == 'eccredit') {

	require_once libfile('function/ec_credit');
	$uid = !empty($_G['gp_uid']) ? intval($_G['gp_uid']) : $_G['uid'];

	loadcache('usergroups');

	$member = DB::fetch_first("SELECT m.uid, mf.customstatus, m.username, m.groupid, mp.taobao, mp.alipay, ms.buyercredit, ms.sellercredit, m.regdate
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_profile')." mp USING(uid)
		LEFT JOIN ".DB::table('common_member_status')." ms USING(uid)
		LEFT JOIN ".DB::table('common_member_field_forum')." mf USING(uid)
		WHERE m.uid='$uid'");
	if(!$member) {
		showmessage('member_nonexistence', NULL, array(), array('login' => 1));
	}

	$member['avatar'] = '<div class="avatar">'.avatar($member['uid']);
	if($_G['cache']['usergroups'][$member['groupid']]['groupavatar']) {
		$member['avatar'] .= '<br /><img src="'.$_G['cache']['usergroups'][$member['groupid']]['groupavatar'].'" border="0" alt="" />';
	}
	$member['avatar'] .= '</div>';

	$member['taobaoas'] = str_replace("'", '', addslashes($member['taobao']));
	$member['regdate'] = dgmdate($member['regdate'], 'd');
	$member['usernameenc'] = rawurlencode($member['username']);
	$member['buyerrank'] = 0;
	if($member['buyercredit']){
		foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
			if($member['buyercredit'] <= $credit) {
				$member['buyerrank'] = $level;
				break;
			}
		}
	}
	$member['sellerrank'] = 0;
	if($member['sellercredit']){
		foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
			if($member['sellercredit'] <= $credit) {
				$member['sellerrank'] = $level;
				break;
			}
		}
	}

	$query = DB::query("SELECT variable, value, expiration FROM ".DB::table('forum_spacecache')." WHERE uid='$uid' AND variable IN ('buyercredit', 'sellercredit')");
	$caches = array();
	while($cache = DB::fetch($query)) {
		$caches[$cache['variable']] = unserialize($cache['value']);
		$caches[$cache['variable']]['expiration'] = $cache['expiration'];
	}

	foreach(array('buyercredit', 'sellercredit') AS $type) {
		if(!isset($caches[$type]) || TIMESTAMP > $caches[$type]['expiration']) {
			$caches[$type] = updatecreditcache($uid, $type, 1);
		}
	}
	@$buyerpercent = $caches['buyercredit']['all']['total'] ? sprintf('%0.2f', $caches['buyercredit']['all']['good'] * 100 / $caches['buyercredit']['all']['total']) : 0;
	@$sellerpercent = $caches['sellercredit']['all']['total'] ? sprintf('%0.2f', $caches['sellercredit']['all']['good'] * 100 / $caches['sellercredit']['all']['total']) : 0;
	$need_count = false;

	include template('home/space_eccredit');
	exit;

} elseif($_GET['view'] == 'onlyuser') {
	$uid = !empty($_G['gp_uid']) ? intval($_G['gp_uid']) : $_G['uid'];
	$wheresql = "t.sellerid = '$uid'";
} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_G['gp_fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql = "t.sellerid='$fuid'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql = "t.sellerid IN ($space[feedfriend])";
			$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' ORDER BY num DESC LIMIT 0,100");
		while ($value = DB::fetch($query)) {
			$userlist[] = $value;
		}

	} else {
		$need_count = false;
	}
}

$actives = array($_GET['view'] =>' class="a"');

if($need_count) {
	if($searchkey = stripsearchkey($_G['gp_searchkey'])) {
		$wheresql .= " AND t.subject LIKE '%$searchkey%'";
	}
	$havecache = false;
	if($_G['gp_view'] == 'all') {
		$cachetime = $_G['gp_order'] == 'hot' ? 43200 : 3000;
		if(!empty($_G['cache']['space_trade'][$alltype]) && is_array($_G['cache']['space_trade'][$alltype])) {
			$cachearr = $_G['cache']['space_trade'][$alltype];
			if(!empty($cachearr['dateline']) && $cachearr['dateline'] > $_G['timestamp'] - $cachetime) {
				$list = $cachearr['data'];
				$hiddennum = $threadarr['hiddennum'];
				$havecache = true;
			}
		}
	}
	if(!$havecache) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_trade')." t WHERE $wheresql"),0);
		if($count) {
			$query = DB::query("SELECT t.* FROM ".DB::table('forum_trade')." t
				INNER JOIN ".DB::table('forum_thread')." th ON t.tid=th.tid AND th.displayorder>='0'
				WHERE $wheresql
				ORDER BY $ordersql LIMIT $start,$perpage");
			$pids = $aids = $thidden = array();
			while ($value = DB::fetch($query)) {
				$aids[$value['aid']] = $value['aid'];
				$value['dateline'] = dgmdate($value['dateline']);
				$pids[] = (float)$value['pid'];
				$list[$value['pid']] = $value;
			}
			if($_G['gp_view'] == 'all') {
				$_G['cache']['space_trade'][$alltype] = array(
					'dateline' => $_G['timestamp'],
					'hiddennum' => $hiddennum,
					'data' => $list
				);
				save_syscache('space_trade', $_G['cache']['space_trade']);
			}

			if($_G['gp_view'] != 'all') {
				$multi = multi($count, $perpage, $page, $theurl);
			}

		}
	} else {
		$count = count($list);
	}
}

if($count) {
	$emptyli = array();
	if(count($list) % 5 != 0) {
		for($i = 0; $i < 5 - count($list) % 5; $i++) {
			$emptyli[] = $i;
		}
	}
}

if($_G['uid']) {
	$_GET['view'] = !$_GET['view'] ? 'we' : $_GET['view'];
	$navtitle = lang('core', 'title_'.$_GET['view'].'_trade');
} else {
	$navtitle = lang('core', 'title_trade');
}

include_once template("diy:home/space_trade");

?>