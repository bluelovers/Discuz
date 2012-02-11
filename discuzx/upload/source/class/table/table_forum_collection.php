<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collection.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collection extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collection';
		$this->_pk    = 'ctid';
		$this->_pre_cache_key = 'forum_collection_';

		parent::__construct();
	}


	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid), $this->_pk);
	}

	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d '.DB::limit($start, $limit), array($this->_table, $uid), $this->_pk);
	}

	public function range($start = 0, $limit = 0, $reqthread = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE threadnum>=%d ORDER BY lastupdate DESC '.DB::limit($start, $limit), array($this->_table, $reqthread));
	}

	public function fetch_all($ctid = '', $orderby = '', $ordersc = '', $start = 0, $limit = 0, $title = '', $cachetid = '') {
		if($this->_allowmem && $cachetid) {
			$data = $this->fetch_cache($cachetid, $this->_pre_cache_key.'tid_');
			if($data) {
				return $data;
			}
		}
		$sql = '';
		if($ctid) {
			$sql .= 'WHERE '.DB::field('ctid', $ctid);
		}
		if($title && str_replace('%', '', $title)) {
			$sql .= ($sql ? ' AND ' : 'WHERE ').DB::field('name', '%'.$title.'%', 'like');
		}
		$sql .= ($orderby && $ordersc) ? ' ORDER BY '.DB::order($orderby, $ordersc) : '';
		$sql .= ' '.DB::limit($start, $limit);
		if(!$sql) {
			return null;
		}
		$data = DB::fetch_all('SELECT * FROM %t %i', array($this->_table, $sql), $this->_pk);
		if($this->_allowmem && $cachetid) {
			$this->store_cache($cachetid, $data, $this->_cache_ttl, $this->_pre_cache_key.'tid_');
		}
		return $data;
	}

	public function count_by_title($title) {
		if(!$title || !str_replace('%', '', $title)) {
			return null;
		}
		$sql = DB::field('name', '%'.$title.'%', 'like');
		return DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, $sql));
	}

	public function count_all_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}

	public function update_by_ctid($ctid, $incthreadnum = 0, $incfollownum = 0, $inccommentnum = 0, $lastupdate = 0, $incratenum = 0, $totalratenum = 0, $lastpost = '') {
		$sql = array();
		$para = array($this->_table);
		if($incthreadnum) {
			$sql[] = 'threadnum=threadnum+\'%d\'';
			$para[] = $incthreadnum;
		}
		if($incfollownum) {
			$sql[] = 'follownum=follownum+\'%d\'';
			$para[] = $incfollownum;
		}
		if($inccommentnum) {
			$sql[] = 'commentnum=commentnum+\'%d\'';
			$para[] = $inccommentnum;
		}
		if($lastupdate != 0) {
			$sql[] = 'lastupdate=%d';
			$para[] = $lastupdate;
		}
		if($incratenum > 0) {
			if($totalratenum > 0) {
				$sql[] = 'ratenum=ratenum+1,rate=(rate+\'%d\')/2';
			} else {
				$sql[] = 'ratenum=ratenum+1,rate=%d';
			}
			$para[] = $incratenum;
		}
		if(count($lastpost) == 4) {
			$sql[] = 'lastpost=%d,lastsubject=%s,lastposttime=%d,lastposter=%s';
			$para = array_merge($para, array($lastpost['lastpost'], $lastpost['lastsubject'], $lastpost['lastposttime'], $lastpost['lastposter']));
		}
		if(!count($sql)) {
			return null;
		}

		$sqlupdate = implode(',', $sql);

		$result = DB::query('UPDATE %t SET '.$sqlupdate.' WHERE '.DB::field($this->_pk, $ctid), $para, false, true);
		return $result;
	}

	public function fetch_all_for_search($conditions, $start = 0, $limit = 20) {
		if(empty($conditions)) {
			return array();
		}
		if($start == -1) {
			return DB::result_first("SELECT count(*) FROM %t WHERE %i", array($this->_table, $conditions));
		}
		return DB::fetch_all("SELECT * FROM %t 	WHERE %i ORDER BY dateline DESC %i", array($this->_table, $conditions, DB::limit($start, $limit)));
	}

	public function update($val, $data, $unbuffered = false, $low_priority = false) {
		$this->checkpk();
		$ret = DB::update($this->_table, $data, DB::field($this->_pk, $val), $unbuffered, $low_priority);
		return $ret;
	}

	public function delete($val, $unbuffered = false) {
		$this->checkpk();
		$ret = DB::delete($this->_table, DB::field($this->_pk, $val), null, $unbuffered);
		return $ret;
	}

	public function fetch($id, $force_from_db = true){
		return parent::fetch($id, true);
	}

}

?>