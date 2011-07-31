<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE pre_common_member_lastip;
EOF;

runquery($sql);
$finish = true;
?>