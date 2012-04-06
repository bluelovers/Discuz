<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_forum.php 17136 2010-09-25 01:39:54Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$forumupdate = $listupdate = false;

$op = !in_array($op , array('editforum', 'recommend')) ? 'editforum' : $op;

if(empty($_G['fid'])) {
	if(!empty($_G['cookie']['modcpfid'])) {
		$fid = $_G['cookie']['modcpfid'];
	} else {
		list($fid) = array_keys($modforums['list']);
	}
	dheader("Location: {$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&fid=$fid");
}

if($_G['fid'] && $_G['forum']['ismoderator']) {

	if($op == 'editforum') {

		require_once libfile('function/editor');

		$alloweditrules = $_G['adminid'] == 1 || $_G['forum']['alloweditrules'] ? true : false;

		if(!submitcheck('editsubmit')) {
			$_G['forum']['rules'] = html2bbcode($_G['forum']['rules']);
		} else {

			require_once libfile('function/discuzcode');
			$forumupdate = true;
			$rulesnew = $alloweditrules ? addslashes(preg_replace('/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i', '', discuzcode(stripslashes($_G['gp_rulesnew']), 1, 0, 0, 0, 1, 1, 0, 0, 1))) : addslashes($_G['forum']['rules']);
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET rules='$rulesnew' WHERE fid='$_G[fid]'");

			$_G['forum']['description'] = html2bbcode(dstripslashes($descnew));
			$_G['forum']['rules'] = html2bbcode(dstripslashes($rulesnew));

		}

	} elseif($op == 'recommend') {

		$useradd = '';

		if($_G['adminid'] == 3) {
			$useradd = "AND moderatorid IN ('$_G[uid]', 0)";
		}
		$ordernew = !empty($_G['gp_ordernew']) && is_array($_G['gp_ordernew']) ? $_G['gp_ordernew'] : array();

		if(submitcheck('editsubmit') && $_G['forum']['modrecommend']['sort'] != 1) {
			$threads = array();
			foreach($_G['gp_order'] as $id => $position) {
				$threads[$id]['order'] = $position;
			}
			foreach($_G['gp_subject'] as $id => $title) {
				$threads[$id]['subject'] = $title;
			}
			foreach($_G['gp_expirationrecommend'] as $id => $expiration) {
				$expiration = trim($expiration);
				if(!empty($expiration)) {
					if(!preg_match('/^\d{4}-\d{1,2}-\d{1,2} +\d{1,2}:\d{1,2}$/', $expiration)) {
						showmessage('recommend_expiration_invalid');
					}
					list($expiration_date, $expiration_time) = explode(' ', $expiration);
					list($expiration_year, $expiration_month, $expiration_day) = explode('-', $expiration_date);
					list($expiration_hour, $expiration_min) = explode(':', $expiration_time);
					$expiration_sec = 0;

					$expiration_timestamp = mktime($expiration_hour, $expiration_min, $expiration_sec, $expiration_month, $expiration_day, $expiration_year);
				} else {
					$expiration_timestamp = 0;
				}
				$threads[$id]['expiration'] = $expiration_timestamp;
			}
			if(@$ids = dimplode($_G['gp_delete'])) {
				$listupdate = true;
				DB::query("DELETE FROM ".DB::table('forum_forumrecommend')." WHERE fid='$_G[fid]' AND tid IN($ids)");
			}
			if(!empty($_G['gp_delete']) && is_array($_G['gp_delete'])) {
				foreach($_G['gp_delete'] as $id) {
					$threads[$id]['delete'] = true;
					unset($threads[$id]);
				}
			}
			foreach($threads as $id => $item) {
				$item['displayorder'] = intval($item['order']);
				$item['subject'] = dhtmlspecialchars($item['subject']);
				DB::query("UPDATE ".DB::table('forum_forumrecommend')." SET subject='$item[subject]', displayorder='$item[displayorder]', moderatorid='$_G[uid]', expiration='$item[expiration]' WHERE tid='$id'");
			}
			$listupdate = true;
		}

		$page = max(1, intval($_G['page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forumrecommend')." WHERE fid='$_G[fid]'");
		$multipage = multi($threadcount, $_G['tpp'], $page, "$cpscript?action=$_G[gp_action]&fid=$_G[fid]&page=$page");

		$threadlist = array();
		$query = DB::query("SELECT f.*, m.username as moderator
				FROM ".DB::table('forum_forumrecommend')." f
				LEFT JOIN ".DB::table('common_member')." m ON f.moderatorid=m.uid
				WHERE f.fid='$_G[fid]' $useradd LIMIT $start_limit,$_G[tpp]");
		while($thread = DB::fetch($query)) {
			$thread['authorlink'] = $thread['authorid'] ? "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>" : 'Guest';
			$thread['moderatorlink'] = $thread['moderator'] ? "<a href=\"home.php?mod=space&uid=$thread[moderatorid]\" target=\"_blank\">$thread[moderator]</a>" : 'System';
			$thread['expiration'] = $thread['expiration'] ? dgmdate($thread['expiration']) : '';
			$threadlist[] = $thread;
		}

	}
}

?>