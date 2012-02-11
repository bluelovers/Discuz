<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_postcache.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_postcache extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_postcache';
		$this->_pk    = 'pid';

		parent::__construct();
	}

	public function delete_by_dateline($dateline) {
		return DB::delete($this->_table, DB::field('dateline', $dateline, '<'));
	}
}

?>