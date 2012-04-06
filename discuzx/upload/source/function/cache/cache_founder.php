<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_founder.php 23525 2011-07-22 04:48:57Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_founder() {
	global $_G;

	$allowadmincp = $status0 = $status1 = array();
	$founders = explode(',', str_replace(' ', '', $_G['config']['admincp']['founder']));
	if($founders) {
		foreach($founders as $founder) {
			if(is_numeric($founder)) {
				$fuid[] = $founder;
			} else {
				$fuser[] = $founder;
			}
		}
		$query = DB::query('SELECT uid FROM '.DB::table('common_member').' WHERE '.($fuid ? 'uid IN ('.dimplode($fuid).')' : '0').' OR '.($fuser ? 'username IN ('.dimplode($fuser).')' : '0'));
		while($founder = DB::fetch($query)) {
			$allowadmincp[$founder['uid']] = $founder['uid'];
		}
	}
	$query = DB::query('SELECT uid FROM '.DB::table('common_admincp_member'));
	while($member = DB::fetch($query)) {
		$allowadmincp[$member['uid']] = $member['uid'];
	}

	$query = DB::query('SELECT uid, allowadmincp FROM '.DB::table('common_member')." WHERE allowadmincp > '0' OR uid IN (".dimplode($allowadmincp).')');
	while($user = DB::fetch($query)) {
		if(isset($allowadmincp[$user['uid']]) && !getstatus($user['allowadmincp'], 1)) {
			$status1[$user['uid']] = $user['uid'];
		} elseif(!isset($allowadmincp[$user['uid']]) && getstatus($user['allowadmincp'], 1)) {
			$status0[$user['uid']] = $user['uid'];
		}
	}
	if(!empty($status0)) {
		DB::query('UPDATE '.DB::table('common_member').' SET allowadmincp=allowadmincp & 0xFE WHERE uid IN ('.dimplode($status0).')');
	}
	if(!empty($status1)) {
		DB::query('UPDATE '.DB::table('common_member').' SET allowadmincp=allowadmincp | 1 WHERE uid IN ('.dimplode($status1).')');
	}

}

?>