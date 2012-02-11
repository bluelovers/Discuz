<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_myapp.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_myapp extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_myapp';
		$this->_pk    = 'appid';

		parent::__construct();
	}
	public function fetch_all_by_flag($flag, $glue = '=', $sort = 'ASC') {
		return DB::fetch_all("SELECT * FROM %t WHERE flag{$glue}%d ORDER BY displayorder $sort", array($this->_table, $flag), $this->_pk);
	}

}

?>