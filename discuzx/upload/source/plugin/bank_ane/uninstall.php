<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tablepre = DB::table('');
$sql = <<<EOF
DROP TABLE IF EXISTS {$tablepre}plugin_banklist;
DROP TABLE IF EXISTS {$tablepre}plugin_bankoperation;
DROP TABLE IF EXISTS {$tablepre}plugin_banklog;
EOF;

DB::query("DELETE FROM ".DB::table('common_addon')." WHERE `key`='44yP53Cy4O'");
runquery($sql);

$finish = TRUE;
?>
