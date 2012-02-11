<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_connect.php 26281 2011-12-08 02:00:40Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_connect extends discuz_table {

	private $_fields;

	public function __construct() {
		$this->_table = 'common_member_connect';
		$this->_pk = 'uid';
		$this->_fields = array('uid', 'conuin', 'conuinsecret', 'conopenid', 'conisfeed', 'conispublishfeed', 'conispublisht', 'conisregister', 'conisqzoneavatar');

		parent::__construct();
	}

	public function fetch_fields_by_openid($openid, $fields = array()) {
		$fields = (array)$fields;
		if(!empty($fields)) {
			$field = implode(',', array_intersect($fields, $this->_fields));
		} else {
			$field = '*';
		}
		if($fields) {
			return DB::fetch_first('SELECT %i FROM %t WHERE conopenid=%s', array($field, $this->_table, $openid));
		}

		return false;
	}



}
?>