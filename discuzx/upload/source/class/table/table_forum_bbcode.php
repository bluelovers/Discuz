<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_bbcode.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_bbcode extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_bbcode';
		$this->_pk    = 'id';

		parent::__construct();
	}
	public function fetch_all_by_available_icon($available = null, $haveicon = false, $glue = '=', $order = 'displayorder', $sort = 'ASC') {
		$parameter = array($this->_table);
		if($available !== null) {
			$parameter[] = $available;
			$wherearr[] = "available{$glue}%d";
		}
		if($haveicon) {
			$wherearr[] = "icon!=''";
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY $order $sort", $parameter, $this->_pk);
	}


}

?>