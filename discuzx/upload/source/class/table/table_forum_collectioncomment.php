<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collectioncomment.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collectioncomment extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collectioncomment';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function delete_by_ctid($ctid) {
		return DB::delete($this->_table, DB::field('ctid', $ctid));
	}

	public function delete_by_cid_ctid($cids, $ctid = 0) {
		if($ctid != 0) {
			$ctidsql = ' AND ctid=\''.dintval($ctid).'\'';
		}
		return DB::query("DELETE FROM %t WHERE cid IN (%n) $ctidsql", array($this->_table, $cids));
	}

	public function delete_by_uid($uid) {
		return DB::query("DELETE FROM %t WHERE %i", array($this->_table, DB::field('uid', $uid)));
	}

	public function fetch_all_by_ctid($ctid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE ctid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $ctid), $this->_pk);
	}

	public function fetch_all_by_uid($uid) {
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('uid', $uid)), $this->_pk);
	}

	public function fetch_rate_by_ctid_uid($ctid, $uid) {
		return DB::result_first('SELECT rate FROM %t WHERE ctid=%d AND uid=%d AND rate!=0', array($this->_table, $ctid, $uid), $this->_pk);
	}

	public function fetch_all_for_search($conditions, $start = 0, $limit = 20) {
		if(empty($conditions)) {
			return array();
		}
		if($start == -1) {
			return DB::result_first("SELECT count(*) FROM %t WHERE %i", array($this->_table, $conditions));
		}
		return DB::fetch_all("SELECT * FROM %t 	WHERE %i ORDER BY dateline DESC %i", array($this->_table, $conditions, DB::limit($start, $limit)));
	}
}

?>