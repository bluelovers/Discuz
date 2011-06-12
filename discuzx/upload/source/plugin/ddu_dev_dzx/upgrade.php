<?php
/*
	Install Uninstall Upgrade AutoStat System Code
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
UPDATE `cdb_ddu_dev_dzx` SET `stat` = 'OK' WHERE `stat` ='ok';
UPDATE `cdb_ddu_dev_dzx` SET `stat` = 'ря╥жеи' WHERE `stat` ='FP';
EOF;
runquery($sql);
$finish = TRUE;
?>