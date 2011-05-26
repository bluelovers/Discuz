<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_profile.php 18074 2010-11-11 07:15:30Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_profile {

	var $version = '1.0';
	var $name = 'profile_name';
	var $description = 'profile_desc';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array();

	function csc($task = array()) {
		global $_G;

		$data = $this->checkfield();
		if(!$data[0]) {
			return true;
		}
		return array('csc' => $data[1], 'remaintime' => 0);
	}

	function view() {
		$data = $this->checkfield();
		return lang('task/profile', 'profile_view', array('profiles' => implode(', ', $data[0])));
	}

	function checkfield() {
		global $_G;

		$fields = lang('task/profile', 'profile_fields');
		loadcache('profilesetting');
		$fieldsql = array();
		foreach($fields as $k => $v) {
			$nk = explode('.', $k);
			if(isset($_G['cache']['profilesetting'][$nk[1]])) {
				$fieldsnew[$nk[1]] = $v;
				$fieldsql[] = $k;
			}
		}
		if($fieldsql) {
			$result = DB::fetch_first("SELECT ".implode(',', $fieldsql)." FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_profile')." mp ON m.uid=mp.uid WHERE m.uid='$_G[uid]'");
			$none = array();
			foreach($result as $k => $v) {
				if(!trim($v)) {
					$none[] = $fieldsnew[$k];
				}
			}
			$csc = intval((count($fields) - count($none)) / count($fields) * 100);
			return array($none, $csc);
		} else {
			return true;
		}
	}

}

?>