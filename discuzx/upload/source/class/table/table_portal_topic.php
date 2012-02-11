<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_topic.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_topic extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_topic';
		$this->_pk    = 'topicid';

		parent::__construct();
	}

	public function count_by_search_where($wherearr) {
		$wheresql = empty($wherearr) ? '' : ' WHERE '.implode(' AND ', $wherearr);
		return DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).$wheresql);
	}

	public function fetch_all_by_search_where($wherearr, $ordersql, $start, $limit) {
		$wheresql = empty($wherearr) ? '' : ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).$wheresql.' '.$ordersql.DB::limit($start, $limit), null, 'topicid');
	}

	public function fetch_by_name($name) {
		return $name ? DB::fetch_first('SELECT * FROM %t WHERE name=%s LIMIT 1', array($this->_table, $name)) : false;
	}

	public function increase($ids, $data) {
		$ids = array_map('dintval', (array)$ids);
		$sql = array();
		$allowkey = array('commentnum', 'viewnum');
		foreach($data as $key => $value) {
			if(($value = intval($value)) && in_array($key, $allowkey)) {
				$sql[] = "`$key`=`$key`+'$value'";
			}
		}
		if(!empty($sql)){
			DB::query('UPDATE '.DB::table($this->_table).' SET '.implode(',', $sql).' WHERE uid IN ('.dimplode($ids).')', 'UNBUFFERED');
		}
	}
	public function fetch_all_by_title($idtype, $sqlsubject) {
		return DB::fetch_all("SELECT $idtype FROM ".DB::table($this->_table)." WHERE $sqlsubject");
	}
}

?>