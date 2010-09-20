<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: groupbuy.inc.php 4373 2010-09-08 08:27:09Z yumiao $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$_g_xid = intval($_GET['xid']);
$_g_uid = intval($_GET['uid']);

if($_REQUEST['id'] && $_REQUEST['xid']) {
	$groupbuy = $_BCACHE->getiteminfo('groupbuy', $_REQUEST['xid'], $_REQUEST['id']);
	$groupbuy['message'] = bbcode2html($groupbuy['message']);
	if(!$groupbuy) {
		showmessage('not_found_msg', 'index.php');
	}
}
if(!empty($_GET['do'])) {
	if(!in_array($_GET['do'], array('markdelstatus', 'marknormalstatus', 'groupbuy_attend_detail'))) {
		showmessage('system_error', '', 'error');
	} else {
		if(!ckfounder($_G['uid']) && !array_key_exists($_REQUEST['id'], $_G['myshopsarr'])) {
			showmessage('no_perm', "store.php?id=".$_GET['id']."&action=groupbuy&xid=".$_g_xid);
		}
	}
}

if(submitcheck('submitgroupbuyjoin')) {
	@include_once B_ROOT.'./uc_client/client.php';
	$arr_data = array();

	$joininfo = $_POST['join'];
	$arr_data['itemid'] = $joininfo['groupbuyid'];
	$arr_data['uid'] = $_G['uid'];
	$arr_data['username'] = $_G['username'];
	$arr_data['realname'] = $joininfo['realname'];
	$arr_data['mobile'] = $joininfo['mobile'];
	$arr_data['dateline'] = $_G['timestamp'];

	if($groupbuy['grade'] < 3 || $groupbuy['close'] == 1) {
		$end_join = true;
	}

	if($groupbuy['groupbuymaxnum'] > 0 && ($groupbuy['groupbuymaxnum'] == $groupbuy['buyingnum'])) {
		$end_join = true;
	}

	if(empty($arr_data['uid'])) {
		$checkresults[] = array('username'=>$lang['not_login']);
	}

	if($end_join) {
		showmessage('groupbuy_end_join');
		exit;
	}

	$_joined_info = loadClass('groupbuy')->exist_join_user($_G['uid'], $arr_data['itemid']);
	if(!empty($_joined_info)) {
		if($_joined_info['status'] == 1) {
			showmessage('already_joined');
			exit;
		} else {
			$arr_data['status'] = 0;
		}
	}
	if(empty($arr_data['realname'])) {
		$checkresults[] = array('realname'=>$lang['realname_is_require']);
	}

	if(empty($arr_data['mobile'])) {
		$checkresults[] = array('mobile'=>$lang['mobile_is_require']);
	}

	if(!is_numeric($arr_data['mobile'])) {
		$checkresults[] = array('mobile'=>$lang['mobilenumber_not_numeric']);
	}

	if(!empty($checkresults)) {
		showmessage('requirefiled_not_complate', '', '', '', $checkresults);
	}
	$groupbuyattr = loadClass('attr')->get_groupby_user_attr();
	foreach($groupbuyattr as $k => $v) {
		$arr_data[$v['fieldname']] = $joininfo[$v['fieldname']];
	}
	inserttable('groupbuyjoin', $arr_data, 0, true);
	if(!empty($_joined_info) && $arr_data['status'] == 0) {
		if(isset($lang['groupbuy_join_rewriteinfo_title'])) {
			eval("\$pm_shop_title = \"".$lang['groupbuy_join_rewriteinfo_title']."\";");
		}
		if(isset($lang['groupbuy_join_rewriteinfo_message'])) {
			eval("\$pm_shop_message = \"".$lang['groupbuy_join_rewriteinfo_message']."\";");
		}
		uc_pm_send($arr_data['uid'], $shop['uid'], $pm_shop_title, $pm_shop_message);
	} else {
		$c_groupby = loadClass('groupbuy');
		$c_groupby->update_groupby_join_num($arr_data['itemid']);
		if(isset($lang['groupbuy_join_success_title'])) {
			eval("\$pm_title = \"".$lang['groupbuy_join_success_title']."\";");
		}
		if(isset($lang['groupbuy_join_success_message'])) {
			eval("\$pm_message = \"".$lang['groupbuy_join_success_message']."\";");
		}
		uc_pm_send($shop['uid'], $_G['uid'], $pm_title, $pm_message);
	}
	$_BCACHE->deltype('detail', 'groupbuy', $groupbuy['shopid'], $groupbuy['itemid']);
	showmessage('join_success', "store.php?id=".$_GET['id']."&action=groupbuy&xid=".$_g_xid);
	exit;
}

if(empty($_g_xid)) {
	$tpp = $_G['setting']['groupbuyperpage'];
	//团购列表
	$_BCACHE->cachesql('groupbuylist', 'SELECT i.itemid FROM '.tname('groupbuyitems')." i WHERE i.shopid='$shop[itemid]' AND i.grade>2 ORDER BY i.displayorder_s ASC, i.itemid DESC", 0, 1, $tpp, 0, 'storelist', 'groupbuy', $shop['itemid']);
	$groupbuylist_multipage = $_SBLOCK['groupbuylist_multipage'];
	$resultcount = $_SBLOCK['groupbuylist_listcount'];
	foreach($_SBLOCK['groupbuylist'] as $result) {
		$result = $_BCACHE->getiteminfo('groupbuy', $result['itemid'], $shop['itemid']);
		$result['groupbuytime'] = date('Y-m-d', $result['validity_start']).' '.$lang['groupbuyto'].' '.date('Y-m-d', $result['validity_end']);
		$result['thumb'] = str_replace('static/image/nophoto.gif', 'static/image/noimg.gif', $result['thumb']);
		$result['message'] = trim(strip_tags($result['message']));
		$result['groupbuydiscount'] = round(($result['groupbuypriceo'] / $result['groupbuyprice']), 2) * 10;
		$result['groupbuysave'] = round($result['groupbuyprice'] - $result['groupbuypriceo']);
		$result['groupbuypriceo'] = round($result['groupbuypriceo']);
		$result['groupbuyprice'] = round($result['groupbuyprice']);
		$groupbuylist[] = $result;
	}
	$seo_title = $lang['groupbuylist'] . ' - ' . $seo_title;
	$theurl = "store.php?id=$shop[itemid]&action=groupbuy";

} elseif($_GET['do'] == 'markdelstatus') {
	if(!$_g_xid || !$_g_uid) { showmessagee('system_error');}
	$c_groupby = loadClass('groupbuy');
	$sql = "update ".tname('groupbuyjoin')." set status = 0 WHERE itemid = ".$_g_xid." AND uid=".$_g_uid.";";
	$query = DB::query($sql);
	$c_groupby->update_groupby_join_num($_g_xid, -1);
	if($groupbuy['close'] == 1 && $groupbuy['validity_end'] > $_G['timestamp'] && $groupbuy['buyingnum'] == $groupbuy['groupbuymaxnum']) {
		DB::query("UPDATE ".tname('groupbuyitems')." SET close = 0 WHERE itemid=".$_g_xid);
	}
	$_BCACHE->deltype('detail', 'groupbuy', $_GET['id'], $_g_xid);
	$username = DB::result_first("SELECT username FROM ".tname('members')." WHERE uid=".$_g_uid);
	if(isset($lang['groupbuy_join_cancel_title'])) {
		eval("\$pm_title = \"".$lang['groupbuy_join_cancel_title']."\";");
	}
	if(isset($lang['groupbuy_join_cancel_message'])) {
		eval("\$pm_message = \"".$lang['groupbuy_join_cancel_message']."\";");
	}
	uc_pm_send($shop['uid'], $_g_uid, $pm_title, $pm_message);
	header("location: store.php?id=".$_GET['id']."&action=groupbuy&do=groupbuy_attend_detail&xid=".$_g_xid);
	exit;

} elseif($_GET['do'] == 'marknormalstatus') {
	if(!$_g_xid || !$_g_uid) { showmessagee('system_error');}
	$c_groupby = loadClass('groupbuy');
	$sql = "update ".tname('groupbuyjoin')." set status = 1 WHERE itemid = ".$_g_xid." AND uid=".$_g_uid.";";
	$query = DB::query($sql);
	$c_groupby->update_groupby_join_num($_g_xid);
	$_BCACHE->deltype('detail', 'groupbuy', $_GET['id'], $_g_xid);
	$username = DB::result_first("SELECT username FROM ".tname('members')." WHERE uid=".$_g_uid);
	if(isset($lang['groupbuy_join_finalsuccess_title'])) {
		eval("\$pm_title = \"".$lang['groupbuy_join_finalsuccess_title']."\";");
	}
	if(isset($lang['groupbuy_join_finalsuccess_message'])) {
		eval("\$pm_message = \"".$lang['groupbuy_join_finalsuccess_message']."\";");
	}
	uc_pm_send($shop['uid'], $_g_uid, $pm_title, $pm_message);
	header("location: store.php?id=".$_GET['id']."&action=groupbuy&do=groupbuy_attend_detail&xid=".$_g_xid);
	exit;

} elseif($_GET['do'] == 'groupbuy_attend_detail') {

	$groupbuy['date'] = date('Y'.$lang['year'].'m'.$lang['mon'].'d'.$lang['day'], $groupbuy['dateline']);
	$groupbuy['days'] = ceil(($_G['timestamp'] - $groupbuy['dateline']) / 86400);
	$groupbuy['join_total_num'] = DB::fetch(DB::query("SELECT count(*) as count FROM ".tname("groupbuyjoin")." WHERE itemid = ".$_g_xid.""));
	$groupbuy['join_total_num'] = $groupbuy['join_total_num']['count'];
	$groupbuyattr = loadClass('attr')->get_groupby_user_attr();
	$sql = "SELECT * FROM ".tname('groupbuyjoin')." WHERE itemid = ".$_g_xid;
	$query = DB::query($sql);
	while($res = DB::fetch($query)) {
		$groupbuy_join_list[] = $res;
	}

	//导出 Excel
	if($_GET['exportexcel'] == 1) {
		header("Content-Disposition: attachment; filename=".$groupbuy['subject'].".csv");
		header('Content-Type:APPLICATION/OCTET-STREAM');
		echo $lang['joinusername'].','.$lang['realname'].','.$lang['mobile'].',';
		foreach($groupbuyattr as $attr) {
			echo '"'.$attr['fieldtitle'].'",';
		}
		echo "\n";
		foreach($groupbuy_join_list as $list_item) {
			if($list_item['status']) {
				echo '"'.str_replace('"','""',$list_item['username']).'",';
				echo '"'.str_replace('"','""',$list_item['realname']).'",';
				echo '"'.str_replace('"','""',$list_item['mobile']).'",';
				foreach($groupbuyattr as $attr) {
					echo '"'.str_replace('"','""',$list_item[$attr['fieldname']]).'",';
				}
				echo "\n";
			}
		}
		exit;
	}
}else {
	$groupbuyattr = loadClass('attr')->get_groupby_user_attr();
	$allowreply = ($shop['allowreply'] && $groupbuy['allowreply']) ? 1 : 0;
	$groupbuy['groupbuytime'] = date('Y-m-d', $groupbuy['validity_end']);
	$groupbuy['groupbuydiscount'] = round(($groupbuy['groupbuypriceo'] / $groupbuy['groupbuyprice']), 2) * 10;
	$groupbuy['groupbuysave'] = round($groupbuy['groupbuyprice'] - $groupbuy['groupbuypriceo']);
	$groupbuy['surplusnum'] = !empty($groupbuy['groupbuymaxnum']) ? $groupbuy['groupbuymaxnum'] - $groupbuy['buyingnum'] : $lang['groupbuy_notrestricted'];
		$groupbuy['groupbuypriceo'] = round($groupbuy['groupbuypriceo']);
		$groupbuy['groupbuyprice'] = round($groupbuy['groupbuyprice']);
	$relatedarr = array();
	$relatedarr = getrelatedinfo('groupbuy', $groupbuy['itemid'], $shop['itemid']);
	//更新统计数
	$isupdate = freshcookie($action,$groupbuy['itemid']);
	if($isupdate || !$_G['setting']['updateview']) updateviewnum($action,$groupbuy['itemid']);
	//评论
	$listcount = $groupbuy['replynum'];
	$_G['setting']['viewspace_pernum'] = intval($_G['setting']['viewspace_pernum']);
	$type = 'groupbuy';

	$my_join_info = loadClass('groupbuy')->get_my_join_info($_G['uid'], $groupbuy['itemid']);
	if($my_join_info) {
		$already_joined = true;
	} else {
		$already_joined = false;
	}
	
	$groupbuy_is_on = true;
	
	if($groupbuy['validity_end'] < $_G['timestamp'] || $groupbuy['grade'] < 3 || $groupbuy['close'] == 1) {
		$groupbuy_is_on = false;
	}
	$seo_title = $groupbuy['subject'] . ' - ' . $seo_title;
	$seo_description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($groupbuy['message'])), 200));

}
?>