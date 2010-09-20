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

$uid = !empty($_G['gp_uid']) ? intval($_G['gp_uid']) : $_G['uid'];

if(empty($sortid)) {
	showmessage(lang('category/template', 'house_lack_args'));
}

$isgroupadmin = $_G['uid'] == $uid ? 1 : 0;
$usergroup = $category_usergroup;

$member = DB::fetch_first("SELECT cm.threads, cm.groupid, m.uid, m.username FROM ".DB::table('category_'.$modidentifier.'_member')." cm
	LEFT JOIN ".DB::table('common_member')." m ON cm.uid=m.uid WHERE cm.uid='$uid'");

if(empty($member['uid'])) {
	showmessage(lang('category/template', 'house_class_nothing'));
}

require_once libfile('function/category');

$usergroupid = $member['groupid'];
$username = $member['username'];
$avatar = category_uc_avatar($member['uid']);
$usergrouplist[$usergroupid]['icon'] = $usergrouplist[$usergroupid]['icon'] ? $_G['setting']['attachurl'].'common/'.$usergrouplist[$usergroupid]['icon'] : '';
$usergrouplist[$usergroupid]['postdayper'] = $usergrouplist[$usergroupid]['postdayper'] ? intval($usergrouplist[$usergroupid]['postdayper']) : '';
$_G['category_member']['todaythreads'] = intval($_G['category_member']['todaythreads']);

loadcache(array('category_option_'.$sortid, 'category_template_'.$sortid));
$sortoptionarray = $_G['cache']['category_option_'.$sortid];
$templatearray = $_G['cache']['category_template_'.$sortid]['subject'];

$_G['category_threadlist'] = $threadids = array();

$page = $_G['page'];
$start_limit = ($page - 1) * $_G['tpp'];
$colorarray = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');

$_G['category_threadcount'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$modidentifier.'_thread')." WHERE sortid='$sortid' AND authorid='$uid'");
$multipage = multi($_G['category_threadcount'], $_G['tpp'], $page, "$modurl?mod=my&sortid=$sortid&uid=$uid");

$query = DB::query("SELECT t.*, ts.* FROM ".DB::table('category_'.$modidentifier.'_thread')." t
	LEFT JOIN ".DB::table('category_sortvalue')."$sortid ts ON t.tid=ts.tid
	WHERE sortid='$sortid' AND authorid='$uid' ORDER BY ts.dateline DESC LIMIT $start_limit, $_G[tpp]");
while($thread = DB::fetch($query)) {
	if($thread['highlight']) {
			$string = sprintf('%02d', $thread['highlight']);
			$stylestr = sprintf('%03b', $string[0]);

			$thread['highlight'] = ' style="';
			$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
			$thread['highlight'] .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
			$thread['highlight'] .= '"';
	} else {
		$thread['highlight'] = '';
	}
	$threadids[] = $thread['tid'];
	$_G['category_threadlist'][$thread['tid']] = $thread;
}

$sortlistarray = showsorttemplate($sortid, $sortoptionarray, $templatearray, $_G['category_threadlist'], $threadids, $arealist, $modurl);
$stemplate = $sortlistarray['template'];
$sortexpiration = $sortlistarray['expiration'];

include template('diy:category/'.$modidentifier.'_my');

?>