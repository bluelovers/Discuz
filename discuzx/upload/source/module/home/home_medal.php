<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_medal.php 22728 2011-05-18 09:05:00Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
loadcache('medals');

if(!$_G['uid'] && $_G['gp_action']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$_G['mnid'] = 'mn_common';
$medallist = $medallogs = array();
$tpp = 10;
$page = max(1, intval($_G['gp_page']));
$start_limit = ($page - 1) * $tpp;

if(empty($_G['gp_action'])) {
	include libfile('function/forum');
	$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." WHERE available='1' ORDER BY displayorder LIMIT 0,100");
	while($medal = DB::fetch($query)) {
		$medal['permission'] = medalformulaperm(serialize(array('medal' => unserialize($medal['permission']))), 1);
		$medallist[$medal['medalid']] = $medal;
	}

	$query = DB::query("SELECT m.uid, m.username, ml.medalid, ml.dateline FROM ".DB::table('forum_medallog')." ml
		USE INDEX(dateline) LEFT JOIN ".DB::table('common_member')." m USING(uid)
		WHERE ml.type<'2' ORDER BY ml.dateline DESC LIMIT 10");
	$lastmedals = array();
	while($lastmedal = DB::fetch($query)) {
		$lastmedal['dateline'] = dgmdate($lastmedal['dateline'], 'u');
		$lastmedals[] = $lastmedal;
	}

} elseif($_G['gp_action'] == 'apply' && submitcheck('medalsubmit')) {

	$medalid = intval($_G['gp_medalid']);
	$_G['forum_formulamessage'] = $_G['forum_usermsg'] = $medalnew = '';
	$medal = DB::fetch_first("SELECT * FROM ".DB::table('forum_medal')." WHERE medalid='$medalid'");
	if(!$medal['type']) {
		showmessage('medal_apply_invalid');
	}

	$medaldetail = DB::fetch_first("SELECT medalid FROM ".DB::table('forum_medallog')." WHERE uid='$_G[uid]' AND medalid='$medalid' AND type<'3'");
	if($medaldetail['medalid']) {
		showmessage('medal_apply_existence', 'home.php?mod=medal');
	}

	$applysucceed = FALSE;
	$medalpermission = $medal['permission'] ? unserialize($medal['permission']) : '';
	if($medalpermission[0] || $medalpermission['usergroupallow']) {
		include libfile('function/forum');
		medalformulaperm(serialize(array('medal' => $medalpermission)), 1);

		if($_G['forum_formulamessage']) {
			showmessage('medal_permforum_nopermission', 'home.php?mod=medal', array('formulamessage' => $_G['forum_formulamessage'], 'usermsg' => $_G['forum_usermsg']));
		} else {
			$applysucceed = TRUE;
		}
	} else {
		$applysucceed = TRUE;
	}

	if($applysucceed) {
		$expiration = empty($medal['expiration'])? 0 : TIMESTAMP + $medal['expiration'] * 86400;
		if($medal['type'] == 1) {
			$usermedal = DB::fetch_first("SELECT medals FROM ".DB::table('common_member_field_forum')." WHERE uid='$_G[uid]'");
			$medal['medalid'] = $medal['medalid'].(empty($expiration) ? '' : '|'.$expiration);
			$medalnew = $usermedal['medals'] ? $usermedal['medals']."\t".$medal['medalid'] : $medal['medalid'];
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='$medalnew' WHERE uid='$_G[uid]'");
			$medalmessage = 'medal_get_succeed';
		} else {
			$medalmessage = 'medal_apply_succeed';
			manage_addnotify('verifymedal');
		}

		DB::query("INSERT INTO ".DB::table('forum_medallog')." (uid, medalid, type, dateline, expiration, status) VALUES ('$_G[uid]', '$medalid', '$medal[type]', '$_G[timestamp]', '$expiration', '0')");
		showmessage($medalmessage, 'home.php?mod=medal', array('medalname' => $medal['name']));
	}

} elseif($_G['gp_action'] == 'log') {

	include libfile('function/forum');
	$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." WHERE available='1' ORDER BY displayorder LIMIT 0,100");
	while($medal = DB::fetch($query)) {
		$medallist[$medal['medalid']] = $medal;
	}

	$medaldata = DB::result_first("SELECT medals FROM ".DB::table('common_member_field_forum')." WHERE uid='$_G[uid]'");
	$membermedal = $medaldata ? explode("\t", $medaldata) : array();
	foreach($membermedal as $k => $medal) {
		if(!in_array($medal, array_keys($medallist))) {
			unset($membermedal[$k]);
		}
	}
	$medalcount = count($membermedal);

	if(!empty($membermedal)) {
		$mymedal = array();
		foreach($membermedal as $medalid) {
			if($medalpos = strpos($medalid, '|')) {
				$medalid = substr($medalid, 0, $medalpos);
			}
			$mymedal['name'] = $_G['cache']['medals'][$medalid]['name'];
			$mymedal['image'] = $medallist[$medalid]['image'];
			$mymedals[] = $mymedal;
		}
	}

	$medallognum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_medallog')." WHERE uid='$_G[uid]' AND type<'2'");
	$multipage = multi($medallognum, $tpp, $page, "home.php?mod=medal&action=log");

	$query = DB::query("SELECT me.*, m.image FROM ".DB::table('forum_medallog')." me
			LEFT JOIN ".DB::table('forum_medal')." m USING (medalid)
			WHERE me.uid='$_G[uid]' ORDER BY me.dateline DESC LIMIT $start_limit,$tpp");
	while($medallog = DB::fetch($query)) {
		$medallog['name'] = $_G['cache']['medals'][$medallog['medalid']]['name'];
		$medallog['dateline'] = dgmdate($medallog['dateline']);
		$medallog['expiration'] = !empty($medallog['expiration']) ? dgmdate($medallog['expiration']) : '';
		$medallogs[] = $medallog;
	}

} else {
	showmessage('undefined_action');
}

$navtitle = lang('core', 'title_medals_list');

include template('home/space_medal');

?>