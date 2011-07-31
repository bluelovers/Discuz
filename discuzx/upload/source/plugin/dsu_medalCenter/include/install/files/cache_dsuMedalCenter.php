<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: cache_dsuMedalCenter.php 26 2011-01-08 19:00:35Z chuzhaowei@gmail.com $
*/
!defined('IN_DISCUZ') && exit('Access Denied');

function build_cache_dsuMedalCenter() {
	$data = array();
	$query = DB::query("SELECT m.*, mf.* FROM ".DB::table('forum_medal')." m LEFT JOIN ".DB::table('dsu_medalfield')." mf ON m.medalid = mf.medalid");
	while($medal = DB::fetch($query)) {
		$data[$medal['medalid']] = array('name' => $medal['name'], 'image' => $medal['image']);
	}
	save_syscache('dsuMedalCenter', $data);
}

?>