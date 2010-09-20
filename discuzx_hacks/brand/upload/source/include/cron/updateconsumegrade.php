<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: updateconsumegrade.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$consumecronfile = B_ROOT.'./data/system/updateconsumegrade.cache.php';
if(!file_exists($consumecronfile)) {
	$consumecrontext = '$cronconsumeid=0';
	writefile($consumecronfile, $consumecrontext, 'php');
}
@include($consumecronfile);
$pernum = 1000;
$resultarr = array();
$wheresql = 'itemid>'.$cronconsumeid.' AND';
$consumenum = DB::result_first('SELECT COUNT(itemid) FROM '.tname('consumeitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC');
$query = DB::query('SELECT itemid, validity_end FROM '.tname('consumeitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC LIMIT '.$pernum);
while($value = DB::fetch($query)) {
	if(!empty($value['validity_end']) && ($value['validity_end'] < $_G['timestamp'])) {
		DB::query('UPDATE '.tname('consumeitems').' SET grade=2 WHERE itemid='.$value['itemid'], 'UNBUFFERED');
	}
	$resultarr[] = $value;
}

if(($consumenum > $pernum)) {

	$cronlastconsume = array_pop($resultarr);
	$cronconsumeid = $cronlastconsume['itemid'];
	$consumecrontext = '$cronconsumeid='.$cronconsumeid;
	writefile($consumecronfile, $consumecrontext, 'php');
	runcron($cron['cronid']);

} else {
	$consumecrontext = '$cronconsumeid=0';
	writefile($consumecronfile, $consumecrontext, 'php');
	cronnextrun(array($cron['cronid']));
}

?>