<?php
/*
	Install Uninstall Upgrade AutoStat System Code
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE IF EXISTS `cdb_ddu_dev_dzx`;
CREATE TABLE IF NOT EXISTS `cdb_ddu_dev_dzx` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
`task` VARCHAR( 100 ) NOT NULL ,
`stat` VARCHAR( 50 ) NOT NULL ,
`url` VARCHAR( 200 ) NOT NULL ,
`user` VARCHAR( 100 ) NOT NULL ,
`last` VARCHAR( 10 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ENGINE = MYISAM CHARACTER SET gbk COLLATE gbk_chinese_ci;
EOF;
runquery($sql);
$finish = TRUE;
?>