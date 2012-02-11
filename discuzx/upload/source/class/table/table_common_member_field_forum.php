<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_field_forum.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_field_forum extends discuz_table_archive
{
	public function __construct() {

		$this->_table = 'common_member_field_forum';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'common_member_field_forum_';
		$this->_cache_ttl = 0;

		parent::__construct();
	}

}

?>