<?php

/**
 *      [Ʒņ䝠(C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_logs.php 10422 2010-05-11 07:02:05Z liulanbo $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$page = intval($_GET['page']);
$operation = trim($_GET['operation']);
$_REQUEST['keyword'] = trim($_REQUEST['keyword']);
$_GET['lpp'] = intval($_GET['lpp']);
$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
$checklpp = array();
$checklpp[$lpp] = 'selected="selected"';

$operation = in_array($operation, array('admin')) ? $operation : 'admin';

$logdir = B_ROOT.'./data/log/';
$logfiles = get_log_files($logdir, $operation.'log');
$logs = array();
rsort($logfiles);
if($logfiles) {
	$logs = file(!empty($_GET['day']) ? $logdir.$_GET['day'].'_'.$operation.'log.php' : $logdir.$logfiles[0]);
}

$start = ($page - 1) * $lpp;
$logs = array_reverse($logs);

if(empty($_REQUEST['keyword'])) {
	$num = count($logs);
	$multipage = multi($num, $lpp, $page, $BASESCRIPT."?action=logs&operation=$operation&lpp=$lpp".(!empty($_GET['day']) ? '&day='.$_GET['day'] : ''), 1);
	$logs = array_slice($logs, $start, $lpp);

} else {
	foreach($logs as $key => $value) {
		if(strpos($value, $_REQUEST['keyword']) === false) {
			unset($logs[$key]);
		}
	}
	$multipage = '';
}

$usergroup = array();

shownav('admintools', 'nav_logs', 'nav_logs_'.$operation);
if($logfiles) {
	$sel = '<select class="right" onchange="location.href=\''.$BASESCRIPT.'?action=logs&operation='.$operation.'&day=\'+this.value">';
	foreach($logfiles as $logfile) {
		list($date) = explode('_', $logfile);
		$sel .= '<option value="'.$date.'"'.($date == $_GET['day'] ? ' selected="selected"' : '').'>'.$date.'</option>';
	}
	$sel .= '</select>';
} else {
	$sel = '';
}
showsubmenu('nav_logs_admin');

showformheader("logs&operation=$operation");
showtableheader('', 'fixpadding');
$filters = '';

if($operation == 'admin') {

	showtablerow('class="header"', array('class="td23"','class="td23"','class="td23"','class="td24"','class="td24"', ''), array(
		$lang['operator'],
		$lang['ip'],
		$lang['time'],
		$lang['action'],
		$lang['other']
	));

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = sgmdate($log[1], 'y-n-j H:i');
		$log[2] = sstripslashes($log[2]);
		$log[5] = rtrim($log[5]);
		$log[6] = cutstr($log[6], 200);
 		showtablerow('', array('class="bold"'), array($log[2], $log[3], $log[1], $log[4], $log[5]));
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
			if(strpos($file, $logfile) !== false) {
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
			return array_slice($lfs, -2, 2);
		}
		return array();
	}
	return array();
}

if($_REQUEST['keyword']) {
	$filters = '';
}
showsubmit($operation == 'invite' ? 'invitesubmit' : '', 'submit', 'del', $filters, $multipage.(empty($_REQUEST['keyword']) ? $lang['logs_lpp'].':<select onchange="if(this.options[this.selectedIndex].value != \'\') {window.location=\''.$BASESCRIPT.'?action=logs&operation='.$operation.'&lpp=\'+this.options[this.selectedIndex].value }"><option value="20" '.$checklpp[20].'> 20 </option><option value="40" '.$checklpp[40].'> 40 </option><option value="80" '.$checklpp[80].'> 80 </option></select>' : ''). '&nbsp;<input type="text" class="txt" name="keyword" value="'.$_REQUEST['keyword'].'" /><input type="submit" class="btn" value="'.$lang['search'].'"  />');
showtablefooter();
showformfooter();

?>