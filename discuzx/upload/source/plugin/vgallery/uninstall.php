<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
DB::query("DROP TABLE IF EXISTS hsk_vgallerys");
DB::query("DROP TABLE IF EXISTS hsk_vgallery_evaluate");
DB::query("DROP TABLE IF EXISTS hsk_vgallery_sort");
DB::query("DROP TABLE IF EXISTS hsk_vgallery_report");
DB::query("DROP TABLE IF EXISTS hsk_vgallery_top5");
DB::query("DROP TABLE IF EXISTS hsk_vgallery_favorites");
$finish = TRUE;
?>