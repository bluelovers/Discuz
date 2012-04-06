<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_domain.php 20556 2011-02-25 10:19:29Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$domainlength = checkperm('domainlength');

if($_G['setting']['allowspacedomain'] && !empty($_G['setting']['domain']['root']['home']) && $domainlength) {
	checklowerlimit('modifydomain');
} else {
	showmessage('no_privilege_spacedomain');
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

	showmessage('domain_succeed', 'home.php?mod=spacecp&ac=domain');
}

$result = DB::fetch_first("SELECT * FROM ".DB::table('common_setting')." WHERE skey='profilegroup'");
$defaultop = '';
if(!empty($result['svalue'])) {
	$profilegroup = unserialize($result['svalue']);
}

$actives = array('profile' =>' class="a"');
$opactives = array('domain' =>' class="a"');

include_once template("home/spacecp_domain");

?>