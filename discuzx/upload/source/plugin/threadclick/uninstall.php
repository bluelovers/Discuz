<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS `pre_common_plugin_threadclick`;
DELETE FROM `pre_home_clickuser` WHERE idtype='tid';
DELETE FROM `pre_home_click` WHERE idtype='tid';

EOF;

runquery($sql);

$finish = TRUE;

?>