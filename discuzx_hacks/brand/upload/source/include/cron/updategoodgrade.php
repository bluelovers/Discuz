<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: updategoodgrade.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$goodcronfile = B_ROOT.'./data/system/updategoodgrade.cache.php';
if(!file_exists($goodcronfile)) {
	$goodcrontext = '$crongoodid=0';
	writefile($goodcronfile, $goodcrontext, 'php');
}
@include($goodcronfile);
$pernum = 1000;
$resultarr = array();
$wheresql = 'itemid>'.$crongoodid.' AND';
$goodnum = DB::result_first('SELECT COUNT(itemid) FROM '.tname('gooditems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC');
$query = DB::query('SELECT itemid, validity_end FROM '.tname('gooditems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC LIMIT '.$pernum);
while($value = DB::fetch($query)) {
	if(!empty($value['validity_end']) && ($value['validity_end'] < $_G['timestamp'])) {
		DB::query('UPDATE '.tname('gooditems').' SET grade=2 WHERE itemid='.$value['itemid'], 'UNBUFFERED');
	}
	$resultarr[] = $value;
}

if(($goodnum > $pernum)) {

	$cronlastgood = array_pop($resultarr);
	$crongoodid = $cronlastgood['itemid'];
	$goodcrontext = '$crongoodid='.$crongoodid;
	writefile($goodcronfile, $goodcrontext, 'php');
	runcron($cron['cronid']);

} else {
	$goodcrontext = '$crongoodid=0';
	writefile($goodcronfile, $goodcrontext, 'php');
	cronnextrun(array($cron['cronid']));
}

?>