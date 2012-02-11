<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_district.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_district extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_district';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_all_by_upid($upid, $order = null, $sort = 'DESC') {
		$ordersql = $order !== null ? " ORDER BY $order $sort" : '';
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('upid', $upid)." $ordersql", array($this->_table), $this->_pk);
	}

	public function fetch_all_by_name($name) {
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('name', $name), array($this->_table));
	}

}

?>