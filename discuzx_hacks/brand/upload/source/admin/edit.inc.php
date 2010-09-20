<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: edit.inc.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

if ($mname == 'shop' && $_GET['action'] == 'edit' && empty($_POST['valuesubmit']) && empty($_GET['op'])) {
	if(!empty($_GET['itemid'])) {
		ssetcookie('shopid', $_GET['itemid'], 3600 * 10);
		getpanelinfo($_GET['itemid']);
		if(!empty($_G['cookie']['i_referer'])) {
			header('Location: '.$_G['cookie']['i_referer']);
		}
	} elseif(!empty($_G['cookie']['shopid'])) {
		getpanelinfo(intval($_G['cookie']['shopid']));
	}

	if(!empty($_SGLOBAL['panelinfo'])) {
		echo '<script type="text/javascript" charset="'.$_G['charset'].'">var leftmenu = $(window.parent.document).find("#leftmenu");leftmenu.find("ul").css("display", "none");$(window.parent.document).find("#menu_paneladd").css("display", "");</script>';
	}
} elseif(in_array($mname, array('good','notice','album','consume','groupbuy')) && $_GET['shopid']) {
	getpanelinfo($_GET['shopid']);
	ssetcookie('shopid', $_GET['shopid'], 3600 * 10);
}
if(!ckfounder($_G['uid'])) {
	if(!check_itemaccess($_GET['itemid'], $mname)) {
		cpmsg('no_'.$mname.'_itemaccess', 'admin.php?action=list&m='.$mname);
	}
}
if(empty($_SGLOBAL['panelinfo']) && !empty($_REQUEST['shopid'])) {
	getpanelinfo($_REQUEST['shopid']);
}
if(!empty($_POST['valuesubmit'])) {

	$checkresults = array();
	if($mname == "notice" || $mname == "shop" ) {
		//标题样式
		empty($_POST['strongsubject'])?$_POST['strongsubject']='':$_POST['strongsubject']=1;
		empty($_POST['underlinesubject'])?$_POST['underlinesubject']='':$_POST['underlinesubject']=1;
		empty($_POST['emsubject'])?$_POST['emsubject']='':$_POST['emsubject']=1;
		empty($_POST['fontcolorsubject'])?$_POST['fontcolorsubject']='#      ':$_POST['fontcolorsubject']='#'.$_POST['fontcolorsubject'];
		$_POST['styletitle'] = sprintf("%7s%1s%1s%1s",substr($_POST['fontcolorsubject'], -7),$_POST['emsubject'],$_POST['strongsubject'],$_POST['underlinesubject']);

		if($_POST['styletitle'] === '#         ') {
			$_POST['styletitle']  = '';
		}
		unset($_POST['strongsubject'], $_POST['underlinesubject'], $_POST['emsubject'], $_POST['fontcolorsubject']);
	}
	if($mname=='consume' && (strtotime($_POST['validity_end'])<strtotime($_POST['validity_start']))) {
		array_push($checkresults, array('validity_end'=>lang('consume_validity_error')));
	}
	//检查新增到UC注册
	$ucid = 0;
	$ucname = $ucemail = $ucpwd = '';
	if($mname=='shop' && !empty($_POST['ucreg_username'])) {

		if(strlen($_POST['ucreg_password']) < 1 || $_POST['ucreg_password'] != $_POST['ucreg_rtpassword']) {
			array_push($checkresults, array('ucreg_password'=>lang('ucreg_rtpwd_error')));
		}
		if(!empty($checkresults)) {
			cpmsg('ucreg_rtpwd_error', '', '', '', true, true, $checkresults);
		}

		require_once(B_ROOT.'./uc_client/client.php');
		$ucid = uc_user_register($_POST['ucreg_username'], $_POST['ucreg_password'], $_POST['ucreg_email']);
		if($ucid < 0) {
			if($ucid == -1) {
				array_push($checkresults, array('ucreg_username'=>$lang['user_name_is_not_legitimate']));
			} elseif($ucid == -2) {
				array_push($checkresults, array('ucreg_username'=>$lang['include_not_registered_words']));
			} elseif($ucid == -3) {
				array_push($checkresults, array('ucreg_username'=>$lang['user_name_already_exists']));
			} elseif($ucid == -4) {
				array_push($checkresults, array('ucreg_email'=>$lang['email_format_is_wrong']));
			} elseif($ucid == -5) {
				array_push($checkresults, array('ucreg_email'=>$lang['email_not_registered']));
			} elseif($ucid == -6) {
				array_push($checkresults, array('ucreg_email'=>$lang['email_has_been_registered']));
			} else {
				array_push($checkresults, array('message'=>$lang['register_error']));
			}
			if(!empty($checkresults)) {
				cpmsg('user_info_failed', '', '', '', true, true, $checkresults);
			}
		}
		$ucdata = uc_get_user($ucid, 1);
		list($ucid, $ucname, $ucemail) = $ucdata;

		//unset所有uc注册变量
		$ucarr = array('ucreg_username', 'ucreg_password', 'ucreg_rtpassword', 'ucreg_email');
		foreach($ucarr as $value) {
			unset($_POST[$value]);
		}
	}

	//提交了数据
	if($itemid = pkpost($cacheinfo)) {

		if(in_array($mname, array('good', 'notice', 'consume', 'album', 'groupbuy'))) {

			if(!empty($_POST['attr_ids'])) {
				require_once( B_ROOT.'/batch.attribute.php');
				setattributesettings($_POST['catid'], $itemid, $_POST['attr_ids']);
			}
			if($mname == 'good' || $mname == 'groupbuy') {
				DB::query("DELETE FROM ".tname('relatedinfo')." WHERE itemid='$itemid' AND type='$mname'");
				if(!empty($_POST['relatedobject'])) {
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
						$relatedinfoarr[] = '(\''.$itemid.'\', \''.$mname.'\', \''.$relatedid.'\', \''.$relatedtype.'\', \''.$_POST['shopid'].'\')';
					}
					DB::query("REPLACE INTO ".tname("relatedinfo")." (itemid, `type`, `relatedid`, `relatedtype`, `shopid`) VALUES ".implode(",", $relatedinfoarr)." ");
				}
			}
		}
		if(!empty($_POST['syncfid']) || !empty($_SGLOBAL['panelinfo']['syncfid'])) {
			require_once(B_ROOT.'./api/bbs_syncpost.php');
			syncpost($itemid, $mname);
		}
		$shopid = intval($_G['cookie']['shopid']);
		//删除各种分类的缓存
		$_BCACHE->deltype('sitelist', 'attr');
		$_BCACHE->deltype('sitelist', $mname);
		$_BCACHE->deltype('storelist', $mname, $shopid);
		if($mname=='shop') {
			//删除各种分类的缓存
			$_BCACHE->deltype('detail', 'shop', $_POST['itemid']);
			if($ucid>0) {
				//新注册账号
				$insertsqlarr = array(
					'uid' => $ucid,
					'username' => $ucname,
					'password' => md5($ucid.'|'.random(8)),
					'email' => $ucemail,
					'myshopid' => $itemid,
					'dateline' => $_G['timestamp'],
					'updatetime' => $_G['timestamp'],
					'ip' => $_G['clientip']
				);
				inserttable('members', $insertsqlarr, 0, false, 1);
				DB::query('UPDATE '.tname('shopitems')." SET uid='$ucid', username='$ucname' WHERE itemid='$itemid'");
			}
			cpmsg('update_success', 'admin.php?action=list&m='.$mname);
		} else {
			//删除各种分类的缓存
			$_BCACHE->deltype('detail', $mname, $shopid, $_POST['itemid']);
			cpmsg('update_success', 'admin.php?action='.($_POST['itemid']?'list':'add').'&m='.$mname);
		}
	}
} else {
	//没有提交数据
	$editvalue = $echofield = array();

	//判断信息id
	if($_GET['action']=='edit') {
		if($_GET['itemid']) {
			$wheresql = ' i.itemid=\''.$_GET['itemid'].'\'';
		} else {
			cpmsg('no_item', 'admin.php?action=list&m='.$mname);
		}
		//取得信息
		$query = DB::query('SELECT * FROM '.tname($mname.'items').' i '.($mname=='album'?'':'INNER JOIN '.tname($mname.'message').' m ON i.itemid=m.itemid').' WHERE '.$wheresql.' ORDER BY i.itemid DESC LIMIT 1');
		$editvalue = DB::fetch($query);
		if(empty($editvalue)) {
			cpmsg('no_item', 'admin.php?action=list&m='.$mname);
		}
		if($mname == 'good' || $mname == 'groupbuy') {
			$relatedarr = array();
			$relatedarr = getrelatedinfo($mname, $editvalue['itemid'], $editvalue['shopid']);
		}
		$editvalue['dateline'] = sgmdate($editvalue['dateline']);

		//管理员查看基本信息&& $mname=='shop'
		if($_GET['op']=='adminview') {
			if(empty($_SGLOBAL['panelinfo'])) {
				getpanelinfo($_GET['itemid']);
			}
			if($_GET['updatepass'] == 1) {
				$updateser = DB::fetch(DB::query("SELECT * FROM ".tname("itemupdates")." WHERE itemid='$_GET[itemid]' and type = '$mname'"));
				$update = unserialize($updateser['update']);
				$update = sstripslashes($update);
				$update['groupid'] = $_SGLOBAL['panelinfo']['group']['title'];
				$categorylist = getmodelcategory($mname);
				$update['attr_catid'] = $update['catid'];
				$update['catid'] = $categorylist[$update['catid']]['name'];
				$categorylist = getmodelcategory('region');
				$update['region'] = $categorylist[$update['region']]['name'];
				if(!empty($update['subjectimage'])) {
					$update['subjectimage'] = B_URL.'/'.getattachurl($update['subjectimage']);
				}

				if(!empty($update['banner'])) {
					$update['banner'] = B_URL.'/'.getattachurl($update['banner']);
				}
				if(!empty($update['windowsimg'])) {
					$update['windowsimg'] = B_URL.'/'.getattachurl($update['windowsimg']);
				}
				if($mname!='shop') {
					if($update['grade'] == 0 || $update['grade'] == 3) {
						$update['grade'] = '显示';
					} elseif($update['grade'] == '2') {
						$update['grade'] = '关闭';
					}
				} else {
					$update['grade'] = lang('grade_5');
				}
				$update['isdiscount'] = $update['isdiscount']?lang('yes'):lang('no');
				$editvalue = $update;
				$editvalue['itemid'] = $_GET['itemid'];
				$editvalue['message'] = trim(strip_tags($editvalue['message']));
				if($mname=='shop') {
					$editvalue['validity_start'] = date("Y-m-d", (!empty($_SGLOBAL['panelinfo']['validity_start']) ? $_SGLOBAL['panelinfo']['validity_start'] : time()));
					$editvalue['validity_end'] = date("Y-m-d", $_SGLOBAL['panelinfo']['validity_end']);
				} else {
					$update['validity_start'] = date("Y-m-d", $update['validity_start']);
					$update['validity_end'] = date("Y-m-d", $update['validity_end']);
				}
			} elseif($mname=='shop') {
				$editvalue['groupid'] = !empty($editvalue['groupid']) ? $_SGLOBAL['panelinfo']['group']['title'] : '';
				$categorylist = getmodelcategory('region');
				$editvalue['region'] = !empty($editvalue['region']) ? $categorylist[$editvalue['region']]['name']:'';
				$categorylist = getmodelcategory($mname);
				$editvalue['catid'] = $categorylist[$editvalue['catid']]['name'];
				$editvalue['subjectimage'] = !empty($editvalue['subjectimage']) ?  B_URL.'/'.getattachurl($editvalue['subjectimage']):'';
				$editvalue['grade'] = lang('grade_'.$editvalue['grade']);
				$editvalue['validity_start'] = date("Y-m-d", (!empty($_SGLOBAL['panelinfo']['validity_start']) ? $_SGLOBAL['panelinfo']['validity_start'] : time()));
				$editvalue['validity_end'] = date("Y-m-d", $editvalue['validity_end']);
				$editvalue['isdiscount'] = $editvalue['isdiscount']?lang('yes'):lang('no');
			}
			if($mname!='shop') {
				$editvalue['subjectimage'] = !empty($editvalue['subjectimage']) ?  B_URL.'/'.getattachurl($editvalue['subjectimage']):'';
				if($editvalue['grade'] == 0 || $editvalue['grade'] == 3) {
					$editvalue['grade'] = '显示';
				} elseif($editvalue['grade'] == '2') {
					$editvalue['grade'] = '关闭';
				}
				require_once( B_ROOT.'/batch.attribute.php');
				$attributes = getattr($_GET['itemid'], $editvalue['catid']);
				$categorylist = getmodelcategory($mname);
				$editvalue['catid'] = $categorylist[$editvalue['catid']]['name'];
				$editvalue['validity_start'] = date("Y-m-d", $editvalue['validity_start']);
				$editvalue['validity_end'] = date("Y-m-d", $editvalue['validity_end']);

			}
			shownav('infomanage', $mname.'_adminview');
			showsubmenu($mname.'_adminview');
			showtips($mname.'_adminview_tips');
			showformheader('batchmod&m='.$mname.'&operation=passupdate');
			showtableheader();
			if($mname == 'shop') {
				$allow_admin_view_fields = array('subject', 'letter', 'groupid', 'syncfid', 'catid', 'region', 'grade', 'validity_start', 'validity_end', 'message', 'subjectimage', 'banner', 'windowsimg', 'windowstext', 'tips', 'tel', 'address', 'forum', 'isdiscount', 'discount');
				$query = DB::query('SELECT fieldname,fieldtitle FROM '.tname('modelcolumns').' WHERE mid = 2 order by displayorder' );
				while($value = DB::fetch($query)){

					if(!preg_match('/^ext_|^applicant/',$value['fieldname'])){
						continue;
					}
					array_push($allow_admin_view_fields, $value['fieldname']);
					if(empty($lang['shop_'.$value['fieldname']])) {
						$lang['shop_'.$value['fieldname']] = $value['fieldtitle'];
					}
				}
			} elseif($mname == 'good') {
				$allow_admin_view_fields = array('subject', 'catid', 'message', 'subjectimage', 'priceo', 'minprice', 'maxprice', 'validity_start', 'validity_end');
			} elseif($mname == 'groupbuy') {
				$allow_admin_view_fields = array('subject', 'catid', 'message', 'subjectimage', 'groupbuyprice', 'groupbuypriceo', 'groupbuymaxnum', 'validity_start', 'validity_end');
			} elseif($mname == 'consume') {
				$allow_admin_view_fields = array('subject', 'catid', 'message', 'exception', 'subjectimage', 'validity_start', 'validity_end');
			} elseif($mname == 'notice') {
				$allow_admin_view_fields = array('subject', 'catid', 'message', 'subjectimage', 'jumpurl', 'validity_start', 'validity_end');
			} elseif($mname == 'album') {
				$allow_admin_view_fields = array('subject', 'catid', 'subjectimage');
			} else {
				$allow_admin_view_fields = array('subject', 'catid', 'message', 'subjectimage');
			}
			foreach($allow_admin_view_fields as $value) {
				$showtype = 'p';
				if($value == 'message')
					$showtype = 'p_pre';
				$editvalue[$value] = !empty($update[$value]) ? $update[$value] : $editvalue[$value];
				showsetting($mname.'_'.$value, $value, $editvalue[$value], $showtype);

			}
			if($mname=="album") {
				$photosview = "<a target=\"_blank\" href=\"{$_SERVER[SCRIPT_NAME]}?&action=list&m=photo&shopid=$editvalue[shopid]&albumid=$editvalue[itemid]&filtersubmit=GO\">".lang('verify_photosview').'</a>';
				showsetting($mname.'_photosview', 'photosview', $photosview, "p_pre");
			}
			if(!empty($editvalue['attr_ids'])) {
				echo '<tr><td colspan="2" class="td27">'.lang('item_attribute').'</td></tr>';
				require_once( B_ROOT.'/batch.attribute.php');
				$attributes = getattribute($editvalue['attr_catid']);
				foreach($editvalue['attr_ids'] as $key=>$value) {
					echo '<tr class="noborder"><td class="vtop rowform">'.$attributes[$key]['attr_name'].': '.($attributes[$key]['attr_type'] == 0?$attributes[$key]['attr_values'][$value]['attr_text']:$value).'</td><td class="vtop rowform"></td></tr>';

				}
			} elseif(!empty($attributes)) {
				echo '<tr><td colspan="2" class="td27">'.lang('item_attribute').'</td></tr>';
				foreach ($attributes as $key=>$value) {
					echo '<tr class="noborder"><td class="vtop rowform">'.$attributes[$key]['attr_name'].': '.($attributes[$key]['attr_id'] == 0?$attributes[$key]['attr_values'][$value]['attr_text']:$value).'</td><td class="vtop rowform"></td></tr>';
				}
			}
			if(!empty($editvalue['relatedidstr']) || !empty($relatedarr)) {
				print_r($editvalue['relatedidstr']);
				if(!empty($editvalue['relatedidstr'])) {
					foreach($editvalue['relatedidstr'] as $related) {
						$related = explode('@', $related);
						$relatedtype = trim($related[0]);
						$relatedid = intval($related[1]);
						if(DB::result_first("SELECT itemid FROM ".tname($relatedtype."items")." WHERE itemid='$relatedid'")) {
							$relatedidarr[$relatedid] = $relatedtype;
						}
						$relatedinfo = DB::fetch(DB::query('SELECT itemid, subject FROM '.tname($relatedtype.'items').' WHERE itemid=\''.$relatedid.'\' AND shopid=\''.$editvalue['shopid'].'\''));
						$relatedinfo['type'] = $relatedtype;
						$relatedinfo['simplesubject'] = cutstr($relatedinfo['subject'], 30);
						$goodrelated[] = $relatedinfo;
					}
				} elseif(!empty($relatedarr)) {
					$goodrelated = $relatedarr;
				}
				echo '<tr><td colspan="2" class="td27">'.lang('item_related').'</td></tr>';
				foreach (array('good', 'notice', 'album', 'consume') as $item) {
				echo '<tr><td colspan="2" class="td27">'.lang('related'.$item).'</td></tr>';
					foreach($goodrelated as $related) {
						if($item == $related['type']) {
							echo '<tr class="noborder"><td class="vtop rowform"><p id="priceo" name="priceo">'.$related['subject'].'</p></td><td class="vtop tips2"></td></tr>';
						}
					}
				}
			}
			showhiddenfields(array('item[]' => $editvalue['itemid']));
			showlistmod($mname);
			showtablefooter();
			showformfooter();
			exit;
		}
	}

	//显示导航以及表头
	if($mname == 'shop') {
		shownav('shop', $mname.'_'.$_GET['action'], $editvalue['subject']);
	} else {
		shownav('infomanage', $mname.'_'.$_GET['action'], $_SGLOBAL['panelinfo']['subject']);
	}
	switch($mname) {
		case 'good':
			showsubmenu('menu_list_'.$_GET['action'].'good');
			break;
		case 'groupbuy':
			showsubmenu('menu_list_'.$_GET['action'].'groupbuy');
			break;
		case 'consume':
			showsubmenu('menu_list_'.$_GET['action'].'consume');
			break;
		case 'notice':
			showsubmenu('menu_list_'.$_GET['action'].'notice');
			break;
		case 'shop':
			if($_GET['action'] == 'add') {
				showsubmenu('shop_add');
			} else {
				$shopmenu = array(
					array('shop_edit', 'edit&m=shop&itemid='.$_GET['itemid'], '1'),
					array('menu_shop_theme', 'theme&m=shop&itemid='.$_GET['itemid']),
					array('menu_modifypasswd', 'modifypasswd&m=shop&itemid='.$_GET['itemid'], 0)
				);
				if($_G['setting']['enablemap'] == 1) {
					array_push($shopmenu, array('menu_shop_map', 'map&m=shop&itemid='.$_GET['itemid']));
				}
				showsubmenu('shop_edit', $shopmenu);
			}
			break;
	}
	showtips($mname.'_'.$_GET['action'].'_tips');
	showformheader('edit&m='.$mname, 'enctype');
	showtableheader();

	if($_GET['action']=='add' && $mname=='shop') {
		showusernamefield();//注册显示
	}

	if($mname == 'shop') {
		$grouplist = getgrouplist();
		showsetting('shop_groupid', array('groupid', $grouplist), $editvalue['groupid'], 'select', '', '', '', '', '<span style="color:red">*</span>');
	}
	showbasicfield($mname, $editvalue, $_SSCONFIG, $categorylist); //显示基本字段
	//读取自定义字段
	foreach ($cacheinfo['columns'] as $value) {
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
		if($_GET['op']=='adminview' && (!$value['isrequired'] || preg_match("/^(img|flash|file)$/i", $value['formtype'])) )  {
			unset($value);
		}
		if($value['fieldname'] == 'mapapimark' || (!$_G['setting']['enablecard'] && ($value['fieldname']=='isdiscount' || $value['fieldname']=='discount')) ) {
			unset($value);
		}
		if($value['fieldname'] == "styletitle"||$value['fieldname'] == "groupid"||$value['fieldname'] == "region") {
			unset($value);
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
	echo '<script charset="'.$_G['charset'].'">function getattributes() {$("#attributes").load("batch.attribute.php?ajax=1&itemid='.$editvalue['itemid'].'&typeid="+$("select[name=catid]").val());}</script>';
	if(($mname == 'good' || $mname == 'groupbuy') && $_GET['action'] != 'list') {
		showrelatedinfo($mname);
		if($_GET['action'] == 'add') {
			showrelatedinfojs($mname, $_SGLOBAL['panelinfo']['groupid'], '', $_SGLOBAL['panelinfo']['itemid'], 'admin');
		} else {
			$editvalue['groupid'] = getgroupid($mname, $editvalue['itemid']);
			showrelatedinfojs($mname, $editvalue['groupid'], $editvalue['itemid'], $editvalue['shopid'], 'admin');
		}
	}
	if(!empty($_SGLOBAL['panelinfo'])) {
		showhiddenfields(array('shopid' => $_SGLOBAL['panelinfo']['itemid']));
	} elseif($editvalue['shopid']) {
		showhiddenfields(array('shopid' => $editvalue['shopid']));

	}
	showhiddenfields(array('itemid' => $editvalue['itemid']));
	showhiddenfields(array('nid' => $editvalue['nid']));
	if($_GET['action'] == 'edit') {
		showhiddenfields(array('nocheckcatid' => 1));
	}
	showhiddenfields(array('valuesubmit' => 'yes'));
	showsubmit('settingsubmit', 'submit', '');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
	echo '<script type="text/javascript" charset="'.$_G['charset'].'">loadcalendar();</script>';
	if($editvalue['catid']) {
		echo '<script type="text/javascript" charset="'.$_G['charset'].'">getattributes();</script>';
	}
}
function getgrouplist() {
	global $_G, $_SGLOBAL;
	$grouplist = array(array(0,lang('shop_group')));
	foreach($_SGLOBAL['shopgrouparr'] as $groupid => $group) {
		$grouplist[] = array($groupid, $group['title']);
	}
	return $grouplist;
}
?>