<?php
/*
	Install Uninstall Upgrade AutoStat System Code
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

DB::query("DROP TABLE IF EXISTS ".DB::table('ddu_dev_dzx')."");
$finish = TRUE;
?>