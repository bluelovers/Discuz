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

if(!$ranklist_setting[$type]['available']) {
	showmessage('ranklist_this_status_off');
}
$cache_time = $ranklist_setting[$type]['cache_time'];
$cache_num =  $ranklist_setting[$type]['show_num'];
if($cache_time <= 0 ) $cache_time = 5;
$cache_time = $cache_time * 3600;
if($cache_num <= 0 ) $cache_num = 20;

$multi = '';
$list = array();
$cachetip = TRUE;
$perpage = 20;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;

require_once libfile('function/home');
ckstart($start, $perpage);

$creditkey = $cache_name = '';
$fuids = array();
$count = 0;
$now_pos = 0;
$now_choose = '';

if ($_GET['view'] == 'credit') {

	$creditsrank_change = 1;
	$extcredits = $_G['setting']['extcredits'];
	$now_choose = $_G['gp_orderby'] && $extcredits[$_G['gp_orderby']] ? $_G['gp_orderby'] : 'all';
	$list = getranklistcache_credits();

	if($_G['uid']) {
		$mycredits = $now_choose == 'all' ? $_G['member']['credits'] : $_G['member']['extcredits'.$now_choose];

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

} elseif ($_GET['view'] == 'friendnum') {

	$list = getranklistcache_friendnum();

	if($_G['uid']) {
		$space = $_G['member'];
		space_merge($space, 'count');
		$cookie_name = 'space_top_'.$_GET['view'].'_'.$_G['uid'];
		if($_G['cookie'][$cookie_name]) {
			$now_pos = $_G['cookie'][$cookie_name];
		} else {
			space_merge($space, 'count');
			$pos_sql = "SELECT COUNT(*) FROM ".DB::table('common_member_count')." s WHERE s.friends>'$space[friends]'";
			$now_pos = DB::result(DB::query($pos_sql), 0);
			$now_pos++;
			dsetcookie($cookie_name, $now_pos);
		}
	} else {
		$now_pos = -1;
	}

} elseif($_GET['view'] == 'blog') {

	$list = getranklistcache_blogs();
	$now_pos = -1;

} elseif($_GET['view'] == 'beauty') {

	$list = getranklistcache_beauty();
	$now_pos = -1;

} elseif($_GET['view'] == 'handsome') {

	$list = getranklistcache_handsome();
	$now_pos = -1;

} elseif($_GET['view'] == 'post') {

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
	$list = getranklistcache_posts();

} else {
	$cachetip = FALSE;
	$_GET['view'] = 'show';
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

		$space['unitprice'] = DB::result(DB::query("SELECT unitprice FROM ".DB::table('home_show')." WHERE uid='$space[uid]' AND credit>0"));
		$space['unitprice'] = intval($space['unitprice']);
		$now_pos = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_show')." WHERE unitprice>='$space[unitprice]' AND credit>0"), 0);

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
		$multi = multi($count, $perpage, $page, "misc.php?mod=ranklist&type=member&view=$_GET[view]");
	}
}

if($cachetip) {
	$lastupdate = $list['lastupdate'];
	$nextupdate = $list['nextupdate'];
	unset($list['lastupdated'], $list['lastupdate'], $list['nextupdate']);
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
if($fuids && $_GET['view'] != 'online') {
	$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($fuids).")");
	while ($value = DB::fetch($query)) {
		if(!$value['magichidden']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif ($_GET['view'] == 'online' && $list[$value['uid']]) {
			unset($list[$value['uid']]);
		}
	}
}

$a_actives = array($_GET['view'] => ' class="a"');

include template('diy:ranklist/member');

function getranklistcache_credits() {
	global $_G, $cache_time, $cache_num, $now_choose;
	$ranklistvars = array();
	loadcache('ranklist_member_credit');
	$ranklistvars = & $_G['cache']['ranklist_member_credit'][$now_choose];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_member_credits($now_choose, $cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_member_credit'][$now_choose] = $ranklistvars;
	save_syscache('ranklist_member_credit', $_G['cache']['ranklist_member_credit']);
	return $ranklistvars;
}

function getranklistcache_friendnum() {
	global $_G, $cache_time, $cache_num;
	$ranklistvars = array();
	loadcache('ranklist_member_friendnum');
	$ranklistvars = & $_G['cache']['ranklist_member_friendnum'];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_member_friendnum($cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_member_friendnum'] = $ranklistvars;
	save_syscache('ranklist_member_friendnum', $_G['cache']['ranklist_member_friendnum']);
	return $ranklistvars;
}

function getranklistcache_blogs() {
	global $_G, $cache_time, $cache_num;
	$ranklistvars = array();
	loadcache('ranklist_member_blog');
	$ranklistvars = & $_G['cache']['ranklist_member_blog'];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_member_blogs($cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_member_blog'] = $ranklistvars;
	save_syscache('ranklist_member_blog', $_G['cache']['ranklist_member_blog']);
	return $ranklistvars;
}

function getranklistcache_beauty() {
	global $_G, $cache_time, $cache_num;
	$ranklistvars = array();
	loadcache('ranklist_member_beauty');
	$ranklistvars = & $_G['cache']['ranklist_member_beauty'];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_member_beauty($cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_member_beauty'] = $ranklistvars;
	save_syscache('ranklist_member_beauty', $_G['cache']['ranklist_member_beauty']);
	return $ranklistvars;
}

function getranklistcache_handsome() {
	global $_G, $cache_time, $cache_num;
	$ranklistvars = array();
	loadcache('ranklist_member_handsome');
	$ranklistvars = & $_G['cache']['ranklist_member_handsome'];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_member_handsome($cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_member_handsome'] = $ranklistvars;
	save_syscache('ranklist_member_handsome', $_G['cache']['ranklist_member_handsome']);
	return $ranklistvars;
}

function getranklistcache_posts() {
	global $_G, $cache_time, $cache_num, $now_choose;
	$ranklistvars = array();
	loadcache('ranklist_member_post');
	$ranklistvars = & $_G['cache']['ranklist_member_post'][$now_choose];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_member_posts($now_choose, $cache_num);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_member_post'][$now_choose] = $ranklistvars;
	save_syscache('ranklist_member_post', $_G['cache']['ranklist_member_post']);
	return $ranklistvars;
}

?>