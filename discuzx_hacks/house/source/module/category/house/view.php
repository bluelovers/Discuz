<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_viewthread.php 7253 2010-03-31 09:27:33Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$thread = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_thread')." WHERE tid='$tid'");
$sortid = $thread['sortid'];

if(empty($sortid)) {
	showmessage(lang('category/template', 'house_thread_not_exist'));
}

loadcache(array('category_option_'.$sortid, 'category_template_'.$sortid));
$sortoptionarray = $_G['cache']['category_option_'.$sortid];
$templatearray = $_G['cache']['category_template_'.$sortid]['viewthread'];
$ntemplatearray = $_G['cache']['category_template_'.$sortid]['neighborhood'];

$sortdata = DB::fetch_first("SELECT tid, attachid, dateline, expiration, displayorder, highlight, recommend, groupid, city, district, street, mapposition FROM ".DB::table('category_sortvalue')."$sortid WHERE tid='$tid'");
$districtid = $sortdata['district'];
$streetid = $sortdata['street'];
$cityid = $sortdata['city'];

$mapposition = empty($sortdata['mapposition']) ? '' : explode(',', $sortdata['mapposition']);

if(empty($thread) || empty($sortdata)) {
	showmessage(lang('category/template', 'house_info_not_exist'));
}

if($sortdata['highlight'] || $sortdata['recommend']) {
	if($sortdata['highlight']) {
		$highlight = DB::fetch_first("SELECT expiration FROM ".DB::table('category_threadmod')." WHERE tid='$tid' AND action='highlight' ORDER BY expiration DESC LIMIT 1");
		if(TIMESTAMP > $highlight['expiration'] && !empty($highlight['expiration'])) {
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET highlight='0' WHERE tid='$tid'", 'UNBUFFERED');
		}
	}

	if($sortdata['recommend']) {
		$recommend = DB::fetch_first("SELECT expiration FROM ".DB::table('category_threadmod')." WHERE tid='$tid' AND action='recommend' ORDER BY expiration DESC LIMIT 1");
		if(TIMESTAMP > $recommend['expiration'] && !empty($recommend['expiration'])) {
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET recommend='0' WHERE tid='$tid'", 'UNBUFFERED');
		}
	}
}

$navigation = "&rsaquo; <a href=\"$modurl?mod=list&amp;cid=$cid&amp;sortid=$sortid\">".$sortlist[$sortid]['name']."</a> ";
$navigation .= $arealist['city'][$cityid] ? "&rsaquo; <a href=\"$modurl?mod=list&amp;cid=$cid&amp;sortid=$sortid&amp;filter=all&amp;city=$cityid\">".$arealist['city'][$cityid]."</a> " : '';
$navigation .= $arealist['district'][$cityid][$districtid] ? "&rsaquo; <a href=\"$modurl?mod=list&amp;cid=$cid&amp;sortid=$sortid&amp;filter=all&amp;city=$cityid&amp;district=$districtid\">".$arealist['district'][$cityid][$districtid]."</a> " : '';
$navigation .= $arealist['street'][$districtid][$streetid] ? "&rsaquo; <a href=\"$modurl?mod=list&amp;cid=$cid&amp;sortid=$sortid&amp;filter=all&amp;city=$cityid&amp;district=$districtid&amp;street=$streetid\">".$arealist['street'][$districtid][$streetid]."</a> " : '';

$navtitle = $arealist['city'][$cityid].$arealist['district'][$cityid][$districtid].$arealist['street'][$districtid][$streetid].$thread['subject'].' - '.$sortlist[$sortid]['name'].' - ';

require_once libfile('function/category');

$threadsortshow = threadsortshow($thread['tid'], $sortoptionarray, $templatearray, $thread['authorid'], $sortdata['groupid']);
$thread['avatar'] = category_uc_avatar($thread['authorid']);
$thread['dateline'] = dgmdate($sortdata['dateline'], 'd');
$thread['message'] = nl2br(dhtmlspecialchars($thread['message']));

$groupid = $sortdata['groupid'];
if($usergrouplist[$groupid]['type'] == 'intermediary') {
	$usergrouptitle = $usergrouplist[$groupid]['title'] ? "<a href=\"$modurl?mod=usergroup&amp;gid=$groupid&amp;cid=$cid&amp;sortid=$sortid\">".$usergrouplist[$groupid]['title']."</a>" : '';
	$usergroupicon = $usergrouplist[$groupid]['icon'] ? "<a href=\"$modurl?mod=usergroup&amp;gid=$groupid&amp;cid=$cid&amp;sortid=$sortid\"><img src=\"".$_G['setting']['attachurl'].'common/'.$usergrouplist[$groupid]['icon']."\"></a>" : '';
} else {
	$usergrouptitle = $usergrouplist[$groupid]['title'] ? $usergrouplist[$groupid]['title'] : '';
	$usergroupicon = $usergrouplist[$groupid]['icon'] ? "<img src=\"".$_G['setting']['attachurl'].'common/'.$usergrouplist[$groupid]['icon']."\">" : '';
}

visitedsetcookie($thread['tid']);
$neighborhoodlist = neighborhood($thread['tid'], $sortid, $cityid, $districtid, $streetid, $sortoptionarray, $ntemplatearray, $modurl);

$piclist = array();
if($sortdata['attachid']) {
	$query = DB::query("SELECT url FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$thread[tid]' ORDER BY dateline");
	while($pic = DB::fetch($query)) {
		$piclist[] = 'category/'.$pic['url'];
	}
}

include template('diy:category/'.$modidentifier.'_view');

?>
