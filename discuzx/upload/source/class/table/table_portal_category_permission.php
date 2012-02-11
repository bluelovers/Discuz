<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_category_permission.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_category_permission extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_category_permission';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch($catid, $uid){
		return DB::fetch_first('SELECT * FROM %t WHERE catid=%d AND uid=%d', array($this->_table, $catid, $uid));
	}

	public function fetch_all_by_catid($catid, $uid = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE catid=%d'.($uid ? DB::field('uid', $uid) : ''), array($this->_table, $catid), 'uid');
	}

	public function fetch_all_by_uid($uids, $flag = true, $sort = 'ASC', $start = 0, $limit = 0) {
		$wherearr = array();
		$sort = $sort === 'ASC' ? 'ASC' : 'DESC';
		if($uids) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if(!$flag) {
			$wherearr[] = 'inheritedcatid = \'\'';
		}
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$where.' ORDER BY uid '.$sort.', inheritedcatid'.DB::limit($start, $limit), NULL, 'catid');
	}

	public function count_by_uids($uids, $flag) {
		$wherearr = array();
		if($uids) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if(!$flag) {
			$wherearr[] = 'inheritedcatid = \'\'';
		}
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).$where);
	}

	public function fetch_permission_by_uid($uids) {
		return DB::fetch_all('SELECT uid, sum(allowpublish) as allowpublish, sum(allowmanage) as allowmanage FROM '.DB::table($this->_table)." WHERE uid IN (".dimplode($uids).") GROUP BY uid", null, 'uid');
	}

	public function delete_by_catid_uid_inheritedcatid($catid = false, $uids = false, $inheritedcatid = false) {
		$wherearr = array();
		if($catid) {
			$wherearr[] = DB::field('catid', $catid);
		}
		if($uids) {
			$wherearr[] = DB::field('uid', $uids);
		}
		if($inheritedcatid === true) {
			$wherearr[] = "inheritedcatid>'0'";
		} elseif($inheritedcatid !== false) {
			$wherearr[] = DB::field('inheritedcatid', $inheritedcatid);
		}
		return $wherearr ? DB::delete($this->_table, implode(' AND ', $wherearr)) : false;
	}

	public function insert_batch($users, $catids, $upid = 0) {
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
				DB::query('REPLACE INTO '.DB::table($this->_table).' (catid,uid,allowpublish,allowmanage,inheritedcatid) VALUES '.implode(',', $perms));
			}
		}
	}
}

?>