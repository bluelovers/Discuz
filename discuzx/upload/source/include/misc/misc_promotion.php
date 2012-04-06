<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_promotion.php 11620 2010-06-09 09:46:59Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $_G;

if(!empty($_G['gp_fromuid'])) {
	$fromuid = intval($_G['gp_fromuid']);
	$fromuser = '';
} else {
	$fromuser = $_G['gp_fromuser'];
	$fromuid = '';
}

if(!$_G['uid'] || !($fromuid == $_G['uid'] || $fromuser == $_G['username'])) {

	if($_G['setting']['creditspolicy']['promotion_visit']) {
		if(!DB::fetch_first("SELECT * FROM ".DB::table('forum_promotion')." WHERE ip='$_G[clientip]'")) {
			DB::query("REPLACE INTO ".DB::table('forum_promotion')." (ip, uid, username)
				VALUES ('$_G[clientip]', '$fromuid', '$fromuser')");
			updatecreditbyaction('promotion_visit', $fromuid);
		}
	}

	if($_G['setting']['creditspolicy']['promotion_register']) {
		if(!empty($fromuser) && empty($fromuid)) {
			if(empty($_G['cookie']['promotion'])) {
				$fromuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$fromuser'");
			} else {
				$fromuid = intval($_G['cookie']['promotion']);
			}
		}
		if($fromuid) {
			dsetcookie('promotion', ($_G['cookie']['promotion'] = $fromuid), 1800);
		}
	}

}

?>