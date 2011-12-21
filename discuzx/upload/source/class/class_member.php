<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_member.php 25292 2011-11-03 10:14:21Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class logging_ctl {

	function logging_ctl() {
		require_once libfile('function/misc');
		loaducenter();
	}

	function logging_more($questionexist) {
		global $_G;
		if(empty($_G['gp_lssubmit'])) {
			return;
		}
		$auth = authcode($_G['gp_username']."\t".$_G['gp_password']."\t".($questionexist ? 1 : 0), 'ENCODE');
		$js = '<script type="text/javascript">showWindow(\'login\', \'member.php?mod=logging&action=login&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()).(!empty($_G['gp_cookietime']) ? '&cookietime=1' : '').'\')</script>';
		showmessage('location_login', '', array('type' => 1), array('extrajs' => $js));
	}

	function on_login() {
		global $_G;
		if($_G['uid']) {
			$referer = dreferer();
			$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);
			showmessage('login_succeed', $referer ? $referer : './', $param, array('showdialog' => 1, 'locationtime' => true, 'extrajs' => $ucsynlogin));
		}

		$from_connect = $this->setting['connect']['allow'] && !empty($_G['gp_from']) ? 1 : 0;
		$seccodecheck = $from_connect ? false : $this->setting['seccodestatus'] & 2;
		$seccodestatus = !empty($_G['gp_lssubmit']) ? false : $seccodecheck;
		$invite = getinvite();

		if(!submitcheck('loginsubmit', 1, $seccodestatus)) {

			$auth = '';
			$username = !empty($_G['cookie']['loginuser']) ? htmlspecialchars($_G['cookie']['loginuser']) : '';

			if(!empty($_G['gp_auth'])) {
				list($username, $password, $questionexist) = explode("\t", authcode($_G['gp_auth'], 'DECODE'));
				$username = htmlspecialchars($username);
				if($username && $password) {
					$auth = htmlspecialchars($_G['gp_auth']);
				} else {
					$auth = '';
				}
			}

			$cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_G['gp_cookietime']) ? 'checked="checked"' : '';

			if($seccodecheck) {
				$seccode = random(6, 1) + $seccode{0} * 1000000;
			}

			if($this->extrafile && file_exists(libfile('member/'.$this->extrafile, 'module'))) {
				require_once libfile('member/'.$this->extrafile, 'module');
			}

			$navtitle = lang('core', 'title_login');
			include template($this->template);

		} else {

			if(!empty($_G['gp_auth'])) {
				list($_G['gp_username'], $_G['gp_password']) = daddslashes(explode("\t", authcode($_G['gp_auth'], 'DECODE')));
			}

			if(!($_G['member_loginperm'] = logincheck($_G['gp_username']))) {
				showmessage('login_strike');
			}
			if($_G['gp_fastloginfield']) {
				$_G['gp_loginfield'] = $_G['gp_fastloginfield'];
			}
			$_G['uid'] = $_G['member']['uid'] = 0;
			$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
			if(!$_G['gp_password'] || $_G['gp_password'] != addslashes($_G['gp_password'])) {
				showmessage('profile_passwd_illegal');
			}
			$result = userlogin($_G['gp_username'], $_G['gp_password'], $_G['gp_questionid'], $_G['gp_answer'], $this->setting['autoidselect'] ? 'auto' : $_G['gp_loginfield']);
			$uid = $result['ucresult']['uid'];

			if(!empty($_G['gp_lssubmit']) && ($result['ucresult']['uid'] == -3 || $seccodecheck && $result['status'] > 0)) {
				$_G['gp_username'] = $result['ucresult']['username'];
				$_G['gp_password'] = stripslashes($_G['gp_password']);
				$this->logging_more($result['ucresult']['uid'] == -3);
			}

			if($result['status'] == -1) {
				if(!$this->setting['fastactivation']) {
					$auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');
					showmessage('location_activation', 'member.php?mod='.$this->setting['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()), array(), array('location' => true));
				} else {
					$result = daddslashes($result);
					$init_arr = explode(',', $this->setting['initcredits']);
					DB::insert('common_member', array(
						'uid' => $uid,
						'username' => $result['ucresult']['username'],
						'password' => md5(random(10)),
						'email' => $result['ucresult']['email'],
						'adminid' => 0,
						'groupid' => $this->setting['regverify'] ? 8 : $this->setting['newusergroupid'],
						'regdate' => TIMESTAMP,
						'credits' => $init_arr[0],
						'timeoffset' => 9999
					));
					DB::insert('common_member_status', array(
						'uid' => $uid,
						'regip' => $_G['clientip'],
						'lastip' => $_G['clientip'],
						'lastvisit' => TIMESTAMP,
						'lastactivity' => TIMESTAMP,
						'lastpost' => 0,
						'lastsendmail' => 0
					));
					DB::insert('common_member_profile', array('uid' => $uid));
					DB::insert('common_member_field_forum', array('uid' => $uid));
					DB::insert('common_member_field_home', array('uid' => $uid));
					DB::insert('common_member_count', array(
						'uid' => $uid,
						'extcredits1' => $init_arr[1],
						'extcredits2' => $init_arr[2],
						'extcredits3' => $init_arr[3],
						'extcredits4' => $init_arr[4],
						'extcredits5' => $init_arr[5],
						'extcredits6' => $init_arr[6],
						'extcredits7' => $init_arr[7],
						'extcredits8' => $init_arr[8]
					));
					manyoulog('user', $uid, 'add');
					$result['member'] = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$uid'");
					$result['status'] = 1;
				}
			}

			if($result['status'] > 0) {

				if($this->extrafile && file_exists(libfile('member/'.$this->extrafile, 'module'))) {
					require_once libfile('member/'.$this->extrafile, 'module');
				}

				setloginstatus($result['member'], $_G['gp_cookietime'] ? 2592000 : 0);

				DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."', lastactivity='".TIMESTAMP."' WHERE uid='$_G[uid]'");
				$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

				if($invite['id']) {
					$result = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_invite')." WHERE uid='$invite[uid]' AND fuid='$uid'");
					if(!$result) {
						DB::update("common_invite", array('fuid'=>$uid, 'fusername'=>$_G['username']), array('id'=>$invite['id']));
						updatestat('invite');
					} else {
						$invite = array();
					}
				}
				if($invite['uid']) {
					require_once libfile('function/friend');
					friend_make($invite['uid'], $invite['username'], false);
					dsetcookie('invite_auth', '');
					if($invite['appid']) {
						updatestat('appinvite');
					}
				}

				$param = array(
					'username' => $result['ucresult']['username'],
					'usergroup' => $_G['group']['grouptitle'],
					'uid' => $_G['member']['uid'],
					'groupid' => $_G['groupid'],
					'syn' => $ucsynlogin ? 1 : 0
				);

				$extra = array(
					'showdialog' => true,
					'locationtime' => true,
					'extrajs' => $ucsynlogin
				);
				$loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';

				$location = $invite || $_G['groupid'] == 8 ? 'home.php?mod=space&do=home' : dreferer();
				if(empty($_G['gp_handlekey']) || !empty($_G['gp_lssubmit'])) {
					if(defined('IN_MOBILE')) {
						showmessage('location_login_succeed_mobile', $location, array('username' => $result['ucresult']['username']), array('location' => true));
					} else {
						if(!empty($_G['gp_lssubmit'])) {
							if(!$ucsynlogin) {
								$extra['location'] = true;
							}
							showmessage($loginmessage, $location, $param, $extra);
						} else {
							$href = str_replace("'", "\'", $location);
							showmessage('location_login_succeed', $location, array(),
								array(
									'showid' => 'succeedmessage',
									'extrajs' => '<script type="text/javascript">'.
										'setTimeout("window.location.href =\''.$href.'\';", 3000);'.
										'$(\'succeedmessage_href\').href = \''.$href.'\';'.
										'$(\'main_message\').style.display = \'none\';'.
										'$(\'main_succeed\').style.display = \'\';'.
										'$(\'succeedlocation\').innerHTML = \''.lang('message', $loginmessage, $param).'\';</script>'.$ucsynlogin,
									'striptags' => false,
								)
							);
						}
					}
				} else {
					showmessage($loginmessage, $location, $param, $extra);
				}
			} else {
				$password = preg_replace("/^(.{".round(strlen($_G['gp_password']) / 4)."})(.+?)(.{".round(strlen($_G['gp_password']) / 6)."})$/s", "\\1***\\3", $_G['gp_password']);
				$errorlog = dhtmlspecialchars(
					TIMESTAMP."\t".
					($result['ucresult']['username'] ? $result['ucresult']['username'] : dstripslashes($_G['gp_username']))."\t".
					$password."\t".
					"Ques #".intval($_G['gp_questionid'])."\t".
					$_G['clientip']);
				writelog('illegallog', $errorlog);
				loginfailed($_G['gp_username']);
				$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_G['gp_questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
				showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm']));
			}

		}

	}

	function on_logout() {
		global $_G;

		$ucsynlogout = $this->setting['allowsynlogin'] ? uc_user_synlogout() : '';

		if($_G['gp_formhash'] != $_G['formhash']) {
			showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
		}

		clearcookies();
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
		$_G['setting']['styleid'] = $this->setting['styleid'];

		showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
	}

}

class register_ctl {

	var $showregisterform = 1;

	function register_ctl() {
		global $_G;
		if($_G['setting']['bbclosed']) {
			if(($_G['gp_action'] != 'activation' && !$_G['gp_activationauth']) || !$_G['setting']['closedallowactivation'] ) {
				showmessage('register_disable', NULL, array(), array('login' => 1));
			}
		}

		loadcache(array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional', 'fields_register', 'ipctrl'));
		require_once libfile('function/misc');
		require_once libfile('function/profile');
		if(!function_exists('sendmail')) {
			include libfile('function/mail');
		}
		loaducenter();
	}

	function on_register() {
		global $_G;

		$_G['gp_username'] = $_G['gp_'.$this->setting['reginput']['username']];
		$_G['gp_password'] = $_G['gp_'.$this->setting['reginput']['password']];
		$_G['gp_password2'] = $_G['gp_'.$this->setting['reginput']['password2']];
		$_G['gp_email'] = $_G['gp_'.$this->setting['reginput']['email']];

		if($_G['uid']) {
			$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
			$url_forward = dreferer();
			if(strpos($url_forward, $this->setting['regname']) !== false) {
				$url_forward = 'forum.php';
			}
			showmessage('login_succeed', $url_forward ? $url_forward : './', array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array('extrajs' => $ucsynlogin));
		} elseif(!$this->setting['regclosed'] && (!$this->setting['regstatus'] || !$this->setting['ucactivation'])) {
			if($_G['gp_action'] == 'activation' || $_G['gp_activationauth']) {
				if(!$this->setting['ucactivation'] && !$this->setting['closedallowactivation']) {
					showmessage('register_disable_activation');
				}
			} elseif(!$this->setting['regstatus']) {
				showmessage(!$this->setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $this->setting['regclosemessage']));
			}
		}

		$bbrules = & $this->setting['bbrules'];
		$bbrulesforce = & $this->setting['bbrulesforce'];
		$bbrulestxt = & $this->setting['bbrulestxt'];
		$welcomemsg = & $this->setting['welcomemsg'];
		$welcomemsgtitle = & $this->setting['welcomemsgtitle'];
		$welcomemsgtxt = & $this->setting['welcomemsgtxt'];
		$regname = $this->setting['regname'];

		if($this->setting['regverify']) {
			if($this->setting['areaverifywhite']) {
				$location = $whitearea = '';
				$location = trim(convertip($_G['clientip'], "./"));
				if($location) {
					$whitearea = preg_quote(trim($this->setting['areaverifywhite']), '/');
					$whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
					$whitearea = '.*'.$whitearea.'.*';
					$whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';
					if(@preg_match($whitearea, $location)) {
						$this->setting['regverify'] = 0;
					}
				}
			}

			if($_G['cache']['ipctrl']['ipverifywhite']) {
				foreach(explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {
					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
						$this->setting['regverify'] = 0;
						break;
					}
				}
			}
		}

		$invitestatus = false;
		if($this->setting['regstatus'] == 2) {
			if($this->setting['inviteconfig']['inviteareawhite']) {
				$location = $whitearea = '';
				$location = trim(convertip($_G['clientip'], "./"));
				if($location) {
					$whitearea = preg_quote(trim($this->setting['inviteconfig']['inviteareawhite']), '/');
					$whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
					$whitearea = '.*'.$whitearea.'.*';
					$whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';
					if(@preg_match($whitearea, $location)) {
						$invitestatus = true;
					}
				}
			}

			if($this->setting['inviteconfig']['inviteipwhite']) {
				foreach(explode("\n", $this->setting['inviteconfig']['inviteipwhite']) as $ctrlip) {
					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
						$invitestatus = true;
						break;
					}
				}
			}
		}

		$groupinfo = array();
		if($this->setting['regverify']) {
			$groupinfo['groupid'] = 8;
		} else {
			$groupinfo['groupid'] = $this->setting['newusergroupid'];
		}

		$seccodecheck = $this->setting['seccodestatus'] & 1;
		$secqaacheck = $this->setting['secqaa']['status'] & 1;
		$fromuid = !empty($_G['cookie']['promotion']) && $this->setting['creditspolicy']['promotion_register'] ? intval($_G['cookie']['promotion']) : 0;
		$username = isset($_G['gp_username']) ? $_G['gp_username'] : '';
		$bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
		$auth = $_G['gp_auth'];

		if(!$invitestatus) {
			$invite = getinvite();
		}

		if(!submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)) {

			if($_G['gp_action'] == 'activation') {
				$auth = explode("\t", authcode($auth, 'DECODE'));
				if(FORMHASH != $auth[1]) {
					showmessage('register_activation_invalid', 'member.php?mod=logging&action=login');
				}
				$username = $auth[0];
				$activationauth = authcode("$auth[0]\t".FORMHASH, 'ENCODE');
			}

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

			$username = dhtmlspecialchars($username);

			$htmls = $settings = array();
			foreach($_G['cache']['fields_register'] as $field) {
				$fieldid = $field['fieldid'];
				$html = profile_setting($fieldid, array(), false, false, true);
				if($html) {
					$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
					$htmls[$fieldid] = $html;
				}
			}

			$navtitle = $this->setting['reglinkname'];

			if($this->extrafile && file_exists(libfile('member/'.$this->extrafile, 'module'))) {
				require_once libfile('member/'.$this->extrafile, 'module');
			}

			$dreferer = dreferer();

			include template($this->template);

		} else {

			if($this->setting['regstatus'] == 2 && empty($invite) && !$invitestatus) {
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
				$usernamelen = dstrlen($username);
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
						showmessage('register_activation_message', 'member.php?mod=logging&action=login', array('username' => stripslashes($username)));
					}
				}
				$email = trim($_G['gp_email']);
				if(empty($this->setting['ignorepassword'])) {
					if($_G['gp_password'] !== $_G['gp_password2']) {
						showmessage('profile_passwd_notmatch');
					}

					if(!$_G['gp_password'] || $_G['gp_password'] != addslashes($_G['gp_password'])) {
						showmessage('profile_passwd_illegal');
					}
					$password = $_G['gp_password'];
				} else {
					$password = md5(random(10));
				}
			}

			$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($this->setting['censoruser'] = trim($this->setting['censoruser'])), '/')).')$/i';

			if($this->setting['censoruser'] && @preg_match($censorexp, $username)) {
				showmessage('profile_username_protect');
			}

			if($this->setting['regverify'] == 2 && !trim($_G['gp_regmessage'])) {
				showmessage('profile_required_info_invalid');
			}

			if($_G['cache']['ipctrl']['ipregctrl']) {
				foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
						$ctrlip = $ctrlip.'%';
						$this->setting['regctrl'] = $this->setting['ipregctrltime'];
						break;
					} else {
						$ctrlip = $_G['clientip'];
					}
				}
			} else {
				$ctrlip = $_G['clientip'];
			}

			if($this->setting['regctrl']) {
				$query = DB::query("SELECT ip FROM ".DB::table('common_regip')." WHERE ip LIKE '$ctrlip' AND count='-1' AND dateline>$_G[timestamp]-'".$this->setting['regctrl']."'*3600 LIMIT 1");
				if(DB::num_rows($query)) {
					showmessage('register_ctrl', NULL, array('regctrl' => $this->setting['regctrl']));
				}
			}

			$regipsql = '';
			if($this->setting['regfloodctrl']) {
				if($regattempts = DB::result_first("SELECT count FROM ".DB::table('common_regip')." WHERE ip='$_G[clientip]' AND count>'0' AND dateline>'$_G[timestamp]'-86400")) {
					if($regattempts >= $this->setting['regfloodctrl']) {
						showmessage('register_flood_ctrl', NULL, array('regfloodctrl' => $this->setting['regfloodctrl']));
					} else {
						$regipsql = "UPDATE ".DB::table('common_regip')." SET count=count+1 WHERE ip='$_G[clientip]' AND count>'0'";
					}
				} else {
					$regipsql = "INSERT INTO ".DB::table('common_regip')." (ip, count, dateline)
						VALUES ('$_G[clientip]', '1', '$_G[timestamp]')";
				}
			}

			$profile = $verifyarr = array();
			foreach($_G['cache']['fields_register'] as $field) {
				if(defined('IN_MOBILE')) {
					break;
				}
				$field_key = $field['fieldid'];
				$field_val = $_G['gp_'.$field_key];
				if($field['formtype'] == 'file' && !empty($_FILES[$field_key]) && $_FILES[$field_key]['error'] == 0) {
					$field_val = true;
				}

				if(!profile_check($field_key, $field_val)) {
					$showid = !in_array($field['fieldid'], array('birthyear', 'birthmonth')) ? $field['fieldid'] : 'birthday';
					showmessage($field['title'].lang('message', 'profile_illegal'), '', array(), array(
						'showid' => 'chk_'.$showid,
						'extrajs' => $field['title'].lang('message', 'profile_illegal').($field['formtype'] == 'text' ? '<script type="text/javascript">'.
							'$(\'registerform\').'.$field['fieldid'].'.className = \'px er\';'.
							'$(\'registerform\').'.$field['fieldid'].'.onblur = function () { if(this.value != \'\') {this.className = \'px\';$(\'chk_'.$showid.'\').innerHTML = \'\';}}'.
							'</script>' : '')
					));
				}
				if($field['needverify']) {
					$verifyarr[$field_key] = $field_val;
				} else {
					$profile[$field_key] = $field_val;
				}
			}

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
						showmessage('undefined_action');
					}
				}
			} else {
				list($uid, $username, $email) = $activation;
			}
			$_G['username'] = $username;
			if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid'")) {
				if(!$activation) {
					uc_user_delete($uid);
				}
				showmessage('profile_uid_duplicate', '', array('uid' => $uid));
			}

			$password = md5(random(10));
			$secques = $questionid > 0 ? random(8) : '';

			if(isset($_POST['birthmonth']) && isset($_POST['birthday'])) {
				$profile['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
			}
			if(isset($_POST['birthyear'])) {
				$profile['zodiac'] = get_zodiac($_POST['birthyear']);
			}

			if($_FILES) {
				require_once libfile('class/upload');
				$upload = new discuz_upload();

				foreach($_FILES as $key => $file) {
					$field_key = 'field_'.$key;
					if(!empty($_G['cache']['fields_register'][$field_key]) && $_G['cache']['fields_register'][$field_key]['formtype'] == 'file') {

						$upload->init($file, 'profile');
						$attach = $upload->attach;

						if(!$upload->error()) {
							$upload->save();

							if(!$upload->get_image_info($attach['target'])) {
								@unlink($attach['target']);
								continue;
							}

							$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
							if($_G['cache']['fields_register'][$field_key]['needverify']) {
								$verifyarr[$key] = $attach['attachment'];
							} else {
								$profile[$key] = $attach['attachment'];
							}
						}
					}
				}
			}

			if($regipsql) {
				DB::query($regipsql);
			}

			if($invite && $this->setting['inviteconfig']['invitegroupid']) {
				$groupinfo['groupid'] = $this->setting['inviteconfig']['invitegroupid'];
			}

			$init_arr = explode(',', $this->setting['initcredits']);
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
			$status_data = array(
				'uid' => $uid,
				'regip' => $_G['clientip'],
				'lastip' => $_G['clientip'],
				'lastvisit' => TIMESTAMP,
				'lastactivity' => TIMESTAMP,
				'lastpost' => 0,
				'lastsendmail' => 0,
			);
			$profile['uid'] = $uid;
			$field_forum['uid'] = $uid;
			$field_home['uid'] = $uid;

			if($this->extrafile && file_exists(libfile('member/'.$this->extrafile, 'module'))) {
				require_once libfile('member/'.$this->extrafile, 'module');
			}

			DB::insert('common_member', $userdata);
			DB::insert('common_member_status', $status_data);
			DB::insert('common_member_profile', $profile);
			DB::insert('common_member_field_forum', $field_forum);
			DB::insert('common_member_field_home', $field_home);

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
			$userstats = array('totalmembers' => $totalmembers, 'newsetuser' => stripslashes($username));

			save_syscache('userstats', $userstats);

			if($this->setting['regctrl'] || $this->setting['regfloodctrl']) {
				DB::query("DELETE FROM ".DB::table('common_regip')." WHERE dateline<='$_G[timestamp]'-".($this->setting['regctrl'] > 72 ? $this->setting['regctrl'] : 72)."*3600", 'UNBUFFERED');
				if($this->setting['regctrl']) {
					DB::query("INSERT INTO ".DB::table('common_regip')." (ip, count, dateline)
						VALUES ('$_G[clientip]', '-1', '$_G[timestamp]')");
				}
			}

			$regmessage = dhtmlspecialchars($_G['gp_regmessage']);
			if($this->setting['regverify'] == 2) {
				DB::query("REPLACE INTO ".DB::table('common_member_validate')." (uid, submitdate, moddate, admin, submittimes, status, message, remark)
					VALUES ('$uid', '$_G[timestamp]', '0', '', '1', '0', '$regmessage', '')");
				manage_addnotify('verifyuser');
			}

			setloginstatus(array(
				'uid' => $uid,
				'username' => dstripslashes($_G['username']),
				'password' => $password,
				'groupid' => $groupinfo['groupid'],
			), 0);
			include_once libfile('function/stat');
			updatestat('register');

			if($invite['id']) {
				$result = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_invite')." WHERE uid='$invite[uid]' AND fuid='$uid'");
				if(!$result) {
					DB::update("common_invite", array('fuid'=>$uid, 'fusername'=>$_G['username'], 'regdateline' => $_G['timestamp'], 'status' => 2), array('id'=>$invite['id']));
					updatestat('invite');
				} else {
					$invite = array();
				}
			}
			if($invite['uid']) {
				if($this->setting['inviteconfig']['inviteaddcredit']) {
					updatemembercount($uid, array($this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['inviteaddcredit']));
				}
				if($this->setting['inviteconfig']['invitedaddcredit']) {
					updatemembercount($invite['uid'], array($this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['invitedaddcredit']));
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
				$welcomemsgtitle = addslashes(replacesitevar($welcomemsgtitle));
				$welcomemsgtxt = addslashes(replacesitevar($welcomemsgtxt));
				if($welcomemsg == 1) {
					$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
					notification_add($uid, 'system', $welcomemsgtxt, array(), 1);
				} elseif($welcomemsg == 2) {
					sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
				} elseif($welcomemsg == 3) {
					sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
					$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
					notification_add($uid, 'system', $welcomemsgtxt, array(), 1);
				}
			}

			if($fromuid) {
				updatecreditbyaction('promotion_register', $fromuid);
				dsetcookie('promotion', '');
			}
			dsetcookie('loginuser', '');
			dsetcookie('activationauth', '');
			dsetcookie('invite_auth', '');

			loadcache('setting', true);
			$_G['setting']['lastmember'] = stripslashes($username);
			$settingnew = $_G['setting'];
			$settingnew['pluginhooks'] = array();
			save_syscache('setting', $settingnew);

			switch($this->setting['regverify']) {
				case 1:
					$idstring = random(6);
					$authstr = $this->setting['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';
					DB::query("UPDATE ".DB::table('common_member_field_forum')." SET authstr='$authstr' WHERE uid='$_G[uid]'");
					$verifyurl = "{$_G[siteurl]}member.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";
					$email_verify_message = lang('email', 'email_verify_message', array(
						'username' => $_G['member']['username'],
						'bbname' => $this->setting['bbname'],
						'siteurl' => $_G['siteurl'],
						'url' => $verifyurl
					));
					sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message);
					$message = 'register_email_verify';
					$locationmessage = 'register_email_verify_location';
					$url_forward = dreferer();
					break;
				case 2:
					$message = 'register_manual_verify';
					$locationmessage = 'register_manual_verify_location';
					$url_forward = $_G['setting']['homestatus'] ? 'home.php?mod=space&do=home' : 'home.php?mod=spacecp';
					break;
				default:
					$message = 'register_succeed';
					$locationmessage = 'register_succeed_location';
					$url_forward = dreferer();
					break;
			}
			$param = array('bbname' => $this->setting['bbname'], 'username' => $_G['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']);
			if(strpos($url_forward, $this->setting['regname']) !== false || strpos($url_forward, 'buyinvitecode') !== false) {
				$url_forward = 'forum.php';
			}
			$href = str_replace("'", "\'", $url_forward);
			$extra = array(
				'showid' => 'succeedmessage',
				'extrajs' => '<script type="text/javascript">'.
					'setTimeout("window.location.href =\''.$href.'\';", 3000);'.
					'$(\'succeedmessage_href\').href = \''.$href.'\';'.
					'$(\'main_message\').style.display = \'none\';'.
					'$(\'main_succeed\').style.display = \'\';'.
					'$(\'succeedlocation\').innerHTML = \''.lang('message', $locationmessage).'\';'.
				'</script>',
				'striptags' => false,
			);
			showmessage($message, $url_forward, $param, $extra);
		}




	}

}

?>