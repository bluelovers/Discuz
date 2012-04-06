<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: job.inc.php 27070 2012-01-04 05:55:20Z songlixin $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_G['gp_formhash'] != formhash()) {
	exit('Access Denied');
}

require_once libfile('class/sec');
$sec = Sec::getInstance();

$limit = 3;
while ($limit > 0) {
	$limit = $limit - 1;
	$sec->retryReportData();
}



?>