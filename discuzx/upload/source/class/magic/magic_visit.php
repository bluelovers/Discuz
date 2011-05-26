<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_money.php 7830 2010-04-14 02:22:32Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_visit {

	var $version = '1.0';
	var $name = 'visit_name';
	var $description = 'visit_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		$settings = array(
			'num' => array(
				'title' => 'visit_num',
				'type' => 'select',
				'value' => array(
					array('5', '5'),
					array('10', '10'),
					array('20', '20'),
				),
				'default' => '10'
			),
		);
		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		$magicnew['num'] = in_array($parameters['num'], array(5,10,20,50)) ? intval($parameters['num']) : '10';
	}

	function usesubmit() {
		global $_G;

		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		$friends = $uids = $fids = array();
		$query = DB::query('SELECT fuid as uid, fusername as username FROM '.DB::table('home_friend')." WHERE uid='$_G[uid]' LIMIT 500");
		while($value=DB::fetch($query)) {
			$uids[] = intval($value['uid']);
			$friends[$value['uid']] = $value;
		}
		$count = count($uids);
		if(!$count) {
			showmessage('magicuse_has_no_valid_friend');
		} elseif($count == 1) {
			$fids = array($uids[0]);
		} else {
			$keys = array_rand($uids, min($num, $count));
			$fids = array();
			foreach ($keys as $key) {
				$fids[] = $uids[$key];
			}
		}
		$users = array();
		foreach($fids as $uid) {
			$value = $friends[$uid];
			$value['avatar'] = str_replace("'", "\'", avatar($value['uid'], 'small'));
			$users[$uid] = $value;
		}

		$inserts = array();
		if($_POST['visitway'] == 'poke') {
			$note = '';
			$icon = intval($_POST['visitpoke']);
			foreach ($fids as $fid) {
				$inserts[] = "('$fid', '$_G[uid]', '$_G[username]', '$note', '$_G[timestamp]', '$icon')";
			}
			$repokeids = array();
			$query = DB::query("SELECT * FROM ".DB::table('home_poke')." WHERE uid IN (".dimplode($fids).") AND fromuid = '$_G[uid]'");
			while ($value = DB::fetch($query)) {
				$repokeids[] = $value['uid'];
			}
			DB::query('REPLACE INTO '.DB::table('home_poke').'(uid, fromuid, fromusername, note, dateline, iconid) VALUES '.implode(',',$inserts));
			$ids = array_diff($fids, $repokeids);
			if($ids) {
				require_once libfile('function/spacecp');
				$pokemsg = makepokeaction($icon);
				$pokenote = array(
							'fromurl' => 'home.php?mod=space&uid='.$_G['uid'],
							'fromusername' => $_G['username'],
							'fromuid' => $_G['uid'],
							'from_id' => $_G['uid'],
							'from_idtype' => 'pokequery',
							'pokemsg' => $pokemsg
						);
				foreach($ids as $puid) {
					notification_add($puid, 'poke', 'poke_request', $pokenote);
				}
			}
		} elseif($_POST['visitway'] == 'comment') {
			$message = getstr($_POST['visitmsg'], 255, 1, 1);
			$ip = $_G['clientip'];
			$note_inserts = array();
			foreach ($fids as $fid) {
				$actor = "<a href=\"home.php?mod=space&uid=$_G[uid]\">$_G[username]</a>";
				$inserts[] = "('$fid', '$fid', 'uid', '$_G[uid]', '$_G[username]','$ip', '$_G[timestamp]', '$message')";
				$note = lang('spacecp', 'magic_note_wall', array('actor' => $actor, 'url'=>"home.php?mod=space&uid=$fid&do=wall"));
				$note_inserts[] = "('$fid', 'comment', '1', '$_G[uid]', '$_G[username]', '$note', '$_G[timestamp]')";
			}
			DB::query('INSERT INTO '.DB::table('home_comment')."(uid, id, idtype, authorid, author, ip, dateline, message) VALUES ".implode(",", $inserts));
			DB::query('INSERT INTO '.DB::table('home_notification')."(uid, type, new, authorid, author, note, dateline) VALUES ".implode(",",$note_inserts));
			DB::query('UPDATE '.DB::table('common_member')." SET newprompt = newprompt + 1 WHERE uid IN (".dimplode($fids).")");
		} else {
			foreach ($fids as $fid) {
				$inserts[] = "('$fid', '$_G[uid]', '$_G[username]', '$_G[timestamp]')";
			}
			DB::query('REPLACE INTO '.DB::table('home_visitor')."(uid, vuid, vusername, dateline) VALUES ".implode(",",$inserts));
		}
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', 'uid', $_G['uid']);

		$op = 'show';
		include template('home/magic_visit');
	}

	function show() {
		global $_G;
		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		magicshowtips(lang('magic/visit', 'visit_info', array('num'=>$num)));
		$op = 'use';
		include template('home/magic_visit');
	}

}

?>