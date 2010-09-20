<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: updateshopgrade.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$shopcronfile = B_ROOT.'./data/system/updateshopgrade.cache.php';
if(!file_exists($shopcronfile)) {
	$shopcrontext = '$cronshopid=0';
	writefile($shopcronfile, $shopcrontext, 'php');
}
@include($shopcronfile);
$pernum = 1000;
$resultarr = array();
$wheresql = 'itemid>'.$cronshopid.' AND';
$shopnum = DB::result_first('SELECT COUNT(itemid) FROM '.tname('shopitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC');
$query = DB::query('SELECT itemid, validity_end FROM '.tname('shopitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC LIMIT '.$pernum);
while($value = DB::fetch($query)) {
	if(!empty($value['validity_end']) && ($value['validity_end'] < $_G['timestamp'])) {
		DB::query('UPDATE '.tname('shopitems').' SET grade=2 WHERE itemid='.$value['itemid'], 'UNBUFFERED');
	}
	$resultarr[] = $value;
}

if(($shopnum > $pernum)) {

	$cronlastshop = array_pop($resultarr);
	$cronshopid = $cronlastshop['itemid'];
	$shopcrontext = '$cronshopid='.$cronshopid;
	writefile($shopcronfile, $shopcrontext, 'php');
	runcron($cron['cronid']);

} else {
	$shopcrontext = '$cronshopid=0';
	writefile($shopcronfile, $shopcrontext, 'php');
	cronnextrun(array($cron['cronid']));
}

?>