<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_poll.php 20818 2011-03-04 08:21:11Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$_G['gp_order'] = in_array($_G['gp_order'], array('dateline', 'hot')) ? $_G['gp_order'] : 'dateline';
$opactives['poll'] = 'class="a"';

if(empty($_GET['view'])) $_GET['view'] = 'we';

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'poll',
	'view' => $_GET['view'],
	'order' => $_G['gp_order'],
	'fuid' => $_GET['fuid'],
	'filter' => $_G['gp_filter'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = '1';
$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;

if($_GET['view'] == 'all') {

	$start = 0;
	$perpage = 100;
	$alltype = 'dateline';
	if($_G['gp_order'] == 'hot') {
		$wheresql .= " AND t.replies>='$minhot'";
		$orderactives = array('hot' => ' class="a"');
		$alltype = 'hot';
	} else {
		$orderactives = array('dateline' => ' class="a"');
	}
	loadcache('space_poll');

} elseif($_GET['view'] == 'me') {

	$filter = in_array($_G['gp_filter'], array('publish', 'join')) ? $_G['gp_filter'] : 'publish';
	if($filter == 'join') {
		$wheresql = "p.uid = '$space[uid]' AND p.tid = t.tid";
		$apply_sql = ', '.DB::table('forum_pollvoter').' p ';
	} else {
		$wheresql = "t.authorid = '$space[uid]'";
	}
	$filteractives = array($filter => ' class="a"');

} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql = "t.authorid='$fuid'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql = "t.authorid IN ($space[feedfriend])";
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

	$wheresql .= " AND t.special='1'";

	$wheresql .= $_G['gp_view'] != 'me' ? " AND t.displayorder>='0'" : '';
	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND t.subject LIKE '%$searchkey%'";
		$searchkey = dhtmlspecialchars($searchkey);
	}

	$havecache = false;
	if($_G['gp_view'] == 'all') {

		$cachetime = $_G['gp_order'] == 'hot' ? 43200 : 3000;
		if(!empty($_G['cache']['space_poll'][$alltype]) && is_array($_G['cache']['space_poll'][$alltype])) {
			$pollarr = $_G['cache']['space_poll'][$alltype];
			if(!empty($pollarr['dateline']) && $pollarr['dateline'] > $_G['timestamp'] - $cachetime) {
				$list = $pollarr['data'];
				$hiddennum = $pollarr['hiddennum'];
				$havecache = true;
			}
		}
	}

	if(!$havecache) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_thread')." t $apply_sql WHERE $wheresql"),0);
		if($count) {
			$query = DB::query("SELECT t.* FROM ".DB::table('forum_thread')." t $apply_sql
				WHERE $wheresql
				ORDER BY $ordersql LIMIT $start,$perpage");

			loadcache('forums');
			$tids = array();
			require_once libfile('function/misc');
			while($value = DB::fetch($query)) {
				if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
					$hiddennum++;
					continue;
				}
				$tids[$value['tid']] = $value['tid'];
				$list[$value['tid']] = procthread($value);
			}
			if($tids) {
				$query = DB::query("SELECT * FROM ".DB::table('forum_poll')." WHERE tid IN(".dimplode($tids).")");
				while($value = DB::fetch($query)) {
					$value['pollpreview'] = explode("\t", trim($value['pollpreview']));
					$list[$value['tid']]['poll'] = $value;
				}
			}
			if($_G['gp_view'] == 'all') {
				$_G['cache']['space_poll'][$alltype] = array(
					'dateline' => $_G['timestamp'],
					'hiddennum' => $hiddennum,
					'data' => $list
				);
				save_syscache('space_poll', $_G['cache']['space_poll']);
			}

			if($_G['gp_view'] != 'all') {
				$multi = multi($count, $perpage, $page, $theurl);
			}

		}
	} else {
		$count = count($list);
	}

}

if($_G['uid']) {
	$_G['gp_view'] = !$_G['gp_view'] ? 'we' : $_G['gp_view'];
	$navtitle = lang('core', 'title_'.$_G['gp_view'].'_poll');
} else {
	$_G['gp_order'] = !$_G['gp_order'] ? 'dateline' : $_G['gp_order'];
	$navtitle = lang('core', 'title_'.$_G['gp_order'].'_poll');
}

$actives = array($_GET['view'] => ' class="a"');
include_once template("diy:home/space_poll");

?>