<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_pic.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_pic extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_pic';
		$this->_pk    = 'picid';

		parent::__construct();
	}

	public function update_click($picid, $clickid, $incclick) {
		$clickid = intval($clickid);
		return DB::query('UPDATE %t SET click'.$clickid.' = click'.$clickid.'+\'%d\' WHERE picid = %d', array($this->_table, $incclick, $picid));
	}
	public function update_hot($picid, $num = 1) {
		return DB::query('UPDATE %t SET hot=hot+\'%d\' WHERE picid=%d', array($this->_table, $num, $picid));
	}
	public function update_sharetimes($picid, $num = 1) {
		return DB::query('UPDATE %t SET sharetimes=sharetimes+\'%d\' WHERE picid=%d', array($this->_table, $num, $picid));
	}
	public function fetch_all_by_uid($uids, $start = 0, $limit = 0, $picids = 0) {
		$picidsql = $picids ? DB::field('picid', $picids).' AND ' : '';
		return DB::fetch_all("SELECT * FROM %t WHERE $picidsql ".DB::field('uid', $uids).DB::limit($start, $limit), array($this->_table));
	}
	public function update_for_uid($uids, $picids, $data) {
		return DB::update($this->_table, $data, DB::field('picid', $picids).' AND '.DB::field('uid', $uids));
	}
	public function fetch_all_by_albumid($albumids, $start = 0, $limit = 0, $picids = 0, $orderbypicid = 0, $orderbydateline = 0, $uid = 0, $count = false) {
		$albumids = $albumids < 0 ? 0 : $albumids;
		$picidsql = $picids ? DB::field('picid', $picids).' AND ' : '';
		if($orderbypicid) {
			$ordersql = 'ORDER BY picid DESC ';
		} elseif($orderbydateline) {
			$ordersql = 'ORDER BY dateline DESC ';
		}
		$uidsql = $uid ? ' AND '.DB::field('uid', $uid) : '';
		if ($count) {
			return DB::result_first("SELECT COUNT(*) FROM %t WHERE $picidsql ".DB::field('albumid', $albumids)." $uidsql", array($this->_table));
		} else {
			return DB::fetch_all("SELECT * FROM %t WHERE $picidsql ".DB::field('albumid', $albumids)." $uidsql $ordersql".DB::limit($start, $limit), array($this->_table));
		}
	}
	public function update_for_albumid($albumid, $data) {
		return DB::update($this->_table, $data, DB::field('albumid', $albumid));
	}
	public function delete_by_uid($uids) {
		return DB::query("DELETE FROM %t WHERE ".DB::field('uid', $uids), array($this->_table));
	}
	public function delete_by_albumid($albumids) {
		return DB::query("DELETE FROM %t WHERE ".DB::field('albumid', $albumids), array($this->_table));
	}
	public function fetch_all_by_sql($where = '1', $orderby = '', $start = 0, $limit = 0, $count = 0, $joinalbum = 1) {
		if($count) {
			return DB::result_first("SELECT count(*) FROM ".DB::table($this->_table)." p WHERE $where");
		}
		return DB::fetch_all("SELECT ".($joinalbum ? 'a.*, ' : '')."p.* FROM ".DB::table($this->_table)." p ".($joinalbum ? "LEFT JOIN ".DB::table('home_album')." a USING(albumid)" : '')." WHERE $where ".($orderby ? "ORDER BY $orderby " : '').DB::limit($start, $limit));
	}
	public function fetch_albumpic($albumid, $uid) {
		return DB::fetch_first("SELECT filepath, thumb FROM %t WHERE albumid=%d AND uid=%d ORDER BY thumb DESC, dateline DESC LIMIT 0,1", array($this->_table, $albumid, $uid));
	}
	public function check_albumpic($albumid, $status = NULL, $uid = 0) {
		$sql = $albumid ? DB::field('albumid', $albumid) : '';
		$sql .= $uid ? ($sql ? ' AND ' : '').DB::field('uid', $uid) : '';
		$sql .= $status === NULL ? '' : ($sql ? ' AND ' : '').DB::field('status', $status);
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE $sql", array($this->_table));
	}
	public function count_size_by_uid($uid) {
		return DB::result_first("SELECT SUM(size) FROM ".DB::table($this->_table)." WHERE uid=%d", array($this->_table, $uid));
	}
	public function fetch_by_id_idtype($id, $idtype) {
		return DB::fetch_first('SELECT * FROM %t WHERE %i', array($this->_table, DB::field($idtype, $id)));
	}
	public function update_dateline_by_id_idtype_uid($id, $idtype, $dateline, $uid) {
		return DB::update($this->_table, array('dateline' => $dateline), array($idtype => $id, 'uid' => $uid));
	}
}

?>