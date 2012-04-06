<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_checkonline.php 7955 2010-04-15 05:18:34Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_checkonline {

	var $version = '1.0';
	var $name = 'checkonline_name';
	var $description = 'checkonline_desc';
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
			showmessage(lang('magic/checkonline', 'checkonline_info_nonexistence'));
		}

		$member = getuserinfo($_G['gp_username'], array('uid', 'groupid'));
		$this->_check($member['groupid']);

		$online = DB::fetch_first("SELECT action, lastactivity, invisible FROM ".DB::table('common_session')." WHERE uid='$member[uid]'");

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'uid', $member['uid']);

		if($member['uid'] != $_G['uid']) {
			notification_add($member['uid'], 'magic', lang('magic/checkonline', 'checkonline_notification'), array('magicname' => $this->magic['name']), 1);
		}

		if($online) {
			$time = dgmdate($online['lastactivity'], 'u');
			if($online['invisible']) {
				showmessage(lang('magic/checkonline', 'checkonline_hidden_message'), '', array('username' => stripslashes($_G['gp_username']), 'time' => $time), array('showdialog' => 1));
			} else {
				showmessage(lang('magic/checkonline', 'checkonline_online_message'), '', array('username' => stripslashes($_G['gp_username']), 'time' => $time), array('showdialog' => 1));
			}
		} else {
			showmessage(lang('magic/checkonline', 'checkonline_offline_message'), '', array('username' => stripslashes($_G['gp_username'])), array('showdialog' => 1));
		}
	}

	function show() {
		global $_G;
		$user = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		if($user) {
			$member = getuserinfo($user, array('groupid'));
			$this->_check($member['groupid']);
		}
		magicshowtype('top');
		magicshowsetting(lang('magic/checkonline', 'checkonline_targetuser'), 'username', $user, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_G['gp_id'])) {
			$member = getuserinfo($_G['gp_id'], array('groupid'));
			$this->_check($member['groupid']);
		}
	}

	function _check($groupid) {
		if(!checkmagicperm($this->parameters['targetgroups'], $groupid)) {
			showmessage(lang('magic/checkonline', 'checkonline_info_noperm'));
		}
	}

}

?>