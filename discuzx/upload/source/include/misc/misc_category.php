<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_category.php 26665 2011-12-19 07:31:16Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$gquery = DB::query("SELECT f.fid, f.fup, f.type, f.name, ff.moderators, ff.extra, f.domain, f.catforumcolumns AS forumcolumns, f.styleid, ff.description, ff.seotitle, ff.seodescription, ff.keywords FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$gid'");

$sql = !empty($_G['member']['accessmasks']) ? "SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.domain, f.catforumcolumns AS forumcolumns,
				f.lastpost, f.inheritedmod, ff.description, ff.seotitle, ff.seodescription, ff.keywords, ff.moderators, ff.icon, ff.viewperm, ff.extra, ff.redirect, a.allowview
				FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid
				LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid
				WHERE f.fup='$gid' AND f.status='1' AND f.type='forum' ORDER BY f.displayorder"
			: "SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.todayposts, f.lastpost, f.inheritedmod, f.domain,
				ff.description, ff.seotitle, ff.seodescription, ff.keywords, ff.moderators, ff.icon, ff.viewperm, ff.extra, ff.redirect
				FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
				WHERE f.fup='$gid' AND f.status='1' AND f.type='forum' ORDER BY f.displayorder";

$query = DB::query($sql);
if(!DB::num_rows($gquery) || !DB::num_rows($query)) {
	showmessage('forum_nonexistence', NULL);
}

while(($forum = DB::fetch($gquery)) || ($forum = DB::fetch($query))) {
	$forum['extra'] = unserialize($forum['extra']);
	if(!is_array($forum['extra'])) {
		$forum['extra'] = array();
	}
	if($forum['type'] != 'group') {
		$threads += $forum['threads'];
		$posts += $forum['posts'];
		$todayposts += $forum['todayposts'];
		if(forum($forum)) {
			$forum['orderid'] = $catlist[$forum['fup']]['forumscount'] ++;
			$forum['subforums'] = '';
			$forumlist[$forum['fid']] = $forum;
			$catlist[$forum['fup']]['forums'][] = $forum['fid'];
			$fids .= ','.$forum['fid'];
		}
	} else {
		$forum['collapseimg'] = 'collapsed_no.gif';
		$collapse['category_'.$forum['fid']] = '';

		if($forum['moderators']) {
			$forum['moderators'] = moddisplay($forum['moderators'], 'flat');
		}
		$catlist[$forum['fid']] = $forum;

		$navigation = '<em>&rsaquo;</em> '.$forum['name'];
		$navtitle_g = strip_tags($forum['name']);
	}
}
if($catlist) {
	foreach($catlist as $key => $var) {
		if($var['forumscount'] && $var['forumcolumns']) {
			$catlist[$key]['forumcolwidth'] = (floor(100 / $var['forumcolumns']) - 0.1).'%';
			$catlist[$key]['endrows'] = '';
			if($colspan = $var['forumscount'] % $var['forumcolumns']) {
				while(($var['forumcolumns'] - $colspan) > 0) {
					$catlist[$key]['endrows'] .= '<td>&nbsp;</td>';
					$colspan ++;
				}
				$catlist[$key]['endrows'] .= '</tr>';
			}
		}
	}
}
$query = DB::query("SELECT fid, fup, name, threads, posts, todayposts, domain FROM ".DB::table('forum_forum')." WHERE status='1' AND fup IN ($fids) AND type='sub' ORDER BY displayorder");
while($forum = DB::fetch($query)) {

	if($_G['setting']['subforumsindex'] && $forumlist[$forum['fup']]['permission'] == 2) {
		$forumurl = !empty($forum['domain']) && !empty($_G['setting']['domain']['root']['forum']) ? 'http://'.$forum['domain'].'.'.$_G['setting']['domain']['root']['forum'] : 'forum.php?mod=forumdisplay&fid='.$forum['fid'];
		$forumlist[$forum['fup']]['subforums'] .= '<a href="'.$forumurl.'"><u>'.$forum['name'].'</u></a>&nbsp;&nbsp;';
	}
	$forumlist[$forum['fup']]['threads'] 	+= $forum['threads'];
	$forumlist[$forum['fup']]['posts'] 	+= $forum['posts'];
	$forumlist[$forum['fup']]['todayposts'] += $forum['todayposts'];

}

?>