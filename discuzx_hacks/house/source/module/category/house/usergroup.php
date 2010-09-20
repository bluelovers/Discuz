<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: house_index.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$gid = intval($_G['gp_gid']);
$actionarray = array('list', 'member');
$action = $_G['gp_action'] && in_array($_G['gp_action'], $actionarray) ? $_G['gp_action'] : 'list';
$districtlist = array();

if($arealist) {
	foreach($arealist['district'] as $districtarray) {
		foreach($districtarray as $did => $district) {
			$districtlist[$did] = $district;
		}
	}
}

require_once libfile('function/category');

$_G['category_threadlist'] = $threadids = $memberlist = array();
$isgroupadmin = 0;

if(!empty($gid)) {

	$usergroup = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_usergroup')." WHERE gid='$gid'");
	$usergroup['banner'] = $usergroup['banner'] ? get_logoimg($usergroup['banner']) : '';
	$navtitle = $usergroup['title'].' - ';

	$query = DB::query("SELECT cm.threads, m.uid, m.username FROM ".DB::table('category_'.$modidentifier.'_member')." cm
			LEFT JOIN ".DB::table('common_member')." m ON cm.uid=m.uid
			WHERE cm.groupid='$gid' ORDER BY cm.threads DESC LIMIT 5");
	while($member = DB::fetch($query)) {
		$memberlist[$member['uid']]['username'] = $member['username'];
		$memberlist[$member['uid']]['avatar'] = category_uc_avatar($member['uid'], 'small');
		$memberlist[$member['uid']]['threads'] = $member['threads'];
	}

	$isgroupadmin = $_G['uid'] == $usergroup['manageuid'] || $channel['managegid'][$_G['groupid']] ? 1 : 0;

	if($action == 'list') {
		loadcache(array('category_option_'.$sortid, 'category_template_'.$sortid));
		$sortoptionarray = $_G['cache']['category_option_'.$sortid];
		$templatearray = $_G['cache']['category_template_'.$sortid]['subject'];
		$rtemplatearray = $_G['cache']['category_template_'.$sortid]['recommend'];
		$recommendlist = recommendsort($sortid, $sortoptionarray, $gid, $rtemplatearray, $districtlist, $modurl);

		$page = $_G['page'];
		$start_limit = ($page - 1) * $_G['tpp'];

		$sortcondition['orderby'] = 'dateline';
		$sortcondition['ascdesc'] = 'DESC';

		$selectadd = array('groupid' => $gid);
		$sortdata = sortsearch($_G['gp_sortid'], $sortoptionarray, $_G['gp_searchoption'], $selectadd, $sortcondition, $start_limit, $_G['tpp']);
		$tidsadd = $sortdata['tids'] ? "tid IN (".dimplode($sortdata['tids']).")" : '';
		$_G['category_threadcount'] = $sortdata['count'];

		$multipage = multi($_G['category_threadcount'], $_G['tpp'], $page, "$modurl?mod=usergroup&action=list&sortid=$sortid&gid=$gid&cid=$cid");

		$_G['category_threadlist'] = $sortdata['datalist'];

		$query = DB::query("SELECT * FROM ".DB::table('category_'.$modidentifier.'_thread')." ".($tidsadd ? 'WHERE '.$tidsadd : '')."");
		while($thread = DB::fetch($query)) {
			$_G['category_threadlist'][$thread['tid']]['subject'] .= $thread['subject'];
			$_G['category_threadlist'][$thread['tid']]['author'] .= $thread['author'];
			$_G['category_threadlist'][$thread['tid']]['authorid'] .= $thread['authorid'];
		}

		$sortlistarray = showsorttemplate($sortid, $sortoptionarray, $templatearray, $_G['category_threadlist'], $threadids, $arealist, $modurl);
		$stemplate = $sortlistarray['template'];
		$sortexpiration = $sortlistarray['expiration'];
	}


} else {
	$usergrouplist = array();
	$query = DB::query("SELECT * FROM ".DB::table('category_'.$modidentifier.'_usergroup')." ORDER BY displayorder ");
	while($group = DB::fetch($query)) {
		$usergrouplist[$group['gid']]['banner'] = $group['banner'] ? $_G['setting']['attachurl'].'common/'.$group['banner'] : '';
		$usergrouplist[$group['gid']]['title'] = $group['title'];
		$usergrouplist[$group['gid']]['type'] = $group['type'];
	}
}

include template('diy:category/'.$modidentifier.'_usergroup:'.$gid);

?>