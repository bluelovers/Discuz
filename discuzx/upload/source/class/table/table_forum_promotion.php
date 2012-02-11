<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_promotion.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_promotion extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_promotion';
		$this->_pk    = 'ip';

		parent::__construct();
	}

	public function count_by_uid($uid) {
		if(!empty($uid)) {
			$parameter = array($this->_table, $uid);
			$where = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
			return DB::result_first("SELECT COUNT(*) FROM %t WHERE $where", $parameter);
		} else {
			return 0;
		}
	}
	public function delete_all() {
		return DB::query("DELETE FROM %t", array($this->_table));
	}

}

?>