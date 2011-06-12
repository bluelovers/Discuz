<?php

// Systemadmin operation for Discuz! Bank Hack
// Created by LFLY1573


if(!defined('IN_BANKHACK')) {
	exit('Access Denied');
}

loaducenter();
$action = $_G['gp_action'];
if($action=='add') {

} elseif($action=='doadd') {
	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($_G['gp_loginpass']=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error']);
	}
	$var_bankname = dhtmlspecialchars(trim($_G['gp_var_bankname']));
	$var_creator = trim($_G['gp_var_creator']);
	$var_investment = abs(intval(trim($_G['gp_var_investment'])));
	$poundage = abs(intval(trim($_G['gp_poundage'])));
	if($_G['gp_var_bankname']=='') {
		showmessage($bankmsglang['bankname_error']);
	}
	if($var_creator=='') {
		showmessage($bankmsglang['creator_error']);
	}
	$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$var_creator'");
	if(!@$creatorinfo = DB::result_first("SELECT $moneycredits FROM ".DB::table('common_member_count')." WHERE uid='$uid'")) {
		showmessage($bankmsglang['creator_error']);
	}
	if($creatorinfo<$investment+$poundage) {
		showmessage($bankmsglang['creatormoney_error']);
	}
	$tempcurrate = serialize(array(0 => $hackVars['currentaccrual']));
	DB::query("UPDATE ".DB::table('common_member_count')." SET $moneycredits=$moneycredits-$var_investment-$poundage WHERE uid='$_G[uid]'");
	DB::query("INSERT INTO ".DB::table('plugin_banklist')." (bankname,banklogo,creator,opentime,bankstatus,bankadmin,investment,bankroll,deposit,usernum,notice,opencost,currentrate,fixedrate,lendingrate,changetax) VALUES('$var_bankname','','$var_creator','$_G[timestamp]','1','','$var_investment','$var_investment','0','0','$banktmplang[bank_info_msg]','0','$tempcurrate','$hackVars[fixedaccrual]','$hackVars[fixedaccrual]','0')");
	$pmtitle = $banktmplang['pm_addbank_title'];
	eval("\$pmcontent = \"".$banktmplang['pm_addbank_content']."\";");
	sendpm($creatorinfo['uid'], $pmtitle, $pmcontent);
	eval("\$logmsg = \"".$banktmplang['log_addbank']."\";");
	hack_writeBanklog(0, 0, $logmsg, 1);
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=setup");

} elseif($action=='off') {

	$query = DB::query("SELECT id,bankname FROM ".DB::table('plugin_banklist'));
	$banklist = '';
	while($bankdata=DB::fetch($query)) {
		$banklist .= '<option value="'.$bankdata['id'].'">'.$bankdata['bankname'].'</option>';
	}

} elseif($action=='dochange') {

	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($_G['gp_loginpass']=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error']);
	}
	if($bankid==0) {
		showmessage($bankmsglang['selectbank_error']);
	}
	$newcreator = trim($_G['gp_newcreator']);
	if($newcreator=='' || $newcreator==$bankinfo['creator']) {
		showmessage($bankmsglang['creator_error']);
	}
	$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username='$newcreator'");
	if(!@$newcreatorinfo = DB::fetch($query)) {
		showmessage($bankmsglang['creator_error']);
	}
	DB::query("UPDATE ".DB::table('plugin_banklist')." SET creator='$newcreator' WHERE id='$bankid'");
	$pmtitle = $banktmplang['pm_chgcreator_title'];
	eval("\$pmcontent = \"".$banktmplang['pm_chgcreator_content1']."\";");
	sendpm($newcreatorinfo['uid'], $pmtitle, $pmcontent);
	$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username='$bankinfo[creator]'");
	if($creatorinfo = DB::fetch($query)) {
		eval("\$pmcontent = \"".$banktmplang['pm_chgcreator_content2']."\";");
		sendpm($creatorinfo['uid'], $pmtitle, $pmcontent);
	}
	eval("\$logmsg = \"".$banktmplang['log_chgcreator']."\";");
	hack_writeBanklog(0, 0, $logmsg, 1);
	hack_writeBanklog($bankid, 0, $logmsg, 1);
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=setup&action=off");

} elseif($action=='dodelbank') {

	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($loginpass=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error']);
	}
	if($bankid==0) {
		showmessage($bankmsglang['selectbank_error']);
	}
	DB::query("DELETE FROM ".DB::table('plugin_banklist')." WHERE id='$bankid'");
	DB::query("DELETE FROM ".DB::table('plugin_bankoperation')." WHERE bankid='$bankid'");
	DB::query("DELETE FROM ".DB::table('plugin_banklog')." WHERE bankid='$bankid'");
	eval("\$logmsg = \"".$banktmplang['log_delbank']."\";");
	hack_writeBanklog(0, 0, $logmsg, 1);
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=setup&action=off");

} elseif($action=='dodellog') {

	$dellogday = abs(intval(trim($_G['gp_dellogday'])));
	$ucresult = uc_user_login($_G['uid'], $_G['gp_loginpass'], 1);
	if($loginpass=='' || $ucresult[0]<=0) {
		showmessage($bankmsglang['loginpass_error']);
	}
	if($dellogday<30) {
		showmessage($bankmsglang['logdel_error']);
	}
	$deltypesql = (intval($isdelbuylog)==1) ? "AND issystem='0'" : "AND issystem='1'";
	$deltime = $_G['timestamp']-$dellogday*86400;
	DB::query("DELETE FROM ".DB::table('plugin_banklog')." WHERE bankid='0' $deltypesql AND optime<'$deltime'");
	eval("\$logmsg = \"".$banktmplang['log_dellog']."\";");
	hack_writeBanklog(0, 0, $logmsg, 1);
	showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=setup");

} else {
	$bsearchkey = trim($_G['gp_bsearchkey']);
	$bserachtype = intval(trim($_G['gp_bserachtype']));
	$searchsql = "bankid='0'";
	$searchsql .= ($bserachtype==1) ? " AND issystem='0'" : " AND issystem='1'";
	if($bsearchkey!='') $searchsql .= " AND remark LIKE '%$bsearchkey%'";
	$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_banklog')." WHERE $searchsql");
	if($datanum>0) {
		$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&mode=setup&bserachtype=$bserachtype&bsearchkey=".rawurlencode($bsearchkey));
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
}

?>
