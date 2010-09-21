<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_profile.php 16892 2010-09-16 07:50:10Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$operation = in_array($_GET['op'], array('base', 'contact', 'edu', 'work', 'info', 'bbs', 'password', 'verify')) ? trim($_GET['op']) : 'base';
$space = getspace($_G['uid']);
space_merge($space, 'field_home');
space_merge($space, 'profile');
$seccodecheck = $_G['setting']['seccodestatus'] & 8;
$secqaacheck = $_G['setting']['secqaa']['status'] & 4;
$_G['group']['seccode'] = 1;
@include_once DISCUZ_ROOT.'./data/cache/cache_domain.php';
$spacedomain = isset($rootdomain['home']) && $rootdomain['home'] ? $rootdomain['home'] : array();
if($operation != 'password') {

	include_once libfile('function/profile');

	loadcache('profilesetting');
	if(empty($_G['cache']['profilesetting'])) {
		require_once libfile('function/cache');
		updatecache('profilesetting');
		loadcache('profilesetting');
	}
}

$allowcstatus = !empty($_G['group']['allowcstatus']) ? true : false;
$verify = DB::fetch_first("SELECT * FROM ".DB::table("common_member_verify")." WHERE uid='$_G[uid]'");
$validate = array();
if($_G['setting']['regverify'] == 2 && $_G['groupid'] == 8) {
	$validate = DB::fetch_first("SELECT * FROM ".DB::table('common_member_validate')." WHERE uid='$_G[uid]' AND status='1'");
}

if(submitcheck('profilesubmit')) {

	require_once libfile('function/discuzcode');

	$forum = $setarr = $verifyarr = $errorarr = array();
	$forumfield = array('customstatus', 'sightml');

	if(!class_exists('discuz_censor')) {
		include libfile('class/censor');
	}
	$censor = discuz_censor::instance();

	if($_G['gp_vid']) {
		$vid = intval($_G['gp_vid']);
		$verifyconfig = $_G['setting']['verify'][$vid];
		if($verifyconfig['available']) {
			$verifyinfo = DB::fetch_first("SELECT * FROM ".DB::table("common_member_verify_info")." WHERE uid='$_G[uid]' AND verifytype='$vid'");
			if(!empty($verifyinfo)) {
				$verifyinfo['field'] = unserialize($verifyinfo['field']);
			}
			foreach($verifyconfig['field'] as $key => $field) {
				if(!isset($verifyinfo['field'][$key])) {
					$verifyinfo['field'][$key] = $key;
				}
			}
		} else {
			$vid = 0;
		}
	}
	foreach($_POST as $key => $value) {
		$field = $_G['cache']['profilesetting'][$key];
		if(in_array($key, $forumfield)) {
			$censor->check($value);
			if($censor->modbanned()) {
				profile_showerror($key, lang('spacecp', 'profile_censor'));
			}
			if($key == 'sightml') {
				loadcache(array('smilies', 'smileytypes'));
				$value = cutstr($value, $_G['group']['maxsigsize'], '');
				foreach($_G['cache']['smilies']['replacearray'] AS $skey => $smiley) {
					$_G['cache']['smilies']['replacearray'][$skey] = '[img]'.$_G['siteurl'].'static/image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$skey]]['directory'].'/'.$smiley.'[/img]';
				}
				$value = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], trim($value));
				$forum[$key] = addslashes(discuzcode(stripslashes($value), 1, 0, 0, 0, $_G['group']['allowsigbbcode'], $_G['group']['allowsigimgcode'], 0, 0, 1));
			} elseif($key=='customstatus' && $allowcstatus) {
				$forum[$key] = dhtmlspecialchars(trim($value));
			}
			continue;
		} elseif($field && !$field['available']) {
			continue;
		} elseif($key == 'timeoffset') {
			DB::update('common_member', array('timeoffset' => intval($value)), array('uid'=>$_G['uid']));
		}
		if($field['formtype'] == 'file') {
			if((!empty($_FILES[$key]) && $_FILES[$key]['error'] == 0) || (!empty($space[$key]) && empty($_G['gp_deletefile'][$key]))) {
				$value = '1';
			} else {
				$value = '';
			}
		}
		if(empty($field)) {
			continue;
		} elseif(profile_check($key, $value, $space)) {
			$censor->check($value);
			if($censor->modbanned()) {
				profile_showerror($key, lang('spacecp', 'profile_censor'));
			}
			$setarr[$key] = dhtmlspecialchars(trim($value));
		} else {
			if($key=='birthprovince') {
				$key = 'birthcity';
			} elseif($key=='resideprovince' || $key=='residecommunity'||$key=='residedist') {
				$key = 'residecity';
			} elseif($key=='birthyear' || $key=='birthmonth') {
				$key = 'birthday';
			}
			profile_showerror($key);
		}
		if($field['formtype'] == 'file') {
			unset($setarr[$key]);
		}
		if($vid && $verifyconfig['available'] && isset($verifyconfig['field'][$key])) {
			if(isset($verifyinfo['field'][$key]) && $setarr[$key] !== $space[$key]) {
				$verifyarr[$key] = $setarr[$key];
			}
			unset($setarr[$key]);
		}
		if(isset($setarr[$key]) && $_G['cache']['profilesetting'][$key]['needverify']) {
			if($setarr[$key] !== $space[$key]) {
				$verifyarr[$key] = $setarr[$key];
			}
			unset($setarr[$key]);
		}
	}
	if($_G['gp_deletefile'] && is_array($_G['gp_deletefile'])) {
		foreach($_G['gp_deletefile'] as $key => $value) {
			@unlink(getglobal('setting/attachdir').'./profile/'.$space[$key]);
			$setarr[$key] = '';
		}
	}
	if($_FILES) {
		require_once libfile('class/upload');
		$upload = new discuz_upload();

		foreach($_FILES as $key => $file) {
			$upload->init($file, 'profile');
			$attach = $upload->attach;

			if(!$upload->error()) {
				$upload->save();

				if(!$upload->get_image_info($attach['target'])) {
					@unlink($attach['target']);
					continue;
				}

				$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
				if($vid && $verifyconfig['available'] && isset($verifyconfig['field'][$key])) {
					if(isset($verifyinfo['field'][$key])) {
						@unlink(getglobal('setting/attachdir').'./profile/'.$verifyinfo['field'][$key]);
						$verifyarr[$key] = $attach['attachment'];
					}
					continue;
				}
				if(isset($setarr[$key]) && $_G['cache']['profilesetting'][$key]['needverify']) {
					@unlink(getglobal('setting/attachdir').'./profile/'.$verifyinfo['field'][$key]);
					$verifyarr[$key] = $attach['attachment'];
					continue;
				}
				@unlink(getglobal('setting/attachdir').'./profile/'.$space[$key]);
				$setarr[$key] = $attach['attachment'];
			}
		}
	}
	if($vid && !empty($verifyinfo['field']) && is_array($verifyinfo['field'])) {
		foreach($verifyinfo['field'] as $key => $fvalue) {
			if(empty($verifyarr[$key]) && !isset($verifyarr[$key]) && isset($verifyinfo['field'][$key])) {
				$verifyarr[$key] = !empty($fvalue) && $key != $fvalue ? $fvalue : $space[$key];
			}
		}
	}
	if($forum) {
		if(!$_G['group']['maxsigsize']) {
			$forum['sightml'] = '';
		}
		DB::update('common_member_field_forum', $forum, array('uid'=>$_G['uid']));
	}

	if(isset($_POST['birthmonth']) && ($space['birthmonth'] != $_POST['birthmonth'] || $space['birthday'] != $_POST['birthday'])) {
		$setarr['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
	}
	if(isset($_POST['birthyear']) && $space['birthyear'] != $_POST['birthyear']) {
		$setarr['zodiac'] = get_zodiac($_POST['birthyear']);
	}

	if($setarr) {
		DB::update('common_member_profile', $setarr, array('uid'=>$_G['uid']));
	}

	if($verifyarr) {
		DB::query('DELETE FROM '.DB::table('common_member_verify_info')." WHERE uid='$_G[uid]' AND verifytype='$vid'");
		$setverify = array(
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'verifytype' => $vid,
				'field' => daddslashes(serialize($verifyarr)),
				'dateline' => $_G['timestamp']
			);

		DB::insert('common_member_verify_info', $setverify);
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_verify')." WHERE uid='$_G[uid]'"), 0);
		if(!$count) {
			DB::insert('common_member_verify', array('uid' => $_G['uid']));
		}
	}

	if(isset($_POST['privacy'])) {
		foreach($_POST['privacy'] as $key=>$value) {
			if(isset($_G['cache']['profilesetting'][$key])) {
				$space['privacy']['profile'][$key] = intval($value);
			}
		}
		DB::update('common_member_field_home', array('privacy'=>addslashes(serialize($space['privacy']))), array('uid'=>$space['uid']));
	}

	if($_G['setting']['my_app_status']) manyoulog('user', $_G['uid'], 'update');

	include_once libfile('function/feed');
	feed_add('profile', 'feed_profile_update_'.$operation, array('hash_data'=>'profile'));

	profile_showsuccess();

} elseif(submitcheck('passwordsubmit', 0, $seccodecheck, $secqaacheck)) {

	$membersql = $memberfieldsql = $authstradd1 = $authstradd2 = $newpasswdadd = '';
	$setarr = array();
	$emailnew = dhtmlspecialchars($_G['gp_emailnew']);

	if($_G['gp_questionidnew'] === '') {
		$_G['gp_questionidnew'] = $_G['gp_answernew'] = '';
	} else {
		$secquesnew = $_G['gp_questionidnew'] > 0 ? random(8) : '';
	}
	if(!empty($_G['gp_newpassword']) && $_G['gp_newpassword'] != $_G['gp_newpassword2']) {
		showmessage('profile_passwd_notmatch', '', array(), array('return' => true));
	}

	loaducenter();
	$ucresult = uc_user_edit($_G['username'], $_G['gp_oldpassword'], $_G['gp_newpassword'], $emailnew != $_G['member']['email'] ? $emailnew : '', 0, $_G['gp_questionidnew'], $_G['gp_answernew']);
	if($ucresult == -1) {
		showmessage('profile_passwd_wrong', '', array(), array('return' => true));
	} elseif($ucresult == -4) {
		showmessage('profile_email_illegal', '', array(), array('return' => true));
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal', '', array(), array('return' => true));
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate', '', array(), array('return' => true));
	}

	if($emailnew != $_G['member']['email']) {
		$setarr['email'] = $emailnew;
		$setarr['emailstatus'] = 0;
	}
	if(!empty($_G['gp_newpassword']) || $secquesnew) {
		$setarr['password'] = md5(random(10));
	}

	$authstr = false;
	if($_G['adminid'] == 0 && $emailnew != $_G['member']['email']) {
		if($_G['setting']['regverify'] == 1 && (($_G['group']['grouptype'] == 'member' && $_G['adminid'] == 0) || $_G['groupid'] == 8)) {
			$idstring = random(6);
			$setarr['groupid'] = $groupid = 8;
			loadcache('usergroup_8');
			$authstr = true;
			DB::update('common_member_field_forum', array('authstr' => TIMESTAMP."\t2\t".$idstring), array('uid' => $_G['uid']));
			$verifyurl = "{$_G[siteurl]}member.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";
			$email_verify_message = lang('email', 'email_verify_message', array(
				'username' => $_G['member']['username'],
				'bbname' => $_G['setting']['bbname'],
				'siteurl' => $_G['siteurl'],
				'url' => $verifyurl
			));
			include_once libfile('function/mail');
			sendmail("{$_G[member][username]} <$emailnew>", lang('email', 'email_verify_subject'), $email_verify_message);
		} else {
			emailcheck_send($space['uid'], $emailnew);
		}
	}
	if($setarr) {
		DB::update('common_member', $setarr, array('uid' => $_G['uid']));
	}

	if($authstr) {
		showmessage('profile_email_verify', 'home.php?mod=spacecp&ac=profile&op=password');
	} else {
		showmessage('profile_succeed', 'home.php?mod=spacecp&ac=profile&op=password');
	}
}

if($operation == 'password') {

	$resend = getcookie('resendemail');
	$resend = empty($resend) ? true : (TIMESTAMP - $resend) > 300;
	if($_G['gp_resend'] && $resend) {
		$toemail = $space['newemail'] ? $space['newemail'] : $space['email'];
		emailcheck_send($space['uid'], $toemail);
		dsetcookie('resendemail', TIMESTAMP);
		showmessage('send_activate_mail_succeed', "home.php?mod=spacecp&ac=profile&op=password");
	} elseif ($_G['gp_resend']) {
		showmessage('send_activate_mail_error', "home.php?mod=spacecp&ac=profile&op=password");
	}

	$actives = array('password' =>' class="a"');
	$navtitle = lang('core', 'title_password_security');

} else {

	space_merge($space, 'field_home');
	space_merge($space, 'field_forum');

	require_once libfile('function/editor');
	$space['sightml'] = html2bbcode($space['sightml']);

	$vid = $_G['gp_vid'] ? intval($_G['gp_vid']) : 0;

	$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();
	$_G['setting']['privacy'] = $_G['setting']['privacy'] ? $_G['setting']['privacy'] : array();
	$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : unserialize($_G['setting']['privacy']);
	$_G['setting']['privacy']['profile'] = !empty($_G['setting']['privacy']['profile']) ? $_G['setting']['privacy']['profile'] : array();
	$privacy = array_merge($_G['setting']['privacy']['profile'], $privacy);

	$actives = array('profile' =>' class="a"');
	$opactives = array($operation =>' class="a"');
	$allowitems = array();
	if($operation == 'base') {
		$allowitems = array('realname', 'gender', 'birthday', 'birthcity', 'residecity', 'residedist', 'affectivestatus', 'lookingfor', 'bloodtype', 'field1', 'field2', 'field3', 'field4', 'field5', 'field6', 'field7', 'field8');
	} elseif($operation == 'contact') {
		$allowitems = array('telephone', 'mobile', 'alipay', 'icq', 'qq', 'yahoo', 'msn', 'taobao');
	} elseif($operation == 'edu') {
		$allowitems = array('graduateschool', 'education');
	} elseif($operation == 'work') {
		$allowitems = array('occupation', 'company', 'position', 'revenue');
	} elseif($operation == 'info') {
		$allowitems = array('idcardtype', 'idcard', 'address', 'zipcode', 'nationality', 'residecommunity', 'residesuite', 'height', 'weight', 'site', 'bio', 'interest');
	} elseif($operation == 'verify') {
		if($vid == 0) {
			foreach($_G['setting']['verify'] as $key => $setting) {
				if($setting['available']) {
					$_G['gp_vid'] = $vid = $key;
					break;
				}
			}
		}
		$actives = array('verify' =>' class="a"');
		$opactives = array($operation.$vid =>' class="a"');
		$allowitems = $_G['setting']['verify'][$vid]['field'];
	}
	$showbtn = ($vid && $verify['verify'.$vid] != 1) || empty($vid);
	if(!empty($verify) && is_array($verify)) {
		foreach($verify as $key => $flag) {
			if(in_array($key, array('verify1', 'verify2', 'verify3', 'verify4', 'verify5')) && $flag == 1) {
				$verifyid = intval(substr($key, -1, 1));
				foreach($_G['setting']['verify'][$verifyid]['field'] as $field) {
					$_G['cache']['profilesetting'][$field]['unchangeable'] = 1;
				}
			}
		}
	}

	if($vid) {
		$query = DB::query('SELECT field FROM '.DB::table('common_member_verify_info')." WHERE uid='$_G[uid]' AND verifytype='$vid'");
		while($value = DB::fetch($query)) {
			$field = unserialize($value['field']);
			foreach($field as $key => $fvalue) {
				$space[$key] = $fvalue;
			}
		}
	}
	$htmls = $settings = array();
	foreach($allowitems as $fieldid) {
		$html = profile_setting($fieldid, $space, $vid ? false : true);
		if($html) {
			$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
			$htmls[$fieldid] = $html;
		}
	}

}

include template("home/spacecp_profile");

function get_constellation($birthmonth,$birthday) {
	$birthmonth = intval($birthmonth);
	$birthday = intval($birthday);
	$idx = $birthmonth;
//	if ($birthday <= 22) {
//		if (1 == $birthmonth) {
//			$idx = 12;
//		} else {
//			$idx = $birthmonth - 1;
//		}
//	}
//	return $idx > 0 && $idx <= 12 ? lang('space', 'constellation_'.$idx) : '';

	if ($birthmonth < 1 || $birthmonth > 12 || $birthday < 1 || $birthday > 31) return '';

	$carray = array(22, 20, 19, 21, 20, 21, 22, 23, 23, 23, 24, 22, 22);

	$_idx = ($birthday >= $carray[$idx]) ? $idx : $idx - 1;
	if ($_idx < 1) $_idx = 12;
	$idx = $_idx;
	$_idx = $_idx - 1;

	return lang('space', 'constellation_' . $idx);
}

function get_zodiac($birthyear) {
	$birthyear = intval($birthyear);
	$idx = (($birthyear - 1900) % 12) + 1;
	return $idx > 0 && $idx <= 12 ? lang('space', 'zodiac_'. $idx) : '';
}

function profile_showerror($key, $extrainfo) {
	echo '<script>';
	echo 'parent.show_error("'.$key.'", "'.$extrainfo.'");';
	echo '</script>';
	exit();
}

function profile_showsuccess() {
	echo '<script type="text/javascript">';
	echo 'parent.show_success();';
	echo '</script>';
	exit();
}

?>