<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_blockpermission.php 23372 2011-07-12 01:50:34Z zhangguosheng $
 */

class block_permission {

	function block_permission() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new block_permission();
		}
		return $object;
	}

	function add_users_perm($bid, $users) {
		$sqlarr = $uids = array();
		$bid = intval($bid);
		if(!empty($bid) && !empty($users)) {
			foreach ($users as $v) {
				$sqlarr[] = "('$bid','$v[uid]','$v[allowmanage]','$v[allowrecommend]','$v[needverify]','')";
				$uids[] = $v['uid'];
			}
			if(!empty($sqlarr)) {
				DB::query('REPLACE INTO '.DB::table('common_block_permission').' (bid,uid,allowmanage,allowrecommend,needverify,inheritedtplname) VALUES '.implode(',', $sqlarr));
			}
			$this->_update_member_allowadmincp($uids);
		}
	}

	function _update_member_allowadmincp($uids) {
		if(!empty($uids)) {
			$userperms = array();
			$query = DB::query('SELECT uid, sum(allowmanage) as mn, sum(allowrecommend) as rc, sum(needverify) as nv FROM '.DB::table('common_block_permission')." WHERE uid IN (".dimplode($uids).") GROUP BY uid");
			while($v = DB::fetch($query)) {
				$userperms[$v['uid']] = array('allowmanage'=>$v['mn'], 'allowrecommend'=>$v['rc'], 'needverify'=>$v['nv']);
			}
			$query = DB::query('SELECT uid,allowadmincp FROM '.DB::table('common_member')." WHERE uid IN (".dimplode($uids).")");
			while($v = DB::fetch($query)) {
				$v['allowadmincp'] = setstatus(4, empty($userperms[$v['uid']]['allowmanage']) ? 0 : 1, $v['allowadmincp']);
				if($userperms[$v['uid']]['allowrecommend'] > 0 ) {
					if($userperms[$v['uid']]['allowrecommend'] == $userperms[$v['uid']]['needverify']) {
						$v['allowadmincp'] = setstatus(5, 1, $v['allowadmincp']);
						$v['allowadmincp'] = setstatus(6, 0, $v['allowadmincp']);
					} else {
						$v['allowadmincp'] = setstatus(5, 0, $v['allowadmincp']);
						$v['allowadmincp'] = setstatus(6, 1, $v['allowadmincp']);
					}
				} else {
					$v['allowadmincp'] = setstatus(5, 0, $v['allowadmincp']);
					$v['allowadmincp'] = setstatus(6, 0, $v['allowadmincp']);
				}
				DB::update('common_member', array('allowadmincp'=>$v['allowadmincp']), "uid='$v[uid]'");
			}
		}
	}

	function delete_users_perm($bid, $users) {
		$bid = intval($bid);
		if($bid && $users) {
			$where = "bid='$bid' AND uid IN (".dimplode($users).") AND inheritedtplname=''";
			DB::delete('common_block_permission', $where);
			DB::delete('common_block_favorite', "uid IN(".dimplode($users).") AND bid='$bid'");
			$this->_update_member_allowadmincp($users);
		}
	}

	function delete_inherited_perm_by_bid($bids, $inheritedtplname = '', $uid = 0) {
		if(!is_array($bids)) $bids = array($bids);
		if($bids) {
			$uid = intval($uid);
			$where = empty($uid) ? '' : " AND uid='$uid'";
			$where .= empty($inheritedtplname) ? " AND inheritedtplname<>''" : " AND inheritedtplname='$inheritedtplname'";
			DB::delete('common_block_permission', 'bid IN('.dimplode($bids).")$where");
			if($uid) {
				DB::delete('common_block_favorite', "uid='$uid' AND bid IN(".dimplode($bids).")");
				$this->_update_member_allowadmincp(array($uid));
			}
		}
	}

	function remake_inherited_perm($bid) {
		$bid = intval($bid);
		if($bid) {
			$targettplname = DB::result_first('SELECT targettplname FROM '.DB::table('common_template_block')." WHERE bid='$bid'");
			if($targettplname) {
				$tplpermsission = & template_permission::instance();
				$userperm = $tplpermsission->get_users_perm_by_template($targettplname);
				$this->add_users_blocks($userperm, $bid, $targettplname);
			}
		}
	}

	function get_perms_by_bid($bid, $uid = 0) {
		$perms = array();
		$bid = intval($bid);
		$uid = intval($uid);
		if($bid) {
			$where = $uid ? " AND uid='$uid'" : '';
			$query = DB::query("SELECT * FROM ".DB::table('common_block_permission')." WHERE bid='$bid'$where");
			while($value = DB::fetch($query)) {
				$perms[] = $value;
			}
		}
		return $perms;
	}

	function get_bids_by_uid($uid, $start = 0, $limit = 30){
		$perms = array();
		$uid = intval($uid);
		$start = intval($start);
		$limit = intval($limit);
		if($bid) {
			$query = DB::query("SELECT * FROM ".DB::table('common_block_permission')." WHERE uid='$uid' LIMIT $start, $limit");
			while($value = DB::fetch($query)) {
				$perms[] = $value;
			}
		}
		return $perms;
	}

	function add_users_blocks($users, $bids, $tplname = '') {
		$blockperms = array();
		if(!empty($users) && !empty($bids)){
			if(!is_array($bids)) {
				$bids = array($bids);
			}
			$bidsstr = dimplode($bids);

			$uids = $notinherit = array();
			foreach($users as $user) {
				$uids[] = $user['uid'];
			}
			if(!empty($uids)) {
				$query = DB::query('SELECT bid,uid FROM '.DB::table('common_block_permission')." WHERE uid IN (".dimplode($uids).") AND inheritedtplname=''");
				while($value = DB::fetch($query)) {
					if(in_array($value['bid'], $bids)) {
						$notinherit[$value['bid']][$value['uid']] = true;
					}
				}
			}
			foreach($users as $user) {
				$tplname = !empty($user['inheritedtplname']) ? $user['inheritedtplname'] : $tplname;
				foreach ($bids as $bid) {
					if(empty($notinherit[$bid][$user['uid']])) {
						$blockperms[] = "('$bid','$user[uid]','$user[allowmanage]','$user[allowrecommend]','$user[needverify]','$tplname')";
					}
				}
			}
			if($blockperms) {
				DB::query('REPLACE INTO '.DB::table('common_block_permission').' (bid,uid,allowmanage,allowrecommend,needverify,inheritedtplname) VALUES '.implode(',', $blockperms));
				$this->_update_member_allowadmincp($uids);
			}
		}
	}

	function delete_perm_by_inheritedtpl($tplname, $uids) {
		if(!empty($uids) && !is_array($uids)) $uids = array($uids);
		if($tplname) {
			$where = empty($uids) ? '' : ' uid IN('.dimplode($uids).') AND';
			DB::delete('common_block_permission', "$where inheritedtplname='$tplname'");
			if($uids) {
				$this->_update_member_allowadmincp($uids);
			}
		}
	}

	function delete_perm_by_template($templates) {
		if($templates) {
			DB::delete('common_block_permission', ' inheritedtplname IN('.dimplode($templates).')');
		}
	}
	function get_bids_by_template($tplname) {
		global $_G;
		$bids = array();
		if(!is_array($tplname)) $tplname = array($tplname);
		if(!empty($tplname)) {
			$query = DB::query('SELECT tb.bid FROM '.DB::table('common_template_block').' tb LEFT JOIN '.DB::table('common_block').' b ON b.bid=tb.bid WHERE tb.targettplname IN ('.dimplode($tplname).") AND b.notinherited='0'");
			while($value = DB::fetch($query)) {
				$bids[$value['bid']] = $value['bid'];
			}
		}
		return $bids;
	}
}

class template_permission {
	function template_permission() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new template_permission();
		}
		return $object;
	}

	function add_users($tplname, $users) {
		$templates = $this->_get_templates_subs($tplname);
		$this->_add_users_templates($users, $templates);

		$blockpermission = & block_permission::instance();
		$bids = $blockpermission->get_bids_by_template($templates);
		$blockpermission->add_users_blocks($users, $bids, $tplname);
	}

	function delete_users($tplname, $uids) {
		$uids = !is_array($uids) ? array($uids) : $uids;
		$uids = array_map('intval', $uids);
		$uids = array_filter($uids);
		if($uids) {
			DB::delete('common_template_permission', " targettplname='$tplname' AND uid IN (".dimplode($uids).") AND inheritedtplname=''");
		}
		$this->delete_perm_by_inheritedtpl($tplname, $uids);
	}

	function add_blocks($tplname, $bids){
		$users = $this->get_users_perm_by_template($tplname);
		if($users) {
			$blockpermission = & block_permission::instance();
			$blockpermission->add_users_blocks($users, $bids, $tplname);
		}
	}

	function get_users_perm_by_template($tplname){
		$perm = array();
		if($tplname) {
			$query = DB::query('SELECT * FROM '.DB::table('common_template_permission')." WHERE targettplname='$tplname'");
			while($value = DB::fetch($query)) {
				$perm[$value['uid']] = $value;
			}
		}
		return $perm;
	}

	function _add_users_templates($users, $templates, $uptplname = '') {
		$blockperms = array();
		if(!empty($users) && !empty($templates)){
			if(!is_array($templates)) {
				$templates = array($templates);
			}
			foreach($users as $user) {
				$inheritedtplname = $uptplname ? $uptplname : '';
				foreach ($templates as $tpl) {
					$blockperms[] = "('$tpl','$user[uid]','$user[allowmanage]','$user[allowrecommend]','$user[needverify]','$inheritedtplname')";
					$inheritedtplname = empty($inheritedtplname) ? $tpl : $inheritedtplname;
				}
			}
			if($blockperms) {
				DB::query('REPLACE INTO '.DB::table('common_template_permission').' (targettplname,uid,allowmanage,allowrecommend,needverify,inheritedtplname) VALUES '.implode(',', $blockperms));
			}
		}
	}

	function delete_allperm_by_tplname($tplname){
		if($tplname) {
			$tplname = is_array($tplname) ? $tplname : array($tplname);
			$blockpermission = & block_permission::instance();
			$blockpermission->delete_perm_by_template($tplname);
			$tplnames = dimplode($tplname);
			DB::delete('common_template_permission', ' targettplname IN('.$tplnames.') OR inheritedtplname IN('.$tplnames.")");
		}
	}
	function delete_inherited_perm_by_tplname($templates, $inheritedtplname = '', $uid = 0) {
		if($templates && !is_array($templates)) {
			$templates = $this->_get_templates_subs($templates);
		}
		if($templates) {
			$uid = intval($uid);
			$where = empty($uid) ? '' : " AND uid='$uid'";
			$where .= $inheritedtplname ? " AND inheritedtplname='$inheritedtplname'" : "AND inheritedtplname!=''";
			DB::delete('common_template_permission', ' targettplname IN('.dimplode($templates).")$where");
			$blockpermission = & block_permission::instance();
			$blocks = $blockpermission->get_bids_by_template($templates);
			$blockpermission->delete_inherited_perm_by_bid($blocks, $inheritedtplname, $uid);
		}
	}

	function delete_perm_by_inheritedtpl($tplname, $uids = array()) {
		if($uids && !is_array($uids)) $uids = array($uids);
		if($tplname) {
			$where = !empty($uids) ? ' uid IN('.dimplode($uids).') AND' : '';
			DB::delete('common_template_permission', "$where inheritedtplname='$tplname'");
			$blockpermission = & block_permission::instance();
			$blockpermission->delete_perm_by_inheritedtpl($tplname, $uids);
		}
	}

	function remake_inherited_perm($tplname, $parenttplname) {
		if($tplname && $parenttplname) {
			$users = $this->get_users_perm_by_template($parenttplname);
			$templates = $this->_get_templates_subs($tplname);
			$this->_add_users_templates($users, $templates, $parenttplname);

			$blockpermission = & block_permission::instance();
			$bids = $blockpermission->get_bids_by_template($templates);
			$blockpermission->add_users_blocks($users, $bids, $parenttplname);
		}
	}

	function _get_templates_subs($tplname){
		global $_G;
		$tplpre = 'portal/list_';
		$cattpls = array($tplname);
		if(substr($tplname, 0, 12) == $tplpre){
			loadcache('portalcategory');
			$portalcategory = $_G['cache']['portalcategory'];
			$catid = intval(str_replace($tplpre, '', $tplname));
			if(isset($portalcategory[$catid]) && !empty($portalcategory[$catid]['children'])) {
				$children = array();
				foreach($portalcategory[$catid]['children'] as $cid) {
					if(!$portalcategory[$cid]['notinheritedblock']) {
						$cattpls[] = $tplpre.$cid;
						if(!empty($portalcategory[$cid]['children'])) {
							$children = array_merge($children, $portalcategory[$cid]['children']);
						}
					}
				}
				if(!empty($children)) {
					foreach($children as $cid) {
						if(!$portalcategory[$cid]['notinheritedblock']) {
							$cattpls[] = $tplpre.$cid;
						}
					}
				}
			}
		}
		return $cattpls;
	}

	function _get_templates_ups($tplname){
		global $_G;
		$tplpre = 'portal/list_';
		$cattpls = array($tplname);
		if(substr($tplname, 0, 12) == $tplpre){
			loadcache('portalcategory');
			$portalcategory = $_G['cache']['portalcategory'];
			$catid = intval(str_replace($tplpre, '', $tplname));
			if(isset($portalcategory[$catid]) && !$portalcategory[$catid]['notinheritedblock']) {
				$upid = $portalcategory[$catid]['upid'];
				while(!empty($upid)) {
					$cattpls[] = $tplpre.$upid;
					$upid = !$portalcategory[$upid]['notinheritedblock'] ? $portalcategory[$upid]['upid'] : 0;
				}
			}
		}
		return $cattpls;
	}

}

?>