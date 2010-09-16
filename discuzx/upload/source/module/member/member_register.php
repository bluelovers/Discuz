<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_register.php 16532 2010-09-08 06:51:34Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

loaducenter();

if($_G['setting']['bbclosed']) {
	if(($_G['gp_action'] != 'activation' && !$_G['gp_activationauth']) || !$_G['setting']['closedallowactivation'] ) {
		showmessage('register_disable', NULL, array(), array('login' => 1));
	}
}

if(!function_exists('sendmail')) {
	include libfile('function/mail');
}
$_G['setting']['reginput'] = unserialize($_G['setting']['reginput']);
if(!preg_match('/^[A-z]\w+?$/', $_G['setting']['reginput']['username'])) {
	$_G['setting']['reginput']['username'] =  'username';
}
if(!preg_match('/^[A-z]\w+?$/', $_G['setting']['reginput']['password'])) {
	$_G['setting']['reginput']['password'] =  'password';
}
if(!preg_match('/^[A-z]\w+?$/', $_G['setting']['reginput']['password2'])) {
	$_G['setting']['reginput']['password2'] =  'password2';
}
if(!preg_match('/^[A-z]\w+?$/', $_G['setting']['reginput']['email'])) {
	$_G['setting']['reginput']['email'] =  'email';
}

$_G['gp_username'] = $_G['gp_'.$_G['setting']['reginput']['username']];
$_G['gp_password'] = $_G['gp_'.$_G['setting']['reginput']['password']];
$_G['gp_password2'] = $_G['gp_'.$_G['setting']['reginput']['password2']];
$_G['gp_email'] = $_G['gp_'.$_G['setting']['reginput']['email']];

if($_G['uid']) {
	$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
	showmessage('login_succeed', 'forum.php', array('username' => $_G['member']['username'], 'uid' => $_G['uid']), array('extrajs' => $ucsynlogin));
} elseif (!$_G['setting']['regstatus'] || !$_G['setting']['ucactivation']) {
	if($_G['gp_action'] == 'activation' || $_G['gp_activationauth']) {
		if(!$_G['setting']['ucactivation'] && !$_G['setting']['closedallowactivation']) {
			showmessage('register_disable_activation');
		}
	} elseif(!$_G['setting']['regstatus']) {
		showmessage(!$_G['setting']['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $_G['setting']['regclosemessage']));
	}
}

$inviteconfig = array();
$query = DB::query("SELECT * FROM ".DB::table('common_setting')." WHERE skey IN ('bbrules', 'bbrulesforce', 'bbrulestxt', 'welcomemsg', 'welcomemsgtitle', 'welcomemsgtxt', 'inviteconfig')");
while($setting = DB::fetch($query)) {
	$$setting['skey'] = $setting['svalue'];
}

if($_G['setting']['regverify'] == 0) {
	if($_G['setting']['outlandverify']) {
		$location = $localareaexp = '';
		require_once libfile('function/misc');
		$location = trim(convertip($_G['clientip'], "./"));
		if($location && $location != '- Unknown' && $location != '- LAN') {
			$localareaexp = preg_quote(($_G['setting']['localarea'] = trim($_G['setting']['localarea'])), '/');
			$localareaexp = str_replace(array("\\*"), array('.*'), $localareaexp);
			$localareaexp = '.*'.$localareaexp.'.*';
			$localareaexp = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $localareaexp).')$/i';
			if(!@preg_match($localareaexp, $location)) {
				$_G['setting']['regverify'] = 3;
			}
		}
	}
}

if($_G['cache']['ipctrl']['ipverifywhite']) {
	foreach(explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {
		if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
			$_G['setting']['regverify'] = 0;
			break;
		}
	}
}
$groupinfo = array();
if($_G['setting']['regverify']) {
	$groupinfo['groupid'] = 8;
} else {
	$groupinfo['groupid'] = $_G['setting']['newusergroupid'];
}
$seccodecheck = $_G['setting']['seccodestatus'] & 1;
$secqaacheck = $_G['setting']['secqaa']['status'] & 1;

$fromuid = !empty($_G['cookie']['promotion']) && $_G['setting']['creditspolicy']['promotion_register'] ? intval($_G['cookie']['promotion']) : 0;

$username = isset($_G['gp_username']) ? $_G['gp_username'] : '';

$bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
$auth = $_G['gp_auth'];

require_once libfile('function/profile');

$invite = getinvite();

if(!submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)) {

	if($_G['gp_action'] == 'activation') {
		$auth = explode("\t", authcode($auth, 'DECODE'));
		if(FORMHASH != $auth[1]) {
			showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');
		}
		$username = $auth[0];
		$activationauth = authcode("$auth[0]\t".FORMHASH, 'ENCODE');
	}

	$_G['referer'] = isset($_G['referer']) ? dhtmlspecialchars($_G['referer']) : dreferer();

	$fromuser = !empty($fromuser) ? dhtmlspecialchars($fromuser) : '';
	if($fromuid) {
		$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid='$fromuid'");
		if(DB::num_rows($query)) {
			$fromuser = dhtmlspecialchars(DB::result($query, 0));
		} else {
			dsetcookie('promotion');
		}
	}

	$bbrulestxt = nl2br("\n$bbrulestxt\n\n");
	if($_G['gp_action'] == 'activation') {
		$auth = dhtmlspecialchars($auth);
	}

	if($seccodecheck) {
		$seccode = random(6, 1);
	}
	if($_G['setting']['secqaa']['status'][1]) {
		$seccode = random(1, 1) * 1000000 + substr($seccode, -6);
	}

	$username = dhtmlspecialchars($username);

	$htmls = $settings = array();
	foreach($_G['cache']['fields_register'] as $field) {
		$fieldid = $field['fieldid'];
		$html = profile_setting($fieldid);
		if($html) {
			$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
			$htmls[$fieldid] = $html;
		}
	}
	$navtitle = $_G['setting']['reglinkname'];
	include template('member/register');

} else {

	$invite = getinvite();
	if($_G['setting']['regstatus'] == 2 && empty($invite)) {
		showmessage('not_open_registration_invite');
	}

	if($bbrules && $bbrulehash != $_POST['agreebbrule']) {
		showmessage('register_rules_agree');
	}

	$activation = array();
	if(isset($_G['gp_activationauth'])) {
		$activationauth = explode("\t", authcode($_G['gp_activationauth'], 'DECODE'));
		if($activationauth[1] == FORMHASH && !($activation = daddslashes(uc_get_user($activationauth[0]), 1))) {
			showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');
		}
	}

	if(!$activation) {
		$usernamelen = strlen($username);
		if($usernamelen < 3) {
			showmessage('profile_username_tooshort');
		} elseif($usernamelen > 15) {
			showmessage('profile_username_toolong');
		}
		$username = addslashes(trim(dstripslashes($username)));
		if(uc_get_user($username) && !DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$username'")) {
			if($_G['inajax']) {
				showmessage('profile_username_duplicate');
			} else {
				showmessage('register_activation_message', 'member.php?mod=logging&action=login', array('username' => $username));
			}
		}

		if($_G['gp_password'] !== $_G['gp_password2']) {
			showmessage('profile_passwd_notmatch');
		}

		if(!$_G['gp_password'] || $_G['gp_password'] != addslashes($_G['gp_password'])) {
			showmessage('profile_passwd_illegal');
		}

		$email = trim($_G['gp_email']);
		$password = $_G['gp_password'];

	}

	$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';

	if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
		showmessage('profile_username_protect');
	}

	$profile = $verifyarr = array();
	foreach($_G['cache']['fields_register'] as $field) {
		$field_key = $field['fieldid'];
		$field_val = $_G['gp_'.$field_key];
		if(!profile_check($field_key, $field_val)) {
			showmessage('profile_required_info_invalid');
		}
		if($field['needverify']) {
			$verifyarr[$field_key] = $field_val;
		} else {
			$profile[$field_key] = $field_val;
		}
	}

	if($_G['setting']['regverify'] == 2 && !trim($_G['gp_regmessage'])) {
		showmessage('profile_required_info_invalid');
	}

	if($_G['cache']['ipctrl']['ipregctrl']) {
		foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
			if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
				$ctrlip = $ctrlip.'%';
				$_G['setting']['regctrl'] = 72;
				break;
			} else {
				$ctrlip = $_G['clientip'];
			}
		}
	} else {
		$ctrlip = $_G['clientip'];
	}
	if($_G['setting']['regctrl']) {
		$query = DB::query("SELECT ip FROM ".DB::table('common_regip')." WHERE ip LIKE '$ctrlip' AND count='-1' AND dateline>$_G[timestamp]-'".$_G['setting']['regctrl']."'*3600 LIMIT 1");
		if(DB::num_rows($query)) {
			showmessage('register_ctrl', NULL, array('regctrl' => $_G['setting']['regctrl']));
		}
	}

	$secques = $questionid > 0 ? random(8) : '';

	if(!$activation) {
		$uid = uc_user_register($username, $password, $email, $questionid, $answer, $_G['clientip']);

		if($uid <= 0) {
			if($uid == -1) {
				showmessage('profile_username_illegal');
			} elseif($uid == -2) {
				showmessage('profile_username_protect');
			} elseif($uid == -3) {
				showmessage('profile_username_duplicate');
			} elseif($uid == -4) {
				showmessage('profile_email_illegal');
			} elseif($uid == -5) {
				showmessage('profile_email_domain_illegal');
			} elseif($uid == -6) {
				showmessage('profile_email_duplicate');
			} else {
				showmessage('undefined_action', NULL);
			}
		}
	} else {
		list($uid, $username, $email) = $activation;
	}

	if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid'")) {
		if(!$activation) {
			uc_user_delete($uid);
		}
		showmessage('profile_uid_duplicate', '', array('uid' => $uid));
	}

	if($_G['setting']['regfloodctrl']) {
		if($regattempts = DB::result_first("SELECT count FROM ".DB::table('common_regip')." WHERE ip='$_G[clientip]' AND count>'0' AND dateline>'$_G[timestamp]'-86400")) {
			if($regattempts >= $_G['setting']['regfloodctrl']) {
				showmessage('register_flood_ctrl', NULL, array('regfloodctrl' => $_G['setting']['regfloodctrl']));
			} else {
				DB::query("UPDATE ".DB::table('common_regip')." SET count=count+1 WHERE ip='$_G[clientip]' AND count>'0'");
			}
		} else {
			DB::query("INSERT INTO ".DB::table('common_regip')." (ip, count, dateline)
				VALUES ('$_G[clientip]', '1', '$_G[timestamp]')");
		}
	}

	$password = md5(random(10));


	if($invite && $_G['setting']['inviteconfig']['invitegroupid']) {
		$groupinfo['groupid'] = $_G['setting']['inviteconfig']['invitegroupid'];
	}
	$init_arr = explode(',', $_G['setting']['initcredits']);
	$userdata = array(
		'uid' => $uid,
		'username' => $username,
		'password' => $password,
		'email' => $email,
		'adminid' => 0,
		'groupid' => $groupinfo['groupid'],
		'regdate' => TIMESTAMP,
		'credits' => $init_arr[0],
		'timeoffset' => 9999
		);
	DB::insert('common_member', $userdata);
	$status_data = array(
		'uid' => $uid,
		'regip' => $_G['clientip'],
		'lastip' => $_G['clientip'],
		'lastvisit' => TIMESTAMP,
		'lastactivity' => TIMESTAMP,
		'lastpost' => 0,
		'lastsendmail' => 0,
		);
	DB::insert('common_member_status', $status_data);
	$profile['uid'] = $uid;
	DB::insert('common_member_profile', $profile);
	DB::insert('common_member_field_forum', array('uid' => $uid));
	DB::insert('common_member_field_home', array('uid' => $uid));
	if($verifyarr) {
		$setverify = array(
				'uid' => $uid,
				'username' => $username,
				'verifytype' => '0',
				'field' => daddslashes(serialize($verifyarr)),
				'dateline' => TIMESTAMP,
			);
		DB::insert('common_member_verify_info', $setverify);
		DB::insert('common_member_verify', array('uid' => $uid));
	}

	$count_data = array(
		'uid' => $uid,
		'extcredits1' => $init_arr[1],
		'extcredits2' => $init_arr[2],
		'extcredits3' => $init_arr[3],
		'extcredits4' => $init_arr[4],
		'extcredits5' => $init_arr[5],
		'extcredits6' => $init_arr[6],
		'extcredits7' => $init_arr[7],
		'extcredits8' => $init_arr[8]
		);
	DB::insert('common_member_count', $count_data);
	DB::insert('common_setting', array('skey' => 'lastmember', 'svalue' => $username), false, true);
	manyoulog('user', $uid, 'add');

	$totalmembers = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member'));
	$userstats = array('totalmembers' => $totalmembers, 'newsetuser' => $username);

	save_syscache('userstats', $userstats);

	if($_G['setting']['regctrl'] || $_G['setting']['regfloodctrl']) {
		DB::query("DELETE FROM ".DB::table('common_regip')." WHERE dateline<='$_G[timestamp]'-".($_G['setting']['regctrl'] > 72 ? $_G['setting']['regctrl'] : 72)."*3600", 'UNBUFFERED');
		if($_G['setting']['regctrl']) {
			DB::query("INSERT INTO ".DB::table('common_regip')." (ip, count, dateline)
				VALUES ('$_G[clientip]', '-1', '$_G[timestamp]')");
		}
	}

	$regmessage = dhtmlspecialchars($_G['gp_regmessage']);
	if($_G['setting']['regverify'] == 2) {
		DB::query("REPLACE INTO ".DB::table('common_member_validate')." (uid, submitdate, moddate, admin, submittimes, status, message, remark)
			VALUES ('$uid', '$_G[timestamp]', '0', '', '1', '0', '$regmessage', '')");
	} elseif($_G['setting']['regverify'] == 3) {
		$regmessage = dhtmlspecialchars(lang('message', 'register_ip_outlandverify'));
		DB::query("REPLACE INTO ".DB::table('common_member_validate')." (uid, submitdate, moddate, admin, submittimes, status, message, remark)
			VALUES ('$uid', '$_G[timestamp]', '0', '', '1', '0', '$regmessage', '')");
	}

	$_G['uid'] = $uid;
	$_G['username'] = $username;
	$_G['member']['username'] = dstripslashes($_G['username']);
	$_G['member']['password'] = $password;
	$_G['groupid'] = $groupinfo['groupid'];
	include_once libfile('function/stat');
	updatestat('register');

	$_CORE = & discuz_core::instance();
	$_CORE->session->set('uid', $uid);
	$_CORE->session->set('username', $username);

	dsetcookie('auth', authcode("{$_G['member']['password']}\t$_G[uid]", 'ENCODE'), 2592000, 1, true);

	if($invite['id']) {
		DB::update("common_invite", array('fuid'=>$uid, 'fusername'=>$username, 'regdateline' => $_G['timestamp'], 'status' => 2), array('id'=>$invite['id']));
		updatestat('invite');
	}
	if($invite['uid']) {
		if($_G['setting']['inviteconfig']['inviteaddcredit']) {
			updatemembercount($uid,
				array($_G['setting']['inviteconfig']['inviterewardcredit'] => $_G['setting']['inviteconfig']['inviteaddcredit']));
		}
		if($_G['setting']['inviteconfig']['invitedaddcredit']) {
			updatemembercount($invite['uid'],
				array($_G['setting']['inviteconfig']['inviterewardcredit'] => $_G['setting']['inviteconfig']['invitedaddcredit']));
		}
		require_once libfile('function/friend');
		friend_make($invite['uid'], $invite['username'], false);
		notification_add($invite['uid'], 'friend', 'invite_friend', array('actor' => '<a href="home.php?mod=space&uid='.$invite['uid'].'" target="_blank">'.$invite['username'].'</a>'), 1);

		space_merge($invite, 'field_home');
		if(!empty($invite['privacy']['feed']['invite'])) {
			require_once libfile('function/feed');
			$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$_G['username'].'</a>');
			feed_add('friend', 'feed_invite', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $invite['uid'], $invite['username']);
		}
		if($invite['appid']) {
			updatestat('appinvite');
		}
	}

	if($welcomemsg && !empty($welcomemsgtxt)) {
		$welcomtitle = !empty($_G['setting']['welcomemsgtitle']) ? $_G['setting']['welcomemsgtitle'] : "Welcome to ".$_G['setting']['bbname']."!";
		$welcomtitle = addslashes(replacesitevar($welcomtitle));
		$welcomemsgtxt = addslashes(replacesitevar($welcomemsgtxt));
		if($welcomemsg == 1) {
			sendpm($uid, $welcomtitle, $welcomemsgtxt, 0);
		} elseif($welcomemsg == 2) {
			sendmail("$username <$email>", $welcomtitle, $welcomemsgtxt);
		}
	}

	if($fromuid) {
		updatecreditbyaction('promotion_register', $fromuid);
		dsetcookie('promotion', '');
	}

	dsetcookie('loginuser', '');
	dsetcookie('activationauth', '');
	dsetcookie('invite_auth', '');


	include_once libfile('function/cache');
	updatesettings();

	if(!empty($_G['inajax'])) {
		$_G['setting']['msgforward'] = unserialize($_G['setting']['msgforward']);
		$mrefreshtime = intval($_G['setting']['msgforward']['refreshtime']) * 1000;
		$message = 1;
		if($_G['setting']['regverify'] != 1) {
			include template('member/register');
		}
	}

	$param = array('bbname' => $_G['setting']['bbname'], 'username' => $_G['username'], 'uid' => $_G['uid']);

	switch($_G['setting']['regverify']) {
		case 1:
			$idstring = random(6);
			$authstr = $_G['setting']['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET authstr='$authstr' WHERE uid='$_G[uid]'");
			$verifyurl = "{$_G[siteurl]}member.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";
			$email_verify_message = lang('email', 'email_verify_message', array(
				'username' => $_G['member']['username'],
				'bbname' => $_G['setting']['bbname'],
				'siteurl' => $_G['siteurl'],
				'url' => $verifyurl
			));
			sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message);
			if(!empty($_G['inajax'])) {
				include template('member/register');
			} else {
				showmessage('profile_email_verify', '', $param);
			}
			break;
		case 2:
		case 3:
			showmessage('register_manual_verify', 'home.php?mod=space&do=home', $param);
			break;
		default:
			showmessage('register_succeed', dreferer(), $param);
			break;
	}

}


?>