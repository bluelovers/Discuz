<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_portal_article_related.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_portal_article_related extends discuz_table
{
	public function __construct() {

		$this->_table = 'portal_article_related';
		$this->_pk    = 'aid';

		parent::__construct();
	}

	public function delete_by_aid_raid($aid, $raid = null) {
		$aid ? DB::delete($this->_table, DB::field('aid', $aid).($raid ? ' OR '.DB::field('raid', $raid) : '')) : '';
	}

	public function insert_batch($aid, $list) {
		$replaces = array();
		$displayorder = 0;
		unset($list[$aid]);
		foreach($list as $value) {
			$replaces[] = "('$aid', '$value[aid]', '$displayorder')";
			$replaces[] = "('$value[aid]', '$aid', '0')";
			$displayorder++;
		}
		if($replaces) {
			DB::query('REPLACE INTO '.DB::table($this->_table).' (aid,raid,displayorder) VALUES '.implode(',', $replaces));
		}
	}

	public function fetch_all_by_aid($aid) {
		return $aid ? DB::fetch_all('SELECT * FROM %t WHERE aid=%d ORDER BY displayorder', array($this->_table, $aid), 'raid') : array();
	}
}

?>