<?php
/*
	Advertisement Centre Database Struct For Discuz! X2 by sw08
	卸載程序
	最後修改:2011-7-20 16:58:49
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE pre_advmarket;
DROP TABLE pre_advmarket_operatelog;

EOF;

runquery($sql);

DB::query("DELETE FROM ".DB::table('common_cron')."  WHERE `filename`='cron_admarket.php'");

$finish = TRUE;
?>