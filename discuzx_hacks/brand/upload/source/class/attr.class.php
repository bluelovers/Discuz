<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attr.class.php 4024 2010-07-28 10:18:05Z bihuizi $
 */

class attr {

	function __construct() {
	}

	function attr() {
		$this->__construct();
	}

	function get_groupby_user_attr() {
		$attr = array();
		include_once(B_ROOT.'./source/function/cache.func.php');
		//讀入緩存
		$mname = 'shop';
		$cacheinfo = getmodelinfoall('modelname', 'groupbuy');
		if(!empty($cacheinfo['columns'])) {
			foreach($cacheinfo['columns'] as $column) {
				if($column['allowpost'] == 1 && preg_match('/^user_/',$column['fieldname'])) {
				$attr[] = $column;
				}
			}
		}
		return $attr;
	}

}
?>