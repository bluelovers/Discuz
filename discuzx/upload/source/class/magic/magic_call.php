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

class magic_call {

	var $version = '1.0';
	var $name = 'call_name';
	var $description = 'call_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {}

	function setsetting(&$magicnew, &$parameters) {}

	function usesubmit() {
		global $_G;

		$id = intval($_G['gp_id']);
		$idtype = $_G['gp_idtype'];
		$blog = magic_check_idtype($id, $idtype);

		$num = 10;
		$list = $ids = $note_inserts = array();
		$fusername = dimplode($_POST['fusername']);
		if($fusername) {
			$query = DB::query('SELECT * FROM '.DB::table('home_friend')." WHERE uid='$_G[uid]' AND fusername IN (".$fusername.") LIMIT $num");
			$note = lang('spacecp', 'magic_call', array('url'=>"home.php?mod=space&uid=$_G[uid]&do=blog&id=$id"));
			while($value = DB::fetch($query)) {
				$ids[] = $value['fuid'];
				$value['avatar'] = str_replace("'", "\'", avatar($value[fuid],'small'));
				$list[] = $value;
				$note_inserts[] = "('$value[fuid]', '$name', '1', '$_G[uid]', '$_G[username]', '$note', '$_G[timestamp]')";
			}
		}
		if(empty($ids)) {
			showmessage('magicuse_has_no_valid_friend');
		}
		DB::query('INSERT INTO '.DB::table('home_notification').'(uid, type, new, authorid, author, note, dateline) VALUES '.implode(',',$note_inserts));
		DB::query('UPDATE '.DB::table('common_member').' SET newprompt = newprompt + 1 WHERE uid IN ('.dimplode($ids).')');

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', $idtype, $id);

		$op = 'show';
		include template('home/magic_call');
	}

	function show() {
		$id = intval($_GET['id']);
		$idtype = $_GET['idtype'];
		magic_check_idtype($id, $idtype);
		magicshowtips(lang('magic/call', 'call_info'));
		$op = 'use';
		include template('home/magic_call');
	}
}

?>