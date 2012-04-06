<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_avatar.php 18515 2010-11-25 07:35:31Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(submitcheck('avatarsubmit')) {
	showmessage('do_success', 'cp.php?ac=avatar&quickforward=1');
}

loaducenter();
$uc_avatarflash = uc_avatar($_G['uid'], 'virtual', 0);

if(empty($space['avatarstatus']) && uc_check_avatar($_G['uid'], 'middle')) {
	DB::update('common_member', array('avatarstatus'=>'1'), array('uid'=>$_G['uid']));

	updatecreditbyaction('setavatar');

	manyoulog('user', $_G['uid'], 'update');
}
$actives = array('avatar' =>' class="a"');
include template("home/spacecp_avatar");

?>