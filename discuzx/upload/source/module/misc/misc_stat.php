<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: misc_stat.php 21574 2011-04-01 01:51:36Z congyushuai $
*/

define('CACHE_TIME', 18000);

$op = $_G['gp_op'];
if(!in_array($op, array('basic', 'threadsrank', 'postsrank', 'forumsrank', 'creditsrank', 'trade', 'onlinetime', 'team', 'trend', 'modworks', 'memberlist', 'forumstat', 'trend'))) {
	$op = 'basic';
}
if(!$_G['group']['allowstatdata'] && $op != 'trend') {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$navtitle = lang('core', 'title_stats_'.$op).' - '.lang('core', 'title_stats');

loadcache('statvars');

if($op == 'basic') {
	$statvars = getstatvars('basic');
	extract($statvars);
	include template('forum/stat_main');
} elseif($op == 'forumsrank') {
	$statvars = getstatvars('forumsrank');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'threadsrank') {
	$statvars = getstatvars('threadsrank');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'postsrank') {
	$statvars = getstatvars('postsrank');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'creditsrank') {
	$statvars = getstatvars('creditsrank');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'trade') {
	$statvars = getstatvars('trade');
	extract($statvars);
	include template('forum/stat_trade');
} elseif($op == 'onlinetime') {
	$statvars = getstatvars('onlinetime');
	extract($statvars);
	include template('forum/stat_onlinetime');
} elseif($op == 'team') {
	$statvars = getstatvars('team');
	extract($statvars);
	include template('forum/stat_team');
} elseif($op == 'modworks' && $_G['setting']['modworkstatus']) {
	$statvars = getstatvars('modworks');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'memberlist' && $_G['setting']['memliststatus']) {
	$statvars = getstatvars('memberlist');
	extract($statvars);
	include template('forum/stat_memberlist');
} elseif($op == 'forumstat') {
	$statvars = getstatvars('forumstat');
	extract($statvars);
	include template('forum/stat_misc');
} elseif($op == 'trend') {
	include libfile('misc/stat', 'include');
} else {
	showmessage('undefined_action');
}

function getstatvars($type) {
	global $_G;
	$statvars = & $_G['cache']['statvars'][$type];

	if(!empty($statvars['lastupdated']) && TIMESTAMP - $statvars['lastupdated'] < CACHE_TIME) {
		return $statvars;
	}

	switch($type) {
		case 'basic':
		case 'forumsrank':
		case 'threadsrank':
		case 'postsrank':
		case 'creditsrank':
		case 'trade':
		case 'onlinetime':
		case 'team':
		case 'modworks':
		case 'memberlist':
		case 'forumstat':
			$statvars = call_user_func('getstatvars_'.$type, ($type == 'forumstat' ? $_G['gp_fid'] : ''));//getstatvars_forumstat($_G['gp_fid']);
			break;
	}
	return $statvars;
}

function getstatvars_basic() {
	global $_G;

	$statvars = array();
	$membersinfo= DB::fetch_first("SELECT COUNT(*) AS members, (MAX(regdate)-MIN(regdate))/86400 AS runtime FROM ".DB::table('common_member'));
	$statvars['members'] = $membersinfo['members'];
	$members_runtime = $membersinfo['runtime'];
	@$statvars['membersaddavg'] = round($statvars['members'] / $members_runtime);
	$statvars['memnonpost'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_count')." WHERE posts='0'");
	$statvars['mempost'] = $statvars['members'] - $statvars['memnonpost'];
	$statvars['admins'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE adminid<>'0' AND adminid<>'-1'");
	$statvars['lastmember'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE regdate>'".(TIMESTAMP - 86400)."'");
	$statvars['mempostpercent'] = number_format((double)$statvars['mempost'] / $statvars['members'] * 100, 2);

	$bestmember = DB::fetch_first("SELECT author, COUNT(*) AS posts FROM ".DB::table(getposttable())." WHERE dateline>='$_G[timestamp]'-86400 AND invisible='0' AND authorid>'0' GROUP BY author ORDER BY posts DESC LIMIT 1");
	$statvars['bestmem'] = $bestmember['author'];
	$statvars['bestmemposts'] = $bestmember['posts'];
	$postsinfo = DB::fetch_first("SELECT COUNT(*) AS posts, (MAX(dateline)-MIN(dateline))/86400 AS runtime FROM ".DB::table(getposttable()));
	$statvars['posts'] = $postsinfo['posts'];
	$runtime= $postsinfo['runtime'];

	@$statvars['postsaddavg'] = round($statvars['posts'] / $runtime);

	@$statvars['mempostavg'] = sprintf ("%01.2f", $statvars['posts'] / $statvars['members']);

	$statvars['forums'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE type='forum' AND status<>'3'");

	$statvars['hotforum'] = DB::fetch_first("SELECT posts, threads, fid, name FROM ".DB::table('forum_forum')." WHERE status='1' ORDER BY posts DESC LIMIT 1");

	$statvars['threads'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread'));

	$statvars['postsaddtoday'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable())." WHERE dateline>='".(TIMESTAMP - 86400)."' AND invisible='0'");

	@$statvars['threadreplyavg'] = sprintf ("%01.2f", ($statvars['posts'] - $statvars['threads']) / $statvars['threads']);

	$statvars['membersaddtoday'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE regdate>='".(TIMESTAMP - 86400)."'");

	@$statvars['activeindex'] = round(($statvars['membersaddavg'] / $statvars['members'] + $statvars['postsaddavg'] / $statvars['posts']) * 1500 + $statvars['threadreplyavg'] * 10 + $statvars['mempostavg'] * 1 + $statvars['mempostpercent'] / 10);

	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);
	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['basic'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function gettimelimit_rank($type, $timelimit, $limit = 0) {
	$data = array();
	$limit = intval($limit);
	if($type == 'forums') {
		$query = DB::query("SELECT DISTINCT(p.fid) AS fid, f.name, COUNT(pid) AS posts FROM ".DB::table(getposttable())." p
			LEFT JOIN ".DB::table('forum_forum')." f USING (fid)
			WHERE dateline>='$timelimit' AND invisible='0' AND authorid>'0'
			GROUP BY p.fid ORDER BY posts DESC LIMIT 0, $limit");
	} elseif($type == 'member') {
		$query = DB::query("SELECT DISTINCT(author) AS username, COUNT(pid) AS posts
			FROM ".DB::table(getposttable())." WHERE dateline>='$timelimit' AND invisible='0' AND authorid>'0'
			GROUP BY author
			ORDER BY posts DESC
			LIMIT 0, 20");
	}
	while($row = DB::fetch($query)) {
		$data[] = $row;
	}
	return $data;
}
function getstatvars_forumsrank() {
	global $_G;

	$statvars = array();
	$threads = $posts = array();
	$query = DB::query("SELECT fid, name, threads FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group' ORDER BY threads DESC");
	while($forum = DB::fetch($query)) {
		$statvars['forums']++;
		$threads[] = $forum;
	}
	$statvars['forums'] = $statvars['forums'] ? $statvars['forums'] : 20;

	$query = DB::query("SELECT fid, name, posts FROM ".DB::table('forum_forum')." WHERE status='1' AND type<>'group' ORDER BY posts DESC LIMIT 0, $statvars[forums]");
	while($forum = DB::fetch($query)) {
		$posts[] = $forum;
	}
	$thismonth = gettimelimit_rank('forums', $_G['timestamp'] - 86400 * 30, $statvars['forums']);
	$today = gettimelimit_rank('forums', $_G['timestamp'] - 86400, $statvars['forums']);

	for($i = 0; $i < $statvars['forums']; $i++) {
		$bgclass = $i % 2 ? ' class="alt"' : '';
		$forumsrank[0] .= $threads[$i]['name'] || $posts[$i]['name'] ? "<tr".$bgclass."><td class=\"stat_subject\"><a href=\"forumdisplay.php?fid={$threads[$i]['fid']}\" target=\"_blank\">{$threads[$i]['name']}</a></td><td class=\"stat_num\">{$threads[$i]['threads']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"forumdisplay.php?fid={$posts[$i]['fid']}\" target=\"_blank\">{$posts[$i]['name']}</a></td><td class=\"stat_num\">{$posts[$i]['posts']}</td>\n" : '';
		$forumsrank[1] .= $thismonth[$i]['name'] || $today[$i]['name'] ? "<tr".$bgclass."><td class=\"stat_subject\"><a href=\"forumdisplay.php?fid={$thismonth[$i]['fid']}\" target=\"_blank\">{$thismonth[$i]['name']}</a></td><td class=\"stat_num\">{$thismonth[$i]['posts']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"forumdisplay.php?fid={$today[$i]['fid']}\" target=\"_blank\">{$today[$i]['name']}</a></td><td class=\"stat_num\">{$today[$i]['posts']}</td></tr>\n" : '';
	}
	$statvars['forumsrank'] = $forumsrank;
	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['forumsrank'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_threadsrank() {
	global $_G;

	$statvars = array();
	$threadsrank = '';
	$threadview = $threadreply = array();
	$query = DB::query("SELECT views, tid, subject FROM ".DB::table('forum_thread')." WHERE displayorder>='0' ORDER BY views DESC LIMIT 0, 20");
	while($thread = DB::fetch($query)) {
		$thread['subject'] = cutstr($thread['subject'], 45);
		$threadview[] = $thread;
	}

	$query = DB::query("SELECT replies, tid, subject FROM ".DB::table('forum_thread')." WHERE displayorder>='0' ORDER BY replies DESC LIMIT 0, 20");
	while($thread = DB::fetch($query)) {
		$thread['subject'] = cutstr($thread['subject'], 50);
		$threadreply[] = $thread;
	}

	for($i = 0; $i < 20; $i++) {
		$bgclass = $i % 2 ? ' class="alt"' : '';
		$threadsrank .= "<tr".$bgclass."><td class=\"stat_subject\"><a href=\"forum.php?mod=viewthread&tid={$threadview[$i]['tid']}\">{$threadview[$i]['subject']}</a>&nbsp;</td><td class=\"stat_num\">{$threadview[$i]['views']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"forum.php?mod=viewthread&tid={$threadreply[$i]['tid']}\">{$threadreply[$i]['subject']}</a><td class=\"stat_num\">{$threadreply[$i]['replies']}</td></tr>\n";
	}

	$statvars['threadsrank'] = $threadsrank;
	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['threadsrank'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_postsrank() {
	global $_G;

	$statvars = array();
	$postsrank = '';
	$posts = $digestposts = $thismonth = $today = array();

	$query = DB::query("SELECT m.username, m.uid, mc.posts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
		ORDER BY mc.posts DESC
		LIMIT 0, 20");
	while($member = DB::fetch($query)) {
		$posts[] = $member;
	}

	$query = DB::query("SELECT m.username, m.uid, mc.digestposts
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
		ORDER BY mc.digestposts DESC
		LIMIT 0, 20");
	while($member = DB::fetch($query)) {
		$digestposts[] = $member;
	}
	$thismonth = gettimelimit_rank('member', $_G['timestamp'] - 86400 * 30);
	$today = gettimelimit_rank('member', $_G['timestamp'] - 86400);

	for($i = 0; $i < 20; $i++) {
		$bgclass = $i % 2 ? ' class="alt"' : '';
		@$postsrank .= "<tr".$bgclass."><td class=\"stat_subject\"><a href=\"space.php?username=".rawurlencode($posts[$i]['username'])."\" target=\"_blank\">{$posts[$i]['username']}</a>&nbsp;</td><td class=\"stat_num\">{$posts[$i]['posts']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"space.php?username=".rawurlencode($digestposts[$i]['username'])."\" target=\"_blank\">{$digestposts[$i]['username']}</a></td><td class=\"stat_num\">{$digestposts[$i]['digestposts']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"space.php?username=".rawurlencode($thismonth[$i]['username'])."\" target=\"_blank\">{$thismonth[$i]['username']}</a></td><td class=\"stat_num\">{$thismonth[$i]['posts']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"space.php?username=".rawurlencode($today[$i]['username'])."\" target=\"_blank\">{$today[$i]['username']}</a></td><td class=\"stat_num\">{$today[$i]['posts']}</td></tr>\n";
	}
	$statvars['postsrank'] = $postsrank;
	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['postsrank'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);
	return $statvars;
}

function getstatvars_creditsrank() {
	global $_G;

	$statvars = array();
	$creditsrank = '';
	$extcredits = $_G['setting']['extcredits'];
	foreach($extcredits as $id => $credit) {
		$query = DB::query("SELECT m.username, m.uid, mc.extcredits$id AS credits
			FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
			ORDER BY extcredits$id DESC
			LIMIT 0, 20");
		while($member = DB::fetch($query)) {
			$extendedcredits[$id][] = $member;
		}
	}

	$statvars['extendedcredits'] = $extendedcredits;

	if(is_array($extendedcredits)) {
		$extcreditfirst = 0;
		$extcreditkeys = $creditsrank = array();
		foreach($extendedcredits as $key => $extendedcredit) {
			$max = $extendedcredit[0]['credits'];
			!$extcreditfirst && $extcreditfirst = $key;
			$extcreditkeys[] = $key;
			foreach($extendedcredit as $i => $members) {
				@$width = intval(370 * $members['credits'] / $max);
				$width += 2;
				$creditsrank[$key] .= "<tr><td width=\"100\"><a href=\"space.php?uid=$members[uid]\" target=\"_blank\">$members[username]</a></strong></td>\n".
					"<td style=\"border-right:none\"><div class=\"pbg\"><div class=\"pbr\" style=\"width: {$width}px; background-color: #5AAF4A;\">&nbsp;</div></div>&nbsp; <strong>$members[credits]</strong></td></tr>\n";
			}
		}
		$extcredit = empty($extcredit) || !in_array($extcredit, $extcreditkeys) ? $extcreditfirst : intval($extcredit);
	}

	$statvars['creditsrank'] = $creditsrank;

	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['creditsrank'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);
	return $statvars;
}

function getstatvars_trade() {
	global $_G;
	$statvars = array();
	$query = DB::query("SELECT subject, tid, pid, seller, sellerid, SUM(tradesum) as tradesum
		FROM ".DB::table('forum_trade')."
		WHERE tradesum>0
		GROUP BY sellerid
		ORDER BY tradesum DESC
		LIMIT 10");
	while($data = DB::fetch($query)) {
		$tradesums[] = $data;
	}
	$statvars['tradesums'] = $tradesums;

	$query = DB::query("SELECT subject, tid, pid, seller, sellerid, SUM(credittradesum) as credittradesum
	FROM ".DB::table('forum_trade')."
	WHERE credittradesum>0
	GROUP BY sellerid
	ORDER BY credittradesum DESC
	LIMIT 10");
	while($data = DB::fetch($query)) {
		$credittradesums[] = $data;
	}
	$statvars['credittradesums'] = $credittradesums;

	$query = DB::query("SELECT subject, tid, pid, seller, sellerid, SUM(totalitems) as totalitems
	FROM ".DB::table('forum_trade')."
	WHERE totalitems>0
	GROUP BY sellerid
	ORDER BY totalitems DESC
	LIMIT 10");
	while($data = DB::fetch($query)) {
		$totalitems[] = $data;
	}
	$statvars['totalitems'] = $totalitems;

	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);
	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['trade'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_onlinetime() {
	global $_G;
	$statvars = array();
	$onlines = '';
	$total = $thismonth = array();

	$query = DB::query("SELECT o.uid, m.username, o.total AS time
		FROM ".DB::table('common_onlinetime')." o
		LEFT JOIN ".DB::table('common_member')." m USING (uid)
		ORDER BY o.total DESC LIMIT 20");
	while($online = DB::fetch($query)) {
		$online['time'] = round($online['time'] / 60, 2);
		$total[] = $online;
	}

	$dateline = strtotime(gmdate('Y-m-01', TIMESTAMP));
	$query = DB::query("SELECT o.uid, m.username, o.thismonth AS time
		FROM ".DB::table('common_onlinetime')." o, ".DB::table('common_member')." m, ".DB::table('common_member_status')." ms
		WHERE o.uid=m.uid AND ms.uid=m.uid AND ms.lastactivity>='$dateline'
		ORDER BY o.thismonth DESC LIMIT 20");
	while($online = DB::fetch($query)) {
		$online['time'] = round($online['time'] / 60, 2);
		$thismonth[] = $online;
	}

	for($i = 0; $i < 20; $i++) {
		$bgclass = $i % 2 ? ' class="alt"' : '';
		@$onlines .= "<tr".$bgclass."><td class=\"stat_subject\"><a href=\"space.php?uid={$total[$i]['uid']}\" target=\"_blank\">{$total[$i]['username']}</a>&nbsp;</td><td class=\"stat_num\">{$total[$i]['time']}</td>\n".
			"<td class=\"stat_subject\"><a href=\"space.php?uid={$thismonth[$i]['uid']}\" target=\"_blank\">{$thismonth[$i]['username']}</a></td><td class=\"stat_num\">{$thismonth[$i]['time']}</td></tr>\n";
	}

	$statvars['onlines'] = $onlines;

	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['onlinetime'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_team() {
	global $_G;

	$statvars = array();
	$team = array();

	$forums = $moderators = $members = $fuptemp = array();
	$categories = array(0 => array('fid' => 0, 'fup' => 0, 'type' => 'group', 'name' => $_G['setting']['bbname']));

	$uids = 0;
	$query = DB::query("SELECT fid, uid
		FROM ".DB::table('forum_moderator')."
		WHERE inherited='0'
		ORDER BY displayorder");
	while($moderator = DB::fetch($query)) {
		$moderators[$moderator['fid']][] = $moderator['uid'];
		$uids .= ','.$moderator['uid'];
	}

	if($_G['setting']['oltimespan']) {
		$oltimeadd1 = ', o.thismonth AS thismonthol, o.total AS totalol';
		$oltimeadd2 = "LEFT JOIN ".DB::table('common_onlinetime')." o ON o.uid=m.uid";
	} else {
		$oltimeadd1 = $oltimeadd2 = '';
	}
	$totaloffdays = $totalol = $totalthismonthol = 0;
	$query = DB::query("SELECT m.uid, m.username, m.adminid, ms.lastactivity, m.credits, mc.posts $oltimeadd1
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_status')." ms ON ms.uid=m.uid
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
		$oltimeadd2
		WHERE m.uid IN ($uids) OR m.adminid IN (1, 2) ORDER BY m.adminid");

	$admins = array();
	while($member = DB::fetch($query)) {
		if($member['adminid'] == 1 || $member['adminid'] == 2) {
			$admins[] = $member['uid'];
		}

		$member['offdays'] = intval((TIMESTAMP - $member['lastactivity']) / 86400);
		$totaloffdays += $member['offdays'];

		if($_G['setting']['oltimespan']) {
			$member['totalol'] = round($member['totalol'] / 60, 2);
			$member['thismonthol'] = gmdate('Yn', $member['lastactivity']) == gmdate('Yn', TIMESTAMP) ? round($member['thismonthol'] / 60, 2) : 0;
			$totalol += $member['totalol'];
			$totalthismonthol += $member['thismonthol'];
		}

		$members[$member['uid']] = $member;
		$uids .= ','.$member['uid'];
	}

	$totalthismonthposts = 0;
	$query = DB::query("SELECT authorid, COUNT(*) AS posts FROM ".DB::table(getposttable())."
		WHERE dateline>=$_G[timestamp]-86400*30 AND authorid IN ($uids) AND invisible='0' GROUP BY authorid");
	while($post = DB::fetch($query)) {
		$members[$post['authorid']]['thismonthposts'] = $post['posts'];
		$totalthismonthposts += $post['posts'];
	}

	$totalmodposts = $totalmodactions = 0;
	if($_G['setting']['modworkstatus']) {
		$starttime = gmdate("Y-m-1", TIMESTAMP + $_G['setting']['timeoffset'] * 3600);
		$query = DB::query("SELECT uid, SUM(count) AS actioncount FROM ".DB::table('forum_modwork')."
			WHERE dateline>='$starttime' GROUP BY uid");
		while($member = DB::fetch($query)) {
			$members[$member['uid']]['modactions'] = $member['actioncount'];
			$totalmodactions += $member['actioncount'];
		}
	}

	$query = DB::query("SELECT fid, fup, type, name, inheritedmod
		FROM ".DB::table('forum_forum')." WHERE status='1' ORDER BY type, displayorder");
	while($forum = DB::fetch($query)) {
		$forum['moderators'] = count($moderators[$forum['fid']]);
		switch($forum['type']) {
			case 'group':
				$categories[$forum['fid']] = $forum;
				$forums[$forum['fid']][$forum['fid']] = $forum;
				$catfid = $forum['fid'];
				break;
			case 'forum':
				$forums[$forum['fup']][$forum['fid']] = $forum;
				$fuptemp[$forum['fid']] = $forum['fup'];
				$catfid = $forum['fup'];
				break;
			case 'sub':
				$forums[$fuptemp[$forum['fup']]][$forum['fid']] = $forum;
				$catfid = $fuptemp[$forum['fup']];
				break;
		}
		if(!empty($moderators[$forum['fid']])) {
			$categories[$catfid]['moderating'] = 1;
		}
	}

	foreach($categories as $fid => $category) {
		if(empty($category['moderating'])) {
			unset($categories[$fid]);
		}
	}

	$team = array	(
		'categories' => $categories,
		'forums' => $forums,
		'admins' => $admins,
		'moderators' => $moderators,
		'members' => $members,
		'avgoffdays' => @($totaloffdays / count($members)),
		'avgthismonthposts' => @($totalthismonthposts / count($members)),
		'avgtotalol' => @($totalol / count($members)),
		'avgthismonthol' => @($totalthismonthol / count($members)),
		'avgmodactions' => @($totalmodactions / count($members)),
	);

	loadcache('usergroups');
	if(is_array($team)) {
		foreach($team['members'] as $uid => $member) {
			@$member['thismonthposts'] = intval($member['thismonthposts']);
			@$team['members'][$uid]['offdays'] = $member['offdays'] > $team['avgoffdays'] ? '<b><i>'.$member['offdays'].'</i></b>' : $member['offdays'];
			@$team['members'][$uid]['thismonthposts'] = $member['thismonthposts'] < $team['avgthismonthposts'] / 2 ? '<b><i>'.$member['thismonthposts'].'</i></b>' : $member['thismonthposts'];
			@$team['members'][$uid]['lastactivity'] = dgmdate($member['lastactivity'] + $timeoffset * 3600, 'd');
			@$team['members'][$uid]['thismonthol'] = $member['thismonthol'] < $team['avgthismonthol'] / 2 ? '<b><i>'.$member['thismonthol'].'</i></b>' : $member['thismonthol'];
			@$team['members'][$uid]['totalol'] = $member['totalol'] < $team['avgtotalol'] / 2 ? '<b><i>'.$member['totalol'].'</i></b>' : $member['totalol'];
			@$team['members'][$uid]['modposts'] = $member['modposts'] < $team['avgmodposts'] / 2 ? '<b><i>'.intval($member['modposts']).'</i></b>' : intval($member['modposts']);
			@$team['members'][$uid]['modactions'] = $member['modactions'] < $team['avgmodactions'] / 2 ? '<b><i>'.intval($member['modactions']).'</i></b>' : intval($member['modactions']);
			@$team['members'][$uid]['grouptitle'] = $_G['cache']['usergroups'][$member[adminid]]['grouptitle'];
		}
	}

	$statvars['team'] = $team;
	$statvars['lastupdate'] = dgmdate(TIMESTAMP);
	$statvars['nextupdate'] = dgmdate(TIMESTAMP + CACHE_TIME);

	$statvars['lastupdated'] = TIMESTAMP;
	$_G['cache']['statvars']['team'] = $statvars;
	save_syscache('statvars', $_G['cache']['statvars']);

	return $statvars;
}

function getstatvars_modworks() {
	global $_G;
	$statvars = array();

	$before = $_G['gp_before'];
	$before = (isset($before) && $before > 0 && $before <=  $_G['setting']['maxmodworksmonths']) ? intval($before) : 0 ;

	list($now['year'], $now['month'], $now['day']) = explode("-", dgmdate(TIMESTAMP, 'Y-n-j'));

	$monthlinks = array();
	$uid = !empty($_G['gp_uid']) ? $_G['gp_uid'] : 0;
	for($i = 0; $i <= $_G['setting']['maxmodworksmonths']; $i++) {
		$month = date("Y-m", mktime(0, 0, 0, $now['month'] - $i, 1, $now['year']));
		if($i != $before) {
			$monthlinks[$i] = "<li><a href=\"misc.php?mod=stat&op=modworks&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
		} else {
			$thismonth = $month;
			$starttime = $month.'-01';
			$endtime = date("Y-m-01", mktime(0, 0, 0, $now['month'] - $i + 1 , 1, $now['year']));
			$daysofmonth = date("t", mktime(0, 0, 0, $now['month'] - $i , 1, $now['year']));
			$monthlinks[$i] = "<li class=\"xw1 a\"><a href=\"misc.php?mod=stat&op=modworks&before=$i&uid=$uid\" hidefocus=\"true\">$month</a></li>";
		}
	}
	$statvars['monthlinks'] = $monthlinks;

	$expiretime = date('Y-m', mktime(0, 0, 0, $now['month'] - $_G['setting']['maxmodworksmonths'] - 1, 1, $now['year']));
	$daysofmonth = empty($before) ? $now['day'] : $daysofmonth;

	$mergeactions = array('OPN' => 'CLS', 'ECL' => 'CLS', 'UEC' => 'CLS', 'EOP' => 'CLS', 'UEO' => 'CLS',
		'UDG' => 'DIG', 'EDI' =>'DIG', 'UED' => 'DIG', 'UST' => 'STK', 'EST' => 'STK',	'UES' => 'STK',
		'DLP' => 'DEL',	'PRN' => 'DEL',	'UDL' => 'DEL',	'UHL' => 'HLT',	'EHL' => 'HLT',	'UEH' => 'HLT',
		'SPL' => 'MRG', 'ABL' => 'EDT', 'RBL' => 'EDT');

	if($uid) {

		$uid = $_G['gp_uid'];
		$member = DB::fetch_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$uid' AND adminid>'0'");
		if(!$member) {
			showmessage('member_not_found');
		}

		$modactions = $totalactions = array();
		for($i = 1; $i <= $daysofmonth; $i++) {
			$modactions[sprintf("$thismonth-%02d", $i)] = array();
		}

		$query = DB::query("SELECT * FROM ".DB::table('forum_modwork')." WHERE uid='$uid' AND dateline>='{$starttime}' AND dateline<'$endtime'");
		while($data = DB::fetch($query)) {
			if(isset($mergeactions[$data['modaction']])) {
				$data['modaction'] = $mergeactions[$data['modaction']];
			}
			$modactions[$data['dateline']][$data['modaction']]['count'] += $data['count'];
			$modactions[$data['dateline']][$data['modaction']]['posts'] += $data['posts'];
			$totalactions[$data['modaction']]['count'] += $data['count'];
			$totalactions[$data['modaction']]['posts'] += $data['posts'];
		}
		$statvars['modactions'] = $modactions;

	} else {

		$members = array();
		$uids = $totalmodactions = 0;

		$query = DB::query("SELECT uid, username, adminid FROM ".DB::table('common_member')." WHERE adminid IN (1, 2, 3) ORDER BY adminid, uid");
		while($member = DB::fetch($query)) {
			$members[$member['uid']] = $member;
			$uids .= ', '.$member['uid'];
		}

		$query = DB::query("SELECT uid, modaction, SUM(count) AS count, SUM(posts) AS posts
				FROM ".DB::table('forum_modwork')."
				WHERE uid IN ($uids) AND dateline>='$starttime' AND dateline<'$endtime' GROUP BY uid, modaction");

		while($data = DB::fetch($query)) {
			if(isset($mergeactions[$data['modaction']])) {
				$data['modaction'] = $mergeactions[$data['modaction']];
			}
			$members[$data['uid']]['total'] += $data['count'];
			$totalmodactioncount += $data['count'];

			$members[$data['uid']][$data['modaction']]['count'] += $data['count'];
			$members[$data['uid']][$data['modaction']]['posts'] += $data['posts'];

		}

		$avgmodactioncount = @($totalmodactioncount / count($members));
		foreach($members as $id => $member) {
			$members[$id]['totalactions'] = intval($members[$id]['totalactions']);
			$members[$id]['username'] = ($members[$id]['total'] < $avgmodactioncount / 2) ? ('<b><i>'.$members[$id]['username'].'</i></b>') : ($members[$id]['username']);
		}

		if(!empty($before)) {
			DB::query("DELETE FROM ".DB::table('forum_modwork')." WHERE dateline<'{$expiretime}-01'", 'UNBUFFERED');
		} else {
			$members['thismonth'] = $starttime;
			$members['lastupdate'] = TIMESTAMP;
			unset($members['lastupdate'], $members['thismonth']);
		}
	}
	$statvars['members'] = $members;
	$modactioncode = lang('forum/modaction');

	$bgarray = array();
	foreach($modactioncode as $key => $val) {
		if(isset($mergeactions[$key])) {
			unset($modactioncode[$key]);
		}
	}

	$statvars['modactioncode'] = $modactioncode;
	$tdcols = count($modactioncode) + 1;
	$tdwidth = floor(90 / ($tdcols - 1)).'%';
	$statvars['tdwidth'] = $tdwidth;
	$statvars['uid'] = $uid;
	return $statvars;
}

function getstatvars_memberlist() {
	global $_G;
	$statvars = array();
	$order = $_G['gp_order'];
	$order = isset($order) && in_array($order, array('uid','credits','regdate', 'gender','username','posts','lastvisit')) ? $order : '';
	$orderadd = $sql = $num = '';

	$order = empty($order) ? '' : $order;
	switch($order) {
		case 'credits': $orderadd = "ORDER BY credits"; break;
		case 'gender': 	$orderadd = "ORDER BY gender"; break;
		case 'regdate': $orderadd = "ORDER BY regdate"; break;
		case 'username': $orderadd = "ORDER BY username"; break;
		case 'posts': $orderadd = "ORDER BY posts"; break;
		case 'lastvisit': $orderadd = "ORDER BY lastvisit"; break;
		case 'uid': $orderadd = "ORDER BY m.uid"; break;
		default: $orderadd = 'ORDER BY uid'; $order = 'uid'; break;
	}
	if($_G['gp_asc']) {
		$orderadd .= " ASC";
	} else {
		$orderadd .= " DESC";
	}
	$srchmem = $_G['gp_srchmem'];
	$sql = !empty($srchmem) ? " WHERE username LIKE '".str_replace(array('_', '%'), array('\_', '\%'), $srchmem)."%'" : '';
	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." $sql");

	$page = $_G['setting']['membermaxpages'] && $_G['gp_page'] > $_G['setting']['membermaxpages'] ? 1 : $_G['gp_page'];
	if(empty($page)) {
		$page = 1;
	}
	$start_limit = ($page - 1) * $_G['setting']['memberperpage'];

	$multipage = multi($num, $_G['setting']['memberperpage'], $page, "misc.php?mod=stat&op=memberlist&srchmem=".rawurlencode($srchmem)."&order=$order&asc={$_G[gp_asc]}", $_G['setting']['membermaxpages']);

	$memberlist = array();
	$query = DB::query("SELECT m.uid, m.username, mp.gender, m.email, m.regdate, ms.lastvisit, mc.posts, m.credits
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_profile')." mp ON mp.uid=m.uid
		LEFT JOIN ".DB::table('common_member_status')." ms ON ms.uid=m.uid
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
		$sql $orderadd LIMIT $start_limit, {$_G['setting']['memberperpage']}");
	while($member = DB::fetch($query)) {
		$member['usernameenc'] = rawurlencode($member['username']);
		$member['regdate'] = dgmdate($member['regdate']);
		$member['lastvisit'] = dgmdate($member['lastvisit']);
		$memberlist[] = $member;
	}

	$statvars['memberlist'] = $memberlist;
	$statvars['multipage'] = $multipage;

	return $statvars;
}

function getstatvars_forumstat($fid) {
	global $_G;
	$xml = "<chart>\n";
	$statvars = array();
	$monthdays = array('31', '28', '31', '30', '31', '30', '31', '31', '30', '31', '30', '31');
	if(!$fid) {
		$query = DB::query("SELECT fid, type, name, posts FROM ".DB::table('forum_forum')." WHERE status<>'3' AND type<>'group'");
		$forums = array();
		while($forum = DB::fetch($query)) {
			$forums[] = $forum;
		}
		$statvars['forums'] = $forums;
	} else {
		$foruminfo = DB::fetch_first("SELECT fid, name, posts, threads, todayposts FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
		$statvars['foruminfo'] = $foruminfo;

		$current_date = $end_date = date('Y-m-d');
		$current_month = $end_month = date('Y-m');
		$current_month_start = $end_month_start = $current_month . '-01';
		if($_G['gp_month']) {
			$end_month = trim($_G['gp_month']);
			$month = substr($end_month, strpos($end_month, '-') + 1);
			$end_date = $end_month . '-' . $monthdays[$month - 1];
			$end_month_start = $end_month . '-' . '01';
		}
		$statvars['month'] = $end_month;
		$query = DB::query("SELECT logdate, fid, value
			FROM ".DB::table('forum_statlog')."
			WHERE logdate>='$end_month_start' AND logdate<='$end_date' AND type='1' AND fid='$fid'
			ORDER BY logdate ASC");
		$logs = array();
		$xml .= "<xaxis>\n";
		$xmlvalue = '';
		$xaxisindex = 0;
		while($log = DB::fetch($query)) {
			$logs[] = $log;
			list($yyyy, $mm, $dd) = explode('-', $log['logdate']);
			$xaxisindex++;
			$xml .= "<value xid=\"{$xaxisindex}\">{$mm}{$dd}</value>\n";
			$xmlvalue .= "<value xid=\"{$xaxisindex}\">{$log['value']}</value>\n";
		}
		$xml .= "</xaxis>\n";
		$xml .= "<graphs>\n";
		$xml .= "<graph gid=\"0\" title=\"".diconv(lang('spacecp', 'do_stat_post_number'), CHARSET, 'UTF-8')."\">\n";
		$xml .= $xmlvalue;
		$xml .= "</graph>\n";
		$xml .= "</graphs>\n";
		$xml .= "</chart>\n";
		if($_G['gp_xml']) {
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
			@header("Content-type: application/xml; charset=utf-8");
			echo $xml;
			exit;
		}
		$statvars['logs'] = $logs;

		$mindate = DB::result_first("SELECT MIN(logdate) FROM ".DB::table('forum_statlog')." WHERE fid='$fid'");
		list($minyear, $minmonth, $minday) = explode('-', $mindate);
		$minmonth = $minyear . '-' . $minmonth;
		$month = $minmonth;
		$monthlist = array();
		while(datecompare($month, $current_month) <= 0) {
			$monthlist[] = $month;
			$month = getnextmonth($month);
		}
		$statvars['monthlist'] = $monthlist;

		$query = DB::query("SELECT logdate, `value` FROM ".DB::table('forum_statlog')." WHERE fid='$fid' AND type='1'");
		$monthposts = array();
		while($data = DB::fetch($query)) {
			list($year, $month, $day) = explode('-', $data['logdate']);
			if(isset($monthposts[$year.'-'.$month])) {
				$monthposts[$year.'-'.$month] += $data['value'];
			} else {
				$monthposts[$year.'-'.$month] = $data['value'];
			}
		}
		$statvars['monthposts'] = $monthposts;
	}
	$statvars['statuspara'] = "path=&settings_file=data/stat_setting.xml&data_file=".urlencode("misc.php?mod=stat&op=forumstat&fid=$fid&month={$_G['gp_month']}&xml=1");
	return $statvars;
}

function datecompare($date1, $date2) {
	$year1 = $month1 = $day1 = 1;
	$year2 = $month2 = $day2 = 1;
	list($year1, $month1, $day1) = explode('-', $date1);
	list($year2, $month2, $day2) = explode('-', $date2);

	return mktime(0, 0, 0, $month1, $day1, $year1) - mktime(0, 0, 0, $month2, $day2, $year2);
}

function getnextmonth($monthdate) {
	list($year, $month) = explode('-', $monthdate);
	$month = $month + 1;
	if($month > 12) {
		$month = 1;
		$year = $year + 1;
	}
	$month = sprintf("%02d", $month);
	return $year . '-' . $month;
}