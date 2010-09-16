<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_showip.php 13786 2010-07-30 07:47:50Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_showip {

	var $version = '1.0';
	var $name = 'showip_name';
	var $description = 'showip_desc';
	var $price = '10';
	var $weight = '10';
	var $useevent = 1;
	var $targetgroupperm = true;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
	}

	function setsetting(&$magicnew, &$parameters) {
	}

	function usesubmit() {
		global $_G;
		if(empty($_G['gp_username'])) {
			showmessage(lang('magic/showip', 'showip_info_nonexistence'));
		}

		$member = getuserinfo($_G['gp_username'], array('uid', 'groupid'));
		$this->_check($member['groupid']);

		$ip = DB::result_first("SELECT lastip FROM ".DB::table('common_member_status')." WHERE uid='$member[uid]'");

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'uid', $member['uid']);

		if($member['uid'] != $_G['uid']) {
			notification_add($member['uid'], 'magic', lang('magic/showip', 'showip_notification'), array('magicname' => $this->magic['name']), 1);
		}

		showmessage(lang('magic/showip', 'showip_ip_message'), '', array('username' => stripslashes($_G['gp_username']), 'ip' => $ip), array('showdialog' => 1));
	}

	function show() {
		global $_G;
		$user = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		if($user) {
			$member = getuserinfo($user, array('groupid'));
			$this->_check($member['groupid']);
		}
		magicshowtype('top');
		magicshowsetting(lang('magic/showip', 'showip_targetuser'), 'username', $user, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_G['gp_id'])) {
			$member = getuserinfo($_G['gp_id'], array('groupid', 'username'));
			if($_G['group']['allowviewip']) {
				$_G['gp_username'] = $member['username'];
				$this->usesubmit();
			} else {
				$this->_check($member['groupid']);
			}
		}
	}

	function _check($groupid) {
		if(!checkmagicperm($this->parameters['targetgroups'], $groupid)) {
			showmessage(lang('magic/showip', 'showip_info_noperm'));
		}
	}

}

?>