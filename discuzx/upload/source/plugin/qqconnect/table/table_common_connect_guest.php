<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_connect_guest.php 28639 2012-03-06 12:05:21Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_connect_guest extends discuz_table {

    public function __construct() {
        $this->_table = 'common_connect_guest';
        $this->_pk = 'conopenid';

        parent::__construct();
    }

}
?>