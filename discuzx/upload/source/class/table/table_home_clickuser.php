<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_clickuser.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_clickuser extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_clickuser';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_id_idtype($id, $idtype, $start = 0, $limit = 0) {
		$parameter = array($this->_table, $id, $idtype);
		$wherearr = array();
		$wherearr[] = is_array($id) ? 'id IN(%n)' : 'id=%d';
		$wherearr[] = 'idtype=%s';
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY dateline DESC ".DB::limit($start, $limit), $parameter);
	}

	public function delete_by_id_idtype($id, $idtype) {
		$parameter = array($this->_table, $id, $idtype);
		$wherearr = array();
		$wherearr[] = is_array($id) ? 'id IN(%n)' : 'id=%d';
		$wherearr[] = 'idtype=%s';
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::query('DELETE FROM %t '.$wheresql, $parameter);
	}

	public function delete_by_dateline($dateline) {
		return DB::query('DELETE FROM %t WHERE dateline<%d', array($this->_table, $dateline));
	}
	public function delete_by_uid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids));
	}

	public function count_by_uid_id_idtype($uid, $id, $idtype) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND id=%d AND idtype=%s', array($this->_table, $uid, $id, $idtype));
	}

	public function count_by_id_idtype($id, $idtype) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE id=%d AND idtype=%s', array($this->_table, $id, $idtype));
	}

}

?>