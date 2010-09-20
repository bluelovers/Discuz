<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: store.php 4406 2010-09-13 07:48:43Z fanshengshuai $
 */

require_once('./common.php');
require_once(B_ROOT.'./source/adminfunc/brandpost.func.php');

$_SGLOBAL['shopid'] = $shopid = intval($_GET['id']);
$shop = $gatherarr = $columnsallinfoarr = array();

$_GET['xid'] = intval($_GET['xid']);

$cacheinfo = getmodelinfoall('modelname', 'shop');
if(empty($cacheinfo['models'])) {
	showmessage('visit_the_channel_does_not_exist', '/');
}
$modelsinfoarr = $cacheinfo['models'];
$categories = $cacheinfo['categories'];


// 允許的方法
$acs = array('index', 'good', 'notice', 'consume', 'album', 'map', 'groupbuy');
if(!in_array($_GET['action'], $acs)) {
	$_GET['action'] = 'index';
}
$action = $_GET['action'];

// 當前導航樣式
if(in_array($action, array('good', 'notice', 'consume', 'album', 'map', 'groupbuy'))) {
	$cur_store_style = $action;
	if($action == "good") {
		$active['goods'] = ' class="active"';
		$cur_store_style = 'goods';
	} else {
		$active[$action] = ' class="active"';
	}
} else {
	$active['street'] = ' class="active"';
}

$isshowmore = 0;
$columnsinfoarr = array('fixed'=>array(), 'message'=>array());
if($shopid) {
	$shop = $_BCACHE->getshopinfo($shopid);
	if(!empty($shop['score'])) {
		$shop['score'] = round(($shop['score'] / $shop['remarknum']), 2);
		$shop['roundingscore'] = round($shop['score']);
		for($i = 1; $i <= 8; $i++) {
			if($shop['score'.$i]) {
				$shop['stripwidth'.$i] = (round(($shop['score'.$i] / $shop['remarknum']), 1) * 20).'%';
				$shop['score'.$i] = round(($shop['score'.$i] / $shop['remarknum']), 2);
			}
		}
	}
	$allowreply = $shop['allowreply'];
	$shop['shopurl'] = $_G['setting']['urltype']==3 ? 'store-'.$shop['itemid'].'.html' : 'store.php?id='.$shop['itemid'];

	$_BCACHE->cachesql('brandlinkslist', 'SELECT * FROM '.tname('brandlinks')." WHERE shopid='$shop[itemid]' AND displayorder>'-1' ORDER BY displayorder ASC", 0, 0, 1000, 0, 'storelist', 'brandlinks', $shop['itemid']);
	foreach($_SBLOCK['brandlinkslist'] as $result) {
		$result['shortname'] = cutstr($result['name'], 35);
		$brandlinkslist[] = $result;
	}
}

if($shop['grade'] == 0) {
	showmessage('shop_optpass');
} elseif($shop['grade'] < 2) {
	showmessage('shop_close');
} else {
	if($_GET['action'] == 'index'){
		$ck_item_id = $_GET['id'];
	} else {
		$ck_item_id = $_GET['xid'];
	}
	if($_GET['action'] == 'index') {
		ck_item_status($ck_item_id, $_GET['action'], 1, $_GET['id']);
	}
}

// 查詢自定義字段內容
if(empty($shop)) {
	showmessage('not_found', 'index.php');
} else {
	if(!empty($cacheinfo['columns'])) {
		foreach($cacheinfo['columns'] as $temp) {
			$tmpvalue = trim($shop[$temp['fieldname']]);
			if((empty($temp['isfile']) && strlen($tmpvalue) > 0) || (!empty($temp['isfile']) && $tmpvalue != 0)) {
				if(preg_match("/^(select|radio)$/i", $temp['formtype']) || (preg_match("/^(VARCHAR|CHAR)$/i", $temp['fieldtype']) && $temp['fieldlength'] <= 20) || (preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE)$/i", $temp['fieldtype']) && $temp['formtype'] != 'file')) {
					$arraytype = 'fixed';
				} else {
					$arraytype = 'message';
				}

				if($temp['formtype'] == 'checkbox') {
					$tmpvalue = explode("\n", $shop[$temp['fieldname']]);
				} elseif($temp['formtype'] == 'textarea' && empty($temp['ishtml'])) {
					if($arraytype == 'fixed') {
						$tmpvalue = str_replace("\n", '&nbsp;', $shop[$temp['fieldname']]);
					} else {
						$tmpvalue = str_replace("\n", '<br />', $shop[$temp['fieldname']]);
					}
				}

				$temp['filepath'] = '';
				if(!empty($temp['isimage']) || !empty($temp['isflash'])) {
					$temp['filepath'] = A_URL.'/'.$tmpvalue;
				} elseif(!empty($temp['isfile'])) {
					$temp['filepath'] = rawurlencode(authcode('shop,'.$tmpvalue, 'ENCODE'));
				}
				$columnsallinfoarr[$temp['fieldname']] = $columnsinfoarr[$arraytype][] = array(
					'fieldname'	=>	$temp['fieldname'],
					'fieldcomment'	=>	$temp['fieldcomment'],
					'fieldtype'	=>	$temp['fieldtype'],
					'formtype'	=>	$temp['formtype'],
					'ishtml'	=>	$temp['ishtml'],
					'isfile'	=>	$temp['isfile'],
					'isimage'	=>	$temp['isimage'],
					'isflash'	=>	$temp['isflash'],
					'filepath'	=>	$temp['filepath'],
					'value'	=>	$tmpvalue
				);
			}
		}
	}
}

// header
$shop['subjectimage'] = str_replace('static/image/nophoto.gif', 'static/image/shoplogo.gif', $shop['subjectimage']);
$shop['banner'] = getattachurl($shop['banner'], 0, B_URL.'/static/image/shopbanner.gif');
$shop['windowsimg'] = getattachurl($shop['windowsimg'], 0, B_URL.'/static/image/shopwindow.gif');
if(empty($shop['mapapimark'])) {
	$shop['mapapimark'] = "(39.917,116.397)";
}

// 分類
$guidearr = $thecat = array();
$thecat['upid'] = $_SGLOBAL['shopcates'][$shop['catid']]['upid'];
$thecat['upname'] = $_SGLOBAL['shopcates'][$thecat['upid']]['name'];
$thecat['catid'] = $shop['catid'];
$thecat['name'] = $_SGLOBAL['shopcates'][$thecat['catid']]['name'];
if(!empty($thecat['upname'])) {
	$guidearr[] = array('url' => 'street.php?catid='.$thecat['upid'], 'name' => $thecat['upname']);
}
if(!empty($thecat['name'])) {
	$guidearr[] = array('url' => 'street.php?catid='.$thecat['catid'], 'name' => $thecat['name']);
}
$rootcatid = getrootcatid($shop['catid']);
if($_SGLOBAL['shopcates'][$rootcatid]['commtmodel'] == 1) {
	$_SGLOBAL['commentmodel'] = getcommentmodel($_SGLOBAL['shopcates'][$rootcatid]['cmid']);
}

if(!empty($cacheinfo['fielddefault']['subject'])) $lang['subject'] = $cacheinfo['fielddefault']['subject'];
if(!empty($cacheinfo['fielddefault']['subjectimage'])) $lang['photo_title'] = $cacheinfo['fielddefault']['subjectimage'];
if(!empty($cacheinfo['fielddefault']['catid'])) $lang['system_catid'] = $cacheinfo['fielddefault']['catid'];
if(!empty($cacheinfo['fielddefault']['message'])) $lang['content'] = $cacheinfo['fielddefault']['message'];

// 自定義類別
if(!empty($cacheinfo['columns'])) {
	foreach($cacheinfo['columns'] as $tmpvalue) {
		if(!empty($tmpvalue['fielddata'])) {
			$temparr = explode("\r\n", $tmpvalue['fielddata']);
			if($tmpvalue['formtype'] != 'linkage') {
				$gatherarr[$tmpvalue['fieldname']] = $temparr;
			}
		}
	}
}

// 初始化店舖的標題和關鍵字，放在包含模型文件前面
$seo_title = $shop['subject'].' - '.$_G['setting']['sitename'];
$seo_title = strip_tags($seo_title);

if ($shop['keywords']) {
	$seo_keywords = $shop['keywords'];
} else {
	$seo_keywords = $shop['subject'].','.$_G['setting']['seokeywords'];
}


if($action == 'map') {

} elseif($action != 'map' && $action != 'index') {

	require_once(B_ROOT.'./source/store/'.$action.'.inc.php');
	$act_item = $$action;
	$comment_itemid = $act_item['itemid'];
	$type=$action;
	$xid = intval($_GET['xid']);
	if ($xid > 0) {
		DB::query("UPDATE ".tname($action."items")." set viewnum = viewnum + 1 where itemid=".$xid);
	}
} else {
	$comment_itemid = $shop['itemid'];
	$type = 'shop';
	// 判斷是否已經賦值了Meta中的Message信息，放在包含文件後面
	if(!empty($shop['description'])) {
		$seo_description = $shop['description'];
	} else {
		$seo_description = cutstr(strip_tags($shop['message']), 150, $havedot=1);
	}
}

if($action == 'index' || $_GET['xid']) {
	$perpage = $_G['setting']['commentperpage'];
	$page = $_GET['page'];
	// 評論
	if($action == 'index') {
		$listcount = $shop['replynum'];
	}

	$repeatids = $commentlistarr = array();
	$commentnum = 0;
	$sql = "SELECT c.*, r.message AS recomment, r.dateline AS replytime, cs.score, cs.score1, cs.score2, cs.score3, cs.score4, cs.score5, cs.score6, cs.score7, cs.score8 FROM ".tname('spacecomments')." c LEFT JOIN ".tname('spacecomments')." r ON c.cid = r.upcid LEFT JOIN ".tname('commentscores')." cs ON c.cid = cs.cid WHERE c.type = '$type' AND c.upcid = 0 AND c.itemid='$comment_itemid' AND c.status='1' ORDER BY c.cid ".($_G['setting']['commorderby']?'DESC':'ASC');
	$_BCACHE->cachesql('shopcomment', $sql, 0, 1, $_G['setting']['commentperpage'], 0, 'storelist', 'comment', $comment_itemid);
	$commentnum = $_SBLOCK['shopcomment_listcount'];
	$multipage = $_SBLOCK['shopcomment_multipage'];
	foreach($_SBLOCK['shopcomment'] as $comment) {
		$execute = true;
		if($comment['isprivate'] == 1) {
			if(!in_array($_G['uid'], array($comment['authorid'], $comment['shopuid']))) {
				$execute = false;
			}
		}
		if($execute) {
			if($comment['recomment']) {
				$currentmessage = array();
				preg_match_all ("/\<div class=\"new\">(.+)?\<\/div\>/is", $comment['recomment'], $currentmessage, PREG_SET_ORDER);
				if(!empty($currentmessage)) $comment['recomment'] = $currentmessage[0][0];
				$comment['recomment'] = preg_replace("/\<div class=\"quote\"\>\<blockquote.+?\<\/blockquote\>\<\/div\>/is", '',$comment['recomment']);
			}
			$comment = formatcomment($comment, $repeatids);
			$commentlistarr[] = $comment;
			if(!empty($comment['firstcid']) && !in_array($comment['firstcid'], $repeatids)) {
				$repeatids[] = $comment['firstcid'];
			}
		}
	}
	$commentlist = $commentlistarr;
}

$tips = array();
$tips = explode("\n",$shop['tips']);
$mouseover[$action] = ' class="mouseover"';
$actionurl = '';
if($action != 'index' && $action != 'map') {
	$actionurl = "&action=$action";
}
if($_GET['xid']) {
	$xidurl = "&xid=$_GET[xid]";
}
$stuffurl = "store.php?id=$shop[itemid]".$actionurl.$xidurl."&op=view";
$theurl = "store.php?id=$shop[itemid]".$actionurl.$xidurl;
if($_GET['op']=='preview' && ($shop['uid']==$_G['uid'] || ckfounder($_G['uid'])) && file_exists(B_ROOT.'./templates/store/'.$_GET['theme'].'/preview.jpg')) {
	$btheme = $_GET['theme'];
} elseif($shop['themeid']>0 && ($shop['themeid']=intval($shop['themeid'])) && file_exists(B_ROOT.'./templates/store/t'.$shop['themeid'].'/preview.jpg')) {
	$btheme = 't'.$shop['themeid'];
} else {
	$btheme = 'default';
}

$shop['nav'] = loadClass("nav")->get_shop_nav($shopid);

include brandtheme('header.html.php', $btheme);
include brandtheme($action.'.html.php', $btheme);
include template('templates/site/default/footer.html.php', 1);

ob_out(); //正則處理url/模板

function brandtheme($tpl, $btheme) {
	if($btheme!='default' && file_exists(B_ROOT.'./templates/store/'.$btheme.'/'.$tpl)) {
		return template('templates/store/'.$btheme.'/'.$tpl, 1);
	} else {
		return template('templates/store/default/'.$tpl, 1);
	}
}

function freshcookie($action,$shopid) {
	global $_G, $_SC;

	$shopid = $action.$shopid;
	$isupdate = 1;
	$old = empty($_G['cookie']['supe_refresh_items'])?0:trim($_G['cookie']['supe_refresh_items']);
	$shopidarr = explode('_', $old);
	if(in_array($shopid, $shopidarr)) {
		$isupdate = 0;
	} else {
		$shopidarr[] = trim($shopid);
		ssetcookie('supe_refresh_items', implode('_', $shopidarr));
	}
	if(empty($_G['cookie']['supe_refresh_items'])) $isupdate = 0;

	return $isupdate;
}

function updateviewnum($action,$shopid) {
	global $_G, $_SGLOBAL;

	DB::query('UPDATE '.tname($action.'items').' SET viewnum=viewnum+1 WHERE itemid=\''.$shopid.'\'');
}

?>