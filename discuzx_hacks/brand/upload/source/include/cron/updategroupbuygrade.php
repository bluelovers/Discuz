<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: updategroupbuygrade.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$groupbuycronfile = B_ROOT.'./data/system/updategroupbuygrade.cache.php';
if(!file_exists($groupbuycronfile)) {
	$groupbuycrontext = '$crongroupbuyid=0';
	writefile($groupbuycronfile, $groupbuycrontext, 'php');
}
@include($groupbuycronfile);
$pernum = 1000;
$resultarr = array();
$wheresql = 'itemid>'.$crongroupbuyid.' AND';
$groupbuynum = DB::result_first('SELECT COUNT(itemid) FROM '.tname('groupbuyitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC');
$query = DB::query('SELECT itemid, validity_end FROM '.tname('groupbuyitems').' WHERE '.$wheresql.' grade>2 ORDER BY itemid ASC LIMIT '.$pernum);
while($value = DB::fetch($query)) {
	if(!empty($value['validity_end']) && ($value['validity_end'] < $_G['timestamp'])) {
		DB::query('UPDATE '.tname('groupbuyitems').' SET grade=2 WHERE itemid='.$value['itemid'], 'UNBUFFERED');
	}
	$resultarr[] = $value;
}

if(($groupbuynum > $pernum)) {

	$cronlastgroupbuy = array_pop($resultarr);
	$crongroupbuyid = $cronlastgroupbuy['itemid'];
	$groupbuycrontext = '$crongroupbuyid='.$crongroupbuyid;
	writefile($groupbuycronfile, $groupbuycrontext, 'php');
	runcron($cron['cronid']);

} else {
	$groupbuycrontext = '$crongroupbuyid=0';
	writefile($groupbuycronfile, $groupbuycrontext, 'php');
	cronnextrun(array($cron['cronid']));
}

?>