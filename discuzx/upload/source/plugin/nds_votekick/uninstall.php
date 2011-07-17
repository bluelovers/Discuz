<?php



if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
DB::query("DROP TABLE IF EXISTS ".DB::table('nds_votekick')."");
$finish = TRUE;

?>