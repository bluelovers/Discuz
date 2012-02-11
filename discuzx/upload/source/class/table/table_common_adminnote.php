<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_adminnote.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_adminnote extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_adminnote';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete($id, $admin = '') {
		return DB::query('DELETE FROM %t WHERE '.DB::field('id', $id).' %i', array($this->_table, ($admin ? ' AND '.DB::field('admin', $admin) : '')));
	}

	public function fetch_all_by_access($access) {
		 return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('access', $access).' ORDER BY dateline DESC', array($this->_table));
	}

	public function count_by_access($access) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.DB::field('access', $access), array($this->_table));
	}

}

?>