<?php

// Bankadmin operation for Discuz! Bank Hack
// Created by LFLY1573

if(!defined('IN_BANKHACK')) {
	exit('Access Denied');
}
$action = trim($_G['gp_action']);
$op = trim($_G['gp_op']);
$loginpass = $_G['gp_loginpass'];
$var_bankname = trim($_G['gp_var_bankname']);
$var_banklogo = trim($_G['gp_var_banklogo']);

loaducenter();
if($action=='dovar') {

	$ucresult = uc_user_login($_G['uid'], $loginpass, 1);
	if($loginpass=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
	}
	$banknamesql = '';
	if($issupbankadmin==1) {
		$var_bankname = dhtmlspecialchars(trim($var_bankname));
		if($var_bankname=='') {
			showmessage($bankmsglang['bankname_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
		}
		$banknamesql = ",bankname='$var_bankname'";
	}
	$var_banklogo = dhtmlspecialchars(trim(preg_match("/^https?:\/\/.+/i", $var_banklogo) ? $var_banklogo : ($var_banklogo ? 'http://'.$var_banklogo : '')));
	if($var_banklogo!='' && !in_array(strtolower(fileext($var_banklogo)), array('gif', 'jpg', 'png'))) {
		showmessage($bankmsglang['banklogo_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
	}
	$var_bankstatus = intval(trim($_G['gp_var_bankstatus']));
	if($var_bankstatus!=1) $var_bankstatus=0;
	$var_bankadmin = dhtmlspecialchars(trim($_G['gp_var_bankadmin']));
	$var_notice = trim($_G['gp_var_notice']);
	$var_opencost = abs(intval(trim($_G['gp_var_opencost'])));
	$var_currentrate = dhtmlspecialchars(trim($_G['gp_var_currentrate']));
	if($var_currentrate=='') {
		showmessage($bankmsglang['bankrate_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
	} else {
		$currentarray = array();
		foreach(explode("\n", $var_currentrate) as $key => $line) {
			if(strpos($line, ',') === FALSE) {
				showmessage($bankmsglang['bankrate_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
			} else {
				$item = explode(',', $line);
				$curmoneynum = abs(intval(trim($item[0])));
				$curmoneyrate = trim($item[1]);
				if(!is_numeric($curmoneyrate) || $curmoneyrate<0) {
					showmessage($bankmsglang['bankrate_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
				}
				$currentarray[$curmoneynum] = $curmoneyrate;
			}
		}
		ksort($currentarray);
		$currentarray[0] = reset($currentarray);
		ksort($currentarray);
		$var_currentrate = serialize($currentarray);
	}
	if(!is_numeric($_G['gp_var_fixedrate']) || !is_numeric($_G['gp_var_lendingrate']) || !is_numeric($_G['gp_var_changetax'])) {
		showmessage($bankmsglang['bankrate_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=var", array(), array('alert'=>'error'));
	} else {
		$var_fixedrate = abs($_G['gp_var_fixedrate']);
		$var_lendingrate = abs($_G['gp_var_lendingrate']);
		$var_changetax = abs($_G['gp_var_changetax']);
	}
	DB::query("UPDATE ".DB::table('plugin_banklist')." SET banklogo='$var_banklogo',bankstatus='$var_bankstatus',bankadmin='$var_bankadmin',notice='$var_notice',opencost='$var_opencost',currentrate='$var_currentrate',fixedrate='$var_fixedrate',lendingrate='$var_lendingrate',changetax='$var_changetax'{$banknamesql} WHERE id='$bankid'");
	eval("\$logmsg = \"".$banktmplang['log_editbankset']."\";");
	hack_writeBanklog($bankid, 0, $logmsg, 1);
	showmessage($bankmsglang['bankset_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid");

} elseif($action=='var') {

	$bankinfo['currentrateset'] = '';
	foreach($bankinfo['currentrate'] as $key => $value) {
		if($bankinfo['currentrateset']!='') $bankinfo['currentrateset'] .= "\n";
		$bankinfo['currentrateset'] .= $key.','.$value;
	}

} elseif($action=='acc') {

	$datanum = $bankinfo['usernum'];
	if($datanum>0) {
		$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc");
		$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND optype='0' ORDER BY id DESC LIMIT $start_limit, $pernum");
		$rowcolor = 1;
		$datalist = array();
		while($datashow = DB::fetch($query)) {
			$datashow['tr'] = $rowcolor++;
			$datashow['status'] = ($datashow['opstatus']==1) ? $banktmplang['mybank_status_no'] : $banktmplang['mybank_status_yes'];
			$datashow['opentimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['endtime'] + $_G['setting']['timeoffset'] * 3600);
			$datalist[] = $datashow;
		}
	}

} elseif($action=='showacc') {

	$buser = trim($_G['gp_buser']);
	if($buser=='') {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc", array(), array('alert'=>'error'));
	}
	$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND username='$buser' ORDER BY optype");
	$buserstatus = 0;
	$isdelaylen = 0;
	$rowcolor = 1;
	$buercurinfo = array();
	$buerfixinfo = array();
	$buerleninfo = array();
	while($datashow = DB::fetch($query)) {
		$datashow['tr'] = $rowcolor++;
		$datashow['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['begintime'] + $_G['setting']['timeoffset'] * 3600);
		if($datashow['optype']==2 && $datashow['opstatus']==0) {
		} else {
			$datashow['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['endtime'] + $_G['setting']['timeoffset'] * 3600);
		}
		if($datashow['optype']==0) {
			$buserstatus = ($datashow['opstatus']==1) ? 2 : 1;
			$buercurinfo = $datashow;
		} elseif($datashow['optype']==1) {
			$datashow['rate'] = $datashow['extchar']*1000;
			$buerfixinfo[] = $datashow;
		} elseif($datashow['optype']==2) {
			$datashow['rate'] = $datashow['extchar']*1000;
			if($isdelaylen==0 && $datashow['opstatus']==1 && $datashow['endtime']<$timestamp) {
				$isdelaylen = 1;
			}
			$buerleninfo[] = $datashow;
		}
	}
	if($buserstatus==0) {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc", array(), array('alert'=>'error'));
	}

} elseif($action=='douser') {

	$buser = trim($_G['gp_buser']);
	if($buser=='') {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc", array(), array('alert'=>'error'));
	}
	$query = DB::query("SELECT uid,opstatus,extchar FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND username='$buser' AND optype='0'");
	if(!$buserinfo = DB::fetch($query)) {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc", array(), array('alert'=>'error'));
	}
	if($op=='cgs') {
		$s = (abs(intval(trim($_G['gp_s'])))==1) ? 1 : 0;//i am not sure if here should like this
		if($s==1) {
			$chgstatus = $banktmplang['mybank_status_no'];
			$moresql = '';
		} else {
			$chgstatus = $banktmplang['mybank_status_yes'];
			$moresql = ",begintime='$timestamp'";
		}
		DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opstatus='$s'{$moresql} WHERE bankid='$bankid' AND uid='$buserinfo[uid]' AND optype='0'");
		$pmtitle = $banktmplang['pm_acc_title'];
		eval("\$pmcontent = \"".$banktmplang['pm_accstatus_content']."\";");
		sendpm($buserinfo['uid'], $pmtitle, $pmcontent);
		eval("\$logmsg = \"".$banktmplang['log_accstatus']."\";");
		hack_writeBanklog($bankid, 0, $logmsg, 1, $buser);
		showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=showacc&buser=$buser");
	} elseif($op=='cgp') {
		$newbankpass = random(6);
		$md5newpass = md5($newbankpass);
		DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET extchar='$md5newpass' WHERE bankid='$bankid' AND uid='$buserinfo[uid]' AND optype='0'");
		$pmtitle = $banktmplang['pm_acc_title'];
		eval("\$pmcontent = \"".$banktmplang['pm_accpass_content']."\";");
		sendpm($buserinfo['uid'], $pmtitle, $pmcontent);
		eval("\$logmsg = \"".$banktmplang['log_accpass']."\";");
		hack_writeBanklog($bankid, 0, $logmsg, 1, $buser);
		showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=showacc&buser=$buser");
	} else {
		$noid = abs(intval(trim($_G['gp_noid'])));
		$query = DB::query("SELECT opnum FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$buserinfo[uid]' AND optype='2' AND opstatus='1' AND endtime<'{$_G[timestamp]}'");
		if(!$buleninfo = DB::fetch($query)) {
			showmessage($bankmsglang['var_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc", array(), array('alert'=>'error'));
		}
		$query = DB::query("SELECT opnum FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND uid='$buserinfo[uid]' AND optype='1'");
		if(!$bufixinfo = DB::fetch($query)) {
			showmessage($bankmsglang['var_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=acc", array(), array('alert'=>'error'));
		}
		$banknum = $bufixinfo['opnum'];
		DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum=opnum+$banknum,begintime='{$_G[timestamp]}' WHERE bankid='$bankid' AND uid='$buserinfo[uid]' AND optype='0'");
		DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
		hack_updateDeposit($buserinfo['uid']);
		$pmtitle = $banktmplang['pm_acc_title'];
		eval("\$pmcontent = \"".$banktmplang['pm_acctocur_content']."\";");
		sendpm($buserinfo['uid'], $pmtitle, $pmcontent);

		eval("\$logmsg = \"".$banktmplang['log_acctocur']."\";");
		hack_writeBanklog($bankid, $banknum, $logmsg, 1, $buser);
		showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=showacc&buser=$buser");
	}

} elseif($action=='lok') {

	if($op!='') {
		$noid = abs(intval(trim($_G['gp_noid'])));
		$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND optype='2' AND opstatus='0'");
		if(!$buleninfo = DB::fetch($query)) {
			showmessage($bankmsglang['var_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lok", array(), array('alert'=>'error'));
		}
		$banknum = $buleninfo['opnum'];
		if($op=='y') {
			if($bankinfo['bankroll']<$banknum) {
				showmessage($bankmsglang['bankroll_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lok", array(), array('alert'=>'error'));
			}
			$getendtime = $_G['timestamp']+$buleninfo['endtime']*86400;
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$banknum WHERE id='$bankid'");
			DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opstatus='1',begintime='{$_G[timestamp]}',endtime='$getendtime' WHERE id='$noid'");
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits+$banknum WHERE uid='$buleninfo[uid]'");
			$pmtitle = $banktmplang['pm_lencheck_title'];
			eval("\$pmcontent = \"".$banktmplang['pm_lencheckyes_content']."\";");
			sendpm($buleninfo['uid'], $pmtitle, $pmcontent);
			eval("\$logmsg = \"".$banktmplang['log_lencheckyes']."\";");
			hack_writeBanklog($bankid, $banknum, $logmsg, 1, $buleninfo['username']);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lok");
		} else {
			DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
			$pmtitle = $banktmplang['pm_lencheck_title'];
			eval("\$pmcontent = \"".$banktmplang['pm_lencheckno_content']."\";");
			sendpm($buleninfo['uid'], $pmtitle, $pmcontent);
			eval("\$logmsg = \"".$banktmplang['log_lencheckno']."\";");
			hack_writeBanklog($bankid, $banknum, $logmsg, 1, $buleninfo['username']);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lok");
		}
	} else {
		//$query = $db->query("SELECT COUNT(*) FROM {$tablepre}bankoperation WHERE bankid='$bankid' AND optype='2' AND opstatus='0'");
		$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND optype='2' AND opstatus='0'");
		if($datanum>0) {
			$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lok");
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND optype='2' AND opstatus='0' ORDER BY id LIMIT $start_limit, $pernum");
			$rowcolor = 1;
			$datalist = array();
			while($datashow = DB::fetch($query)) {
				$datashow['tr'] = $rowcolor++;
				$datashow['rate'] = $datashow['extchar']*1000;
				$datashow['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['begintime'] + $_G['setting']['timeoffset'] * 3600);
				$datalist[] = $datashow;
			}
		}
	}

} elseif($action=='lof') {

	if($op!='') {
		$noid = abs(intval(trim($_G['gp_noid'])));
		$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND optype='2' AND opstatus='1' AND endtime<'{$_G[timestamp]}'");
		if(!$buleninfo = DB::fetch($query)) {
			showmessage($bankmsglang['var_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
		}
		$banknum = $buleninfo['opnum'];
		$buserid = $buleninfo['uid'];
		$lenaccnum = hack_accrualLen($buleninfo['opnum'], $buleninfo['extchar'], $buleninfo['begintime'], $buleninfo['endtime']);
		if($op=='ok') {
			$query = DB::query("SELECT opnum FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$buserid' AND optype='0'");
			if(!$bucurinfo = DB::fetch($query)) {
				showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
			}
			$query = DB::query("SELECT $moneycredits FROM ".DB::table('common_member_count')." WHERE uid='$buserid'");
			if(!$bumeminfo = DB::fetch($query)) {
				showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
			}
			if($banknum+$lenaccnum>$bucurinfo['opnum']+$bumeminfo[$moneycredits]) {
				showmessage($bankmsglang['lofok_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
			}
			if($bucurinfo['opnum']>=$banknum+$lenaccnum) {
				$getcur = $banknum+$lenaccnum;
				$getmom = 0;
				DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum=opnum-$getcur,begintime='{$_G[timestamp]}' WHERE bankid='$bankid' AND uid='$buserid' AND optype='0'");
				DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll+$getcur,deposit=deposit-$getcur WHERE id='$bankid'");
			} else {
				$getcur = $bucurinfo['opnum'];
				$getmom = $banknum+$lenaccnum-$getcur;
				DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum='0',begintime='{$_G[timestamp]}' WHERE bankid='$bankid' AND uid='$buserid' AND optype='0'");
				DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-$getmom WHERE uid='$buserid'");
				DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll+$banknum+$lenaccnum,deposit=deposit-$getcur WHERE id='$bankid'");
			}
			DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
			hack_updateDeposit($buserid);
			$pmtitle = $banktmplang['pm_lenoff_title'];
			eval("\$pmcontent = \"".$banktmplang['pm_lenoffok_content']."\";");
			sendpm($buserid, $pmtitle, $pmcontent);
			eval("\$logmsg = \"".$banktmplang['log_lenoffok']."\";");
			hack_writeBanklog($bankid, $banknum, $logmsg, 1, $buleninfo['username']);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lof");
		} elseif($op=='up') {
			if($hackVars['lenmsgto']=='') {
				showmessage($bankmsglang['lofup_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
			}
			$item = explode(',', $hackVars['lenmsgto']);
			$pmtouer = '';
			foreach($item as $value) {
				if(trim($value)!='') {
					if($pmtouer!='') $pmtouer .= ',';
					$pmtouer .= "'".addslashes(trim($value))."'";
				}
			}
			if($pmtouer=='') {
				showmessage($bankmsglang['lofup_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
			}
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ($pmtouer)");
			$pmtouid = '';
			while($pmtolist = DB::fetch($query)) {
				if($pmtouid!='') $pmtouid .= ',';
				$pmtouid .= $pmtolist['uid'];
			}
			
			if($pmtouid=='') {
				showmessage($bankmsglang['lofup_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
			}
			$pmtitle = $banktmplang['pm_lenoff_title'];
			eval("\$pmcontent = \"".$banktmplang['pm_lenoffup_content']."\";");
			sendpm($pmtouid, $pmtitle, $pmcontent);
			eval("\$logmsg = \"".$banktmplang['log_lenoffup']."\";");
			hack_writeBanklog($bankid, $banknum, $logmsg, 1, $buleninfo['username']);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lof");
		} else {
			showmessage('undefined_action', "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
		}
	} else {

		$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND optype='2' AND opstatus='1' AND endtime<'{$_G[timestamp]}'");
		if($datanum>0) {
			$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lof");
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND optype='2' AND opstatus='1' AND endtime<'{$_G[timestamp]}' ORDER BY id LIMIT $start_limit, $pernum");
			$rowcolor = 1;
			$datalist = array();
			while($datashow = DB::fetch($query)) {
				$datashow['tr'] = $rowcolor++;
				$datashow['rate'] = $datashow['extchar']*1000;
				$datashow['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['begintime'] + $_G['setting']['timeoffset'] * 3600);
				$datashow['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['endtime'] + $_G['setting']['timeoffset'] * 3600);
				$datashow['accrual'] = hack_accrualLen($datashow['opnum'], $datashow['extchar'], $datashow['begintime'], $datashow['endtime']);
				$datalist[] = $datashow;
			}
		}
	}

} elseif($action=='lma') {

	if($issupbankadmin!=1) {
		showmessage('undefined_action', "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
	}
	$noid = abs(intval(trim($_G['gp_noid'])));
	$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND optype='2' AND opstatus='1' AND endtime<'{$_G[timestamp]}'");
	if(!$buleninfo = DB::fetch($query)) {
		showmessage($bankmsglang['var_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
	}
	$banknum = $buleninfo['opnum'];
	$buserid = $buleninfo['uid'];
	$lenaccnum = hack_accrualLen($buleninfo['opnum'], $buleninfo['extchar'], $buleninfo['begintime'], $buleninfo['endtime']);
	$query = DB::query("SELECT * FROM ".DB::table('common_member_count')." WHERE uid='$buserid'");
	if(!$bumeminfo = DB::fetch($query)) {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
	}
	if($op=='do') {
		$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
		if($loginpass=='' || $ucresult[0]<=0) {
			showmessage($bankmsglang['loginpass_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=lof", array(), array('alert'=>'error'));
		}
		$gb = intval($_G['gp_gb']);
		$sql = $comma = $cinfo = '';
		$extcreditsnew = $_G['gp_extcreditsnew'];
		if(is_array($extcreditsnew)) {
			foreach($extcreditsnew as $id => $value) {
				if($id<=8 && $bumeminfo['extcredits'.$id]>($value = intval($value))) {
					$tempdecnum = $bumeminfo['extcredits'.$id]-$value;
					$cinfo .= ' '.$extcredits[$id]['title'].': -'.$tempdecnum.$extcredits[$id]['unit'];
					$sql .= $comma."extcredits$id='$value'";
					$comma = ',';
				}
			}
		}
		if($gb==1) {
			$bankhavenum = $banknum;
		} elseif($gb==2) {
			$bankhavenum = $banknum+$lenaccnum;
		} else {
			$bankhavenum = 0;
		}
		if($sql!='') {
			DB::query("UPDATE ".DB::table('common_member_count')." SET $sql WHERE uid='$buserid'");
		}
		if($bankhavenum>0) {
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll+$bankhavenum WHERE id='$bankid'");
		}
		DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
		hack_updateDeposit($buserid);
		$pmtitle = $banktmplang['pm_lenoff_title'];
		eval("\$pmcontent = \"".$banktmplang['pm_lenoffdo_content']."\";");
		sendpm($buserid, $pmtitle, $pmcontent);
		eval("\$logmsg = \"".$banktmplang['log_lenoffdo']."\";");
		hack_writeBanklog($bankid, $banknum, $logmsg, 1, $buleninfo['username']);
		showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=lof");
	} else {
		$buleninfo['rate'] = $buleninfo['extchar']*1000;
		$buleninfo['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $buleninfo['begintime'] + $_G['setting']['timeoffset'] * 3600);
		$buleninfo['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $buleninfo['endtime'] + $_G['setting']['timeoffset'] * 3600);
		$creditsshow = '';
		for($i=1; $i<=8; $i++) {
			if(isset($extcredits[$i])) {
				$creditsshow .= '<p>'.$extcredits[$i]['title'].': <input type="text" class="px" size="10" name="extcreditsnew['.$i.']" value="'.$bumeminfo['extcredits'.$i].'"> '.$extcredits[$i]['unit'].'</p>';
			}
		}
	}

} elseif($action=='dofin') {

	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($loginpass=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	$banknum = intval($_G['gp_banknum']);//, "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	if($banknum<=0) {
		showmessage($bankmsglang['banknum_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	if($mycash<$banknum) {
		showmessage($bankmsglang['moneynum_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-$banknum WHERE uid='$_G[uid]'");
	DB::query("UPDATE ".DB::table('plugin_banklist')." SET investment=investment+$banknum,bankroll=bankroll+$banknum WHERE id='$bankid'");
	eval("\$logmsg = \"".$banktmplang['log_fin']."\";");
	hack_writeBanklog($bankid, $banknum, $logmsg, 1);
	if($discuz_user!=$bankinfo['creator']) {
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username='$bankinfo[creator]'");
		if($creatorinfo = DB::fetch($query)) {
			$pmtitle = $banktmplang['pm_fin_title'];
			eval("\$pmcontent = \"".$banktmplang['pm_fin_content']."\";");
			sendpm($creatorinfo['uid'], $pmtitle, $pmcontent);
		}
	}
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=fin");

} elseif($action=='dogetfin') {

	if($isbankadmin!=2) {
		showmessage('undefined_action', "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($loginpass=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	$banknum = intval($_G['gp_banknum']);
	if($banknum<=0 || $bankinfo['bankroll']<$banknum) {
		showmessage($bankmsglang['banknum_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	$touser = trim($_G['gp_touser']);
	if($touser=='') {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username='$touser'");
	if(!@$touserinfo = DB::fetch($query)) {
		showmessage($bankmsglang['user_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=fin", array(), array('alert'=>'error'));
	}
	DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$banknum WHERE id='$bankid'");
	DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits+$banknum WHERE uid='$touserinfo[uid]'");
	eval("\$logmsg = \"".$banktmplang['log_fin_get']."\";");
	hack_writeBanklog($bankid, $banknum, $logmsg, 1, $touser);
	if($discuz_user!=$touser) {
		$pmtitle = $banktmplang['pm_fin_get_title'];
		eval("\$pmcontent = \"".$banktmplang['pm_fin_get_content']."\";");
		sendpm($touserinfo['uid'], $pmtitle, $pmcontent);
	}
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=fin");

} elseif($action=='log') {

	$bsearchkey = trim($_G['gp_bsearchkey']);
	$bserachtype = intval(trim($_G['gp_bserachtype']));
	$bserachnum = abs(intval(trim($_G['gp_bserachnum'])));
	$searchsql = "bankid='$bankid'";
	if($bserachtype==1) $searchsql .= " AND issystem='1'";
	if($bserachnum>0) $searchsql .= " AND opnum>='$bserachnum'";
	if($bsearchkey!='') $searchsql .= " AND remark LIKE '%$bsearchkey%'";
	$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_banklog')." WHERE $searchsql");
	if($datanum>0) {
		$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&mode=admin&action=log&bankid=$bankid&bserachtype=$bserachtype&bserachnum=$bserachnum&bsearchkey=".rawurlencode($bsearchkey));
		$query = DB::query("SELECT * FROM ".DB::table('plugin_banklog')." WHERE $searchsql ORDER BY id DESC LIMIT $start_limit, $pernum");
		$rowcolor = 1;
		$datalist = array();
		while($datashow = DB::fetch($query)) {
			$datashow['tr'] = $rowcolor++;
			$datashow['optimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['optime'] + $_G['setting']['timeoffset'] * 3600);
			if($bsearchkey!='') $datashow['remark'] = str_replace("$bsearchkey", "<span style=\"font-weight:bold;color:red\">$bsearchkey</span>", $datashow['remark']);
			$datalist[] = $datashow;
		}
	}

} elseif($action=='dodellog') {
	
	$dellogday = abs(intval(trim($_G['gp_dellogday'])));
	$isdeladmlog = abs(intval(trim($_G['gp_isdeladmlog'])));
	if($isdeladmlog!=1 || $isbankadmin!=2) $isdeladmlog=0;
	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($loginpass=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=log", array(), array('alert'=>'error'));
	}
	if($dellogday<30) {
		showmessage($bankmsglang['logdel_error'], "plugin.php?id=bank_ane:bank&bankid=$bankid&mode=admin&action=log", array(), array('alert'=>'error'));
	}
	$deltime = $_G['timestamp']-$dellogday*86400;
	$delmoresql = ($isdeladmlog==0) ? " AND issystem='0'" : '';
	DB::query("DELETE FROM ".DB::table('plugn_banklog')." WHERE bankid='$bankid' AND optime<'$deltime'$delmoresql");
	eval("\$logmsg = \"".$banktmplang['log_dellog']."\";");
	hack_writeBanklog($bankid, 0, $logmsg, 1);
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=admin&bankid=$bankid&action=log");

} else {
}

?>
