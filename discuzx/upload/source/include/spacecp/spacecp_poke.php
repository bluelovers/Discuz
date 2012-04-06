<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_poke.php 20203 2011-02-17 07:14:41Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

if($uid == $_G['uid']) {
	showmessage('not_to_their_own_greeted');
}

if($op == 'send' || $op == 'reply') {

	if(!checkperm('allowpoke')) {
		showmessage('no_privilege_poke');
	}

	cknewuser();

	$tospace = array();

	if($uid) {
		$tospace = getspace($uid);
	} elseif ($_POST['username']) {
		$tospace = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_POST[username]' LIMIT 1");
	}

	if($tospace && isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	if(submitcheck('pokesubmit')) {
		if(empty($tospace)) {
			showmessage('space_does_not_exist');
		}

		$notetext = getstr($_POST['note'], 150, 1, 1);
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

		require_once libfile('function/friend');
		friend_addnum($tospace['uid']);

		if($op == 'reply') {
			DB::query("DELETE FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' AND fromuid='$uid'");
			DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt-'1' WHERE uid='$_G[uid]'");
		}
		updatecreditbyaction('poke', 0, array(), $uid);

		if($setarr['iconid']) {
			require_once libfile('function/spacecp');
			$pokemsg = makepokeaction($setarr['iconid']);
		} else {
			$pokemsg = lang('home/template', 'say_hi');
		}
		if(!empty($setarr['note'])) {
			$pokemsg .= ', '.lang('home/template', 'say').':'.$setarr['note'];
		}

		$note = array(
				'fromurl' => 'home.php?mod=space&uid='.$_G['uid'],
				'fromusername' => $_G['username'],
				'fromuid' => $_G['uid'],
				'from_id' => $_G['uid'],
				'from_idtype' => 'pokequery',
				'pokemsg' => $pokemsg
			);
		notification_add($uid, 'poke', 'poke_request', $note);

		include_once libfile('function/stat');
		updatestat('poke');

		showmessage('poke_success', dreferer(), array('username' => $tospace['username'], 'uid' => $uid, 'from' => $_G['gp_from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));

	}

} elseif($op == 'ignore') {
	if(submitcheck('ignoresubmit')) {
		$where = empty($uid)?'':"AND fromuid='$uid'";
		DB::query("DELETE FROM ".DB::table('home_poke')." WHERE uid='$_G[uid]' $where");

		showmessage('has_been_hailed_overlooked', '', array('uid' => $uid, 'from' => $_G['gp_from']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => 0));
	}

} elseif($op == 'view') {

	$_GET['uid'] = intval($_GET['uid']);

	$list = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE uid='$space[uid]' AND fromuid='$_GET[uid]'");
	if($value = DB::fetch($query)) {
		$pokeuid = $value['uid']+$value['fromuid'];

		$value['uid'] = $value['fromuid'];
		$value['username'] = $value['fromusername'];

		require_once libfile('function/friend');
		$value['isfriend'] = $value['uid']==$space['uid'] || friend_check($value['uid']) ? 1 : 0;

		$subquery = DB::query("SELECT * FROM ".DB::table('home_pokearchive')." WHERE pokeuid='$pokeuid' ORDER BY dateline");
		while ($subvalue = DB::fetch($subquery)) {
			$list[$subvalue['pid']] = $subvalue;
		}

	}

} else {

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$fuids = $list = array();
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_poke')." WHERE uid='$space[uid]'"), 0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value['uid'] = $value['fromuid'];
			$value['username'] = $value['fromusername'];

			$fuids[$value['uid']] = $value['uid'];
			$list[$value['uid']] = $value;
		}
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

}

$actives = array($op=='send'?'send':'poke' =>' class="a"');

include_once template('home/spacecp_poke');

?>