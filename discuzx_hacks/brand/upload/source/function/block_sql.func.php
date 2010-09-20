<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_sql.func.php 4371 2010-09-08 06:03:14Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

function runsql($paramarr, $bbsdb='', $returncount=0, $multicachekey='') {
	global $_G, $_SGLOBAL, $_SBLOCK;
	//处理SQL
	$sqlstring = getblocksql($paramarr['sql']);

	//初始化
	$listcount = 1;

	//连接数据库
	//$thedb = empty($bbsdb)?$_SGLOBAL['db']:$bbsdb;

	//分页
	if(!empty($paramarr['perpage'])) {
		$countsql = '';
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)WHERE(.+?)ORDER', 2, 3);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)WHERE(.+?)LIMIT', 2, 3);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)WHERE(.+?)$', 2, 3);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)ORDER', 2, -1);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)LIMIT', 2, -1);
		}
		if(empty($countsql)) {
			$countsql = getcountsql($sqlstring, 'SELECT(.+?)FROM(.+?)$', 2, -1);
		}
		if(!empty($countsql)) {
			if($returncount>0) {
				//需要更新计数缓存时
				$listcount = DB::result_first($countsql);
				return $listcount;
			} else {
				//无需更新缓存时
				$listcount = intval(unserialize($_SBLOCK[$multicachekey]['value']));
			}
			if($listcount) {
				$paramarr['perpage'] = intval($paramarr['perpage']);
				if(empty($paramarr['perpage'])) $paramarr['perpage'] = 20;

				if(empty($_G['page'])) $_G['page'] = 1;
				$_G['page'] = intval($_G['page']);
				if($_G['page'] < 1) $_G['page'] = 1;

				$start = ($_G['page']-1)*$paramarr['perpage'];

				//SQL文
				$sqlstring = preg_replace("/ LIMIT(.+?)$/is", '', $sqlstring);
				$sqlstring .= ' LIMIT '.$start.','.$paramarr['perpage'];
			}
		}
	} elseif(!empty($paramarr['limit'])) {

		$paramarr['limit'] = getdotstring($paramarr['limit'], 'int', true, array(), 1, false);
		if($paramarr['limit']) {
			//SQL文
			$sqlstring = preg_replace("/ LIMIT(.+?)$/is", '', $sqlstring);
			$sqlstring .= ' LIMIT '.$paramarr['limit'];
		}
	}
	return array($sqlstring, $listcount);
}
//获取数量sql
function getcountsql($sqlstring, $rule, $tablename, $where) {
	preg_match("/$rule/i", $sqlstring, $mathes);
	if(empty($mathes)) {
		$countsql = '';
	} else {
		if($where < 0) $mathes[$where] = '1';//无限制条件
		$countsql = "SELECT COUNT(*) FROM {$mathes[$tablename]} WHERE {$mathes[$where]}";
	}
	return $countsql;
}



?>