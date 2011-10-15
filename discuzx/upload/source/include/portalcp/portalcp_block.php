<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_block.php 24302 2011-09-06 07:25:27Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('function/block');
$oparr = array('block', 'data', 'style', 'itemdata', 'setting', 'remove', 'item', 'blockclass',
				'getblock', 'thumbsetting', 'push', 'recommend', 'verifydata', 'managedata',
				'saveblockclassname', 'saveblocktitle', 'convert', 'favorite', 'banids');
$op = in_array($_GET['op'], $oparr) ? $_GET['op'] : 'block';
$_GET['from'] = $_GET['from'] == 'cp' ? 'cp' : null;
$allowmanage = $allowdata = 0;

$block = array();
$bid = !empty($_G['gp_bid']) ? intval($_G['gp_bid']) : 0;
if($bid) {
	$block = DB::fetch_first("SELECT b.* FROM ".DB::table('common_block')." b WHERE b.bid='$bid'");
	if(empty($block)) {
		showmessage('block_not_exist');
	}
	$_G['block'][$bid] = $block;
	$blockperm = getblockperm($bid);
	if($blockperm['allowmanage']) {
		$allowmanage = 1;
		$allowdata = 1;
	}
	if ($blockperm['allowrecommend'] && !$blockperm['needverify']) {
		$allowdata = 1;
	}
}

$block['param'] = empty($block['param'])?array():unserialize($block['param']);
if(empty($block['bid'])) {
	$bid = 0;
}

$_GET['classname'] = !empty($_GET['classname']) ? $_GET['classname'] : ($block ? $block['blockclass'] : 'html_html');
$theclass = block_getclass($_GET['classname'], true);
$theclass['script'] = isset($theclass['script']) ? $theclass['script'] : array();
if(!empty($_GET['styleid']) && isset($theclass['style'][$_GET['styleid']])) {
	$thestyle = $theclass['style'][$_GET['styleid']];
} elseif(isset($theclass['style'][$block['styleid']])) {
	$_GET['styleid'] = intval($block['styleid']);
	$thestyle = $theclass['style'][$_GET['styleid']];
} else {
	$_GET['styleid'] = 0;
	$thestyle = (array)unserialize($block['blockstyle']);
}
$_GET['script'] = !empty($_GET['script']) && isset($theclass['script'][$_GET['script']])
		? $_GET['script']
		: (!empty($block['script']) ? $block['script'] : key($theclass['script']));

$blocktype = (!empty($_GET['blocktype']) || !empty($block['blocktype'])) ? 1 : 0;
$nocachetime = in_array($_GET['script'], array('blank', 'line', 'banner', 'vedio', 'google')) ? true : false;
$is_htmlblock = ($_GET['classname'] == 'html_html') ? 1 : 0;
$showhtmltip = false;
if($op == 'data' && $is_htmlblock) {
	$op = 'block';
	$showhtmltip = true;
}
$block['blockclass'] = empty($block['blockclass']) ? $_GET['classname'] : $block['blockclass'];
$is_recommendable = block_isrecommendable($block);

if($op == 'block') {
	if($bid && !$allowmanage) {
		showmessage('block_edit_nopermission');
	}
	if(!$bid) {
		list($tpl, $id) = explode(':', $_GET['tpl']);
		if(trim($tpl)=='portal/portal_topic_content') {
			if(!$_G['group']['allowaddtopic'] && !$_G['group']['allowmanagetopic']) {
				showmessage('block_edit_nopermission');
			}
		} elseif(!$_G['group']['allowdiy']) {
			showmessage('block_edit_nopermission');
		}
	}

	if(submitcheck('blocksubmit')) {
		$_POST['cachetime'] = intval($_POST['cachetime']) * 60;
		$_POST['styleid'] = intval($_POST['styleid']);
		$_POST['shownum'] = intval($_POST['shownum']);
		$_POST['picwidth'] = $_POST['picwidth'] ? intval($_POST['picwidth']) : 0;
		$_POST['picheight'] = $_POST['picheight'] ? intval($_POST['picheight']) : 0;
		$_POST['script'] = isset($theclass['script'][$_POST['script']]) ? $_POST['script'] : key($theclass['script']);
		$_POST['a_target'] = in_array($_POST['a_target'], array('blank', 'top', 'self')) ? $_POST['a_target'] : 'blank';
		$_POST['dateformat'] = in_array($_POST['dateformat'], array('Y-m-d', 'm-d', 'H:i', 'Y-m-d H:i')) ? $_POST['dateformat'] : 'Y-m-d';
		$_POST['isblank'] = intval($_POST['isblank']);
		$summary = getstr($_POST['summary'], '', 1, 1, 0, 1);
		if($summary) {
			$tag = block_ckeck_summary($summary);
			if($tag != $summary) {
				$msg = lang('portalcp', 'block_diy_summary_html_tag').$tag.lang('portalcp', 'block_diy_summary_not_closed');
				showmessage($msg);
			}
		}

		$_POST['shownum'] = $_POST['shownum'] > 0 ? $_POST['shownum'] : 10;
		$_POST['parameter']['items'] = $_POST['shownum'];
		include_once libfile('function/home');
		$setarr = array(
			'name' => getstr($_POST['name'], 255, 1, 1, 0, 0),
			'summary' => $summary,
			'styleid' => $_POST['styleid'],
			'script' => $_POST['script'],
			'cachetime' => intval($_POST['cachetime']),
			'punctualupdate' => !empty($_POST['punctualupdate']) ? '1' : '0',
			'shownum' => $_POST['shownum'],
			'picwidth' => $_POST['picwidth'] && $_POST['picwidth'] > 8 && $_POST['picwidth'] < 1960 ? $_POST['picwidth'] : 0,
			'picheight' => $_POST['picheight'] && $_POST['picheight'] > 8 && $_POST['picheight'] < 1960 ? $_POST['picheight'] : 0,
			'target' => $_POST['a_target'],
			'dateuformat' => !empty($_POST['dateuformat']) ? '1' : '0',
			'dateformat' => $_POST['dateformat'],
			'hidedisplay' => $_POST['hidedisplay'] ? '1' : '0',
			'dateline' => TIMESTAMP,
			'isblank' => $_POST['isblank']
		);

		$picdata = array();
		if(!empty($_FILES)) {
			foreach($_FILES as $varname => $file) {
				if($file['tmp_name']) {
					$result = pic_upload($file, 'portal');
					$pic = 'portal/'.$result['pic'];
					$strbid = $bid ? $bid : '{bid}';
					$picdata[] = "('$strbid', '$pic', '$result[remote]', '1')";
					$pic = $result['remote'] ? $_G['setting']['ftp']['attachurl'].$pic : $_G['setting']['attachurl'].$pic;
					$_POST['parameter'][$varname] = $pic;
				}
			}
		}
		$setarr['param'] = addslashes(serialize($_POST['parameter']));

		if($bid) {
			DB::update('common_block', $setarr, array('bid'=>$bid));
		} else {
			$setarr['blockclass'] = $_GET['classname'];
			$setarr['uid'] = $_G['uid'];
			$setarr['username'] = $_G['username'];
			$setarr['notinherited'] = 0;
			if($blocktype == 1) {
				$setarr['blocktype'] = '1';
			}
			$bid = DB::insert('common_block', $setarr, true);
		}

		if(!empty($picdata)) {
			$str = implode(',', $picdata);
			$str = str_replace('{bid}', $bid, $str);
			DB::query('INSERT INTO '.DB::table('common_block_pic')." (bid, pic, picflag, `type`) VALUES $str");
		}

		$_G['block'][$bid] = DB::fetch_first("SELECT * FROM ".DB::table('common_block')." WHERE bid='$bid'");
		block_updatecache($bid, true);
		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=block&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	loadcache('blockconvert');
	$block['script'] = isset($block['script']) ? $block['script'] : $_GET['script'];
	$settings = block_setting($_GET['classname'], $block['script'], $block['param']);
	$scriptarr = array($block['script'] => ' selected');
	$stylearr = array($_GET['styleid'] => ' selected');

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$block['cachetime_min'] = intval($block['cachetime'] / 60);
	$targetarr[$block['target']] = ' selected';

	$dateformats = block_getdateformats($block['dateformat']);

	$block['summary'] = htmlspecialchars($block['summary']);
	$blockclassname = '';
	$blockclass = $block['blockclass'] ? $block['blockclass'] : $_G['gp_classname'];
	$arr = explode('_', $blockclass);
	if(count($arr) == 2) {
		$blockclassname = $_G['cache']['blockclass'][$arr[0]]['subs'][$blockclass]['name'];
	}
	$blockclassname = empty($blockclassname) ? $blockclass : $blockclassname;

} elseif($op == 'banids') {
	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	if(isset($_G['gp_bannedids']) && $block['param']['bannedids'] != $_G['gp_bannedids']) {
		$arr = explode(',', $_G['gp_bannedids']);
		$arr = array_map('intval', $arr);
		$arr = array_filter($arr);
		$_G['gp_bannedids'] = implode(',', $arr);
		$block['param']['bannedids'] = $_G['gp_bannedids'];
		DB::update('common_block', array('param'=>addslashes(serialize($block['param']))), array('bid'=>$bid));
		$_G['block'][$bid] = $block;
		block_updatecache($bid, true);
	}

	showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=data&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));

} elseif($op == 'data') {
	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	if(submitcheck('updatesubmit')) {
		if($_POST['displayorder']) {
			asort($_POST['displayorder']);
			$orders = $ids = array();
			$order = 1;
			foreach($_POST['displayorder'] as $k=>$v) {
				$k = intval($k);
				$ids[] = $k;
				$orders[$k] = $order;
				$order++;
			}
			$items = array();
			$query = DB::query('SELECT itemid, displayorder, itemtype FROM '.DB::table('common_block_item')." WHERE bid='$bid' AND itemid IN (".dimplode($ids).')');
			while(($value=DB::fetch($query))) {
				$items[$value['itemid']] = $value;
			}
			foreach($items as $key=>$value) {
				$itemtype = !empty($_POST['locked'][$key]) ? '1' : '0';
				if($orders[$key] != $value['displayorder'] || $itemtype != $value['itemtype']) {
					DB::update('common_block_item', array('displayorder'=>$orders[$key], 'itemtype'=>$itemtype), array('itemid'=>$key));
				}
			}
		}
		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=data&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	$itemlist = array();
	if($bid) {
		$query = DB::query('SELECT * FROM '.DB::table('common_block_item')." WHERE bid='$bid' ORDER BY displayorder");
		$preorders = array();
		while(($value = DB::fetch($query))) {
			if($value['itemtype']==1 && $value['enddate'] && $value['enddate'] <= TIMESTAMP) {
				continue;
			}
			$value['ispreorder'] = false;
			if($value['itemtype']==1) {
				if($value['startdate'] > TIMESTAMP) {
					$value['ispreorder'] = true;
				} else {
					$preorders[$value['displayorder']] = $value['itemid'];
				}
			}
			$itemlist[$value['itemid']] = $value;
		}
		if($preorders) {
			foreach($itemlist as $key=>$value) {
				if(isset($preorders[$value['displayorder']]) && $value['itemid'] != $preorders[$value['displayorder']]) {
					unset($itemlist[$key]);
				}
			}
		}
	}

	$block['param']['bannedids'] = !empty($block['param']['bannedids']) ? stripslashes($block['param']['bannedids']) : '';

} elseif($op == 'style') {
	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if(submitcheck('stylesubmit')) {
		$_POST['name'] = trim($_POST['name']);
		$arr = array(
			'name' => $_POST['name'],
			'blockclass' => $_GET['classname'],
		);
		$_POST['template'] = stripslashes($_POST['template']);

		include_once libfile('function/block');
		block_parse_template($_POST['template'], $arr);
		if(!empty($_POST['name'])) {
			$styleid = DB::insert('common_block_style', daddslashes($arr), true);
		}
		$arr['fields'] = unserialize($arr['fields']);
		$arr['template'] = unserialize($arr['template']);
		$arr = serialize($arr);
		$arr = addslashes($arr);
		DB::update('common_block', array('blockstyle'=>$arr, 'styleid'=>'0'), array('bid'=>$bid));

		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=style&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	$blockstyle = array();
	if(!empty($block['styleid'])) {
		$blockstyle = block_getstyle($block['styleid']);
	} else {
		$blockstyle = unserialize($block['blockstyle']);
	}
	$template = block_build_template($blockstyle['template']);

	$samplecode = '';
	if($block['hidedisplay']) {
		$samplecode = '<ul>\n'
			.'<!--{loop $_G[block_1] $key $value}-->\n'
			.'<li><a href="$value[url]">$value[title]</a></li>\n'
			.'<!--{/loop}-->\n'
			.'</ul>';
		$samplecode = htmlspecialchars($samplecode);
		$samplecode = str_replace('\n', '<br />', $samplecode);
	}

} elseif($op == 'itemdata') {

	if(!$bid ||  (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}
	if(!$is_recommendable) {
		showmessage('block_no_recommend_library');
	}

	$theurl = 'portal.php?mod=portalcp&ac=block&op=itemdata';
	$perpage = 20;
	$page = max(1,intval($_GET['page']));
	$start = ($page-1)*$perpage;
	if($start<0) $start = 0;

	if(submitcheck('deletesubmit')) {
		$ids = array();
		if(!empty($_POST['ids'])) {
			$dataids = dimplode($_POST['ids']);
			$query = DB::query('SELECT dataid FROM '.DB::table('common_block_item_data')." WHERE bid='$bid' AND dataid IN ($dataids)");
			while(($value=DB::fetch($query))) {
				$ids[] = intval($value['dataid']);
			}
		}
		if($ids) {
			DB::query('DELETE FROM '.DB::table('common_block_item_data')." WHERE dataid IN (".dimplode($ids).")");
		}
		showmessage('do_success', "portal.php?mod=portalcp&ac=block&op=itemdata&bid=$bid&page=$page");
	}

	$datalist = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_block_item_data')." WHERE bid='$bid' AND isverified='1' ORDER BY stickgrade DESC, verifiedtime DESC LIMIT $start, $perpage");
	while(($value=DB::fetch($query))) {
		$value['verifiedtime'] = dgmdate($value['verifiedtime']);
		$datalist[$value['dataid']] = $value;
	}

} elseif($op == 'setting') {

	if(($bid && !$allowmanage)) {
		showmessage('block_edit_nopermission');
	}

	$settings = array();
	if($theclass['script'][$_GET['script']]) {
		$settings = block_setting($_GET['classname'], $_GET['script'], $block['param']);
	}

	$block['script'] = isset($block['script']) ? $block['script'] : $_GET['script'];
	$scriptarr = array($block['script'] => ' selected');
	$stylearr = array($_GET['styleid'] => ' selected');

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$block['cachetime_min'] = intval($block['cachetime'] / 60);
	$targetarr[$block['target']] = ' selected';

} elseif($op == 'thumbsetting') {

	if(($bid && !$allowmanage)) {
		showmessage('block_edit_nopermission');
	}

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$block['cachetime_min'] = intval($block['cachetime'] / 60);
	$targetarr[$block['target']] = ' selected';

	$dateformats = block_getdateformats($block['dateformat']);

} elseif($op == 'remove') {

	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	if($_GET['itemid']) {
		$_GET['itemid'] = intval($_GET['itemid']);
		$item = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE itemid='$_GET[itemid]' AND bid='$bid'");
		if($item) {
			DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE itemid='$_GET[itemid]'");
			if($item['itemtype'] != '1') {
				block_ban_item($block, $item);
			}
			block_updatecache($bid, true);
		}
	}
	showmessage('do_success', "portal.php?mod=portalcp&ac=block&op=data&bid=$bid", array('bid'=>$bid));

} elseif( in_array($op, array('item', 'push', 'recommend', 'verifydata', 'managedata'))) {

	if(!$bid) {
		showmessage('block_edit_nopermission');
	}

	$itemid = $_GET['itemid'] ? intval($_GET['itemid']) : 0;
	$dataid = $_GET['dataid'] ? intval($_GET['dataid']) : 0;
	$_GET['id'] = intval($_GET['id']);
	$_GET['idtype'] = preg_replace('/[^\w]/', '', $_GET['idtype']);

	$item = $perm = array();
	if($op == 'item') {
		if(!$allowmanage && !$allowdata) {
			showmessage('block_edit_nopermission');
		}
		if($itemid) {
			$item = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE itemid='$itemid'");
			$item['fields'] = unserialize($item['fields']);
		}
	} elseif($op == 'push') {

		$item = get_push_item($thestyle, $_GET['id'], $_GET['idtype']);
		if($itemid) {
			$item['itemid'] = $itemid;
		}
	} elseif($op == 'recommend') {
		$perm = getblockperm($bid);
		if(!$perm['allowmanage'] && !$perm['allowrecommend']) {
			showmessage('block_no_right_recommend');
		}

		$isrepeatrecommend = false;
		$idtype = $_GET['idtype'] == 'gtid' ? 'tid' : $_GET['idtype'];
		$item = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item_data')." WHERE bid='$bid' AND id='$_GET[id]' AND idtype='$idtype'");
		if($item) {
			$item['fields'] = unserialize($item['fields']);
			$isrepeatrecommend = true;

			if(!$perm['allowmanage'] && $item['uid'] != $_G['uid']) {
				showmessage('data_in_mod_library');
			}

		} else {
			if(in_array($_GET['idtype'],array('tid', 'gtid', 'aid', 'picid', 'blogid'))) {
				$_GET['idtype'] = $_GET['idtype'] == 'gtid' ? 'tids' : $_GET['idtype'].'s';
			}
			$item = get_push_item($thestyle, $_GET['id'], $_GET['idtype'], $block['blockclass'], $block['script']);
			if(empty($item)) showmessage('block_data_type_invalid', null, null, array('msgtype'=>3));
		}
	} elseif($op=='verifydata' || $op=='managedata') {
		if(!$allowmanage && !$allowdata) {
			showmessage('no_right_manage_data');
		}
		if($dataid) {
			$item = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item_data')." WHERE dataid='$dataid'");
			$item['fields'] = unserialize($item['fields']);
		}
	}

	if(!$item) {
		showmessage('block_edit_nopermission');
	}

	$item['oldpic'] = $item['pic'];
	if($item['picflag'] == '1') {
		$item['pic'] = $item['pic'] ? $_G['setting']['attachurl'].$item['pic'] : '';
	} elseif($item['picflag'] == '2') {
		$item['pic'] = $item['pic'] ? $_G['setting']['ftp']['attachurl'].$item['pic'] : '';
	}

	$item['startdate'] = $item['startdate'] ? dgmdate($item['startdate']) : dgmdate(TIMESTAMP);
	$item['enddate'] = $item['enddate'] ? dgmdate($item['enddate']) : '';
	$orders = range(1, $block['shownum']);
	$orderarr[$item['displayorder']] = ' selected="selected"';
	$item['showstyle'] = !empty($item['showstyle']) ? (array)(unserialize($item['showstyle'])) : (!empty($item['fields']['showstyle']) ? $item['fields']['showstyle'] : array());
	$showstylearr = array();
	foreach(array('title_b', 'title_i', 'title_u', 'title_c', 'summary_b', 'summary_i', 'summary_u', 'title_c') as $value) {
		if(!empty($item['showstyle'][$value])) {
			$showstylearr[$value] = 'class="a"';
		}
	}

	$itemfields = $blockitem = $item;
	unset($itemfields['fields']);
	$item['fields'] = (array)$item['fields'];
	foreach($item['fields'] as $key=>$value) {
		if($theclass['fields'][$key]) {
			switch($theclass['fields'][$key]['datatype']) {
				case 'date':
					$itemfields[$key] = dgmdate($value);
					break;
				case 'int':
					$itemfields[$key] = intval($value);
					break;
				case 'string':
					$itemfields[$key] = htmlspecialchars($value);
					break;
				default:
					$itemfields[$key] = $value;
			}
		}
	}

	$showfields = array();
	if(empty($thestyle['fields'])) {
		$template = block_build_template($thestyle['template']);
		$thestyle['fields'] = block_parse_fields($template);
		DB::update('common_block_style', array('fields'=>addslashes(serialize($thestyle['fields']))), array('styleid'=>intval($thestyle['styleid'])));
	}
	foreach($thestyle['fields'] as $fieldname) {
		$showfields[$fieldname] = "1";
	}

	if(submitcheck('itemsubmit') || submitcheck('recommendsubmit') || submitcheck('verifydatasubmit') || submitcheck('managedatasubmit')) {
		$item['bid'] = $block['bid'];
		$item['displayorder'] = intval($_POST['displayorder']);
		$item['startdate'] = !empty($_POST['startdate']) ? strtotime($_POST['startdate']) : 0;
		$item['enddate'] = !empty($_POST['enddate']) ? strtotime($_POST['enddate']) : 0;
		$item['itemtype'] = !empty($_POST['locked']) ? '1' : '2';
		$item['title'] = htmlspecialchars($_POST['title']);
		$item['url'] = $_POST['url'];
		$item['summary'] = cutstr($_POST['summary'], $block['param']['summarylength'], '');
		if($_FILES['pic']['tmp_name']) {
			$result = pic_upload($_FILES['pic'], 'portal');
			$item['pic'] = 'portal/'.$result['pic'];
			$item['picflag'] = $result['remote'] ? '2' : '1';
			$item['makethumb'] = 0;
			$item['thumbpath'] = '';
			$thumbdata = array('bid' => $block['bid'], 'itemid' => $item['itemid'], 'pic' => $item['pic'], 'picflag' => $result['remote'], 'type' => '1');
			DB::insert('common_block_pic', $thumbdata);
		} elseif($_POST['pic']) {
			$pic = htmlspecialchars($_POST['pic']);
			$urls = parse_url($pic);
			if(!empty($urls['scheme']) && !empty($urls['host'])) {
				$item['picflag'] = '0';
				$item['thumbpath'] = '';
			} else {
				$item['picflag'] = intval($_POST['picflag']);
			}
			$item['pic'] = $pic;
			$item['makethumb'] = 0;
		}
		unset($item['oldpic']);
		$item['showstyle'] = $_POST['showstyle']['title_b'] || $_POST['showstyle']['title_i'] || $_POST['showstyle']['title_u'] || $_POST['showstyle']['title_c'] ? dstripslashes($_POST['showstyle']) : array();
		$item['showstyle'] = empty($item['showstyle']) ? '' : daddslashes(serialize($item['showstyle']));

		foreach($theclass['fields'] as $key=>$value) {
			if(!isset($item[$key]) && isset($_POST[$key])) {
				if($value['datatype'] == 'int') {
					$_POST[$key] = intval($_POST[$key]);
				} elseif($value['datatype'] == 'date') {
					$_POST[$key] = strtotime($_POST[$key]);
				} else {
					$_POST[$key] = dstripslashes($_POST[$key]);
				}
				$item['fields'][$key] = $_POST[$key];
			}
		}
		if(isset($item['fields']['fulltitle'])) {
			$item['fields']['fulltitle'] = $item['title'];
		}
		$item['fields']	= addslashes(serialize($item['fields']));

		$item['title'] = cutstr($item['title'], $block['param']['titlelength'], '');

		if(submitcheck('itemsubmit')) {

			if($item['startdate'] > $_G['timestamp']) {
				DB::insert('common_block_item', $item, false, true);
				if($block['itemtype']=='1') {
					block_ban_item($block, $item);
				}
			} elseif(empty($item['enddate']) || $item['enddate'] > $_G['timestamp']) {
				DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE bid='$bid' AND displayorder='$item[displayorder]'");
				DB::insert('common_block_item', $item, false, true);
				if($block['itemtype']=='1') {
					block_ban_item($block, $item);
				}
			} else {
				DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE itemid='$item[itemid]' AND bid='$bid'");
			}
			block_updatecache($bid, true);
			showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=data&bid='.$block['bid'], array('bid'=>$bid));

		} elseif(submitcheck('recommendsubmit')) {
			include_once libfile('function/home');
			unset($item['itemid']);
			unset($item['thumbpath']);
			$item['itemtype'] = '0';
			$item['uid'] = $_G['uid'];
			$item['username'] = $_G['username'];
			$item['dateline'] = TIMESTAMP;
			$item['isverified'] = empty($_POST['needverify']) && ($perm['allowmanage'] || empty($perm['needverify'])) ? '1' : '0';
			$item['verifiedtime'] = TIMESTAMP;

			DB::insert('common_block_item_data', $item, false, true);
			if($_G['gp_showrecommendtip'] && ($_G['gp_idtype'] == 'tid' || $_G['gp_idtype'] == 'gtid')) {
				$modarr = array(
					'tid' => $item['id'],
					'uid' => $item['uid'],
					'username' => $item['username'],
					'dateline' => TIMESTAMP,
					'action' => 'REB',
					'status' => '1',
					'stamp' => '',
					'reason' => getstr($_G['gp_recommendto'], 20, 1, 1, 0, 0),
				);
				DB::insert('forum_threadmod', $modarr);
				$stampsql = '';
				loadcache('stamptypeid');
				if(array_key_exists(4, $_G['cache']['stamptypeid'])) {
					$stampsql = ", stamp='".$_G['cache']['stamptypeid']['4']."'";
				}
				DB::query("UPDATE ".DB::table('forum_thread')." SET moderated='1' $stampsql WHERE tid='$item[id]'");
			}
			if(!empty($_POST['updateblock'])) {
				block_updatecache($bid, true);
			}
			$showrecommendrate = '';
			if($_G['group']['raterange'] && ($_G['gp_idtype'] == 'tid' || $_G['gp_idtype'] == 'gtid')) {
				$showrecommendrate = 1;
			}
			if($showrecommendrate) {
				showmessage('do_success', dreferer('portal.php'), array(), array('showdialog' => true, 'closetime' => 0.01, 'extrajs' =>
					'<script type="text/javascript" reload="1">
					showWindow("rate", "forum.php?mod=misc&action=rate&tid='.$item[id].'&pid='.$_G[gp_recommend_thread_pid].'&showratetip=1", "get", -1);
					</script>'));
			} elseif($_G['gp_showrecommendtip']) {
				showmessage('do_success', dreferer('portal.php'), array(), array('showdialog' => true, 'closetime' => true, 'extrajs' =>
					'<script type="text/javascript" reload="1">
					window.location.reload();
					</script>'));
			} else {
				showmessage('do_success', dreferer('portal.php'), array(), array('showdialog' => true, 'closetime' => true));
			}
		} elseif(submitcheck('verifydatasubmit')) {
			unset($item['thumbpath']);
			$item['isverified'] = '1';
			$item['verifiedtime'] = TIMESTAMP;
			DB::update('common_block_item_data', $item, array('dataid'=>$dataid));
			if(!empty($_POST['updateblock'])) {
				block_updatecache($bid, true);
			}
			showmessage('do_success', dreferer('portal.php?mod=portalcp&ac=blockdata&op=manage&bid='.$bid));
		} elseif(submitcheck('managedatasubmit')) {
			unset($item['thumbpath']);
			$item['stickgrade'] = intval($_POST['stickgrade']);
			DB::update('common_block_item_data', $item, array('dataid'=>$dataid));
			showmessage('do_success', dreferer('portal.php?mod=portalcp&ac=block&op=itemdata&bid='.$bid));
		}
	}

} elseif ($op == 'getblock') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	block_get_batch($bid);
	if(!empty($_GET['forceupdate'])) block_updatecache($bid, !empty($_GET['forceupdate']));
	if(strexists($block['summary'], '<script')) {
		$block['summary'] = lang('portalcp', 'block_diy_nopreview');
		$_G['block'][$bid] = $block;
		$_G['block'][$bid]['cachetime'] = 0;
		$_G['block'][$bid]['nocache'] = true;
	}
	$html = block_fetch_content($bid, $block['blocktype']);

} elseif ($op == 'saveblockclassname') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if (submitcheck('saveclassnamesubmit')) {
		$setarr = array('classname'=>getstr($_POST['classname'], 100, 0, 0, 0, -1));
		DB::update('common_block',$setarr,array('bid'=>$bid));
	}
	block_memory_clear($bid);

	showmessage('do_success');
} elseif ($op == 'saveblocktitle') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if (submitcheck('savetitlesubmit')) {
		$_POST['title'] = preg_replace('/\<script|\<iframe|\<\/iframe\>/is', '', $_POST['title']);
		$title = dstripslashes($_POST['title']);
		$title = preg_replace('/url\([\'"](.*?)[\'"]\)/','url($1)',$title);

		$_G['siteurl'] = str_replace(array('/','.'),array('\/','\.'),$_G['siteurl']);
		$title = preg_replace('/\"'.$_G['siteurl'].'(.*?)\"/','"$1"',$title);

		$setarr = array('title'=>daddslashes($title));
		DB::update('common_block',$setarr,array('bid'=>$bid));
	}

	block_memory_clear($bid);

	showmessage('do_success');
} elseif ($op == 'convert') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}
	block_convert($bid, $_G['gp_toblockclass']);
} elseif ($op == 'favorite') {
	$perm = getblockperm($bid);
	if(!$perm['allowmanage'] && !$perm['allowrecommend']) {
		showmessage('block_no_right_recommend');
	}
	$favoriteop = '';
	if(!block_check_favorite($_G['uid'], $bid)) {
		$setarr = array(
			'uid' => $_G['uid'],
			'bid' => $bid,
		);
		block_add_favorite($setarr);
		$favoriteop = 'add';
	} else {
		block_delete_favorite($_G['uid'], $bid);
		$favoriteop = 'del';
	}
}

include_once template("portal/portalcp_block");

function block_checkdefault($block) {
	if(empty($block['shownum'])) {
		$block['shownum'] = 10;
	}
	if(!isset($block['cachetime'])) {
		$block['cachetime'] = '3600';
	}
	if(empty($block['picwidth'])) {
		$block['picwidth'] = "200";
	}
	if(empty($block['picheight'])) {
		$block['picheight'] = "200";
	}
	if(empty($block['target'])) {
		$block['target'] = "blank";
	}
	return $block;
}

function block_getdateformats($format='') {
	$formats = array('Y-m-d', 'm-d', 'H:i', 'Y-m-d H:i');
	$return = array();
	foreach($formats as $value) {
		$return[] = array(
			'format' => $value,
			'selected' => $format==$value ? ' selected="selected"' : '',
			'time' => dgmdate(TIMESTAMP, $value)
		);
	}
	return $return;
}

function block_ban_item($block, $item) {
	global $_G;
	$parameters = !empty($block['param']) ? $block['param'] : array();
	$bannedids = !empty($parameters['bannedids']) ? explode(',', $parameters['bannedids']) : array();
	$bannedids[] = intval($item['id']);
	$bannedids = array_unique($bannedids);
	$parameters['bannedids'] = implode(',', $bannedids);
	$parameters = serialize($parameters);
	$_G['block'][$block['bid']]['param'] = $parameters;
	$parameters = addslashes($parameters);
	DB::update('common_block', array('param'=>$parameters), array('bid'=>intval($block['bid'])));
}

function get_push_item($blockstyle, $id, $idtype, $blockclass = '', $script = '') {
	$item = array();
	$obj = null;
	if(empty($blockclass) || empty($script)) {
		if($idtype == 'tids') {
			$obj = block_script('forum', 'thread');
		} elseif($idtype == 'gtids') {
			$obj = block_script('group', 'groupthread');
		} elseif($idtype == 'aids') {
			$obj = block_script('portal', 'article');
		} elseif($idtype == 'picids') {
			$obj = block_script('space', 'pic');
		} elseif($idtype == 'blogids') {
			$obj = block_script('space', 'blog');
		}
	} else {
		list($blockclass) = explode('_', $blockclass);
		$obj = block_script($blockclass, $script);
	}
	if($obj && is_object($obj)) {
		$paramter = array($idtype => intval($id));
		$return = $obj->getData($blockstyle, $paramter);
		if($return['data']) {
			$item = array_shift($return['data']);
		}
	}
	return $item;
}

function block_convert($bid, $toblockclass) {
	global $_G;
	$bid = intval($bid);
	if(empty($bid) || empty($toblockclass)) return false;
	$block = DB::fetch_first('SELECT * FROM '.DB::table('common_block')." WHERE bid='".intval($bid)."'");
	if($block) {
		loadcache('blockconvert');
		$fromblockclass = $block['blockclass'];
		list($bigclass) = explode('_', $fromblockclass);
		$convertrule = null;
		if(!empty($_G['cache']['blockconvert']) && !empty($_G['cache']['blockconvert'][$bigclass][$fromblockclass][$toblockclass])) {
			$convertrule = $_G['cache']['blockconvert'][$bigclass][$fromblockclass][$toblockclass];
		}
		if(!empty($convertrule)) {
			$blockstyle = array();
			if($block['styleid']) {
				$blockstyle = DB::fetch_first('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid='".intval($block['styleid'])."'");
				if($blockstyle) {
					unset($blockstyle['styleid']);
					$blockstyle['fields'] = unserialize($blockstyle['fields']);
					$blockstyle['template'] = unserialize($blockstyle['template']);
				}
			} elseif($block['blockstyle']) {
				$blockstyle = unserialize($block['blockstyle']);
			}

			if($blockstyle) {
				$blockstyle['name'] = '';
				$blockstyle['blockclass'] = $toblockclass;
				foreach($blockstyle['fields'] as $key => $value) {
					$blockstyle['fields'][$key] = str_replace($convertrule['searchkeys'], $convertrule['replacekeys'], $value);
				}

				$fun = create_function('&$v','$v = "{".$v."}";');
				array_walk($convertrule['searchkeys'], $fun);
				array_walk($convertrule['replacekeys'], $fun);

				foreach($blockstyle['template'] as $key => $value) {
					$blockstyle['template'][$key] = str_replace($convertrule['searchkeys'], $convertrule['replacekeys'], $value);
				}
				unset($block['bid']);
				$block['styleid'] = '0';
				$block['script'] = $convertrule['script'];
				$block['blockclass'] = $toblockclass;
				$block['blockstyle'] = serialize($blockstyle);
				DB::update('common_block', daddslashes($block), array('bid'=>$bid));
			}
		}

	}
}

function block_check_favorite($uid, $bid){
	$uid = intval($uid);
	$bid = intval($bid);
	if($uid && $bid) {
		return DB::result_first('SELECT count(*) FROM '.DB::table('common_block_favorite')." WHERE uid='$uid' AND bid='$bid'");
	} else {
		return false;
	}
}

function block_add_favorite($setarr){
	$arr = array(
		'uid' => intval($setarr['uid']),
		'bid' => intval($setarr['bid']),
		'dateline' => TIMESTAMP
	);
	return DB::insert('common_block_favorite', $arr, true);
}

function block_delete_favorite($uid, $bid){
	$uid = intval($uid);
	$bid = intval($bid);
	if($uid && $bid) {
		return DB::delete('common_block_favorite', " uid='$uid' AND bid='$bid'");
	} else {
		return false;
	}

}

function block_ckeck_summary($summary){
	if($summary) {
		$tags = array('div', 'table', 'tbody', 'tr', 'td', 'th');
		foreach($tags as $tag) {
			preg_match_all('/(<'.$tag.')|(<\/'.$tag.'>)/i', $summary, $all);
			if(!empty($all[1]) && !empty($all[2])) {
				$all[1] = array_filter($all[1]);
				$all[2] = array_filter($all[2]);
				if(count($all[1]) !== count($all[2])) {
					return $tag;
				}
			}
		}
	}
	return $summary;
}
?>