<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: report.inc.php 4324 2010-09-04 07:08:16Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

$op = trim($_GET['op']);
$rrid = intval($_REQUEST['rrid']);

if(empty($op)) {

	if(submitcheck('deletesubmit')) {

		if(trim($_POST['operation']) == 'delete') {
			if(!empty($_POST['report'])) {
				$rid = implode(',', $_POST['report']);
				DB::query("DELETE FROM ".tname('reportlog')." WHERE rid IN ($rid)");
				cpmsg('message_success', 'admin.php?action=report');
			} else {
				cpmsg('notselect_item');
			}
		}
	}

	if(submitcheck('filtersubmit')) {

		showformheader('report');
		showtableheader('');
		showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'report\')" name="chkall" checked>', 'rid', 'reportusername', 'reportedshopname', 'reporteditemname', 'reportreason', 'reportnum', 'rdateliane'));

		$wheresql = '';
		$wheresql .= !empty($_REQUEST['reporttype']) ? ' AND type=\''.trim($_REQUEST['reporttype']).'\'' : '';
		$wheresql .= !empty($_REQUEST['username']) ? ' AND username LIKE \'%'.trim($_REQUEST['username']).'%\'' : '';
		$wheresql .= !empty($_REQUEST['reasonid']) ? ' AND reasonid=\''.intval($_REQUEST['reasonid']).'\'' : '';
		$wheresql .= !empty($_REQUEST['shopid']) ? ' AND shopid=\''.intval($_REQUEST['shopid']).'\'' : '';
        if(!ckfounder($_G['uid'])) {
            $query = DB::query("SELECT itemid FROM ".DB::table("shopitems")." WHERE catid IN (".$_SGLOBAL['adminsession']['cpgroupshopcats'].")");
            while($result = DB::fetch($query)) {
                $shopitems[] = $result['itemid'];
            }
            if(!empty($shopitems)) {
                $wheresql .= ' AND shopid IN ('.implode(",", $shopitems).')';
            } else {
                $wheresql .= ' AND shopid IN (0)';
            }
        }
        if(!empty($wheresql)) {
			$wheresql = ' WHERE'.substr($wheresql, 4);
		}
		$report = $reportarr = array();
		$tpp = 15;
		$page = $_GET['page'] > 0 ? intval($_GET['page']) : 1;
		$rstart = ($page - 1) * $tpp;
		$query = DB::query("SELECT count(rid) AS count  FROM ".tname('reportlog').$wheresql.";");
		$value = DB::fetch($query);
		foreach($_GET as $key=>$_value) {
			if(in_array($key, array('action', 'formhash', 'filtersubmit', 'reporttype', 'username', 'shopid'))) {
				$url .= '&'.$key.'='.$_value;
			}
		}
		$url = '?'.substr($url, 1);
		$multipage = multi($value['count'], $tpp, $page, 'admin.php'.$url, $phpurl=1);
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
			showtablerow('', array(), array('<input class="checkbox" type="checkbox" name="report[]" value="'.$value['rid'].'" checked/>', $value['rid'], $value['username'], '<a href=\'store.php?id='.$value['shopid'].'\' target=\'_blank\'>'.$value['shopname'].'</a>', '<a href=\'store.php?id='.$value['shopid'].'&action='.$value['type'].'&xid='.$value['itemid'].'\' target=\'_blank\'>'.$value['itemname'].'</a>['.$value['typename'].']', '<span title="'.$value['reason'].'">'.$value['shortreason'].'</span>', $value['num'], date('Y-m-d', $value['dateline'])));
		}
		showtablefooter();
		echo $multipage;
		showcommentmod();
		showformfooter();
		bind_ajax_form();
	} else {
		shownav('infomanage', 'report_list');
		showsubmenu('report_list', array(
			array('report_list', 'report', '1'),
			array('modreason_list', 'report&op=reasonlist', '0')
		));
		showtips('report_list_tips');
		show_searchform_report();
	}

} elseif($op == 'reasonlist') {

	if(submitcheck('deletesubmit')) {

		if(trim($_POST['operation']) == 'delete') {
			if(!empty($_POST['reasonids'])) {
				$rrid = implode(',', $_POST['reasonids']);
				DB::query("DELETE FROM ".tname('reportreasons')." WHERE rrid IN ($rrid)");
				cpmsg('message_success', 'admin.php?action=report&op=reasonlist');
			} else {
				cpmsg('notselect_item');
			}
		}
	}
	shownav('infomanage', 'modreason_list');
	showsubmenu('modreason_list', array(
		array('report_list', 'report', '0'),
		array('modreason_list', 'report&op=reasonlist', '1')
	));
	showtips('modreason_list_tips');

	$reasonnum = 0;
	$reasonlist =array();
	$tpp = 15;
	$page = $_GET['page'] > 0 ? intval($_GET['page']) : 1;
	$rstart = ($page - 1) * $tpp;
	$reasonnum = DB::result_first("SELECT count(rrid) FROM ".tname('reportreasons').";");
	$multipage = multi($reasonnum, $tpp, $page, 'admin.php?action=report&op=reasonlist', $phpurl=1);
	$query = DB::query("SELECT * FROM ".tname("reportreasons")." ORDER BY rrid ASC LIMIT ".$rstart.", ".$tpp.";");
	while($result = DB::fetch($query)) {
		$reasonlist[] = $result;
	}
	showformheader('report&op=reasonlist');
	showtableheader('');
	showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'reasonids\')" name="chkall" checked>', 'reasonid', 'reasoncontent', 'operation'));
	foreach($reasonlist as $reason) {
		showtablerow('', array(), array(
				'<input name="reasonids['.$reason['rrid'].']" type="checkbox" value="'.$reason['rrid'].'" checked/>',
				$reason['rrid'],
				$reason['content'],
				'[<a href="admin.php?action=report&op=editreason&rrid='.$reason['rrid'].'">'.lang('reason_edit').'</a>]',
				));
	}
	echo '<tr class="hover"><td></td><td><a href="?action=report&op=addreason" class="addtr">'.lang('reason_add').'</a></td><td></td><td></td><td></td></tr>';
	showtablefooter();
	echo $multipage;
	showcommentmod();
	showformfooter();
	bind_ajax_form();

} elseif($op == 'addreason' && submitcheck('valuesubmit')) {

	$content = trim($_POST['content']);
	DB::query("INSERT INTO ".tname("reportreasons")." (`content`) VALUES ('$content')");
	cpmsg('message_success', 'admin.php?action=report&op=reasonlist');

} elseif($op == 'editreason' && submitcheck('valuesubmit')) {

	$content = trim($_POST['content']);
	DB::query("UPDATE ".tname("reportreasons")." SET content='$content' WHERE rrid='$rrid'");
	cpmsg('message_success', 'admin.php?action=report&op=reasonlist');

} elseif($op == 'addreason' || $op == 'editreason') {

	if($op == 'editreason') {
		$reason = DB::fetch(DB::query("SELECT * FROM ".tname("reportreasons")." WHERE rrid='$rrid' LIMIT 1"));
	}

	shownav('infomanage', 'modreason_list');
	showsubmenu('modreason_list', array(
		array('report_list', 'report', '0'),
		array('modreason_list', 'report&op=reasonlist', '1')
	));
	showtips('modreason_list_tips');
	showformheader('report&op='.$_GET['op']);
	showtableheader('');
	showsetting('reasoncontent', 'content', $reason['content'], 'text');
	showhiddenfields(array('rrid' => $reason['rrid']));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}

?>