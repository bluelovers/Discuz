<?php

// Function for Discuz! Bank Hack
// Created by LFLY1573

if(!defined('IN_BANKHACK')) {
	exit('Access Denied');
}

function hack_checkStr($allstr, $curstr) {
	return strstr(",$allstr,", ",$curstr,") ? 1 : 0;
}

function hack_accrualCur($moneynum, $begintime) {
	global $hackVars,$bankinfo,$_G;
	$tempinfo = array();
	$tempinfo['all'] = $tempinfo['bank'] = $tempinfo['system'] = 0;
	$currate = hack_showMyCur($moneynum);
	$daynum = floor(($_G['timestamp']-$begintime)/86400);
	if($moneynum>0 && $daynum>0 && $currate>0) {
		$tempinfo['all'] = floor($moneynum*$currate*$daynum);
		if($hackVars['currentaccrual']>=$currate) {
			$tempinfo['system'] = $tempinfo['all'];
		} elseif($hackVars['currentaccrual']>0) {
			$tempinfo['system'] = floor($moneynum*$hackVars['currentaccrual']*$daynum);
			$tempinfo['bank'] = $tempinfo['all']-$tempinfo['system'];
		} else {
			$tempinfo['bank'] = $tempinfo['all'];
		}
	}
	return $tempinfo;
}

function hack_accrualFix($moneynum, $rate, $begintime, $endtime) {
	global $hackVars,$_G;
	$tempinfo = array();
	$tempinfo['all'] = $tempinfo['bank'] = $tempinfo['system'] = 0;
	if($endtime<=$_G['timestamp']) {
		$daynum = floor(($endtime-$begintime)/86400);
		if($moneynum>0 && $daynum>0 && $rate>0) {
			$tempinfo['all'] = floor($moneynum*$rate*$daynum);
			if($hackVars['fixedaccrual']>=$rate) {
				$tempinfo['system'] = $tempinfo['all'];
			} elseif($hackVars['fixedaccrual']>0) {
				$tempinfo['system'] = floor($moneynum*$hackVars['fixedaccrual']*$daynum);
				$tempinfo['bank'] = $tempinfo['all']-$tempinfo['system'];
			} else {
				$tempinfo['bank'] = $tempinfo['all'];
			}
		}
	}
	return $tempinfo;
}

function hack_accrualLen($moneynum, $rate, $begintime, $endtime) {
	global $_G;
	$tempinfo = 0;
	if($begintime<$_G['timestamp']) {
		$daynum = ceil(($_G['timestamp']-$begintime)/86400);
		$tempinfo = ceil($moneynum*$rate*$daynum);
		if($endtime<$_G['timestamp']) {
			$moredaynum = ceil(($_G['timestamp']-$endtime)/86400);
			$tempinfo = $tempinfo+ceil($moneynum*$rate*$moredaynum);
		}
	}
	return $tempinfo;
}

function hack_getConfig() {
	global $_G;
	//@include DISCUZ_ROOT.'./data/cache/plugin_bank.php';
	return $_G['cache']['plugin']['bank_ane'];
}

function hack_showCurInfo() {
	global $bankinfo,$banktmplang,$moneyname;
	$showinfo = $oldkey = $oldvalue = '';
	$i = 0;
	if(is_array($bankinfo['currentrate'])) {
		$curcount = count($bankinfo['currentrate']);
		foreach($bankinfo['currentrate'] as $key => $value) {
			$i++;
			if($curcount==1) {
				$showinfo = ($value*1000).$banktmplang['thousand_sign'];
			} else {
				if($i>1) {
					$showinfo .= $oldkey.'--'.($key-1).$moneyname.': '.($oldvalue*1000);
					$showinfo .= $banktmplang['thousand_sign'].', ';
				}
				if($i==$curcount) {
					$showinfo .= $key.$moneyname.$banktmplang['upwards'].': '.($value*1000);
					$showinfo .= $banktmplang['thousand_sign'];
				}
				$oldkey = $key;
				$oldvalue = $value;
			}
		}
	}
	return $showinfo;
}

function hack_showMyCur($moneynum) {
	global $bankinfo;
	$temprate = $bankinfo['currentrate'];
	krsort($temprate);
	foreach($temprate as $key => $value) {
		if($moneynum>=$key) {
			return $value;
		}
	}
	return 0;
}

function hack_writeBanklog($bank, $num, $log, $issys = 0, $other = '') {
	global $_G;
	DB::query("INSERT INTO ".DB::table('plugin_banklog')." (uid,username,bankid,issystem,opnum,remark,otheruser,optime,opip) VALUES('$_G[uid]','$_G[username]','$bank','$issys','$num','$log','$other','$_G[timestamp]','$_G[clientip]')");
}

function hack_updateDeposit($intuid = 0) {
	global $_G,$depositcredits;
	if($intuid==0) $intuid = $_G['uid'];
	if($depositcredits!='none') {
		$query = DB::query("SELECT SUM(opnum) FROM ".DB::table('plugin_bankoperation')." WHERE uid='$intuid' AND optype<'2'");
		$datanum = DB::result_first("SELECT SUM(opnum) FROM ".DB::table('plugin_bankoperation')." WHERE uid='$intuid' AND optype<'2'");
		$datanum = intval($datanum);
		DB::query("UPDATE ".DB::table('common_member_count')." SET $depositcredits=$datanum WHERE uid='$intuid'");
	}
}

?>
