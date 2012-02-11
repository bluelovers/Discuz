<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_comment.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_comment extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_comment';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function fetch_all_by_id_idtype($id, $idtype = '', $orderby = '', $ordersc = 'DESC', $start = 0, $limit = 0) {
		$sql = array(DB::field('id', $id));
		if($idtype) {
			$sql[] = DB::field('idtype', $idtype);
		}
		$wheresql = implode(' AND ', $sql);
		if($orderby) {
			$wheresql .= ' ORDER BY '.DB::order($orderby, $ordersc);
		}
		if($limit) {
			$wheresql .= DB::limit($start, $limit);
		}
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, $wheresql));
	}

	public function count_by_id_idtype($id, $idtype) {
		$sql = DB::field('id', $id).' AND '.DB::field('idtype', $idtype);
		return DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, $sql));
	}

	public function delete_by_id_idtype($id, $idtype) {
		$para = DB::field('id', $id);
		if($idtype) {
			$para .= ' AND '.DB::field('idtype', $idtype);
		}
		return DB::delete($this->_table, $para);
	}

	public function count_all_by_search($sql) {
		return DB::result_first('SELECT count(*) FROM %t c WHERE 1 %i', array($this->_table, $sql));
	}

	public function fetch_all_by_search($idtype, $jointable, $sql, $start, $limit) {
		return DB::fetch_all('SELECT c.*, a.title FROM %t c LEFT JOIN %t a ON a.'.$idtype.'=c.id WHERE 1 %i ORDER BY c.dateline DESC %i', array($this->_table, $jointable, $sql, DB::limit($start, $limit)));
	}

}

?>