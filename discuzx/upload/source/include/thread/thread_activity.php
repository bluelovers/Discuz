<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_activity.php 20005 2011-01-27 10:10:01Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$isverified = $applied = 0;
$ufielddata = $applyinfo = '';
if($_G['uid']) {
	$query = DB::query("SELECT message, verified, ufielddata FROM ".DB::table('forum_activityapply')." WHERE tid='$_G[tid]' AND uid='$_G[uid]'");
	if(DB::num_rows($query)) {
		$applyinfo = DB::fetch($query);
		$isverified = $applyinfo['verified'];
		if($applyinfo['ufielddata']) {
			$ufielddata = unserialize($applyinfo['ufielddata']);
		}
		$applied = 1;
	}
}
$applylist = array();
$activity = DB::fetch_first("SELECT * FROM ".DB::table('forum_activity')." WHERE tid='$_G[tid]'");
$activityclose = $activity['expiration'] ? ($activity['expiration'] > TIMESTAMP ? 0 : 1) : 0;
$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'u');
$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto']) : 0;
$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration']) : 0;
$activity['attachurl'] = $activity['thumb'] = '';
if($activity['ufield']) {
	$activity['ufield'] = unserialize($activity['ufield']);
	if($activity['ufield']['userfield']) {
		$htmls = $settings = array();
		require_once libfile('function/profile');
		foreach($activity['ufield']['userfield'] as $fieldid) {
			if(empty($ufielddata['userfield'])) {
				$query = DB::query("SELECT ".implode(',', $activity['ufield']['userfield'])." FROM ".DB::table('common_member_profile')." WHERE uid='$_G[uid]'");
				$ufielddata['userfield'] = DB::fetch($query);
			}
			$html = profile_setting($fieldid, $ufielddata['userfield'], false, true);
			if($html) {
				$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
				$htmls[$fieldid] = $html;
			}
		}
	}
} else {
	$activity['ufield'] = '';
}

if($activity['aid']) {
	$attach = DB::fetch_first("SELECT * FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='$activity[aid]'");
	if($attach['isimage']) {
		$activity['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
		$activity['thumb'] = $attach['thumb'] ? getimgthumbname($activity['attachurl']) : $activity['attachurl'];
		$activity['width'] = $attach['thumb'] && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
	}
	$skipaids[] = $activity['aid'];
}


$applylistverified = array();
$noverifiednum = 0;
$query = DB::query("SELECT aa.username, aa.uid, aa.verified, aa.dateline, aa.message, aa.payment, aa.ufielddata, m.groupid FROM ".DB::table('forum_activityapply')." aa
	LEFT JOIN ".DB::table('common_member')." m USING(uid)
	LEFT JOIN ".DB::table('common_member_field_forum')." mf USING(uid)
	WHERE aa.tid='$_G[tid]' ORDER BY aa.dateline DESC");
while($activityapplies = DB::fetch($query)) {
	$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
	if($activityapplies['verified'] == 1) {
		$activityapplies['ufielddata'] = unserialize($activityapplies['ufielddata']);
		if(count($applylist) < $_G['setting']['activitypp']) {
			$activityapplies['message'] = preg_replace("/(".lang('forum/misc', 'contact').".*)/", '', $activityapplies['message']);
			$applylist[] = $activityapplies;
		}
	} else {
		if(count($applylistverified) < 8) {
			$applylistverified[] = $activityapplies;
		}
		$noverifiednum++;
	}

}

$applynumbers = $activity['applynumber'];
$aboutmembers = $activity['number'] >= $applynumbers ? $activity['number'] - $applynumbers : 0;
$allapplynum = $applynumbers + $noverifiednum;
if($_G['forum']['status'] == 3) {
	$isgroupuser = groupperm($_G['forum'], $_G['uid']);
}
?>