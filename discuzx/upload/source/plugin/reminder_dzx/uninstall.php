<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$plugintable = DB::table('common_plugin_reminder');
$sql = <<<EOF

DROP TABLE IF EXISTS $plugintable;

EOF;

runquery($sql);

$finish = TRUE;

?>