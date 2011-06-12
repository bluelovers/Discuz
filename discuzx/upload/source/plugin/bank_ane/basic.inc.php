<?php

// Basic operation for Discuz! Bank Hack
// Created by LFLY1573

if(!defined('IN_BANKHACK')) {
	exit('Access Denied');
}
if($bankid>0) {
	$action = trim($_G['gp_action']);
	$op = trim($_G['gp_op']);
	$mybankstatus = 0;
	$_G['gp_banknum'] = intval($_G['gp_banknum']);
	$_G['gp_daynum'] = intval($_G['gp_daynum']);
	$query = DB::query("SELECT opstatus,opnum,extchar,begintime,endtime FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='0'");
	if($mybankinfo = DB::fetch($query)) {
		$mybankstatus = 1;
		if($mybankinfo['opstatus']==1) $mybankstatus=2;
		$mybankinfo['curtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $mybankinfo['begintime']+$_G['setting']['timeoffset']*3600);
		$mybankinfo['opentimeshow'] = gmdate("{$_G[setting][dateformat]}", $mybankinfo['endtime']+$_G['setting']['timeoffset']*3600);
		$mybankpass = $mybankinfo['extchar'];
	}
	if($action!='' && $action!='open' && $mybankstatus==0) {
		showmessage($bankmsglang['no_open']);
	}
	if($action!='' && $mybankstatus==2) {
		showmessage($bankmsglang['accounts_close']);
	}

	if($action=='open') {

		if($mybankstatus!=0) {
			showmessage('undefined_action');
		}
		if($mycash<$bankinfo['opencost']) {
			showmessage($bankmsglang['moneynum_error']);
		} elseif($bankpass=='' || $_G['gp_bankpass2']=='') {
			showmessage($bankmsglang['pass_error']);
		} elseif($bankpass!=md5($_G['gp_bankpass2'])) {
			showmessage($bankmsglang['pass_twoerror']);
		} else {
			$lognum = $bankinfo['opencost'];
			if($lognum>0) {
				DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-$lognum WHERE uid='$_G[uid]'");
			}
			DB::query("INSERT INTO ".DB::table('plugin_bankoperation')."(uid,username,bankid,optype,opstatus,opnum,extchar,begintime,endtime) VALUES('$_G[uid]','$_G[username]','$bankid','0','0','0','$bankpass','$_G[timestamp]','$_G[timestamp]')");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll+$lognum,usernum=usernum+1 WHERE id='$bankid'");
			eval("\$logmsg = \"".$banktmplang['log_open']."\";");
			hack_writeBanklog($bankid, $lognum, $logmsg);
			showmessage($bankmsglang['open_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid");
		}

	} elseif($action=='cur') {

		if($op=='in') {
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			if($_G['gp_banknum']<=0 || $_G['gp_banknum']>$mycash) {
				showmessage($bankmsglang['banknum_error']);
			}
			$mycuraccrual = hack_accrualCur($mybankinfo['opnum'], $mybankinfo['begintime']);
			if($bankinfo['bankroll']<$mycuraccrual['bank']) {
				showmessage($bankmsglang['bankroll_error']);
			}
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-{$_G[gp_banknum]} WHERE uid='$_G[uid]'");
			DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum=opnum+$mycuraccrual[all]+{$_G[gp_banknum]},begintime='$_G[timestamp]' WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='0'");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$mycuraccrual[bank],deposit=deposit+$mycuraccrual[all]+{$_G[gp_banknum]} WHERE id='$bankid'");
			hack_updateDeposit();
			eval("\$logmsg = \"".$banktmplang['log_curin']."\";");
			hack_writeBanklog($bankid, $_G['gp_banknum'], $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=cur");
		} elseif($op=='out') {
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			if($_G['gp_banknum']<=0 || $_G['gp_banknum']>$mybankinfo['opnum']) {
				showmessage($bankmsglang['banknum_error']);
			}
			$mycuraccrual = hack_accrualCur($mybankinfo['opnum'], $mybankinfo['begintime']);
			if($bankinfo['bankroll']<$mycuraccrual['bank']) {
				showmessage($bankmsglang['bankroll_error']);
			}
			DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum=opnum+$mycuraccrual[all]-{$_G[gp_banknum]},begintime='{$_G[timestamp]}' WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='0'");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$mycuraccrual[bank],deposit=deposit+$mycuraccrual[all]-{$_G[gp_banknum]} WHERE id='$bankid'");
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits+{$_G[gp_banknum]} WHERE uid='$_G[uid]'");
			hack_updateDeposit();
			eval("\$logmsg = \"".$banktmplang['log_curout']."\";");
			hack_writeBanklog($bankid, $_G['gp_banknum'], $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=cur");
		} elseif($op=='ok') {
			$mycuraccrual = hack_accrualCur($mybankinfo['opnum'], $mybankinfo['begintime']);
			if($bankinfo['bankroll']<$mycuraccrual['bank']) {
				showmessage($bankmsglang['bankroll_error']);
			}
			DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum=opnum+$mycuraccrual[all],begintime='$_G[timestamp]' WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='0'");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$mycuraccrual[bank],deposit=deposit+$mycuraccrual[all] WHERE id='$bankid'");
			hack_updateDeposit();
			eval("\$logmsg = \"".$banktmplang['log_curok']."\";");
			hack_writeBanklog($bankid, $_G['gp_banknum'], $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=cur");
		} else {
			$mycurrateshow = (hack_showMyCur($mybankinfo['opnum'])*1000).$banktmplang['thousand_sign'];
			eval("\$banktmplang[cur_myshow] = \"".$banktmplang['cur_myshow']."\";");
			$mycurratenum = hack_accrualCur($mybankinfo['opnum'], $mybankinfo['begintime']);
		}

	} elseif($action=='fix') {

		if($op=='in') {
			$daynum = abs(intval(trim($_G['gp_daynum'])));
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			if($_G['gp_banknum']<=0 || $_G['gp_banknum']>$mycash) {
				showmessage($bankmsglang['banknum_error']);
			}
			if($daynum<30) {
				showmessage($bankmsglang['fixdaynum_error']);
			}
			$endtime = $_G['timestamp']+$_G['gp_daynum']*86400;
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-{$_G[gp_banknum]} WHERE uid='$_G[uid]'");
			DB::query("INSERT INTO ".DB::table('plugin_bankoperation')."(uid,username,bankid,optype,opstatus,opnum,extchar,begintime,endtime) VALUES('$_G[uid]','$_G[username]','$bankid','1','0','$_G[gp_banknum]','$bankinfo[fixedrate]','$_G[timestamp]','$endtime')");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET deposit=deposit+{$_G[gp_banknum]} WHERE id='$bankid'");
			hack_updateDeposit();
			eval("\$logmsg = \"".$banktmplang['log_fixin']."\";");
			hack_writeBanklog($bankid, $_G['gp_banknum'], $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=fix");
		} elseif($op=='out') {
			$noid = abs(intval(trim($_G['gp_noid'])));
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND uid='$_G[uid]' AND optype='1'");
			if(!$fixinfo = DB::fetch($query)) {
				showmessage($bankmsglang['var_error']);
			}
			$myfixaccrual = hack_accrualFix($fixinfo['opnum'], $fixinfo['extchar'], $fixinfo['begintime'], $fixinfo['endtime']);
			if($bankpass=='') {
				$fixinfo['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $fixinfo['begintime'] + $_G['setting']['timeoffset'] * 3600);
				$fixinfo['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $fixinfo['endtime'] + $_G['setting']['timeoffset'] * 3600);
				eval("\$banktmplang[fix_outform] = \"".$banktmplang['fix_outform']."\";");
			} else {
				if($bankpass!=$mybankpass) {
					showmessage($bankmsglang['pass_error']);
				}
				$banknum = $fixinfo['opnum'];
				if($bankinfo['bankroll']<$myfixaccrual['bank']) {
					showmessage($bankmsglang['bankroll_error']);
				}
				DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$myfixaccrual[bank],deposit=deposit-$banknum WHERE id='$bankid'");
				DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits+$banknum+{$myfixaccrual[all]} WHERE uid='$_G[uid]'");
				DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
				hack_updateDeposit();
				eval("\$logmsg = \"".$banktmplang['log_fixout']."\";");
				hack_writeBanklog($bankid, $banknum, $logmsg);
				showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=fix");
			}
		} else {
			eval("\$banktmplang[fix_myshow] = \"".$banktmplang['fix_myshow']."\";");
			$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='1'");
			if($datanum>0) {
				$multipage = multi($datanum, $pernum, $_G['gp_page'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=fix");
				$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='1' ORDER BY id DESC LIMIT $start_limit, $pernum");
				$rowcolor = 1;
				$datalist = array();
				while($datashow = DB::fetch($query)) {
					$datashow['tr'] = $rowcolor++;
					$datashow['rate'] = $datashow['extchar']*1000;
					$datashow['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['begintime'] + $_G['setting']['timeoffset'] * 3600);
					$datashow['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['endtime'] + $_G['setting']['timeoffset'] * 3600);
					$datashow['accrual'] = hack_accrualFix($datashow['opnum'], $datashow['extchar'], $datashow['begintime'], $datashow['endtime']);
					$datalist[] = $datashow;
				}
			}
		}

	} elseif($action=='chg') {

		if($op=='ok') {
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			$mychgaccrual = ceil($_G['gp_banknum']*$bankinfo['changetax']);
			if($_G['gp_banknum']<10 || $_G['gp_banknum']+$mychgaccrual>$mycash) {//ttttttttttttmmmmmmmmmmmmmmmppppppppppppp
				showmessage($bankmsglang['banknum_error']);
			}
			$touser = trim($_G['gp_touser']);
			if($touser=='' || $touser==$_G['username']) {
				showmessage($bankmsglang['user_error']);
			}
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND username='$touser' AND optype='0'");
			if(!@$touserinfo = DB::fetch($query)) {
				showmessage($bankmsglang['user_error']);
			}
			if($touserinfo['opstatus']==1) {
				showmessage($bankmsglang['accounts_toclose']);
			}
			$yourcuraccrual = hack_accrualCur($touserinfo['opnum'], $touserinfo['begintime']);
			if($bankinfo['bankroll']<$yourcuraccrual['bank']) {
				showmessage($bankmsglang['bankroll_error']);
			}
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-{$_G[gp_banknum]}-$mychgaccrual WHERE uid='{$_G[uid]}'");
			DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET opnum=opnum+$yourcuraccrual[all]+{$_G[gp_banknum]},begintime='{$_G[timestamp]}' WHERE bankid='$bankid' AND uid='$touserinfo[uid]' AND optype='0'");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll-$yourcuraccrual[bank]+$mychgaccrual,deposit=deposit+$yourcuraccrual[all]+{$_G[gp_banknum]} WHERE id='$bankid'");
			hack_updateDeposit();
			hack_updateDeposit($touserinfo['uid']);
			eval("\$logmsg = \"".$banktmplang['log_chgok']."\";");
			hack_writeBanklog($bankid, $_G['gp_banknum'], $logmsg, 0, $touser);
			$pmtitle = $banktmplang['pm_chg_title'];
			eval("\$pmcontent = \"".$banktmplang['pm_chg_content']."\";");
			sendpm($touserinfo['uid'], $pmtitle, $pmcontent);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=chg");
		} else {
			eval("\$banktmplang[chg_myshow] = \"".$banktmplang['chg_myshow']."\";");
		}

	} elseif($action=='len') {

		if($op=='try') {
			$daynum = abs(intval(trim($_G['gp_daynum'])));
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			if($_G['gp_banknum']<=0) {
				showmessage($bankmsglang['banknum_error']);
			}
			if($_G['gp_daynum']<=0) {
				showmessage($bankmsglang['lendaynum_error']);
			}
			DB::query("INSERT INTO ".DB::table('plugin_bankoperation')."(uid,username,bankid,optype,opstatus,opnum,extchar,begintime,endtime) VALUES('{$_G[uid]}','{$_G[username]}','$bankid','2','0','{$_G[gp_banknum]}','$bankinfo[lendingrate]','{$_G[timestamp]}','{$_G[gp_daynum]}')");
			eval("\$logmsg = \"".$banktmplang['log_lentry']."\";");
			hack_writeBanklog($bankid, $_G['gp_banknum'], $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=len");
		} elseif($op=='gu') {
			$noid = abs(intval(trim($_G['gp_noid'])));
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND uid='$_G[uid]' AND optype='2' AND opstatus='0'");
			if(!$leninfo = DB::fetch($query)) {
				showmessage($bankmsglang['var_error']);
			}
			DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
			eval("\$logmsg = \"".$banktmplang['log_lengiveup']."\";");
			hack_writeBanklog($bankid, $leninfo['opnum'], $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=len");
		} elseif($op=='gb') {
			$noid = abs(intval(trim($_G['gp_noid'])));
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid' AND bankid='$bankid' AND uid='$_G[uid]' AND optype='2' AND opstatus='1'");
			if(!$leninfo = DB::fetch($query)) {
				showmessage($bankmsglang['var_error']);
			}
			$banknum = $leninfo['opnum'];
			$mylenaccrual = hack_accrualLen($banknum, $leninfo['extchar'], $leninfo['begintime'], $leninfo['endtime']);
			if($mycash<$banknum+$mylenaccrual) {
				showmessage($bankmsglang['moneynum_error']);
			}
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-$banknum-$mylenaccrual WHERE uid='$_G[uid]'");
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET bankroll=bankroll+$banknum+$mylenaccrual WHERE id='$bankid'");
			DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE id='$noid'");
			eval("\$logmsg = \"".$banktmplang['log_lengiveback']."\";");
			hack_writeBanklog($bankid, $banknum, $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=len");
		} else {
			eval("\$banktmplang[len_myshow] = \"".$banktmplang['len_myshow']."\";");
			$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='2' ORDER BY opstatus DESC");
			$rowcolor = 1;
			$datalist = array();
			while($datashow = DB::fetch($query)) {
				$datashow['tr'] = $rowcolor++;
				$datashow['rate'] = $datashow['extchar']*1000;
				$datashow['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['begintime'] + $_G['setting']['timeoffset'] * 3600);
				if($datashow['opstatus']==1) {
					$datashow['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['endtime'] + $_G['setting']['timeoffset'] * 3600);
					if($datashow['endtime']<$_G['timestamp']) {
						$datashow['endtimeshow'] .= ' <span style="color: red">'.$banktmplang['len_outtime'].'</span>';
					}
					$datashow['accrual'] = hack_accrualLen($datashow['opnum'], $datashow['extchar'], $datashow['begintime'], $datashow['endtime']);
				}
				$datalist[] = $datashow;
			}
		}

	} elseif($action=='pas') {

		if($op=='cg') {
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			if($_G['gp_newbankpass']=='' || $_G['gp_newbankpass2']=='' || $_G['gp_newbankpass']!=$_G['gp_newbankpass2']) {
				showmessage($bankmsglang['pass_twoerror']);
			}
			$newbankpass = md5($_G['gp_newbankpass']);
			DB::query("UPDATE ".DB::table('plugin_bankoperation')." SET extchar='$newbankpass' WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype='0'");
			eval("\$logmsg = \"".$banktmplang['log_changepass']."\";");
			hack_writeBanklog($bankid, 0, $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid&action=pas");
		} elseif($op=='cl') {
			if($_G['gp_bankpass']=='' || md5($_G['gp_bankpass'])!=$mybankpass) {
				showmessage($bankmsglang['pass_error']);
			}
			$query = DB::query("SELECT opnum FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$_G[uid]' AND optype>'0'");
			if($myhaveother = DB::fetch($query)) {
				showmessage($bankmsglang['clear_error']);
			}
			$banknum = $mybankinfo['opnum'];
			DB::query("UPDATE ".DB::table('plugin_banklist')." SET deposit=deposit-$banknum,usernum=usernum-1 WHERE id='$bankid'");
			DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits+$banknum WHERE uid='$_G[uid]'");
			DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid' AND uid='$_G[uid]'");
			eval("\$logmsg = \"".$banktmplang['log_clear']."\";");
			hack_writeBanklog($bankid, $banknum, $logmsg);
			showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&bankid=$bankid");
		} else {
		}

	} elseif($action=='log') {

		$show = intval(trim($_G['gp_show']));
		$moresql = ($show==1) ? "otheruser='{$_G[username]}'" : "uid='{$_G[uid]}'";
		$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_banklog')." WHERE bankid='$bankid' AND $moresql AND issystem='0'");
		if($datanum>0) {
			$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&bankid=$bankid&action=log&show=$show");
			$query = DB::query("SELECT * FROM ".DB::table('plugin_banklog')." WHERE bankid='$bankid' AND $moresql AND issystem='0' ORDER BY id DESC LIMIT $start_limit, $pernum");
			$rowcolor = 1;
			$datalist = array();
			while($datashow = DB::fetch($query)) {
				$datashow['tr'] = $rowcolor++;
				$datashow['optimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['optime'] + $_G['setting']['timeoffset'] * 3600);
				$datalist[] = $datashow;
			}
		}

	} else {

	}
} else {
	$query = DB::query("SELECT b.*,p.endtime FROM ".DB::table('plugin_banklist')." b LEFT JOIN ".DB::table('plugin_bankoperation')." p ON p.uid='$_G[uid]' AND p.optype='0' AND b.id=p.bankid");
	$banklist = array();
	while($bankdata=DB::fetch($query)) {
		$bankdata['opentimeshow'] = gmdate("{$_G[setting][dateformat]}", $bankdata['opentime']+$_G['setting']['timeoffset']*3600);
		$bankdata['allmoneynum'] = $bankdata['bankroll']+$bankdata['deposit'];
		$bankdata['endtime'] = intval($bankdata['endtime']);
		$banklist[] = $bankdata;
	}
}

?>
