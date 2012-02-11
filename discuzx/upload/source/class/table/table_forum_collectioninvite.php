<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collectioninvite.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collectioninvite extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collectioninvite';
		$this->_pk    = '';

		parent::__construct();
	}


	public function fetch_by_ctid_uid($ctid, $uid) {
		return DB::fetch_first('SELECT * FROM %t WHERE ctid=%d AND uid=%d', array($this->_table, $ctid, $uid));
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

	public function delete_by_ctid($ctid) {
		return DB::delete($this->_table, DB::field('ctid', $ctid));
	}

	public function delete_by_dateline($dateline) {
		return DB::delete($this->_table, DB::field('dateline', $dateline, '<='));
	}
}

?>