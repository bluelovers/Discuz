<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_friend_request.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_friend_request extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_friend_request';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_by_uid($uid) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d LIMIT 0,1', array($this->_table, $uid));
	}
	public function fetch_by_uid_fuid($uid, $fuid) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND fuid=%d', array($this->_table, $uid, $fuid));
	}
	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $uid));
	}
	public function delete_by_uid_or_fuid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids).' OR '.DB::field('fuid', $uids));
	}
	public function delete_by_uid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids));
	}
	public function delete_by_uid_fuid($uid, $fuid) {
		return DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('fuid', $fuid));
	}
	public function count_by_uid_fuid($uid, $fuid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND fuid=%d', array($this->_table, $uid, $fuid));
	}
	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

}

?>