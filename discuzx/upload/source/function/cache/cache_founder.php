<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_founder.php 20041 2011-01-30 05:01:46Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_founder() {
	//BUG:全新安裝後 必須要更新緩存才能顯示管理中心的連結
	global $_G;

	$allowadmincp = array();
	$founders = explode(',', str_replace(' ', '', $_G['config']['admincp']['founder']));
	if($founders) {
		foreach($founders as $founder) {
			if(is_numeric($founder)) {
				$fuid[] = $founder;
			} else {
				$fuser[] = $founder;
			}
		}
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE ".($fuid ? "uid IN (".dimplode($fuid).")" : '0')." OR ".($fuser ? "username IN (".dimplode($fuser).")" : '0'));
		while($founder = DB::fetch($query)) {
			$allowadmincp[$founder['uid']] = $founder['uid'];
		}
	}
	$query = DB::query("SELECT uid FROM ".DB::table('common_admincp_member'));
	while($member = DB::fetch($query)) {
		$allowadmincp[$member['uid']] = $member['uid'];
	}
	DB::update('common_member', array('allowadmincp' => 0), "allowadmincp='1'");
	DB::update('common_member', array('allowadmincp' => 1), 'uid IN ('.dimplode($allowadmincp).')');
}

?>