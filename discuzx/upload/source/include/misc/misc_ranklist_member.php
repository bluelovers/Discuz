<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_top.php 11682 2010-06-11 02:38:30Z chenchunshao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$multi = $gettype = '';
$list = array();
$cachetip = TRUE;
$perpage = 20;
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
if($page < 1) {
	$page = 1;
}
$start = ($page - 1) * $perpage;

require_once libfile('function/home');
ckstart($start, $perpage);

$creditkey = $cache_name = '';
$fuids = array();
$count = 0;
$now_pos = 0;
$now_choose = '';

if ($_G['gp_view'] == 'credit') {

	$gettype = 'credit';
	$creditsrank_change = 1;
	$extcredits = $_G['setting']['extcredits'];
	$now_choose = $_G['gp_orderby'] && $extcredits[$_G['gp_orderby']] ? $_G['gp_orderby'] : 'all';
	if(!$_G['gp_orderby'] || !$extcredits[$_G['gp_orderby']]) {
		$_G['gp_orderby'] = 'all';
	}
	if($_G['uid']) {
		$mycredits = $now_choose == 'all' ? $_G['member']['credits'] : getuserprofile('extcredits'.$now_choose);
		$cookie_name = 'space_top_credit_'.$_G['uid'].'_'.$now_choose;
		if($_G['cookie'][$cookie_name]) {
			$now_pos = $_G['cookie'][$cookie_name];
		} else {
			if($now_choose == 'all') {
				$pos_sql = "SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE credits>'$mycredits'";
			} else {
				$pos_sql = "SELECT COUNT(*) FROM ".DB::table('common_member_count')." WHERE extcredits$now_choose>'$mycredits'";
			}
			$now_pos = DB::result(DB::query($pos_sql), 0);
			$now_pos++;
			dsetcookie($cookie_name, $now_pos);
		}
	} else {
		$now_pos = -1;
	}
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif ($_G['gp_view'] == 'friendnum') {

	$gettype = 'friend';
	if($_G['uid']) {
		$space = $_G['member'];
		space_merge($space, 'count');
		$cookie_name = 'space_top_'.$_G['gp_view'].'_'.$_G['uid'];
		if($_G['cookie'][$cookie_name]) {
			$now_pos = $_G['cookie'][$cookie_name];
		} else {
			$pos_sql = "SELECT COUNT(*) FROM ".DB::table('common_member_count')." s WHERE s.friends>'$space[friends]'";
			$now_pos = DB::result(DB::query($pos_sql), 0);
			$now_pos++;
			dsetcookie($cookie_name, $now_pos);
		}
	} else {
		$now_pos = -1;
	}
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif ($_G['gp_view'] == 'invite') {

	$gettype = 'invite';
	$now_pos = -1;
	$inviterank_change = 1;
	$now_choose = 'thisweek';
	switch($_G['gp_orderby']) {
		case 'thismonth':
			$now_choose = 'thismonth';
			break;
		case 'today':
			$now_choose = 'today';
			break;
		case 'thisweek':
			$now_choose = 'thisweek';
			break;
		default :
			$now_choose = 'all';
	}
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_G['gp_view'] == 'blog') {

	$gettype = 'blog';
	$now_pos = -1;
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_G['gp_view'] == 'beauty') {

	$gettype = 'girl';
	$now_pos = -1;
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_G['gp_view'] == 'handsome') {

	$gettype = 'boy';
	$now_pos = -1;
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_G['gp_view'] == 'post') {

	$gettype = 'post';
	$postsrank_change = 1;
	$now_pos = -1;
	$now_choose = 'posts';
	switch($_G['gp_orderby']) {
		case 'digestposts':
			$now_choose = 'digestposts';
			break;
		case 'thismonth':
			$now_choose = 'thismonth';
			break;
		case 'today':
			$now_choose = 'today';
			break;
	}
	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} elseif($_G['gp_view'] == 'onlinetime') {

	$gettype = 'onlinetime';
	$onlinetimerank_change = 1;
	$now_pos = -1;
	$now_choose = 'thismonth';
	switch($_G['gp_orderby']) {
		case 'thismonth':
			$now_choose = 'thismonth';
			break;
		case 'all':
			$now_choose = 'all';
			break;
		default :
			$_G['gp_orderby'] = 'thismonth';
	}

	$view = $_G['gp_view'];
	$orderby = $_G['gp_orderby'];
	$list = getranklistdata($type, $view, $orderby);

} else {
	$gettype = 'bid';
	$cachetip = FALSE;
	$_G['gp_view'] = 'show';
	$creditid = 0;
	if($_G['setting']['creditstransextra'][6]) {
		$creditid = intval($_G['setting']['creditstransextra'][6]);
		$creditkey = 'extcredits'.$creditid;
	} elseif ($_G['setting']['creditstrans']) {
		$creditid = intval($_G['setting']['creditstrans']);
		$creditkey = 'extcredits'.$creditid;
	}
	$extcredits = $_G['setting']['extcredits'];
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_show')." WHERE credit>0"),0);
	$space = array();
	if($count) {
		$space = $_G['member'];
		space_merge($space, 'count');
		$space['credit'] = empty($creditkey) ? 0 : $space[$creditkey];

		$myshowinfo = DB::fetch_first("SELECT unitprice, credit FROM ".DB::table('home_show')." WHERE uid='$space[uid]' AND credit>0");
		$myallcredit = intval($myshowinfo['credit']);
		$space['unitprice'] = intval($myshowinfo['unitprice']);
		$now_pos = DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_show')." WHERE unitprice>='$space[unitprice]' AND credit>0");

		$deluser = false;
		$query = DB::query("SELECT uid, username, unitprice, credit AS show_credit, note AS show_note FROM ".DB::table('home_show')." ORDER BY unitprice DESC, credit DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if(!$deluser && $value['show_credit'] < 1) {
				$deluser = true;
			} else {
				$list[$value['uid']] = $value;
			}
		}
		if($deluser) {
			DB::query("DELETE FROM ".DB::table('home_show')." WHERE credit<1");
		}
		$multi = multi($count, $perpage, $page, "misc.php?mod=ranklist&type=member&view=$_G[gp_view]");
	}
}

if($cachetip) {
	$lastupdate = $_G['lastupdate'];
	$nextupdate = $_G['nextupdate'];
}

$myfuids =array();
$query = DB::query("SELECT fuid, fusername FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]'");
while ($value = DB::fetch($query)) {
	$myfuids[$value['fuid']] = $value['fuid'];
}
$myfuids[$_G['uid']] = $_G['uid'];

$i = $_G['gp_page'] ? ($_G['gp_page']-1)*$perpage+1 : 1;
foreach($list as $key => $value) {
	$fuids[] = $value['uid'];
	if(isset($value['lastactivity'])) $value['lastactivity'] = dgmdate($value['lastactivity'], 't');
	$value['isfriend'] = empty($myfuids[$value['uid']])?0:1;
	$list[$key] = $value;
	$list[$key]['rank'] = $i;
	$i++;
}

$ols = array();
if($fuids) {
	$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($fuids).")");
	while ($value = DB::fetch($query)) {
		if(!$value['magichidden'] && !$value['invisible']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif ($_G['gp_view'] == 'online' && $list[$value['uid']]) {
			unset($list[$value['uid']]);
		}
	}
}

$a_actives = array($_G['gp_view'] => ' class="a"');

$navname = $_G['setting']['navs'][8]['navname'];
$navtitle = lang('ranklist/navtitle', 'ranklist_title_member_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_member_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_member_'.$gettype);

include template('diy:ranklist/member');

?>