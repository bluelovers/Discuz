<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_domain.php 16639 2010-09-11 02:50:00Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$domainlength = checkperm('domainlength');

if($_G['setting']['allowspacedomain'] && !empty($_G['setting']['domain']['root']['home']) && $domainlength) {
	checklowerlimit('modifydomain');
} else {
	showmessage('no_privilege');
}

if(submitcheck('domainsubmit')) {

	$setarr = array();
	$_POST['domain'] = strtolower(trim($_POST['domain']));
	if($_POST['domain'] != $space['domain']) {

		if(empty($domainlength) || empty($_POST['domain'])) {
			$setarr['domain'] = '';
		} else {
			require_once libfile('function/domain');
			if(domaincheck($_POST['domain'], $_G['setting']['domain']['root']['home'], $domainlength)) {
				$setarr['domain'] = $_POST['domain'];
			}
		}
	}
	if($setarr) {
		updatecreditbyaction('modifydomain');
		DB::update('common_member_field_home', $setarr, array('uid' => $_G['uid']));
		require_once libfile('function/delete');
		deletedomain($_G['uid'], 'home');
		if(!empty($setarr['domain'])) {
			DB::insert('common_domain', array('domain' => $setarr['domain'], 'domainroot' => addslashes($_G['setting']['domain']['root']['home']), 'id' => $_G['uid'], 'idtype' => 'home'));
		}
	}

	showmessage('do_success', 'home.php?mod=spacecp&ac=domain');
}
$actives = array('profile' =>' class="a"');
$opactives = array('domain' =>' class="a"');

include_once template("home/spacecp_domain");

?>