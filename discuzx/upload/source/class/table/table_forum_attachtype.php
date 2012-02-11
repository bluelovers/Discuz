<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_attachtype.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_attachtype extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_attachtype';
		$this->_pk    = 'id';

		parent::__construct();
	}
	public function fetch_by_fid_extension($fid, $extension) {
		return DB::fetch_first('SELECT * FROM %t WHERE fid=%d AND extension=%s', array($this->_table, $fid, $extension));
	}
	public function fetch_all_by_fid($fid) {
		return DB::fetch_all('SELECT * FROM %t WHERE fid=%d', array($this->_table, $fid), $this->_pk);
	}
	public function delete_by_id_fid($id, $fid) {
		return DB::delete($this->_table, DB::field('id', $id).' AND '.DB::field('fid', $fid));
	}
	public function count_by_extension_fid($extension, $fid = null) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($fid !== null) {
			$wherearr[] = 'fid=%d';
			$parameter[] = $fid;
		}
		$parameter[] = $extension;
		$wherearr[] = 'extension=%s';
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::result_first('SELECT COUNT(*) FROM %t'.$wheresql, $parameter);
	}

	public function count_by_fid($fid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE fid=%d', array($this->_table, $fid));
	}

}

?>