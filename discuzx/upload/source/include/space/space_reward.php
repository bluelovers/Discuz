<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_reward.php 25327 2011-11-07 07:27:20Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$_G['gp_flag'] = empty($_G['gp_flag']) ? 0 : intval($_G['gp_flag']);
$_GET['fuid'] = empty($_GET['fuid']) ? 0 : intval($_GET['fuid']);
$opactives['reward'] = 'class="a"';

$_GET['view'] = in_array($_GET['view'], array('we', 'me', 'all')) ? $_GET['view'] : 'we';

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = $userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'reward',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'flag' => $_GET['flag'],
	'type' => $_GET['type'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = "t.special='3'";

$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;
require_once libfile('function/misc');
if($_GET['view'] == 'all') {
	$start = 0;
	$perpage = 100;

	$alltype = $ordertype = in_array($_G['gp_order'], array('new', 'hot')) ? $_G['gp_order'] : 'new';
	if($_GET['order'] == 'hot') {
		$wheresql .= " AND t.replies>='$minhot'";
	}
	$orderactives = array($ordertype => ' class="a"');
	loadcache('space_reward');
} elseif($_GET['view'] == 'me') {
	$wheresql = "t.authorid = '$space[uid]' AND t.special='3'";
} else {

	space_merge($space, 'field_home');
	if($space['feedfriend']) {
		$fuid_actives = array();
		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql = "t.authorid='$fuid' AND t.special='3'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql .= " AND t.authorid IN ($space[feedfriend])";
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

	$wheresql .= $_G['gp_view'] != 'me' ? " AND t.displayorder>='0'" : '';
	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND t.subject LIKE '%$searchkey%'";
		$searchkey = dhtmlspecialchars($searchkey);
	}

	if($_G['gp_flag'] < 0) {
		$wheresql .= " AND t.price < '0'";
		$alltype .= '1';
	} elseif($_G['gp_flag'] > 0) {
		$wheresql .= " AND t.price > '0'";
		$alltype .= '0';
	}

	$havecache = false;
	if($_G['gp_view'] == 'all') {

		$cachetime = $_G['gp_order'] == 'hot' ? 43200 : 3000;
		if(!empty($_G['cache']['space_reward'][$alltype]) && is_array($_G['cache']['space_reward'][$alltype])) {
			$cachearr = $_G['cache']['space_reward'][$alltype];
			if(!empty($cachearr['dateline']) && $cachearr['dateline'] > $_G['timestamp'] - $cachetime) {
				$list = $cachearr['data'];
				$hiddennum = $cachearr['hiddennum'];
				$havecache = true;
			}
		}
	}
	if(!$havecache) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_thread')." t WHERE $wheresql"),0);
		if($count) {
			$query = DB::query("SELECT t.* FROM ".DB::table('forum_thread')." t
				WHERE $wheresql
				ORDER BY $ordersql LIMIT $start,$perpage");

			while($value = DB::fetch($query)) {
				if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
					$hiddennum++;
					continue;
				}
				$list[] = procthread($value);
			}

			if($_G['gp_view'] == 'all') {
				$_G['cache']['space_reward'][$alltype] = array(
					'dateline' => $_G['timestamp'],
					'hiddennum' => $hiddennum,
					'data' => $list
				);
				save_syscache('space_reward', $_G['cache']['space_reward']);
			}

			if($_G['gp_view'] != 'all') {
				$multi = multi($count, $perpage, $page, $theurl);
			}

		}
	} else {
		$count = count($list);
	}
}

$creditid = 0;
if($_G['setting']['creditstransextra'][2]) {
	$creditid = intval($_G['setting']['creditstransextra'][2]);
} elseif ($_G['setting']['creditstrans']) {
	$creditid = intval($_G['setting']['creditstrans']);
}

if($_G['uid']) {
	$_G['gp_view'] = !$_G['gp_view'] ? 'we' : $_G['gp_view'];
	$navtitle = lang('core', 'title_'.$_G['gp_view'].'_reward');
} else {
	$_G['gp_order'] = !$_G['gp_order'] ? 'dateline' : $_G['gp_order'];
	$navtitle = lang('core', 'title_'.$_G['gp_order'].'_reward');
}

include_once template("diy:home/space_reward");

?>