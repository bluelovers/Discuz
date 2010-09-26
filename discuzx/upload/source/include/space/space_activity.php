<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_activity.php 16887 2010-09-16 06:50:12Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

//if(empty($_GET['view'])) $_GET['view'] = 'we';

// bluelovers
if(empty($_GET['view'])) {
	space_merge($space, 'field_home');

	if($space['feedfriend']) {
		$_GET['view'] = $_G['gp_view'] = 'we';
	} else {
		$_GET['view'] = $_G['gp_view'] = 'all';
	}
}
// bluelovers

$_GET['order'] = empty($_GET['order']) ? 'dateline' : $_GET['order'];

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'activity',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'type' => $_GET['type'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = '1';
$threadsql = $apply_sql = '';

$f_index = '';
$need_count = true;
require_once libfile('function/misc');
if($_GET['view'] == 'all') {
	if($_GET['order'] == 'hot') {
		$threadsql .= " t.special='4' AND t.replies>='$minhot'";
		$apply_sql = "INNER JOIN ".DB::table('forum_thread')." t ON t.special='4' AND t.tid = a.tid AND t.replies>='$minhot' AND t.displayorder>'-1'";
	}
	$orderactives = array($_GET['order'] => ' class="a"');
} elseif($_GET['view'] == 'me') {
	$viewtype = in_array($_G['gp_type'], array('orig', 'apply')) ? $_G['gp_type'] : 'orig';
	if($_GET['type'] == 'apply') {
		$wheresql = "1";
		$apply_sql = "INNER JOIN ".DB::table('forum_activityapply')." apply ON apply.uid = '$space[uid]' AND apply.tid = a.tid";
	} else {
		$wheresql = "a.uid = '$space[uid]'";
	}
	$orderactives = array($viewtype => ' class="a"');
} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql = "a.uid='$fuid'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql = "a.uid IN ($space[feedfriend])";
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

	$today = strtotime(dgmdate($_G['timestamp'], 'Y-m-d'));
	$order = '';
	if($_G['gp_view'] != 'all') {
		$wheresql .= " AND a.starttimefrom >'$today'";
	} elseif(empty($_G['gp_order'])) {
		$order = 'DESC';
	}

	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$threadsql .= " AND t.subject LIKE '%$searchkey%'";
	}
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_activity')." a $apply_sql WHERE $wheresql"),0);
	if($count) {
		if($_GET['view'] == 'all' && $_GET['order'] == 'hot') {
			$apply_sql = '';
		}
		$threadsql = empty($threadsql) ? '' : $threadsql.' AND ';
		$query = DB::query("SELECT a.*, t.* FROM ".DB::table('forum_activity')." a $apply_sql
			INNER JOIN ".DB::table('forum_thread')." t ON $threadsql t.tid=a.tid
			WHERE t.displayorder>'-1' AND $wheresql
			ORDER BY a.starttimefrom $order LIMIT $start, $perpage");
	}
}

if($count) {
	loadcache('forums');
	$daytids = $tids = array();

	while ($value = DB::fetch($query)) {
		if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
			$hiddennum++;
			continue;
		}
		$date = dgmdate($value['starttimefrom'], 'Ymd');
		$tids[$value['tid']] = $value['tid'];
		$value['week'] = dgmdate($value['starttimefrom'], 'w');
		$value['month'] = dgmdate($value['starttimefrom'], 'n'.lang('space', 'month'));
		$value['day'] = dgmdate($value['starttimefrom'], 'j');
		$value['time'] = dgmdate($value['starttimefrom'], 'Y'.lang('space', 'year').'m'.lang('space', 'month').'d'.lang('space', 'day'));
		$value['starttimefrom'] = dgmdate($value['starttimefrom']);

		$daytids[$value['tid']] = $date;
		$list[$date][$value['tid']] = procthread($value);
	}
	if($tids) {
		require_once libfile('function/post');
		$activitylist = getfieldsofposts('tid, pid, message, dateline', "tid IN (".dimplode($tids).") AND first='1'");
		foreach($activitylist as $value) {
			$date = $daytids[$value['tid']];
			$value['message'] = messagecutstr($value['message'], 150);
			$list[$date][$value['tid']]['message'] = $value['message'];
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);
}

if($_G['uid']) {
	if($_G['gp_view'] == 'all') {
		$navtitle = lang('core', 'title_view_all').lang('core', 'title_activity');
	} elseif($_G['gp_view'] == 'me') {
		$navtitle = lang('core', 'title_my_activity');
	} else {
		$navtitle = lang('core', 'title_friend_activity');
	}
} else {
	if($_G['gp_order'] == 'hot') {
		$navtitle = lang('core', 'title_top_activity');
	} else {
		$navtitle = lang('core', 'title_newest_activity');
	}
}

include_once template("diy:home/space_activity");

?>