<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: edit.inc.php 4476 2010-09-15 04:51:33Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}
// 没有审核的用户进入后台后，能编辑的店铺属性
if(pkperm('unverified')) {
	$allow_unverified_fields = array('itemid', 'nid', 'subject', 'catid', 'message', 'tel', 'address');
	foreach ($cacheinfo['columns'] as $key=>$value) {
		if(preg_match('/(^ext_)|(^applicant)/',$value['fieldname']) && ($value['allowpost'] == 1) && ($value['formtype'] != 'img')){
			$allow_unverified_fields[$key] = $value['fieldname'];
		}
	}
}

if(!empty($_POST['valuesubmit'])) {
	if($_GET['m'] == "notice" || $_GET['m']=='shop') {
		//标题样式
		empty($_POST['strong'])?$_POST['strong']='':$_POST['strong']=1;
		empty($_POST['underline'])?$_POST['underline']='':$_POST['underline']=1;
		empty($_POST['em'])?$_POST['em']='':$_POST['em']=1;
		empty($_POST['fontcolor'])?$_POST['fontcolor']='#      ':$_POST['fontcolor']='#'.$_POST['fontcolor'];
		$_POST['styletitle'] = sprintf("%7s%1s%1s%1s",substr($_POST['fontcolor'], -7),$_POST['em'],$_POST['strong'],$_POST['underline']);
		if($_POST['styletitle'] === '#         ') {
			$_POST['styletitle']  = '';
		}
		unset($_POST['strong'], $_POST['underline'], $_POST['em'], $_POST['fontcolor']);
		if(!empty($_POST['region_3'])&&$_POST['region_3']!=-1) {
			$_POST['region'] = $_POST['region_3'];
		} elseif(empty($_POST['region_3'])&&!empty($_POST['region_2'])&&$_POST['region_2']!=-1) {
			$_POST['region'] = $_POST['region_2'];
		} elseif((empty($_POST['region_2'])||$_POST['region_2']==-1)&&!empty($_POST['region_1'])) {
			$_POST['region'] = $_POST['region_1'];
		}
		unset($_POST['region_3']);unset($_POST['region_2']);unset($_POST['region_1']);
	}
	if($_GET['m']=='shop') {
		$_POST['catid'] = $_SGLOBAL['panelinfo']['catid'];
		$_POST['isdiscount'] = $_SGLOBAL['panelinfo']['isdiscount'];
		$_POST['groupid'] = $_SGLOBAL['panelinfo']['groupid'];
	}
	// 提交了数据  Check the perm
	if(pkperm('unverified')) {
		$tmparr = array();
		foreach($allow_unverified_fields as $value) {
			$tmparr[$value] = $_POST[$value];
		}
		unset($_POST);
		$_POST = $tmparr;
	}

	if($itemid = pkpost($cacheinfo)) {
		$itemgrade = DB::result_first("SELECT grade FROM ".tname($mname."items")." WHERE itemid = '$itemid'");
		if(in_array($mname, array('good', 'notice', 'consume', 'album', 'groupbuy'))) {

			if(!empty($_POST['attr_ids'])) {
				require_once( B_ROOT.'/batch.attribute.php');
				if($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$mname]) {
					$_SGLOBAL['updatesqlarr']['attr_ids'] = $_POST['attr_ids'];
				} else {
					setattributesettings($_POST['catid'], $itemid, $_POST['attr_ids']);
				}
			}
			if($mname == 'good' || $mname == 'groupbuy') {
				if(!empty($_POST['relatedobject'])) {
					if($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$mname]) {
						$_SGLOBAL['updatesqlarr']['relatedidstr'] = explode(',', trim($_POST['relatedobject']));
					} else {
						$relatedidarr = $relatedinfoarr = array();
						$relatedidstr = '';
						$relatedidstr = explode(',', trim($_POST['relatedobject']));
						foreach($relatedidstr as $related) {
							$related = explode('@', $related);
							$relatedtype = trim($related[0]);
							$relatedid = intval($related[1]);
							if(DB::result_first("SELECT itemid FROM ".tname($relatedtype."items")." WHERE itemid='$relatedid'")) {
								$relatedidarr[$relatedid] = $relatedtype;
							}
						}
						foreach($relatedidarr as $relatedid=>$relatedtype) {
							$relatedinfoarr[] = '(\''.$itemid.'\', \''.$mname.'\', \''.$relatedid.'\', \''.$relatedtype.'\', \''.$_SGLOBAL['panelinfo']['itemid'].'\')';
						}
						DB::query("DELETE FROM ".tname('relatedinfo')." WHERE itemid='$itemid'");
						DB::query("REPLACE INTO ".tname("relatedinfo")." (itemid, `type`, `relatedid`, `relatedtype`, `shopid`) VALUES ".implode(",", $relatedinfoarr)." ");
					}
				}
			}
		}
		if($_SGLOBAL['itemupdate'] && $itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$mname]) {
			$update = serialize($_SGLOBAL['updatesqlarr']);
			$gradeuparr =array('itemid'=>$itemid);
			if(in_array($mname, array('good', 'notice', 'consume', 'album'))) {
				$gradeuparr['shopid'] = $_G['myshopid'];
			}
			updatetable($mname.'items', array('updateverify'=>1), $gradeuparr);
			$update = saddslashes($update);

			DB::query("REPLACE INTO ".tname("itemupdates")." (`itemid`, `type`, `updatestatus`, `update`) VALUES ($itemid, '$mname', '1', '$update');");
		} else {
			if(!empty($_POST['syncfid']) || !empty($_SGLOBAL['panelinfo']['syncfid'])) {
				require_once(B_ROOT.'./api/bbs_syncpost.php');
				syncpost($itemid, $mname);
			}
		}
		if($_POST['stuffurl']) {
			$nexturl = $_POST['stuffurl'];
		} else {
			if($mname == 'shop') {
				$nexturl = 'panel.php?action=edit&m='.$mname;
			}else{
				$nexturl = 'panel.php?action=list&m='.$mname;
			}
		}

		//删除相关缓存
		$_BCACHE->deltype('sitelist', 'attr');
		$_BCACHE->deltype('sitelist', $mname);
		$_BCACHE->deltype('storelist', $mname, $_G['myshopid']);
		if($mname=='shop') {
			$_BCACHE->deltype('detail', 'shop', $_G['myshopid']);
		} else {
			$_BCACHE->deltype('detail', $mname, $_G['myshopid'], $_POST['itemid']);
		}
		cpmsg('update_success', $nexturl);
	}

} else {

	//没有提交数据
	$editvalue = $echofield = array();
	if($_GET['action'] == 'edit') {
		if($mname == 'shop') {
			$wheresql = ' i.itemid=\''.$_G['myshopid'].'\'';
		} else {
			$wheresql = ' i.itemid=\''.$_GET['itemid'].'\' AND i.shopid=\''.$_G['myshopid'].'\'';
		}

		//取得信息
		$query = DB::query('SELECT * FROM '.tname($mname.'items').' i
										INNER JOIN '.tname($mname.'message').' m ON i.itemid=m.itemid
										'.($mname == 'shop'?' LEFT JOIN '.tname('shopupdate').' u ON i.itemid = u.shopid ':'').'
										WHERE '.$wheresql.' ORDER BY i.itemid DESC LIMIT 1');
		$editvalue = DB::fetch($query);
		if(empty($editvalue)) {
			cpmsg('no_item', 'panel.php?action=list&m='.$mname);
		}

		if($editvalue['updateverify'] == 1) {
			$query = DB::query("SELECT * FROM ".tname('itemupdates')." WHERE `itemid` ='".($mname == 'shop'? $_G['myshopid']:$_GET[itemid])."' AND `type` = '$mname'");
			$result = DB::fetch($query);
			$update = unserialize($result['update']);
			$update = sstripslashes($update);
			foreach($update as $key=>$value) {
				$editvalue[$key] = $value;
			}
			if(!empty($update['relatedidstr'])) {
				foreach($update['relatedidstr'] as $related) {
					$related = explode('@', $related);
					$relatedtype = trim($related[0]);
					$relatedid = intval($related[1]);
					if(DB::result_first("SELECT itemid FROM ".tname($relatedtype."items")." WHERE itemid='$relatedid'")) {
						$relatedidarr[$relatedid] = $relatedtype;
					}
					$relatedinfo = DB::fetch(DB::query('SELECT itemid, subject FROM '.tname($relatedtype.'items').' WHERE itemid=\''.$relatedid.'\' AND shopid=\''.$_G['myshopid'].'\''));
					$relatedinfo['type'] = $relatedtype;
					$relatedinfo['simplesubject'] = cutstr($relatedinfo['subject'], 30);
					$relatedarr[] = $relatedinfo;
				}
			}
		} else {
			if($mname == 'good' || $mname == 'groupbuy') {
				$relatedarr = array();
				$relatedarr = getrelatedinfo($mname, $editvalue['itemid'], $_G['myshopid']);
			}
		}
		$editvalue['dateline'] = sgmdate($editvalue['dateline']);
	}

	$required = '<span style="color:red">*</span>';

	if(pkperm('unverified')) {
		//显示导航以及表头
		shownav($mname, $mname.'_unverified');
		showsubmenu($mname.'_unverified');
		showtips($mname.'_unverified_tips');
		showformheader('edit&m='.$mname);
		showtableheader();
		foreach($allow_unverified_fields as $key=>$value) {
			if($value == "itemid" || $value == "nid") continue;
			if($value != 'catid') {
				if(preg_match('/(^ext_)/',$value)){
					showsetting($cacheinfo['columns'][$key]['fieldtitle'], $value, $editvalue[$value], 'text');
				}else{
					showsetting($mname.'_'.$value, $value, $editvalue[$value], 'text');
				}
			} else {
				$categorylist = getmodelcategory('shop');
				$editvalue[$value] = $categorylist[$editvalue[$value]]['name'];
				showsetting($mname.'_'.$value, $value, $editvalue[$value], 'p');
			}
		}
		showhiddenfields(array('itemid' => $editvalue['itemid']));
		showhiddenfields(array('nid' => $editvalue['nid']));

		showsubmit('valuesubmit', 'submit', '');
		showtablefooter();
		showformfooter();
		bind_ajax_form();
		exit;
	}

	//显示导航以及表头
	switch($mname) {
		case 'good':
			shownav('infomanage', $mname.'_'.$_GET['action']);
			showsubmenu('menu_list_addgood', array(
				array('menu_good', 'list&m=good', '0'),
				array('menu_list_addgood', 'add&m=good', '1')
			));
			break;
		case 'groupbuy':
			shownav('infomanage', $mname.'_'.$_GET['action']);
			showsubmenu('menu_list_addgroupbuy', array(
				array('menu_groupbuy', 'list&m=groupbuy', '0'),
				array('menu_list_addgroupbuy', 'add&m=groupbuy', '1')
			));
			break;
		case 'consume':
			shownav('infomanage', $mname.'_'.$_GET['action']);
			showsubmenu('menu_list_addconsume', array(
				array('menu_consume', 'list&m=consume', '0'),
				array('menu_list_addconsume', 'add&m=consume', '1')
			));
			break;
		case 'notice':
			shownav('infomanage', $mname.'_'.$_GET['action']);
			showsubmenu('menu_list_addnotice', array(
				array('menu_notice', 'list&m=notice', '0'),
				array('menu_list_addnotice', 'add&m=notice', '1')
			));
			break;
		case 'shop':
			shownav('shop', $mname.'_'.$_GET['action']);
			showsubmenu('shop_add');
			break;
	}
	showtips($mname.'_'.$_GET['action'].'_tips');
	showformheader('edit&m='.$mname, 'enctype');
	showtableheader('', '', 'id="shoptable"');
	showbasicfield($mname, $editvalue, $_SSCONFIG, $categorylist, 'panel'); //显示基本字段

	//读取自定义字段
	foreach ($cacheinfo['columns'] as $value) {
		if($value['allowpost'] == 0) {
			continue;
		}
		if($mname == "groupbuy" && preg_match('/^user_|^ext_/',$value['fieldname'])) {
			continue;
		}
		if($mname == "good" && $value['fieldname'] == "intro") {
			continue;
		}
		$temparr = $temparr2 = array();
		$other = $required = $value['required'] = '';
		if($value['formtype'] == 'select') {
			$temparr2 = array(''=>'');
		}

		$temparr = explode("\r\n", $value['fielddata']);
		foreach($temparr as $value2) {
			$temparr2[$value2] = $value2;
		}
		if($value['isrequired']) {
			$value['required'] = '<span style="color:red">*</span>';
		}
		$value['formtype'] = $value['formtype'] == 'text' ? 'input' : $value['formtype'];
		$value['formtype'] = $value['formtype'] == 'linkage' ? 'select' : $value['formtype'];
		if($value['formtype'] == 'checkbox') {
			$editvalue[$value['fieldname']] = explode("\n", $editvalue[$value['fieldname']]);
		}
		$fileurl = A_URL.'/'.$editvalue[$value['fieldname']];
		if(preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
			$value['formtype'] = 'file';
		}
		if($value['fieldname'] == 's_enablealbum' || $value['fieldname'] == 'intro' || $value['fieldname'] == 's_enableconsume' || $value['fieldname'] == 's_enablenotice' || $value['fieldname'] == 's_enablegood' || $value['fieldname'] == 'isdiscount' || $value['fieldname'] == 'mapapimark' || ((!$_G['setting']['enablecard'] || !$editvalue['isdiscount']) && $value['fieldname'] == 'discount')) {
			unset($value);
		}
		if($value['fieldname'] == "styletitle"||$value['fieldname'] == "groupid"||$value['fieldname'] == "region") {
			unset($value);
		}
		if($value['fieldname'] == "forum") {
			if(!pkperm('isadmin')) {
				unset($value);
			}
		}

		if(empty($value)) { continue;}

		if($value['formtype'] != 'timestamp') {
			// 如果是自定字
			if(strpos($value['fieldname'], 'ext_')===0){
				$title = $value['fieldtitle'];
			} else {
				$title = $mname.'_'.$value['fieldname'];
			}
			pklabel(array('type'=>$value['formtype'], 'alang'=>$title, 'name'=>$value['fieldname'], 'options'=>$temparr2, 'rows'=>10, 'width'=>'30%', 'size'=>'60', 'value'=>$editvalue[$value['fieldname']],  'other'=>$other, 'fileurl'=>$fileurl, 'required'=>$value['required']));
		}

	}
	echo '<script>
		function getattributes() {
			$("#attributes").load("batch.attribute.php?ajax=1'.(($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$mname]) || $editvalue['updateverify']==0?'&itemid='.$editvalue['itemid']:'').'&typeid="+$("select[name=catid]").val());
		}</script>';
	if(($mname == 'good' || $mname == 'groupbuy') && $_GET['action'] != 'list') {
		showrelatedinfo($mname);
		showrelatedinfojs($mname, $_SGLOBAL['panelinfo']['groupid'], $editvalue['itemid'], $_G['myshopid']);
	}
	showtablefooter();
	showhiddenfields(array('itemid' => $editvalue['itemid']));
	showhiddenfields(array('nid' => $editvalue['nid']));
	showhiddenfields(array('valuesubmit' => 'yes'));
	if($taskmessage) {
		showhiddenfields(array('stuffurl' => $nexttask));
		showsubmit('settingsubmit', 'submitnext', '', '', '', $nexttask);
	} else {
		showsubmit('settingsubmit', 'submit', '');
	}
	showformfooter();
	bind_ajax_form();
	echo '<script type="text/javascript" charset="'.$_G['charset'].'">loadcalendar();</script>';
	if($editvalue['catid']) {

		if(!empty($editvalue['attr_ids'])) {
			foreach($editvalue['attr_ids'] as $attrid=>$attr) {
				$attrscriptstr .= '$("#attributes select[name=attr_ids['.$attrid.']]").val("'.$attr.'");';
			}
			echo '<script type="text/javascript" charset="'.$_G['charset'].'">$(function() {jQuery.get("batch.attribute.php?ajax=1&typeid="+$("#catid").val(),"",function(data) {$("#attributes").append(data);'
				.$attrscriptstr.
				'})});</script>';
		} else {
			echo '<script type="text/javascript" charset="'.$_G['charset'].'">getattributes();</script>';

		}
	}
}
?>