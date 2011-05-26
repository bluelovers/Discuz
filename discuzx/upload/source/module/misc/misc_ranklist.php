<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_ranklist.php 20799 2011-03-04 03:19:52Z congyushuai $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = $_G['page'];
$type = $_G['gp_type'];

if(!in_array($type, array('index', 'member', 'thread', 'blog', 'poll', 'picture', 'activity', 'forum', 'group'))) {
	$type = 'index';
}

$ranklist_setting = $_G['setting']['ranklist'];
if(!$ranklist_setting['status']) {
	showmessage('ranklist_status_off');
}

$navtitle = lang('core', 'title_ranklist_'.$type);

if($type != 'index') {
	if(!$ranklist_setting[$type]['available']) {
		showmessage('ranklist_this_status_off');
	}
}

include libfile('misc/ranklist_'.$type, 'include');
function getranklist_thread($num = 20, $view = 'replies', $orderby = 'all') {
	$dateline = $timestamp = '';
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
		$dateline = "t.dateline>='$timestamp' AND";
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
		$dateline = "t.dateline>='$timestamp' AND";
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
		$dateline = "t.dateline>='$timestamp' AND";
	}
	$data = array();
	$query = DB::query("SELECT t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.views, t.replies, t.favtimes, t.sharetimes, t.heats, f.name AS forum
		FROM ".DB::table('forum_thread')." t
		LEFT JOIN ".DB::table('forum_forum')." f USING(fid)
		WHERE $dateline t.displayorder>='0'
		ORDER BY t.$view DESC
		LIMIT 0, $num");
	$rank = 0;
	while($thread = DB::fetch($query)) {
		++$rank;
		$thread['rank'] = $rank;
		$thread['dateline'] = dgmdate($thread['dateline']);
		$data[] = $thread;
	}
	return $data;
}

function getranklist_poll($num = 20, $view = 'heats', $orderby = 'all') {
	$dateline = $timestamp = '';
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
		$dateline = "AND t.dateline>='$timestamp'";
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
		$dateline = "AND t.dateline>='$timestamp'";
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
		$dateline = "AND t.dateline>='$timestamp'";
	}
	$data = array();
	$query = DB::query("SELECT t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.favtimes, t.sharetimes, t.heats,  p.pollpreview, p.voters
		FROM ".DB::table('forum_thread')." t
		LEFT JOIN ".DB::table('forum_poll')." p ON p.tid=t.tid
		WHERE t.special='1' $dateline AND t.displayorder>='0'
		ORDER BY t.$view DESC
		LIMIT 0, $num");
	require_once libfile('function/forum');
	$rank = 0;
	while($poll = DB::fetch($query)) {
		++$rank;
		$poll['rank'] = $rank;
		$poll['avatar'] = avatar($poll['authorid'], 'small');
		$poll['dateline'] = dgmdate($poll['dateline']);
		$poll['pollpreview'] = explode("\t", trim($poll['pollpreview']));
		$data[] = $poll;
	}
	return $data;
}

function getranklist_activity($num = 20, $view = 'heats', $orderby = 'all') {
	global $_G;
	$dateline = $timestamp = '';
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
		$dateline = "AND t.dateline>='$timestamp'";
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
		$dateline = "AND t.dateline>='$timestamp'";
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
		$dateline = "AND t.dateline>='$timestamp'";
	}
	$data = array();
	$query = DB::query("SELECT t.tid, t.subject, t.views, t.author, t.authorid, t.replies, t.heats, t.sharetimes, t.favtimes, act.aid, act.starttimefrom, act.starttimeto, act.place, act.class, act.applynumber, act.expiration
		FROM ".DB::table('forum_thread')." t
		LEFT JOIN ".DB::table('forum_activity')." act ON act.tid=t.tid
		WHERE t.special='4' AND t.isgroup='0' AND t.closed='0' $dateline AND t.displayorder>='0'
		ORDER BY t.$view DESC
		LIMIT 0, $num");
	$rank = 0;$attachtables = array();
	while($thread = DB::fetch($query)) {
		++$rank;
		$thread['rank'] = $rank;
		$thread['starttimefrom'] = dgmdate($thread['starttimefrom']);
		if($thread['starttimeto']) {
			$thread['starttimeto'] = dgmdate($thread['starttimeto']);
		} else {
			$thread['starttimeto'] = '';
		}
		if($thread['expiration'] && TIMESTAMP > $thread['expiration']) {
			$thread['has_expiration'] = true;
		} else {
			$thread['has_expiration'] = false;
		}
		$data[$thread['tid']] = $thread;
		$attachtables[getattachtableid($thread['tid'])][] = $thread['aid'];
	}
	foreach($attachtables as $attachtable => $aids) {
		$attachtable = 'forum_attachment_'.$attachtable;
		$query = DB::query("SELECT tid, attachment, remote FROM ".DB::table($attachtable)." WHERE aid IN (".dimplode($aids).")");
		while($attach = DB::fetch($query)) {
			$attach['attachurl'] = ($thread['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
			$data[$attach['tid']] = array_merge($data[$attach['tid']], $attach);
		}
	}
	return $data;
}

function getranklist_picture($num = 20, $view = 'hot', $orderby = 'all') {
	$dateline = $timestamp = '';
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
		$dateline = "WHERE p.dateline>='$timestamp'";
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
		$dateline = "WHERE p.dateline>='$timestamp'";
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
		$dateline = "WHERE p.dateline>='$timestamp'";
	}

	$data = array();
	$query = DB::query("SELECT p.picid, p.uid, p.username, p.title, p.filepath, p.thumb, p.remote, p.hot, p.sharetimes, p.click1,
		p.click2, p.click3 , p.click4, p.click5, p.click6, p.click7, p.click8, a.albumid, a.albumname, a.friend
		FROM ".DB::table('home_pic')." p
		LEFT JOIN ".DB::table('home_album')." a ON p.albumid=a.albumid
		$dateline
		ORDER BY p.$view DESC
		LIMIT 0, $num");

	require_once libfile('function/home');
	$rank = 0;
	while($picture = DB::fetch($query)) {
		++$rank;
		$picture['rank'] = $rank;
		$picture['url'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', $picture['thumb'], $picture['remote']) : STATICURL.'image/common/nopublish.gif';;
		$picture['origurl'] = pic_get($picture['filepath'], 'album', 0, $picture['remote']);
		$data[] = $picture;
	}
	return $data;
}

function getranklist_pictures_index($num = 20, $dateline = 0, $orderby = 'hot DESC') {
	$picturelist = array();
	$query = DB::query("SELECT p.picid, p.uid, p.username, p.title, p.filepath, p.thumb, p.remote, a.albumid, a.albumname, a.friend
		FROM ".DB::table('home_pic')." p
		LEFT JOIN ".DB::table('home_album')." a ON p.albumid=a.albumid
		WHERE p.hot>'3'
		ORDER BY p.dateline DESC
		LIMIT 0, $num");

	require_once libfile('function/home');
	while($picture = DB::fetch($query)) {
		$picture['url'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', $picture['thumb'], $picture['remote']) : STATICURL.'image/common/nopublish.gif';;
		$picture['origurl'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', 0, $picture['remote']) : STATICURL.'image/common/nopublish.gif';
		$picturelist[] = $picture;
	}
	return $picturelist;
}

function getranklist_members($offset = 0, $limit = 20) {
	require_once libfile('function/forum');
	$members = array();
	$query = DB::query("SELECT *
		FROM ".DB::table('home_show')."
		ORDER BY unitprice DESC, credit DESC
		LIMIT $offset, $limit");
	while($member = DB::fetch($query)) {
		$member['avatar'] = avatar($member['uid'], 'small');
		$members[] = $member;
	}
	return $members;
}

function getranklist_girls($offset = 0, $limit = 20, $orderby = 'ORDER BY s.unitprice DESC, s.credit DESC') {
	$members = array();
	$query = DB::query("SELECT m.uid, m.username, mc.*, mp.gender
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('home_show')." s ON s.uid=m.uid
		LEFT JOIN ".DB::table('common_member_profile')." mp ON mp.uid=m.uid
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
		WHERE mp.gender='2'
		ORDER BY $orderby
		LIMIT $offset, $limit");
	while($member = DB::fetch($query)) {
		$member['avatar'] = avatar($member['uid'], 'small');
		$members[] = $member;
	}
	return $members;
}

function getranklist_blog($num = 20, $view = 'hot', $orderby = 'all') {
	$dateline = $timestamp = '';
	if($orderby == 'today') {
		$timestamp = TIMESTAMP - 86400;
		$dateline = "AND b.dateline>='$timestamp'";
	} elseif($orderby == 'thisweek') {
		$timestamp = TIMESTAMP - 604800;
		$dateline = "AND b.dateline>='$timestamp'";
	} elseif($orderby == 'thismonth') {
		$timestamp = TIMESTAMP - 2592000;
		$dateline = "AND b.dateline>='$timestamp'";
	}

	$data = array();
	$query = DB::query("SELECT b.blogid, b.uid, b.username, b.subject, b.dateline, b.viewnum, b.replynum, b.hot, b.sharetimes, b.favtimes,
		b.click1, b.click2, b.click3, b.click4, b.click5, b.click6, b.click7, b.click8, bf.message
		FROM ".DB::table('home_blog')." b
		LEFT JOIN ".DB::table('home_blogfield'). " bf ON bf.blogid=b.blogid
		WHERE b.friend='0' AND status = '0' $dateline
		ORDER BY b.$view DESC
		LIMIT 0, $num");
	require_once libfile('function/forum');
	require_once libfile('function/post');
	$rank = 0;
	while($blog = DB::fetch($query)) {
		++$rank;
		$blog['rank'] = $rank;
		$blog['dateline'] = dgmdate($blog['dateline']);
		$blog['avatar'] = avatar($blog['uid'], 'small');
		$blog['message'] = preg_replace('/<([^>]*?)>/', '', $blog['message']);
		$blog['message'] = messagecutstr($blog['message'], 140);
		$data[] = $blog;
	}
	return $data;
}

function getranklist_forum($num = 20, $view = 'threads') {
	global $_G;

	$timestamp = TIMESTAMP;
	$data = array();
	$timelimit = 0;
	if($view == 'posts') {
		$sql = "SELECT fid, name, posts FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group' ORDER BY posts DESC LIMIT 0, $num";
	} elseif($view == 'thismonth') {
		$timelimit = $timestamp-86400*30;
	} elseif($view == 'today'){
		$timelimit = $timestamp-86400;
	} else {
		$sql = "SELECT fid, name, threads AS posts FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group' ORDER BY threads DESC LIMIT 0, $num";
	}
	if($timelimit) {
		$sql = "SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table(getposttable())." p, ".DB::table('forum_forum')." f
		WHERE p.fid=f.fid AND p.dateline>='$timelimit' AND p.invisible='0' AND p.authorid>'0' AND f.status='1' AND f.type<>'group'
		GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $num";
	}
	$query = DB::query($sql);
	$i = 1;
	while($result = DB::fetch($query)) {
		$result['rank'] = $i;
		$data[] = $result;
		$i++;
	}

	return $data;

}

function getranklist_group($num = 20, $view = 'threads') {
	global $_G;

	$timestamp = TIMESTAMP;
	$data = array();
	$timelimit = 0;

	if($view == 'posts') {
		$sql = "SELECT fid, name, posts FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub' ORDER BY posts DESC LIMIT 0, $num";
	} elseif($view == 'thismonth') {
		$timelimit = $timestamp-86400*30;
	} elseif($view == 'today'){
		$timelimit = $timestamp-86400;
	} elseif($view == 'credit'){
		$sql = "SELECT fid, name, commoncredits FROM ".DB::table('forum_forum')."
		WHERE status='3' AND type='sub' ORDER BY commoncredits DESC LIMIT 0, $num";
	} elseif($view == 'member'){
		$sql = "SELECT f.fid, f.name, ff.membernum FROM ".DB::table('forum_forum')." f, ".DB::table('forum_forumfield')." ff
		WHERE f.fid=ff.fid AND f.status='3' AND f.type='sub'
		ORDER BY ff.membernum DESC LIMIT 0, $num";
	} else {
		$sql = "SELECT fid, name, threads AS posts FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub' ORDER BY threads DESC LIMIT 0, $num";
	}
	if($timelimit) {
		$sql = "SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table(getposttable())." p, ".DB::table('forum_forum')." f
		WHERE p.fid=f.fid AND p.dateline>='$timelimit' AND p.invisible='0' AND p.authorid>'0' AND f.status='3' AND f.type='sub'
		GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $num";
	}
	$query = DB::query($sql);
	$i = 1;
	while($result = DB::fetch($query)) {
		$result['rank'] = $i;
		$data[] = $result;
		$i++;
	}

	return $data;

}

function getranklist_member($num = 20, $view = 'credit', $orderby = 'all') {
	$data = array();
	$functionname = 'getranklist_member_'.$view;
	$data = $functionname($num, $orderby);
	return $data;
}

function getranklist_member_credit($num, $orderby) {
	global $_G;

	$data = array();
	if($orderby == 'all') {
		$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid,m.credits,field.spacenote FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_home')." field ON field.uid=m.uid
			ORDER BY m.credits DESC LIMIT 0, $num";
	} else {
		$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid, mc.extcredits$orderby AS extcredits
			FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE mc.extcredits$orderby>0
			ORDER BY extcredits$orderby DESC LIMIT 0, $num";
	}

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$data[] = $result;
	}

	return $data;

}

function getranklist_member_friendnum($num) {
	global $_G;

	$num = intval($num);
	$num = $num ? $num : 20;
	$data = $users = $oldorder = array();
	$query = DB::query('SELECT uid, friends FROM '.DB::table('common_member_count').' WHERE friends>0 ORDER BY friends DESC LIMIT '.$num);
	while($user = DB::fetch($query)) {
		$users[$user['uid']] = $user;
		$oldorder[] = $user['uid'];
	}
	$uids = array_keys($users);
	if($uids) {
		$query = DB::query('SELECT m.uid, m.username, m.videophotostatus, m.groupid, field.spacenote
			FROM '.DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_home')." field ON m.uid=field.uid
			WHERE m.uid IN (".dimplode($uids).")");
		while($value = DB::fetch($query)) {
			$users[$value['uid']] = array_merge($users[$value['uid']], $value);
		}

		foreach($oldorder as $uid) {
			$data[] = $users[$uid];
		}

	}
	return $data;

}

function getranklist_member_invite($num, $orderby) {
	global $_G;

	if($orderby == 'thisweek') {
		$dateline = "AND dateline>".(TIMESTAMP - 604800);
	} elseif($orderby == 'thismonth') {
		$dateline = "AND dateline>".(TIMESTAMP - 2592000);
	} elseif($orderby == 'today') {
		$dateline = "AND dateline>".(TIMESTAMP - 86400);
	}

	$invite = $invitearray = $inviteuidarray = $invitefieldarray = array();
	$sql = "SELECT count(*) AS invitenum ,uid FROM ".DB::table('common_invite')."
		WHERE status='2' $dateline  GROUP BY uid
		ORDER BY invitenum DESC LIMIT 0, $num";
	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$invitearray[] = $result;
		$inviteuidarray[] = $result['uid'];
	}

	$sql = "SELECT uid, username, videophotostatus, groupid FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($inviteuidarray).")";
	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$invitememberfield[$result[uid]] = $result;
	}
	if($invitearray) {
		foreach($invitearray as $key => $var) {
			$invite[] = $var;
			$invite[$key]['username'] = $invitememberfield[$var['uid']]['username'];
			$invite[$key]['videophotostatus'] = $invitememberfield[$var['uid']]['videophotostatus'];
			$invite[$key]['groupid'] = $invitememberfield[$var['uid']]['groupid'];
		}
	}
	return $invite;

}

function getranklist_member_onlinetime($num, $orderby) {
	global $_G;

	if($orderby == 'thismonth') {
		$orderby = 'thismonth';
		$online = 'thismonth AS onlinetime';
	} elseif($orderby == 'all') {
		$orderby = 'total';
		$online = 'total AS onlinetime';
	}

	$onlinetime = $onlinetimearray = $onlinetimeuidarray = $onlinetimefieldarray = array();
	$sql = "SELECT *, $online FROM ".DB::table('common_onlinetime')." WHERE $orderby>0
		ORDER BY $orderby DESC LIMIT 0, $num";
	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$onlinetimearray[] = $result;
		$onlinetimeuidarray[] = $result['uid'];
	}

	$sql = "SELECT uid, username, videophotostatus, groupid FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($onlinetimeuidarray).")";
	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$onlinetimefieldarray[$result[uid]] = $result;
	}
	if($onlinetimearray) {
		foreach($onlinetimearray as $key => $var) {
			$onlinetime[] = $var;
			$onlinetime[$key]['username'] = $onlinetimefieldarray[$var['uid']]['username'];
			$onlinetime[$key]['videophotostatus'] = $onlinetimefieldarray[$var['uid']]['videophotostatus'];
			$onlinetime[$key]['groupid'] = $onlinetimefieldarray[$var['uid']]['groupid'];
		}
	}
	return $onlinetime;

}

function getranklist_member_blog($num) {
	global $_G;

	$blogs = array();
	$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid,c.blogs FROM ".DB::table('common_member').
			" m LEFT JOIN ".DB::table('common_member_count')." c ON m.uid=c.uid WHERE c.blogs>0 ORDER BY blogs DESC LIMIT 0, $num";

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$blogs[] = $result;
	}

	return $blogs;

}


function getranklist_member_gender($gender, $num = 20) {
	global $_G;

	$num = intval($num);
	$num = $num ? $num : 20;
	$data = $users = $oldorder = array();
	$query = DB::query("SELECT c.uid, c.views FROM ".DB::table('common_member_count')." c
			LEFT JOIN ".DB::table('common_member_profile')." p ON c.uid=p.uid
			WHERE c.views>0 AND p.gender = '$gender' ORDER BY c.views DESC LIMIT 0, $num");
	while($user = DB::fetch($query)) {
		$users[$user['uid']] = $user;
		$oldorder[] = $user['uid'];
	}
	$uids = array_keys($users);
	if($uids) {
		$query = DB::query('SELECT m.uid, m.username, m.videophotostatus, m.groupid
			FROM '.DB::table('common_member')." m
			WHERE m.uid IN (".dimplode($uids).")");
		while($value = DB::fetch($query)) {
			$users[$value['uid']] = array_merge($users[$value['uid']], $value);
		}

		foreach($oldorder as $uid) {
			$data[] = $users[$uid];
		}

	}
	return $data;

}

function getranklist_member_beauty($num = 20) {
	return getranklist_member_gender(2, $num);
}

function getranklist_member_handsome($num = 20) {
	return getranklist_member_gender(1, $num);
}

function getranklist_member_post($num, $orderby) {
	global $_G;

	$timestamp = TIMESTAMP;
	$posts = array();
	$timelimit = 0;
	if($orderby == 'digestposts') {
		$sql = "SELECT m.username, m.uid, mc.digestposts AS posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE mc.digestposts>0
		ORDER BY mc.digestposts DESC LIMIT 0, $num";
	} elseif($orderby == 'thismonth') {
		$timelimit = $timestamp-86400*30;
	} elseif($orderby == 'today') {
		$timelimit = $timestamp-86400;
	} else {
		$sql = "SELECT m.username, m.uid, mc.posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE	mc.posts>0
		ORDER BY mc.posts DESC LIMIT 0, $num";
	}
	if($timelimit) {
		$sql = "SELECT DISTINCT(author) AS username, authorid AS uid, COUNT(pid) AS posts
		FROM ".DB::table(getposttable())." WHERE dateline>='$timelimit' AND invisible='0' AND authorid>'0'
		GROUP BY author
		ORDER BY posts DESC LIMIT 0, $num";
	}
	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$posts[] = $result;
	}

	return $posts;

}

function getranklistdata($type, $view = '', $orderby = 'all') {
	global $_G;
	$cache_time = $_G['setting']['ranklist'][$type]['cache_time'];
	$cache_num =  $_G['setting']['ranklist'][$type]['show_num'];
	if($cache_time <= 0 ) {
		$cache_time = 5;
	}
	$cache_time = $cache_time * 3600;
	if($cache_num <= 0 ) {
		$cache_num = 20;
	}

	$ranklistvars = array();
	loadcache('ranklist_'.$type);
	$ranklistvars = & $_G['cache']['ranklist_'.$type][$view][$orderby];

	if(empty($ranklistvars['lastupdated']) || (TIMESTAMP - $ranklistvars['lastupdated'] > $cache_time)) {
		$functionname = 'getranklist_'.$type;

		if(!discuz_process::islocked('ranklist_update', 600)) {
			$ranklistvars = $functionname($cache_num, $view, $orderby);
			$ranklistvars['lastupdated'] = TIMESTAMP;
			$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
			$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
			$_G['cache']['ranklist_'.$type][$view][$orderby] = $ranklistvars;
			save_syscache('ranklist_'.$type, $_G['cache']['ranklist_'.$type]);
		}
		discuz_process::unlock('ranklist_update');
	}
	$_G['lastupdate'] = $ranklistvars['lastupdate'];
	$_G['nextupdate'] = $ranklistvars['nextupdate'];
	unset($ranklistvars['lastupdated'], $ranklistvars['lastupdate'], $ranklistvars['nextupdate']);
	return $ranklistvars;
}

?>