<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOT
DROP TABLE IF EXISTS pre_plugin_active;
EOT;

runquery($sql);
$finish = true;
?>
