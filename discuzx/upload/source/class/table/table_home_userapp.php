<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_userapp.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_userapp extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_userapp';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_by_uid_appid($uid, $appid) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND appid=%d', array($this->_table, $uid, $appid));
	}

	public function fetch_max_menuorder_by_uid($uid) {
		return DB::result_first('SELECT MAX(menuorder) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

	public function fetch_all_by_uid_appid($uid = 0, $appid = 0, $order = null, $sort = 'DESC' , $start = 0, $limit = 0) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($uid) {
			$parameter[] = $uid;
			$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
		}
		if($appid) {
			$parameter[] = $appid;
			$wherearr[] = is_array($appid) ? 'appid IN(%n)' : 'appid=%d';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		$sort = in_array($sort, array('DESC', 'ASC')) ? $sort : 'DESC';
		$ordersql = $order !== null ? "ORDER BY $order $sort " : '';
		if(!$uid) {
			$limit = $limit ? $limit : 100;
		}
		return DB::fetch_all("SELECT * FROM %t $wheresql $ordersql ".DB::limit($start, $limit), $parameter);
	}

	public function update_by_uid_appid($uid = 0, $appid = 0, $data = array()) {
		$wherearr = array();
		if($uid) {
			$wherearr[] = DB::field('uid', $uid);
		}
		if($appid) {
			$wherearr[] = DB::field('appid', $appid);
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? implode(' AND ', $wherearr) : '';
		return DB::update($this->_table, $data, $wheresql);
	}

	public function delete_by_uid_appid($uid = 0, $appid = 0) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($uid) {
			$parameter[] = $uid;
			$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
		}
		if($appid) {
			$parameter[] = $appid;
			$wherearr[] = is_array($appid) ? 'appid IN(%n)' : 'appid=%d';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

		return DB::query("DELETE FROM %t $wheresql", $parameter);
	}
	public function delete_by_uid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids));
	}

}

?>