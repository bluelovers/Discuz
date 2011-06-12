<?php
if(!defined('IN_DISCUZ')) {
	exit('Access denied');
}
$sql = <<<EOT
CREATE TABLE pre_plugin_active (
`id` SMALLINT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cid` SMALLINT( 4 ) NOT NULL ,
`title` CHAR( 80 ) NOT NULL ,
`url` VARCHAR( 225 ) NOT NULL ,
`info` VARCHAR( 225 ) NOT NULL ,
`begin` INT( 10 ) NOT NULL ,
`end` INT( 10 ) NOT NULL ,
`place` VARCHAR( 50 ) NOT NULL ,
`dateline` INT( 10 ) NOT NULL
) ENGINE = MYISAM ;
EOT;

runquery($sql);
$finish = true;

?>
