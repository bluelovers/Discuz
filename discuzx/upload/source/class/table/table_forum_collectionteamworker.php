<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collectionteamworker.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collectionteamworker extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collectionteamworker';
		$this->_pk    = '';

		parent::__construct();
	}

	public function delete_by_ctid($ctid) {
		return DB::delete($this->_table, DB::field('ctid', $ctid));
	}

	public function delete_by_ctid_uid($ctid, $uid) {
		$condition = array();

		if($ctid) {
			$condition[] = DB::field('ctid', $ctid);
		}

		if($uid) {
			$condition[] = DB::field('uid', $uid);
		}

		DB::delete($this->_table, implode(' AND ', $condition));
	}

	public function delete_by_uid($uid) {
		return DB::query("DELETE FROM %t WHERE %i", array($this->_table, DB::field('uid', $uid)));
	}

	public function fetch_all_by_ctid($ctid) {
		return DB::fetch_all("SELECT * FROM %t WHERE ctid=%d", array($this->_table, $ctid), 'uid');
	}

	public function count_by_ctid($ctid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE ctid=%d", array($this->_table, $ctid));
	}

	public function fetch_all_by_uid($uid) {
		return DB::fetch_all("SELECT * FROM %t WHERE uid=%d", array($this->_table, $uid), 'ctid');
	}

	public function update_by_ctid($ctid, $title) {
		return DB::update($this->_table, array('name'=>$title), DB::field('ctid', $ctid));
	}

	public function update($ctid, $uid, $data, $unbuffered = false, $low_priority = false) {
		return DB::update($this->_table, $data, DB::field('ctid', $ctid).' AND '.DB::field('uid', $uid), $unbuffered, $low_priority);
	}
}

?>