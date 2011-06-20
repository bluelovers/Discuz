<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
INSERT INTO pre_common_cron VALUES ('0','1','system','版主工资发放','cron_teamstar.php','1308127620','1309446000','-1','30','23','55');
EOF;

runquery($sql);

$finish = TRUE;
?>