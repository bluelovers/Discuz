<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: updatenoticegrade.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$noticecronfile = B_ROOT.'./data/system/updatenoticegrade.cache.php';
if(!file_exists($noticecronfile)) {
	$noticecrontext = '$cronnoticeid=0';
	writefile($noticecronfile, $noticecrontext, 'php');
}
@include($noticecronfile);
$pernum = 1000;
$resultarr = array();
$wheresql = 'itemid>'.$cronnoticeid.' AND';
$noticenum = DB::result_first('SELECT COUNT(itemid) FROM '.tname('noticeitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC');
$query = DB::query('SELECT itemid, validity_end FROM '.tname('noticeitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC LIMIT '.$pernum);
while($value = DB::fetch($query)) {
	if(!empty($value['validity_end']) && ($value['validity_end'] < $_G['timestamp'])) {
		DB::query('UPDATE '.tname('noticeitems').' SET grade=2 WHERE itemid='.$value['itemid'], 'UNBUFFERED');
	}
	$resultarr[] = $value;
}

if(($noticenum > $pernum)) {

	$cronlastnotice = array_pop($resultarr);
	$cronnoticeid = $cronlastnotice['itemid'];
	$noticecrontext = '$cronnoticeid='.$cronnoticeid;
	writefile($noticecronfile, $noticecrontext, 'php');
	runcron($cron['cronid']);

} else {
	$noticecrontext = '$cronnoticeid=0';
	writefile($noticecronfile, $noticecrontext, 'php');
	cronnextrun(array($cron['cronid']));
}

?>