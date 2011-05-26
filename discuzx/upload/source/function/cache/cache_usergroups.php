<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_usergroups.php 19929 2011-01-24 09:41:05Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_usergroups() {
	global $_G;

	$data = array();
	$query = DB::query("SELECT u.groupid, u.type, u.grouptitle, u.creditshigher, u.creditslower, u.stars, u.color, u.icon, uf.readaccess, u.system, uf.allowgetattach, uf.allowgetimage, uf.allowmediacode FROM ".DB::table('common_usergroup')." u
		LEFT JOIN ".DB::table('common_usergroup_field')." uf ON u.groupid=uf.groupid ORDER BY u.creditslower");

	while($group = DB::fetch($query)) {
		if($group['type'] == 'special') {
			if($group['system'] != 'private') {
				list($dailyprice) = explode("\t", $group['system']);
				$group['pubtype'] = $dailyprice > 0 ? 'buy' : 'free';
			}
		}
		unset($group['system']);
		$groupid = $group['groupid'];
		$group['grouptitle'] = $group['color'] ? '<font color="'.$group['color'].'">'.$group['grouptitle'].'</font>' : $group['grouptitle'];
		if($_G['setting']['userstatusby'] == 1) {
			$group['userstatusby'] = 1;
		} elseif($_G['setting']['userstatusby'] == 2) {
			if($group['type'] != 'member') {
				$group['userstatusby'] = 1;
			} else {
				$group['userstatusby'] = 2;
			}
		}
		if($group['type'] != 'member') {
			unset($group['creditshigher'], $group['creditslower']);
		}
		unset($group['groupid']);
		$data[$groupid] = $group;
	}
	save_syscache('usergroups', $data);

	build_cache_usergroups_single();

	$query = DB::query("SELECT * FROM ".DB::table('common_admingroup'));
	while($data = DB::fetch($query)) {
		save_syscache('admingroup_'.$data['admingid'], $data);
	}
}

function build_cache_usergroups_single() {
	$pluginvalue = pluginsettingvalue('groups');
	$allowthreadplugin = unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='allowthreadplugin'"));

	$query = DB::query("SELECT * FROM ".DB::table('common_usergroup')." u
		LEFT JOIN ".DB::table('common_usergroup_field')." uf ON u.groupid=uf.groupid
		LEFT JOIN ".DB::table('common_admingroup')." a ON u.groupid=a.admingid");
	while($data = DB::fetch($query)) {
		$ratearray = array();
		if($data['raterange']) {
			foreach(explode("\n", $data['raterange']) as $rating) {
				$rating = explode("\t", $rating);
				$ratearray[$rating[0]] = array('isself' => $rating[1], 'min' => $rating[2], 'max' => $rating[3], 'mrpd' => $rating[4]);
			}
		}
		$data['raterange'] = $ratearray;
		$data['grouptitle'] = $data['color'] ? '<font color="'.$data['color'].'">'.$data['grouptitle'].'</font>' : $data['grouptitle'];
		$data['grouptype'] = $data['type'];
		$data['grouppublic'] = $data['system'] != 'private';
		$data['groupcreditshigher'] = $data['creditshigher'];
		$data['groupcreditslower'] = $data['creditslower'];
		$data['maxspacesize'] = intval($data['maxspacesize']) * 1024 * 1024;
		$data['allowthreadplugin'] = !empty($allowthreadplugin[$data['groupid']]) ? $allowthreadplugin[$data['groupid']] : array();
		$data['plugin'] = $pluginvalue[$data['groupid']];
		unset($data['type'], $data['system'], $data['creditshigher'], $data['creditslower'], $data['groupavatar'], $data['admingid']);
		save_syscache('usergroup_'.$data['groupid'], $data);
	}
}