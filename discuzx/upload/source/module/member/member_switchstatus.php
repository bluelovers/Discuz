<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_switchstatus.php 15296 2010-08-23 02:54:00Z zhaoxiongfei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if($_G['uid']) {

	if(!$_G['group']['allowinvisible']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	DB::query("UPDATE ".DB::table('common_session')." SET invisible = !invisible WHERE uid='$_G[uid]'", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table('common_member_status')." SET invisible = !invisible WHERE uid='$_G[uid]'", 'UNBUFFERED');
	$_G['session']['invisible'] = $_G['session']['invisible'] ? 0 : 1;
	$language = lang('forum/misc');
	$msg = $_G['session']['invisible'] ? $language['login_invisible_mode'] : $language['login_normal_mode'];
	showmessage('<a href="member.php?mod=switchstatus" title="'.$language['login_switch_invisible_mode'].'" ajaxtarget="loginstatus">'.$msg.'</a>', dreferer(), array(), array('msgtype' => 3, 'showmsg' => 1));

}

?>