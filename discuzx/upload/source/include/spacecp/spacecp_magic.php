<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_magic.php 20078 2011-02-12 07:23:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$space['credit'] = $space['credits'];

$op = empty($_GET['op']) ? "view" : $_GET['op'];
$mid = empty($_GET['mid']) ? '' : trim($_GET['mid']);

if(!checkperm('allowmagics')) {
	showmessage('magic_groupid_not_allowed');
}

if($op == 'cancelflicker') {

	$mid = 'flicker';
	$_GET['idtype'] = 'cid';
	$_GET['id'] = intval($_GET['id']);
	$query = DB::query('SELECT * FROM '.DB::table('home_comment')." WHERE cid = '$_GET[id]' AND authorid = '$_G[uid]'");
	$value = DB::fetch($query);
	if(!$value || !$value['magicflicker']) {
		showmessage('no_flicker_yet');
	}

	if(submitcheck('cancelsubmit')) {
		DB::update('home_comment', array('magicflicker'=>0), array('cid'=>$_GET['id'], 'authorid'=>$_G['uid']));
		showmessage('do_success', dreferer(), array(), array('showdialog' => 1, 'closetime' => true));
	}

} elseif($op == 'cancelcolor') {

	$mid = 'color';
	$_GET['id'] = intval($_GET['id']);
	$mapping = array('blogid'=>'blogfield', 'tid'=>'thread');
	$tablename = $mapping[$_GET['idtype']];
	if(empty($tablename)) {
		showmessage('no_color_yet');
	}
	$query = DB::query('SELECT * FROM '.DB::table($tablename)." WHERE $_GET[idtype] = '$_GET[id]' AND uid = '$_G[uid]'");
	$value = DB::fetch($query);
	if(!$value || !$value['magiccolor']) {
		showmessage('no_color_yet');
	}

	if(submitcheck('cancelsubmit')) {
		DB::update($tablename, array('magiccolor'=>0), array($_GET['idtype']=>$_GET[id]));
		$query = DB::query('SELECT * FROM '.DB::table('home_feed')." WHERE id = '$_GET[id]' AND idtype = '$_GET[idtype]'");
		$feed = DB::fetch($query);
		if($feed) {
			$feed['body_data'] = unserialize($feed['body_data']);
			if($feed['body_data']['magic_color']) {
				unset($feed['body_data']['magic_color']);
			}
			$feed['body_data'] = serialize($feed['body_data']);
			DB::update('home_feed', array('body_data'=>$feed['body_data']), array('feedid'=>$feed['feedid']));
		}
		showmessage('do_success', dreferer(), 0);
	}

} elseif($op == 'receivegift') {

	$uid = intval($_GET['uid']);
	$mid = 'gift';
	$info = DB::result_first('SELECT magicgift FROM '.DB::table('common_member_field_home')." WHERE uid = '$uid'");
	$info = $info ? unserialize($info) : array();
	if(!empty($info['left'])) {
		$info['receiver'] = is_array($info['receiver']) ? $info['receiver'] : array();
		if(in_array($_G['uid'], $info['receiver'])) {
			showmessage('haved_red_bag');
		}
		$percredit = min($info['left'], $info['percredit']);
		$info['receiver'][] = $_G['uid'];
		$info['left'] = $info['left'] - $percredit;
		if($info['left'] > 0) {
			DB::update('common_member_field_home', array('magicgift'=>addslashes(serialize($info))), array('uid'=>$uid));
		} else {
			DB::update('common_member_field_home', array('magicgift'=>''), array('uid'=>$uid));
		}
		$credittype = '';
		if(preg_match('/^extcredits[1-8]$/', $info['credittype'])) {
			$extcredits = str_replace('extcredits', '', $info['credittype']);
			updatemembercount($_G['uid'], array($extcredits => $percredit), 1, 'AGC', $info['magicid']);
			$credittype = $_G['setting']['extcredits'][$extcredits]['title'];
		}
		showmessage('haved_red_bag_gain', dreferer(), array('percredit' => $percredit, 'credittype' => $credittype), array('showdialog' => 1, 'locationtime' => true));
	}
	showmessage('space_no_red_bag', dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));

} elseif($op == 'retiregift') {

	$mid = 'gift';
	$info = DB::result_first('SELECT magicgift FROM '.DB::table('common_member_field_home')." WHERE uid = '$_G[uid]'");
	$info = $info ? unserialize($info) : array();
	$leftcredit = intval($info['left']);
	if($leftcredit<=0) {
		DB::update('common_member_field_home', array('magicgift'=>''), array('uid'=>$_G['uid']));
		showmessage('red_bag_no_credits');
	}

	$extcredits = str_replace('extcredits', '', $info['credittype']);
	$credittype = $_G['setting']['extcredits'][$extcredits]['title'];

	if(submitcheck('cancelsubmit')) {
		DB::update('common_member_field_home', array('magicgift'=>''), array('uid'=>$_G['uid']));
		if(preg_match('/^extcredits[1-8]$/', $info['credittype'])) {
			updatemembercount($_G['uid'], array($extcredits => $leftcredit), 1, 'RGC', $info['magicid']);
		}
		showmessage('return_red_bag', dreferer(), array('leftcredit' => $leftcredit, 'credittype' => $credittype), array('showdialog' => 1, 'locationtime' => true));
	}
}

include_once template('home/spacecp_magic');

?>