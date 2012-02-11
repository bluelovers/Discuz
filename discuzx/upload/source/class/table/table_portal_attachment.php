<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_attachment.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_attachment extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_attachment';
		$this->_pk    = 'attachid';

		parent::__construct();
	}

	public function fetch_all_by_aid($aid) {
		return $aid ? DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('aid', $aid).' ORDER BY attachid DESC', array($this->_table), $this->_pk) : array();
	}

	public function fetch_by_aid_image($aid) {
		return $aid ? DB::fetch_first('SELECT * FROM %t WHERE aid=%d AND isimage=1', array($this->_table, $aid)) : array();
	}

	public function update_to_used($newaids, $aid) {
		DB::update($this->_table, array('aid'=>$aid), DB::field('attachid', $newaids).' AND aid=0');
	}

}

?>