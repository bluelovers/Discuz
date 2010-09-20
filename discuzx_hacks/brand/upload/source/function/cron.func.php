<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron.func.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

include_once(B_ROOT.'./data/system/crons.cache.php');

//执行计划任务，并更新计划任务CACHE
function runcron($cronid = 0) {
	global $_G, $_SGLOBAL, $_SBLOCK, $lang;

	//锁定
	$lockfile = B_ROOT.'./log/cron.lock.log';
	if(file_exists($lockfile)) {
		if($_G['timestamp'] - filemtime($lockfile) < 300) {//5分钟
			return;
		}
	}
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, "\n");
		fclose($fp);
	}

	//读取cron列表缓存
	if(empty($_SGLOBAL['crons'])) return;

	@set_time_limit(1000);
	@ignore_user_abort(true);

	$cronids = array();
	$crons = $cronid ? array($cronid => $_SGLOBAL['crons'][$cronid]) : $_SGLOBAL['crons'];

	if(empty($crons) || !is_array($crons)) return;

	foreach($crons as $id => $cron) {
		if($cron['nextrun'] <= $_G['timestamp'] || $id == $cronid) {
			$cronids[] = $id;
			if(!@include B_ROOT.($cronfile = "./source/include/cron/$cron[filename]")) {
				errorlog('CRON', $cron['name']." : Cron script($cronfile) not found or syntax error", 0);
			}
		}
	}

	cronnextrun($cronids);

	@unlink($lockfile);
}

//下次执行的时间
function cronnextrun($cronids) {
	global $_G, $_SGLOBAL;

	if(!is_array($cronids) || !$cronids) {
		return false;
	}

	$timestamp = $_G['timestamp'];
	$minutenow = gmdate('i', $timestamp + $_G['setting']['timeoffset'] * 3600);
	$hournow = gmdate('H', $timestamp + $_G['setting']['timeoffset'] * 3600);
	$daynow = gmdate('d', $timestamp + $_G['setting']['timeoffset'] * 3600);
	$monthnow = gmdate('m', $timestamp + $_G['setting']['timeoffset'] * 3600);
	$yearnow = gmdate('Y', $timestamp + $_G['setting']['timeoffset'] * 3600);
	$weekdaynow = gmdate('w', $timestamp + $_G['setting']['timeoffset'] * 3600);

	foreach($cronids as $cronid) {
		if(!$cron = $_SGLOBAL['crons'][$cronid]) {
			continue;
		}
		if($cron['weekday'] == -1) {
			if($cron['day'] == -1) {
				$firstday = $daynow;
				$secondday = $daynow + 1;
			} else {
				$firstday = $cron['day'];
				$secondday = $cron['day'] + gmdate('t', $timestamp + $_G['setting']['timeoffset'] * 3600);
			}
		} else {
			$firstday = $daynow + ($cron['weekday'] - $weekdaynow);
			$secondday = $firstday + 7;
		}

		if($firstday < $daynow) {
			$firstday = $secondday;
		}

		if($firstday == $daynow) {
			$todaytime = crontodaynextrun($cron);
			if($todaytime['hour'] == -1 && $todaytime['minute'] == -1) {
				$cron['day'] = $secondday;
				$nexttime = crontodaynextrun($cron, 0, -1);
				$cron['hour'] = $nexttime['hour'];
				$cron['minute'] = $nexttime['minute'];
			} else {
				$cron['day'] = $firstday;
				$cron['hour'] = $todaytime['hour'];
				$cron['minute'] = $todaytime['minute'];
			}
		} else {
			$cron['day'] = $firstday;
			$nexttime = crontodaynextrun($cron, 0, -1);
			$cron['hour'] = $nexttime['hour'];
			$cron['minute'] = $nexttime['minute'];
		}
		$nextrun = gmmktime($cron['hour'], $cron['minute'], 0, $monthnow, $cron['day'], $yearnow) - $_G['setting']['timeoffset'] * 3600;
		DB::query("UPDATE ".tname('crons')." SET lastrun='$timestamp', nextrun='$nextrun' WHERE cronid='$cronid'");
	}

	include_once(B_ROOT.'./source/function/cache.func.php');
	updatecronscache();
	updatecroncache();
}

// gets next run time today after $hour, $minute
// returns -1,-1 if not again today
function crontodaynextrun($cron, $hour = -2, $minute = -2) {
	global $_G, $_SGLOBAL;

	$timestamp = $_G['timestamp'];
	$hour = $hour == -2 ? gmdate('H', $timestamp + $_G['setting']['timeoffset'] * 3600) : $hour;
	$minute = $minute == -2 ? gmdate('i', $timestamp + $_G['setting']['timeoffset'] * 3600) : $minute;

	$nexttime = array();
	if($cron['hour'] == -1 && !$cron['minute']) {
		$nexttime['hour'] = $hour;
		$nexttime['minute'] = $minute + 1;
	} elseif($cron['hour'] == -1 && $cron['minute'] != '') {
		$nexttime['hour'] = $hour;
		if(($nextminute = cronnextminute($cron['minute'], $minute)) === false) {
			++$nexttime['hour'];
			$nextminute = $cron['minute'][0];
		}
		$nexttime['minute'] = $nextminute;
	} elseif($cron['hour'] != -1 && $cron['minute'] == '') {
		if($cron['hour'] < $hour) {
			$nexttime['hour'] = $nexttime['minute'] = -1;
		} else if ($cron['hour'] == $hour) {
			$nexttime['hour'] = $cron['hour'];
			$nexttime['minute'] = $minute + 1;
		} else {
			$nexttime['hour'] = $cron['hour'];
			$nexttime['minute'] = 0;
		}
	} elseif($cron['hour'] != -1 && $cron['minute'] != '') {
		$nextminute = cronnextminute($cron['minute'], $minute);
		if($cron['hour'] < $hour || ($cron['hour'] == $hour && $nextminute === false)) {
			$nexttime['hour'] = -1;
			$nexttime['minute'] = -1;
		} else {
			$nexttime['hour'] = $cron['hour'];
			$nexttime['minute'] = $nextminute;
		}
	}
	if(empty($nexttime['minute'])) $nexttime['minute'] = 0;
	return $nexttime;
}

//一小时内下次执行的分钟
function cronnextminute($nextminutes, $minutenow) {
	foreach($nextminutes as $nextminute) {
		if($nextminute > $minutenow) {
			return $nextminute;
		}
	}
	return false;
}

?>