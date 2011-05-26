<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_pm.php 21555 2011-03-31 06:04:49Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loaducenter();

$list = array();

$plid = empty($_GET['plid'])?0:intval($_GET['plid']);
$daterange = empty($_GET['daterange'])?0:intval($_GET['daterange']);
$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
$opactives['pm'] = 'class="a"';

if($_GET['subop'] == 'view') {
	$type = $_GET['type'];
	$page = empty($_GET['page']) ? 0 : intval($_GET['page']);

	$chatpmmember = intval($_GET['chatpmmember']);
	$chatpmmemberlist = array();
	if($chatpmmember) {
		$chatpmmember = uc_pm_chatpmmemberlist($_G['uid'], $plid);
		if(!empty($chatpmmember)) {
			$authorid = $founderuid = $chatpmmember['author'];
			$query = DB::query("SELECT m.uid, m.username, mfh.recentnote FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_field_home')." mfh ON m.uid=mfh.uid WHERE m.uid IN (".dimplode($chatpmmember['member']).")");
			while($member = DB::fetch($query)) {
				$chatpmmemberlist[$member['uid']] = $member;
			}
		}
		require_once libfile('function/friend');
		$friendgrouplist = friend_group_list();
		$actives = array('chatpmmember'=>' class="a"');
	} else {
		if($touid) {
			$ols = array();
			if(defined('IN_MOBILE')) {
				$perpage = 5;
			} else {
				$perpage = 10;
			}
			$perpage = mob_perpage($perpage);
			if(!$daterange) {
				$tousername = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$touid'");
				$count = uc_pm_view_num($_G['uid'], $touid, 0);
				if(!$page) {
					$page = ceil($count/$perpage);
				}
				$list = uc_pm_view($_G['uid'], 0, $touid, 5, ceil($count/$perpage)-$page+1, $perpage, 0, 0);
				$multi = pmmulti($count, $perpage, $page, "home.php?mod=space&do=pm&subop=view&touid=$touid");
			} else {
				showmessage('parameters_error');
			}
		} else {
			if(defined('IN_MOBILE')) {
				$perpage = 10;
			} else {
				$perpage = 50;
			}
			$perpage = mob_perpage($perpage);
			if(!$daterange) {
				$count = uc_pm_view_num($_G['uid'], $plid, 1);
				if(!$page) {
					$page = ceil($count/$perpage);
				}
				$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count/$perpage)-$page+1, $perpage, $type, 1);
			} else {
				$list = uc_pm_view($_G['uid'], 0, $plid, 5, ceil($count/$perpage)-$page+1, $perpage, $type, 1);
				$chatpmmember = uc_pm_chatpmmemberlist($_G['uid'], $plid);
				if(!empty($chatpmmember)) {
					$authorid = $founderuid = $chatpmmember['author'];
					$query = DB::query("SELECT m.uid, m.username, mfh.recentnote FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_field_home')." mfh ON m.uid=mfh.uid WHERE m.uid IN (".dimplode($chatpmmember['member']).")");
					while($member = DB::fetch($query)) {
						$chatpmmemberlist[$member['uid']] = $member;
					}
					$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($chatpmmember['member']).")");
					while ($value = DB::fetch($query)) {
						if(!$value['magichidden'] && !$value['invisible']) {
							$ols[$value['uid']] = $value['lastactivity'];
						}
					}
				}
				$membernum = count($chatpmmemberlist);
				$subject = $list[0]['subject'];
				$refreshtime = $_G['setting']['chatpmrefreshtime'];
				$multi = pmmulti($count, $perpage, $page, "home.php?mod=space&do=pm&subop=view&plid=$plid&type=$type");

			}
		}
		$founderuid = empty($list)?0:$list[0]['founderuid'];
		$pmid = empty($list)?0:$list[0]['pmid'];
	}
	$actives['privatepm'] = ' class="a"';

} elseif($_GET['subop'] == 'viewg') {

	$grouppm = DB::fetch_first("SELECT mgp.status, gp.* FROM ".DB::table("common_grouppm")." gp
		INNER JOIN ".DB::table('common_member_grouppm')." mgp ON gp.id=mgp.gpmid WHERE gp.id='$_G[gp_pmid]' AND mgp.uid='$_G[uid]' AND mgp.status>='0' ORDER BY gp.dateline DESC");
	if($grouppm) {
		$grouppm['numbers'] = $grouppm['numbers'] - 1;
	}
	if(!$grouppm['status']) {
		DB::update('common_member_grouppm', array('status' => 1, 'dateline' => TIMESTAMP), "gpmid='$_G[gp_pmid]' AND uid='$_G[uid]'");
	}
	$actives['announcepm'] = ' class="a"';

} elseif($_GET['subop'] == 'ignore') {

	$ignorelist = uc_pm_blackls_get($_G['uid']);
	$actives = array('ignore'=>' class="a"');

} elseif($_GET['subop'] == 'setting') {

	$actives = array('setting'=>' class="a"');
	$acceptfriendpmstatus = $_G['member']['onlyacceptfriendpm'] ? $_G['member']['onlyacceptfriendpm'] : ($_G['setting']['onlyacceptfriendpm'] ? 1 : 2);
	$ignorelist = uc_pm_blackls_get($_G['uid']);

} else {

	$filter = in_array($_GET['filter'], array('newpm', 'privatepm', 'announcepm')) ? $_GET['filter'] : 'privatepm';

	$perpage = 15;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;

	$grouppms = $gpmids = $gpmstatus = array();
	$newpm = 0;

	if($filter == 'privatepm' && $page == 1 || $filter == 'announcepm') {
		$status = $filter == 'announcepm' ? "`status`>='0'" : "`status`='0'";
		$query = DB::query("SELECT gpmid, status FROM ".DB::table("common_member_grouppm")." WHERE uid='$_G[uid]' AND $status");
		while($gpuser = DB::fetch($query)) {
			$gpmids[] = $gpuser['gpmid'];
			$gpmstatus[$gpuser['gpmid']] = $gpuser['status'];
		}
		if($gpmids) {
			$query = DB::query("SELECT * FROM ".DB::table("common_grouppm")." WHERE id IN (".dimplode($gpmids).") ORDER BY id DESC");
			while($grouppm = DB::fetch($query)) {
				$grouppm['message'] = cutstr(strip_tags($grouppm['message']), 100, '');
				$grouppms[] = $grouppm;
			}
		}
	}

	if($filter == 'privatepm' || $filter == 'newpm') {
		$result = uc_pm_list($_G['uid'], $page, $perpage, 'inbox', $filter, 200);
		$count = $result['count'];
		$list = $result['data'];
	}

	if($filter == 'privatepm' && $page == 1) {
		$newpmarr = uc_pm_checknew($_G['uid'], 1);
		$newpm = $newpmarr['newpm'];
	}

	if($_G['member']['newpm']) {
		DB::update('common_member', array('newpm' => 0), array('uid' => $_G['uid']));
		uc_pm_ignore($_G['uid']);
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=space&do=pm&filter=$filter");
	$actives = array($filter=>' class="a"');
}

if(!empty($list)) {
	$today = $_G['timestamp'] - ($_G['timestamp'] + $_G['setting']['timeoffset'] * 3600) % 86400;
	foreach ($list as $key => $value) {
		$value['lastsummary'] = str_replace('&amp;', '&', $value['lastsummary']);
		$value['lastsummary'] = preg_replace("/&[a-z]+\;/i", '', $value['lastsummary']);
		$value['daterange'] = 5;
		if($value['lastdateline'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['lastdateline'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['lastdateline'] >= $today - 172800) {
			$value['daterange'] = 3;
		} elseif($value['lastdateline'] >= $today - 604800) {
			$value['daterange'] = 4;
		}
		$list[$key] = $value;
	}
}

include_once template("diy:home/space_pm");

function pmmulti($count, $perpage, $curpage, $mpurl) {
	$return = '';
	$lang['next'] = lang('core', 'nextpage');
	$lang['prev'] = lang('core', 'prevpage');
	$next = $curpage < ceil($count/$perpage) ? '<a href="'.$mpurl.'&amp;page='.($curpage + 1).'#last" class="nxt">'.$lang['next'].'</a>' : '';
	$prev = $curpage > 1 ? '<span class="pgb"><a href="'.$mpurl.'&amp;page='.($curpage - 1).'#last">'.$lang['prev'].'</a></span>' : '';
	if($next || $prev) {
		$return = '<div class="pg">'.$prev.$next.'</div>';
	}
	return $return;
}

?>