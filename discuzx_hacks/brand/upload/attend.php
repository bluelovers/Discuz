<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attend.php 4452 2010-09-14 12:35:50Z fanshengshuai $
 */

require_once('./common.php');
if (!$_G['setting']['multipleshop']) {
	if(intval($_G['myshopid']) > 0){
		showmessage($lang['oneowner_onshop'], "index.php");
	}
}

$do = $_GET['do'];
require_once(B_ROOT.'./uc_client/client.php');
include_once(B_ROOT.'./source/function/cache.func.php');
//读入缓存
$mname = 'shop';
$cacheinfo = getmodelinfoall('modelname', $mname);
$mid = $cacheinfo['models']['mid'];

if($do && $do=="register") {
	//第三步，提交数据，检查填写的基本信息
	if(submitcheck('attendsubmit')) {

		$checkunits = array(
			array('subject', '2', '30', $lang['attend_subject_error']),
			array('address', '5', '30', $lang['attend_address_error'])
		);
		if(!empty($cacheinfo['columns'])) {
			foreach($cacheinfo['columns'] as $column) {
				if($column['allowpost'] == 1 && $column['allowshow'] == 1 && $column['formtype'] != 'img' && $column['isrequired'] == 1 && preg_match('/(^ext_)|(^applicant)/',$column['fieldname'])) {
					$errormessage = !empty($lang['attend_'.$column['fieldname'].'_error']) ? $lang['attend_'.$column['fieldname'].'_error'] : ($column['fieldminlength'] < $column['fieldlength'] ? $column['fieldtitle'].$lang['is'].$column['fieldminlength'].'-'.$column['fieldlength'].$lang['word'] : $column['fieldlength'].$lang['word']);
					array_push($checkunits, array($column['fieldname'], $column['fieldminlength'], $column['fieldlength'], $errormessage));
				}
			}
		}
		$checkresults = array();
		foreach($checkunits as $unit) {
			$intoarray = 0;
			if(empty($_POST[$unit[0]])) {
				$intoarray = 1;
			} elseif(bstrlen($_POST[$unit[0]]) < $unit[1] || bstrlen($_POST[$unit[0]]) > $unit[2]) {
				$intoarray = 1;
			} elseif(in_array($unit[0], array('applicantmobi', 'applicantpost')) && !is_numeric($_POST[$unit[0]])) {
				$intoarray = 1;
			}
			if($intoarray == 1) {
				$checkresults[] = array($unit[0]=>$unit[3]);
			}
		}
		if($_POST['catid'] < 1) {
			array_push($checkresults, array('catid'=>$lang['attend_cat_must_select']));
		}
		if($_POST['region'] == -1) {
			array_push($checkresults, array('region'=>$lang['attend_region']));
		}
		if(empty($_G['uid'])) {
			$_POST['username'] = addslashes(trim(stripslashes($_POST['username'])));
			if(empty($_POST['username']) || strlen($_POST['username']) < 3 || strlen($_POST['username']) > 15) {
				array_push($checkresults, array('username'=>$lang['attend_username_error']));
			}
			if((intval($_POST['existuser']) == 0) && $_POST['password_1'] !== $_POST['password']) {
				array_push($checkresults, array('password_1'=>$lang['attend_password_repeat']));
			}
			if(empty($_POST['password']) || $_POST['password'] != addslashes($_POST['password'])) {
				array_push($checkresults, array('password'=>$lang['profile_passwd_illegal']));
			}
			if((intval($_POST['existuser']) == 0) && empty($_POST['email'])) {
				array_push($checkresults, array('email'=>$lang['attend_email_is_null']));
			}
		}

		if(!empty($checkresults)) {
			showmessage('applicant_info_failed', '', '', '', $checkresults);
		}
		$exist_user_auth = false;
		//没有登录
		if(empty($_G['uid'])) {

			setcookie('_refer', 'admin.php?action='.$_GET['action'].'&m='.$GET['m']);

			$_G['uid'] = $newuid = uc_user_register($_POST['username'], $_POST['password'], $_POST['email']);
			if($newuid <= 0) {
				if($newuid == -1) {
					array_push($checkresults, array('username'=>$lang['user_name_is_not_legitimate']));
				} elseif($newuid == -2) {
					array_push($checkresults, array('username'=>$lang['include_not_registered_words']));
				} elseif($newuid == -3) {
					$ucresult = uc_user_login($_POST['username'], $_POST['password'], $loginfield == 'uid');
					list($members['uid'], $members['username'], $members['password'], $members['email']) = saddslashes($ucresult);
					if($members['uid'] <= 0) {
						array_push($checkresults, array('username'=>$lang['user_name_already_exists']));
					} else {
						$newuid = $members['uid'] ;
					}
					$exist_user_auth = true;

				} elseif($newuid == -4) {
					array_push($checkresults, array('email'=>$lang['email_format_is_wrong']));
				} elseif($newuid == -5) {
					array_push($checkresults, array('email'=>$lang['email_not_registered']));
				} elseif($newuid == -6) {
					array_push($checkresults, array('email'=>$lang['email_has_been_registered']));
				} else {
					array_push($checkresults, array('message'=>$lang['register_error']));
				}
				if(!empty($checkresults)) {
					showmessage('user_info_failed', '', '', '', $checkresults);
				}
			}

			// 设置LOGIN
			$cookievalue = authcode(md5($_POST['password'])."\t$newuid", 'ENCODE');
			ssetcookie('auth', $cookievalue, $cookietime, 1, true);
			setcookie('_refer', '');
			$_G['uid'] = $newuid;
			$_G['username'] = $_POST['username'];
			$shop['uid'] = $_G['uid'];
			$shop['username'] = $_G['username'];
			$shop['password'] = md5($_POST['password']);
			$shop['dateline'] = $_G['timestamp'];
			$shop['email'] = $_POST['email'];
			if (!$exist_user_auth) {
				inserttable('members',$shop,1);
			}
			unset($shop['password']);
			unset($shop['email']);
		}

		$shop['uid'] = $_G['uid'];
		$shop['username'] = $_G['username'];
		$shop['dateline'] = $_G['timestamp'];
		$shop['subject'] = $_POST['subject'];
		$shop['address'] = $_POST['address'];
		$shop['region'] = $_POST['region'];

		foreach($cacheinfo['columns'] as $column) {
			if (($column['allowpost'] == 1) && ($column['allowshow'] == 1) && ($column['formtype'] != 'img')){
				if(preg_match('/(^ext_)|(^applicant)/',$column['fieldname'])){
					if(!empty($_POST[$column['fieldname']])){
						$itemmessage[$column['fieldname']] = $_POST[$column['fieldname']];
					}
				} elseif(in_array($column['fieldname'], array('subject','subjectimage','address','tel','catid'))){
					if(!empty($_POST[$column['fieldname']])){
						$shop[$column['fieldname']] = $_POST[$column['fieldname']];
					}
				}
			}
		}
		$shop['letter'] = getletter(trim($_POST['subject']));
		$shop['address'] = $_POST['address'];
		$shop['catid'] = $_POST['catid'];
		$shop['validity_end'] = mktime(0,0,0,date('m',$_G['timestamp']),date('d',$_G['timestamp']), (date('Y',$_G['timestamp']) + 10));
		// 自动为用户加商店
		$shopid = inserttable('shopitems',$shop,1);

		$itemmessage['itemid'] = $shopid;
		$itemmessage['postip'] = $_G['clientip'];
		inserttable('shopmessage',$itemmessage);
		$_BCACHE->deltype('sitelist', 'shop', $_G['uid']);

		if(!pkperm('isadmin')) {
			DB::query('UPDATE '.tname('members')." SET myshopid='$shopid' WHERE uid='$_G[uid]'");
		}

		if ($_G['setting']['auditnewshops']) {
			uc_pm_send(0 , $_SC['founder'] , $lang['apm_admin_title'] , $lang['apm_admin_msg']);
			uc_pm_send(0 , $_G['uid'] , $lang['apm_panel_title'] , $lang['apm_panel_msg']);
			showmessage('apm_panel_msg', 'index.php');
		} else {
			DB::query('UPDATE '.tname('shopitems')." SET grade='3', groupid='{$_G['setting']['defaultshopgroup']}' WHERE itemid='$shopid'");
			showmessage('attend_register_success', 'index.php');
		}

		exit;

	} else {
		showmessage('no_submit', geturl('attend.php'));
	}
} elseif($do && $do=="attend") {
	//第二步，填写基本信息

	$uid = $_G['uid'];
	$ucurl = avatar($uid);

	if(postget('refer')) {
		$refer = postget('refer');
	} else {
		if(!empty($_SERVER['HTTP_REFERER'])) {
			$refer = $_SERVER['HTTP_REFERER'];
		} else {
			$refer = B_URL_ALL;
		}
	}

	if(empty($uid)) {
		setcookie('_refer', 'attend.php?do=attend');
	}

	$showarr = getmodelcategory('region');

	$arr_cat = getmodelcategory('shop');

	include template('templates/site/default/attend_step2.html.php', 1);
} else {
	//第一步
	include template('templates/site/default/attend_step1.html.php', 1);
}

ob_out(); //正则处理url/模板

?>