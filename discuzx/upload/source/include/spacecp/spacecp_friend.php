<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_friend.php 22841 2011-05-25 08:40:43Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/friend');

$op = empty($_GET['op'])?'':$_GET['op'];
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

$space['key'] = space_key($space['uid']);

$actives = array($op=>' class="a"');

if($op == 'add') {

	if(!checkperm('allowfriend')) {
		showmessage('no_privilege_addfriend');
	}

	if($uid == $_G['uid']) {
		showmessage('friend_self_error');
	}

	if(friend_check($uid)) {
		showmessage('you_have_friends');
	}

	$tospace = getspace($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}

	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	$groups = friend_group_list();

	space_merge($space, 'count');
	space_merge($space, 'field_home');

	$maxfriendnum = checkperm('maxfriendnum');
	if($maxfriendnum && $space['friends'] >= $maxfriendnum + $space['addfriend']) {
		if($_G['magic']['friendnum']) {
			showmessage('enough_of_the_number_of_friends_with_magic');
		} else {
			showmessage('enough_of_the_number_of_friends');
		}
	}

	if(friend_request_check($uid)) {

		if(submitcheck('add2submit')) {

			$_POST['gid'] = intval($_POST['gid']);
			friend_add($uid, $_POST['gid']);

			if(ckprivacy('friend', 'feed')) {
				require_once libfile('function/feed');
				feed_add('friend', 'feed_friend_title', array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">$tospace[username]</a>"));
			}

			notification_add($uid, 'friend', 'friend_add');
			showmessage('friends_add', dreferer(), array('username' => $tospace['username'], 'uid'=>$uid, 'from' => $_G['gp_from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}

		$op = 'add2';
		$groupselect = empty($space['privacy']['groupname']) ? array(1 => ' checked') : array();
		$navtitle = lang('core', 'title_friend_add');
		include template('home/spacecp_friend');
		exit();

	} else {

		if(getcount('home_friend_request', array('uid'=>$uid, 'fuid'=>$_G['uid']))) {
			showmessage('waiting_for_the_other_test');
		}

		if(submitcheck('addsubmit')) {

			$_POST['gid'] = intval($_POST['gid']);
			$_POST['note'] = censor($_POST['note']);
			friend_add($uid, $_POST['gid'], $_POST['note']);

			$note = array(
				'uid' => $_G['uid'],
				'url' => 'home.php?mod=spacecp&ac=friend&op=add&uid='.$_G['uid'].'&from=notice',
				'from_id' => $_G['uid'],
				'from_idtype' => 'friendrequest',
				'note' => !empty($_POST['note']) ? lang('spacecp', 'friend_request_note', array('note' => $_POST['note'])) : ''
			);

			notification_add($uid, 'friend', 'friend_request', $note);

			require_once libfile('function/mail');
			$values = array(
				'username' => $tospace['username'],
				'url' => getsiteurl().'home.php?mod=spacecp&ac=friend&amp;op=request'
			);
			sendmail_touser($uid, lang('spacecp', 'friend_subject', $values), '', 'friend_add');
			showmessage('request_has_been_sent', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));

		} else {
			include_once template('home/spacecp_friend');
			exit();
		}
	}

} elseif($op == 'ignore') {

	if($uid) {
		if(submitcheck('friendsubmit')) {

			if(friend_check($uid)) {
				friend_delete($uid);
			} else {
				friend_request_delete($uid);
			}
			showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request', array('uid'=>$uid, 'from' => $_G['gp_from']), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 0));
		}
	} elseif($_GET['key'] == $space['key']) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend_request')." WHERE uid='$_G[uid]'"), 0);
		if($count) {
			DB::delete('home_friend_request', array('uid'=>$_G['uid']));

			dsetcookie('promptstate_'.$_G['uid'], $space['newprompt'], 31536000);
		}
		showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request');
	}

} elseif($op == 'addconfirm') {

	if(!checkperm('allowfriend')) {
		showmessage('no_privilege_addfriend');
	}
	if($_GET['key'] == $space['key']) {

		$maxfriendnum = checkperm('maxfriendnum');
		space_merge($space, 'field_home');
		space_merge($space, 'count');

		if($maxfriendnum && $space['friends'] >= $maxfriendnum + $space['addfriend']) {
			if($_G['magic']['friendnum']) {
				showmessage('enough_of_the_number_of_friends_with_magic');
			} else {
				showmessage('enough_of_the_number_of_friends');
			}
		}

		$query = DB::query("SELECT fuid, fusername FROM ".DB::table('home_friend_request')." WHERE uid='$space[uid]' LIMIT 0,1");
		if($value = DB::fetch($query)) {
			friend_add($value['fuid']);
			showmessage('friend_addconfirm_next', 'home.php?mod=spacecp&ac=friend&op=addconfirm&key='.$space['key'], array('username' => $value['fusername']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	}

	showmessage('do_success', 'home.php?mod=spacecp&ac=friend&op=request&quickforward=1');

} elseif($op == 'find') {

	$maxnum = 36;

	$recommenduser = $myfuids = $fuids =array();

	$i = 0;
	$query = DB::query("SELECT fuid, fusername FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' ORDER BY num DESC");
	while ($value = DB::fetch($query)) {
		if($i < 100) {
			$fuids[$value['fuid']] = $value['fuid'];
		}
		$myfuids[$value['fuid']] = $value['fuid'];
		$i++;
	}
	$myfuids[$space['uid']] = $space['uid'];

	$query = DB::query("SELECT * FROM ".DB::table('home_specialuser'));
	while ($value = DB::fetch($query)) {
		$recommenduser[$value['uid']] = $value;
	}

	$i = 0;
	$nearlist = array();
	$myip = explode('.', $_G['clientip']);
	$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE ip1='$myip[0]' AND ip2='$myip[1]' AND ip3='$myip[2]' LIMIT 0,200");
	while($value = DB::fetch($query)) {
		if($value['uid'] && empty($myfuids[$value['uid']])) {
			$nearlist[$value['uid']] = $value;
			$i++;
			if($i>=$maxnum) break;
		}
	}

	$i = 0;
	$friendlist = array();
	if($fuids) {
		$query = DB::query("SELECT fuid AS uid, fusername AS username FROM ".DB::table('home_friend')."
			WHERE uid IN (".dimplode($fuids).") LIMIT 0,200");
		$fuids[$space['uid']] = $space['uid'];
		while ($value = DB::fetch($query)) {
			if(empty($myfuids[$value['uid']])) {
				$friendlist[$value['uid']] = $value;
				$i++;
				if($i>=$maxnum) break;
			}
		}
	}

	$i = 0;
	$onlinelist = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_session'));
	while ($value = DB::fetch($query)) {
		if($value['uid'] && empty($myfuids[$value['uid']]) && !isset($onlinelist[$value['uid']])) {
			$onlinelist[$value['uid']] = $value;
			$i++;
			if($i>=$maxnum) break;
		}
	}
	$navtitle = lang('core', 'title_people_might_know');

} elseif($op == 'changegroup') {

	if(submitcheck('changegroupsubmit')) {
		DB::update('home_friend', array('gid'=>intval($_POST['group'])), array('uid'=>$_G['uid'], 'fuid'=>$uid));
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer(), array('gid'=>intval($_POST['group'])), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND fuid='$uid'");
	if(!$friend = DB::fetch($query)) {
		showmessage('specified_user_is_not_your_friend');
	}
	$groupselect = array($friend['gid'] => ' checked');

	$groups = friend_group_list();


} elseif($op == 'editnote') {

	if(submitcheck('editnotesubmit')) {
		$note = getstr($_POST['note'], 20, 1, 1);
		DB::update('home_friend', array('note'=>$note), array('uid'=>$_G['uid'], 'fuid'=>$uid));
		showmessage('do_success', dreferer(), array('uid'=>$uid, 'note'=>$note), array('showdialog'=>1, 'msgtype' => 2, 'closetime' => true));
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND fuid='$uid'");
	if(!$friend = DB::fetch($query)) {
		showmessage('specified_user_is_not_your_friend');
	}


} elseif($op == 'changenum') {

	if(submitcheck('changenumsubmit')) {
		$num = abs(intval($_POST['num']));
		if($num > 9999) $num = 9999;
		DB::update('home_friend', array('num'=>$num), array('uid'=>$_G['uid'], 'fuid'=>$uid));
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer(), array('fuid'=>$uid, 'num'=>$num), array('showmsg' => true, 'timeout' => 3, 'return'=>1));
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' AND fuid='$uid'");
	if(!$friend = DB::fetch($query)) {
		showmessage('specified_user_is_not_your_friend');
	}

} elseif($op == 'group') {

	if(submitcheck('groupsubmin')) {
		if(empty($_POST['fuids'])) {
			showmessage('please_correct_choice_groups_friend', dreferer());
		}
		$ids = dimplode($_POST['fuids']);
		$groupid = intval($_POST['group']);
		DB::update('home_friend', array('gid'=>$groupid), "uid='$_G[uid]' AND fuid IN ($ids)");
		friend_cache($_G['uid']);
		showmessage('do_success', dreferer());
	}

	$perpage = 50;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = array();
	$multi = $wheresql = '';

	space_merge($space, 'count');

	if($space['friends']) {

		$groups = friend_group_list();

		$theurl = 'home.php?mod=spacecp&ac=friend&op=group';
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}

		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_friend')." main
			WHERE main.uid='$space[uid]' $wheresql"), 0);
		if($count) {
			$query = DB::query("SELECT main.fuid AS uid,main.fusername AS username, main.gid, main.num FROM ".DB::table('home_friend')." main
				WHERE main.uid='$space[uid]' $wheresql
				ORDER BY main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$value['group'] = $groups[$value['gid']];
				$list[] = $value;
			}
		}
		$multi = multi($count, $perpage, $page, $theurl);
	}

	$actives = array('group'=>' class="a"');



} elseif($op == 'request') {

	if(submitcheck('requestsubmin')) {
		showmessage('do_success', dreferer());
	}

	$maxfriendnum = checkperm('maxfriendnum');
	if($maxfriendnum) {
		$maxfriendnum = $maxfriendnum + $space['addfriend'];
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = array();

	$count = getcount('home_friend_request', array('uid'=>$space['uid']));
	if($count) {
		$fuids = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend_request')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
		while ($value = DB::fetch($query)) {
			$fuids[$value['fuid']] = $value['fuid'];
			$list[$value['fuid']] = $value;
		}
	} else {

		dsetcookie('promptstate_'.$space['uid'], $newprompt, 31536000);

	}

	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=friend&op=request");

	$navtitle = lang('core', 'title_friend_request');

} elseif($op == 'groupname') {

	$groups = friend_group_list();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}
	space_merge($space, 'field_home');
	if(submitcheck('groupnamesubmit')) {
		$space['privacy']['groupname'][$group] = getstr($_POST['groupname'], 20, 1, 1);
		privacy_update();
		showmessage('do_success', dreferer(), array('gid'=>$group), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}
} elseif($op == 'groupignore') {

	$groups = friend_group_list();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}
	space_merge($space, 'field_home');
	if(submitcheck('groupignoresubmit')) {
		if(isset($space['privacy']['filter_gid'][$group])) {
			unset($space['privacy']['filter_gid'][$group]);
			$ignore = false;
		} else {
			$space['privacy']['filter_gid'][$group] = $group;
			$ignore = true;
		}
		privacy_update();
		friend_cache($_G['uid']);

		showmessage('do_success', dreferer(), array('group' => $group, 'ignore' => $ignore), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
	}

} elseif($op == 'blacklist') {

	if($_GET['subop'] == 'delete') {
		$_GET['uid'] = intval($_GET['uid']);
		DB::query("DELETE FROM ".DB::table('home_blacklist')." WHERE uid='$space[uid]' AND buid='$_GET[uid]'");
		showmessage('do_success', "home.php?mod=space&uid=$_G[uid]&do=friend&view=blacklist&quickforward=1&start=$_GET[start]");
	}

	if(submitcheck('blacklistsubmit')) {
		$_POST['username'] = trim($_POST['username']);
		$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE username='$_POST[username]'");
		if(!$tospace = DB::fetch($query)) {
			showmessage('space_does_not_exist');
		}
		if($tospace['uid'] == $space['uid']) {
			showmessage('unable_to_manage_self');
		}

		friend_delete($tospace['uid']);

		DB::insert('home_blacklist', array('uid'=>$space['uid'], 'buid'=>$tospace['uid'], 'dateline'=>$_G['timestamp']), 0, true);

		showmessage('do_success', "home.php?mod=space&uid=$_G[uid]&do=friend&view=blacklist&quickforward=1&start=$_GET[start]");
	}

} elseif($op == 'rand') {

	$userlist = $randuids = array();
	space_merge($space, 'count');
	if($space['friends']<5) {
		$query = DB::query("SELECT uid FROM ".DB::table('common_session')." LIMIT 0,100");
	} else {
		$query = DB::query("SELECT fuid as uid FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]'");
	}
	while($value = DB::fetch($query)) {
		if($value['uid'] != $space['uid']) {
			$userlist[] = $value['uid'];
		}
	}
	$randuids = sarray_rand($userlist, 1);
	showmessage('do_success', "home.php?mod=space&quickforward=1&uid=".array_pop($randuids));

} elseif ($op == 'getcfriend') {

	$fuid = empty($_GET['fuid']) ? 0 : intval($_GET['fuid']);

	$list = array();
	if($fuid) {
		$friend = $friendlist = array();
		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' OR uid='$fuid'");
		while($value = DB::fetch($query)) {
			$friendlist[$value['uid']][] = $value['fuid'];
			$friend[$value['fuid']] = $value;
		}
		if($friendlist[$_G['uid']] && $friendlist[$fuid]) {
			$cfriend = array_intersect($friendlist[$_G['uid']], $friendlist[$fuid]);
			$i = 0;
			foreach($cfriend as $key => $uid) {
				if(isset($friend[$uid])) {
					$list[] = array('uid' => $friend[$uid]['fuid'], 'username' => $friend[$uid]['fusername']);
					$i++;
					if($i >= 15) break;
				}
			}
		}

	}
} elseif($op == 'getinviteuser') {

	require_once libfile('function/search');
	$perpage = 20;
	$username = empty($_G['gp_username'])?'':searchkey($_G['gp_username'], "f.fusername LIKE '{text}%'");
	$page = empty($_G['gp_page'])?0:intval($_G['gp_page']);
	$gid = isset($_G['gp_gid']) ? intval($_G['gp_gid']) : -1;
	if($page<1) $page = 1;
	$start = ($page-1) * $perpage;
	$json = array();
	$wheresql = '';
	if($gid > -1) {
		$wheresql .= " AND f.gid='$gid'";
	}
	if(!empty($username)) {
		$wheresql .= $username;
	}
	$singlenum = 0;
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('home_friend')." f WHERE f.uid='$_G[uid]' $wheresql");
	if($count) {
		$query = DB::query("SELECT f.*, m.username FROM ".DB::table('home_friend')." f LEFT JOIN ".DB::table('common_member')." m ON f.fuid=m.uid WHERE f.uid='$_G[uid]' $wheresql ORDER BY f.num DESC, f.dateline DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {
			$value['fusername'] = daddslashes($value['username']);
			$value['avatar'] = avatar($value['fuid'], 'small', true);
			$singlenum++;
			$json[$value['fuid']] = "$value[fuid]:{'uid':$value[fuid], 'username':'$value[fusername]', 'avatar':'$value[avatar]'}";
		}
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}, 'maxfriendnum':'$count', 'singlenum':'$singlenum'}";

} elseif($op == 'search') {

	$searchkey = stripsearchkey($_GET['searchkey']);
	if(strlen($searchkey) < 2) {
		showmessage('username_less_two_chars');
	}

	$list = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE username LIKE '%$searchkey%' LIMIT 0,100");
	while ($value = DB::fetch($query)) {
		$list[$value['uid']] = $value;
	}
	$navtitle = lang('core', 'title_search_friend');
}

include template('home/spacecp_friend');

?>