<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mod_cron.php 21878 2011-04-14 09:23:58Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mod_cron extends remote_service
{
	function run() {

		if(!$this->config['cron']) {
			$this->error(100, 'cron service is off. Please check "config.global.php" on your webserver folder.');
		}

		$discuz = & discuz_core::instance();
		$discuz->init_cron = true;
		$discuz->_init_cron();
		$this->success('Cron work is done');
	}

}