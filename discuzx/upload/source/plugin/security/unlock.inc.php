<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$referer = dreferer();
$seccodeverify = !empty($_G['gp_seccodeverify']) ? $_G['gp_seccodeverify'] : '';
$sechash = !empty($_G['gp_sechash']) ? $_G['gp_sechash'] : '';

if (submitcheck('securitysubmit')){
	$status = check_seccode($seccodeverify, $sechash) ? TRUE :FALSE;
	if ($status) {
		$user = DB::fetch_first("SELECT uid, credits FROM ".DB::table('common_member')." WHERE uid = $_G[uid]");
		$usergroup = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type = 'member' AND creditshigher <= $user[credits] AND creditslower > $user[credits]");

		$data = array(
			'groupid' => $usergroup,
		);

		DB::update('common_member', $data, "uid = $_G[uid]");

		showmessage('security:unlock_success',$referer);
	} else {
		showmessage('security:wrongcode',$referer);
	}
}

include template('security:seccode');
?>