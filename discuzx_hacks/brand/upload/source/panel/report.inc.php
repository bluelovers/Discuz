<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: report.inc.php 4335 2010-09-06 03:59:41Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

shownav('infomanage', 'menu_report');
showsubmenu('menu_report');
showtips('report_list_tips');
showlistsearch_report();
showformheader('report');
showtableheader('report_listresult', 'notop');
showsubtitle(array('rid', 'reportusername', 'reporteditemname', 'reportreason', 'reportnum', 'rdateliane'));

$wheresql = '';
$wheresql .= !empty($_POST['reporttype']) ? ' AND type=\''.trim($_POST['reporttype']).'\'' : '';
$wheresql .= !empty($_POST['username']) ? ' AND username LIKE \'%'.trim($_POST['username']).'%\'' : '';
$wheresql .= ' AND shopid=\''.$_G['myshopid'].'\'';

if(!empty($wheresql)) {
	$wheresql = ' WHERE'.substr($wheresql, 4);
}

$report = $reportarr = array();
$tpp = 15;
$page = $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$rstart = ($page - 1) * $tpp;
$query = DB::query("SELECT count(rid) AS count  FROM ".tname('reportlog').";");
$value = DB::fetch($query);
$multipage = multi($value['count'], $tpp, $page, 'panel.php?action=report', $phpurl=1);
$query = DB::query("SELECT * FROM ".tname('reportlog').$wheresql." ORDER BY dateline DESC LIMIT ".$rstart.", ".$tpp.";");
while($report = DB::fetch($query)) {
	$report['shopname'] = DB::result_first("SELECT subject FROM ".tname('shopitems')." WHERE itemid='$report[shopid]'");
	$item = DB::fetch(DB::query("SELECT subject, reportnum FROM ".tname($report['type'].'items')." WHERE itemid='$report[itemid]'"));
	$report['itemname'] = $item['subject'];
	$report['num'] = $item['reportnum'];
	$report['shortreason'] = cutstr($report['reason'], 30, 1);
	$report['typename'] = $lang['header_'.$report['type']];
	$reportarr[] = $report;
}
foreach($reportarr as $value) {
	showtablerow('', array(), array($value['rid'], $value['username'], '<a href=\'store.php?id='.$value['shopid'].'&action='.$value['type'].'&xid='.$value['itemid'].'\' target=\'_blank\'>'.$value['itemname'].'</a>['.$value['typename'].']', '<span title="'.$value['reason'].'">'.$value['shortreason'].'</span>', $value['num'], date('Y-m-d', $value['dateline'])));
}
showtablefooter();
echo $multipage;
showformfooter();

?>