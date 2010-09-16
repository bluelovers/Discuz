<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_ranklist.php 16454 2010-09-07 03:48:02Z zhangguosheng $
 */

function getranklist_threads($num = 20, $dateline = 0, $orderby = 'replies DESC') {
	$dateline = !$dateline ? TIMESTAMP - 86400 * 30 : $dateline;
	$threadlist = array();
	$query = DB::query("SELECT t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.views, t.replies, t.favtimes, t.sharetimes, f.name AS forum
		FROM ".DB::table('forum_thread')." t
		LEFT JOIN ".DB::table('forum_forum')." f USING(fid)
		WHERE t.dateline>='$dateline' AND t.special='0' AND t.displayorder>='0'
		ORDER BY t.$orderby
		LIMIT 0, $num");
	$rank = 0;
	while($thread = DB::fetch($query)) {
		++$rank;
		$thread['rank'] = $rank;
		$thread['dateline'] = dgmdate($thread['dateline']);
		$threadlist[] = $thread;
	}
	return $threadlist;
}

function getranklist_polls($num = 20, $dateline = 0, $orderby = 'heats DESC') {
	$dateline = !$dateline ? TIMESTAMP - 86400 * 30 : $dateline;
	$polllist = array();
	$query = DB::query("SELECT t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.favtimes, t.sharetimes, t.heats,  p.pollpreview, p.voters
		FROM ".DB::table('forum_thread')." t
		LEFT JOIN ".DB::table('forum_poll')." p ON p.tid=t.tid
		WHERE t.special='1' AND t.dateline>='$dateline' AND t.displayorder>='0'
		ORDER BY t.$orderby
		LIMIT 0, $num");
	require_once libfile('function/forum');
	$rank = 0;
	while($poll = DB::fetch($query)) {
		++$rank;
		$poll['rank'] = $rank;
		$poll['avatar'] = discuz_uc_avatar($poll['authorid'], 'small');
		$poll['dateline'] = dgmdate($poll['dateline']);
		$poll['pollpreview'] = explode("\t", trim($poll['pollpreview']));
		$polllist[] = $poll;
	}
	return $polllist;
}

function getranklist_activities($num = 20, $dateline = 0, $orderby = 't.heats DESC') {
	global $_G;
	$dateline = !$dateline ? TIMESTAMP - 86400 * 30 : $dateline;
	$threadlist = array();
	$query = DB::query("SELECT t.tid, t.subject, t.views, t.author, t.authorid, t.replies, t.heats, t.sharetimes, t.favtimes, act.aid, act.starttimefrom, act.starttimeto, act.place, act.class, act.applynumber, act.expiration, a.attachment, a.remote
		FROM ".DB::table('forum_thread')." t
		LEFT JOIN ".DB::table('forum_activity')." act ON act.tid=t.tid
		LEFT JOIN ".DB::table('forum_attachment')." a ON a.aid=act.aid
		WHERE t.special='4' AND t.isgroup='0' AND t.closed='0' AND t.dateline>='$dateline' AND t.displayorder>='0'
		ORDER BY $orderby
		LIMIT 0, $num");
	$rank = 0;
	while($thread = DB::fetch($query)) {
		++$rank;
		$thread['rank'] = $rank;
		$thread['starttimefrom'] = dgmdate($thread['starttimefrom']);
		if($thread['starttimeto']) {
			$thread['starttimeto'] = dgmdate($thread['starttimeto']);
		} else {
			$thread['starttimeto'] = lang('ranklist/template', 'to_today');
		}
		if($thread['expiration'] && TIMESTAMP > $thread['expiration']) {
			$thread['has_expiration'] = true;
		} else {
			$thread['has_expiration'] = false;
		}
		if($thread['aid']) {
			$thread['attachurl'] = ($thread['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$thread['attachment'];
		}
		$threadlist[] = $thread;
	}
	return $threadlist;
}

function getranklist_pictures($num = 20, $dateline = 0, $orderby = 'hot DESC') {
	$dateline = !$dateline ? TIMESTAMP - 86400 * 30 : $dateline;
	$picturelist = array();
	$query = DB::query("SELECT p.picid, p.uid, p.username, p.title, p.filepath, p.thumb, p.remote, p.hot, p.sharetimes, p.click1,
		p.click2, p.click3 , p.click4, p.click5, p.click6, p.click7, p.click8, a.albumid, a.albumname, a.friend
		FROM ".DB::table('home_pic')." p
		LEFT JOIN ".DB::table('home_album')." a ON p.albumid=a.albumid
		WHERE p.dateline>='$dateline'
		ORDER BY p.$orderby
		LIMIT 0, $num");

	require_once libfile('function/home');
	$rank = 0;
	while($picture = DB::fetch($query)) {
		++$rank;
		$picture['rank'] = $rank;
		$picture['url'] = $picture['friend'] == 0 ? pic_get($picture['filepath'], 'album', $picture['thumb'], $picture['remote']) : STATICURL.'image/common/nopublish.gif';;
		$picture['origurl'] = pic_get($picture['filepath'], 'album', 0, $picture['remote']);
		$picturelist[] = $picture;
	}
	return $picturelist;
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
		$member['avatar'] = discuz_uc_avatar($member['uid'], 'small');
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
		$member['avatar'] = discuz_uc_avatar($member['uid'], 'small');
		$members[] = $member;
	}
	return $members;
}

function getranklist_blogs($num = 20, $dateline = 0, $orderby = 'hot DESC') {
	$dateline = !$dateline ? TIMESTAMP - 86400 * 30 : $dateline;
	$query = DB::query("SELECT b.blogid, b.uid, b.username, b.subject, b.dateline, b.viewnum, b.replynum, b.hot, b.sharetimes, b.favtimes,
		b.click1, b.click2, b.click3, b.click4, b.click5, b.click6, b.click7, b.click8, bf.message
		FROM ".DB::table('home_blog')." b
		LEFT JOIN ".DB::table('home_blogfield'). " bf ON bf.blogid=b.blogid
		WHERE b.friend='0' AND b.dateline>='$dateline'
		ORDER BY b.$orderby
		LIMIT 0, $num");
	require_once libfile('function/forum');
	require_once libfile('function/post');
	$rank = 0;
	while($blog = DB::fetch($query)) {
		++$rank;
		$blog['rank'] = $rank;
		$blog['dateline'] = dgmdate($blog['dateline']);
		$blog['avatar'] = discuz_uc_avatar($blog['uid'], 'small');
		$blog['message'] = preg_replace('/<([^>]*?)>/', '', $blog['message']);
		$blog['message'] = messagecutstr($blog['message'], 140);
		$blogs[] = $blog;
	}
	return $blogs;
}

function getranklist_forums($type = 'threads' , $num = 20) {
	global $_G;

	$timestamp = TIMESTAMP;
	$forums = array();

	if($type == 'posts') {
		$sql = "SELECT fid, name, posts FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group' ORDER BY posts DESC LIMIT 0, $num";
	} elseif($type == 'thismonth') {
		$sql = "SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table('forum_post')." p, ".DB::table('forum_forum')." f
		WHERE p.fid=f.fid AND p.dateline>='$timestamp'-86400*30 AND p.invisible='0' AND p.authorid>'0' AND f.status='1' AND f.type<>'group'
		GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $num";
	} elseif($type == 'today'){
		$sql = "SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table('forum_post')." p, ".DB::table('forum_forum')." f
		WHERE p.fid=f.fid AND p.dateline>='$timestamp'-86400 AND p.invisible='0' AND p.authorid>'0' AND f.status='1' AND f.type<>'group'
		GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $num";
	} else {
		$sql = "SELECT fid, name, threads AS posts FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group' ORDER BY threads DESC LIMIT 0, $num";
	}
	$query = DB::query($sql);
	$i = 1;
	while($result = DB::fetch($query)) {
		$result['rank'] = $i;
		$forums[] = $result;
		$i++;
	}

	return $forums;

}

function getranklist_groups($type = 'threads' , $num = 20) {
	global $_G;

	$timestamp = TIMESTAMP;
	$groups = array();

	if($type == 'posts') {
		$sql = "SELECT fid, name, posts FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub' ORDER BY posts DESC LIMIT 0, $num";
	} elseif($type == 'thismonth') {
		$sql = "SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table('forum_post')." p, ".DB::table('forum_forum')." f
		WHERE p.fid=f.fid AND p.dateline>='$timestamp'-86400*30 AND p.invisible='0' AND p.authorid>'0' AND f.status='3' AND f.type='sub'
		GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $num";
	} elseif($type == 'today'){
		$sql = "SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table('forum_post')." p, ".DB::table('forum_forum')." f
		WHERE p.fid=f.fid AND p.dateline>='$timestamp'-86400 AND p.invisible='0' AND p.authorid>'0' AND f.status='3' AND f.type='sub'
		GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $num";
	} elseif($type == 'credit'){
		$sql = "SELECT fid, name, commoncredits FROM ".DB::table('forum_forum')."
		WHERE status='3' AND type='sub' ORDER BY commoncredits DESC LIMIT 0, $num";
	} elseif($type == 'member'){
		$sql = "SELECT f.fid, f.name, ff.membernum FROM ".DB::table('forum_forum')." f, ".DB::table('forum_forumfield')." ff
		WHERE f.fid=ff.fid AND f.status='3' AND f.type='sub'
		ORDER BY ff.membernum DESC LIMIT 0, $num";
	} else {
		$sql = "SELECT fid, name, threads AS posts FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub' ORDER BY threads DESC LIMIT 0, $num";
	}
	$query = DB::query($sql);
	$i = 1;
	while($result = DB::fetch($query)) {
		$result['rank'] = $i;
		$groups[] = $result;
		$i++;
	}

	return $groups;

}

function getranklist_member_credits($type = 'all' , $num = 20) {
	global $_G;

	$credits = array();
	if($type == 'all') {
		$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid,m.credits,field.spacenote FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_home')." field ON field.uid=m.uid
			ORDER BY m.credits DESC LIMIT 0, $num";
	} else {
		$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid, mc.extcredits$type AS extcredits
			FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE mc.extcredits$type>0
			ORDER BY extcredits$type DESC LIMIT 0, $num";
	}

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$credits[] = $result;
	}

	return $credits;

}

function getranklist_member_friendnum($num = 20) {
	global $_G;

	$friendnum = array();
	$sql = "SELECT main.friends, m.uid, m.username, m.videophotostatus, m.groupid, field.spacenote FROM ".DB::table('common_member_count')." main
		LEFT JOIN ".DB::table('common_member')." m ON m.uid=main.uid
		LEFT JOIN ".DB::table('common_member_field_home')." field ON field.uid=main.uid WHERE main.friends>0
		ORDER BY main.friends DESC LIMIT 0, $num";

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$friendnum[] = $result;
	}

	return $friendnum;

}

function getranklist_member_blogs($num = 20) {
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

function getranklist_member_beauty($num = 20) {
	global $_G;

	$beauty = array();
	$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid,c.views FROM ".DB::table('common_member').
			" m LEFT JOIN ".DB::table('common_member_count')." c ON m.uid=c.uid LEFT JOIN ".DB::table('common_member_profile')." p ON m.uid=p.uid
			WHERE p.gender = '2' AND c.views>0 ORDER BY c.views DESC LIMIT 0, $num";

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$beauty[] = $result;
	}

	return $beauty;

}

function getranklist_member_handsome($num = 20) {
	global $_G;

	$handsome = array();
	$sql = "SELECT m.uid,m.username,m.videophotostatus,m.groupid,c.views FROM ".DB::table('common_member').
			" m LEFT JOIN ".DB::table('common_member_count')." c ON m.uid=c.uid LEFT JOIN ".DB::table('common_member_profile')." p ON m.uid=p.uid
			WHERE p.gender = '1' AND c.views>0 ORDER BY c.views DESC LIMIT 0, $num";

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$handsome[] = $result;
	}

	return $handsome;

}

function getranklist_member_posts($type = 'posts', $num = 20) {
	global $_G;

	$timestamp = TIMESTAMP;
	$posts = array();
	if($type == 'digestposts') {
		$sql = "SELECT m.username, m.uid, mc.digestposts AS posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE mc.digestposts>0
		ORDER BY mc.digestposts DESC LIMIT 0, $num";
	} elseif($type == 'thismonth') {
		$sql = "SELECT DISTINCT(author) AS username, authorid AS uid, COUNT(pid) AS posts
		FROM ".DB::table('forum_post')." WHERE dateline>='$timestamp'-86400*30 AND invisible='0' AND authorid>'0'
		GROUP BY author
		ORDER BY posts DESC LIMIT 0, $num";
	} elseif($type == 'today') {
		$sql ="SELECT DISTINCT(author) AS username, authorid AS uid, COUNT(pid) AS posts
		FROM ".DB::table('forum_post')." WHERE dateline >='$timestamp'-86400 AND invisible='0' AND authorid>'0'
		GROUP BY author
		ORDER BY posts DESC LIMIT 0, $num";
	} else {
		$sql = "SELECT m.username, m.uid, mc.posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid WHERE	mc.posts>0
		ORDER BY mc.posts DESC LIMIT 0, $num";
	}

	$query = DB::query($sql);
	while($result = DB::fetch($query)) {
		$posts[] = $result;
	}

	return $posts;

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

include libfile('misc/ranklist_'.$type, 'include');
?>