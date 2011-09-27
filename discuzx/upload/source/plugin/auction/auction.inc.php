<?php
/*
 *	auction.inc.php 积分竞拍插件
 *	For Discuz!X2
 *	2011-03-17 10:36:18  zhouxingming
 *
 * */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = lang('plugin/auction', 'auction');

$actionarr = array('index', '1', '2', 'my', 'search', 'mydetail', 'refresh');
$action = in_array($_G['gp_action'], $actionarr) ? $_G['gp_action'] : 'index';
$typeid = intval($_G['gp_typeid']);
$perpage = 15;



$tops = checktop();
if(empty($tops)) {
	include_once DISCUZ_ROOT.'./data/cache/cache_auctiontop.php';
}

if($action == 'index') {

	$typeid = intval($action);
	$type1 = $type2 = array();
	$type1 = getauctions('index', 8, false, "starttimeto>'$_G[timestamp]' AND status=0");
	$type2 = getauctions('index', 4, false, "starttimeto<'$_G[timestamp]' OR status=1");

} elseif(in_array($action, array(1, 2))) {

	$aucs = getauctions($action, $perpage, true, '', 'plugin.php?id=auction&action=search&sctype='.$action);

} elseif($action == 'search') {

	if(!$_G['uid']) {
		showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
	}

	$srchtxt = trim($_G['gp_sctxt']);
	$sctype = in_array($_G['gp_sctype'], array(1,2,3)) ? $_G['gp_sctype'] : 0;
	$sctime = in_array($_G['gp_sctime'], array(1,2,3)) ? $_G['gp_sctime'] : 0;

	$sqladd = '';
	if($srchtxt) {
		$sqladd .= " name LIKE '%$srchtxt%' ";
	}
	if($sctype) {
		switch($sctype) {
			case '1':
				$sqladd = ($sqladd ? 'AND' : '')." typeid='1' AND extra='1'";
				break;
			case '2':
				$sqladd = ($sqladd ? 'AND' : '')." typeid='1' AND extra='0'";
				break;
			case '3':
				$sqladd = ($sqladd ? 'AND' : '')." typeid='2' AND extra='0'";
				break;
		}
	}
	switch($sctime) {
		case '1':
			$sqladd .= ($sqladd ? 'AND' : '')." starttimeto>'{$_G['timestamp']}' AND starttimefrom<'{$_G['timestamp']}'";
			break;
		case '2':
			$sqladd .= ($sqladd ? 'AND' : '')." starttimefrom>'{$_G['timestamp']}'";
			break;
		case '3':
			$sqladd .= ($sqladd ? 'AND' : '')." starttimeto<'{$_G['timestamp']}'";
			break;
		case '4':
			$sqladd .= "";
			break;
	}
	$aucs = getauctions('search', $perpage, true, $sqladd, 'plugin.php?id=auction&action=search&srchtxt='.$srchtxt.'&sctype='.$sctype.'&sctime='.$sctime);

} elseif ($action == 'mydetail') {

	$_G['gp_ajaxtarget'] = '';
	if(!$_G['uid']) {
		showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
	}
	$mwait = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE uid='$_G[uid]' AND status=0 AND starttimeto<'{$_G[timestamp]}'");
	$mstart = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE uid='$_G[uid]'");
	$mjoin = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE uid='$_G[uid]' ");
	$mgot = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE uid='$_G[uid]' AND status=1");
	$mleft = getuserprofile('extcredits'.$_G['cache']['plugin']['auction']['auc_extcredit']);
	if($_G['setting']['creditstrans'] == $_G['cache']['plugin']['auction']['auc_extcredit']) {
		if($_G['setting']['ec_account'] || $_G['setting']['ec_tenpay_opentrans_chnid']) {
			$charge = lang('plugin/auction', 'm_charge');
		}
	}
	include template('auction:mydetail');
} elseif($action == 'my') {
	$filter = trim($_G['gp_filter']);
	$fileter = in_array($filter, array('start', 'join', 'got')) ? $filter : 'join';
	$page = intval($_G['gp_page']);
	$page = $page > 0 ? $page : 1;
	$start = !$pages ? 0 : ($page - 1) * $limit;

	if($filter == 'start') {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE uid='$_G[uid]'");
		$query = DB::query("SELECT * FROM ".DB::table('plugin_auction')." WHERE uid='$_G[uid]' ORDER BY starttimefrom DESC LIMIT $start,$perpage");
		$url = 'plugin.php?id=auction&action=my&filter=start';
	
	} elseif($filter == 'join') {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE uid='$_G[uid]'");
		$query = DB::query("SELECT *,a.status as astatus, aa.status as aastatus FROM ".DB::table('plugin_auctionapply')." aa LEFT JOIN ".DB::table('plugin_auction')." a ON a.tid=aa.tid WHERE aa.uid='$_G[uid]' ORDER BY a.starttimefrom DESC LIMIT $start,$perpage");
		$url = 'plugin.php?id=auction&action=my&filter=join';
	
	} elseif($filter == 'got') {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auctionapply')." WHERE uid='$_G[uid]' AND status='1'");
		$query = DB::query("SELECT * FROM ".DB::table('plugin_auctionapply')." aa LEFT JOIN ".DB::table('plugin_auction')." a ON a.tid=aa.tid WHERE aa.uid='$_G[uid]' AND aa.status='1' ORDER BY a.starttimefrom DESC LIMIT $start,$perpage");
		$url = 'plugin.php?id=auction&action=my&filter=got';
	
	} elseif($filter == 'wait') {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_auction')." WHERE uid='$_G[uid]' AND status=0 AND starttimeto<'{$_G[timestamp]}'");
		$query = DB::query("SELECT * FROM ".DB::table('plugin_auction')." WHERE uid='$_G[uid]' AND status=0 AND starttimeto<'{$_G[timestamp]}' ORDER BY starttimefrom DESC LIMIT $start,$perpage");
		$url = 'plugin.php?id=auction&action=my&filter=wait';
	}
	while($result = DB::fetch($query)) {
		if($result['aid']) {
			$result['att'] = getforumimg($result['aid']);
		} else {
			$result['att'] = 'static/image/common/nophotosmall.gif" width="140';
		}
		$aucs[] = $result;
	}
		$multi = multi($count, $perpage, $page, $url);
} elseif($action == 'refresh') {
	if(in_array($_G['adminid'], array(1))) {
		updatetop();
		showmessage(lang('plugin/auction', 'm_refresh_success'), 'plugin.php?id=auction', '', array('alert' => 'right'));
	} else {
		showmessage(lang('plugin/auction', 'm_no_perm'));
	}
}
include template('auction:auction');


function updatetop() {
	global $_G;
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('plugin_auction')." WHERE starttimeto>'$_G[timestamp]' AND status=0 ORDER BY hot DESC LIMIT 10");
	while($top = DB::fetch($query)) {
		if($top['aid']) {
			$top['imgthumb'] = getforumimg($top['aid'], 0, 48, 48);
		} else {
			$top['imgthumb'] = 'static/image/common/nophotosmall.gif" width="48';
		}
		$data[] = $top;
	}
	
	require_once libfile('function/cache');
	writetocache('auctiontop', getcachevars(array('tops' => $data)));
	return $data;
}

function checktop() {
	global $_G;
	$cachefile = DISCUZ_ROOT.'./data/cache/cache_auctiontop.php';
	$lockfile = DISCUZ_ROOT.'./data/auctiontop.lock';
	$data = array();
	if(!file_exists($lockfile) || $_G['timestamp'] - filemtime($lockfile) > 100) {
		if($_G['timestamp'] - filemtime($cachefile) > 3600) {
			@touch($lockfile);
			$data = updatetop();
			@unlink($lockfile);
		}
	}
	return $data;

}

function getauctions($typeid, $limit, $pages = false, $sqladd = '', $url = '') {
	global $_G,$multi,$filter;

	$page = intval($_G['gp_page']);
	$page = $page > 0 ? $page : 1;
	$start = !$pages ? 0 : ($page - 1) * $limit;
	$table = DB::table('plugin_auction');
	if($typeid == 'index') {
		$where = $sqladd ? "WHERE $sqladd" : '';
	} elseif(in_array($typeid, array(1,2))) {
		$where = "WHERE typeid='$typeid'";
	} elseif($typeid == 'search') {
		$where = $sqladd ? "WHERE $sqladd" : '';
	}

	$typeid != 'index' && $count = DB::result_first("SELECT COUNT(*) FROM ".$table." $where");
	if($count || $typeid == 'index'){

	
		$query = DB::query("SELECT * FROM ".$table." $where ORDER BY ".($limit == 4 ? 'starttimeto' : 'starttimefrom')." DESC LIMIT $start,$limit");
		while($result = DB::fetch($query)) {
			if($result['aid']) {
				$result['att'] = getforumimg($result['aid']);
			} else {
				$result['att'] = 'static/image/common/nophotosmall.gif" width="140';
			}
			$data[] = $result;
		}
		if($pages) {

			$multi = multi($count, $limit, $page, $url);
		}
	}
	return $data;
}
?>
