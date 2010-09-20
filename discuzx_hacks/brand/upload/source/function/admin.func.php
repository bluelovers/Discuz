<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admin.func.php 4378 2010-09-09 02:55:13Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

//获取限制条件
function getwheres($intkeys, $strkeys, $randkeys, $likekeys, $pre='') {

	$wherearr = array();
	$urls = array();

	foreach ($intkeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)) {
			$wherearr[] = "{$pre}{$var}='".intval($value)."'";
			$urls[] = "$var=$value";
		}
	}

	foreach ($strkeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)) {
			$wherearr[] = "{$pre}{$var}='$value'";
			$urls[] = "$var=".rawurlencode($value);
		}
	}

	foreach ($randkeys as $vars) {
		$value1 = isset($_GET[$vars[1].'1'])?$vars[0]($_GET[$vars[1].'1']):'';
		$value2 = isset($_GET[$vars[1].'2'])?$vars[0]($_GET[$vars[1].'2']):'';
		if($value1) {
			$wherearr[] = "{$pre}{$vars[1]}>='$value1'";
			$urls[] = "{$vars[1]}1=".rawurlencode($_GET[$vars[1].'1']);
		}
		if($value2) {
			$wherearr[] = "{$pre}{$vars[1]}<='$value2'";
			$urls[] = "{$vars[1]}2=".rawurlencode($_GET[$vars[1].'2']);
		}
	}

	foreach ($likekeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)>1) {
			$wherearr[] = "{$pre}{$var} LIKE BINARY '%$value%'";
			$urls[] = "$var=".rawurlencode($value);
		}
	}

	return array('wherearr'=>$wherearr, 'urls'=>$urls);
}


//删除用户信息
function deletespace($uids) {
	global $_G, $_SGLOBAL;

	$sqlstr = '';
	$insertsql = '';
	if(is_array($uids)) {
		$sqlstr = simplode($uids);
		$query = DB::query('SELECT uid, username FROM '.tname('members')." WHERE uid IN ($sqlstr)");
		$users = array();
		while ($value = DB::fetch($query)) {
			$users[$value['uid']] = $value;
		}
		$insertsqlarr = array();
		foreach ($uids as $uid) {
			$insertsqlarr[] = "($uid, 'delete', '$_G[timestamp]', '".$users[$uid]['username']."')";
		}
		$insertsql = implode(',', $insertsqlarr);
	} else {
		$sqlstr = '\''.intval($uids).'\'';
		$query = DB::query('SELECT uid, username FROM '.tname('members')." WHERE uid=$sqlstr");
		$user = DB::fetch($query);
		$insertsql = "($uids, 'delete', '$_G[timestamp]', '$user[username]')";
	}

	if($uids) {
		$delfilearr = array();
		$delnewids = $delshopids = array();
		$query = DB::query("SELECT filepath, thumbpath FROM ".tname('attachments')." WHERE uid IN ($sqlstr)");
		while ($value = DB::fetch($query)) {
			$delfilearr[] = $value['filepath'];
			$delfilearr[] = $value['thumbpath'];
		}

		if($delfilearr) {
			foreach ($delfilearr as $delfile) {
				unlink(A_DIR.'/'.$delfile);
			}
		}

		$query = DB::query("SELECT itemid FROM ".tname('shopitems')." WHERE uid IN ($sqlstr)");
		while ($value = DB::fetch($query)) {
			$delshopids[] = $value['itemid'];
		}
		$delshopsql = '';
		if($delshopids) {
			$delshopsql = simplode($delshopids);
			DB::query('DELETE FROM '.tname('shopitems')." WHERE itemid IN ($delshopsql)");
			DB::query('DELETE FROM '.tname('shopmessage')." WHERE itemid IN ($delshopsql)");
		}
		DB::query('DELETE FROM '.tname('attachments')." WHERE uid IN ($sqlstr)");
		DB::query('DELETE FROM '.tname('members')." WHERE uid IN ($sqlstr)");
		DB::query('DELETE FROM '.tname('spacecomments')." WHERE uid IN ($sqlstr)");
	}
}

//获取数目
function getcount($tablename, $wherearr, $get='COUNT(*)') {
	global $_G, $_SGLOBAL;
	if(empty($wherearr)) {
		$wheresql = '1';
	} else {
		$wheresql = $mod = '';
		foreach ($wherearr as $key => $value) {
			$wheresql .= $mod."`$key`='$value'";
			$mod = ' AND ';
		}
	}
	return DB::result(DB::query("SELECT $get FROM ".tname($tablename)." WHERE $wheresql LIMIT 1"), 0);
}

?>