<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: brandlinks.inc.php 4442 2010-09-14 09:43:34Z yumiao $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$op = trim($_GET['op']);
$linkid = empty($_REQUEST['linkid']) ? '': intval($_REQUEST['linkid']);
$checkresults = array();

if(submitcheck('valuesubmit')) {

	$displayorder = !empty($_POST['displayorder']) ? intval($_POST['displayorder']) : 100;
	if(empty($_POST['name']) || strlen(trim($_POST['name'])) > 30 || empty($_POST['url'])) {
		array_push($checkresults, array('name'=>lang('addbrandlinks_name_error')));
	}
	if(empty($_POST['url'])) {
		array_push($checkresults, array('url'=>lang('addbrandlinks_url_error')));
	}
	if(!empty($checkresults)) {
		cpmsg('add_error', '', 'error', '', true, true, $checkresults);
	}
	$setsqlarr = array(
			'linkid' => $linkid,
			'displayorder' => $displayorder,
			'name' => trim($_POST['name']),
			'url' => trim($_POST['url']),
			'shopid' => intval($_POST['shopid'])
			);
	inserttable('brandlinks', $setsqlarr, '', 1);
	if(empty($linkid)) {
		itemnumreset('brandlinks', $setsqlarr['shopid']);
	}
	$_BCACHE->deltype('storelist', 'brandlinks', $_POST['shopid']);
	$url = trim($_POST['op']) == 'edit' ?  '?action=brandlinks' : '?action=add&m=brandlinks&op=add';
	cpmsg('addbrandlinks_success', $BASESCRIPT.$url, 'succeed');
}

if(submitcheck('deletesubmit')) {
	if(trim($_POST['operation']) == 'delete') {
		if(!empty($_POST['link'])) {
			$linkid = implode(',', $_POST['link']);

			foreach($_POST['item_shopid'] as $pitemid=>$pshopid) {
				if(in_array($pitemid, $_POST['link'])) {
					if(!empty($shopids[$pshopid])) {
						$shopids[$pshopid]++;
					} else {
						$shopids[$pshopid] = 1;
					}
				}
			}
			$subnum = 0;
			foreach($shopids as $ushopid=>$uitemnum) {
                $subnum = DB::result_first("SELECT count(*) FROM ".tname("brandlinks")." WHERE linkid IN ($linkid) and shopid = '$ushopid'");
				itemnumreset('brandlinks', $ushopid, $do = 'sub', $subnum);
			    $subnum = 0;
			}
			DB::query("DELETE FROM ".tname('brandlinks')." WHERE linkid IN ($linkid)");

		} else {
			cpmsg('notselect_item', '', 'error', '', true, true);
		}
	} elseif(trim($_POST['operation']) == 'display') {
		foreach($_POST['display'] as $key=>$value) {
			$key = intval($key);
			$value = intval($value);
			if($key > 0 && $value > -1) {
				DB::query('UPDATE '.tname('brandlinks').' SET displayorder=\''.$value.'\' WHERE linkid=\''.$key.'\';');
			}
		}
		$_BCACHE->deltype('storelist', 'brandlinks', $_POST['shopid']);
	}
	cpmsg('message_success', 'admin.php?action=brandlinks');
}
shownav('infomanage', 'nav_brandlinks'.$op, $_SGLOBAL['panelinfo']['subject']);
showsubmenu('nav_brandlinks'.$op);
if($op == 'add' || $op == 'edit') {
	if($op == 'add' && $_SGLOBAL['panelinfo']['group']['maxnumbrandlinks'] > 0 && $_SGLOBAL['panelinfo']['itemnum_brandlinks'] >= $_SGLOBAL['panelinfo']['group']['maxnumbrandlinks'])
		cpmsg('toomuchitem');
	showtips($op.'brandlinks_list_tips');
	showformheader('brandlinks');
	showtableheader('');
	if($op == 'edit') {
		if($linkid > 0 ) {
			$link = DB::fetch(DB::query("select * from ".tname('brandlinks')." where linkid='$linkid'"));
		} else {
			cpmsg('brandlinks_iderror');
		}
		$shopid = $link['shopid'];
	} else {
		$shopid = $_SGLOBAL['panelinfo']['itemid'];
	}
	$required = '<span style="color:red">*</span>';
	showsetting('brandlinks_name', 'name', $link['name'], 'text', '', '', '', '', $required);
	showsetting('brandlinks_url', 'url', $link['url'], 'text', '', '', '', '', $required);
	showhiddenfields(array('shopid' => $shopid));
	showsetting('brandlinks_displayorder', 'displayorder', $link['displayorder'], 'number');
	showhiddenfields(array('linkid' => $linkid));
	showhiddenfields(array('op' => $op));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
	exit;
}

if(submitcheck('filtersubmit')) {

	showtips('brandlinks_list_tips');
	showformheader('brandlinks');
	showtableheader('');
	showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'link\')" name="chkall" checked>', 'brandlinks_displayorder', 'brandlinks_name', 'brandlinks_url', 'brandlinks_shopname', 'operation'));

	$wheresql = '';
	$wheresql .= !empty($linkid) ? ' AND linkid=\''.$linkid.'\'' : '';
	$wheresql .= !empty($_REQUEST['name']) ? ' AND name LIKE \'%'.trim($_REQUEST['name']).'%\'' : '';
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
	$link = $linkarr = array();
	$tpp = 15;
	$page = $_GET['page'] > 0 ? intval($_GET['page']) : 1;
	$lstart = ($page - 1) * $tpp;
	$query = DB::query("SELECT count(linkid) AS count  FROM ".tname('brandlinks').$wheresql.";");
	$value = DB::fetch($query);
	foreach($_GET as $key=>$_value) {
		if(in_array($key, array('action', 'formhash', 'filtersubmit', 'linkid', 'name', 'shopid'))) {
			$url .= '&'.$key.'='.$_value;
		}
	}
	$url = '?'.substr($url, 1);
	$multipage = multi($value['count'], $tpp, $page, 'admin.php'.$url, $phpurl=1);
	$query = DB::query('SELECT * FROM '.tname('brandlinks').$wheresql.' ORDER BY displayorder LIMIT '.$lstart.', '.$tpp.';');
	while($link = DB::fetch($query)){
		$rowItem = array();
		$link['shopname'] = DB::result_first("SELECT subject FROM ".tname('shopitems')." WHERE itemid='$link[shopid]'");
		$rowItem[] = '<input class="checkbox" type="checkbox" name="link[]" value="'.$link['linkid'].'" checked/>'.(isset($link['shopid'])?'<input type="hidden" name="item_shopid['.$link[linkid].']" value="'.$link[shopid].'"/>':'');
		$rowItem[] = '<input name="display['.$link['linkid'].']" type="text" size="2" value="'.$link['displayorder'].'" />';
		$rowItem[] = $link['name'];
		$rowItem[] = $link['url'];
		$rowItem[] = $link['shopname'];
		$rowItem[] = '[<a href="admin.php?action=brandlinks&op=edit&linkid='.$link['linkid'].'">'.lang('edit').'</a>]';
		showtablerow('', array(), $rowItem);
	}
	showtablefooter();
	echo $multipage;
	showcommentmod();
	showformfooter();
	bind_ajax_form();
} elseif($_GET['action'] == 'brandlinks') {
	show_searchform_brandlinks();
}
?>