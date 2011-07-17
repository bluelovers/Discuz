<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_forum_imgpoll;
DROP TABLE IF EXISTS pre_forum_imgpolloption;

EOF;

runquery($sql);
$finish = TRUE;
?>