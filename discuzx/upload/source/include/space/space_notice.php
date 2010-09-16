<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_notice.php 16279 2010-09-02 09:33:15Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 100;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

$list = array();
$count = 0;
$multi = '';

$view = (!empty($_GET['view']) && in_array($_GET['view'], array('userapp')))?$_GET['view']:'notice';
$actives = array($view=>' class="a"');

if($view == 'userapp') {

	space_merge($space, 'status');

	if($_GET['op'] == 'del') {
		$appid = intval($_GET['appid']);
		DB::query("DELETE FROM ".DB::table('common_myinvite')." WHERE appid='$appid' AND touid='$_G[uid]'");

		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_myinvite')." WHERE touid='$_G[uid]'"), 0);
		$changecount = $count - $space['myinvitations'];
		if($changecount) {
			member_status_update($_G['uid'], array('myinvitations' => $changecount));
		}

		showmessage('do_success', "home.php?mod=space&do=notice&view=userapp&quickforward=1");
	}

	$filtrate = 0;
	$count = 0;
	$apparr = array();
	$type = intval($_GET['type']);
	$query = DB::query("SELECT * FROM ".DB::table('common_myinvite')." WHERE touid='$_G[uid]' ORDER BY dateline DESC");
	while ($value = DB::fetch($query)) {
		$count++;
		$key = md5($value['typename'].$value['type']);
		$apparr[$key][] = $value;
		if($filtrate) {
			$filtrate--;
		} else {
			if($count < $perpage) {
				if($type && $value['appid'] == $type) {
					$list[$key][] = $value;
				} elseif(!$type) {
					$list[$key][] = $value;
				}
			}
		}
	}

	if(empty($count) && $space['myinvitations']) {
		$changecount = 0 - $space['myinvitations'];
		if($changecount) {
			member_status_update($_G['uid'], array('myinvitations' => $changecount));
		}
	}

} else {
	space_merge($space, 'status');

	if(!empty($_GET['ignore'])) {
		DB::update('home_notification', array('new'=>'0', 'from_num'=>0), array('new'=>'1', 'uid'=>$_G['uid']));

		$changecount = 0 - $space['notifications'];
		if($changecount) {
			member_status_update($_G['uid'], array('notifications' => $changecount));
		}
	}

	foreach (array('wall', 'piccomment', 'blogcomment', 'clickblog', 'clickpic', 'sharecomment', 'doing', 'friend', 'credit', 'bbs', 'system', 'thread', 'task', 'group') as $key) {
		$noticetypes[$key] = lang('notification', "type_$key");
	}

	$type = trim($_GET['type']);
	$typesql = $type?"AND type='$type'":'';

	$fuids = $newids = array();
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' $typesql"), 0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' $typesql ORDER BY new DESC, dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if($value['new']) {
				$newids[] = $value['id'];
				$value['style'] = 'color:#000;font-weight:bold;';
			} else {
				$value['style'] = '';
			}
			$fuids[$value['id']] = $value['authorid'];
			if($value['from_num'] > 0) $value['from_num'] = $value['from_num'] - 1;
			$list[$value['id']] = $value;
		}
		if($fuids) {
			require_once libfile('function/friend');
			friend_check($fuids);

			foreach($fuids as $key => $fuid) {
				$value = array();
				$value['isfriend'] = $fuid==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$fuid] ? 1 : 0;
				$list[$key] = array_merge($list[$key], $value);
			}

		}
		$multi = multi($count, $perpage, $page, "home.php?mod=space&do=$do");
	}

	$newnotice = $space['notifications'];
	if($newids) {
		DB::query("UPDATE ".DB::table('home_notification')." SET new='0', from_num='0' WHERE id IN (".dimplode($newids).")");
		$newcount = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_notification')." WHERE uid='$_G[uid]' AND new='1'"), 0);

		$changecount = $newcount - $space['notifications'];
		if($changecount) {
			member_status_update($_G['uid'], array('notifications' => $changecount));
		}

		$space['notifications'] = $newcount;
	}

	$newprompt = 0;
	foreach (array('notifications','myinvitations','pokes','pendingfriends') as $key) {
		$newprompt = $newprompt + $space[$key];
	}

	if($newprompt != $space['newprompt']) {
		$space['newprompt'] = $newprompt;
		DB::update('common_member', array('newprompt'=>$newprompt), array('uid'=>$_G['uid']));
	}

	if($newprompt) {
		$pokes = $pendingfriends = array();
		if($space['pendingfriends']) {
			$query = DB::query("SELECT * FROM ".DB::table('home_friend_request')." WHERE uid='$_G[uid]' ORDER BY dateline DESC LIMIT 0, 2");
			while($value = DB::fetch($query)) {
				$pendingfriends[] = $value;
			}
		}
		if($space['pokes']) {
			$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' ORDER BY dateline DESC LIMIT 0, 2");
			while($value = DB::fetch($query)) {
				$pokes[] = $value;
			}
		}
	}

}
dsetcookie('promptstate_'.$_G['uid'], $newprompt, 31536000);
include_once template("diy:home/space_notice");

?>