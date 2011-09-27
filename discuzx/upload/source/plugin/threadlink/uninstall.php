<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-08 17:39
 *	Filename: uninstall.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<SQL
DROP TABLE pre_threadlink_base ;
DROP TABLE pre_threadlink_link ;
SQL;
runquery($sql);
$finish = 1;
?>
