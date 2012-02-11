<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_smiley.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_smiley extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_smiley';
		$this->_pk    = 'id';

		parent::__construct();
	}
	public function fetch_all_by_type($type) {
		$typesql = is_array($type) ? 'type IN(%n)' : 'type=%s';
		return DB::fetch_all("SELECT * FROM %t WHERE $typesql ORDER BY displayorder", array($this->_table, $type), $this->_pk);
	}
	public function fetch_all_by_typeid_type($typeid, $type, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE typeid=%d AND type=%s ORDER BY displayorder '.DB::limit($start, $limit), array($this->_table, $typeid, $type), $this->_pk);
	}
	public function fetch_all_by_type_code_typeid($type, $typeid) {
		return DB::fetch_all("SELECT * FROM %t WHERE type=%s AND code<>'' AND typeid=%d ORDER BY displayorder ", array($this->_table, $type, $typeid), $this->_pk);
	}
	public function fetch_all_cache() {
		return DB::fetch_all("SELECT s.id, s.code, s.url, t.typeid FROM %t s INNER JOIN %t t ON t.typeid=s.typeid WHERE s.type='smiley' AND s.code<>'' AND t.available='1' ORDER BY LENGTH(s.code) DESC", array($this->_table, 'forum_imagetype'));

	}
	public function fetch_by_id_type($id, $type) {
		return DB::fetch_first('SELECT * FROM %t WHERE id=%d AND type=%s', array($this->_table, $id, $type), $this->_pk);
	}
	public function update_by_type($type, $data) {
		return DB::update($this->_table, $data, DB::field('type', $type));
	}
	public function update_by_id_type($id, $type, $data) {
		return DB::update($this->_table, $data, DB::field('id', $id).' AND '.DB::field('type', $type));
	}
	public function update_code_by_typeid($typeid) {
		$typeidsql = is_array($typeid) ? 'typeid IN(%n)' : 'typeid=%d';
		return DB::query("UPDATE %t SET code=CONCAT('{:', typeid, '_', id, ':}') WHERE $typeidsql", array($this->_table, $typeid));
	}
	public function update_code_by_id($ids) {
		$idssql = is_array($ids) ? 'id IN(%n)' : 'id=%d';
		return DB::query("UPDATE %t SET code=CONCAT('{:', typeid, '_', id, ':}') WHERE $idssql", array($this->_table, $ids));
	}
	public function count_by_type($type) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE type IN(%n)', array($this->_table, $type));
	}
	public function count_by_typeid($typeid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE typeid=%d', array($this->_table, $typeid));
	}
	public function count_by_type_typeid($type, $typeid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE type=%s AND typeid IN(%n)', array($this->_table, $type, $typeid));
	}
	public function count_by_type_code_typeid($type, $typeid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE type=%s AND code<>'' AND typeid=%d", array($this->_table, $type, $typeid));
	}

}

?>