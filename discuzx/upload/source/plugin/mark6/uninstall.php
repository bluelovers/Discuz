<?php
// this file is not created by author 1224
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
exit('ACCESS DENIED');
}
$tablepre = DB::table('plugin_');
$charset = $_G['charset'];
$commonpre = DB::table('common_');
$uninstallSQL = <<<EOT
DROP TABLE IF EXISTS `{$tablepre}mark6`;
DROP TABLE IF EXISTS `{$tablepre}mark6cp`;
DROP TABLE IF EXISTS `{$tablepre}mark6jackpot`;
DROP TABLE IF EXISTS `{$tablepre}mark6list`;

DELETE FROM `{$commonpre}cron` WHERE filename='cron_mark6.php';
EOT;
runquery($uninstallSQL);
$finish = true;

?>