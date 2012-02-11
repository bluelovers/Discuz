<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_word.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_word extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_word';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_by_find($find) {
		return DB::fetch_first("SELECT * FROM %t WHERE find=%s", array($this->_table, $find));
	}
	public function fetch_all_order_type_find() {
		return DB::fetch_all('SELECT * FROM %t ORDER BY type ASC, find ASC', array($this->_table), $this->_pk);
	}

	public function fetch_all() {
		return DB::fetch_all('SELECT * FROM %t', array($this->_table), $this->_pk);
	}

	public function fetch_all_by_type_find($type = null, $find = null , $start = 0, $limit = 0) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($type !== null) {
			$parameter[] = $type;
			$wherearr[] = "`type`=%d";
		}
		if($find !== null) {
			$parameter[] = '%'.addslashes(stripsearchkey($find)).'%';
			$wherearr[] = "`find` LIKE %s";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY find ASC".DB::limit($start, $limit), $parameter);
	}


	public function update_by_type($types, $data) {
		if(!empty($types)) {
			$types = array_map('dintval', (array)$types);
			return DB::update($this->_table, $data, "type IN (".dimplode($types).")");
		}
		return 0;
	}
	public function update_by_find($find, $data) {
		return DB::update($this->_table, $data, DB::field('find', $find));
	}

	public function count_by_type_find($type = null, $find = null) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($type !== null) {
			$parameter[] = $type;
			$wherearr[] = "`type`=%d";
		}
		if($find !== null) {
			$parameter[] = '%'.addslashes(stripsearchkey($find)).'%';
			$wherearr[] = "`find` LIKE %s";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first("SELECT COUNT(*) FROM %t $wheresql", $parameter);
	}

}

?>