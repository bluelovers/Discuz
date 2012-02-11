<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_article_title.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_article_title extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_article_title';
		$this->_pk    = 'aid';

		parent::__construct();
	}


	public function update_click($cid, $clickid, $incclick) {
		$clickid = intval($clickid);
		return DB::query('UPDATE %t SET click'.$clickid.' = click'.$clickid.'+\'%d\' WHERE aid = %d', array($this->_table, $incclick, $cid));
	}
	public function fetch_count_for_cat($catid) {
		if(empty($catid)) {
			return 0;
		}
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE catid=%d', array($this->_table, $catid));
	}
	public function fetch_count_for_idtype($id, $idtype) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE id=%d AND idtype=%s", array($this->_table, $id, $idtype));
	}
	public function fetch_all_for_cat($catid, $status = null, $orderaid = 0, $start = 0, $limit = 0) {
		if(empty($catid)) {
			return array();
		}
		$statussql = $status !== null ? ' AND '.DB::field('status', $status) : '';
		$orderaidsql = $orderaid ? ' ORDER BY aid DESC' : '';
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('catid', $catid).$statussql.$orderaidsql.DB::limit($start, $limit), array($this->_table));
	}
	public function update_for_cat($catid, $data) {
		if(empty($catid) || empty($data)) {
			return false;
		}
		DB::query('UPDATE '.DB::table($this->_table).' SET '.DB::implode($data).' WHERE '.DB::field('catid', $catid));
	}
	public function range($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' ORDER BY dateline DESC'.DB::limit($start, $limit));
	}
	public function fetch_all_by_sql($where, $order = '', $start = 0, $limit = 0, $count = 0, $alias = '') {
		$where = $where ? " WHERE $where" : '';
		if($count) {
			return DB::result_first('SELECT count(*) FROM '.DB::table($this->_table).' '.$alias.$where.$order.DB::limit($start, $limit));
		}
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' '.$alias.$where.' '.$order.' '.DB::limit($start, $limit));
	}
	public function fetch_all_by_title($idtype, $sqlsubject) {
		return DB::fetch_all("SELECT $idtype FROM ".DB::table($this->_table)." WHERE $sqlsubject");
	}
	public function fetch_all_for_search($aids, $orderby = '', $ascdesc = '', $start = 0, $limit = 0) {
		return DB::fetch_all("SELECT at.*,ac.viewnum, ac.commentnum FROM ".DB::table($this->_table)." at LEFT JOIN ".DB::table('portal_article_count')." ac ON at.aid=ac.aid WHERE at.".DB::field('aid', $aids)." ORDER BY $orderby $ascdesc ".DB::limit($start, $limit));
	}
}

?>