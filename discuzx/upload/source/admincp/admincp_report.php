<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_misc.php 7390 2010-04-07 05:53:38Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$operation = $operation ? $operation : 'newreport';
if(submitcheck('delsubmit')) {
	if(!empty($_G['gp_reportids'])) {
		DB::query("DELETE FROM ".DB::table('common_report')." WHERE id IN(".dimplode($_G['gp_reportids']).")");
	}
}
if(submitcheck('resolvesubmit')) {
	if(!empty($_G['gp_reportids'])) {
		$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
		foreach($_G['gp_reportids'] as $id) {
			$creditchange = '';
			$opresult = !empty($_G['gp_creditsvalue'][$id])? $curcredits."\t".intval($_G['gp_creditsvalue'][$id]) : 'ignore';
			$uid = $_G['gp_reportuids'][$id];
			$msg = !empty($_G['gp_msg'][$id]) ? '<br />'.htmlspecialchars($_G['gp_msg'][$id]) : '';
			if(!empty($_G['gp_creditsvalue'][$id])) {
				$credittag = $_G['gp_creditsvalue'][$id] > 0 ? '+' : '';
				$creditchange = '<br />'.cplang('report_your').$_G['setting']['extcredits'][$curcredits]['title'].'&nbsp;'.$credittag.$_G['gp_creditsvalue'][$id];
				updatemembercount($uid, array($curcredits => $_G['gp_creditsvalue'][$id]), true, 'RPC', $id);
			}
			if($uid != $_G['uid'] && ($creditchange || $msg)) {
				notification_add($uid, 'report', 'report_change_credits', array('creditchange' => $creditchange, 'msg' => $msg), 1);
			}
			DB::query("UPDATE ".DB::table('common_report')." SET opuid='$_G[uid]', opname='$_G[username]', optime='".TIMESTAMP."', opresult='$opresult' WHERE id='$id'");
		}
		cpmsg('report_resolve_succeed', 'action=report', 'succeed');
	}
}
if(submitcheck('receivesubmit') && $admincp->isfounder) {
	$supmoderator = $_G['gp_supmoderator'];
	$adminuser = $_G['gp_adminuser'];
	$report_receive = serialize(array('adminuser' => $adminuser, 'supmoderator' => $supmoderator));
	DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('report_receive', '$report_receive')");
	updatecache('setting');
	cpmsg('report_receive_succeed', 'action=report&operation=receiveuser', 'succeed');
}
shownav('topic', 'nav_report');
$lpp = empty($_G['gp_lpp']) ? 20 : $_G['gp_lpp'];
$start = ($page - 1) * $lpp;
if($operation == 'newreport') {
	showsubmenu('nav_report', array(
		array('report_newreport', 'report', 1),
		array('report_resolved', 'report&operation=resolved', 0),
		array('report_receiveuser', 'report&operation=receiveuser', 0)
	));
	showtips('report_tips');
	showformheader('report&operation=newreport');
	showtableheader();
	$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
	$report_reward = unserialize($_G['setting']['report_reward']);
	$offset = abs(ceil(($report_reward['max'] - $report_reward['min']) / 10));
	if($report_reward['max'] > $report_reward['min']) {
		for($vote = $report_reward['max']; $vote >= $report_reward['min']; $vote -= $offset) {
			if($vote != 0) {
				$rewardlist .= $vote ? '<option value="'.$vote.'">'.($vote > 0 ? '+'.$vote : $vote).'</option>' : '';
			} else {
				$rewardlist .= '<option value="0" selected>'.cplang('report_newreport_no_operate').'</option>';
			}
		}
	}
	showsubtitle(array('', 'report_detail', 'report_user', ($report_reward['max'] != $report_reward['min'] ? 'operation' : '')));
	$reportcount = DB::result_first("SELECT count(*) FROM ".DB::table('common_report')." WHERE opuid=0");
	$query = DB::query("SELECT * FROM ".DB::table('common_report')." WHERE opuid=0 ORDER BY num DESC, dateline DESC LIMIT $start, $lpp");
	while($row = DB::fetch($query)) {
		showtablerow('', array('class="td25"', 'class="td28"', '', ''), array(
			'<input type="checkbox" class="checkbox" name="reportids[]" value="'.$row['id'].'" />',
			'<b>'.cplang('report_newreport_url').'</b><a href="'.$row['url'].'" target="_blank">'.$row['url'].'</a><br \><b>'.cplang('report_newreport_time').'</b>'.dgmdate($row['dateline']).'<br><b>'.cplang('report_newreport_message').'</b><br>'.$row['message'],
			'<a href="home.php?mod=space&uid='.$row['uid'].'">'.$row['username'].'</a><input type="hidden" name="reportuids['.$row['id'].']" value="'.$row['uid'].'">',
			($report_reward['max'] != $report_reward['min'] ? $_G['setting']['extcredits'][$curcredits]['title'].':&nbsp;<select name="creditsvalue['.$row['id'].']">'.$rewardlist.'</select><br /><br />'.cplang('report_note').':&nbsp;<input type="text" name="msg['.$row['id'].']" value="">' : '')
		));
	}
	$multipage = multi($reportcount, $lpp, $page, ADMINSCRIPT."?action=report&lpp=$lpp", 0, 3);

	showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'reportids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;<input type="submit" class="btn" name="delsubmit" value="'.$lang['delete'].'" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="btn" name="resolvesubmit" value="'.cplang('report_newreport_resolve').'" />', $multipage);
	showtablefooter();
	showformfooter();
} elseif($operation == 'resolved') {
	showsubmenu('nav_report', array(
		array('report_newreport', 'report', 0),
		array('report_resolved', 'report&operation=resolved', 1),
		array('report_receiveuser', 'report&operation=receiveuser', 0)
	));
	showformheader('report&operation=resolved');
	showtableheader();
	showsubtitle(array('', 'report_detail', 'report_optuser', 'report_opttime'));
	$reportcount = DB::result_first("SELECT count(*) FROM ".DB::table('common_report')." WHERE opuid>0");
	$query = DB::query("SELECT * FROM ".DB::table('common_report')." WHERE opuid>0 ORDER BY optime DESC LIMIT $start, $lpp");
	while($row = DB::fetch($query)) {
		if($row['opresult'] == 'ignore') {
			$opresult = cplang('report_newreport_no_operate');
		} else {
			$row['opresult'] = explode("\t", $row['opresult']);
			if($row['opresult'][1] > 0) {
				$row[opresult][1] = '+'.$row[opresult][1];
			}
			$opresult = $_G['setting']['extcredits'][$row[opresult][0]]['title'].'&nbsp;'.$row[opresult][1];
		}
		showtablerow('', array('class="td25"', 'class="td28"', '', '', 'class="td26"'), array(
			'<input type="checkbox" class="checkbox" name="reportids[]" value="'.$row['id'].'" />',
			'<b>'.cplang('report_newreport_url').'</b><a href="'.$row['url'].'" target="_blank">'.$row['url'].'</a><br><b>'.cplang('report_newreport_time').'</b>'.dgmdate($row['dateline']).'<br><b>'.cplang('report_newreport_message').'</b>: '.$row['message'].'<br \><b>'.cplang('report_resolved_result').'</b>'.$opresult,
			$row['opname'],
			date('y-m-d H:i', $row['optime'])
		));
	}
	$multipage = multi($reportcount, $lpp, $page, ADMINSCRIPT."?action=report&operation=resolved&lpp=$lpp", 0, 3);
	showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'reportids\')" /><label for="chkall">'.cplang('del').'</label>&nbsp;&nbsp;<input type="submit" class="btn" name="delsubmit" value="'.$lang['delete'].'" />', $multipage);
	showtablefooter();
	showformfooter();
} elseif($operation == 'receiveuser') {
	showsubmenu('nav_report', array(
		array('report_newreport', 'report', 0),
		array('report_resolved', 'report&operation=resolved', 0),
		array('report_receiveuser', 'report&operation=receiveuser', 1)
	));
	if(!$admincp->isfounder) {
		cpmsg('report_need_founder');
	}
	$report_receive = unserialize($_G['setting']['report_receive']);
	showformheader('report&operation=receiveuser');
	showtips('report_receive_tips');
	$users = array();
	$founders = $_G['config']['admincp']['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes($_G['config']['admincp']['founder']))) : array();
	if($founders) {
		$founderexists = true;
		$fuid = $fuser = array();
		foreach($founders as $founder) {
			if(is_numeric($founder)) {
				$fuid[] = $founder;
			} else {
				$fuser[] = $founder;
			}
		}
		$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE ".($fuid ? "uid IN (".dimplode($fuid).")" : '0')." OR ".($fuser ? "username IN (".dimplode($fuser).")" : '0'));
		while($founder = DB::fetch($query)) {
			$users[$founder['uid']] = $founder['username'];
		}
	}
	$query = DB::query("SELECT uid FROM ".DB::table('common_admincp_member')." am LEFT JOIN ".DB::table('common_admincp_perm')." ap ON am.cpgroupid=ap.cpgroupid where am.cpgroupid=0 OR ap.perm='report'");
	while($user = DB::fetch($query)) {
		if(empty($users[$user[uid]])) {
			$newuids[] = $user['uid'];
		}
	}
	if($newuids) {
		$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($newuids).")");
		while($user = DB::fetch($query)) {
			$users[$user['uid']] = $user['username'];
		}
	}
	$supmoderator = array();
	$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE adminid='2'");
	while($row = DB::fetch($query)) {
		if(empty($users[$row[uid]])) {
			$supmoderator[$row['uid']] = $row['username'];
		}
	}
	showtableheader('<input type="checkbox" name="chkall_admin" id="chkall_admin" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'adminuser\', \'chkall_admin\')" />'.cplang('usergroups_system_1'));
	foreach($users as $uid => $username) {
		$username = trim($username);
		if(empty($username) || empty($uid)) continue;
		$checked = in_array($uid, $report_receive['adminuser']) ? 'checked' : '';
		showtablerow('style="height:20px;width:50px;"', array('class="td25"'), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"adminuser[]\" value=\"$uid\" $checked>",
			"<a href=\"home.php?mod=space&uid=$uid\" target=\"_blank\">$username</a>"
			));
	}
	showtablefooter();

	showtableheader('<input type="checkbox" name="chkall_sup" id="chkall_sup" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'supmoderator\', \'chkall_sup\')" />'.cplang('usergroups_system_2'));
	foreach($supmoderator as $uid => $username) {
		$username = trim($username);
		if(empty($username) || empty($uid)) continue;
		$checked = in_array($uid, $report_receive['supmoderator']) ? 'checked' : '';
		showtablerow('style="height:20px;width:50px;"', array('class="td25"'), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"supmoderator[]\" value=\"$uid]\" $checked>",
			"<a href=\"home.php?mod=space&uid=$uid\" target=\"_blank\">$username</a>"
			));
	}
	showsubmit('', '', '', '<input type="submit" class="btn" name="receivesubmit" value="'.$lang['submit'].'" />');
	showtablefooter();
	showformfooter();
}