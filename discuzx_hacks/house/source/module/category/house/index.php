<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: house_index.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($sortlist) {
	$sortids = array();
	foreach($sortlist as $id => $sort) {
		$sortids[] = $id;
	}

	$totalthread = $todaythread = 0;
	$query = DB::query("SELECT threads, todaythreads FROM ".DB::table('category_sort')." WHERE sortid IN (".dimplode($sortids).")");
	while($sort = DB::fetch($query)) {
		$totalthread += $sort['threads'];
		$todaythread += $sort['todaythreads'];
	}
}

include template('diy:category/'.$modidentifier.'_index');