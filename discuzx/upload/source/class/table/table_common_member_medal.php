<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_medal.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_medal extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_medal';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_uid($uid) {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('uid', $uid), 'medalid');
	}

	public function delete_by_uid_medalid($uid, $medalid) {
		DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('medalid', $medalid));
	}

	public function count_by_uid_medalid($uid, $medalid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND medalid=%d', array($this->_table, $uid, $medalid));
	}
}

?>