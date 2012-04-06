<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_home.php 19561 2011-01-10 02:28:57Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


if($op == 'addnote' && submitcheck('submit')) {
	$newaccess = 4 + ($_G['gp_newaccess'][2] << 1) + $_G['gp_newaccess'][3];
	$newexpiration = TIMESTAMP + (intval($_G['gp_newexpiration']) > 0 ? intval($_G['gp_newexpiration']) : 30) * 86400;
	$newmessage = nl2br(dhtmlspecialchars(trim($_G['gp_newmessage'])));
	if($newmessage != '') {
		DB::query("INSERT INTO ".DB::table('common_adminnote')." (admin, access, adminid, dateline, expiration, message)
			VALUES ('$_G[username]', '$newaccess', '$_G[adminid]', '$_G[timestamp]', '$newexpiration', '$newmessage')");
	}
}

if($op == 'delete' && submitcheck('notlistsubmit')) {
	if(is_array($_G['gp_delete']) && $deleteids = dimplode($_G['gp_delete'])) {
		DB::query("DELETE FROM ".DB::table('common_adminnote')." WHERE id IN($deleteids) AND ('$_G[adminid]'=1 OR admin='$_G[username]')");
	}
}

switch($_G['adminid']) {
	case 1: $access = '1,2,3,4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}

$notelist = array();
$query = DB::query("SELECT * FROM ".DB::table('common_adminnote')." WHERE access IN ($access) ORDER BY dateline DESC");
while($note = DB::fetch($query)) {
	if($note['expiration'] < TIMESTAMP) {
		DB::query("DELETE FROM ".DB::table('common_adminnote')." WHERE id='$note[id]'");
	} else {
		$note['expiration'] = ceil(($note['expiration'] - $note['dateline']) / 86400);
		$note['dateline'] = dgmdate($note['dateline']);
		$note['checkbox'] = '<input type="checkbox" name="delete[]" class="pc" '.($note['admin'] == $_G['member']['username'] || $_G['adminid'] == 1 ? "value=\"$note[id]\"" : 'disabled').'>';
		$note['admin'] = '<a href="home.php?mod=space&username='.rawurlencode($note['admin']).'" target="_blank">'.$note['admin'].'</a>';
		$notelist[] = $note;
	}
}

?>