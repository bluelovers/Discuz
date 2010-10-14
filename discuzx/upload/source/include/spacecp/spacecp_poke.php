<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_poke.php 16279 2010-09-02 09:33:15Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

if($uid == $_G['uid']) {
	showmessage('not_to_their_own_greeted');
}

//BUG: 由於 pokeuid 為使用 UID 相加所以在某些狀況下可能造成讀取到不是自己的紀錄

if($op == 'send' || $op == 'reply') {

	if(!checkperm('allowpoke')) {
		showmessage('no_privilege');
	}

	ckrealname('poke');

	cknewuser();

	$tospace = array();

	if($uid) {
		$tospace = getspace($uid);
	} elseif ($_POST['username']) {
		$tospace = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_POST[username]' LIMIT 1");
	}

	// bluelovers
	// 修正當使用用戶名作為輸入時 UID 沒有進行更正的 BUG
	$uid = $tospace['uid'];
	// bluelovers

	if($tospace['videophotostatus']) {
		ckvideophoto('poke', $tospace);
	}

	if($tospace && isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	if(submitcheck('pokesubmit')) {
		if(empty($tospace)) {
			showmessage('space_does_not_exist');
		}

		// bluelovers
		// 修正短時間內連發的BUG
		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', array('waittime' => $waittime));
		}
		// bluelovers

//		$oldpoke = getcount('home_poke', array('uid'=>$uid, 'fromuid'=>$_G['uid']));
		$oldpoke = getcount('home_poke', array('uid'=>$uid, 'fromuid'=>$_G['uid'], 'ignore' => 0));

//		$notetext = getstr($_POST['note'], 150, 1, 1);
		$notetext = getstr(trim($_POST['note']), 255, 1, 1);
		$notetext = censor($notetext);
		$setarr = array(
			'pokeuid' => $uid+$_G['uid'],
			'uid' => $uid,
			'fromuid' => $_G['uid'],
			'note' => $notetext, //need to do
			'dateline' => $_G['timestamp'],
			'iconid' => intval($_POST['iconid'])
		);
		DB::insert('home_pokearchive', $setarr);

		$setarr = array(
			'uid' => $uid,
			'fromuid' => $_G['uid'],
			'fromusername' => $_G['username'],
			'note' => getstr($_POST['note'], 150, 1, 1),
			'dateline' => $_G['timestamp'],
			'iconid' => intval($_POST['iconid'])
		);

		DB::insert('home_poke', $setarr, 0, true);

		if(!$oldpoke) {
			DB::query("UPDATE ".DB::table('common_member_status')." SET pokes=pokes+1 WHERE uid='$uid'");
			DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt+1 WHERE uid='$uid'");
		}

		require_once libfile('function/friend');
		friend_addnum($tospace['uid']);

		if($op == 'reply') {
//			DB::query("DELETE FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' AND fromuid='$uid'");
			DB::update('home_poke', array('ignore' => 1), "uid='$_G[uid]' AND fromuid='$uid'");
			DB::query("UPDATE ".DB::table('common_member_status')." SET pokes=pokes-'1' WHERE uid='$_G[uid]'");
			DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt-'1' WHERE uid='$_G[uid]'");
		}
		updatecreditbyaction('poke', 0, array(), $uid);

		include_once libfile('function/stat');
		updatestat('poke');

		showmessage('poke_success', dreferer(), array('username' => $tospace['username'], 'uid' => $uid, 'from' => $_G['gp_from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));

	}

// bluelovers
} elseif($op == 'delete') {
	if(submitcheck('ignoresubmit')) {
		$where = "AND fromuid='$uid'";
		DB::query("DELETE FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' $where");
		DB::query("DELETE FROM ".DB::table('home_pokearchive')." WHERE uid='$_G[uid]' $where");
		$pokenum = getcount('home_poke', array('uid'=>$_G['uid'], 'ignore' => 0));

		space_merge($space, 'status');
		if($pokenum != $space['pokes']) {
			$changenum = $pokenum - $space['pokes'];
			member_status_update($space['uid'], array('pokes' => $changenum));
		}
		showmessage('has_been_hailed_overlooked', '', array('uid' => $uid, 'from' => $_G['gp_from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 0));
	}
// bluelovers

} elseif($op == 'ignore') {
	if(submitcheck('ignoresubmit')) {
		$where = empty($uid)?'':"AND fromuid='$uid'";
//		DB::query("DELETE FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' $where");
//		$pokenum = getcount('home_poke', array('uid'=>$_G['uid']));

		DB::update('home_poke', array('ignore' => 1), "uid='$_G[uid]' $where");
		$pokenum = getcount('home_poke', array('uid'=>$_G['uid'], 'ignore' => 0));

		space_merge($space, 'status');
		if($pokenum != $space['pokes']) {
			$changenum = $pokenum - $space['pokes'];
			member_status_update($space['uid'], array('pokes' => $changenum));
		}
		showmessage('has_been_hailed_overlooked', '', array('uid' => $uid, 'from' => $_G['gp_from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 0));
	}

} elseif($op == 'view') {

	$_GET['uid'] = intval($_GET['uid']);

	// bluelovers
	$sub_ok = false;
	// bluelovers

	$list = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE uid='$space[uid]' AND fromuid='$_GET[uid]'");
	if($value = DB::fetch($query)) {
		$pokeuid = $value['uid']+$value['fromuid'];

		$value['uid'] = $value['fromuid'];
		$value['username'] = $value['fromusername'];

		require_once libfile('function/friend');
		$value['isfriend'] = $value['uid']==$space['uid'] || friend_check($value['uid']) ? 1 : 0;

//		$subquery = DB::query("SELECT * FROM ".DB::table('home_pokearchive')." WHERE pokeuid='$pokeuid' ORDER BY dateline");
		$subquery = DB::query("SELECT * FROM ".DB::table('home_pokearchive')." WHERE (uid='$_GET[uid]' AND fromuid='$space[uid]') OR (uid='$space[uid]' AND fromuid='$_GET[uid]') ORDER BY dateline");
		while ($subvalue = DB::fetch($subquery)) {

			// bluelovers
			$_user = getuserbyuid($value['fromuid']);
			$subvalue['fromusername'] = $_user['username'];

			$_user = getuserbyuid($value['uid']);
			$subvalue['username'] = $_user['username'];
			// bluelovers

			$list[$subvalue['pid']] = $subvalue;
		}

		// bluelovers
		if (!$sub_ok) {
			$list[] = $value;
		}
		// bluelovers
	}

	// bluelovers
	if (!$sub_ok && $value2 = DB::fetch_first("SELECT * FROM ".DB::table('home_poke')." WHERE (uid='$_GET[uid]' AND fromuid='$space[uid]')")) {
//		$value2['uid'] = $value2['fromuid'];
//		$value2['username'] = $value2['fromusername'];

		$_user = getuserbyuid($value2['uid']);
		$value2['username'] = $_user['username'];

		$list[] = $value2;
	}

//	dexit("SELECT * FROM ".DB::table('home_poke')." WHERE (uid='$_GET[uid]' AND fromuid='$space[uid]')");
	// bluelovers

} elseif($op == 'getpoke') {
	$pokequery = DB::fetch_first("SELECT * FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' ORDER BY dateline DESC LIMIT 1, 1");
} else {

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	// bluelovers
	$ignore = 0;
	if ($op == 'myignore') $ignore = 1;
	// bluelovers

	$fuids = $list = array();
//	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_poke')." WHERE uid='$space[uid]'"), 0);
	$pokenum = $count = getcount('home_poke', $op == 'mysend' ? array('fromuid' => $space['uid'], 'ignore' => $ignore) : array('uid' => $space['uid'], 'ignore' => 0));
	if($count) {

		// bluelovers
		$lostuid = array();
		// bluelovers

//		$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT $start,$perpage");
		$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE ".($op == 'mysend' ? "fromuid='$space[uid]'" : "uid='$space[uid]'")." AND `ignore`='$ignore' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
//			$value['uid'] = $value['fromuid'];
//			$value['username'] = $value['fromusername'];

			// bluelovers
			if ($op == 'mysend') {
//				$value['uid'] = $value['fromuid'];
//				$value['username'] = $value['fromusername'];
//				$lostuid[] = $value['uid'];

				$_user = getuserbyuid($value['uid']);
				$value['username'] = $_user['username'];
			} else {
				$value['uid'] = $value['fromuid'];
				$value['username'] = $value['fromusername'];
			}
			// bluelovers

			$fuids[$value['uid']] = $value['uid'];
			$list[$value['uid']] = $value;
		}

//		dexit(array($space['uid'], $list));

		if($fuids) {
			require_once libfile('function/friend');
			friend_check($fuids);

			$value = array();
			foreach($fuids as $key => $fuid) {
				$value['isfriend'] = $fuid==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$fuid] ? 1 : 0;
				$list[$fuid] = array_merge($list[$fuid], $value);
			}

		}
	}
	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=poke");

	// bluelovers
	($op == 'mysend' || $ignore) && $pokenum = $space['pokes'];
	// bluelovers

//	$pokenum = getcount('home_poke', array('uid'=>$space['uid']));
	space_merge($space, 'status');
	if($pokenum != $space['pokes']) {
		$changenum = $pokenum - $space['pokes'];
		member_status_update($space['uid'], array('pokes' => $changenum));
	}

}

//$actives = array($op=='send'?'send':'poke' =>' class="a"');
$actives = array((($op == 'send' || $op == 'mysend' || $op == 'myignore') ? $op : 'poke')  =>' class="a"');

include_once template('home/spacecp_poke');

?>