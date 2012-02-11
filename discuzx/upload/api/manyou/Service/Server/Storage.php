<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Storage.php 27316 2012-01-16 03:08:11Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Storage extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onStorageSetConfig($data) {
		if ($data['xf_storage_enc_key']) {
			$insert = array(
						'skey' => 'xf_storage_enc_key',
						'svalue' => $data['xf_storage_enc_key'],
					);
			C::t('common_setting')->insert($insert, 0, 1);
	        return true;
	    }
	    return false;
	}
}