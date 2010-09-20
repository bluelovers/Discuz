<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: report.php 4372 2010-09-08 08:13:42Z yumiao $
 */

require_once('./common.php');

$result = '';
$id = intval($_REQUEST['id']);
$type = trim($_REQUEST['type']);
$reasonid = intval($_REQUEST['reasonid']);

if(empty($_G['uid'])) {
	$result = 'notlogin';
}
if(submitcheck('reportsubmit')) {
	$reason = shtmlspecialchars(trim($_POST['reason']));
	if(bstrlen($reason) < 1 || bstrlen($reason) > 250) {
		$result = 'message_length';
	} elseif(empty($reasonid) || $reasonid < 0) {
		$result = 'notselect_reasonid';
	} else {
		if(!$_G['myshopid']) {
			if(!empty($id) && !empty($type)) {
				if(DB::result_first("SELECT rid FROM ".tname('reportlog')." WHERE type='$type' AND itemid='$id' AND uid='$_G[uid]'")) {
					$result = 'only_allowto_report_once';
				} else {
					$shopid = ($type == 'shop') ? $id : DB::result_first("SELECT shopid FROM ".tname($type.'items')." WHERE itemid='$id'");
					if($shopid) {
						$setsqlarr = array(
							'type' => $type,
							'itemid' => $id,
							'uid' => $_G['uid'],
							'username' => $_G['username'],
							'status' => 1,
							'reasonid' => $reasonid,
							'reason' => $reason,
							'shopid' => $shopid,
							'dateline' => $_G['timestamp']
						);
						$rid = inserttable('reportlog', $setsqlarr, 1);
						if($rid) {
							DB::query("UPDATE ".tname($type.'items')." SET displayorder=displayorder+1, reportnum=reportnum+1 WHERE itemid='$id'");
						}
						$result = 'report_success';
					} else {
						$result = 'no_item_in_shop';
					}
				}
			} else {
				$result = 'not_get_datas';
			}
		} else {
			$result = 'manager_notallowto_report';
		}
	}
} else {
	$reasonarr = array();
	$query = DB::query("SELECT * FROM ".tname("reportreasons")." ORDER BY rrid ASC;");
	while($reason = DB::fetch($query)) {
		$reasonarr[] = $reason;
	}
}
include template('templates/store/default/report.html.php', 1);
?>