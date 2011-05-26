<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_debate.php 20818 2011-03-04 08:21:11Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$opactives['debate'] = 'class="a"';

if(empty($_GET['view'])) $_GET['view'] = 'we';
$_GET['order'] = empty($_GET['order']) ? 'dateline' : $_GET['order'];
$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = $userlist = array();
$count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'debate',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'type' => $_GET['type'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
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
	$alltype = 'dateline';
	if($_GET['order'] == 'hot') {
		$wheresql = "t.replies>='$minhot'";
		$alltype = 'hot';
	}
	$orderactives = array($_GET['order'] => ' class="a"');
	loadcache('space_debate');

} elseif($_GET['view'] == 'me') {

	if($_GET['type'] == 'reply') {
		$wheresql = "p.authorid = '$space[uid]' AND p.first='0' AND p.tid = t.tid";
		$posttable = getposttable();
		$apply_sql = ', '.DB::table($posttable).' p ';
	} else {
		$wheresql = "t.authorid = '$space[uid]'";
	}
	$viewtype = in_array($_G['gp_type'], array('orig', 'reply')) ? $_G['gp_type'] : 'orig';
	$typeactives = array($viewtype => ' class="a"');

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

	$wheresql .= " AND t.special='5'";
	$wheresql .= $_G['gp_view'] != 'me' ? " AND t.displayorder>='0'" : '';
	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND t.subject LIKE '%$searchkey%'";
		$searchkey = dhtmlspecialchars($searchkey);
	}

	$havecache = false;
	if($_G['gp_view'] == 'all') {

		$cachetime = $_G['gp_order'] == 'hot' ? 43200 : 3000;
		if(!empty($_G['cache']['space_debate'][$alltype]) && is_array($_G['cache']['space_debate'][$alltype])) {
			$cachearr = $_G['cache']['space_debate'][$alltype];
			if(!empty($cachearr['dateline']) && $cachearr['dateline'] > $_G['timestamp'] - $cachetime) {
				$list = $cachearr['data'];
				$havecache = true;
			}
		}
	}

	if(!$havecache) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_thread')." t $apply_sql WHERE $wheresql"),0);
		if($count) {
			$field = $apply_sql ? ', p.message' : '';
			$query = DB::query("SELECT t.* $field FROM ".DB::table('forum_thread')." t $apply_sql
				WHERE $wheresql
				ORDER BY $ordersql LIMIT $start,$perpage");

			$dids = $special = $multitable = $tids = array();
			require_once libfile('function/post');
			while($value = DB::fetch($query)) {
				$value['dateline'] = dgmdate($value['dateline']);
				if($_GET['view'] == 'me' && $_GET['type'] == 'reply' && $page == 1 && count($special) < 2) {
					$value['message'] = messagecutstr($value['message'], 200);
					$special[$value['tid']] = $value;
				} else {
					if($page == 1 && count($special) < 2) {
						$tids[$value['posttableid']][$value['tid']] = $value['tid'];
						$special[$value['tid']] = $value;
					} else {
						$list[$value['tid']] = $value;
					}
				}
				$dids[$value['tid']] = $value['tid'];
			}
			if($tids) {
				foreach($tids as $postid => $tid) {
					$posttable = getposttable();
					$query = DB::query("SELECT tid, message FROM ".DB::table($posttable)." WHERE tid IN(".dimplode($tid).")");
					while($value = DB::fetch($query)) {
						$special[$value['tid']]['message'] = messagecutstr($value['message'], 200);
					}
				}
			}
			if($dids) {
				$query = DB::query("SELECT * FROM ".DB::table('forum_debate')." WHERE tid IN(".dimplode($dids).")");
				while($value = DB::fetch($query)) {
					$value['negavotesheight'] = $value['affirmvotesheight'] = '8px';
					if($value['affirmvotes'] || $value['negavotes']) {
						$allvotes = $value['affirmvotes'] + $value['negavotes'];
						$value['negavotesheight'] = round($value['negavotes']/$allvotes * 100, 2).'%';
						$value['affirmvotesheight'] = round($value['affirmvotes']/$allvotes * 100, 2).'%';
					}
					if($list[$value['tid']]) {
						$list[$value['tid']] = array_merge($value, $list[$value['tid']]);
					} elseif($special[$value['tid']]) {
						$special[$value['tid']] = array_merge($value, $special[$value['tid']]);
					}
				}
			}

			if($_G['gp_view'] == 'all') {
				$_G['cache']['space_debate'][$alltype] = array(
					'dateline' => $_G['timestamp'],
					'data' => $list
				);
				save_syscache('space_debate', $_G['cache']['space_debate']);
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
	if($_G['gp_view'] == 'all') {
		$navtitle = lang('core', 'title_view_all').lang('core', 'title_debate');
	} elseif($_G['gp_view'] == 'me') {
		$navtitle = lang('core', 'title_my_debate');
	} else {
		$navtitle = lang('core', 'title_friend_debate');
	}
} else {
	if($_G['gp_order'] == 'hot') {
		$navtitle = lang('core', 'title_top_debate');
	} else {
		$navtitle = lang('core', 'title_newest_debate');
	}
}

include_once template("diy:home/space_debate");

?>