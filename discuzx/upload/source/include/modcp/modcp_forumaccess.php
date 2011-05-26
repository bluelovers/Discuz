<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_forumaccess.php 17939 2010-11-08 06:09:39Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$list = $logids = array();

include_once(libfile('function/forumlist'));
$forumlistall = forumselect(false, false, $_G['fid']);

$adderror = $successed = 0;
$new_user = isset($_G['gp_new_user']) ? trim($_G['gp_new_user']) : '';

if($_G['fid'] && $_G['forum']['ismoderator'] && $new_user != '' && submitcheck('addsubmit')) {
	$deleteaccess = isset($_G['gp_deleteaccess']) ? 1 : 0;
	foreach (array('view', 'post', 'reply', 'getattach', 'getimage', 'postattach', 'postimage') as $key) {
		${'new_'.$key} = isset($_G['gp_new_'.$key]) ? intval($_G['gp_new_'.$key]) : '';
	}

	if($new_user != '') {

		$user = DB::fetch_first("SELECT uid, adminid FROM ".DB::table('common_member')." WHERE username='$new_user'");
		$uid = $user['uid'];

		if(empty($user)) {
			$adderror = 1;
		} elseif($user['adminid'] && $_G['adminid'] != 1) {
			$adderror = 2;
		} else {

			$access = DB::fetch_first("SELECT * FROM ".DB::table('forum_access')." WHERE fid='$_G[fid]' AND uid='$uid'");

			if($deleteaccess) {

				if($access && $_G['adminid'] != 1 && inwhitelist($access)) {
					$adderror = 3;
				} else {
					$successed = true;
					$access && delete_access($uid, $_G['fid']);
				}

			} elseif($new_view || $new_post || $new_reply || $new_getattach || $new_getimage || $new_postattach || $new_postimage) {

				if($new_view == -1) {
					$new_view = $new_post = $new_reply = $new_getattach = $new_getimage = $new_postattach = $new_postimage = -1;
				} else {
					$new_view = 0;
					$new_post = $new_post ? -1 : 0;
					$new_reply = $new_reply ? -1 : 0;
					$new_getattach = $new_getattach ? -1 : 0;
					$new_getimage = $new_getimage ? -1 : 0;
					$new_postattach = $new_postattach ? -1 : 0;
					$new_postimage = $new_postimage ? -1 : 0;
				}

				if(empty($access)) {
					$successed = true;
					DB::query("INSERT INTO ".DB::table('forum_access')." SET
						uid='$uid', fid='$_G[fid]', allowview='$new_view', allowpost='$new_post', allowreply='$new_reply',
						allowgetattach='$new_getattach', allowgetimage='$new_getimage',
						allowpostattach='$new_postattach', allowpostimage='$new_postimage',
						adminuser='$_G[uid]', dateline='$_G[timestamp]'");
					DB::query("UPDATE ".DB::table('common_member')." SET accessmasks='1' WHERE uid='$uid'", 'UNBUFFERED');

				} elseif($new_view == -1 && $access['allowview'] == 1 && $_G['adminid'] != 1) {
					$adderror = 3;
				} else {
					if($_G['adminid'] > 1) {
						$new_view = $access['allowview'] == 1 ? 1 : $new_view;
						$new_post = $access['allowpost'] == 1 ? 1 : $new_post;
						$new_reply = $access['allowreply'] == 1 ? 1 : $new_reply;
						$new_getattach = $access['allowgetattach'] == 1 ? 1 : $new_getattach;
						$new_getimage = $access['allowgetimage'] == 1 ? 1 : $new_getimage;
						$new_postattach = $access['postattach'] == 1 ? 1 : $new_postattach;
						$new_postimage = $access['postimage'] == 1 ? 1 : $new_postimage;
					}
					$successed = true;
					DB::query("UPDATE ".DB::table('forum_access')." SET
						allowview='$new_view', allowpost='$new_post', allowreply='$new_reply',
						allowgetattach='$new_getattach', allowgetimage='$new_getimage',
						allowpostattach='$new_postattach', allowpostimage='$new_postimage',
						adminuser='$_G[uid]', dateline='$_G[timestamp]' WHERE uid='$uid' AND fid='$_G[fid]'");
					DB::query("UPDATE ".DB::table('common_member')." SET accessmasks='1' WHERE uid='$uid'", 'UNBUFFERED');

				}
			}
		}
	}

	$new_user = $adderror ? $new_user : '';
}

$new_user = dhtmlspecialchars($new_user);
$fidadd = $useradd = '';
$suser = isset($_G['gp_suser']) ? trim($_G['gp_suser']) : '';
if(submitcheck('searchsubmit')) {
	$fidadd = $_G['fid'] ? "AND fid='$_G[fid]'" : '';
	if($suser != '') {
		$suid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$suser'");
		$useradd = "AND uid='$suid'";
	}
}
$suser = dhtmlspecialchars($suser);

$page = max(1, intval($_G['page']));
$ppp = 10;
$list = array('pagelink' => '', 'data' => array());

if($num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_access')." WHERE 1=1 $fidadd $useradd")) {

	$page = $page > ceil($num / $ppp) ? ceil($num / $ppp) : $page;
	$start_limit = ($page - 1) * $ppp;
	$list['pagelink'] = multi($num, $ppp, $page, "forum.php?mod=modcp&fid=$_G[fid]&action=$_G[gp_action]");

	$query = DB::query("SELECT * FROM ".DB::table('forum_access')." WHERE 1=1 $fidadd $useradd ORDER BY dateline DESC LIMIT $start_limit, $ppp");
	$uidarray = array();
	while($access = DB::fetch($query)) {
		$uidarray[$access['uid']] = $access['uid'];
		$uidarray[$access['adminuser']] = $access['adminuser'];
		$access['allowview'] = accessimg($access['allowview']);
		$access['allowpost'] = accessimg($access['allowpost']);
		$access['allowreply'] = accessimg($access['allowreply']);
		$access['allowpostattach'] = accessimg($access['allowpostattach']);
		$access['allowgetattach'] = accessimg($access['allowgetattach']);
		$access['allowgetimage'] = accessimg($access['allowgetimage']);
		$access['allowpostimage'] = accessimg($access['allowpostimage']);
		$access['dateline'] = dgmdate($access['dateline'], 'd');
		$access['forum'] = '<a href="forum.php?mod=forumdisplay&fid='.$access['fid'].'" target="_blank">'.strip_tags($_G['cache']['forums'][$access['fid']]['name']).'</a>';
		$list['data'][] = $access;
	}

	$users = array();
	if($uids = dimplode($uidarray)) {
		$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
		while ($user = DB::fetch($query)) {
			$users[$user['uid']] = $user['username'];
		}
	}
}

function delete_access($uid, $fid) {
	DB::query("DELETE FROM ".DB::table('forum_access')." WHERE uid='$uid' AND fid='$fid'");
	$mask = DB::result_first("SELECT count(*) FROM ".DB::table('forum_access')." WHERE uid='$uid'");
	if(!$mask) {
		DB::query("UPDATE ".DB::table('common_member')." SET accessmasks='' WHERE uid='$uid'", 'UNBUFFERED');
	}
}

function accessimg($access) {
	return $access == -1 ? '<img src="'.STATICURL.'image/common/access_disallow.gif" />' :
		($access == 1 ? '<img src="'.STATICURL.'image/common/access_allow.gif" />' : '<img src="'.STATICURL.'image/common/access_normal.gif" />');
}

function inwhitelist($access) {
	$return = false;
	foreach (array('allowview', 'allowpost', 'allowreply', 'allowpostattach', 'allowgetattach', 'allowgetimage') as $key) {
		if($access[$key] == 1) {
			$return = true;
			break;
		}
	}
	return $return;
}

?>