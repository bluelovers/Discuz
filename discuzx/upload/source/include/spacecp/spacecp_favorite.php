<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_share.php 7910 2010-04-15 01:55:08Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$_GET['type'] = in_array($_GET['type'], array("thread", "forum", "group", "blog", "album", "article", "all")) ? $_GET['type'] : 'all';
if($_GET['op'] == 'delete') {

	if($_G['gp_checkall']) {
		if($_G['gp_favorite']) {
			DB::query('DELETE FROM '.DB::table('home_favorite')." WHERE uid='$_G[uid]' AND favid IN (".dimplode($_G['gp_favorite']).")");
		}
		showmessage('favorite_delete_succeed', 'home.php?mod=space&uid='.$_G['uid'].'&do=favorite&view=me&type='.$_GET['type'].'&quickforward=1');
	} else {
		$favid = intval($_GET['favid']);
		$thevalue = DB::fetch_first('SELECT * FROM '.DB::table('home_favorite')." WHERE favid='$favid'");
		if(empty($thevalue) || $thevalue['uid'] != $_G['uid']) {
			showmessage('favorite_does_not_exist');
		}

		if(submitcheck('deletesubmit')) {
			DB::query('DELETE FROM '.DB::table('home_favorite')." WHERE favid='$favid'");
			showmessage('do_success', 'home.php?mod=space&uid='.$_G['uid'].'&do=favorite&view=me&type='.$_GET['type'].'&quickforward=1', array('favid' => $favid, 'id' => $thevalue['id']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true, 'locationtime' => 3));
		}
	}

} else {


	cknewuser();

	$type = empty($_GET['type']) ? '' : $_GET['type'];
	$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
	$spaceuid = empty($_GET['spaceuid']) ? 0 : intval($_GET['spaceuid']);
	$idtype = $title = $icon = '';
	switch($type) {
		case 'thread':
			$idtype = 'tid';
			$title = DB::result_first('SELECT subject FROM '.DB::table('forum_thread')." WHERE tid='$id'");
			$icon = '<img src="static/image/feed/thread.gif" alt="thread" class="vm" /> ';
			break;
		case 'forum':
			$idtype = 'fid';
			$title = DB::result_first('SELECT `name` FROM '.DB::table('forum_forum')." WHERE fid='$id' AND status !='3'");
			$icon = '<img src="static/image/feed/discuz.gif" alt="forum" class="vm" /> ';
			break;
		case 'blog';
			$idtype = 'blogid';
			$title = DB::result_first('SELECT subject FROM '.DB::table('home_blog')." WHERE blogid='$id' AND uid='$spaceuid'");
			$icon = '<img src="static/image/feed/blog.gif" alt="blog" class="vm" /> ';
			break;
		case 'group';
			$idtype = 'gid';
			$title = DB::result_first('SELECT `name` FROM '.DB::table('forum_forum')." WHERE fid='$id' AND status ='3'");
			$icon = '<img src="static/image/feed/group.gif" alt="group" class="vm" /> ';
			break;
		case 'album';
			$idtype = 'albumid';
			$title = DB::result_first('SELECT albumname FROM '.DB::table('home_album')." WHERE albumid='$id' AND uid='$spaceuid'");
			$icon = '<img src="static/image/feed/album.gif" alt="album" class="vm" /> ';
			break;
		case 'space';
			$idtype = 'uid';
			$title = DB::result_first('SELECT username FROM '.DB::table('common_member')." WHERE uid='$id'");
			$icon = '<img src="static/image/feed/profile.gif" alt="space" class="vm" /> ';
			break;
		case 'article';
			$idtype = 'aid';
			$title = DB::result_first('SELECT title FROM '.DB::table('portal_article_title')." WHERE aid='$id'");
			$icon = '<img src="static/image/feed/article.gif" alt="article" class="vm" /> ';
			break;
	}
	if(empty($idtype) || empty($title)) {
		showmessage('favorite_cannot_favorite');
	}

	if(DB::result_first('SELECT * FROM '.DB::table('home_favorite')." WHERE uid='$_G[uid]' AND idtype='$idtype' AND id='$id'")) {
		showmessage('favorite_repeat');
	}
	$description = '';
	$description_show = nl2br($description);

	$fav_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_favorite')." WHERE id='$id' AND idtype='$idtype'");
	if(submitcheck('favoritesubmit') || $type == 'forum' || $type == 'group') {
		$arr = array(
			'uid' => intval($_G['uid']),
			'idtype' => $idtype,
			'id' => $id,
			'spaceuid' => $spaceuid,
			'title' => getstr($title, 255, 0, 1),
			'description' => getstr($_POST['description'], '', 1, 1, 1),
			'dateline' => TIMESTAMP
		);
		DB::insert('home_favorite', $arr);
		switch($type) {
			case 'thread':
				DB::query("UPDATE ".DB::table('forum_thread')." SET favtimes=favtimes+1 WHERE tid='$id'");
				if($_G['setting']['heatthread']['type'] == 2) {
					require_once libfile('function/forum');
					update_threadpartake($id);
				}
				break;
			case 'forum':
				DB::query("UPDATE ".DB::table('forum_forum')." SET favtimes=favtimes+1 WHERE fid='$id'");
				break;
			case 'blog':
				DB::query("UPDATE ".DB::table('home_blog')." SET favtimes=favtimes+1 WHERE blogid='$id' AND uid='$spaceuid'");
				break;
			case 'group':
				DB::query("UPDATE ".DB::table('forum_forum')." SET favtimes=favtimes+1 WHERE fid='$id' AND status='3'");
				break;
			case 'album':
				DB::query("UPDATE ".DB::table('home_album')." SET favtimes=favtimes+1 WHERE albumid='$id' AND uid='$spaceuid'");
				break;
			case 'space':
				DB::query("UPDATE ".DB::table('common_member_status')." SET favtimes=favtimes+1 WHERE uid='$id'");
				break;
			case 'article':
				DB::query("UPDATE ".DB::table('portal_article_count')." SET favtimes=favtimes+1 WHERE aid='$id'");
				break;
		}
		showmessage('favorite_do_success', dreferer(), array('id' => $id), array('showdialog' => true, 'closetime' => true));
	}
}

include template('home/spacecp_favorite');


?>