<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DELETE FROM pre_common_cron WHERE `name` = '版主工资发放';

EOF;

runquery($sql);

$finish = TRUE;

?>