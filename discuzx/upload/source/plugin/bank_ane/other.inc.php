<?php

// other operation for Discuz! Bank Hack
// Created by LFLY1573

if(!defined('IN_BANKHACK')) {
	exit('Access Denied');
}

$action = trim($_G['gp_action']);
$op = trim($_G['gp_op']);
if($action=='buy') {

	$buycredits = array();
	for($i=1; $i<=8; $i++) {
		$buycredits[$i]['sell'] = 0;
		$buycredits[$i]['buy'] = 0;
	}
	$isabled = 0;
	foreach(explode("\n", $hackVars['buycredits']) as $line) {
		$item = explode(',', trim($line));
		if(count($item)==3) {
			$tempcreditsnum = intval(substr($item[0], -1));
			if(isset($extcredits[$tempcreditsnum]['title']) && $tempcreditsnum!=$moneycreditsnum && $tempcreditsnum!=$depositcreditsnum) {
				$buycredits[$tempcreditsnum]['sell'] = abs(intval(trim($item[1])));
				$buycredits[$tempcreditsnum]['buy'] = abs(intval(trim($item[2])));
				if($isabled==0 && ($buycredits[$tempcreditsnum]['sell']>0 || $buycredits[$tempcreditsnum]['buy']>0)) {
					$isabled = 1;
				}
			}
		}
	}
	if(!$isabled) {
		showmessage($bankmsglang['buy_close']);
	}

	$buycreditsnum = intval($_G['gp_buycreditsnum']);
	if($buycreditsnum<1 || $buycreditsnum>8) $buycreditsnum = 0;
	$op = (in_array($op, array('sell', 'buy'))) ? $op : '';
	if($buycreditsnum!=0) {
		$docredit = 'extcredits'.$buycreditsnum;
		if($buycredits[$buycreditsnum]['sell']==0 && $buycredits[$buycreditsnum]['buy']==0) {
			showmessage($bankmsglang['buy_close']);
		}
		if($op!='' && $buycredits[$buycreditsnum][$op]==0) {
			showmessage($bankmsglang['buy_close']);
		}
	}
	if($buycreditsnum!=0 && $op!='') {
		$banknum = intval(trim($_G['gp_banknum']));
		if($banknum<=0) {
			showmessage($bankmsglang['buynum_close']);
		}
		$paynum = $banknum*$buycredits[$buycreditsnum][$op];
		if($op=='sell' && $banknum>$_G['member'][$docredit]) {
			showmessage($bankmsglang['buynum_close']);
		}
		if($op=='buy' && $paynum>$mycash) {
			showmessage($bankmsglang['moneynum_error']);
		}
		$dosql = '';
		$doname = '';
		if($op=='sell') {
			$dosql = "$moneycredits=$moneycredits+$paynum,$docredit=$docredit-$banknum";
			$doname = $banktmplang['buy_sell'];
		} else {
			$dosql = "$moneycredits=$moneycredits-$paynum,$docredit=$docredit+$banknum";
			$doname = $banktmplang['buy_buy'];
		}
		DB::query("UPDATE ".DB::table('common_member_count')." SET $dosql WHERE uid='$_G[uid]'");
		$creditname = $extcredits[$buycreditsnum]['title'].'('.$docredit.')';
		$unit = $extcredits[$buycreditsnum]['unit'];
		eval("\$logmsg = \"".$banktmplang['log_buycredits']."\";");
		hack_writeBanklog(0, $banknum, $logmsg, 0);
		showmessage($bankmsglang['action_success'], "plugin.php?id=bank_ane:bank&mode=other&action=buy");
	}

} elseif($action=='buylog') {

	$datanum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('plugin_banklog')." WHERE bankid='0' AND uid='$_G[uid]' AND issystem='0'");
	if($datanum>0) {
		$multipage = multi($datanum, $pernum, $page, "plugin.php?id=bank_ane:bank&mode=other&action=buylog");
		$query = DB::query("SELECT * FROM ".DB::table('plugin_banklog')." WHERE bankid='0' AND uid='$_G[uid]' AND issystem='0' ORDER BY id DESC LIMIT $start_limit, $pernum");
		$rowcolor = 1;
		$datalist = array();
		while($datashow = DB::fetch($query)) {
			$datashow['tr'] = $rowcolor++;
			$datashow['optimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['optime'] + $_G['setting']['timeoffset'] * 3600);
			$datalist[] = $datashow;
		}
	}

} elseif($action=='list') {

	$query = DB::query("SELECT c.uid,m.username,c.$moneycredits FROM ".DB::table('common_member_count')." c,".DB::table('common_member')." m WHERE m.uid=c.uid  ORDER BY c.$moneycredits DESC LIMIT 0,10");
	$cashlist = array();
	while($datashow = DB::fetch($query)) {
		$cashlist[] = $datashow;
	}
	if($depositcredits!='none') {
		$query = DB::query("SELECT c.uid,m.username,c.$depositcredits FROM ".DB::table('common_member_count')." c,".DB::table('common_member')." m WHERE m.uid=c.uid ORDER BY c.$depositcredits DESC LIMIT 0,10");
		$fundlist = array();
		while($datashow = DB::fetch($query)) {
			$fundlist[] = $datashow;
		}
		$query = DB::query("SELECT c.uid,m.username,(c.$moneycredits+c.$depositcredits) AS allmoney FROM ".DB::table('common_member_count')." c,".DB::table('common_member')." m WHERE m.uid=c.uid ORDER BY allmoney DESC LIMIT 0,10");
		$moneylist = array();
		while($datashow = DB::fetch($query)) {
			$moneylist[] = $datashow;
		}
	}
	if($hackVars['onlybankid']==0) {
		$query = DB::query("SELECT id,bankname,(bankroll+deposit) AS allmoney FROM ".DB::table('plugin_banklist')." ORDER BY allmoney DESC LIMIT 0,10");
		$bankmoneylist = array();
		while($bankdata = DB::fetch($query)) {
			$bankmoneylist[] = $bankdata;
		}
		$query = DB::query("SELECT id,bankname,usernum FROM ".DB::table('plugin_banklist')." ORDER BY usernum DESC LIMIT 0,10");
		$bankuserlist = array();
		while($bankdata = DB::fetch($query)) {
			$bankuserlist[] = $bankdata;
		}
	}

} else {

	$query = DB::query("SELECT id,bankname FROM ".DB::table('plugin_banklist'));
	$banklist = array();
	while($bankdata = DB::fetch($query)) {
		$banklist[$bankdata['id']] = $bankdata['bankname'];
	}
	$query = DB::query("SELECT * FROM ".DB::table('plugin_bankoperation')." WHERE uid='$_G[uid]' ORDER BY optype,bankid");
	$rowcolor = 1;
	$buercurinfo = array();
	$buerfixinfo = array();
	$buerleninfo = array();
	while($datashow = DB::fetch($query)) {
		$datashow['tr'] = $rowcolor++;
		$datashow['bankname'] = $banklist[$datashow['bankid']];
		$datashow['begintimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['begintime'] + $_G['setting']['timeoffset'] * 3600);
		if($datashow['optype']==2 && $datashow['opstatus']==0) {
		} else {
			$datashow['endtimeshow'] = gmdate("{$_G[setting][dateformat]} {$_G[setting][timeformat]}", $datashow['endtime'] + $_G['setting']['timeoffset'] * 3600);
		}
		if($datashow['optype']==0) {
			$buercurinfo[] = $datashow;
		} elseif($datashow['optype']==1) {
			$datashow['rate'] = $datashow['extchar']*1000;
			$buerfixinfo[] = $datashow;
		} elseif($datashow['optype']==2) {
			$datashow['rate'] = $datashow['extchar']*1000;
			$buerleninfo[] = $datashow;
		}
	}
	if(count($buercurinfo)==0) {
		showmessage($bankmsglang['no_openbank']);
	}

}
?>
