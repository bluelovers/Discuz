<?php
/*
	Advertisement Centre Database Struct For Discuz! X2 by sw08
	升級程序
	1.01->1.1升級程序
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

ALTER TABLE `pre_advmarket` ADD `desc` TEXT NOT NULL,
ADD `allowedit` TINYINT( 1 ) NOT NULL DEFAULT '0';

EOF;

runquery($sql);

$finish = TRUE;
?>