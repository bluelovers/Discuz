<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_forumdisplay.php 22941 2011-06-07 01:17:43Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

if($_G['forum']['redirect']) {
	dheader("Location: {$_G[forum][redirect]}");
} elseif($_G['forum']['type'] == 'group') {
	dheader("Location: forum.php?gid=$_G[fid]");
} elseif(empty($_G['forum']['fid'])) {
	showmessage('forum_nonexistence', NULL);
}

$_G['action']['fid'] = $_G['fid'];

$_G['gp_specialtype'] = isset($_G['gp_specialtype']) ? $_G['gp_specialtype'] : '';
$_G['gp_dateline'] = isset($_G['gp_dateline']) ? intval($_G['gp_dateline']) : 0;
$_G['gp_digest'] = isset($_G['gp_digest']) ? 1 : '';
$_G['gp_archiveid'] = isset($_G['gp_archiveid']) ? intval($_G['gp_archiveid']) : 0;

$showoldetails = isset($_G['gp_showoldetails']) ? $_G['gp_showoldetails'] : '';
switch($showoldetails) {
	case 'no': dsetcookie('onlineforum', ''); break;
	case 'yes': dsetcookie('onlineforum', 1, 31536000); break;
}

$_G['forum']['name'] = strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name'];
$_G['forum']['extra'] = unserialize($_G['forum']['extra']);
if(!is_array($_G['forum']['extra'])) {
	$_G['forum']['extra'] = array();
}


$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();
$forumarchive = array();
if($_G['forum']['archive']) {
	$query = DB::query("SELECT * FROM ".DB::table('forum_forum_threadtable')." WHERE fid='{$_G['fid']}'");
	while($archive = DB::fetch($query)) {
		$forumarchive[$archive['threadtableid']] = array(
			'displayname' => htmlspecialchars($threadtable_info[$archive['threadtableid']]['displayname']),
			'threads' => $archive['threads'],
			'posts' => $archive['posts'],
		);
		if(empty($forumarchive[$archive['threadtableid']]['displayname'])) {
			$forumarchive[$archive['threadtableid']]['displayname'] = lang('forum/thread', 'forum_archive').' '.$archive['threadtableid'];
		}
	}
}


$forum_up = $_G['cache']['forums'][$_G[forum][fup]];
if($_G['forum']['type'] == 'forum') {
	$fgroupid = $_G['forum']['fup'];
	if(empty($_G['gp_archiveid'])) {
		$navigation = '<em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> <em>&rsaquo;</em> <a href="forum.php?gid='.$forum_up['fid'].'">'.$forum_up['name'].'</a><em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['forum']['fid'].'">'.$_G['forum']['name'].'</a>';
	} else {
		$navigation = '<em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> <em>&rsaquo;</em> '.'<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a> <em>&rsaquo;</em> '.$forumarchive[$_G['gp_archiveid']]['displayname'];
	}
	$seodata = array('forum' => $_G['forum']['name'], 'fgroup' => $forum_up['name'], 'page' => intval($_G['gp_page']));
} else {
	$fgroupid = $forum_up['fup'];
	if(empty($_G['gp_archiveid'])) {
		$forum_top =  $_G['cache']['forums'][$forum_up[fup]];
		$navigation = '<em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> <em>&rsaquo;</em> <a href="forum.php?gid='.$forum_top['fid'].'">'.$forum_top['name'].'</a><em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$forum_up['fid'].'">'.$forum_up['name'].'</a><em>&rsaquo;</em> '.$_G['forum']['name'];
	} else {
		$navigation = '<em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> <em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['forum']['fup'].'">'.$forum_up['name'].'</a> <em>&rsaquo;</em> '.'<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a> <em>&rsaquo;</em> '.$forumarchive[$_G['gp_archiveid']]['displayname'];
	}
	$seodata = array('forum' => $_G['forum']['name'], 'fup' => $forum_up['name'], 'fgroup' => $forum_top['name'], 'page' => intval($_G['gp_page']));
}

$rssauth = $_G['rssauth'];
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="'.$_G['setting']['bbname'].' - '.$navtitle.'" href="'.$_G['siteurl'].'forum.php?mod=rss&fid='.$_G['fid'].'&amp;auth='.$rssauth."\" />\n") : '';

$forumseoset = array(
	'seotitle' => $_G['forum']['seotitle'],
	'seokeywords' => $_G['forum']['keywords'],
	'seodescription' => $_G['forum']['seodescription']
);

$seotype = 'threadlist';
if($_G['forum']['status'] == 3) {
	$navtitle = get_title_page($_G['forum']['name'], $_G['page']).' - '.$_G['setting']['navs'][3]['navname'];
	$metakeywords = $_G['forum']['metakeywords'];
	$metadescription = $_G['forum']['description'];
	$_G['seokeywords'] = $_G['setting']['seokeywords']['group'];
	$_G['seodescription'] = $_G['setting']['seodescription']['group'];
	$action = getgpc('action') ? $_G['gp_action'] : 'list';
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == -1) {
		showmessage('forum_not_group', 'group.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', 'forum.php?mod=group&fid='.$_G['fid']);
	} elseif($status == 3) {
		showmessage('forum_group_moderated', 'forum.php?mod=group&fid='.$_G['fid']);
	}
	$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
	$_G['grouptypeid'] = $_G['forum']['fup'];
	$_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');

	$nav = get_groupnav($_G['forum']);
	$groupnav = $nav['nav'];
	$onlinemember = grouponline($_G['fid']);
	$groupmanagers = $_G['forum']['moderators'];
	$groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'));
	$seotype = 'grouppage';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
	$seodata['gdes'] = $_G['forum']['description'];
	$forumseoset = array();
}
$_G['forum']['banner'] = get_forumimg($_G['forum']['banner']);

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata, $forumseoset);

if(!$navtitle) {
	$navtitle = get_title_page($_G['forum']['name'], $_G['page']);
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!empty($_G['gp_typeid']) && !empty($_G['forum']['threadtypes']['types'][$_G['gp_typeid']])) {
	$navtitle = strip_tags($_G['forum']['threadtypes']['types'][$_G['gp_typeid']]).' - '.$navtitle;
}
if(!$metakeywords) {
	$metakeywords = $_G['forum']['name'];
}
if(!$metadescription) {
	$metadescription = $_G['forum']['name'];
}
if($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm']) && !$_G['forum']['allowview']) {
	showmessagenoperm('viewperm', $_G['fid'], $_G['forum']['formulaperm']);
} elseif($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password']) {
	if($_G['gp_action'] == 'pwverify') {
		if($_G['gp_pw'] != $_G['forum']['password']) {
			showmessage('forum_passwd_incorrect', NULL);
		} else {
			dsetcookie('fidpw'.$_G['fid'], $_G['gp_pw']);
			showmessage('forum_passwd_correct', "forum.php?mod=forumdisplay&fid=$_G[fid]");
		}
	} elseif($_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
		include template('forum/forumdisplay_passwd');
		exit();
	}
}

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'forum_rules_'.$_G['fid']) === FALSE) {
	$collapse['forum_rules'] = '';
	$collapse['forum_rulesimg'] = 'no';
} else {
	$collapse['forum_rules'] = 'display: none';
	$collapse['forum_rulesimg'] = 'yes';
}

$forumlastvisit = 0;
if(empty($_G['forum']['picstyle']) && isset($_G['cookie']['forum_lastvisit']) && strexists($_G['cookie']['forum_lastvisit'], 'D_'.$_G['fid'])) {
	preg_match('/D\_'.$_G['fid'].'\_(\d+)/', $_G['cookie']['forum_lastvisit'], $a);
	$forumlastvisit = $a[1];
	unset($a);
}
dsetcookie('forum_lastvisit', preg_replace("/D\_".$_G['fid']."\_\d+/", '', $_G['cookie']['forum_lastvisit']).'D_'.$_G['fid'].'_'.TIMESTAMP, 604800);

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();

$threadtable = $_G['gp_archiveid'] && in_array($_G['gp_archiveid'], $threadtableids) ? "forum_thread_{$_G['gp_archiveid']}" : 'forum_thread';

if($_G['setting']['allowmoderatingthread'] && $_G['uid']) {
	$threadmodcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." WHERE fid='{$_G['fid']}' AND displayorder='-2' AND authorid='{$_G['uid']}'");
}

if($_G['forum']['modworks'] || $_G['forum']['modnewposts']) {
	$_G['forum']['modnewposts'] && $_G['forum']['modworks'] = 1;
	$modusernum = $_G['group']['allowmoduser'] ? DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_validate')." WHERE status='0'") : 0;
}

$optionadd = $filterurladd = $searchsorton = '';

$quicksearchlist = array();
if(!empty($_G['forum']['threadsorts']['types'])) {
	require_once libfile('function/threadsort');

	$showpic = intval($_G['gp_showpic']);
	$templatearray = $sortoptionarray = array();
	foreach($_G['forum']['threadsorts']['types'] as $stid => $sortname) {
		loadcache(array('threadsort_option_'.$stid, 'threadsort_template_'.$stid));
		sortthreadsortselectoption($stid);
		$templatearray[$stid] = $_G['cache']['threadsort_template_'.$stid]['subject'];
		$sortoptionarray[$stid] = $_G['cache']['threadsort_option_'.$stid];
	}

	if(!empty($_G['forum']['threadsorts']['defaultshow']) && empty($_G['gp_sortid']) && empty($_G['gp_sortall'])) {
		$_G['gp_sortid'] = $_G['forum']['threadsorts']['defaultshow'];
		$_G['gp_filter'] = 'sortid';
		$_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'].'&sortid='.$_G['gp_sortid'] : 'sortid='.$_G['gp_sortid'];
		$filterurladd = '&amp;filter=sort';
	}

	$_G['gp_sortid'] = $_G['gp_sortid'] ? $_G['gp_sortid'] : $_G['gp_searchsortid'];
	if(isset($_G['gp_sortid']) && $_G['forum']['threadsorts']['types'][$_G['gp_sortid']]) {
		$searchsortoption = $sortoptionarray[$_G['gp_sortid']];
		$quicksearchlist = quicksearch($searchsortoption);
		$_G['forum_optionlist'] = $_G['cache']['threadsort_option_'.$_G['gp_sortid']];
		$forum_optionlist = getsortedoptionlist();
	}
}

$moderatedby = $_G['forum']['status'] != 3 ? moddisplay($_G['forum']['moderators'], 'forumdisplay') : '';
$_G['gp_highlight'] = empty($_G['gp_highlight']) ? '' : htmlspecialchars($_G['gp_highlight']);
if($_G['forum']['autoclose']) {
	$closedby = $_G['forum']['autoclose'] > 0 ? 'dateline' : 'lastpost';
	$_G['forum']['autoclose'] = abs($_G['forum']['autoclose']) * 86400;
}

$subexists = 0;
foreach($_G['cache']['forums'] as $sub) {
	if($sub['type'] == 'sub' && $sub['fup'] == $_G['fid'] && (!$_G['setting']['hideprivate'] || !$sub['viewperm'] || forumperm($sub['viewperm']) || strstr($sub['users'], "\t$_G[uid]\t"))) {
		if(!$sub['status']) {
			continue;
		}
		$subexists = 1;
		$sublist = array();
		$sql = !empty($_G['member']['accessmasks']) ? "SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.domain, ff.description, ff.moderators, ff.icon, ff.viewperm, ff.extra, ff.redirect, a.allowview FROM ".DB::table('forum_forum')." f
						LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid
						LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid
						WHERE fup='$_G[fid]' AND status>'0' AND type='sub' ORDER BY f.displayorder"
					: "SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.domain, ff.description, ff.moderators, ff.icon, ff.viewperm, ff.extra, ff.redirect FROM ".DB::table('forum_forum')." f
						LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
						WHERE f.fup='$_G[fid]' AND f.status>'0' AND f.type='sub' ORDER BY f.displayorder";
		$query = DB::query($sql);
		while($sub = DB::fetch($query)) {
			$sub['extra'] = unserialize($sub['extra']);
			if(!is_array($sub['extra'])) {
				$sub['extra'] = array();
			}
			if(forum($sub)) {
				$sub['orderid'] = count($sublist);
				$sublist[] = $sub;
			}
		}
		break;
	}
}

if(!empty($_G['gp_archiveid']) && in_array($_G['gp_archiveid'], $threadtableids)) {
	$subexists = 0;
}

if($subexists) {
	if($_G['forum']['forumcolumns']) {
		$_G['forum']['forumcolwidth'] = (floor(100 / $_G['forum']['forumcolumns']) - 0.1).'%';
		$_G['forum']['subscount'] = count($sublist);
		$_G['forum']['endrows'] = '';
		if($colspan = $_G['forum']['subscount'] % $_G['forum']['forumcolumns']) {
			while(($_G['forum']['forumcolumns'] - $colspan) > 0) {
				$_G['forum']['endrows'] .= '<td>&nbsp;</td>';
				$colspan ++;
			}
			$_G['forum']['endrows'] .= '</tr>';
		}
	}
	if(empty($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'subforum_'.$_G['fid']) === FALSE) {
		$collapse['subforum'] = '';
		$collapseimg['subforum'] = 'collapsed_no.gif';
	} else {
		$collapse['subforum'] = 'display: none';
		$collapseimg['subforum'] = 'collapsed_yes.gif';
	}
}


$page = $_G['page'];
$subforumonly = $_G['forum']['simple'] & 1;
$simplestyle = !$_G['forum']['allowside'] || $page > 1 ? true : false;

if($subforumonly) {
	$_G['setting']['fastpost'] = false;
	$_G['gp_orderby'] = '';
	if(!defined('IN_ARCHIVER')) {
		include template('diy:forum/forumdisplay:'.$_G['fid']);
	} else {
		include loadarchiver('forum/forumdisplay');
	}
	exit();
}

$page = $_G['setting']['threadmaxpages'] && $page > $_G['setting']['threadmaxpages'] ? 1 : $page;
$start_limit = ($page - 1) * $_G['tpp'];

if($_G['forum']['modrecommend'] && $_G['forum']['modrecommend']['open']) {
	$_G['forum']['recommendlist'] = recommendupdate($_G['fid'], $_G['forum']['modrecommend'], '', 1);
}
$recommendgroups = array();
if($_G['forum']['status'] != 3 && $_G['setting']['groupstatus']) {
	loadcache('forumrecommend');
	$recommendgroups = $_G['cache']['forumrecommend'][$_G['fid']];
}

if($recommendgroups) {
	if(empty($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'recommendgroups_'.$_G['fid']) === FALSE) {
		$collapse['recommendgroups'] = '';
		$collapseimg['recommendgroups'] = 'collapsed_no.gif';
	} else {
		$collapse['recommendgroups'] = 'display: none';
		$collapseimg['recommendgroups'] = 'collapsed_yes.gif';
	}
}
if(!$simplestyle || !$_G['forum']['allowside'] && $page == 1) {
	if($_G['cache']['announcements_forum'] && (!$_G['cache']['announcements_forum']['endtime'] || $_G['cache']['announcements_forum']['endtime'] > TIMESTAMP)) {
		$announcement = $_G['cache']['announcements_forum'];
		$announcement['starttime'] = dgmdate($announcement['starttime'], 'd');
	} else {
		$announcement = NULL;
	}
}

$filteradd = $sortoptionurl = $sp = '';
$sorturladdarray = $selectadd = array();
$forumdisplayadd = array('orderby' => '');
$specialtype = array('poll' => 1, 'trade' => 2, 'reward' => 3, 'activity' => 4, 'debate' => 5);
$filterfield = array('digest', 'recommend', 'sortall', 'typeid', 'sortid', 'dateline', 'page', 'orderby', 'specialtype', 'author', 'view', 'reply', 'lastpost');

foreach($filterfield as $v) {
	$forumdisplayadd[$v] = '';
}

$filter = isset($_G['gp_filter']) && in_array($_G['gp_filter'], $filterfield) ? $_G['gp_filter'] : '';
$multiadd = array();
if($filter) {
	if($query_string = $_SERVER['QUERY_STRING']) {
		$query_string = substr($query_string, (strpos($query_string, "&") + 1));
		parse_str($query_string, $geturl);
		$geturl = daddslashes($geturl, 1);
		if($geturl && is_array($geturl)) {
			$issort = isset($_G['gp_sortid']) && isset($_G['forum']['threadsorts']['types'][$_G['gp_sortid']]) && $quicksearchlist ? TRUE : FALSE;
			$selectadd = $issort ? $geturl : array();
			foreach($filterfield as $option) {
				foreach($geturl as $field => $value) {
					if(in_array($field, $filterfield) && $option != $field && $field != 'page' && ($field != 'orderby' || !in_array($option, array('author', 'reply', 'view', 'lastpost', 'heat')))) {
						if(!(in_array($option, array('digest', 'recommend')) && in_array($field, array('digest', 'recommend')))) {
							$forumdisplayadd[$option] .= '&'.$field.'='.rawurlencode($value);
						}
					}
				}
				if($issort) {
					$sfilterfield = array_merge(array('filter', 'sortid', 'orderby', 'fid'), $filterfield);
					foreach($geturl as $soption => $value) {
						$forumdisplayadd[$soption] .= !in_array($soption, $sfilterfield) ? "&$soption=".rawurlencode($value) : '';
					}
				}
			}
			if($issort && is_array($quicksearchlist)) {
				foreach($quicksearchlist as $option) {
					$identifier = $option['identifier'];
					foreach($geturl as $option => $value) {
						$sorturladdarray[$identifier] .= !in_array($option, array('filter', 'sortid', 'orderby', 'fid', 'searchsort', $identifier)) ? "&amp;$option=$value" : '';
					}
				}
			}

			foreach($geturl as $field => $value) {
				if($field != 'page' && $field != 'fid') {
					$multiadd[] = $field.'='.rawurlencode($value);
					if(in_array($field, $filterfield)) {
						$filteradd .= $sp;
						if($field == 'digest') {
							$filteradd .= "AND digest>'0'";
						} elseif($field == 'recommend') {
							$filteradd .= "AND recommends>'".intval($_G['setting']['recommendthread']['iconlevels'][0])."'";
						} elseif($field == 'specialtype') {
							$filteradd .= "AND special='$specialtype[$value]'";
						} elseif($field == 'dateline') {
							$filteradd .= $value ? "AND lastpost>='".(TIMESTAMP - $value)."'" : '';
						} elseif($field == 'typeid' || $field == 'sortid') {
							$filteradd .= "AND $field='$value'";
						}
						$sp = ' ';
					}
				}
			}
		}
	}
	$simplestyle = true;
}

if(!empty($_G['gp_orderby']) && in_array($_G['gp_orderby'], array('lastpost', 'dateline', 'replies', 'views', 'recommends', 'heats'))) {
	$forumdisplayadd['orderby'] .= '&orderby='.$_G['gp_orderby'];
} else {
	$_G['gp_orderby'] = isset($_G['cache']['forums'][$_G['fid']]['orderby']) ? $_G['cache']['forums'][$_G['fid']]['orderby'] : 'lastpost';
}

$_G['gp_ascdesc'] = isset($_G['cache']['forums'][$_G['fid']]['ascdesc']) ? $_G['cache']['forums'][$_G['fid']]['ascdesc'] : 'DESC';

$check = array();
$check[$filter] = $check[$_G['gp_orderby']] = $check[$_G['gp_ascdesc']] = 'selected="selected"';

if(($_G['forum']['status'] != 3 && $_G['forum']['allowside']) || !empty($_G['forum']['threadsorts']['templatelist'])) {
	updatesession();
	$onlinenum = getonlinenum($_G['fid']);
	if(!IS_ROBOT && ($_G['setting']['whosonlinestatus'] == 2 || $_G['setting']['whosonlinestatus'] == 3)) {
		$_G['setting']['whosonlinestatus'] = 1;
		$detailstatus = $showoldetails == 'yes' || (((!isset($_G['cookie']['onlineforum']) && !$_G['setting']['whosonline_contract']) || $_G['cookie']['onlineforum']) && !$showoldetails);

		if($detailstatus) {
			$actioncode = lang('forum/action');
			$whosonline = array();
			$forumname = strip_tags($_G['forum']['name']);

			$query = DB::query("SELECT uid, groupid, username, invisible, lastactivity FROM ".DB::table('common_session')." WHERE uid>'0' AND fid='$_G[fid]' AND invisible='0' ORDER BY lastactivity DESC LIMIT 12");
			$_G['setting']['whosonlinestatus'] = 1;
			while($online = DB::fetch($query)) {
				if($online['uid']) {
					$online['icon'] = isset($_G['cache']['onlinelist'][$online['groupid']]) ? $_G['cache']['onlinelist'][$online['groupid']] : $_G['cache']['onlinelist'][0];
				} else {
					$online['icon'] = $_G['cache']['onlinelist'][7];
					$online['username'] = $_G['cache']['onlinelist']['guest'];
				}
				$online['lastactivity'] = dgmdate($online['lastactivity'], 't');
				$whosonline[] = $online;
			}
			unset($online);
		}
	} else {
		$_G['setting']['whosonlinestatus'] = 0;
	}
}

if($_G['forum']['threadsorts']['types'] && $sortoptionarray && ($_G['gp_searchoption'] || $_G['gp_searchsort'])) {
	$sortid = intval($_G['gp_sortid']);

	if($_G['gp_searchoption']){
		$forumdisplayadd['page'] = '&sortid='.$sortid;
		foreach($_G['gp_searchoption'] as $optionid => $option) {
			$identifier = $sortoptionarray[$sortid][$optionid]['identifier'];
			$forumdisplayadd['page'] .= $option['value'] ? "&searchoption[$optionid][value]=$option[value]&searchoption[$optionid][type]=$option[type]" : '';
		}
	}

	if($searchsorttids = sortsearch($_G['gp_sortid'], $sortoptionarray, $_G['gp_searchoption'], $selectadd, $_G['fid'])) {
		$filteradd .= "AND t.tid IN (".dimplode($searchsorttids).")";
	}
}

$fidsql = '';
if($_G['forum']['relatedgroup']) {
	$relatedgroup = explode(',', $_G['forum']['relatedgroup']);
	$relatedgroup[] = $_G['fid'];
	$fidsql = " t.fid IN(".dimplode($relatedgroup).")";
} else {
	$fidsql = " t.fid='{$_G['fid']}'";
}
if(empty($filter) && empty($_G['gp_sortid']) && empty($_G['gp_archiveid']) && empty($_G['forum']['archive']) && empty($_G['forum']['relatedgroup'])) {
	$_G['forum_threadcount'] = $_G['forum']['threads'];
} else {
	$indexadd = '';
	if(strexists($filteradd, "t.digest>'0'")) {
		$indexadd = " FORCE INDEX (digest) ";
	}
	$_G['forum_threadcount'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." t $indexadd WHERE $fidsql $filteradd AND t.displayorder>='0'");
}

$thisgid = $_G['forum']['type'] == 'forum' ? $_G['forum']['fup'] : (!empty($_G['cache']['forums'][$_G['forum']['fup']]['fup']) ? $_G['cache']['forums'][$_G['forum']['fup']]['fup'] : 0);
$forumstickycount = $stickycount = 0;
$stickytids = '';
if($_G['setting']['globalstick'] && $_G['forum']['allowglobalstick']) {
	$stickytids = $_G['cache']['globalstick']['global']['tids'];
	if(!empty($_G['cache']['globalstick']['categories'][$thisgid]['count'])) {
		$stickytids .= ($stickytids ? ',' : '').$_G['cache']['globalstick']['categories'][$thisgid]['tids'];
	}

	if($_G['forum']['status'] != 3) {
		$stickycount = $_G['cache']['globalstick']['global']['count'];
		if(!empty($_G['cache']['globalstick']['categories'][$thisgid])) {
			$stickycount += $_G['cache']['globalstick']['categories'][$thisgid]['count'];
		}
	}
}

$forumstickytids = array();
$forumstickycount = 0;
$forumstickfid = $_G['forum']['status'] != 3 ? $_G['fid'] : $_G['forum']['fup'];
if(isset($_G['cache']['forumstick'][$forumstickfid])) {
	$forumstickycount = count($_G['cache']['forumstick'][$forumstickfid]);
	$forumstickytids = $_G['cache']['forumstick'][$forumstickfid];
}
if(!empty($forumstickytids)) {
	$stickytids .= ($stickytids ? ', ' : '').dimplode($forumstickytids);
}
$stickycount += $forumstickycount;

if($_G['forum']['picstyle']) {
	$forumdefstyle = isset($_G['gp_forumdefstyle']) ? $_G['gp_forumdefstyle'] : '';
	if($forumdefstyle) {
		switch($forumdefstyle) {
			case 'no': dsetcookie('forumdefstyle', ''); break;
			case 'yes': dsetcookie('forumdefstyle', 1, 31536000); break;
		}
	}
	if(empty($_G['cookie']['forumdefstyle'])) {
		$stickycount = 0;
	}
}

$filterbool = !empty($filter) && in_array($filter, $filterfield);
$_G['forum_threadcount'] += $filterbool ? 0 : $stickycount;
$forumdisplayadd['page'] = !empty($forumdisplayadd['page']) ? $forumdisplayadd['page'] : '';
$multipage_archive = $_G['gp_archiveid'] && in_array($_G['gp_archiveid'], $threadtableids) ? "&archiveid={$_G['gp_archiveid']}" : '';
$multipage = multi($_G['forum_threadcount'], $_G['tpp'], $page, "forum.php?mod=forumdisplay&fid=$_G[fid]".($multiadd ? '&'.implode('&', $multiadd) : '')."$multipage_archive", $_G['setting']['threadmaxpages']);
$extra = rawurlencode(!IS_ROBOT ? 'page='.$page.($forumdisplayadd['page'] ? '&filter='.$filter.$forumdisplayadd['page'] : '').($forumdisplayadd['orderby'] ? $forumdisplayadd['orderby'] : '') : 'page=1');

$separatepos = 0;
$_G['forum_threadlist'] = $threadids = array();
$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');

$displayorderadd = !$filterbool && $stickycount ? 't.displayorder IN (0, 1)' : 't.displayorder IN (0, 1, 2, 3, 4)';

if(($start_limit && $start_limit > $stickycount) || !$stickycount || $filterbool) {
	$indexadd = '';
	if(strexists($filteradd, "t.digest>'0'")) {
		$indexadd = " FORCE INDEX (digest) ";
	}
	$querysticky = '';
	$query = DB::query("SELECT t.* FROM ".DB::table($threadtable)." t $indexadd
		WHERE $fidsql $filteradd AND ($displayorderadd)
		ORDER BY t.displayorder DESC, t.$_G[gp_orderby] $_G[gp_ascdesc]
		LIMIT ".($filterbool ? $start_limit : $start_limit - $stickycount).", $_G[tpp]");

} else {

	$querysticky = DB::query("SELECT t.* FROM ".DB::table($threadtable)." t
		WHERE t.tid IN ($stickytids) AND (t.displayorder IN (2, 3, 4))
		ORDER BY displayorder DESC, $_G[gp_orderby] $_G[gp_ascdesc]
		LIMIT $start_limit, ".($stickycount - $start_limit < $_G['tpp'] ? $stickycount - $start_limit : $_G['tpp']));

	if($_G['tpp'] - $stickycount + $start_limit > 0) {
		$query = DB::query("SELECT t.* FROM ".DB::table($threadtable)." t
			WHERE $fidsql $filteradd AND ($displayorderadd)
			ORDER BY displayorder DESC, $_G[gp_orderby] $_G[gp_ascdesc]
			LIMIT ".($_G['tpp'] - $stickycount + $start_limit));
	} else {
		$query = '';
	}

}

$_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
$page = $_G['page'];
$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));

$verify = $verifyuids = $grouptids = array();
while(($querysticky && $thread = DB::fetch($querysticky)) || ($query && $thread = DB::fetch($query))) {
	if($_G['forum']['picstyle'] && empty($_G['cookie']['forumdefstyle'])) {
		if($thread['fid'] != $_G['fid'] && empty($thread['cover'])) {
			continue;
		}
		$thread['coverpath'] = getthreadcover($thread['tid'], $thread['cover']);
		$thread['cover'] = abs($thread['cover']);
	}
	$thread['forumstick'] = in_array($thread['tid'], $forumstickytids);
	$thread['related_group'] = 0;
	if($_G['forum']['relatedgroup'] && $thread['fid'] != $_G['fid']) {
		if($thread['closed'] > 1) continue;
		$thread['related_group'] = 1;
		$grouptids[] = $thread['tid'];
	}
	$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
	if($thread['typeid'] && !empty($_G['forum']['threadtypes']['prefix']) && isset($_G['forum']['threadtypes']['types'][$thread['typeid']])) {
		if($_G['forum']['threadtypes']['prefix'] == 1) {
			$thread['typehtml'] = '<em>[<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'</a>]</em>';
		} elseif($_G['forum']['threadtypes']['icons'][$thread['typeid']] && $_G['forum']['threadtypes']['prefix'] == 2) {
			$thread['typehtml'] = '<em><a title="'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'" href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=typeid&amp;typeid='.$thread['typeid'].'">'.'<img style="vertical-align: middle;padding-right:4px;" src="'.$_G['forum']['threadtypes']['icons'][$thread['typeid']].'" alt="'.$_G['forum']['threadtypes']['types'][$thread['typeid']].'" /></a></em>';
		}
	} else {
		$thread['typeid'] = $thread['typehtml'] = '';
	}

	$thread['sorthtml'] = $thread['sortid'] && !empty($_G['forum']['threadsorts']['prefix']) && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ?
		'<em>[<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&amp;filter=sortid&amp;sortid='.$thread['sortid'].'">'.$_G['forum']['threadsorts']['types'][$thread['sortid']].'</a>]</em>' : '';
	$thread['multipage'] = '';
	$topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
	$multipate_archive = $_G['gp_archiveid'] && in_array($_G['gp_archiveid'], $threadtableids) ? "archiveid={$_G['gp_archiveid']}" : '';
	if($topicposts > $_G['ppp']) {
		$pagelinks = '';
		$thread['pages'] = ceil($topicposts / $_G['ppp']);
		$realtid = $_G['forum']['status'] != 3 && $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
		for($i = 2; $i <= 6 && $i <= $thread['pages']; $i++) {
			$pagelinks .= "<a href=\"forum.php?mod=viewthread&tid=$realtid&amp;".(!empty($multipate_archive) ? "$multipate_archive&amp;" : '')."extra=$extra&amp;page=$i\">$i</a>";
		}
		if($thread['pages'] > 6) {
			$pagelinks .= "..<a href=\"forum.php?mod=viewthread&tid=$realtid&amp;".(!empty($multipate_archive) ? "$multipate_archive&amp;" : '')."extra=$extra&amp;page=$thread[pages]\">$thread[pages]</a>";
		}
		$thread['multipage'] = '&nbsp;...'.$pagelinks;
	}

	if($thread['highlight']) {
		$string = sprintf('%02d', $thread['highlight']);
		$stylestr = sprintf('%03b', $string[0]);

		$thread['highlight'] = ' style="';
		$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
		$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
		$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
		$thread['highlight'] .= '"';
	} else {
		$thread['highlight'] = '';
	}

	$thread['recommendicon'] = '';
	if(!empty($_G['setting']['recommendthread']['status']) && $thread['recommends']) {
		foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
			if($thread['recommends'] > $i) {
				$thread['recommendicon'] = $k+1;
				break;
			}
		}
	}

	$thread['moved'] = $thread['heatlevel'] = $thread['new'] = 0;
	$thread['icontid'] = $thread['forumstick'] || !$thread['moved'] && $thread['isgroup'] != 1 ? $thread['tid'] : $thread['closed'];
	if($_G['forum']['status'] != 3 && ($thread['closed'] || ($_G['forum']['autoclose'] && TIMESTAMP - $thread[$closedby] > $_G['forum']['autoclose']))) {
		if($thread['isgroup'] == 1) {
			$thread['folder'] = 'common';
			$grouptids[] = $thread['closed'];
		} else {
			if($thread['closed'] > 1) {
				$thread['moved'] = $thread['tid'];
				$thread['replies'] = '-';
				$thread['views'] = '-';
			}
			$thread['folder'] = 'lock';
		}
	} elseif($_G['forum']['status'] == 3 && $thread['closed'] == 1) {
		$thread['folder'] = 'lock';
	} else {
		$thread['folder'] = 'common';
		$thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
		if($thread['replies'] > $thread['views']) {
			$thread['views'] = $thread['replies'];
		}
		if($_G['setting']['heatthread']['iconlevels']) {
			foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
				if($thread['heats'] > $i) {
					$thread['heatlevel'] = $k + 1;
					break;
				}
			}
		}
	}
	$thread['istoday'] = $thread['dateline'] > $todaytime ? 1 : 0;
	$thread['dbdateline'] = $thread['dateline'];
	$thread['dateline'] = dgmdate($thread['dateline'], 'd');
	$thread['dblastpost'] = $thread['lastpost'];
	$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');

	if(in_array($thread['displayorder'], array(1, 2, 3, 4))) {
		$thread['id'] = 'stickthread_'.$thread['tid'];
		$separatepos++;
	} else {
		$thread['id'] = 'normalthread_'.$thread['tid'];
		if($thread['folder'] == 'common' && $thread['dblastpost'] >= $forumlastvisit || !$forumlastvisit) {
			$thread['new'] = 1;
			$thread['folder'] = 'new';
			$thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
		}
	}
	if(isset($_G['setting']['verify']['enabled']) && $_G['setting']['verify']['enabled']) {
		$verifyuids[$thread['authorid']] = $thread['authorid'];
	}
	$threadids[] = $thread['tid'];
	$_G['forum_threadlist'][] = $thread;

}

if($_G['setting']['verify']['enabled'] && $verifyuids) {
	$verifyquery = DB::query("SELECT * FROM ".DB::table('common_member_verify')." WHERE uid IN(".dimplode($verifyuids).")");
	while($value = DB::fetch($verifyquery)) {
		foreach($_G['setting']['verify'] as $vid => $vsetting) {
			if($vsetting['available'] && $vsetting['showicon'] && !empty($vsetting['icon']) && $value['verify'.$vid] == 1) {
				$verify[$value['uid']] .= "<a href=\"home.php?mod=spacecp&ac=profile&op=verify&vid=$vid\" target=\"_blank\">".(!empty($vsetting['icon']) ? '<img src="'.$vsetting['icon'].'" class="vm" alt="'.$vsetting['title'].'" title="'.$vsetting['title'].'" />' : $vsetting['title']).'</a>';
			}
		}

	}
}

$_G['forum_threadnum'] = count($_G['forum_threadlist']) - $separatepos;

if(!empty($grouptids)) {
	$query = DB::query("SELECT t.tid, t.views, f.name, f.fid FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid WHERE t.tid IN(".dimplode($grouptids).")");
	while($row = DB::fetch($query)) {
		$groupnames[$row['tid']]['name'] = $row['name'];
		$groupnames[$row['tid']]['fid'] = $row['fid'];
		$groupnames[$row['tid']]['views'] = $row['views'];
	}
}

$stemplate = $sortexpiration = null;
if($_G['forum']['threadsorts']['types'] && $sortoptionarray && $templatearray) {
	$sortid = intval($_G['gp_sortid']);
	$sortlistarray = showsorttemplate($_G['gp_sortid'], $_G['fid'], $sortoptionarray, $templatearray, $_G['forum_threadlist'], $threadids);
	$stemplate = $sortlistarray['template'];
	$sortexpiration = $sortlistarray['expiration'];

	if(($_G['gp_searchoption'] || $_G['gp_searchsort']) && empty($searchsorttids)) {
		$_G['forum_threadlist'] = $multipage = '';
	}
}

$separatepos = $separatepos ? $separatepos + 1 : 0;

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] ? visitedforums() : '';
$oldthreads = array();

$oldtopics = isset($_G['cookie']['oldtopics']) ? $_G['cookie']['oldtopics'] : 'D';

if($_G['setting']['visitedthreads'] && $oldtopics) {
	$oldtids = array_slice(explode('D', $oldtopics), 0, $_G['setting']['visitedthreads']);
	$oldtidsnew = array();
	foreach($oldtids as $oldtid) {
		$oldtid && $oldtidsnew[] = $oldtid;
	}
	if($oldtidsnew) {
		$query = DB::query("SELECT tid, subject FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($oldtidsnew).")");
		while($oldthread = DB::fetch($query)) {
			$oldthreads[$oldthread['tid']] = $oldthread['subject'];
		}
	}
}


$_G['group']['allowpost'] = (!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || (isset($_G['forum']['allowpost']) && $_G['forum']['allowpost'] == 1 && $_G['group']['allowpost']);
$fastpost = $_G['setting']['fastpost'] && $_G['group']['allowpost'] && !$_G['forum']['allowspecialonly'] && !$_G['forum']['threadsorts']['required'];
$fastpost = $fastpost && !$_G['forum']['allowspecialonly'];
$_G['group']['allowpost'] = isset($_G['forum']['allowpost']) && $_G['forum']['allowpost'] == -1 ?  false : $_G['group']['allowpost'];

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$allowpostattach = $fastpost && ($_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm']))));

if($fastpost) {
	if(!$_G['adminid'] && (!cknewuser(1) || $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60)) {
		$fastpost = false;
	}
	$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'];
	$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
	$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);
}

$showpoll = $showtrade = $showreward = $showactivity = $showdebate = 0;
if($_G['forum']['allowpostspecial']) {
	$showpoll = $_G['forum']['allowpostspecial'] & 1;
	$showtrade = $_G['forum']['allowpostspecial'] & 2;
	$showreward = isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($_G['forum']['allowpostspecial'] & 4);
	$showactivity = $_G['forum']['allowpostspecial'] & 8;
	$showdebate = $_G['forum']['allowpostspecial'] & 16;
}

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && $showpoll;
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && $showtrade;
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && $showreward;
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && $showactivity;
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && $showdebate;
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? unserialize($_G['forum']['threadplugin']) : array();

$allowleftside = !$subforumonly && $_G['setting']['leftsidewidth'] && !$_G['forum']['allowside'];
if(isset($_G['gp_leftsidestatus'])) {
	dsetcookie('disableleftside', $_G['gp_leftsidestatus'], 2592000);
	$_G['cookie']['disableleftside'] = $_G['gp_leftsidestatus'];
}
$leftside = empty($_G['cookie']['disableleftside']) && $allowleftside ? forumleftside() : array();
$leftsideswitch = $allowleftside ? "forum.php?mod=forumdisplay&fid=$_G[fid]&page=$page".($multiadd ? '&'.implode('&', $multiadd) : '') : '';

$template = 'diy:forum/forumdisplay:'.$_G['fid'];

if(!empty($_G['forum']['threadsorts']['templatelist']) && $_G['forum']['status'] != 3) {
	$template = 'diy:forum/forumdisplay_'.$_G['forum']['threadsorts']['templatelist'].':'.$_G['fid'];
} elseif($_G['forum']['status'] == 3) {
	$groupviewed_list = get_viewedgroup();
	write_groupviewed($_G['fid']);
	$template = 'diy:group/group:'.$_G['fid'];
}

if(!defined('IN_ARCHIVER')) {
	include template($template);
} else {
	include loadarchiver('forum/forumdisplay');
}

?>