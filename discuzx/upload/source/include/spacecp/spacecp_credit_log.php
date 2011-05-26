<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_credit_log.php 21696 2011-04-09 02:07:19Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$perpage = 20;
$start = ($page-1)*$perpage;

$gets = array(
	'mod' => 'spacecp',
	'op' => $_G['gp_op'],
	'ac' => 'credit',
	'suboperation' => $_G['gp_suboperation'],
	'exttype' => $_G['gp_exttype'],
	'income' => $_G['gp_income'],
	'starttime' => $_G['gp_starttime'],
	'endtime' => $_G['gp_endtime'],
	'optype' => $_G['gp_optype']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$_G['gp_income'] = intval($_G['gp_income']);
$incomeactives = array($_G['gp_income'] => ' selected="selected"');
$optypes = array('TRC','RTC','RAC','MRC','BGC','RGC','AGC','TFR','RCV','CEC','ECU','SAC','BAC','PRC','RSC','STC','BTC','AFD','UGP','RPC','ACC','RCT','RCA','RCB','BMC','CDC','RKC');
$endunixstr = $beginunixstr = 0;
if($_G['gp_starttime']) {
	$beginunixstr = strtotime($_G['gp_starttime']);
	$_G['gp_starttime'] = dgmdate($beginunixstr, 'Y-m-d');
}
if($_G['gp_endtime']) {
	$endunixstr = strtotime($_G['gp_endtime'].' 23:59:59');
	$_G['gp_endtime'] = dgmdate($endunixstr, 'Y-m-d');
}
if($beginunixstr && $endunixstr && $endunixstr < $beginunixstr) {
	showmessage('start_time_is_greater_than_end_time');
}

if($_G['gp_suboperation'] == 'creditrulelog') {

	$count = DB::result(DB::query("SELECT count(*) FROM ".DB::table('common_credit_rule_log')." WHERE uid='$_G[uid]'"), 0);
	if($count) {
		$query = DB::query("SELECT r.rulename, l.* FROM ".DB::table('common_credit_rule_log')." l LEFT JOIN ".DB::table('common_credit_rule')." r USING(rid) WHERE l.uid='$_G[uid]' ORDER BY l.dateline DESC LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {
			$list[] = $value;
		}
	}

} else {

	loadcache('usergroups');
	$suboperation = 'creditslog';
	$where[] = "uid='$_G[uid]'";
	if($_G['gp_optype'] && in_array($_G['gp_optype'], $optypes)) {
		$where[] = "operation='$_G[gp_optype]'";
	}
	if($beginunixstr) {
		$where[] = "dateline>='$beginunixstr'";
	}
	if($endunixstr) {
		$where[] = "dateline<='$endunixstr'";
	}
	$exttype = intval($_G['gp_exttype']);

	if($exttype && $_G['setting']['extcredits'][$exttype]) {
		$where[] = "extcredits{$exttype}!='0'";
	}
	$income = intval($_G['gp_income']);
	if($income) {
		$incomestr = $income < 0 ? '<' : '>';
		$incomearr = array();
		foreach($_G['setting']['extcredits'] as $id => $arr) {
			$incomearr[] = 'extcredits'.$id.$incomestr."'0'";
		}
		$where[] = '('.implode(' OR ', $incomearr).')';
	}
	$sql = $where ? ' WHERE '.implode(' AND ', $where) : '';
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log').$sql);
	if($count) {
		$aids = $pids = $tids = $taskids = $uids = $loglist = array();
		loadcache(array('magics'));
		$query = DB::query("SELECT * FROM ".DB::table('common_credit_log')." $sql ORDER BY dateline DESC LIMIT $start,$perpage");
		while($log = DB::fetch($query)) {
			$credits = array();
			$havecredit = false;
			$maxid = $minid = 0;
			foreach($_G['setting']['extcredits'] as $id => $credit) {
				if($log['extcredits'.$id]) {
					$havecredit = true;
					$credits[] = $credit['title'].' <span class="'.($log['extcredits'.$id] > 0 ? 'xi1' : 'xg1').'">'.($log['extcredits'.$id] > 0 ? '+' : '').$log['extcredits'.$id].'</span>';
					if($log['operation'] == 'CEC' && !empty($log['extcredits'.$id])) {
						if($log['extcredits'.$id] > 0) {
							$log['maxid'] = $id;
						} elseif($log['extcredits'.$id] < 0) {
							$log['minid'] = $id;
						}
					}

				}
			}
			if(!$havecredit) {
				continue;
			}
			$log['credit'] = implode('<br/>', $credits);
			if(in_array($log['operation'], array('RTC', 'RAC', 'STC', 'BTC', 'ACC', 'RCT', 'RCA', 'RCB'))) {
				$tids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('SAC', 'BAC'))) {
				$aids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('PRC', 'RSC'))) {
				$pids[$log['relatedid']] = $log['relatedid'];
			} elseif(in_array($log['operation'], array('TFR', 'RCV'))) {
				$uids[$log['relatedid']] = $log['relatedid'];
			} elseif($log['operation'] == 'TRC') {
				$taskids[$log['relatedid']] = $log['relatedid'];
			}
			$loglist[] = $log;
		}
		$otherinfo = getotherinfo($aids, $pids, $tids, $taskids, $uids);
	}


}

if($count) {
	$multi = multi($count, $perpage, $page, $theurl);
}

$optypehtml = '<select id="optype" name="optype" class="ps" width="168">';
$optypehtml .= '<option value="">'.lang('spacecp', 'logs_select_operation').'</option>';
foreach($optypes as $type) {
	$optypehtml .= '<option value="'.$type.'"'.($type == $_G['gp_optype'] ? ' selected="selected"' : '').'>'.lang('spacecp', 'logs_credit_update_'.$type).'</option>';
}
$optypehtml .= '</select>';
include template('home/spacecp_credit_log');
?>