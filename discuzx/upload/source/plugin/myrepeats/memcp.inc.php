<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memcp.inc.php 18014 2010-11-10 05:52:07Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

$myrepeatsusergroups = (array)unserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
if(in_array('', $myrepeatsusergroups)) {
	$myrepeatsusergroups = array();
}
$singleprem = FALSE;
$permusers = array();
if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
	$singleprem = TRUE;
}

$query = DB::query("SELECT * FROM ".DB::table('myrepeats')." WHERE username='$_G[username]'");
while($user = DB::fetch($query)) {
	$permusers[] = $user['uid'];
}
$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($permusers).")");
$permusers = array();
while($user = DB::fetch($query)) {
	$permusers[] = $user['username'];
}

if(!$permusers && $singleprem) {
	showmessage('myrepeats:usergroup_disabled');
}

if($_G['gp_pluginop'] == 'add' && submitcheck('adduser')) {
	if($singleprem && in_array(stripslashes($_G['gp_usernamenew']), $permusers) || !$singleprem) {
		$usernamenew = strip_tags($_G['gp_usernamenew']);
		$logindata = addslashes(authcode($_G['gp_passwordnew']."\t".$_G['gp_questionidnew']."\t".$_G['gp_answernew'], 'ENCODE', $_G['config']['security']['authkey']));
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]' AND username='$usernamenew'")) {
			DB::query("UPDATE ".DB::table('myrepeats')." SET logindata='$logindata' WHERE uid='$_G[uid]' AND username='$usernamenew'");
		} else {
			DB::query("INSERT INTO ".DB::table('myrepeats')." (uid, username, logindata, comment) VALUES ('$_G[uid]', '$usernamenew', '$logindata', '".strip_tags($_G['gp_commentnew'])."')");
		}
		dsetcookie('mrn', '');
		dsetcookie('mrd', '');
		$usernamenew = stripslashes($usernamenew);
		showmessage('myrepeats:adduser_succeed', 'home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp', array('usernamenew' => $usernamenew));
	}
} elseif($_G['gp_pluginop'] == 'update' && submitcheck('updateuser')) {
	if(!empty($_G['gp_delete'])) {
		DB::query("DELETE FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]' AND username IN (".dimplode($_G['gp_delete']).")");
	}
	foreach($_G['gp_comment'] as $user => $v) {
		DB::query("UPDATE ".DB::table('myrepeats')." SET comment='".strip_tags($v)."' WHERE uid='$_G[uid]' AND username='$user'");
	}
	dsetcookie('mrn', '');
	dsetcookie('mrd', '');
	showmessage('myrepeats:updateuser_succeed', 'home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp');
}

$username = empty($_G['gp_username']) ? '' : htmlspecialchars(stripslashes($_G['gp_username']));

$repeatusers = array();
$query = DB::query("SELECT * FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]'");
while($myrepeat = DB::fetch($query)) {
	$myrepeat['lastswitch'] = $myrepeat['lastswitch'] ? dgmdate($myrepeat['lastswitch']) : '';
	$myrepeat['usernameenc'] = rawurlencode($myrepeat['username']);
	$myrepeat['comment'] = htmlspecialchars($myrepeat['comment']);
	$repeatusers[] = $myrepeat;
}

$_G['basescript'] = 'home';

?>