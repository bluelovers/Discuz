<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_index.php 20714 2011-03-02 07:02:17Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = '';

$gid = intval(getgpc('gid'));
$sgid = intval(getgpc('sgid'));
$groupids = $groupnav = $typelist = '';
$selectorder = array('default' => '', 'thread' => '', 'membernum' => '', 'dateline' => '', 'activity' => '');
if(!empty($_G['gp_orderby'])) {
	$selectorder[$_G['gp_orderby']] = 'selected';
} else {
	$selectorder['default'] = 'selected';
}
$first = &$_G['cache']['grouptype']['first'];
$second = &$_G['cache']['grouptype']['second'];
require_once libfile('function/group');
$url = $_G['basescript'].'.php';

if($gid) {
	if(!empty($first[$gid])) {
		$curtype = $first[$gid];
		foreach($curtype['secondlist'] as $fid) {
			$typelist[$fid] = $second[$fid];
		}
		$groupids = $first[$gid]['secondlist'];
		$url .= '?gid='.$gid;
		$fup = $gid;
	} else {
		$gid = 0;
	}
} elseif($sgid) {
	if(!empty($second[$sgid])) {
		$curtype = $second[$sgid];
		$fup = $curtype['fup'];
		$groupids = array($sgid);
		$url .= '?sgid='.$sgid;
	} else {
		$sgid = 0;
	}
}

if(empty($curtype)) {
	if($_G['uid'] && empty($_G['mod'])) {
		$usergroups = getuserprofile('groups');
		if(!empty($usergroups)) {
			dheader('Location:group.php?mod=my');
			exit;
		}
	}
	$curtype = array();

} else {
	$nav = get_groupnav($curtype);
	$groupnav = $nav['nav'];
	$_G['grouptypeid'] = $curtype['fid'];
	$perpage = 10;
	if($curtype['forumcolumns'] > 1) {
		$curtype['forumcolwidth'] = floor(99 / $curtype['forumcolumns']).'%';
		$perpage = $curtype['forumcolumns'] * 10;
	}
}
$seodata = array('first' => $nav['first']['name'], 'second' => $nav['second']['name']);
list($navtitle, $metadescription, $metakeywords) = get_seosetting('group', $seodata);


$data = $randgrouplist = $randgroupdata = $grouptop = $newgrouplist = array();
$topgrouplist = $_G['cache']['groupindex']['topgrouplist'];
$lastupdategroup = $_G['cache']['groupindex']['lastupdategroup'];
$todayposts = intval($_G['cache']['groupindex']['todayposts']);
$groupnum = intval($_G['cache']['groupindex']['groupnum']);
$cachetimeupdate = TIMESTAMP - intval($_G['cache']['groupindex']['updateline']);

if(empty($_G['cache']['groupindex']) || $cachetimeupdate > 3600 || empty($lastupdategroup)) {
	$data['randgroupdata'] = $randgroupdata = grouplist('lastupdate', array('ff.membernum', 'ff.icon'), 80);
	$data['topgrouplist'] = $topgrouplist = grouplist('activity', array('f.commoncredits', 'ff.membernum', 'ff.icon'), 10);
	$data['updateline'] = TIMESTAMP;
	$groupdata = DB::fetch_first("SELECT SUM(todayposts) AS todayposts, COUNT(fid) AS groupnum FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub'");
	$data['todayposts'] = $todayposts = $groupdata['todayposts'];
	$data['groupnum'] = $groupnum = $groupdata['groupnum'];
	foreach($first as $id => $toptype) {
		if($toptype['secondlist']) {
			$query = DB::query("SELECT fid, name FROM ".DB::table('forum_forum')." WHERE fup IN(".dimplode($toptype['secondlist']).") ORDER BY commoncredits DESC LIMIT 20");
			while($row = DB::fetch($query)) {
				$data['lastupdategroup'][$id][] = $row;
			}
		}
		if(empty($data['lastupdategroup'][$id])) $data['lastupdategroup'][$id] = array();
	}
	$lastupdategroup = $data['lastupdategroup'];
	save_syscache('groupindex', $data);
}

$list = array();
if($groupids) {
	$orderby = in_array(getgpc('orderby'), array('membernum', 'dateline', 'thread', 'activity')) ? getgpc('orderby') : 'displayorder';
	$page = intval(getgpc('page')) ? intval($_G['gp_page']) : 1;
	$start = ($page - 1) * $perpage;
	$getcount = grouplist('', '', '', $groupids, 1, 1);
	if($getcount) {
		$list = grouplist($orderby, '', array($start, $perpage), $groupids, 1);
		$multipage = multi($getcount, $perpage, $page, $url."&orderby=$orderby");
	}

}

$groupviewed_list = get_viewedgroup();

if(empty($sgid) && empty($gid)) {
	foreach($first as $key => $val) {
		if(is_array($val['secondlist']) && !empty($val['secondlist'])) {
			$first[$key]['secondlist'] = array_slice($val['secondlist'], 0, 8);
		}
	}
}

if(!$navtitle || !empty($sgid) || !empty($gid)) {
	if(!$navtitle) {
		$navtitle = !empty($gid) ? $nav['first']['name'] : (!empty($sgid) ? $nav['second']['name'] : '');
	}
	$navtitle = (!empty($sgid) || !empty($gid) ? get_title_page($navtitle, $_G['page']).' - ' : '').$_G['setting']['navs'][3]['navname'];
	$nobbname = false;
} else {
	$nobbname = true;
}

if(!$metakeywords) {
	$metakeywords = $_G['setting']['navs'][3]['navname'];
}
if(!$metadescription) {
	$metadescription = $_G['setting']['navs'][3]['navname'];
}
if(empty($curtype)) {
	include template('diy:group/index');
} else {
	if(empty($sgid)) {
		include template('diy:group/type:'.$gid);
	} else {
		include template('diy:group/type:'.$fup);
	}
}


?>