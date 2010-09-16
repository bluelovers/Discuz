<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myrepeats.class.php 13767 2010-07-30 06:53:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_myrepeats {

	function plugin_myrepeats() {
		global $_G;
		if(!$_G['uid']) {
			return;
		}

		$myrepeatsusergroups = (array)unserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
		if(in_array('', $myrepeatsusergroups)) {
			$myrepeatsusergroups = array();
		}
		if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
			if(isset($_G['cookie']['mrn'])) {
				$count = $_G['cookie']['mrn'];
			} else {
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('myrepeats')." WHERE username='$_G[username]'");
				dsetcookie('mrn', $count, 3600);
			}
			if(!$count) {
				return;
			}
		}

		if(isset($_G['cookie']['mrd'])) {
			$userlist = explode("\t", $_G['cookie']['mrd']);
		} else {
			$userlist = array();
			$query = DB::query("SELECT username FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]'");
			while($user = DB::fetch($query)) {
				$userlist[] = $user['username'];
			}
			$cookievalue = implode("\t", $userlist);
			dsetcookie('mrd', $cookievalue ? $cookievalue : "\t", 3600);
		}
		$this->global_usernav_extra1 = '<span class="pipe">|</span><span id="myrepeats" class="showmenu" onmouseover="showMenu(this.id)">'.lang('plugin/myrepeats', 'switch').'</span>';
		$list = '<ul id="myrepeats_menu" class="p_pop" style="display:none;">';
		foreach($userlist as $user) {
			if(!$user) {
				continue;
			}
			$user = stripslashes($user);
			$list .= '<li><a href="plugin.php?id=myrepeats:switch&username='.rawurlencode($user).'&formhash='.FORMHASH.'">'.$user.'</a></li>';
		}
		$list .= '<li style="clear:both"><a href="home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp">'.lang('plugin/myrepeats', 'memcp').'</a></li></ul>';
		$this->global_footer = $list;
		return $list;
	}

	function global_usernav_extra1() {
		return $this->global_usernav_extra1;
	}

	function global_footer() {
		return $this->global_footer;
	}

}

class plugin_myrepeats_member extends plugin_myrepeats {

	function logging_myrepeats_output() {
		dsetcookie('mrn', '');
		dsetcookie('mrd', '');
	}

}