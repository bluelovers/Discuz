<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_report.php 14289 2010-10-21 11:32:50Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}
if(!empty($_G['fid'])) {
	$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
	if(submitcheck('reportsubmit')) {
		if($_G['gp_reportids']) {
			foreach($_G['gp_reportids'] as $reportid) {
				if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_report')." WHERE id='$reportid' AND opuid='0'")) {
					$creditchange = '';
					$uid = $_G['gp_reportuids'][$reportid];
					if($uid != $_G['uid']) {
						$msg = !empty($_G['gp_msg'][$reportid]) ? '<br />'.htmlspecialchars($_G['gp_msg'][$reportid]) : '';
						if(!empty($_G['gp_creditsvalue'][$reportid])) {
							$credittag = $_G['gp_creditsvalue'][$reportid] > 0 ? '+' : '';
							$creditchange = '<br />'.lang('forum/misc', 'report_msg_your').$_G['setting']['extcredits'][$curcredits]['title'].'&nbsp;'.$credittag.$_G['gp_creditsvalue'][$reportid];
							updatemembercount($uid, array($curcredits => intval($_G['gp_creditsvalue'][$reportid])), true, 'RPC', $reportid);
						}
						if($creditchange || $msg) {
							notification_add($uid, 'report', 'report_change_credits', array('creditchange' => $creditchange, 'msg' => $msg), 1);
						}
					}
					$opresult = !empty($_G['gp_creditsvalue'][$reportid])? $curcredits."\t".intval($_G['gp_creditsvalue'][$reportid]) : 'ignore';
					DB::query("UPDATE ".DB::table('common_report')." SET opuid='$_G[uid]', opname='$_G[username]', optime='".TIMESTAMP."', opresult='$opresult' WHERE id='$reportid'");
				}
			}
			showmessage('modcp_report_success', "$cpscript?mod=modcp&action=report&fid=$_G[fid]&lpp=$lpp");
		}
	}
	$rewardlist = '';
	$report_reward = unserialize($_G['setting']['report_reward']);
	$offset = abs(ceil(($report_reward['max'] - $report_reward['min']) / 10));
	if($report_reward['max'] > $report_reward['min']) {
		for($vote = $report_reward['max']; $vote >= $report_reward['min']; $vote -= $offset) {
			if($vote != 0) {
				$rewardlist .= $vote ? '<option value="'.$vote.'">'.($vote > 0 ? '+'.$vote : $vote).'</option>' : '';
			} else {
				$rewardlist .= '<option value="0" selected>'.lang('forum/misc', 'report_noreward').'</option>';
			}
		}
	}
	$reportlist = array();
	$lpp = empty($_G['gp_lpp']) ? 20 : intval($_G['gp_lpp']);
	$lpp = min(200, max(5, $lpp));
	$page = max(1, intval($_G['page']));
	$start = ($page - 1) * $lpp;

	$reportcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_report')." WHERE opuid=0 AND fid='$_G[fid]'");
	$query = DB::query("SELECT * FROM ".DB::table('common_report')." WHERE opuid=0 AND fid='$_G[fid]' ORDER BY num DESC, dateline DESC LIMIT $start, $lpp");
	while($row = DB::fetch($query)) {
		$row['dateline'] = dgmdate($row['dateline']);
		$reportlist[] = $row;
	}
	$multipage = multi($reportcount, $lpp, $page, "$cpscript?mod=modcp&action=report&fid=$_G[fid]&lpp=$lpp");
}
?>