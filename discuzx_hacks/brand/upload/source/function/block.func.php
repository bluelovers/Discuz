<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block.func.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

function block_sql($paramarr, $multicachekey='', $returncount=0) {
	global $_G, $_SGLOBAL, $_GET, $_SERVER;

	if(!empty($paramarr['sql'])) {
		require_once(B_ROOT.'./source/function/block_sql.func.php');
		if($returncount>0) {
			return runsql($paramarr, '', 1);
		}
		list($sqlstring, $listcount) = runsql($paramarr, '', 0, $multicachekey);
		if(!empty($paramarr['perpage'])) {
			if($listcount) {
				$urlarr = $_GET;
				foreach($urlarr as $key=>$value) {
					if(empty($value)) {
						unset($urlarr[$key]);
					} else {
						$urlarr[$key] = rawurlencode($urlarr[$key]);
					}
				}
				unset($urlarr['page']);
				$phpurl = arraytostring($urlarr, '=', '&');
				$phpurl = $phpurl?('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$phpurl):('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
				$theblockarr['multipage'] = multi($listcount, $paramarr['perpage'], $_GET['page'], rawurldecode($phpurl), 1);
				$theblockarr['listcount'] = $listcount;
			}
		}
	}

	//查询数据
	if($listcount) {
		//查询
		$query = DB::query($sqlstring);
		while ($value = DB::fetch($query)) {
			if(isset($value['subjectimage'])) {
				$value['thumb'] = getattachurl($value['subjectimage'], 1);
				$value['subjectimage'] = getattachurl($value['subjectimage']);
			}
			$theblockarr[] = $value;
		}
	}
	return $theblockarr;
}

function stripbbcode($string) {
	return preg_replace("/\[.+?\]/i", '', $string);
}
?>