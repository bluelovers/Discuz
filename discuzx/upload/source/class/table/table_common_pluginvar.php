<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_pluginvar.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_pluginvar extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_pluginvar';
		$this->_pk    = 'pluginvarid';

		parent::__construct();
	}

	public function fetch_all_by_pluginid($pluginid) {
		return DB::fetch_all("SELECT * FROM %t WHERE pluginid=%d ORDER BY displayorder", array($this->_table, $pluginid));
	}
	public function count_by_pluginid($pluginid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE pluginid=%d", array($this->_table, $pluginid));
	}

	public function update_by_variable($pluginid, $variable, $data) {
		DB::update($this->_table, $data, DB::field('pluginid', $pluginid).' AND '.DB::field('variable', $variable));
	}

	public function update_by_pluginvarid($pluginid, $pluginvarid, $data) {
		DB::update($this->_table, $data, DB::field('pluginid', $pluginid).' AND '.DB::field('pluginvarid', $pluginvarid));
	}

	public function check_variable($pluginid, $variable) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE pluginid=%d AND variable=%s", array($this->_table, $pluginid, $variable));
	}

	public function delete_by_pluginid($pluginid) {
		DB::delete($this->_table, DB::field('pluginid', $pluginid));
	}

	public function delete_by_variable($pluginid, $variable) {
		DB::delete($this->_table, DB::field('pluginid', $pluginid).' AND '.DB::field('variable', $variable));
	}

}

?>