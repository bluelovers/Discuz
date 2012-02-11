<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_nav.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_nav extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_nav';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_by_id_navtype($id, $navtype) {
		return DB::fetch_first('SELECT * FROM %t WHERE id=%d AND navtype=%d', array($this->_table, $id, $navtype));
	}

	public function fetch_by_type_identifier($type, $identifier) {
		return DB::fetch_first('SELECT * FROM %t WHERE type=%d AND identifier=%s', array($this->_table, $type, $identifier));
	}

	public function fetch_all_by_navtype($navtype = null) {
		$parameter = array($this->_table);
		$wheresql = '';
		if($navtype !== null) {
			$parameter[] = $navtype;
			$wheresql = ' WHERE navtype=%d';
		}
		return DB::fetch_all('SELECT * FROM %t '.$wheresql.' ORDER BY displayorder', $parameter, $this->_pk);
	}

	public function fetch_all_by_navtype_parentid($navtype, $parentid) {
		return DB::fetch_all('SELECT * FROM %t WHERE navtype=%d AND parentid=%d ORDER BY displayorder', array($this->_table, $navtype, $parentid), $this->_pk);
	}
	public function fetch_all_mainnav() {
		return DB::fetch_all('SELECT * FROM %t WHERE navtype=0 AND (available=1 OR type=0) AND parentid=0 ORDER BY displayorder', array($this->_table), $this->_pk);
	}
	public function fetch_all_subnav($parentid) {
		return DB::fetch_all('SELECT * FROM %t WHERE navtype=0 AND parentid=%d AND available=1 ORDER BY displayorder', array($this->_table, $parentid), $this->_pk);
	}

	public function update_by_identifier($identifier, $data) {
		return DB::update($this->_table, $data, DB::field('identifier', $identifier));
	}
	public function update_by_type_identifier($type, $identifier, $data) {
		return DB::update($this->_table, $data, DB::field('type', $type).' AND '.DB::field('identifier', $identifier));
	}

	public function delete_by_navtype_id($navtype, $ids) {
		return DB::delete($this->_table, DB::field('id', $ids).' AND '.DB::field('navtype', $navtype));
	}
	public function delete_by_navtype_parentid($navtype, $parentid) {
		return DB::delete($this->_table, DB::field('navtype', $navtype).' AND '.DB::field('parentid', $parentid));
	}

	public function delete_by_type_identifier($type, $identifier) {
		return DB::delete($this->_table, DB::field('type', $type).' AND '.DB::field('identifier', $identifier));
	}
	public function delete_by_parentid($id) {
		return DB::delete($this->_table, DB::field('parentid', $id));
	}
	public function count_by_navtype_type_identifier($navtype, $type, $identifier) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE navtype=%d AND type=%d AND identifier=%s', array($this->_table, $navtype, $type, $identifier));
	}

}

?>