<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_portalcategory.php 22543 2011-05-12 03:47:53Z zhangguosheng $
 */

class portal_category {

	function portal_category() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new portal_category();
		}
		return $object;
	}

	function add_users_perm($catid, $users) {
		$sqlarr = $uids = array();
		$catid = intval($catid);
		if(!empty($catid) && !empty($users)) {
			$catids = $this->get_subcatids_by_catid($catid);
			$this->_add_users_cats($users, $catids);
			foreach($users as $v) {
				$uids[$v['uid']] = $v['uid'];
			}
			$this->_update_member_allowadmincp($uids);
		}
	}

	function _update_member_allowadmincp($uids) {
		if(!empty($uids)) {
			$userperms = array();
			$query = DB::query('SELECT uid, sum(allowpublish) as pb, sum(allowmanage) as mn FROM '.DB::table('portal_category_permission')." WHERE uid IN (".dimplode($uids).") GROUP BY uid");
			while($v = DB::fetch($query)) {
				$userperms[$v['uid']] = array('allowpublish'=>$v['pb'], 'allowmanage'=>$v['mn']);
			}
			$query = DB::query('SELECT uid,allowadmincp FROM '.DB::table('common_member')." WHERE uid IN (".dimplode($uids).")");
			while($v = DB::fetch($query)) {
				$v['allowadmincp'] = setstatus(3, empty($userperms[$v['uid']]['allowpublish']) ? 0 : 1, $v['allowadmincp']);
				$v['allowadmincp'] = setstatus(2, empty($userperms[$v['uid']]['allowmanage']) ? 0 : 1, $v['allowadmincp']);
				DB::update('common_member', array('allowadmincp'=>$v['allowadmincp']), "uid='$v[uid]'");
			}
		}
	}

	function delete_users_perm($catid, $uids) {

		$uids = !is_array($uids) ? array($uids) : $uids;
		$uids = array_map('intval', $uids);
		$uids = array_filter($uids);
		if($uids) {
			DB::delete('portal_category_permission', " catid='$catid' AND uid IN (".dimplode($uids).") AND inheritedcatid='0'");
			$this->delete_inherited_perm_by_catid($catid, $catid, $uids);
			$this->_update_member_allowadmincp($uids);
		}
	}

	function delete_inherited_perm_by_catid($catid, $upid = 0, $uid = 0) {
		if(!is_array($catid)) {
			$catids = $this->get_subcatids_by_catid($catid);
		}
		if($catids) {
			$uids = is_array($uid) ? $uid : array($uid);
			foreach($uids as $uid_) {
				$uid_ = intval($uid_);
				$where = empty($uid_) ? '' : " AND uid='$uid_'";
				$where .= $upid ? " AND inheritedcatid='$upid'" : "AND inheritedcatid>'0'";
				DB::delete('portal_category_permission', 'catid IN('.dimplode($catids).")$where");
				if($uid_) {
					$this->_update_member_allowadmincp(array($uid_));
				}
			}
		}
	}

	function remake_inherited_perm($catid) {
		loadcache('portalcategory');
		$portalcategory = getglobal('cache/portalcategory');
		$catid = intval($catid);
		$upid = !empty($portalcategory[$catid]) ? $portalcategory[$catid]['upid'] : 0;
		if($upid) {
			$catids = $this->get_subcatids_by_catid($catid);
			$users = $this->get_perms_by_catid($upid);
			$this->_add_users_cats($users, $catids, $upid);
		}
	}

	function get_perms_by_catid($catid, $uid = 0) {
		$perms = array();
		$catid = intval($catid);
		$uid = intval($uid);
		if($catid) {
			$where = $uid ? " AND uid='$uid'" : '';
			$query = DB::query("SELECT * FROM ".DB::table('portal_category_permission')." WHERE catid='$catid'$where");
			while($value = DB::fetch($query)) {
				$perms[] = $value;
			}
		}
		return $perms;
	}

	function get_catids_by_uid($uid, $start = 0, $limit = 30){
		$perms = array();
		$uid = intval($uid);
		$start = intval($start);
		$limit = intval($limit);
		if($catid) {
			$query = DB::query("SELECT * FROM ".DB::table('portal_category_permission')." WHERE uid='$uid' LIMIT $start, $limit");
			while($value = DB::fetch($query)) {
				$perms[] = $value;
			}
		}
		return $perms;
	}

	function _add_users_cats($users, $catids, $upid = 0) {
		$perms = array();
		if(!empty($users) && !empty($catids)){
			if(!is_array($catids)) {
				$catids = array($catids);
			}
			foreach($users as $user) {
				$inheritedcatid = !empty($user['inheritedcatid']) ? $user['inheritedcatid'] : ($upid ? $upid : 0);
				foreach ($catids as $catid) {
					$perms[] = "('$catid','$user[uid]','$user[allowpublish]','$user[allowmanage]','$inheritedcatid')";
					$inheritedcatid = empty($inheritedcatid) ? $catid : $inheritedcatid;
				}
			}
			if($perms) {
				DB::query('REPLACE INTO '.DB::table('portal_category_permission').' (catid,uid,allowpublish,allowmanage,inheritedcatid) VALUES '.implode(',', $perms));
			}
		}
	}

	function delete_perm_by_inheritedcatid($catid, $uids = array()) {
		if($uids && !is_array($uids)) $uids = array($uids);
		if($catid) {
			$where = !empty($uids) ? ' uid IN('.dimplode($uids).') AND' : '';
			DB::delete('portal_category_permission', "$where inheritedcatid='$catid'");
			if($uids) {
				$this->_update_member_allowadmincp($uids);
			}
		}
	}

	function delete_allperm_by_catid($catid) {
		if($catid) {
			$catid = is_array($catid) ? $catid : array($catid);
			DB::delete('portal_category_permission', ' catid IN('.dimplode($catid).")");
		}
	}
	function get_subcatids_by_catid($catid) {
		loadcache('portalcategory');
		$portalcategory = getglobal('cache/portalcategory');
		$catids = array();
		$catids[$catid] = $catid;
		if(isset($portalcategory[$catid]) && !empty($portalcategory[$catid]['children'])) {
			$children = array();
			foreach($portalcategory[$catid]['children'] as $cid) {
				if(!$portalcategory[$cid]['notinheritedarticle']) {
					$catids[$cid] = $cid;
					if(!empty($portalcategory[$cid]['children'])) {
						$children = array_merge($children, $portalcategory[$cid]['children']);
					}
				}
			}
			if(!empty($children)) {
				foreach($children as $cid) {
					if(!$portalcategory[$cid]['notinheritedarticle']) {
						$catids[$cid] = $cid;
					}
				}
			}
		}
		return $catids;
	}
}
?>