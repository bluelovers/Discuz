<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id:  12003 2010-06-23 07:41:55Z  $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$modulelist = $modulemenu = array();
$query = DB::query("SELECT * FROM ".DB::table('common_module')." WHERE available='1' ORDER BY displayorder");
while($module = DB::fetch($query)) {
	if($module['type'] != 1) {
		$modulelist[$module['mid']] = $module['name'];
	}
}

if(!$operation) {

	$_G['gp_mid'] = intval($_G['gp_mid']);

	$allselected = empty($_G['gp_mid']) ? 1 : 0;
	$modulemenu[] = array('all', 'log', $allselected);

	if(!empty($modulelist)) {
		foreach($modulelist as $mid => $modname) {
			$selected = $_G['gp_mid'] == $mid ? 1 : 0;
			$modulemenu[] = array($modname, 'log&mid='.$mid, $selected);
		}
	}

	shownav('global', 'nav_log');
	showsubmenu('nav_log', $modulemenu);
	showtableheader('log_list', 'fixpadding');
	showsubtitle(array('log_module', 'log_dateline', 'log_action', 'log_ip', 'log_content'));

	$where = !empty($_G['gp_mid']) ? 'mid='.$_G['gp_mid'] : 1;
	$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_log')." WHERE $where");

	$perpage = max(5, empty($_G['gp_perpage']) ? 50 : intval($_G['gp_perpage']));
	$start_limit = ($page - 1) * $perpage;
	$mpurl = ADMINSCRIPT."?action=log";

	$multipage = multi($num, $perpage, $page, $mpurl);

	$query = DB::query("SELECT * FROM ".DB::table('common_log')." WHERE $where ORDER BY dateline");
	while($log = DB::fetch($query)) {
		showtablerow('', array('', '', '', '', ''), array(
			$modulelist[$log['mid']],
			dgmdate($log['dateline'], 'Y-n-j H:i'),
			$log['action'],
			$log['ip'],
			dhtmlspecialchars($log['content']),
		));
	}

	showsubmit('', '', '', '', $multipage);
	showtablefooter();

}

?>