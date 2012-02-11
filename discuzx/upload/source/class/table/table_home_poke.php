<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_poke.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_poke extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_poke';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_all_by_uid_fromuid($uid, $fromuid) {
		$wherearr = array();
		$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
		$wherearr[] = is_array($fromuid) ? 'fromuid IN(%n)' : 'fromuid=%d';

		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

		return DB::fetch_all('SELECT * FROM %t '.$wheresql, array($this->_table, $uid, $fromuid));
	}

	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $uid));
	}

	public function delete_by_uid_or_fromuid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids).' OR '.DB::field('fromuid', $uids));
	}

	public function delete_by_uid_fromuid($uids, $fromuid = 0) {
		$parameter = array($this->_table, $uids);
		$wherearr = array();
		$wherearr[] = is_array($uids) ? 'uid IN(%n)' : 'uid=%d';
		if($fromuid) {
			$wherearr[] = is_array($fromuid) ? 'fromuid IN(%n)' : 'fromuid=%d';
			$parameter[] = $fromuid;
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::query('DELETE FROM %t '.$wheresql, $parameter);
	}

	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

}

?>