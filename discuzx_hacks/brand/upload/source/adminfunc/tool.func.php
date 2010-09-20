<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: tool.func.php 4446 2010-09-14 11:35:06Z xuhui $
 */

if(!defined('IN_STORE') && !defined('IN_ADMIN')) {
	exit('Acess Denied');
}

//更新子分類關聯
function updatesubcatid($upid=0,$type = null) {
	global $_G, $_SGLOBAL;
	$typequery = $type != null ? ' AND type = \''.$type.'\'' : '';
	$topquery = NULL;
	$topcat = array();
	$topquery = DB::query('SELECT catid FROM '.tname('categories').' WHERE  upid=\''.$upid.'\' '.$typequery.' ORDER BY catid;');
	while($topcat = DB::fetch($topquery)) {
		$subquery = NULL;
		$subcats = $subcat = array();
		$subcatstr = '';
		$subcats[$topcat['catid']] = $topcat['catid'];
		$subquery = DB::query('SELECT catid FROM '.tname('categories').' WHERE upid=\''.$topcat['catid'].'\' '.$typequery.' ORDER BY catid;');
		while($subcat = DB::fetch($subquery)) {
			$subcats[$subcat['catid']] = $subcat['catid'];
		}
		$subcatstr = implode(', ', $subcats);
		if(!$subcatstr) { $subcatstr = $topcat['catid'];}
		DB::query('UPDATE '.tname('categories').' SET subcatid=\''.$subcatstr.'\' WHERE catid=\''.$topcat['catid'].'\' LIMIT 1;');
		updatesubcatid($topcat['catid']);  //遞歸處理所有子分類
	}
	return 'message_success';
}

//更新店長權限
function updatememberstats($uid=0) {
	global $_G, $_SGLOBAL;
	/*$where = array();
	$uid = intval($uid);
	if($uid>0) {
		$where[] = "m.uid='$uid'";
	}
	$where[] = 'm.myshopid>0';
	$shopgrade = array("='0'", "='1'", ">'2'");
	$shopstatus = array('none', 'unverified', 'verified');
	//grade和status的對應關係
	$wheresql = implode(' AND ', $where);
	/*foreach($shopgrade as $key=>$value) {
		DB::query('UPDATE '.tname('members').' m INNER JOIN '.tname('shopitems')." s ON m.myshopid=s.itemid SET m.myshopstatus='{$shopstatus[$key]}' WHERE $wheresql AND s.grade{$value}");
	}*/
	return 'message_success';
}

//更新顯示順序
function changedisplayorder($displayarr = array(), $mname='shop', $wheresql='') {
	global $_G, $_SGLOBAL, $cookie_referer;

	foreach($displayarr as $key=>$value) {
		$key = intval($key);
		$value = intval($value);
		if($key > 0) {
			DB::query('UPDATE '.tname($mname.'items').' SET displayorder'.(pkperm('isadmin')?'':'_s').'=\''.$value.'\' WHERE itemid=\''.$key.'\' '.$wheresql);
		}
	}
	cpmsg('message_success', $cookie_referer);
}



//刪除信息
function delmitems($wheresql='', $type = 'shop') {
	global $_G, $_SGLOBAL, $itemarr, $mname, $_BCACHE;

	if(!empty($wheresql)) {
		$thissql = str_replace(' itemid IN', ' i.itemid IN', $wheresql);
		$itemidsql = $relatedidsql = '';
		if($mname != 'shop') {
			$itemidsql = 'itemid';
			$relatedidsql = 'relatedid';
		}
		if($type=='shop') {
			$subsql = str_replace(' itemid IN', ' shopid IN', $wheresql);
			if($_POST['opdelete']) {
				//遞歸刪除所有店舖的子元素
				delmitems($subsql, 'good');
				delmitems($subsql, 'consume');
				delmitems($subsql, 'notice');
				delmitems($subsql, 'photo');
				delmitems($subsql, 'album');
				$commquery = DB::query("SELECT cid FROM ".tname("spacecomments")." WHERE $wheresql");
				while($res = DB::fetch($commquery)) {
					deletecomment($res['cid']);
				}
				$shop_related_sql = str_replace(' itemid IN', ' shopid IN', $wheresql);
				DB::query("DELETE FROM ".tname('relatedinfo')." WHERE ".$shop_related_sql);
			}
			$selectsql = ' i.itemid, i.subject, m.banner, m.windowsimg ';
			$joinsql = tname('shopitems').' i INNER JOIN '.tname('shopmessage').' m ON i.itemid=m.itemid ';
		} elseif($type=='album') {
			$subsql = str_replace(' itemid IN', ' i.albumid IN', $wheresql);
			delmitems($subsql, 'photo');
			$selectsql = ' i.itemid, i.subjectimage ';
			$joinsql = tname($type.'items').' i';
			// 刪除關聯信息
			$related_sql = str_replace(' itemid IN', ' IN', $wheresql);
			DB::query("DELETE FROM ".tname('relatedinfo')." WHERE relatedtype = 'album' AND $relatedid ".$related_sql);
		} elseif($type=='photo') {
			$selectsql = ' i.itemid, i.albumid, i.subjectimage ';
			$joinsql = tname($type.'items').' i';
		} else {
			$selectsql = ' i.itemid, i.subjectimage ';
			$joinsql = tname($type.'items').' i';

			$related_sql = str_replace(' itemid IN', ' IN', $wheresql);
			if ($type == 'good' || $type == 'groupbuy') {
				DB::query("DELETE FROM ".tname('relatedinfo')." WHERE type = '".$type."' AND $itemidsql ".$related_sql);
			}
			DB::query("DELETE FROM ".tname('relatedinfo')." WHERE relatedtype = '".$type."' AND $relatedid ".$related_sql);
		}
		$thisalbumid = 0;
		$query = DB::query('SELECT '.$selectsql.' FROM '.$joinsql.' WHERE '.$thissql);
		$filefields = array('subjectimage', 'banner', 'windowsimg');//刪除圖片
		while($value = DB::fetch($query)) {
			foreach($filefields as $v) {
				if(!empty($value[$v]) && strstr($value[$v], '.jpg')) {
					@unlink(A_DIR.'/'.$value[$v]);
					@unlink(A_DIR.'/'.substr($value[$v], 0, -4).'.thumb.jpg');
				}
			}
			$thisalbumid = $value['albumid'];
		}
		if(in_array($type, array('album', 'photo'))) {
			$query = DB::query('DELETE i FROM '.tname($type.'items').' i WHERE '.$thissql);//刪除相冊和圖片信息，只有item表
			if($type=='photo' && $thisalbumid>0) {
				$picnums = DB::affected_rows($query);
				$query = DB::query('UPDATE '.tname('albumitems')." SET `picnum`=`picnum`-$picnums WHERE itemid='$thisalbumid'");//刪除相冊和圖片信息，只有item表
				if(!empty($thisalbumid)) {
					require_once(B_ROOT.'./api/bbs_syncpost.php');
					syncalbum($thisalbumid);
				}				
				if($_SGLOBAL['panelinfo']['group']['verifyalbum']) {
					$thisalbumgrade = DB::result_first('SELECT grade FROM '.tname('albumitems').' WHERE itemid=\''.$thisalbumid.'\'');
					if($thisalbumgrade == 1) {
						DB::query("UPDATE ".tname('albumitems')." SET grade = 0 WHERE itemid = '$thisalbumid'");
					}

				}
			}
		} elseif($type == 'shop') {
			foreach($itemarr as $itemid) {
				updatemyshopid($itemid);
				deletetable('shopitems', array('itemid'=>$itemid));
				deletetable('shopmessage', array('itemid'=>$itemid));
			}
		} else {
			$query = DB::query('DELETE i, m FROM '.tname($type.'items').' i INNER JOIN '.tname($type.'message').' m ON i.itemid=m.itemid WHERE '.$thissql);//刪除信息
		}
	}
}

function deletecomment($cid=0) {
	global $_G, $_SGLOBAL;
	$cid = intval($cid);
	if($cid>0) {
		$query = DB::query("SELECT itemid, type, upcid FROM ".tname("spacecomments")." WHERE cid = '$cid'");
		$commentinfo = DB::fetch($query);
		DB::query("DELETE FROM ".tname("spacecomments")." WHERE cid='$cid'");
		DB::query("DELETE FROM ".tname("commentscores")." WHERE cid=$cid");
		if($commentinfo['upcid']>0) {
			deletecomment($commentinfo['upcid']);
		} else {
			DB::query("UPDATE ".tname($commentinfo['type']."items")." SET replynum=replynum-1 WHERE itemid='$commentinfo[itemid]'");
		}
	}
	return 'message_success';
}

//更新信息所有者
function changeowner($uid=1, $itemarr='') {
	global $_G, $_SGLOBAL, $mname, $cookie_referer, $_BCACHE;

	if(!empty($itemarr)) {
		$uid = intval($uid);
		if($mname!='shop') {
			cpmsg('mod_notinshop', $cookie_referer); //非店舖不能修改所有者
		}

		if($uid>0) {
			require_once(B_ROOT.'./uc_client/client.php');
			$tmp = uc_get_user($uid, 1);
			$uid = intval($tmp[0]); //讓UC驗證該id是否存在
			$username = addslashes($tmp[1]);
			$email = addslashes($tmp[2]);
			if(!($uid>0 && $username)) {
				cpmsg('no_uid', $cookie_referer, '', '', true, 3);//UC中沒有該用戶
			}

			$thisshopid = DB::result_first("SELECT myshopid FROM ".tname('members')." WHERE uid='$uid'");
			if($thisshopid === false) {
				//會員表中沒有該用戶，自動插入數據
				$insertsqlarr = array(
						'uid' => $uid,
						'username' => $username,
						'password' => '',
						'groupid' => 12,
						'email' => $email,
						'dateline' => $_G['timestamp'],
						'updatetime' => $_G['timestamp'],
						'lastlogin' => 0,
						'ip' => $_G['clientip']
						);
				inserttable('members', $insertsqlarr);
			}
			foreach($itemarr as $itemid) {
				updatemyshopid($itemid);
				updatetable('shopitems', array('uid' => $uid, 'username' => $username), array('itemid'=>$itemid));
			}
			if(!ckfounder($uid)) {
				updatetable('members', array('myshopid' => intval($itemarr[0])), array('uid'=>$uid));
				$_BCACHE->deltype('sitelist', 'shop', $uid);
			}
		} else {
			cpmsg('no_uidanditemid', $cookie_referer);//提交的數據不合法
		}
	}
}

//被刪除或更改店舖所有者的店長重置myshopid
function updatemyshopid($itemid) {
	global $_BCACHE;

	$uid = DB::result_first('SELECT uid FROM '.tname('shopitems')." WHERE itemid='$itemid'");
	$anothershopid = DB::result_first('SELECT itemid FROM '.tname('shopitems')." WHERE itemid<>'$itemid' AND uid='$uid'");
	$anothershopid = !empty($anothershopid) ? $anothershopid : 0;
	updatetable('members', array('myshopid' => $anothershopid), array('uid'=>$uid));
	$_BCACHE->deltype('sitelist', 'shop', $uid);
}

//更新所有信息的所有者
function changeallowner() {
	$query = DB::query('SELECT uid,itemid FROM '.tname('shopitems').'');
	while($shop = DB::fetch($query)) {
		DB::query("update ".tname('members')." set myshopid=".$shop['itemid']." where uid=".$shop['uid']);              
	}
	return 'message_success';
}

//後台管理日誌
function managelog($items = '', $opcheck = 3, $check_txt = '') {
	global $_G, $_SGLOBAL, $mname;

	$itemarr = array();
	$selectsql = $mname != 'shop' ? ', shopid' : '';
	$query = DB::query('SELECT itemid, uid, username, subject'.$selectsql.' FROM '.tname($mname.'items').' WHERE itemid IN ('.$items.')');
	while($value=DB::fetch($query)) {
		$value['shopid'] = $mname != 'shop' ? $value['shopid'] : $value['itemid'];
		$value['shopname'] = $mname != 'shop' ? DB::result_first('SELECT subject FROM '.tname('shopitems').' WHERE itemid=\''.$value['shopid'].'\'') : $value['subject'];
		$itemarr[] = $value;
	}
	$check_txt = !empty($check_txt) ? trim($check_txt) : lang('mod_checktxt_change');
	if(!empty($itemarr)) {
		foreach($itemarr as $item) {
			$reason = trim(strip_tags(str_replace(array('USERNAME', 'SHOPTITLE', 'GRADENOW'), array(saddslashes($item['username']), saddslashes($item['shopname']), $_SGLOBAL['shopgrade'][$opcheck]), $check_txt)));
			$setsqlarr = array(
				'type' => $mname,
				'itemid' => $item['itemid'],
				'uid' => $item['uid'],
				'username' => $item['username'],
				'opcheck' => intval($opcheck),
				'reason' => $reason,
				'shopid' => $item['shopid'],
				'dateline' => $_G['timestamp']
			);
			inserttable('managelog', $setsqlarr);
		}
	}
}

//審核短信通知
function gradechange($items='', $opcheck=3, $check_txt='', $model='shop') {
	global $_G, $_SGLOBAL;
	require_once(B_ROOT.'./uc_client/client.php');
	if($model != 'shop') {
		$query = DB::query('SELECT shopid,itemid,subject FROM '.tname($model.'items').' WHERE itemid IN ('.$items.')');
		while($result = DB::fetch($query)) {
			$shopids[] = $result['shopid'];
			$itemsinfo[$result['itemid']] = $result;
		}
		//echo 'SELECT shopid FROM '.tname($model.'items').' WHERE itemid IN ('.$items.')';
		//print_r($shopids);
		$items = implode(",", $shopids);
	}
	$query = DB::query('SELECT uid, username, subject, itemid as shopid FROM '.tname('shopitems').' WHERE itemid IN ('.$items.')');
	while($value=DB::fetch($query)) {
		$passuids .= $value['uid'].', ';
		$passuidarr[$value['shopid']] = $value;
	}
	if($passuidarr) {
		if($model == 'shop') {
			$passuids = substr($passuids, 0, -2);
			$pmcont = !empty($check_txt)?$check_txt:lang('mod_checktxt_change');
			switch($opcheck) {
				case 1:
					$pmtitle = lang('mod_checktitle_fail');
					//if($model == 'shop') DB::query('UPDATE '.tname('members')." SET myshopstatus='unverified' WHERE uid IN ('$passuids')");//審核後將狀態更新至members表
					break;
				case 2:
					$pmtitle = lang('mod_checktitle_close');
					//if($model == 'shop') DB::query('UPDATE '.tname('members')." SET myshopstatus='verified' WHERE uid IN ('$passuids')");
					break;
				case 3:
					$pmtitle = lang('mod_checktitle_pass');
					//if($model == 'shop') DB::query('UPDATE '.tname('members')." SET myshopstatus='verified' WHERE uid IN ('$passuids')");
					break;
				case 4:
					$pmtitle = lang('mod_checktitle_recommend');
					//if($model == 'shop') DB::query('UPDATE '.tname('members')." SET myshopstatus='verified' WHERE uid IN ('$passuids')");
					break;
				default:
					$pmtitle = lang('mod_checktitle_change');
					//if($model == 'shop') DB::query('UPDATE '.tname('members')." SET myshopstatus='unverified' WHERE uid IN ('$passuids')");
					break;
			}
			foreach($passuidarr as $eachuid) {
				$tmpcont = trim(strip_tags(str_replace(array('USERNAME', 'SHOPTITLE', 'GRADENOW'), array(saddslashes($eachuid['username']), saddslashes($eachuid['subject']), $_SGLOBAL['shopgrade'][$opcheck]), $pmcont)));
				//$_G['uid'] . $eachuid['uid'] . $pmtitle . $tmpcont;
				uc_pm_send(0, $eachuid['uid'] , $pmtitle , $tmpcont);
			}
		} else {
			$pmtitle = $opcheck;
			$pmcont = !empty($check_txt)?$check_txt:lang('mod_checktxt_change_'.$model);
			foreach($itemsinfo as $itemid=>$iteminfo) {
				$eachuid = $passuidarr[$iteminfo['shopid']];
				$tmpcont = trim(strip_tags(str_replace(array('USERNAME', 'SUBJECT'), array($eachuid['username'], '[url='.B_URL.'/store.php?id='.$iteminfo['shopid'].'&action='.$model.'&xid='.$itemid.']'.$iteminfo['subject'].'[/url]'), $pmcont)));
				uc_pm_send(0, $eachuid['uid'] , $pmtitle , $tmpcont);
			}
		}
	}

	//grade_s店舖狀態信息數據冗余到相冊、消費券等各個物件表中
	if($model=='shop') {
		$models = array('album', 'consume', 'good', 'groupbuy', 'notice', 'photo');
		foreach($models as $m) {
			DB::query("UPDATE ".tname($m.'items')." i INNER JOIN ".tname('shopitems')." si ON i.shopid=si.itemid SET i.grade_s = si.grade WHERE i.shopid IN ($items)", 'UNBUFFERED');
		}
	}
}

/**
 * 檢查模形狀態
 */
function checkmodel($name) {
	$state = true;
	$tmpdelarr = array('items', 'message');
	foreach($tmpdelarr as $tmpkey => $tmpvalue) {
		if(!$tableinfo = loadtable($name.$tmpvalue)) {
			$state = false;
			break;
		}
	}
	return $state;
}

function loadtable($table, $force = 0) {
	global $_G, $_SGLOBAL;
	$tables = array();

	if(!isset($tables[$table]) || $force) {
		if(DB::version() > '4.1') {
			$query = DB::query("SHOW FULL COLUMNS FROM ".tname($table), 'SILENT');
		} else {
			$query = DB::query("SHOW COLUMNS FROM ".tname($table), 'SILENT');
		}
		while($field = @DB::fetch($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	if(isset($tables[$table])) {
		return $tables[$table];
	}
	return $tables;
}
function update_album_info($subjectarr=array(), $mname='album', $wheresql='') {
		global $_G, $_SGLOBAL, $cookie_referer;
		if($mname == 'album' || $mname == 'photo') {
			if($mname == 'album' && $_SGLOBAL['panelinfo']['group']['verifyalbum']) {
				$query = DB::query("SELECT * FROM ".tname("albumitems")." WHERE itemid IN ('".implode("' ,'", $_POST['item'])."') AND grade > 0 AND shopid = '".$_SGLOBAL['panelinfo']['itemid']."'");
				while($result = DB::fetch($query)) {
					$albums[$result['itemid']] = $result;
				}

				foreach($subjectarr as $k=>$v){
					if(!empty($albums[$k])) {
						$update = $albums[$k];
						$update['subject'] = $v;
						$update = serialize($update);
						if($albums[$k]['grade'] > 1) {
							DB::query("REPLACE INTO ".tname("itemupdates")." (`itemid`, `type`, `updatestatus`, `update`) VALUES ($k, 'album', '1', '$update');");
							DB::query("UPDATE ".tname("albumitems")." SET updateverify = 1 WHERE itemid = '$k' ;");
						} else {
							DB::query("UPDATE ".tname($mname.'items')." SET subject='$v', grade = 0 WHERE itemid='$k'");
						}
					} else {
						$sql="UPDATE ".tname($mname.'items')." SET subject='$v' WHERE itemid='$k'";
						DB::query($sql);

					}

				}
			} else {
				foreach($subjectarr as $k=>$v){
					$sql="UPDATE ".tname($mname.'items')." SET subject='$v' WHERE itemid='$k' AND $wheresql";
					DB::query($sql);
				}
			}
			cpmsg('message_success', $cookie_referer);
		} else {
			cpmsg('UPDATE Denied');
		}
		exit;
}

// 更改相冊分類和屬性（管理員）
function album_movecat($itemarr=array()) {
	global $_G, $_SGLOBAL, $cookie_referer;
	require_once(B_ROOT.'./batch.attribute.php');
	$num_ok = $num_error = 0;
	//驗證catid
	$catid = $_POST['catid'] = DB::result_first('SELECT catid FROM '.tname('categories')." WHERE catid='$_POST[catid]' AND `type`='album'");
	if($catid && $itemarr) {
		foreach($itemarr as $itemid) {
			$itemid = intval($itemid);
			$shopid = DB::result_first('SELECT shopid FROM '.tname('albumitems')." WHERE itemid='$itemid'");
			getpanelinfo($shopid);
			if($_SGLOBAL['panelinfo']['group']['album_field'] != 'all' && !in_array($catid, explode(",", $_SGLOBAL['panelinfo']['group']['album_field']))) {
				$num_error++;
				continue;
			} else {
				DB::query('UPDATE '.tname('albumitems')." SET catid='$catid' WHERE itemid='$itemid'");
 
				DB::query('DELETE FROM '.tname('itemattribute')." WHERE itemid='$itemid'");
				setattributesettings($_POST['catid'], $itemid, $_POST['attr_ids']);
				$num_ok++;
			}
		}
		cpmsg(lang('mod_album_success1').$num_ok.lang('mod_album_success2').$num_error.lang('mod_album_success3'), $cookie_referer);
	}
	cpmsg('no_operation');
}

// 更改相冊分類和屬性（店長）
function album_movecat_panel($wheresql) {
	global $_G, $_SGLOBAL, $cookie_referer;
	require_once(B_ROOT.'./batch.attribute.php');
	$num_ok = $num_error = 0;
	$itemarr = $gradearr = array();
	//驗證catid
	$catid = $_POST['catid'] = DB::result_first('SELECT catid FROM '.tname('categories')." WHERE catid='$_POST[catid]' AND `type`='album'");
	if($_SGLOBAL['panelinfo']['group']['album_field'] != 'all' && !in_array($catid, explode(",", $_SGLOBAL['panelinfo']['group']['album_field']))) {
		cpmsg('no_perm', $cookie_referer);
	}
	$query = DB::query('SELECT itemid, grade FROM '.tname('albumitems')." WHERE $wheresql");
	while($result = DB::fetch($query)) {
		$itemarr[$result['itemid']] = $result['itemid'];
		$gradearr[$result['itemid']] = $result['grade'];
	}
	if($catid && $itemarr) {
		if($_SGLOBAL['panelinfo']['group']['verifyalbum']) {
			foreach($itemarr as $itemid) {
				if($gradearr[$itemid]>1) {
					$query = DB::query('SELECT * FROM '.tname('albumitems')." WHERE itemid='$itemid' AND shopid='$_G[myshopid]' LIMIT 1");
					$update = DB::fetch($query);
					$update['catid'] = $catid;
					$update['attr_ids'] = $_POST['attr_ids'];
					$update = serialize($update);
					DB::query("REPLACE INTO ".tname("itemupdates")." (`itemid`, `type`, `updatestatus`, `update`) VALUES ($itemid, 'album', '1', '$update');");
					DB::query("UPDATE ".tname("albumitems")." SET updateverify = 1 WHERE itemid = '$itemid' ;");
					$num_ok++;
				} elseif($gradearr[$itemid] == 1) {
					DB::query("UPDATE ".tname("albumitems")." SET grade = 0 WHERE itemid = '$itemid' ;");
					$num_error++;
				}
			}
			cpmsg(lang('mod_album_success1').$num_ok.lang('mod_album_success2').$num_error.lang('mod_album_success3').lang('mod_album_success4'), $cookie_referer);
		} else {
			foreach($itemarr as $itemid) {
				 DB::query('UPDATE '.tname('albumitems')." SET catid='$catid' WHERE itemid='$itemid' AND shopid='$_G[myshopid]'");
				 if(DB::affected_rows()) {
					DB::query('DELETE FROM '.tname('itemattribute')." WHERE itemid='$itemid'");
					setattributesettings($_POST['catid'], $itemid, $_POST['attr_ids']);
					$num_ok++;
				 } else {
					$num_error++;
				 }
			}
			cpmsg(lang('mod_album_success1').$num_ok.lang('mod_album_success2').$num_error.lang('mod_album_success3'), $cookie_referer);
		}
	}
	cpmsg('no_operation');
}

function setalbumimg() {
	global $_G, $_SGLOBAL;
	$albumid = intval($_REQUEST['albumid']);
	$photoid = intval($_REQUEST['photoid']);
	if(pkperm('isadmin')) {
		$pwheresql = " itemid='$photoid'";
		$awheresql = " itemid='$albumid'";
	} else {
		$pwheresql = " itemid='$photoid' AND shopid='$_G[myshopid]'";
		$awheresql = " itemid='$albumid' AND shopid='$_G[myshopid]'";
	}
	$imgurl = DB::result_first('SELECT subjectimage FROM '.tname('photoitems').' WHERE '.$pwheresql);
	if($imgurl) {
		if(strpos($imgurl, 'http://')===0) {
			//遠程圖片的相冊保存到本地做個縮略圖
			$oldalbumimg = DB::result_first('SELECT subjectimage FROM '.tname('albumitems')." WHERE $awheresql AND frombbs='1'");
			if(!empty($oldalbumimg)) {
				@unlink(A_DIR.'/'.$oldalbumimg);
				@unlink(A_DIR.'/'.substr($oldalbumimg, 0, -4).'.thumb.jpg');
			}
			$remoteattach = loadClass('attach')->saveremotefile($imgurl, array(320, 240));
			$imgurl = $remoteattach['file'];
		}
		$query = DB::query('UPDATE '.tname('albumitems')." SET subjectimage='$imgurl' WHERE $awheresql");
		$num = DB::affected_rows($query);
	}
	if($num>0) {
		cpmsg('message_success', '', 'success', '', true, true);
	} else {
		cpmsg('message_fail', '', 'error', '', true, true);
	}

}

function image_text($arr){
	global $_G, $_SGLOBAL;
	//for 55bbs

	foreach($arr as $key=>$value) {
		$$key = $value;
	}

	// Create the image
	$im = imagecreatefromjpeg("static/image/consume/$id.jpg");

	// Create some colors
	if($id==1) {
		$color = imagecolorallocate($im, 111, 78, 0);
		$color1 = imagecolorallocate($im, 120, 87, 4);
		$color2 = imagecolorallocate($im, 120, 87, 4);
	} elseif($id==2) {
		$color = imagecolorallocate($im, 111, 78, 0);
		$color1 = imagecolorallocate($im, 111, 78, 0);
		$color2 = imagecolorallocate($im, 92, 63, 4);
	} elseif($id==3) {
		$color = imagecolorallocate($im, 24, 66, 90);
		$color1 = imagecolorallocate($im, 51, 51, 51);
		$color2 = imagecolorallocate($im, 88, 68, 7);
	} elseif($id==4) {
		$color = imagecolorallocate($im, 47, 89, 5);
		$color1 = imagecolorallocate($im, 51, 51, 51);
		$color2 = imagecolorallocate($im, 82, 59, 0);
	} elseif($id==5) {
		$color = imagecolorallocate($im, 128, 32, 90);
		$color1 = imagecolorallocate($im, 51, 51, 51);
		$color2 = imagecolorallocate($im, 91, 63, 13);
	}

	// The text to draw
	$title = base64_encode($coupon_title);
	$consume_to = lang('consume_to');
	$dealer_name = biconv($dealer_name, $_G['charset'], 'UTF-8');
	$coupon_title = biconv($coupon_title, $_G['charset'], 'UTF-8');
	$begin_date = biconv($begin_date, $_G['charset'], 'UTF-8');
	$end_date = biconv($end_date, $_G['charset'], 'UTF-8');
	$date = $begin_date.$end_date;
	$consume_to = biconv($consume_to, $_G['charset'], 'UTF-8');
	$brief = biconv($brief, $_G['charset'], 'UTF-8');
	$exception = biconv($exception, $_G['charset'], 'UTF-8');
	$text6 = biconv(lang('consume_55note'), $_G['charset'], 'UTF-8');
	$address = biconv($address, $_G['charset'], 'UTF-8');
	$hotline = biconv($hotline, $_G['charset'], 'UTF-8');


	$date = $begin_date.$consume_to.$end_date;
	// Replace path by your own font path
	$font = 'static/image/fonts/'.$_G['setting']['fontpath'];

	// Add the text
	imagettftext($im, 23, 0, 220, 120, $color1, $font, $dealer_name);
	imagettftext($im, 18, 0, 160, 167, $color1, $font, $coupon_title);
	imagettftext($im, 9, 0, 565, 35, $color2, $font, $date);
	change_row($im, 10, 0, 158, 218, $brief, $color, $font, 38);
	change_row($im, 10, 0, 144, 320, $exception, $color, $font, 39);
	change_row($im, 10, 0, 144, 372, $address, $color, $font, 39);
	imagettftext($im, 10, 0, 144, 406, $color, $font, $hotline);

	// Using imagepng() results in clearer text compared with imagejpeg()
	$dirpath = loadClass('attach')->getattachdir();
	if(!empty($dirpath)) {
		$dirpath .= '/';
	}
	if($preview == 1) {
		$filemain = $_G['uid'];
	} else {
		$_SGLOBAL['_num'] = empty($_SGLOBAL['_num']) ? 0 : intval($_SGLOBAL['_num']);
		$_SGLOBAL['_num']++;
		$filemain = $_G['uid'].'_'.sgmdate($_G['timestamp'], 'YmdHis').$_SGLOBAL['_num'].random(4);
	}
	$consumeimgpath = $dirpath.$filemain.'.jpg';
	$status = imagejpeg($im, A_DIR.'/'.$consumeimgpath, 80);
	imagedestroy($im);
	if($status) {
		if($preview != 1) {
			$filesize = filesize(A_DIR.'/'.$consumeimgpath);
			$hash = getmodelhash($mid, $itemid);
			$width = !empty($subjectimagewidth) ? $subjectimagewidth : 100;
			$height = !empty($subjectimageheight) ? $subjectimageheight : 100;
			$thumbarr = array($width, $height);
			$thumbpath = loadClass('image')->makethumb($consumeimgpath, $thumbarr);
			$insertsqlarr = array(
				'isavailable' => 1,
				'type' => 'model',
				'itemid' => $itemid,
				'uid' => $_G['uid'],
				'dateline' => $_G['timestamp'],
				'filename' => 'consume_'.$id.'.jpg',
				'subject' => 'subjectimage',
				'attachtype' => 'jpg',
				'isimage' => 1,
				'size' => $filesize,
				'filepath' => $consumeimgpath,
				'thumbpath' => $thumbpath,
				'hash' => $hash
			);
			inserttable('attachments', $insertsqlarr);
		}
		return $consumeimgpath;
	} else {
		return false;
	}
}

function change_row(&$im, $size, $angle, $x, $y, $str, $color, $font, $row_charnum) {
	$searcharray = array("<br/>", "<BR/>", "<br>", "<BR>", "<p>", "</p>", "\\r", "\\n", "\r", "\n");
	$str = str_replace($searcharray, "!)@(", $str);
	$ex_arr = array();
	$ex_arr = explode("!)@(", $str);
	foreach($ex_arr as $key => $value) {
		$arr = array();
		$count = bstrlen($value, 'utf8');
		$i = 0;
		while($i < $count - 1) {
			if(function_exists('mb_substr')) {
				$arr[] = mb_substr($value, $i, $row_charnum, "utf-8");
			} else {
				$strcut = cutstr($value, $row_charnum, 0, 'utf8');
				$arr[] = $strcut;
				$value = str_replace($strcut, '', $value);
			}
			$i += $row_charnum;
		}
		if(is_array($arr)) {
			foreach($arr as $key=>$vl) {
				imagettftext($im, $size, $angle, $x, $y, $color, $font, $vl);
				$y += 18;
			}
		}
	}
}

function batchmodadmindeletecache($mname) {
	global $_G, $_BCACHE;
	if($_GET['operation']=='setalbumimg') {
		//相冊封面的刪除緩存單獨處理
		$_BCACHE->deltype('detail', 'album', 0, intval($_GET['albumid']));
	} else {
		$_BCACHE->deltype('index', $mname);
		if($mname=='shop') {
			foreach($_POST['item'] as $itemid) {
				$itemid = intval(trim($itemid));
				if($itemid>0) {
					$_BCACHE->deltype('detail', $mname, $itemid);
				}
			}
		} else {
			$_BCACHE->deltype('storelist', $mname);
			foreach($_POST['item'] as $itemid) {
				$itemid = intval(trim($itemid));
				if($itemid>0) {
					$_BCACHE->deltype('detail', $mname, 0, $itemid);
				}
			}
		}
		$_BCACHE->deltype('sitelist', $mname);
	}
}

function batchmodpaneldeletecache($mname) {
	global $_G, $_SGLOBAL, $_BCACHE;
	if($_GET['operation']=='setalbumimg') {
		//相冊封面的刪除緩存單獨處理
		$_BCACHE->deltype('detail', 'album', 0, intval($_GET['albumid']));
	} else {
		foreach($_POST['item'] as $itemid) {
			$itemid = intval(trim($itemid));
			if($itemid>0) {
				$_BCACHE->deltype('detail', $mname, $_G['myshopid'], $itemid);
			}
		}
		$_BCACHE->deltype('sitelist', $mname);
		$_BCACHE->deltype('storelist', $mname, $_G['myshopid']);
	}
}

/*
 *刪除分類後，更新店舖組設置
*/
function synscategroiesforgroup($type,$catid) {
	//取出所有店舖組的分類設置
	$grouplistneedsyns = array();
	$categorylist = getmodelcategory($type);
	$query = DB::query("SELECT id, ".$type."_field FROM ".DB::table("shopgroup")."");
	while($result = DB::fetch($query)) {
		$result['catids'] = explode(",", $result[$type."_field"]);
		if(!empty($result['catids']) && in_array($catid, $result['catids'])) {
			$result['catids'] = array_diff($result['catids'], array($catid));
			foreach($result['catids'] as $key=>$value) {
				if(!array_key_exists($value,$categorylist)) {
					unset($result['catids'][$key]);
				}
			}
			$grouplistneedsyns[] = $result;
		}
	}
	//判斷是否更新、是則進行數據更新
	if(!empty($grouplistneedsyns)) {
		foreach($grouplistneedsyns as $group) {
			DB::update('shopgroup', array($type.'_field' => implode(",", $group['catids'])), "id='$group[id]'");
		}
	}
	return false;
}
?>