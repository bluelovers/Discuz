<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myrepeats.class.php 21730 2011-04-11 06:23:46Z lifangming $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_myrepeats {

	var $value = array();

	function plugin_myrepeats() {
		global $_G;
		if(!$_G['uid']) {
			return;
		}

		$myrepeatsusergroups = (array)unserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
		if(in_array('', $myrepeatsusergroups)) {
			$myrepeatsusergroups = array();
		}
		$userlist = array();
		if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
			if(isset($_G['cookie']['mrn'])) {
				$count = $_G['cookie']['mrn'];
			} else {
				$userlist = $this->_get_rrepeats($_G['username']);
				$count = count($userlist);
				$cookievalue = implode("\t", $userlist);
				dsetcookie('mrd', $cookievalue ? $cookievalue : "\t", 3600);
				dsetcookie('mrn', $count, 3600);
			}
			if(!$count) {
				unset($_G['setting']['plugins']['spacecp']['myrepeats:memcp']);
				return;
			}
		}

		if(isset($_G['cookie']['mrd'])) {
			$userlist = explode("\t", $_G['cookie']['mrd']);
		} elseif(!$userlist) {
			$userlist = $this->_get_rrepeats($_G['username']);
			$query = DB::query("SELECT username FROM ".DB::table('myrepeats')." WHERE uid='$_G[uid]'");
			while($user = DB::fetch($query)) {
				$userlist[$user['username']] = $user['username'];
			}
			$cookievalue = implode("\t", $userlist);
			dsetcookie('mrd', $cookievalue ? $cookievalue : "\t", 3600);
		}

		$this->value['global_usernav_extra1'] = '<span class="pipe">|</span><a id="myrepeats" class="showmenu cur1" onmouseover="delayShow(this)">'.lang('plugin/myrepeats', 'switch').'</a>'."\n";

		$list = '<ul id="myrepeats_menu" class="p_pop" style="display:none;">';
		foreach($userlist as $user) {
			if(!$user) {
				continue;
			}
			$user = stripslashes($user);
			$list .= '<li><a onclick="showWindow(\'myrepeat\', this.href);return false;" href="plugin.php?id=myrepeats:switch&username='.rawurlencode($user).'&formhash='.FORMHASH.'">'.$user.'</a></li>';
		}
		$list .= '<li style="clear:both"><a href="home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp">'.lang('plugin/myrepeats', 'memcp').'</a></li></ul>';
		$this->value['global_footer'] = $list;
		return $list;
	}

	function global_usernav_extra1() {
		return $this->value['global_usernav_extra1'];
	}

	function global_footer() {
		return $this->value['global_footer'];
	}

	function _get_rrepeats($username) {
		$query = DB::query("SELECT my.uid, m.username FROM ".DB::table('myrepeats')." my
			LEFT JOIN ".DB::table('common_member')." m USING(uid) WHERE my.username='$username'");
		$userlist = array();
		while($user = DB::fetch($query)) {
			$userlist[$user['username']] = $user['username'];
		}
		return $userlist;
	}

}

class plugin_myrepeats_member extends plugin_myrepeats {

	function logging_myrepeats_output() {
		dsetcookie('mrn', '');
		dsetcookie('mrd', '');
	}

}

?>