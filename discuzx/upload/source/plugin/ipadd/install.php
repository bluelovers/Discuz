<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE IF EXISTS pre_common_member_lastip;
CREATE TABLE pre_common_member_lastip (
`uid` MEDIUMINT( 8 ) UNSIGNED NOT NULL PRIMARY KEY ,
`lastip` CHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE = MYISAM ;
EOF;

runquery($sql);
$finish = true;
?>