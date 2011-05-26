<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_friend.php 20975 2011-03-09 08:52:58Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 24;
$perpage = mob_perpage($perpage);

$list = $ols = $fuids = array();
$count = 0;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

if(empty($_GET['view']) || $_GET['view'] == 'all') $_GET['view'] = 'me';

ckstart($start, $perpage);

if($_GET['view'] == 'online') {
	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online";
	$actives = array('me'=>' class="a"');

	space_merge($space, 'field_home');
	$wheresql = '';
	if($_GET['type']=='near') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=near";
		$ip = explode('.', $_G['clientip']);
		$wheresql = " WHERE ip1='$ip[0]' AND ip2='$ip[1]' AND ip3='$ip[2]'";
	} elseif($_GET['type']=='friend') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=friend";
		$space['feedfriend'] = !empty($space['feedfriend']) ? $space['feedfriend'] : -1;
		$wheresql = " WHERE uid IN ($space[feedfriend])";
	} elseif($_GET['type']=='member') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=member";
		$wheresql = " WHERE uid>0";
	} else {
		$_GET['type']=='all';
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=all";
		$wheresql = ' WHERE 1';
	}

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_session')." $wheresql"), 0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table("common_session")." $wheresql AND invisible='0' ORDER BY lastactivity DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {

			if($value['magichidden']) {
				$count = $count - 1;
				continue;
			}
			if($_GET['type']=='near') {
				if($value['uid'] == $space['uid']) {
					$count = $count-1;
					continue;
				}
			}

			if(!$value['invisible']) $ols[$value['uid']] = $value['lastactivity'];
			$list[$value['uid']] = $value;
			$fuids[$value['uid']] = $value['uid'];
		}

		if($fuids) {
			require_once libfile('function/friend');
			friend_check($space['uid'], $fuids);

			$query = DB::query("SELECT cm.*, cmfh.* FROM ".DB::table("common_member").' cm
				LEFT JOIN '.DB::table("common_member_field_home")." cmfh ON cmfh.uid=cm.uid
				WHERE cm.uid IN(".dimplode($fuids).")");
			while($value = DB::fetch($query)) {
				$value['isfriend'] = $value['uid']==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$value['uid']] ? 1 : 0;
				$list[$value['uid']] = array_merge($list[$value['uid']], $value);
			}
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'visitor' || $_GET['view'] == 'trace') {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="a"');

	if($_GET['view'] == 'visitor') {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_visitor')." main WHERE main.uid='$space[uid]'"), 0);
		$query = DB::query("SELECT main.vuid AS uid, main.vusername AS username, main.dateline
			FROM ".DB::table('home_visitor')." main
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	} else {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_visitor')." main WHERE main.vuid='$space[uid]'"), 0);
		$query = DB::query("SELECT main.uid AS uid, main.dateline
			FROM ".DB::table('home_visitor')." main
			WHERE main.vuid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	}
	if($count) {
		while ($value = DB::fetch($query)) {
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'blacklist') {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="a"');

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_blacklist')." main WHERE main.uid='$space[uid]'"), 0);
	if($count) {
		$query = DB::query("SELECT s.username, s.groupid, main.dateline, main.buid AS uid
			FROM ".DB::table('home_blacklist')." main
			LEFT JOIN ".DB::table('common_member')." s ON s.uid=main.buid
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['isfriend'] = 0;
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} else {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=$do";
	$actives = array('me'=>' class="a"');

	$_GET['view'] = 'me';

	$wheresql = '';
	if($space['self']) {
		require_once libfile('function/friend');
		$groups = friend_group_list();
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}
	}
	if($_GET['searchkey']) {
		$wheresql = "AND main.fusername LIKE '%$_GET[searchkey]%'";
		$theurl .= "&searchkey=$_GET[searchkey]";
	}

	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend')." main WHERE main.uid='$space[uid]' $wheresql"), 0);
	$friendnum = DB::result_first("SELECT friends FROM ".DB::table('common_member_count')." WHERE uid = '$_G[uid]' LIMIT 1");
	if($count) {

		$query = DB::query("SELECT main.fuid AS uid, main.gid, main.num, main.note FROM ".DB::table('home_friend')." main
			WHERE main.uid='$space[uid]' $wheresql
			ORDER BY main.num DESC, main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$_G["home_friend_".$space['uid'].'_'.$value['uid']] = $value['isfriend'] = 1;
			$fuids[$value['uid']] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	} elseif(!$friendnum) {
		if($specialuser_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_specialuser')." WHERE status = 1 AND uid != '$_G[uid]'")) {
			$query = DB::query("SELECT * FROM ".DB::table('home_specialuser')." WHERE status = 1 AND uid != '$_G[uid]' LIMIT 6");
			while($value = DB::fetch($query)) {
				$fuids[$value['uid']] = $value['uid'];
				$specialuser_list[$value['uid']] = $value;
			}
		}
		if($online_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table("common_session")." WHERE invisible='0' AND uid <> '$_G[uid]' AND uid <> '0'")) {
			$query = DB::query("SELECT * FROM ".DB::table("common_session")." WHERE invisible='0' AND uid <> '$_G[uid]' AND uid <> '0' ORDER BY lastactivity DESC LIMIT 6");
			while($value = DB::fetch($query)) {
				$fuids[$value['uid']] = $value['uid'];
				$oluids[$value['uid']] = $value['uid'];
				$online_list[$value['uid']] = $value;
			}

			$query = DB::query("SELECT cm.*, cmfh.* FROM ".DB::table("common_member").' cm
				LEFT JOIN '.DB::table("common_member_field_home")." cmfh ON cmfh.uid=cm.uid
				WHERE cm.uid IN(".dimplode($oluids).")");
			while($value = DB::fetch($query)) {
				$online_list[$value['uid']] = array_merge($online_list[$value['uid']], $value);
			}

		}
	}

	$diymode = 1;
	if($space['self'] && ($_GET['from'] != 'space' || !$_G['status']['homestatus'])) $diymode = 0;
	if($diymode) {
		$theurl .= "&from=space";
	}

	$multi = multi($count, $perpage, $page, $theurl);

	if($space['self']) {
		$groupselect = array($group => ' class="a"');

		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum) {
			$maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
		}
	}
}

if($fuids) {
	$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($fuids).")");
	while ($value = DB::fetch($query)) {
		if(!$value['magichidden'] && !$value['invisible']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif($list[$value['uid']] && !in_array($_GET['view'], array('me', 'trace', 'blacklist'))) {
			unset($list[$value['uid']]);
			$count = $count - 1;
		}
	}
	if($_GET['view'] != 'me') {
		require_once libfile('function/friend');
		friend_check($fuids);
	}
	if($list) {
		$query = DB::query("SELECT cm.*, cmfh.* FROM ".DB::table("common_member").' cm LEFT JOIN '.DB::table("common_member_field_home")." cmfh ON cmfh.uid=cm.uid WHERE cm.uid IN(".dimplode($fuids).")");
		while($value = DB::fetch($query)) {
			$value['isfriend'] = $value['uid']==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$value['uid']] ? 1 : 0;
			if(empty($list[$value['uid']])) $list[$value['uid']] = array();
			$list[$value['uid']] = array_merge($list[$value['uid']], $value);
		}
	}
}

$navtitle = lang('core', 'title_friend_list');

$navtitle = lang('space', 'sb_friend', array('who' => $space['username']));
$metakeywords = lang('space', 'sb_friend', array('who' => $space['username']));
$metadescription = lang('space', 'sb_share', array('who' => $space['username']));

$a_actives = array($_GET['view'].$_GET['type'] => ' class="a"');
include_once template("diy:home/space_friend");

?>