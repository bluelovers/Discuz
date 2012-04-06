<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_logs.php 27854 2012-02-15 11:12:37Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$lpp = empty($_G['gp_lpp']) ? 20 : $_G['gp_lpp'];
$checklpp = array();
$checklpp[$lpp] = 'selected="selected"';
$extrainput = '';

$operation = in_array($operation, array('illegal', 'rate', 'credit', 'mods', 'medal', 'ban', 'cp', 'magic', 'error', 'invite', 'payment', 'warn')) ? $operation : 'illegal';

$logdir = DISCUZ_ROOT.'./data/log/';
$logfiles = get_log_files($logdir, $operation.'log');
$logs = array();
$lastkey = count($logfiles) - 1;
$lastlog = $logfiles[$lastkey];
krsort($logfiles);
if($logfiles) {
	if(!isset($_G['gp_day']) || strexists($_G['gp_day'], '_')) {
		list($_G['gp_day'], $_G['gp_num']) = explode('_', $_G['gp_day']);
		$logs = file(($_G['gp_day'] ? $logdir.$_G['gp_day'].'_'.$operation.'log'.($_G['gp_num'] ? '_'.$_G['gp_num'] : '').'.php' : $logdir.$lastlog));
	} else {
		$logs = file($logdir.$_G['gp_day'].'_'.$operation.'log'.'.php');
	}
}

$start = ($page - 1) * $lpp;
$logs = array_reverse($logs);

if(empty($_G['gp_keyword'])) {
	$num = count($logs);
	$multipage = multi($num, $lpp, $page, ADMINSCRIPT."?action=logs&operation=$operation&lpp=$lpp".(!empty($_G['gp_day']) ? '&day='.$_GET['day'] : ''), 0, 3);
	$logs = array_slice($logs, $start, $lpp);
} else {
	foreach($logs as $key => $value) {
		if(strpos($value, $_G['gp_keyword']) === FALSE) {
			unset($logs[$key]);
		}
	}
	$multipage = '';
}

$usergroup = array();

if(in_array($operation, array('rate', 'mods', 'ban', 'cp'))) {
	$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup'));
	while($group = DB::fetch($query)) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}

shownav('tools', 'nav_logs', 'nav_logs_'.$operation);
if($logfiles) {
	$sel = '<select class="right" style="margin-right:20px;" onchange="location.href=\''.ADMINSCRIPT.'?action=logs&operation='.$operation.'&keyword='.$_G['gp_keyword'].'&day=\'+this.value">';
	foreach($logfiles as $logfile) {
		list($date, $logtype, $num) = explode('_', $logfile);
		if(is_numeric($date)) {
			$num = intval($num);
			$sel .= '<option value="'.$date.'_'.$num.'"'.($date.'_'.$num == $_G['gp_day'].'_'.intval($_G['gp_num']) ? ' selected="selected"' : '').'>'.($num ? '&nbsp;&nbsp;'.$date.' '.cplang('logs_archive').' '.$num : $date).'</option>';
		} else {
			list($logtype) = explode('.', $logtype);
			$sel .= '<option value="'.$logtype.'"'.($logtype == $_G['gp_day'] ? ' selected="selected"' : '').'>'.$logtype.'</option>';
		}
	}
	$sel .= '</select>';
} else {
	$sel = '';
}
showsubmenu('nav_logs', array(
	array(array('menu' => 'nav_logs_member', 'submenu' => array(
		array('nav_logs_illegal', 'logs&operation=illegal'),
		array('nav_logs_ban', 'logs&operation=ban'),
		array('nav_logs_mods', 'logs&operation=mods'),
	)), '', in_array($operation, array('illegal', 'ban', 'mods'))),
	array(array('menu' => 'nav_logs_system', 'submenu' => array(
		array('nav_logs_cp', 'logs&operation=cp'),
		array('nav_logs_error', 'logs&operation=error'),
	)), '', in_array($operation, array('cp', 'error'))),
	array(array('menu' => 'nav_logs_extended', 'submenu' => array(
		array('nav_logs_rate', 'logs&operation=rate'),
		array('nav_logs_warn', 'logs&operation=warn'),
		array('nav_logs_credit', 'logs&operation=credit'),
		array('nav_logs_magic', 'logs&operation=magic'),
		array('nav_logs_medal', 'logs&operation=medal'),
		array('nav_logs_invite', 'logs&operation=invite'),
		array('nav_logs_payment', 'logs&operation=payment'),
	)), '', in_array($operation, array('rate', 'credit', 'magic', 'medal', 'invite', 'payment')))
), $sel);
if($operation == 'illegal') {
	showtips('logs_tips_illegal');
} elseif($operation == 'ban') {
	showtips('logs_tips_ban');
}
showformheader("logs&operation=$operation");
showtableheader('', 'fixpadding" style="table-layout: fixed');
$filters = '';
if($operation == 'illegal') {

	showtablerow('class="header"', array('class="td23"','class="td24"','class="td23"','class="td23"','class="td23"'), array(
		cplang('time'),
		cplang('ip'),
		cplang('logs_passwd_username'),
		cplang('logs_passwd_password'),
		cplang('logs_passwd_security')
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		if(strtolower($log[2]) == strtolower($_G['member']['username'])) {
			$log[2] = "<b>$log[2]</b>";
		}
		$log[5] = $_G['group']['allowviewip'] ? $log[5] : '-';

		showtablerow('', array('class="smallefont"', 'class="smallefont"', 'class="bold"', 'class="smallefont"', 'class="smallefont"'), array(
			$log[1],
			$log[5],
			$log[2],
			$log[3],
			$log[4]
		));
	}

} elseif($operation == 'rate') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td23"','class="td23"','class="td24"'), array(
		cplang('username'),
		cplang('usergroup'),
		cplang('time'),
		cplang('logs_rating_username'),
		cplang('logs_rating_rating'),
		cplang('subject'),
		cplang('reason'),
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]</a>";
		$log[3] = $usergroup[$log[3]];
		if($log[4] == $_G['member']['username']) {
			$log[4] = "<b>$log[4]</b>";
		}
		$log[4] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[4])."\" target=\"_blank\">$log[4]</a>";
		$log[6] = $_G['setting']['extcredits'][$log[5]]['title'].' '.($log[6] < 0 ? "<b>$log[6]</b>" : "+$log[6]").' '.$_G['setting']['extcredits'][$log[5]]['unit'];
		$log[7] = $log[7] ? "<a href=\"./forum.php?mod=viewthread&tid=$log[7]\" target=\"_blank\" title=\"$log[8]\">".cutstr($log[8], 20)."</a>" : "<i>$lang[logs_rating_manual]</i>";

		showtablerow('', array('class="bold"'), array(
			$log[2],
			$log[3],
			$log[1],
			$log[4],
			(trim($log[10]) == 'D' ? $lang['logs_rating_delete'] : '').$log[6],
			$log[7],
			$log[9]
		));
	}

} elseif($operation == 'warn') {

	showtablerow('class="header"', array('class="td28"','class="td23"','class="td23"','class="td23"'), array(
		cplang('warn_info'),
		cplang('members_warn'),
		cplang('members_access_adminuser'),
		cplang('members_access_dateline'),
	));

	$sql = '';
	$warncount = 0;
	if($_G['gp_keyword']) {
		$searchuserarr = explode(',', $_G['gp_keyword']);
		$sql = " WHERE fw.author IN (".dimplode($searchuserarr).")";
	}

	$warncount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_warning').' fw '.$sql);
	$query = DB::query("SELECT fw.* FROM ".DB::table('forum_warning')." fw $sql ORDER BY fw.wid DESC LIMIT $start, $lpp");
	while($row = DB::fetch($query)) {
		showtablerow('', array('class="td28"', 'class=""', '', 'class="td26"', ''), array(
			'<b>'.cplang('warn_url').'</b><a href="forum.php?mod=redirect&goto=findpost&pid='.$row['pid'].'" target="_blank">'.$_G['siteurl'].'forum.php?mod=redirect&goto=findpost&pid='.$row['pid'].'</a><br><b>'.cplang('warn_reason').'</b>'.$row['reason'],
			'<a href="home.php?mod=space&uid='.$row['authorid'].'">'.$row['author'].'</a>',
			'<a href="home.php?mod=space&uid='.$row['operatorid'].'">'.$row['operator'].'</a>',
			dgmdate($row['dateline'], 'y-m-d H:i'),
		));
	}
	$multipage = multi($warncount, $lpp, $page, ADMINSCRIPT."?action=logs&operation=$operation&keyword=".rawurlencode($_G['gp_keyword'])."&lpp=$lpp", 0, 3);

} elseif($operation == 'credit') {

	$operationlist = array('TRC', 'RTC', 'RAC', 'MRC', 'TFR', 'RCV', 'CEC', 'ECU', 'SAC', 'BAC', 'PRC', 'RSC', 'STC', 'BTC', 'AFD', 'UGP', 'RPC', 'ACC', 'RCT', 'RCA', 'RCB', 'CDC', 'RKC');

	$rdata = array(
		'task' => array('TRC'),
		'thread' => array('RTC', 'RAC', 'STC', 'BTC', 'ACC', 'RCT', 'RCA', 'RCB'),
		'member' => array('TFR', 'RCV', 'CEC', 'ECU', 'AFD', 'CDC', 'RKC'),
		'attach' => array('BAC', 'SAC'),
		'magic' => array('MRC', 'BGC', 'RGC', 'AGC', 'BMC'),
		'post' => array('PRC', 'RSC'),
		'usergroup' => array('UGP'),
		'report' => array('RPC'),
	);

	$perpage = max(50, empty($_G['gp_perpage']) ? 50 : intval($_G['gp_perpage']));
	$start_limit = ($page - 1) * $perpage;

	$where = '1';
	$pageadd = '';
	if($srch_uid = trim($_G['gp_srch_uid'])) {
		if($uid = max(0, intval($srch_uid))) {
			$where .= " AND l.`uid`='$uid'";
			$pageadd .= '&srch_uid='.$uid;
		} else {
			$srch_uid = '';
		}
	} elseif($srch_username = trim($_G['gp_srch_username'])) {
		$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$srch_username'");
		if($uid) {
			$where .= " AND l.`uid`='$uid'";
			$pageadd .= '&srch_username='.rawurlencode($srch_username);
		} else {
			$srch_username = '';
		}
	}
	if(($srch_rtype = trim($_G['gp_srch_rtype'])) && array_key_exists($srch_rtype, $rdata) && isset($_G['gp_srch_rid']) && ($srch_rid = max(0, intval($_G['gp_srch_rid'])))) {
		$where .= " AND l.`relatedid`='$srch_rid'";
		$pageadd .= '&srch_rtype='.$srch_rtype.'&srch_rid='.$srch_rid;
	}
	if($srch_operation = trim($_G['gp_srch_operation'])) {
		if(in_array($srch_operation, $operationlist)) {
			$where .= " AND l.`operation`='$srch_operation'";
			$pageadd .= '&srch_operation='.$srch_operation;
		}
	}
	if($srch_starttime = trim($_G['gp_srch_starttime'])) {
		if($starttime = strtotime($srch_starttime)) {
			$where .= " AND l.`dateline`>'$starttime'";
			$pageadd .= '&srch_starttime='.$srch_starttime;
		} else {
			$srch_starttime = '';
		}
	}
	if($srch_endtime = trim($_G['gp_srch_endtime'])) {
		if($endtime = strtotime($srch_endtime)) {
			$where .= " AND l.`dateline`<'$endtime'";
			$pageadd .= '&srch_endtime='.$srch_endtime;
		} else {
			$srch_endtime = '';
		}
	}

	$select_operation_html = '<select name="srch_operation">';
	$select_operation_html .= '<option>'.cplang('logs_select_operation').'</option>';
	foreach($operationlist as $row) {
		$select_operation_html .= '<option value="'.$row.'"'.($row == $srch_operation ? ' selected="selected"' : '').'>'.cplang('logs_credit_update_'.$row).'</option>';
	}
	$select_operation_html .= '</select>';

	$select_rid_html = '<select name="srch_rtype"><option value="">'.$lang['logs_select_ridtype'].'</option>';
	foreach($rdata as $k => $v) {
		$select_rid_html .= '<option value="'.$k.'"'.($srch_rtype == $k ? ' selected="selected"' : '').'>'.$lang['logs_'.$k.'_id'].'</option>';
	}
	$select_rid_html .= '</select>';

	showtableheader('search', 'fixpadding');
	showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
		array(
			cplang('username'), '<input type="text" name="srch_username" class="txt" value="'.$srch_username.'" />',
			cplang('logs_credit_relatedid'), $select_rid_html.'&nbsp;<input type="text" name="srch_rid" class="txt" value="'.$srch_rid.'" />',
		)
	);
	showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
		array(
			cplang('uid'), '<input type="text" name="srch_uid" class="txt" value="'.$srch_uid.'" />',
			cplang('time'), '<input type="text" name="srch_starttime" class="txt" value="'.$srch_starttime.'" onclick="showcalendar(event, this)" />- <input type="text" name="srch_endtime" class="txt" value="'.$srch_endtime.'" onclick="showcalendar(event, this)" />',
		)
	);
	showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
		array(
			cplang('logs_lpp'), '<input type="text" name="perpage" class="txt" value="'.$perpage.'" size="5" /></label>',
			cplang('type'), $select_operation_html,
		)
	);
	showtablerow('', array('colspan="4"'), array('<input type="submit" name="srchlogbtn" class="btn" value="'.$lang['search'].'" />'));
	showtablefooter();
	echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
	showtableheader('', 'fixpadding');
	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td24"','class="td24"'), array(
		cplang('username'),
		cplang('time'),
		cplang('type'),
		cplang('logs_credits_log_update'),
		cplang('logs_credit_relatedid'),
	));

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." l WHERE $where");

	$mpurl = ADMINSCRIPT."?action=logs&operation=$operation".$pageadd;
	$multipage = multi($num, $perpage, $page, $mpurl, 0, 3);

	$query = DB::query("SELECT l.*, u.username
		FROM ".DB::table('common_credit_log')." l
		LEFT JOIN ".DB::table('common_member')." u
		ON l.uid=u.uid
		WHERE $where
		ORDER BY l.dateline DESC LIMIT $start_limit, $perpage");

	while($log = DB::fetch($query)) {
		$log['dateline'] = dgmdate($log['dateline'], 'y-n-j H:i');
		$log['update'] = '';
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			if($log['extcredits'.$id]) {
				$log['update'] .= $credit['title'].($log['extcredits'.$id] > 0 ? '+' : '').$log['extcredits'.$id].$credit['unit'].'&nbsp;';
			}
		}
		$related = '';
		if(in_array($log['operation'], $rdata['task'])) {
			$rtype = 'task';
			$related = '<a href="home.php?mod=task&do=view&id='.$log['relatedid'].'" target="_blank">'.cplang('logs_task_id').':'.$log['relatedid'].'</a>';
		} elseif(in_array($log['operation'], $rdata['thread'])) {
			$rtype = 'thread';
			$related = '<a href="forum.php?mod=viewthread&tid='.$log['relatedid'].'" target="_blank">'.cplang('logs_thread_id').':'.$log['relatedid'].'</a>';
		} elseif(in_array($log['operation'], $rdata['magic'])) {
			$rtype = 'magic';
			$related = cplang('logs_magic_id').':'.$log['relatedid'];
		} elseif(in_array($log['operation'], $rdata['member'])) {
			$rtype = 'member';
			$related = '<a href="home.php?mod=space&uid='.$log['relatedid'].'&do=profile" target="_blank">'.cplang('uid').':'.$log['relatedid'].'</a>';
		} elseif(in_array($log['operation'], $rdata['attach'])) {
			$rtype = 'attach';
			$aid = aidencode($log['relatedid']);
			$related = '<a href="forum.php?mod=attachment&aid='.$aid.'&findpost=yes" target="_blank">'.cplang('logs_attach_id').':'.$log['relatedid'].'</a>';
		} elseif(in_array($log['operation'], $rdata['post'])) {
			$rtype = 'post';
			$related = '<a href="forum.php?mod=redirect&goto=findpost&pid='.$log['relatedid'].'" target="_blank">'.cplang('logs_post_id').':'.$log['relatedid'].'</a>';
		} elseif(in_array($log['operation'], $rdata['usergroup'])) {
			$rtype = 'usergroup';
			$related = $_G['cache']['group'][$log['relatedid']]['grouptitle'];
		} elseif(in_array($log['operation'], $rdata['report'])) {
			$rtype = 'report';
			$related = cplang('logs_report_id').':'.$log['relatedid'];
		}
		showtablerow('', array('class="bold"'), array(
			"<a href=\"home.php?mod=space&uid=$log[uid]\" target=\"_blank\">$log[username]",
			$log['dateline'],
			cplang('logs_credit_update_'.$log['operation']),
			$log['update'],
			$related.'&nbsp;&nbsp;<a href="'.ADMINSCRIPT.'?action=logs&operation=credit&srch_rtype='.$rtype.'&srch_rid='.$log['relatedid'].'" target="_blank">'.cplang('sameinfo').'</a>',
		));
	}

	showsubmit('', '', '', '', $multipage);


} elseif($operation == 'mods') {

	$modactioncode = lang('forum/modaction');

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td24"','class="td23"','class="td24"','class="td24"','class="td23"'), array(
		cplang('operator'),
		cplang('usergroup'),
		cplang('ip'),
		cplang('time'),
		cplang('forum'),
		cplang('thread'),
		cplang('action'),
		cplang('reason'),
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = dstripslashes($log[2]);
		$log[3] = $usergroup[$log[3]];
		$log[4] = $_G['group']['allowviewip'] ? $log[4] : '-';
		$log[6] = "<a href=\"./forum.php?mod=forumdisplay&fid=$log[5]\" target=\"_blank\">$log[6]</a>";
		$log[8] = "<a href=\"./forum.php?mod=viewthread&tid=$log[7]\" target=\"_blank\" title=\"$log[8]\">".cutstr($log[8], 15)."</a>";
		$log[9] = $modactioncode[trim($log[9])];
		showtablerow('', array('class="bold"'), array(
			"<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2]),
			$log[3],
			$log[4],
			$log[1],
			$log[6],
			$log[8],
			$log[9],
			$log[10],
		));
	}

} elseif($operation == 'ban') {

	showtablerow('class="header"', array('class="td24"', 'class="td23"', 'class="td23"', 'class="td23"', 'class="td25"', 'style="width:160px"', 'class="td23"'), array(
		cplang('operator'),
		cplang('ip'),
		cplang('time'),
		cplang('username'),
		cplang('operation'),
		cplang('logs_banned_group'),
		cplang('validity'),
		cplang('reason'),
	));
	$operations = array(1 => '<b>'.$lang['logs_lock'].'</b>', 2 => '<b>'.$lang['logs_unlock'].'</b>', 3 => '<i>'.$lang['logs_banned_unban'].'</i>', 4 => '<b>'.$lang['logs_banned_ban'].'</b>');
	$extrainput = '&nbsp;'.cplang('operation').': <select name="filter"><option></option>';
	foreach($operations as $k => $v) {
		$extrainput .= '<option value="'.$k.'"'.($_G['gp_filter'] == $k ? ' selected="selected"' : '').'>'.strip_tags($v).'</option>';
	}
	$extrainput .= '</select>';

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]</a> <span class=\"normal\">".$usergroup[$log[3]]."</span>";
		$log[4] = $_G['group']['allowviewip'] ? $log[4] : '-';
		$log[5] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[5])."\" target=\"_blank\">$log[5]</a>";
		$log[8] = trim($log[8]) ? dgmdate($log[8], 'y-n-j') : '';
		if($log[10] == -1) {
			$operation = 1;
		} else {
			if($log[6] == $log[7]) {
				$operation = 2;
			} else {
				$operation = (in_array($log[6], array(4, 5)) && !in_array($log[7], array(4, 5))) ? 3 : 4;
			}
		}
		if(!empty($_G['gp_filter']) && $_G['gp_filter'] != $operation) {
			continue;
		}
		$operation = $operations[$operation];

		showtablerow('', array('class="bold"','class="smallefont"','class="smallefont"','','','','class="smallefont"',''), array(
			$log[2],
			$log[4],
			$log[1],
			$log[5],
			$operation,
			"{$usergroup[$log[6]]} / {$usergroup[$log[7]]}",
			$log[8],
			$log[9]
		));
	}

} elseif($operation == 'cp') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td24"','class="td24"','class="td24"', ''), array(
		cplang('operator'),
		cplang('usergroup'),
		cplang('ip'),
		cplang('time'),
		cplang('action'),
		cplang('other')
	));

echo <<<EOD
<script type="text/javascript">
function togglecplog(k) {
	var cplogobj = $('cplog_'+k);
	if(cplogobj.style.display == 'none') {
		cplogobj.style.display = '';
	} else {
		cplogobj.style.display = 'none';
	}
}
</script>
EOD;

	foreach($logs as $k => $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = dstripslashes($log[2]);
		$log[2] = "<a href=\"home.php?mod=space&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2])."</a>";
		$log[3] = $usergroup[$log[3]];
		$log[4] = $_G['group']['allowviewip'] ? $log[4] : '-';
		$log[5] = rtrim($log[5]);
 		showtablerow('', array('class="bold"'), array($log[2], $log[3], $log[4], $log[1], $log[5], '<a href="javascript:;" onclick="togglecplog('.$k.')">'.cutstr($log[6], 200).'</a>'));
 		echo '<tbody id="cplog_'.$k.'" style="display:none;">';
 		echo '<tr><td colspan="6">'.$log[6].'</td></tr>';
 		echo '</tbody>';
	}

} elseif($operation == 'error') {

	showtablerow('class="header"', array('class="td23"', 'class=""'), array(
		cplang('time'),
		cplang('message'),
	));
	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}

		showtablerow('', array('class="bold"'), array(
			dgmdate($log[1], 'Y-m-d H:i:s'),
			$log[2].'<br>'.$log[4].'<br>'.$log[5]
		));

	}

} elseif($operation == 'invite') {

	if(!submitcheck('invitesubmit')) {

		$where = '1';
		$pageadd = '';
		if($srch_uid = trim($_G['gp_srch_uid'])) {
			if($uid = max(0, intval($srch_uid))) {
				$where .= " AND i.`uid`='$uid'";
				$pageadd .= '&srch_uid='.$uid;
			} else {
				$srch_uid = '';
			}
		} elseif($srch_username = trim($_G['gp_srch_username'])) {
			$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$srch_username'");
			if($uid) {
				$where .= " AND i.`uid`='$uid'";
				$pageadd .= '&srch_username='.rawurlencode($srch_username);
			} else {
				$srch_username = '';
			}
		}
		if($srch_fuid = trim($_G['gp_srch_fuid'])) {
			if($uid = max(0, intval($srch_fuid))) {
				$where .= " AND i.`fuid`='$uid'";
				$pageadd .= '&srch_fuid='.$uid;
			} else {
				$srch_fuid = '';
			}
		}
		if($srch_fusername = trim($_G['gp_srch_fusername'])) {
			$where .= " AND i.`fusername`='$srch_fusername'";
			$pageadd .= '&srch_fusername='.rawurlencode($srch_fusername);
		}
		if($srch_buydate_start = trim($_G['gp_srch_buydate_start'])) {
			if($buydate_start = strtotime($srch_buydate_start)) {
				$where .= " AND i.`dateline`>'$buydate_start'";
				$pageadd .= '&srch_buydate_start='.$srch_buydate_start;
			} else {
				$srch_buydate_start = '';
			}
		}
		if($srch_buydate_end = trim($_G['gp_srch_buydate_end'])) {
			if($buydate_end = strtotime($srch_buydate_end)) {
				$where .= " AND i.`dateline`<'$buydate_end'";
				$pageadd .= '&srch_buydate_end='.$srch_buydate_end;
			} else {
				$srch_buydate_end = '';
			}
		}
		if($srch_ip = trim($_G['gp_srch_ip'])) {
			$pageadd .= '&srch_ip='.rawurlencode($srch_ip);
			$inviteip = str_replace('*', '%', addcslashes($srch_ip, '%_'));
			$srch_ip = htmlspecialchars($srch_ip);
			$where .= " AND i.`inviteip` LIKE '$inviteip%'";
		}
		if($srch_code = trim($_G['gp_srch_code'])) {
			$pageadd .= '&srch_ip='.rawurlencode($srch_code);
			$where .= " AND i.`code`='$srch_code'";
			$srch_code = htmlspecialchars($srch_code);
		}
		showtableheader('search', 'fixpadding');
		showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
			array(
				cplang('username'), '<input type="text" name="srch_username" class="txt" value="'.$srch_username.'" />',
				cplang('logs_invite_ip'), '<input type="text" name="srch_ip" class="txt" value="'.$srch_ip.'" size="5" />',
			)
		);
		showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
			array(
				cplang('uid'), '<input type="text" name="srch_uid" class="txt" value="'.$srch_uid.'" />',
				cplang('logs_invite_code'), '<input type="text" name="srch_code" class="txt" value="'.$srch_code.'" size="5" />',
			)
		);
		showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
			array(
				cplang('logs_invite_target'), '<input type="text" name="srch_fusername" class="txt" value="'.$srch_fusername.'" />',
				cplang('logs_invite_buydate'), '<input type="text" name="srch_buydate_start" class="txt" value="'.$srch_buydate_start.'" onclick="showcalendar(event, this)" />- <input type="text" name="srch_buydate_end" class="txt" value="'.$srch_buydate_end.'" onclick="showcalendar(event, this)" />',
			)
		);
		showtablerow('', array('class="td23"', 'width="150"', 'class="td23"'),
			array(
				cplang('logs_invite_target').cplang('uid'), '<input type="text" name="srch_fuid" class="txt" value="'.$srch_fuid.'" />',
				'', '',
			)
		);
		showtablerow('', array('colspan="4"'), array('<input type="submit" name="srchlogbtn" class="btn" value="'.$lang['search'].'" />'));
		showtablefooter();
		echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
		showtableheader('', 'fixpadding');
		showtablerow('class="header"', array('width="35"','class="td23"','class="td24"','class="td24"','class="td23"','class="td24"','class="td24"'), array(
			'',
			cplang('logs_invite_buyer'),
			cplang('logs_invite_buydate'),
			cplang('logs_invite_expiration'),
			cplang('logs_invite_ip'),
			cplang('logs_invite_code'),
			cplang('logs_invite_status'),
		));

		$tpp = $_G['gp_lpp'] ? intval($_G['gp_lpp']) : $_G['tpp'];
		$start_limit = ($page - 1) * $tpp;

		$dels = array();
		$invitecount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_invite')." i WHERE $where");

		if($invitecount) {
			$multipage = multi($invitecount, $tpp, $page, ADMINSCRIPT."?action=logs&operation=invite&lpp=$lpp$pageadd", 0, 3);

			$query = DB::query("SELECT i.*, m.username FROM ".DB::table('common_invite')." i LEFT JOIN ".DB::table('common_member')." m USING(uid) WHERE $where ORDER BY i.id DESC LIMIT $start_limit,$tpp");
			while($invite = DB::fetch($query)) {
				if(!$invite['fuid'] && $_G['timestamp'] > $invite['endtime']) {
					$dels[] = $invite['id'];
					continue;
				}

				$invite['statuslog'] = $lang['logs_invite_status_'.$invite['status']];
				$username = "<a href=\"home.php?mod=space&uid=$invite[uid]\">$invite[username]</a>";
				$invite['dateline'] = dgmdate($invite['dateline'], 'Y-n-j H:i');
				$invite['expiration'] = dgmdate($invite['endtime'], 'Y-n-j H:i');
				$stats = $invite['statuslog'].($invite['status'] == 2 ? '&nbsp;[<a href="home.php?mod=space&uid='.$invite['fuid'].'" target="_blank">'.$lang['logs_invite_target'].':'.$invite['fusername'].'</a>]' : '');

				showtablerow('', array('', 'class="bold"'), array(
					'<input type="checkbox" class="checkbox" name="delete[]" value="'.$invite['id'].'" />',
					$username,
					$invite['dateline'],
					$invite['expiration'],
					$invite['inviteip'],
					$invite['code'],
					$stats
				));
			}
			showhiddenfields(array('pageadd' => $pageadd));

			if($dels) {
				DB::query("DELETE FROM ".DB::table('common_invite')." WHERE id IN (".dimplode($dels).")");
			}
		}

	} else {

		if($deletelist = dimplode($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('common_invite')." WHERE id IN ($deletelist)");
		}

		header("Location: $_G[siteurl]".ADMINSCRIPT."?action=logs&operation=invite&lpp=$_G[gp_lpp]$_G[gp_pageadd]");
	}

} elseif($operation == 'magic') {

	loadcache('magics');

	$lpp = empty($_G['gp_lpp']) ? 20 : $_G['gp_lpp'];
	$start_limit = ($page - 1) * $lpp;

	$mpurl = ADMINSCRIPT."?action=logs&operation=magic&lpp=$lpp";

	$wheresql = '';
	$wherearr = array();
	if(in_array($_G['gp_opt'], array('1', '2', '3', '4', '5'))) {
		$wherearr[] = "ma.action='{$_G['gp_opt']}'";
		$mpurl .= '&opt='.$_G['gp_opt'];
	}

	if(!empty($_G['gp_magicid'])) {
		$wherearr[] = "ma.magicid='".intval($_G['gp_magicid'])."'";
		$mpurl .= '&magicid='.$_G['gp_magicid'];
	}

	$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

	$check1 = $check2 = array();
	$check1[$_G['gp_magicid']] = 'selected="selected"';
	$check2[$_G['gp_opt']] = 'selected="selected"';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=magic&opt='.$_G['gp_opt'].'&lpp='.$lpp.'&opt='.$_G['gp_opt'].'&magicid=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['magics_type'].'</option>';
	foreach($_G['cache']['magics'] as $id => $magic) {
		$filters .= '<option value="'.$id.'" '.$check1[$id].'>'.$magic['name'].'</option>';
	}
	$filters .= '</select>';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=magic&magicid='.$magicid.'&lpp='.$lpp.'&magicid='.$_G['gp_magicid'].'&opt=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['action'].'</option><option value="">'.$lang['all'].'</option>';
	foreach(array('1', '2', '3', '4', '5') as $o) {
		$filters .= '<option value="'.$o.'" '.$check2[$o].'>'.$lang['logs_magic_operation_'.$o].'</option>';
	}
	$filters .= '</select>';

	showtablerow('class="header"', array('class="td23"', 'class="td23"', 'class="td24"', 'class="td23"', 'class="td23"', 'class="td24"'), array(
		cplang('username'),
		cplang('name'),
		cplang('time'),
		cplang('num'),
		cplang('price'),
		cplang('action')
	));

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_magiclog')." ma $wheresql");
	if($num) {
		$multipage = multi($num, $lpp, $page, $mpurl, 0, 3);
		$query = DB::query("SELECT ma.*, m.username FROM ".DB::table('common_magiclog')." ma
			LEFT JOIN ".DB::table('common_member')." m USING (uid)
			$wheresql ORDER BY dateline DESC LIMIT $start_limit, $lpp");

		$targetuids = $logs = array();
		while($log = DB::fetch($query)) {
			$log['name'] = $_G['cache']['magics'][$log['magicid']]['name'];
			$log['dateline'] = dgmdate($log['dateline'], 'Y-n-j H:i');
			if($log['action'] == 3) {
				$targetuids[] = $log['targetuid'];
			}
			$logs[] = $log;
		}

		if($targetuids) {
			$query = DB::query("SELECT username,uid FROM ".DB::table('common_member')." WHERE uid IN (".dimplode($targetuids).")");
			$targetuids = array();
			while($row = DB::fetch($query)) {
				$targetuids[$row['uid']] = $row['username'];
			}
		}

		foreach($logs as $log) {
			showtablerow('', array('class="bold"'), array(
				"<a href=\"home.php?mod=space&username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]",
				$log['name'],
				$log['dateline'],
				$log['amount'],
				$log['price'],
				$lang['logs_magic_operation_'.$log['action']].($log['action'] == 3 ? '<a href="home.php?mod=space&uid='.$log['targetuid'].'" target="_blank">'.$targetuids[$log['targetuid']].'</a>' : ''),
			));
		}
	}

} elseif($operation == 'medal') {

	loadcache('medals');

	$lpp = empty($_G['gp_lpp']) ? 50 : $_G['gp_lpp'];
	$start_limit = ($page - 1) * $lpp;

	$mpurl = ADMINSCRIPT."?action=logs&operation=medal&lpp=$lpp";

	$wheresql = '';
	$wherearr = array();
	if(in_array($_G['gp_opt'], array('0', '1', '2', '3'))) {
		$wherearr[] = "me.type='{$_G['gp_opt']}'";
		$mpurl .= '&opt='.$_G['gp_opt'];
	}
	if(!empty($_G['gp_medalid'])) {
		$wherearr[] = "me.medalid='".intval($_G['gp_medalid'])."'";
		$mpurl .= '&medalid='.$_G['gp_medalid'];
	}

	$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

	$check1 = $check2 = array();
	$check1[$_G['gp_medalid']] = 'selected="selected"';
	$check2[$_G['gp_opt']] = 'selected="selected"';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=medal&opt='.$_G['gp_opt'].'&lpp='.$lpp.'&medalid=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['medals'].'</option><option value="">'.$lang['all'].'</option>';
	foreach($_G['cache']['medals'] as $id => $medal) {
		$filters .= '<option value="'.$id.'" '.$check1[$id].'>'.$medal['name'].'</option>';
	}
	$filters .= '</select>';

	$filters .= '<select onchange="window.location=\''.ADMINSCRIPT.'?action=logs&operation=medal&medalid='.$_G['gp_medalid'].'&lpp='.$lpp.'&opt=\'+this.options[this.selectedIndex].value"><option value="">'.$lang['action'].'</option><option value="">'.$lang['all'].'</option>';
	foreach(array('0', '1', '2', '3') as $o) {
		$filters .= '<option value="'.$o.'" '.$check2[$o].'>'.$lang['logs_medal_operation_'.$o].'</option>';
	}
	$filters .= '</select>';

	showtablerow('class="header"', array('class="td23"', 'class="td24"', 'class="td23"', 'class="td23"'), array(
		cplang('username'),
		cplang('logs_medal_name'),
		cplang('type'),
		cplang('time'),
		cplang('logs_medal_expiration')
	));

	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_medallog')." me $wheresql");
	if($num) {
		$multipage = multi($num, $lpp, $page, $mpurl, 0, 3);
		$query = DB::query("SELECT me.*, m.username FROM ".DB::table('forum_medallog')." me
			LEFT JOIN ".DB::table('common_member')." m USING (uid)
			$wheresql ORDER BY dateline DESC LIMIT $start_limit, $lpp");

		while($log = DB::fetch($query)) {
			$log['name'] = $_G['cache']['medals'][$log['medalid']]['name'];
			$log['dateline'] = dgmdate($log['dateline'], 'Y-n-j H:i');
			$log['expiration'] = empty($log['expiration']) ? cplang('logs_noexpire') : dgmdate($log['expiration'], 'Y-n-j H:i');
			showtablerow('', array('class="td23"', 'class="td24"', 'class="td23"', 'class="td24"'), array(
				"<a href=\"home.php?mod=space&username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]",
				$log['name'],
				$lang['logs_medal_operation_'.$log['type']],
				$log['dateline'],
				$log['expiration']
			));
		}
	}

} elseif($operation == 'payment') {

	showtablerow('class="header"', array('width="30%"','class="td23"','class="td23"','class="td24"','class="td23"','class="td24"','class="td24"'), array(
		cplang('subject'),
		cplang('logs_payment_amount'),
		cplang('logs_payment_seller'),
		cplang('logs_payment_buyer'),
		cplang('logs_payment_dateline'),
		cplang('logs_payment_buydateline'),
	));

	$tpp = $_G['gp_lpp'] ? intval($_G['gp_lpp']) : $_G['tpp'];
	$start_limit = ($page - 1) * $tpp;

	$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE operation='BTC'");
	if($threadcount) {
		$multipage = multi($threadcount, $tpp, $page, ADMINSCRIPT."?action=logs&operation=payment&lpp=$lpp", 0, 3);
		$query = DB::query("SELECT l.*, m.username, t.tid, t.subject, t.dateline AS postdateline, t.author, t.authorid AS tauthorid
				FROM ".DB::table('common_credit_log')." l
				LEFT JOIN ".DB::table('common_member')." m ON m.uid=l.uid
				LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=l.relatedid
				WHERE l.operation='BTC'
				ORDER BY l.dateline DESC LIMIT $start_limit,$tpp");
		while($paythread = DB::fetch($query)) {
			$paythread['seller'] = $paythread['tauthorid'] ? "<a href=\"home.php?mod=space&uid=$paythread[tauthorid]\">$paythread[author]</a>" : cplang('logs_payment_del')."(<a href=\"home.php?mod=space&uid=$paythread[authorid]\">".cplang('logs_payment_view')."</a>)";
			$paythread['buyer'] = "<a href=\"home.php?mod=space&uid=$paythread[uid]\">$paythread[username]</a>";
			$paythread['subject'] = $paythread['subject'] ? "<a href=\"forum.php?mod=viewthread&tid=$paythread[tid]\">$paythread[subject]</a>" : cplang('logs_payment_del');
			$paythread['dateline'] = dgmdate($paythread['dateline'], 'Y-n-j H:i');
			$paythread['postdateline'] = $paythread['postdateline'] ? dgmdate($paythread['postdateline'], 'Y-n-j H:i') : cplang('logs_payment_del');
			foreach($_G['setting']['extcredits'] as $id => $credits) {
				if($paythread['extcredits'.$id]) {
					$paythread['amount'] = $credits['title'].':'.abs($paythread['extcredits'.$id]);
					break;
				}
			}
			showtablerow('', array('', 'class="bold"'), array(
				$paythread['subject'],
				$paythread['amount'],
				$paythread['seller'],
				$paythread['buyer'],
				$paythread['postdateline'],
				$paythread['dateline']
			));
		}
	}
}

function get_log_files($logdir = '', $action = 'action') {
	$dir = opendir($logdir);
	$files = array();
	while($entry = readdir($dir)) {
		$files[] = $entry;
	}
	closedir($dir);

	if($files) {
		sort($files);
		$logfile = $action;
		$logfiles = array();
		$ym = '';
		foreach($files as $file) {
			if(strpos($file, $logfile) !== FALSE) {
				if(substr($file, 0, 6) != $ym) {
					$ym = substr($file, 0, 6);
				}
				$logfiles[$ym][] = $file;
			}
		}
		if($logfiles) {
			$lfs = array();
			foreach($logfiles as $ym => $lf) {
				$lastlogfile = $lf[0];
				unset($lf[0]);
				$lf[] = $lastlogfile;
				$lfs = array_merge($lfs, $lf);
			}
			return $lfs;
		}
		return array();
	}
	return array();
}

showtablefooter();
showtableheader('', 'fixpadding');
if($operation != 'credit') {
	if(empty($_G['gp_keyword'])) {
		showhiddenfields(array('lpp' => $lpp));
	}
	if(!empty($_GET['day'])) {
		showhiddenfields(array('day' => $_GET['day']));
	}
	showsubmit($operation == 'invite' ? 'invitesubmit' : '', 'submit', 'del', $filters, $multipage.(empty($_G['gp_keyword']) ? cplang('logs_lpp').':<select onchange="if(this.options[this.selectedIndex].value != \'\') {this.form.lpp.value = this.options[this.selectedIndex].value;this.form.submit(); }"><option value="20" '.$checklpp[20].'> 20 </option><option value="40" '.$checklpp[40].'> 40 </option><option value="80" '.$checklpp[80].'> 80 </option></select>' : '').$extrainput.'&nbsp;'.($operation == 'warn' ? cplang('warn_user').': ' : '').($logfiles || $operation == 'warn' ? '<input type="text" class="txt" name="keyword" value="'.$_G['gp_keyword'].'" />'.($_G['gp_day'] ? '<input type="hidden" class="btn" value="'.$_G['gp_day'].'" />' : '').'<input type="submit" class="btn" value="'.$lang['search'].'" />' : ''));
}
showtablefooter();
showformfooter();

?>