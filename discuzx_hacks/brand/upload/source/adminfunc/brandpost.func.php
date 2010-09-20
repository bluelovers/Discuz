<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: brandpost.func.php 4472 2010-09-15 03:22:43Z xuhui $
 */

if(!defined('IN_BRAND')) {
	exit('Acess Denied');
}

/**
 * 後台發佈信息時提交數據處理函數
 */

/**
 * 後台權限檢查
 * @param $permtype=''，目前沒有用戶組概念，權限檢查僅為檢查 verified通過驗證的商家/unverified未經審核通過的商家/isadmin站長。以後考慮按功能來分權限
 * @return 是否
 */
function pkperm($permtype) {
	global $_G;

	if(ckfounder($_G['uid'])) {
		if($permtype != 'unverified') {
			return true;//管理員有所有權限
		} else {
			return false;
		}
	} elseif($permtype == 'isadmin' && $_G['member']['allowadmincp']) {
		return true;
	} elseif($_G['uid'] == 0 || !$_G['myshopid'] || $_G['myshopstatus'] == 'none') {
		return false;//遊客或者未提交入駐沒有任何權限
	} elseif($_G['myshopid'] > 0 && $_G['myshopstatus'] == 'unverified' && $permtype == 'unverified') {
		return true;//提交入駐申請未審核的
	} elseif($_G['myshopid'] > 0 && $_G['myshopstatus'] == 'verified') {
		//入駐通過的權限
		switch($permtype) {
			case 'verified':
				return true;
				break;
			case 'shoplist':
				return false;
				break;
			case 'swfupload':
				return true;
				break;
		}
	}
	return false;//默認為無權限
}

/**
 * 取得模型信息
 * return array
 */
function getmodelinfoall($type, $value) {
	if($type == 'mid') {
		$value = intval($value);
		if(empty($value)) {
			return false;
		}
	} else {
		if(empty($value) || !preg_match("/^[a-z0-9]{2,20}$/i", $value)) {
			return false;
		}
	}
	$cachefile = B_ROOT.'./data/cache/model/model_'.$value.'.cache.php';
	$cacheinfo = '';
	if(file_exists($cachefile)) {
		include_once($cachefile);
	}
	if(!empty($cacheinfo) && is_array($cacheinfo)) {
		return $cacheinfo;
	} else {
		require_once(B_ROOT.'./source/function/cache.func.php');
		return updatemodel($type, $value);
	}
}

/**
 * 檢查提交的模型信息
 * return array
 */
function checkvalues($valuearr, $isedit=0, $admincp=1) {
	global $_G, $mname, $checkresults;
	if(!empty($valuearr)) {
		foreach ($valuearr as $value) {
			if($value['formtype'] == 'img') {
				if(!empty($_FILES[$value['fieldname']]['name'])) {
					$fileext = fileext($_FILES[$value['fieldname']]['name']);
					if(!in_array($fileext, array('jpg'))) {
						array_push($checkresults, array($value['fieldname']=>lang($mname.'_'.$value['fieldname']).pkmodelmsg('upload_pic_error')));
					}
				}
			}
			//判斷是否是必填
			if(!empty($value['isrequired'])) {
				if(preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
					if(empty($_FILES[$value['fieldname']]['name']) && $isedit == 0) {
						array_push($checkresults, array($value['fieldname']=>lang($mname.'_'.$value['fieldname']).pkmodelmsg('required_error')));
					}
				} else {
					if(bstrlen(trim($_POST[$value['fieldname']])) <= 0) {
						array_push($checkresults, array($value['fieldname']=>$value['fieldtitle'].pkmodelmsg('required_error')));
					}
				}
			}
			if(!preg_match("/^(img|flash|file)$/i", $value['formtype'])) {	//判斷長度是否符合要求
				if(!preg_match("/^(TEXT|MEDIUMTEXT|LONGTEXT|FLOAT|DOUBLE)$/i", $value['fieldtype'])) {
					if(isset($_POST[$value['fieldname']]) && (!is_array($_POST[$value['fieldname']]) && bstrlen(trim($_POST[$value['fieldname']])) > 0)) {
						if(in_array($value['fieldname'], array('priceo', 'minprice', 'maxprice'))) {
							if(!is_numeric(trim($_POST[$value['fieldname']]))) {
								array_push($checkresults, array($value['fieldname']=>lang('good_price_not_numeric')));
							}
						}
						if($value['formtype'] != 'checkbox' && bstrlen($_POST[$value['fieldname']]) > $value['fieldlength']) {
							if(in_array($value['fieldname'], array('priceo', 'minprice', 'maxprice'))) {
								array_push($checkresults, array($value['fieldname']=>lang($mname.'_'.$value['fieldname']).pkmodelmsg('good_price_length_restrict')));
							} else {
								array_push($checkresults, array($value['fieldname']=>lang($mname.'_'.$value['fieldname']).pkmodelmsg('length_should_not_exceed').$value['fieldlength']));
							}
						}
					}
				}
			}
		}
	}
}

/**
 * 拼合sql語句
 * return array
 */
function getsetsqlarr($valuearr) {
	$setsqlarr = array();
	if(!empty($valuearr)) {
		foreach ($valuearr as $value) {
			if(isset($_POST[$value['fieldname']])) {
				if(!preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
					//提交來後的數據過濾
					if(preg_match("/^(VARCHAR|CHAR|TEXT|MEDIUMTEXT|LONGTEXT)$/i", $value['fieldtype'])) {
						if($value['formtype'] == 'checkbox') {
							$_POST[$value['fieldname']] = implode("\n", shtmlspecialchars($_POST[$value['fieldname']]));
						}
						if(empty($value['ishtml'])) {
							$_POST[$value['fieldname']] = shtmlspecialchars(trim($_POST[$value['fieldname']]));
						} else {
							$_POST[$value['fieldname']] = trim($_POST[$value['fieldname']]);
						}
						if(!empty($value['isbbcode'])) {
							$_POST[$value['fieldname']] = modeldiscuzcode($_POST[$value['fieldname']]);
						}
					} elseif(preg_match("/^(TINYINT|SMALLINT|MEDIUMINT|INT|BIGINT)$/i", $value['fieldtype'])) {
						$_POST[$value['fieldname']] = intval($_POST[$value['fieldname']]);
					}

					$setsqlarr[$value['fieldname']] = $_POST[$value['fieldname']];
				} elseif($value['isimage']) {
					$setsqlarr[$value['fieldname']] = $_POST[$value['fieldname']];
				}
			}
		}
	}
	return $setsqlarr;
}

function pk_strip_tags($str) {
	$farr = array(
			"/\s+/",  //過濾多餘的空白
			"/<div/isU",
			"/<\/div>/isU",
			"/<(\/?)(script|i?frame|style|html|body|title|link|object|meta|\?|\%)([^>]*?)>/isU",  //過濾 <script 等惡意代碼
			"/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",  //過濾javascript的on事件

	);
	$tarr = array(
			" ",
			"<p",
			"</p>",
			"＜\\1\\2\\3＞",
			"\\1\\2",
	);
	$str = preg_replace( $farr,$tarr,$str);
	return $str;
}

function pkpost($cacheinfo, $cp=1) {
	global $_G, $_SGLOBAL, $theurl, $mname, $checkresults;

	$itemid = !empty($_POST['itemid']) ? intval($_POST['itemid']) : 0;
	$hash = '';
	$op = 'add';
	$mustverify = false;
	$resultitems = $resultmessage = $updateitem = array();
	$modelsinfoarr = $cacheinfo['models'];
	$columnsinfoarr = $cacheinfo['columns'];

	$feedcolum = array();
	foreach($columnsinfoarr as $result) {
		if($mname == "groupbuy" && preg_match('/^user_|^ext_/',$result['fieldname'])) {
			continue;
		}
		if($result['isfixed'] == 1) {
			$resultitems[] = $result;
		} else {
			$resultmessage[] = $result;
		}
		if($result['formtype'] == 'linkage') {
			if(!empty($_POST[$result['fieldname']])) {
				$_POST[$result['fieldname']] = $cacheinfo['linkage']['info'][$result['fieldname']][$_POST[$result['fieldname']]];
			}
		} elseif($result['formtype'] == 'timestamp') {
			if(empty($_POST[$result['fieldname']])) {
				$_POST[$result['fieldname']] = $_G['timestamp'];
			} else {
				$_POST[$result['fieldname']] = sstrtotime($_POST[$result['fieldname']]);
			}
		}
	}
	//輸入檢查
	$_POST['subject'] = trim(strip_tags($_POST['subject']));
	$itemid = $_POST['itemid'];
	$checkresults = array();
	if(bstrlen($_POST['subject']) < 1 || bstrlen($_POST['subject']) > 80) {
		array_push($checkresults, array('subject'=>lang('space_suject_length_error')));
	}

	//數據檢查
	checkvalues(array_merge($resultitems, $resultmessage), 1, 1);

	//商品價格處理 Start
	if($modelsinfoarr['modelname'] == 'good') {
		if(($_POST['minprice'] > 0) && ($_POST['maxprice'] > 0) && ($_POST['maxprice'] < $_POST['minprice'])) {
			array_push($checkresults, array('maxprice'=>lang('maxprice_must_big_then_minprice')));
		}
	}
	//商品價格處理 End
	//修改時檢驗標題圖片是否修改
	$defaultmessage = array();
	if(!empty($itemid)) {
		if(empty($_POST['subjectimage_value']) || !empty($_FILES['subjectimage']['name'])) {	//當file刪除時，或修改時執行刪除操作
			$query = DB::query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'items').' WHERE itemid = \''.$itemid.'\'');
			$defaultmessage = DB::fetch($query);
			$hash = getmodelhash($modelsinfoarr['mid'], $itemid);
			deletetable('attachments', array('hash' => $hash, 'subject' => 'subjectimage'));	//刪除附件表
			updatetable($modelsinfoarr['modelname'].'items', array('subjectimage' => ''), array('itemid'=>$itemid));
			$ext = fileext($defaultmessage['subjectimage']);
			if(in_array($ext, array('jpg', 'jpeg', 'png'))) {
				@unlink(A_DIR.'/'.substr($defaultmessage['subjectimage'] , 0, strrpos($defaultmessage['subjectimage'], '.')).'.thumb.jpg');
			}
			@unlink(A_DIR.'/'.$defaultmessage['subjectimage']);
		}
	}

	//構建數據
	$setsqlarr = $setitemsqlarr = array();
	$setsqlarr = getsetsqlarr($resultitems);
	$itemgrade = DB::result_first("SELECT grade FROM ".tname($mname."items")." WHERE itemid = '$itemid'");
	if($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']]) {
		$setsqlarr['subjectimage'] = $_POST['subjectimage_value'];
	}
	if(empty($_POST['catid']) || $_POST['catid'] < 0) {
		array_push($checkresults, array('catid'=>lang('cat_not_selected')));
	}
	$setsqlarr['catid'] = $_POST['catid'];
	if($modelsinfoarr['modelname'] != 'shop') {
		//限制必填信息所屬店舖
		if(pkperm('isadmin')) {
			if(empty($_POST['shopid'])) {
				array_push($checkresults, array('shopid'=>lang('please_select_shopid')));
			}
			$setsqlarr['shopid'] = intval($_POST['shopid']);
		} else {
			$setsqlarr['shopid'] = $_G['myshopid'];
		}
	} else {
		$setsqlarr['letter'] = !empty($_POST['letter']) ? trim($_POST['letter']) : getletter(trim($_POST['subject']));
		$setsqlarr['keywords'] = trim(strip_tags($_POST['keywords']));
		$setsqlarr['description'] = trim(strip_tags($_POST['description']));
		if(!empty($_POST['syncfid'])) {
			require_once(B_ROOT.'./api/bbs_syncpost.php');
			if(checkbbsfid($_POST['syncfid'])) {
				$setsqlarr['syncfid'] = intval($_POST['syncfid']);
			} else {
				array_push($checkresults, array('syncfid'=>lang('syncfid_noexists')));
			}
		}
		

	}
	$setsqlarr['subject'] = $_POST['subject'];
	$setsqlarr['allowreply'] = 1;
	if(!empty($checkresults)) {
		cpmsg('addobject_error', '', '', '', true, true, $checkresults);
	}

	if(pkperm('isadmin')) {
		$setsqlarr['grade'] = isset($_POST['grade']) ? $_POST['grade'] : 3;
	} elseif($_G['myshopstatus'] == 'verified') {
		if(in_array($modelsinfoarr['modelname'], array('good', 'notice', 'consume', 'album', 'groupbuy')) && $itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']]) {
			$setsqlarr['grade'] = !empty($itemid) ? 5 : 0;
			if(!empty($itemid)) {
				if(in_array($_POST['grade'], array(2,3))) {
					$setsqlarr['grade'] = $_POST['grade'];
				}
			}
			$mustverify = true;

		} else {
			if(in_array($_POST['grade'], array(2,3))) {
				$setsqlarr['grade'] = $_POST['grade'];
			} else {
				$setsqlarr['grade'] = $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']] ? 0 : 3;
			}
		}
	} elseif($_G['myshopstatus'] == 'unverified') {
		$setsqlarr['grade'] = 0;
	}
	$setsqlarr['dateline'] = $_G['timestamp'];
	$setsqlarr['uid'] = $_G['uid'];
	$setsqlarr['username'] = $_G['username'];
	$setsqlarr['lastpost'] = $setsqlarr['dateline'];

	// 標題圖片處理 Start
	if(!empty($modelsinfoarr['thumbsize'])) {
		$modelsinfoarr['thumbsize'] = explode(',', trim($modelsinfoarr['thumbsize']));
		$modelsinfoarr['subjectimagewidth'] = $modelsinfoarr['thumbsize'][0];
		$modelsinfoarr['subjectimageheight'] = $modelsinfoarr['thumbsize'][1];
	}

	if($_POST['imagetype'] == 0 && $modelsinfoarr['modelname'] == 'consume' && $_G['setting']['allowcreateimg']) {
		if($_GET['action'] == 'add') {
			$hotline = $_SGLOBAL['panelinfo']['tel'];
			$address = $_SGLOBAL['panelinfo']['address'];
		} else {
			$shopinfo = DB::fetch(DB::query("SELECT tel, address FROM ".tname('shopitems')." WHERE itemid='$setsqlarr[shopid]'"));
			$hotline = $shopinfo['tel'];
			$address = $shopinfo['address'];
		}
		$dealer_name = DB::result_first("SELECT subject FROM ".tname('shopitems')." WHERE itemid='$setsqlarr[shopid]'");
		$createimgarr = array(
			'id' => intval($_POST['imgtplid']),
			'mid' => intval($modelsinfoarr['mid']),
			'itemid' => intval($itemid),
			'coupon_title' => $setsqlarr['subject'],
			'dealer_id' => $setsqlarr['uid'],
			'dealer_name' => $dealer_name,
			'begin_date' => date('Y-m-d', $setsqlarr['validity_start']),
			'end_date' => date('Y-m-d', $setsqlarr['validity_end']),
			'brief' => trim($_POST['message']),
			'exception' => trim($_POST['exception']),
			'address' => $address,
			'hotline' => $hotline,
			'subjectimagewidth' => $modelsinfoarr['subjectimagewidth'],
			'subjectimageheight' => $modelsinfoarr['subjectimageheight']
		);
		require_once(B_ROOT.'./source/adminfunc/tool.func.php');
		if($consumeimgpath = image_text($createimgarr)) {
			$setsqlarr['subjectimage'] = $consumeimgpath;
			$setsqlarr['imagetype'] = 0;
			$setsqlarr['imgtplid'] = intval($_POST['imgtplid']);
		}
	} else {

		$uploadfilearr = $ids = array();
		$subjectimageid = '';
		$uploadfilearr = uploadfile(array(array('fieldname'=>'subjectimage', 'fieldcomment'=>'圖片標題', 'formtype'=>'img')), $modelsinfoarr['mid'], 0, 1, $modelsinfoarr['subjectimagewidth'], $modelsinfoarr['subjectimageheight']);
		if(!empty($uploadfilearr)) {
			$feedsubjectimg = $uploadfilearr;
			foreach($uploadfilearr as $tmpkey => $tmpvalue) {
				if(empty($tmpvalue['error'])) {
					$setsqlarr[$tmpkey] = $tmpvalue['filepath'];
				}
				if(!empty($tmpvalue['aid'])) {
					$ids[] = $tmpvalue['aid'];
				}
			}
		}
		if($modelsinfoarr['modelname'] == 'consume') {
			$setsqlarr['imagetype'] = 1;
		}
	}
	/* --------- 標題圖片處理 End --------------*/

	//詞語過濾
	if(!empty($modelsinfoarr['allowfilter'])) $setsqlarr = scensor($setsqlarr, 1);
	//發佈時間
	$setsqlarr['dateline'] = $_G['timestamp'];

	// 商品添加簡介
	if($mname == "good") {
		$setsqlarr['intro'] = trim(strip_tags($_POST['intro']));
	}

	if(empty($itemid)) {
		//插入數據
		$itemid = inserttable($modelsinfoarr['modelname'].'items', $setsqlarr, 1);

		if(in_array($modelsinfoarr['modelname'], array('good', 'notice', 'consume', 'album', 'groupbuy')))
		itemnumreset($modelsinfoarr['modelname'], $setsqlarr['shopid']);
	} else {
		$_SGLOBAL['itemupdate'] = 1;
		//更新
		$op = 'update';
		unset($setsqlarr['uid']);
		unset($setsqlarr['username']);
		unset($setsqlarr['lastpost']);
		if($itemgrade == 1 && !pkperm('isadmin')) {
			$setsqlarr['grade'] = 0;
		} elseif($itemgrade == 1 && pkperm('isadmin')) {
			$setsqlarr['grade'] = 1;
		} elseif($itemgrade == 0 && !pkperm('isadmin')) {
			$setsqlarr['grade'] = 0;
		} elseif($itemgrade == 0 && pkperm('isadmin')) {
			$setsqlarr['grade'] = 0;
		}
		if(pkperm('isadmin')) {
			//站長可以post任何數據
			updatetable($modelsinfoarr['modelname'].'items', $setsqlarr, array('itemid'=>$itemid));//權限限制
		} else {
			// 店長不允許更改店舖組
			unset($setsqlarr['groupid']);
			if($modelsinfoarr['modelname'] == 'shop') {
				unset($setsqlarr['validity_start']);
				unset($setsqlarr['validity_end']);
				if($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']]) {
					$updatesqlarr = $setsqlarr;
				} else {
					//店長提交店舖權限檢查
					updatetable($modelsinfoarr['modelname'].'items', $setsqlarr, array('itemid'=>$_G['myshopid']));
				}
			} else {
				if($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']]) {
					$updatesqlarr = $setsqlarr;
				} else {
					//店長只能更改管理的店舖的信息
					updatetable($modelsinfoarr['modelname'].'items', $setsqlarr, array('itemid'=>$itemid, 'shopid'=>$_G['myshopid']));
				}
			}
		}

		$query = DB::query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'message').' WHERE itemid = \''.$itemid.'\'');
		$defaultmessage = DB::fetch($query);
	}
	$hash = getmodelhash($modelsinfoarr['mid'], $itemid);
	if(!empty($ids)) {
		$ids = simplode($ids);
		DB::query('UPDATE '.tname('attachments').' SET hash=\''.$hash.'\' WHERE aid IN ('.$ids.')');
	}
	$do = 'pass';

	if($op == 'update' && !$_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']]) {
		if(!empty($resultmessage)) {
			foreach($resultmessage as $value) {
				if(preg_match("/^(img|flash|file)$/i", $value['formtype']) && !empty($defaultmessage[$value['fieldname']])) {
					if(empty($_POST[$value['fieldname'].'_value']) || !empty($_FILES[$value['fieldname']]['name'])) {	//當file刪除時，或修改時執行刪除操作
						deletetable('attachments', array('hash' => $hash, 'subject' => $value['fieldname']));	//刪除附件表
						updatetable($modelsinfoarr['modelname'].'message', array($value['fieldname'] => ''), array('itemid'=>$itemid));
						@unlink(A_DIR.'/'.substr($defaultmessage[$value['fieldname']] , 0, strrpos($defaultmessage[$value['fieldname']], '.')).'.thumb.jpg');
						@unlink(A_DIR.'/'.$defaultmessage[$value['fieldname']].'.thumb.jpg');
						@unlink(A_DIR.'/'.$defaultmessage[$value['fieldname']]);
					}
				}
			}
		}
	}

	//內容
	$setsqlarr = $uploadfilearr = $ids = array();
	$setsqlarr = getsetsqlarr($resultmessage);
	$uploadfilearr = $feedcolum = uploadfile($resultmessage, $modelsinfoarr['modelname'], $itemid, 0);
	$setsqlarr['message'] = trim($_POST['message']);
	$setsqlarr['message'] = saddslashes(html2bbcode(stripslashes($setsqlarr['message'])));
	if($modelsinfoarr['modelname'] == 'consume') {
		$setsqlarr['exception'] = trim($_POST['exception']);
	}
	if($_POST['imagetype'] == 0 && $modelsinfoarr['modelname'] == 'consume' && $_G['setting']['allowcreateimg']) {
		$setsqlarr['address'] = trim($_POST['address']);
		$setsqlarr['hotline'] = trim($_POST['hotline']);
	}
	$setsqlarr['postip'] = $_G['clientip'];
	if($modelsinfoarr['modelname'] == 'shop' && $itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']]) {
		$setsqlarr['banner'] = $_POST['banner_value'];
		$setsqlarr['windowsimg'] = $_POST['windowsimg_value'];
	}
	if(!empty($uploadfilearr)) {
		foreach($uploadfilearr as $tmpkey => $tmpvalue) {
			if(empty($tmpvalue['error'])) {
				$setsqlarr[$tmpkey] = $tmpvalue['filepath'];
			}
			if(!empty($tmpvalue['aid'])) {
				$ids[] = $tmpvalue['aid'];
			}
		}
	}

	//添加內容
	if(!empty($modelsinfoarr['allowfilter'])) $setsqlarr = scensor($setsqlarr, 1);
	if($op == 'add') {
		$setsqlarr['itemid'] = $itemid;
		//添加內容
		inserttable($modelsinfoarr['modelname'].'message', $setsqlarr);

	} else {
		if($itemgrade > 1 && $_SGLOBAL['panelinfo']['group']['verify'.$modelsinfoarr['modelname']] && !pkperm('isadmin')) {
			$_SGLOBAL['updatesqlarr'] = array_merge($updatesqlarr, $setsqlarr);

		} else {
			//更新內容
			updatetable($modelsinfoarr['modelname'].'message', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
		}

	}

	updatetable('attachments', array('isavailable' => '1', 'type' => 'model'), array('hash'=>$hash));

	return $itemid;
}
// 指處理自定義中的所有上傳
function uploadfile($valuearr, $mid=2, $itemid=0, $havethumb=1, $width=100, $height=100) {
	global $_G, $_SGLOBAL;

	$setsqlarr = array();
	$hash = getmodelhash($mid, $itemid);
	if(!empty($valuearr)) {
		foreach($valuearr as $value) {
			if(!preg_match("/^(img|flash|file)$/i", $value['formtype'])) {
				continue;
			}

			$filearr = $_FILES[$value['fieldname']];
			if(!empty($filearr['name'])) {
				$setsqlarr[$value['fieldname']] = array('fieldcomment' => $value['fieldcomment'], 'filepath' => '', 'error' => '', 'aid' => '');
				if(empty($filearr['size']) || empty($filearr['tmp_name'])) {
					//獲取上傳文件大小失敗，請選擇其他文件上傳
					$setsqlarr[$value['fieldname']]['error'] = modelmsg('get_upload_size_error');
					break;
				}
				$fileext = fileext($filearr['name']);
				if ($value['fieldname'] == 'subjectimage'){
					$newfilearr = loadClass('attach')->savelocalfile($filearr, array($width, $height), '', 1);	//標題圖片上傳
				}else{
					list($width,$height) = explode(',',$value['thumbsize']);
					$newfilearr = loadClass('attach')->savelocalfile($filearr, array($width, $height), '', 1);	//自定義圖片上傳
				}
				if($value['formtype'] == 'img') {
					$attachinfo	= @getimagesize(A_DIR.'/'.$newfilearr['file']);
					if(empty($attachinfo) || ($attachinfo[2] < 1 && $attachinfo[2] > 3)) {
						$setsqlarr[$value['fieldname']]['error'] = modelmsg('get_upload_size_error');
						@unlink(A_DIR.'/'.$newfilearr['file']);
						if($newfilearr['thumb'] != $newfilearr['file']) {
							@unlink(A_DIR.'/'.$newfilearr['thumb']);
						}
						break;
					}
				}

				if(empty($newfilearr['file'])) {
					//上傳文件失敗，請您稍後嘗試重新上傳
					$setsqlarr[$value['fieldname']]['error'] = modelmsg('upload_error');
					break;
				}

				//數據庫
				$insertsqlarr = array(
					'uid' => $_G['uid'],
					'dateline' => $_G['timestamp'],
					'filename' => saddslashes($filearr['name']),
					'subject' => $value['fieldname'],
					'attachtype' => $fileext,
					'isimage' => (in_array($fileext, array('jpg','jpeg','gif','png'))?1:0),
					'size' => $filearr['size'],
					'filepath' => $newfilearr['file'],
					'thumbpath' => $newfilearr['thumb'],
					'hash' => $hash
				);
				$aid = inserttable('attachments', $insertsqlarr, 1);
				$setsqlarr[$value['fieldname']]['filepath'] = $value['formtype'] != 'file' ? $newfilearr['file'] : $aid;
				$setsqlarr[$value['fieldname']]['aid'] = $aid;
			}
		}
	}
	return $setsqlarr;
}

function getmodelhash($mid=2, $itemid=0, $pre='i') {
	$mid = str_pad($mid, 6, 0, STR_PAD_LEFT);
	$itemid = str_pad($itemid, 8, 0, STR_PAD_LEFT);
	return 'm'.$mid.$pre.$itemid;
}

//模型語言包調用
function pkmodelmsg($key) {
	global $_G, $_SGLOBAL;
	include_once(B_ROOT.'./language/model.lang.php');

	$message = $key;
	if(!empty($_SGLOBAL['modellang'][$key])) $message = $_SGLOBAL['modellang'][$key];

	return $message;
}

function pklabel($showarr, $isall = 1) {
	global $_G, $_SGLOBAL, $alang, $lang, $mname;

	$thetext = $htmltext = $thelang = '';

	if(!empty($showarr['alang'])) {
		if(isset($alang[$showarr['alang']])) {
			$thelang = $alang[$showarr['alang']];
		} else {
			$thelang = $showarr['alang'];
		}
	} elseif (!empty($showarr['lang'])) {
		if(isset($lang[$showarr['lang']])) {
			$thelang = $lang[$showarr['lang']];
		} else {
			$thelang = $showarr['lang'];
		}
	}

	if(!isset($showarr['name'])) $showarr['name'] = '';
	if(!isset($showarr['size'])) $showarr['size'] = 30;
	if(!isset($showarr['maxlength'])) $showarr['maxlength'] = '';
	if(!isset($showarr['value'])) $showarr['value'] = '';
	if(!isset($showarr['values'])) $showarr['values'] = array();
	if(!isset($showarr['options'])) $showarr['options'] = array();
	if(!isset($showarr['other'])) $showarr['other'] = '';
	if(!isset($showarr['display'])) $showarr['display'] = '';
	if(!isset($showarr['hots'])) $showarr['hots'] = array();
	if(!isset($showarr['lasts'])) $showarr['lasts'] = array();
	if(!isset($showarr['btnname'])) $showarr['btnname'] = '';
	if(!isset($showarr['title'])) $showarr['title'] = '';
	if(!isset($showarr['mode'])) $showarr['mode'] = '0';
	if(!isset($showarr['cols'])) $showarr['cols'] = '';
	if(!isset($showarr['fileurl'])) $showarr['fileurl'] = '';

	switch ($showarr['type']) {
		case 'input':
			showsetting($showarr['alang'], $showarr['name'], $showarr['value'], 'text', '', '', '', $showarr['other'], $showarr['required']);
			break;
		case 'file':
			showsetting($showarr['alang'], $showarr['name'], $showarr['value'], 'file', '', '', '', $showarr['other'], $showarr['required']);
			if(!empty($showarr['value'])) {
				echo "\n".'<tr class="noborder"><td class="vtop rowform">
<a href="'.$showarr['fileurl'].'" target="_blank">'.$showarr['value'].'</a></td><td class="vtop tips2"><a href="javascript:;" title="Delete" onclick="document.getElementById(\''.$showarr['name'].'_value\').value=\'\'; this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);">'.lang('delete').'</a></td></tr>';
			}
			break;
		case 'edit':
			if(pkperm('isadmin')) {
				$enablealbum = 1;
			} else {
				if($_SGLOBAL['panelinfo']['enablealbum']) {
					$enablealbum=1;
				} else {
					$enablealbum=0;
				}
			}
			echo "<script type=\"text/javascript\">var enablealbum=$enablealbum;</script>";

			echo "<script type=\"text/javascript\" charset=\"utf-8\" src=\"static/js/editor/xheditor-zh-cn.js\"></script>
				<tr><td class=\"td27\" colspan=\"2\">".lang($showarr['alang'])."{$showarr[required]}</td></tr>
				<tr class=\"noborder\"><td class=\"vtop rowform\" colspan=\"2\">
				<textarea cols=\"100\" id=\"{$showarr[name]}\" name=\"{$showarr[name]}\" rows=\"20\" style=\"width:600px;\" class=\"xheditor {tools:'Bold,Italic,Underline,Strikethrough,FontSize,FontColor,BackColor,Separator,Align,List,Separator,Link,Img,About',skin:'default'}\">{$showarr[value]}</textarea>
				</td></tr>";
			break;
		case 'textarea':
			showsetting($showarr['alang'], $showarr['name'], $showarr['value'], 'textarea', '', '', '', $showarr['other'], $showarr['required']);
			break;
		case 'select':
			$optionarr = array();
			foreach($showarr['options'] as $key=>$value) {
				$optionarr[$key][0] = $key;
				$optionarr[$key][1] = $value['pre'].$value['name'];
			}
			showsetting($showarr['alang'], array($showarr['name'], $optionarr), $showarr['value'], 'select', '', '', '', $showarr['other'], $showarr['required']);
			break;
		case 'radio':
			showsetting($showarr['alang'], $showarr['name'], $showarr['value'], 'radio', '', '', '', $showarr['other'], $showarr['required']);
			break;
		case 'radio_a':
			showsetting($showarr['alang'], $showarr['name'], $showarr['value'], 'radio_a', '', '', '', $showarr['other'], $showarr['required']);
			break;
		case 'checkbox':
			$thetext = '';
			$i=0;
			$thetext = '<table class="freetable"><tr>';
			foreach ($showarr['options'] as $tmpkey => $tmpvalue) {
				$thetext .= '<td><input name="'.$showarr['name'].'[]" type="checkbox" value="'.$tmpkey.'"'.$showarr['other'].' />'.$tmpvalue.'</td>';
				if($i%5==4) $thetext .= '</tr><tr>';
				$i++;
			}
			$thetext .= '</tr></table>';
			if(!empty($showarr['value'])) {
				if(is_array($showarr['value'])) {
					$showvaluearr = $showarr['value'];
				} else {
					$showvaluearr = explode(',', $showarr['value']);
				}
				foreach ($showvaluearr as $showvalue) {
					$showvalue = trim($showvalue);
					$thetext = str_replace('value="'.$showvalue.'"', 'value="'.$showvalue.'" checked', $thetext);
				}
			}
			break;
		case 'date':
			$datearr = array(
				'0' => $alang['space_date_null'],
				'86400' => $alang['space_date_day_1'],
				'172800' => $alang['space_date_day_2'],
				'604800' => $alang['space_date_week_1'],
				'1209600' => $alang['space_date_week_2'],
				'2592000' => $alang['space_date_month_1'],
				'7948800' => $alang['space_date_month_3'],
				'15897600' => $alang['space_date_month_6'],
				'31536000' => $alang['space_date_year_1']
			);
			$thetext = getselectstr($showarr['name'], $datearr, $showarr['value']);
			break;
		case 'time':
			$thetext = '<input name="'.$showarr['name'].'" readonly type="text" id="'.$showarr['name'].'" value="'.$showarr['value'].'"/><img src="static/image/admin/time.gif" onClick="getDatePicker(\''.$showarr['name'].'\',event,21)"/>';
			break;
		case 'hidden':
			$htmltext = '<tr><td colspan="2" style="display:none"><input name="'.$showarr['name'].'" type="hidden" value="'.$showarr['value'].'"'.$showarr['other'].' /></td></tr>';
			break;
		default:
			$thetext = '';
			break;
	}

	if(!$isall) {
		return $thetext;
	}

	return $htmltext."\n";
}
function pkregion($showarr) {
	global $_G, $_SC;
	/*
	 if($showarr['value']) {
		$upid = $showarr['options'][$showarr['value']]['upid']? $showarr['options'][$showarr['value']]['upid']:0;
		if($showarr['options'][$upid]['upid'] !=0) {
		$upupid = $showarr['options'][$upid]['upid'];
		} else {
		$upupid = 0;
		}
		}*/
	//echo '<tr><td class="td27" colspan="2">'.lang('shop_'.$showarr['name']).$showarr['required'].'</td></tr><tr><td class="vtop rowform" id="'.$showarr['name'].'div" colspan="2"><select id="selector_0" name="'.$showarr['name'].'"><option value="-1">'.lang('shop_'.$showarr['name']).'</option>';
	echo '<tr><td class="td27" colspan="2">'.lang('shop_'.$showarr['name']).$showarr['required'].'</td></tr><tr><td class="vtop rowform" id="'.$showarr['name'].'div" colspan="2">';
	echo InteractionCategoryMenu($showarr['options'],$showarr['name'],$showarr['value'],null);
	echo '</td></tr>';
	/*
	 foreach($showarr['options'] as $k=>$v) {

		if($v['upid'] == 0) {
		echo '<option value="'.$v['catid'].'"'.(($v['catid']==$upupid)||($v['catid']==$upid)||($upid==0&&$showarr['value']&&$v['catid']==$showarr['value'])?' selected="selected"':'').'>'.$v['name'].'</option>';
		}
		}
		echo '</select><span id="span_catid"></span><script type="text/javascript" charset="utf-8">';

		echo '
		$(function(){

		var '.$showarr['name'].'s = '.json_encode_region($showarr['options']).';
		var selector = 1;
		$("#selector_0").bind("change", function(){creat(this.id);});
		function creat(id) {
		var originalrid = $("#"+id+"").val();
		var csid = id.split("_")[1];
		var newregion = "";
		for(var i in '.$showarr['name'].'s) {
		if('.$showarr['name'].'s[i].upid == originalrid) {
		newregion += "<option value=\""+'.$showarr['name'].'s[i].catid+"\""+((typeof(upid)!="undefined"&&'.$showarr['name'].'s[i].catid==upid)||(typeof(value)!="undefined"&&'.$showarr['name'].'s[i].catid==value)?" selected=\"selected\"":"")+">"+'.$showarr['name'].'s[i].name+"</option>";
		}
		}
		var selectlength = $("#'.$showarr['name'].'div select").length;
		if(selectlength > 1) {
		for(var i = selectlength; i > 0; i--) {
		var cid = $("#'.$showarr['name'].'div select:nth-child("+i+")").attr("id");
		if( cid.split("_")[1] > csid) {
		$("#'.$showarr['name'].'div select:nth-child("+i+")").remove();
		}
		}
		selector = $("#'.$showarr['name'].'div select").length;

		}
		if(newregion!="") {
		$("#"+id+"").after("<select><option value=\"-1\">'.lang("please_select_".$showarr['name']).'</option>"+newregion+"</select>");
		$("#"+id+"").removeAttr("name");
		$("#"+id+" + select").attr("name", "'.$showarr['name'].'");
		$("#"+id+" + select").attr("id", "selector_"+selector);
		$("#"+id+" + select").bind("change", function(){'.($showarr['name']=='region'?'':'').'creat(this.id);});
		} else {
		$("#"+id).attr("name","'.$showarr['name'].'");
		}

		selector = $("#'.$showarr['name'].'div select").length;
		if(selector == 1) {
		$("#selector_0").attr("name","'.$showarr['name'].'");
		}
		'.($showarr['name']=='region'?'':'getattributes();').'
		}


		';

		echo '<script>';
		if(!empty($showarr['value'])) {
		$upids = explode("|", getupid($showarr['value'],$showarr['options']));
		$org = 0;
		for($i=count($upids);$i > 1; $i--) {
		if($upids[$i-1]!=0) {
		echo '$("#selector_'.$org.'").val('.$upids[$i-1].');$("#selector_'.$org.'").change();';
		$org++;
		}
		}

		echo '$("#selector_'.$org.'").val('.$showarr['value'].');$("#selector_'.$org.'").change();';
		}
		echo '});</script></td></tr>';
		*/
}
/**
 * 獲取地區設置
 * $param
 * return array
 */
function getupid($id,$list) {
	$upid = '';
	foreach($list as $key=>$value) {
		if($id == $key) {
			$upid .= $value['upid'];
			if($list[$value['upid']]['upid']!=0) {
				$upid .= getupid($value['upid'],$list);
			}
		}
	}
	return '|'.$upid;
}
/**
 * 獲取關聯信息
 * $param $type 類型
 * $param $itemid 對像ID
 * $param $shopid 商舖ID
 * return array
 */
function getrelatedinfo($type, $itemid, $shopid) {
	global $_G, $_SGLOBAL;

	$relatedarr = array();
	$query = DB::query('SELECT relatedid, relatedtype FROM '.tname('relatedinfo')." WHERE itemid='$itemid' AND type='$type' AND shopid='$shopid'");
	while($related = DB::fetch($query)) {
		$relatedinfo = DB::fetch(DB::query('SELECT itemid, subject FROM '.tname($related['relatedtype'].'items').' WHERE itemid=\''.$related['relatedid'].'\' AND shopid=\''.$shopid.'\''));
		$relatedinfo['type'] = $related['relatedtype'];
		$relatedinfo['simplesubject'] = cutstr($relatedinfo['subject'], 30);
		$relatedarr[] = $relatedinfo;
	}

	return $relatedarr;
}

/**
 * 獲取店舖組設置信息及店舖自定義權限設置。
 * return
 */
function getpanelinfo($shopid = null) {
	global $_G, $_SGLOBAL;
	$shopid = isset($shopid) ? $shopid :$_G['myshopid'];
	$query = DB::query("SELECT si.*, sm.* FROM ".tname("shopitems")." si INNER JOIN  ".tname("shopmessage")." sm ON si.itemid = sm.itemid WHERE si.itemid = $shopid");
	$result = DB::fetch($query);
	if($result['grade'] > 1) {
		$result['group'] = $_SGLOBAL['shopgrouparr'][$result['groupid']];
		$object = array('good', 'notice', 'consume', 'album', 'brandlinks', 'groupbuy');
		foreach($object as $value) {
			$result['enable'.$value] = $result['group']['enable'.$value] + intval($result['s_enable'.$value]);
		}
		$result['enablephoto'] = $result['enablealbum'];
	}
	$_SGLOBAL['panelinfo'] = $result;
}

/**
 * 批准店舖、商品、消費券、公告、相冊、的更新內容
 *return
 */
function pkupdate($cacheinfo, $update) {
	global $_G, $_SGLOBAL, $theurl, $mname;
	$_POST = $update;
	$itemid = $_POST['itemid'];
	$resultitems = $resultmessage = $resultimage = $updateitem = array();
	$modelsinfoarr = $cacheinfo['models'];
	$columnsinfoarr = $cacheinfo['columns'];
	foreach($columnsinfoarr as $result) {
		if($result['isfixed'] == 1) {
			$resultitems[] = $result;
		} else {
			$resultmessage[] = $result;
		}
		if($result['isimage'] == 1) {
			$resultimage[] = $result;
		}
	}
	//構建數據
	$setsqlarr = $setitemsqlarr = array();
	$setsqlarr = getsetsqlarr($resultitems);
	$setsqlarr['subjectimage'] = isset($_POST['subjectimage']) ? $_POST['subjectimage']: '';
	$setsqlarr['updateverify'] = 0;
	$setsqlarr['catid'] = $_POST['catid'];
	$setsqlarr['subject'] = $_POST['subject'];
	$setsqlarr['grade'] = $_POST['grade'];
	unset($setsqlarr['grade']);
	$query = DB::query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'items').' WHERE itemid = \''.$itemid.'\'');
	$defaultmessage = DB::fetch($query);

	$ext = fileext($defaultmessage['subjectimage']);

	if($defaultmessage['subjectimage'] != $setsqlarr['subjectimage']) {
		if(in_array($ext, array('jpg', 'jpeg', 'png'))) {
			@unlink(A_DIR.'/'.substr($defaultmessage['subjectimage'] , 0, strrpos($defaultmessage['subjectimage'], '.')).'.thumb.jpg');
		}
		@unlink(A_DIR.'/'.$defaultmessage['subjectimage']);
	}
	updatetable($modelsinfoarr['modelname'].'items', $setsqlarr, array('itemid'=>$itemid));//權限限制

	$setsqlarr = getsetsqlarr($resultmessage);
	$setsqlarr['message'] = $_POST['message'];

	if($modelsinfoarr['modelname'] == 'shop') {
		$setsqlarr['banner'] = isset($_POST['banner']) ? $_POST['banner']: '';
		$setsqlarr['windowsimg'] = isset($_POST['windowsimg']) ? $_POST['windowsimg']: '';
		$query = DB::query('SELECT * FROM '.tname($modelsinfoarr['modelname'].'message').' WHERE itemid = \''.$itemid.'\'');
		$defaultmessage = DB::fetch($query);

		foreach($resultimage as $ext_img) {
			$ext = fileext($defaultmessage[$ext_img['fieldname']]);
			if($defaultmessage[$ext_img['fieldname']] != $setsqlarr[$ext_img['fieldname']]) {
				if(in_array($ext, array('jpg', 'jpeg', 'png'))) {
					@unlink(A_DIR.'/'.substr($defaultmessage[$ext_img['fieldname']] , 0, strrpos($defaultmessage[$ext_img['fieldname']], '.')).'.thumb.jpg');
				}
				@unlink(A_DIR.'/'.$defaultmessage[$ext_img['fieldname']]);
			}
		}

	} elseif($modelsinfoarr['modelname'] == 'consume') {
		$setsqlarr['exception'] = $_POST['exception'];
	}

	if($setsqlarr) {
		updatetable($modelsinfoarr['modelname'].'message', $setsqlarr, array('itemid'=>$itemid));//權限限制
	}
	if(!empty($_POST['relatedidstr'])) {
		$shopid = $_POST['shopid'];
		foreach($_POST['relatedidstr'] as $related) {
			$related = explode('@', $related);
			$relatedtype = trim($related[0]);
			$relatedid = intval($related[1]);
			if(DB::result_first("SELECT itemid FROM ".tname($relatedtype."items")." WHERE itemid='$relatedid'")) {
				$relatedidarr[$relatedid] = $relatedtype;
			}
		}
		foreach($relatedidarr as $relatedid=>$relatedtype) {
			$goodrelatedarr[] = '(\''.$itemid.'\', \'good\', \''.$relatedid.'\', \''.$relatedtype.'\', \''.$shopid.'\')';
		}
		DB::query("DELETE FROM ".tname('relatedinfo')." WHERE itemid='$itemid' AND type='good'");
		DB::query("REPLACE INTO ".tname('relatedinfo')." (`itemid`, `type`, `relatedid`, relatedtype, `shopid`) VALUES ".implode(",", $goodrelatedarr)." ");
	} elseif($modelsinfoarr['modelname'] == 'good') {
		DB::query("DELETE FROM ".tname('relatedinfo')." WHERE itemid='$itemid' AND type='good'");

	}
	if(!empty($_POST['attr_ids'])) {
		require_once( B_ROOT.'/batch.attribute.php');
		setattributesettings($_POST['catid'], $itemid, $_POST['attr_ids']);
	}
	DB::query("DELETE FROM ".tname('itemupdates')." WHERE `type` = '$modelsinfoarr[modelname]' AND itemid='$itemid'");
	require_once(B_ROOT.'./api/bbs_syncpost.php');
	syncpost($itemid, $mname);
}

/**
 * 修改店舖內容統計數據
 * 添加、刪除商品、相冊、公告、消費券時，修改店舖的內容統計數據。
 *return
 */
function itemnumreset($type, $shopid, $do = 'add', $num = 1) {
	global $_G, $_SGLOBAL;
	if(!in_array($type, array('good', 'notice', 'consume', 'album', 'brandlinks', 'groupbuy'))) {
		return false;
	}
	$fieldname = 'itemnum_'.$type;
	$dosql = $do == 'add' ? ' + ' : ' - ';

	DB::query("UPDATE ".tname("shopitems")." SET $fieldname = $fieldname $dosql $num WHERE itemid = $shopid ;");
}
/*
 * 序列化字符串中帶有引號時，反序列化時會出錯。
 */
function mb_unserialize($serial_str) {
	$serial_str= preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
	$serial_str= str_replace("\n\r", "", $serial_str);
	return unserialize($serial_str);
}
function asc_unserialize($serial_str) {
	$serial_str = preg_replace('!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"', $serial_str );
	$serial_str= str_replace("\n\r", "", $serial_str);
	return unserialize($serial_str);
}
function InteractionCategoryMenu($categorylist,$formname,$value=null,$getattributes=null) {
	if($value) {
		$upid = $categorylist[$value]['upid']? $categorylist[$value]['upid']:0;
		if($categorylist[$upid]['upid'] !=0) {
			$upupid = $categorylist[$upid]['upid'];
		} else {
			$upupid = 0;
		}
	}
	$html = '';
	$html .= '<span id ="'.$formname.'div"><select id="'.$formname.'_selector_0" name="'.$formname.'"><option value="-1">'.lang("please_select_".$formname).'</option>';
	foreach($categorylist as $catid=>$catinfo) {
		if($catinfo['upid']==0) {
			$html .= '<option value="'.$catid.'"'.(($catid==$upupid)||($catid==$upid)||($upid==0&&$value&&$catid==$value)?' selected="selected"':'').'>'.$catinfo['name'].'</option>';
		}
	}
	$html .= '</select></span>';
	$html .= '<span><script>
		$(function(){
			var '.$formname.'s = '.json_encode_region($categorylist).';
			var selector = 1;
			$("#'.$formname.'_selector_0").bind("change", function(){creat(this.id);});
			function creat(id) {
				var originalrid = $("#"+id+"").val();
				var csid = id.split("_")[2];
				var newregion = "";
				for(var i in '.$formname.'s) {
					if('.$formname.'s[i].upid == originalrid) {
						newregion += "<option value=\""+'.$formname.'s[i].catid+"\""+((typeof(upid)!="undefined"&&'.$formname.'s[i].catid==upid)||(typeof(value)!="undefined"&&'.$formname.'s[i].catid==value)?" selected=\"selected\"":"")+">"+'.$formname.'s[i].name+"</option>";
					}
				}
				var selectlength = $("#'.$formname.'div select").length;
				if(selectlength > 1) {
					for(var i = selectlength; i > 0; i--) {
						var cid = $("#'.$formname.'div select:nth-child("+i+")").attr("id");
						if( cid.split("_")[2] > csid) {
							$("#'.$formname.'div select:nth-child("+i+")").remove();
						}
					}
					selector = $("#'.$formname.'div select").length;

				}
				if(newregion!="") {
					$("#"+id+"").after("<select><option value=\"-1\">'.lang("please_select_".$formname).'</option>"+newregion+"</select>");
					$("#"+id+"").removeAttr("name");
					$("#"+id+" + select").attr("name", "'.$formname.'");
					$("#"+id+" + select").attr("id", "'.$formname.'_selector_"+selector);
					$("#"+id+" + select").bind("change", function(){creat(this.id);});
				} else {
					$("#"+id).attr("name","'.$formname.'");
				}

				selector = $("#'.$formname.'div select").length;
				if(selector == 1) {
					$("#'.$formname.'_selector_0").attr("name","'.$formname.'");
				}
				'.(!empty($getattributes)?'getattributes();':'').'
			}
		';

	if(!empty($value)) {
		$upids = explode("|", getupid($value,$categorylist));
		$org = 0;
		for($i=count($upids);$i > 1; $i--) {
			if($upids[$i-1]!=0) {
				$html .= '$("#'.$formname.'_selector_'.$org.'").val('.$upids[$i-1].');$("#'.$formname.'_selector_'.$org.'").change();';
				$org++;
			}
		}

		$html.= '$("#'.$formname.'_selector_'.$org.'").val('.$value.');$("#'.$formname.'_selector_'.$org.'").change();';
	}
	$html .= '});</script></span>';
	return $html;
}
function html2bbcode($str) {
	preg_match_all("/\<img[^>]*aid=\"(\d+)\"[^>]*src\=\".*?\"[^>]*>/i", $str, $match);
	foreach($match[0] as $key=>$matchs) {
		$str = str_replace($matchs,"[attach]".$match[1][$key]."[/attach]",$str);
	}
	return $str;
}

function bbcode2html($str) {
	preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $str, $match);
	$query = DB::query("SELECT * FROM ".tname("photoitems")." WHERE itemid IN ('".implode("', '", $match[1])."')");
	while ($result = DB::fetch($query)) {
		$imagesrc[$result['itemid']] = getattachurl($result['subjectimage'], 1);
	}
	foreach($match[0] as $key=>$matchs) {
		$str = str_replace($matchs,"<img aid=\"".$match[1][$key]."\" src=\"".$imagesrc[$match[1][$key]]."\" />",$str);
	}
	return $str;
}

function check_cpaccess() {
	global $_G, $_SGLOBAL;
	$session = DB::fetch_first("SELECT * FROM ".tname("admincp_member")." WHERE uid = '$_G[uid]'");
	if(!empty($session['customperm'])) {
		$session['customperm'] = unserialize($session['customperm']);
	}
	$_SGLOBAL['adminsession'] = $session;
}
function load_admin_perms() {
	global $_G, $_SGLOBAL;
	if($_SGLOBAL['adminsession']['cpgroupid']) {
		$query = DB::query("SELECT perm FROM ".DB::table('admincp_perm')." WHERE cpgroupid='{$_SGLOBAL['adminsession']['cpgroupid']}'");
		while ($perm = DB::fetch($query)) {
			if(empty($_SGLOBAL['adminsession']['customperm'])) {
				$perms[$perm['perm']] = true;
			} elseif(!in_array($perm['perm'], (array)$_SGLOBAL['adminsession']['customperm'])) {
				$perms[$perm['perm']] = true;
			}
		}
		$cpgroupshopcats = DB::result_first("SELECT cpgroupshopcats FROM ".DB::table('admincp_group')." WHERE cpgroupid='{$_SGLOBAL['adminsession']['cpgroupid']}'");
		if($cpgroupshopcats) {
			$_SGLOBAL['adminsession']['cpgroupshopcats'] = $cpgroupshopcats;
		} else {
			$_SGLOBAL['adminsession']['cpgroupshopcats'] = 0;
		}
	} else {
		$perms['all'] = true;
	}
	return $perms;
}
function permallow($action,$perms) {
	if(isset($perms['all'])) {
		return $perms['all'];
	}

	if(!empty($_POST) && !array_key_exists('_allowpost', $perms)) {
		return false;
	}
	if(in_array($action, array('map', 'theme', 'modifypasswd'))) {
		$action = 'shopinfoedit';
	}
	switch($action) {
		case 'index':
			return true;
			break;
		case 'shopinfoedit';
		$key = 'list&m=shop';
		break;
		case 'list':
			if(!empty($_GET['m'])&&$_GET['m']=='shop'&&!empty($_GET['grade'])&&$_GET['grade']==0) {
				$key = 'list&m=shop&grade=0&optpass=1&filtersubmit=GO';
			} elseif(!empty($_GET['m'])) {
				$key = $action.'&m='.$_GET['m'];
			} else {
				$key = $action;
			}
			break;
		case 'category':
			if(!empty($_GET['type'])) {
				$key = $action.'&type='.$_GET['type'];
			} else {
				$key = $action;
			}
			if(!empty($_GET['op'])&&$_GET['op']=='del') {
				if(!array_key_exists('_allowpost', $perms)) {
					return false;
				}
			}
			break;
		case 'attribute':
			$action = 'category';
			if(!empty($_GET['type'])) {
				$key = $action.'&type='.$_GET['type'];
			}
			break;
		case 'edit':
			$action = 'list';
			if(!empty($_GET['m'])) {
				$key = $action.'&m='.$_GET['m'];
			}
			break;
		case 'add':
			if(!empty($_GET['m']) && $_GET['m'] != 'shop') {
				if($_GET['m'] == 'brandlinks') {
					$key = 'brandlinks';
				} else {
					$action = 'list';
					$key = $action.'&m='.$_GET['m'];
				}
			} else {
				$key = $action.'&m='.$_GET['m'];
			}
			break;
		case 'batchmod':
			$action = 'list';
			if(!empty($_GET['m'])) {
				$key = $action.'&m='.$_GET['m'];
			}
			break;
		case 'tool':
			if(!empty($_GET['operation'])) {

				$key = $action.'&operation='.$_GET['operation'];
				if(in_array($_GET['operation'],array('updatesubcatid', 'updatememberstats', 'changeallowner', 'updateshopitemnum'))) {
					$key = $action.'&operation=updatecache';
				}
			}
			break;
		case 'remark':
			$key = 'comment';
			break;
		case 'db':
			$key = 'db&operation=export';
			break;
		case 'logs':
			$key = 'logs&operation=admin';
			break;
		default:
			$key = $action;
			break;
	}
	//return returnperm($key,$perms);
	if(isset($perms[$key])) {
		return $perms[$key];
	}
	return false;
}

function returnperm($key,$perms) {
	if(isset($perms[$key])) {
		return $perms[$key];
	} else {
		return false;
	}
}
function check_itemaccess($itemid, $mname) {
	global $_G, $_SGLOBAL;
	if($mname == "shop") {
		$shopid = $itemid;
	} else {
		$shopid = DB::result_first("SELECT shopid FROM ".DB::table($mname."items")." WHERE itemid = $itemid");
	}
	if(!empty($shopid)) {
		$shopcatid = DB::result_first("SELECT catid FROM ".DB::table("shopitems")." WHERE itemid = $shopid");
	}
	if(!empty($shopcatid)) {
		if(!empty($_SGLOBAL['adminsession']['cpgroupshopcats']) && in_array($shopcatid, explode(",", $_SGLOBAL['adminsession']['cpgroupshopcats']))) {
			return true;
		}
	}
	return false;
}
?>