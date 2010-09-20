<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: batchmod.inc.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

require_once B_ROOT.'./source/class/shop.class.php';

@set_time_limit(100);
//刪除二次確認之前，寫入跳轉cookie
if(empty($_POST['confirmed'])) {
	ssetcookie('batch_referer', $_SERVER['HTTP_REFERER'], 300);
	$cookie_referer = $_G['cookie']['batch_referer'] = $_SERVER['HTTP_REFERER'];
} else {
	$cookie_referer = $_G['cookie']['batch_referer'];
}

$items = $opsql = $passuids = $tmpcont = $query = '';
$passuidarr = $itemarr = array();
$_POST['opcheck'] = intval($_POST['opcheck']);
$_POST['groupid'] = intval($_POST['groupid']);
$_POST['catid'] = intval($_POST['catid']);
$_POST['opshopid'] = intval($_POST['opshopid']);
$_POST['opallowreply'] = intval($_POST['opallowreply']);
$_POST['opdiscount'] = intval($_POST['opdiscount']);
$_POST['opdelete'] = intval($_POST['opdelete']);
$_POST['opowner'] = intval($_POST['opowner']);

if(empty($_POST['item']) && !in_array($_REQUEST['operation'], array('setalbumimg'))) {
	cpmsg('please_select_items', '', '', '', true, true);
} else {
	foreach($_POST['item'] as $value) {
		$value = intval($value);
		if($value>0) {
			$itemarr[] = $value;
			$items .= '\''.$value.'\' , ';
		}
	}
	$items = substr($items, 0, -2);
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

$wheresql = " itemid IN ($items)";

//刪除首頁、列表、以及對應物件的緩存
batchmodadmindeletecache($mname);

//操作方法
$opsql = 'UPDATE '.tname($mname.'items').' SET ';
switch($_REQUEST['operation']) {
	case 'display':
		changedisplayorder($_POST['display'], $mname, ' AND '.$wheresql);
		break;
	case 'owner':
		changeowner($_POST['opowner'], $itemarr);
		updatememberstats();
		cpmsg('message_success', $cookie_referer);
		break;
	case 'check':
		$opsql .= ' grade=\''.$_POST['opcheck'].'\'';
		$opsql .= $_POST['opcheck'] == "3" ?  ($mname=='shop' ?', groupid=\''.$_POST['newgroupid'].'\'':''): '';
		break;
	case 'movecat':
		if(!empty($_POST['groupid'])) {
			$opsql .= ' groupid=\''.$_POST['groupid'].'\'';
		}
		break;
	case 'changecat':
		if(!empty($_POST['catid']) && $_POST['catid'] > 0) {
			$opsql .= ' catid=\''.$_POST['catid'].'\'';
		} else {
			$opsql = '';
		}
		break;
	case 'moveshop':
		if(!empty($_POST['opshopid']) && is_numeric($_POST['opshopid'])) {
			$shopid = DB::result_first("SELECT itemid FROM ".tname('shopitems')." WHERE itemid='$_POST[opshopid]'");
			if(!empty($shopid)) {
				$opsql .= ' shopid=\''.$_POST['opshopid'].'\'';
			} else {
				cpmsg('moveshop_error', '', 'error');
			}
		} else {
			cpmsg('moveshop_error', '', 'error');
		}
		break;
	case 'discount':
		$opsql .= ' isdiscount=\''.$_POST['opdiscount'].'\'';
		break;
	case 'allowreply':
		$opsql .= ' allowreply=\''.$_POST['opallowreply'].'\'';
		break;
	case 'recommend':
		$opsql .= ' recommend=\''.$_POST['opallowreply'].'\'';
		break;
	case 'delete':
		if(empty($_POST['confirmed'])) {
			$extra_input = '';
			foreach($_POST['item'] as $value) {
				$extra_input .= '<input type="hidden" name="item[]" value="'.$value.'" />';
				if(in_array($mname, array('good', 'notice', 'consume', 'album')))
				$extra_input .= '<input type="hidden" name="item_shopid['.$value.']" value="'.$_POST['item_shopid'][$value].'" />';
			}
			$extra_input .= '<input type="hidden" name="operation" value="delete" /><input type="hidden" name="opdelete" value="'.$_POST['opdelete'].'" />';
			cpmsg('mod_delete_confirm', 'admin.php?action=batchmod&m='.$mname, 'form', $extra_input);//二次確認
		} else {
			if(in_array($mname, array('good', 'notice', 'consume', 'album', 'groupbuy'))) {
				// 刪除的時候更新計數
				$shopid_query = DB::query("select * from ".DB::table($mname.'items'). " where " .$wheresql);
				while ($__item = DB::fetch($shopid_query)) {
					shop::update_item_num($mname, $__item['shopid'], -1);
				}

				if($mname == 'album') {
					$innerjoinsql = '';
					$frombbs = ' and i.frombbs = 0';
				} else {
					$innerjoinsql = "INNER JOIN ".tname($mname.'message')." m ON i.itemid=m.itemid";
					$frombbs = '';
				}
			}
			delmitems($wheresql, $mname); //刪除信息
			if($mname=='shop') { updatememberstats();} //刪除完信息後更新
			$opsql = $wheresql = '';
			cpmsg('message_success', $cookie_referer);
		}
		break;
	case 'pass':
		$opsql .= ' grade=\'3\'';
		break;
	case 'refuse':
		$opsql .= ' grade=\'1\'';
		break;
	case 'update_album_info':
		update_album_info($_POST['subject'], $mname, $wheresql);
		break;
	case 'album_movecat':
		album_movecat($itemarr);
		break;
	case 'setalbumimg':
		setalbumimg();
		break;
	case 'passupdate':
		$check_txt = $_POST['check_txt'];
		$modelname = $cacheinfo['models']['modelname'];
		if($_POST['update'] == 1) {
			$opcheck =3;
			$pmtitle = lang('mod_updatetitle_pass');
			foreach($_POST[item] as $itemid) {
				$updateser = DB::result_first("SELECT `update` FROM ".tname("itemupdates")." WHERE itemid='$itemid' and type = '$mname'");
				$update = unserialize($updateser);
				$update['itemid'] = $itemid;
				if($mname == 'album') {
					foreach($update as $k=>$v) {
						if(in_array($k, array('subject', 'catid'))) {
							$setsqlarr[$k] = $v;
						}
					}
					if($setsqlarr) {
						$setsqlarr['updateverify'] = 0;
						updatetable($mname.'items', $setsqlarr, array('itemid'=>$itemid));//權限限制
						DB::query("UPDATE ".tname("photoitems")." SET `grade` = 3 WHERE grade = 0 AND albumid = '$itemid'");
						if(!empty($update['attr_ids'])) {
							DB::query("DELETE FROM ".tname('itemattribute')." WHERE itemid='$itemid'");
							require_once(B_ROOT.'./batch.attribute.php');
							setattributesettings($update['catid'], $itemid, $update['attr_ids']);
						}
					}
					DB::query("DELETE FROM ".tname('itemupdates')." WHERE `type` = 'album' AND itemid='$itemid'");

				} else {
					pkupdate($cacheinfo, $update);
				}
				unset($update,$setsqlarr,$updateser);
			}

		} else {
			$opcheck =2;
			$pmtitle = lang('mod_updatetitle_fail');
			DB::query("UPDATE ".tname($mname.'items')." SET `updateverify` = 0 WHERE itemid IN ($items)");
			DB::query("DELETE FROM ".tname('itemupdates')." WHERE `type` = '$modelname' AND itemid IN ($items)");

		}
		if($modelname != 'shop') {
			$opcheck = lang('passupdate_title_'.$opcheck.'_'.$mname);
		}
		gradechange($items, $opcheck, $check_txt, $mname);
		cpmsg('message_success', $cookie_referer);
		exit();
		break;
	case 'passcheck':
		$check_txt = $_POST['check_txt'];
		if($_POST['opcheck'] == 1) {
			DB::query("UPDATE ".tname($mname."items")." SET `grade` = 1 WHERE itemid IN ($items)");
		} elseif($_POST['opcheck'] == 3) {
			if($mname == "album") {
				DB::query("UPDATE ".tname("photoitems")." SET `grade` = 3 WHERE grade = 0 AND albumid IN ($items)");
			}
			DB::query("UPDATE ".tname($mname."items")." SET `grade` = 3 WHERE itemid IN ($items)");
			syncpost_check($mname, $items);
		}
		if($modelname != 'shop') {
			$opcheck = lang('passcheck_title_'.$_POST['opcheck'].'_'.$mname);
		}
		gradechange($items, $opcheck, $_POST['check_txt'], $mname);
		cpmsg('message_success', $cookie_referer);
		break;
	default:
		cpmsg('no_operation', '', '', '', true, true);
		break;
}
if(!empty($opsql) && !empty($wheresql)) {

	// 如果是批量移動所屬店舖，更新店舖中的計數,需要在模型的店舖沒有變之前先更新，
	if ($_REQUEST['operation'] == "moveshop") {
		foreach ($itemarr as $v) {
			if($mname == "album" && is_album_from_bbs($v)) {
				continue;
			}
			$old_shopid = shop::get_shopid_by_itemid($mname, $v);

			// 多選的情況下有可能轉到一個店舖中
			if ($old_shopid != $_POST['opshopid']) {
				shop::update_item_num($mname, $old_shopid, -1);
				shop::update_item_num($mname, $_POST['opshopid'], 1);
			}
		}
	}

	DB::query($opsql.' WHERE '.$wheresql);
	//選擇相冊移動店舖時，同時更改圖片的所屬店舖
	if($_REQUEST['operation']=='moveshop' && $mname=='album') {
		$opsql_photo = str_replace('albumitems ', 'photoitems ', $opsql);
		$wheresql_photo = str_replace(' itemid IN', ' albumid IN', $wheresql);
		DB::query($opsql_photo.' WHERE '.$wheresql_photo);
	}
}


if($_REQUEST['operation'] == 'check' && !empty($items)) {
	managelog($items, $_POST['opcheck'], $_POST['check_txt']);
}
if($mname == 'shop' && $_POST['operation']=='check') {
	//短信通知
	gradechange($items, $_POST['opcheck'], $_POST['check_txt']);
}

cpmsg('message_success', $cookie_referer);



/**
 * 查詢相冊是否是論壇導入
 *
 * @param int $albumid
 */
function is_album_from_bbs($albumid) {
	$res = DB::result_first("select frombbs from ".DB::table('albumitems')." where itemid=".$albumid);
	return $res == 1 ? true:false;
}

/**
 * 是否需要想論壇推送
 *
 * @param int $albumid
 */
function syncpost_check($mname, $items) {
	global $_SGLOBAL;
	$query = DB::query("SELECT itemid, shopid FROM ".DB::table($mname.'items')." WHERE itemid IN ($items)");
	while($result = DB::fetch($query)) {
		getpanelinfo($result['shopid']);
		if(!empty($_SGLOBAL['panelinfo']['syncfid'])) {
			require_once(B_ROOT.'./api/bbs_syncpost.php');
			syncpost($result['itemid'], $mname);
		}
	}
	return false;
}
?>