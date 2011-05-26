<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_top.php 21195 2011-03-18 06:55:50Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$operation = in_array($_G['gp_op'], array('modify')) ? trim($_G['gp_op']) : '';
if($_G['setting']['creditstransextra'][6]) {
	$key = 'extcredits'.intval($_G['setting']['creditstransextra'][6]);
} elseif ($_G['setting']['creditstrans']) {
	$key = 'extcredits'.intval($_G['setting']['creditstrans']);
} else {
	showmessage('trade_credit_invalid', '', array(), array('return' => 1));
}
space_merge($space, 'count');

if(submitcheck('friendsubmit')) {

	$showcredit = intval($_POST['stakecredit']);
	if($showcredit > $space[$key]) $showcredit = $space[$key];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}

	$_POST['fusername'] = trim($_POST['fusername']);
	$friend = DB::fetch(DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' AND fusername='$_POST[fusername]'"));
	$fuid = $friend['fuid'];
	if(empty($_POST['fusername']) || empty($fuid) || $fuid == $space['uid']) {
		showmessage('showcredit_fuid_error', '', array(), array('return' => 1));
	}

	$count = getcount('home_show', array('uid'=>$fuid));
	if($count) {
		DB::query("UPDATE ".DB::table('home_show')." SET credit=credit+$showcredit WHERE uid='$fuid'");
	} else {
		DB::insert('home_show', array('uid'=>$fuid, 'username'=>$_POST['fusername'], 'credit'=>$showcredit), 0, true);
	}

	updatemembercount($space['uid'], array($_G['setting']['creditstransextra'][6] => (0-$showcredit)), true, 'RKC', $space['uid']);

	notification_add($fuid, 'credit', 'showcredit', array('credit'=>$showcredit));


	if(ckprivacy('show', 'feed')) {
		require_once libfile('function/feed');
		feed_add('show', 'feed_showcredit', array(
		'fusername' => "<a href=\"home.php?mod=space&uid=$fuid\">{$friend[fusername]}</a>",
		'credit' => $showcredit));
	}

	showmessage('showcredit_friend_do_success', "misc.php?mod=ranklist&type=member");

} elseif(submitcheck('showsubmit')) {

	$showcredit = intval($_POST['showcredit']);
	$unitprice = intval($_POST['unitprice']);
	if($showcredit > $space[$key]) $showcredit = $space[$key];
	if($showcredit < 1 || $unitprice < 1) {
		showmessage('showcredit_error', '', array(), array('return' => 1));
	}
	$_POST['note'] = getstr($_POST['note'], 100, 1, 1);
	$_POST['note'] = censor($_POST['note']);
	$showarr = DB::fetch_first("SELECT * FROM ".DB::table('home_show')." WHERE uid='$_G[uid]'");
	if($showarr) {
		$notesql = $_POST['note']?", note='$_POST[note]'":'';
		$unitprice = $unitprice > $showarr['credit']+$showcredit ? $showarr['credit']+$showcredit : $unitprice;
		DB::query("UPDATE ".DB::table('home_show')." SET credit=credit+$showcredit, unitprice='$unitprice' $notesql WHERE uid='$_G[uid]'");
	} else {
		$unitprice = $unitprice > $showcredit ? $showcredit : $unitprice;
		DB::insert('home_show', array('uid'=>$_G['uid'], 'username'=>$_G['username'], 'unitprice' => $unitprice, 'credit'=>$showcredit, 'note'=>$_POST['note']), 0, true);
	}

	updatemembercount($space['uid'], array($_G['setting']['creditstransextra'][6] => (0-$showcredit)), true, 'RKC', $space['uid']);

	if(ckprivacy('show', 'feed')) {
		require_once libfile('function/feed');
		feed_add('show', 'feed_showcredit_self', array('credit'=>$showcredit), '', array(), $_POST['note']);
	}

	showmessage('showcredit_do_success', dreferer());
}
?>