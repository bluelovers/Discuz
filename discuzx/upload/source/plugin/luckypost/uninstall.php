<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugintable = DB::table('common_plugin_luckypost');
$pluginlogtable = DB::table('common_plugin_luckypostlog');

$sql = <<<EOF

DROP TABLE IF EXISTS {$plugintable};
DROP TABLE IF EXISTS {$pluginlogtable};

EOF;

runquery($sql);

$finish = TRUE;

?>