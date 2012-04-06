<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_threadsorts.php 22852 2011-05-26 04:15:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/threadsort');

threadsort_checkoption($sortid);
$forum_optionlist = getsortedoptionlist();

loadcache(array('threadsort_option_'.$sortid, 'threadsort_template_'.$sortid));
$sqlarr = array();
foreach($_G['cache']['threadsort_option_'.$sortid] AS $key => $val) {
	if($val['profile']) {
		$sqlarr[] = $val['profile'];
	}
}
if($sqlarr) {
	$member_profile_sql = implode(', ', $sqlarr);
	$member_profile = DB::fetch_first("SELECT $member_profile_sql FROM ".DB::table('common_member_profile')." WHERE uid = '$_G[uid]' LIMIT 1");
	unset($member_profile_sql);
}
threadsort_optiondata($pid, $sortid, $_G['cache']['threadsort_option_'.$sortid], $_G['cache']['threadsort_template_'.$sortid]);



?>