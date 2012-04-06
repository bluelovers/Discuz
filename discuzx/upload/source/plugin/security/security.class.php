<?php

/**
 *		[Discuz! X] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: security.class.php 28951 2012-03-20 09:01:38Z liudongdong $
 */


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_security {
	var $debug = 0;
	var $_secStatus;
	var $postReportAction = array('post_newthread_succeed', 'post_edit_succeed', 'post_reply_succeed',
							'post_newthread_mod_succeed', 'post_newthread_mod_succeed', 'post_reply_mod_succeed',
							'edit_reply_mod_succeed', 'edit_newthread_mod_succeed');
	var $userReportAction = array('login_succeed', 'register_succeed', 'location_login_succeed_mobile',
							'location_login_succeed', 'register_succeed_location', 'register_email_verify',
							'register_manual_verify', 'login_succeed_inactive_member');
	var $hookMoudle = array('post', 'logging', 'register');
	var $isAdminGroup = 0;

	function __construct() {
		require_once libfile('function/cloud');
		$this->_secStatus = getcloudappstatus('security');
	}

	function common(){
		global $_G;
		if (!$this->_secStatus) {
			return false;
		}
		if ($_G['uid'] && $_G['gp_mod'] != 'initsys') {
			$lastCookieReportTime = $this->_decodeReportTime($_G['cookie']['cookiereport']);
			if ($lastCookieReportTime < strtotime('today')) {
				$this->_reportLoginUser(array('uid' => $_G['uid']));
			}
		}

		if ($_G['adminid'] > 0) {
			$this->isAdminGroup = 1;
		}

		return true;
	}

	function global_footer() {
		global $_G;
		if (!$this->_secStatus) {
			return false;
		}
		$formhash = formhash();
		$ajaxReportScript = '';
		$processName = 'securitOperate';
		if ($this->isAdminGroup && !discuz_process::islocked($processName, 10)) {
			$ajaxReportScript = <<<EOF
			<script type='text/javascript'>
			var url = SITEURL + '/plugin.php?id=security:sitemaster';
			var x = new Ajax();
			x.post(url, 'formhash=$formhash', function(s){});
			</script>
EOF;
		}

		$processName = 'securitRetry';
		$time = 5;
		if ($_G['gp_d']) {
			$time = 1;
		}
		if (!discuz_process::islocked($processName, $time)) {
			$ajaxRetryScript = <<<EOF
			<script type='text/javascript'>
			var urlRetry = SITEURL + '/plugin.php?id=security:job';
			var ajaxRetry = new Ajax();
			ajaxRetry.post(urlRetry, 'formhash=$formhash', function(s){});
			</script>
EOF;
		}
		return $ajaxReportScript . $ajaxRetryScript;
	}

	function global_footerlink() {
		return '&nbsp;<a href="javascript:;" title="'.lang('plugin/security', 'title').'"><img src="static/image/common/security.png"></a>&nbsp;';
	}

	function deletepost($param) {
		global $_G;
		if (!$this->_secStatus) {
			return false;
		}
		$step = $param['step'];
		$param = $param['param'];
		$ids = $param[0];
		$idType = $param[1];


		if ($_G['gp_formhash'] && $step == 'check' && $idType == 'pid') {
			require_once libfile('function/sec');
			updatePostOperate($ids, 2);
			if ($_G['gp_module'] == 'security' && $_G['gp_method'] == 'setEvilPost') {
				return true;
			}
			logDeletePost($ids, $_G['gp_reason']);
		}
		return true;
	}

	function deletethread($param) {
		global $_G;
		if (!$this->_secStatus) {
			return false;
		}
		$step = $param['step'];
		$param = $param['param'];
		$ids = $param[0];

		if ($_G['gp_formhash'] && $step == 'check') {
			require_once libfile('function/sec');
			updateThreadOperate($ids, 2);
			if ($_G['gp_module'] == 'security' && $_G['gp_method'] == 'setEvilPost') {
				return true;
			}
			logDeleteThread($ids, $_G['gp_reason']);
		}
		return true;
	}


	function _decodeReportTime($time) {
		if (!$time) {
			return 0;
		}
		return authcode($time);
	}
	function _encodeReportTime($time) {
		if (!$time) {
			return 0;
		}
		return authcode($time, 'ENCODE');
	}

	function _reportRegisterUser($param) {
		global $_G;
		if (!$param['uid'] && !$_G['uid']) {
			return false;
		} else {
			$param['uid'] = $_G['uid'];
		}
		$this->secLog('USERREG-UID', $param['uid']);

		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$sec->reportRegister($param['uid']);
		$this->_retryReport();
	}

	function _reportLoginUser($param) {
		global $_G;
		if (!$param['uid'] && !$_G['uid']) {
			return false;
		} else {
			$param['uid'] = $_G['uid'];
		}
		$this->secLog('USERLOG-UID', $param['uid']);
		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$sec->reportLogin($param['uid']);
		$this->_retryReport();
		dsetcookie('cookiereport', $this->_encodeReportTime($_G['timestamp']), '86400');
		return true;
	}

	function _reportMobileLoginUser($param) {
		if (!$param['username']) {
			return false;
		}
		$username = addslashes($param['username']);
		$result = DB::fetch_first("SELECT uid FROM " . DB::table('common_member') . " WHERE username = '$username'");
		return $this->_reportLoginUser($result);
	}

	function _reportNewThread($param) {
		global $_G;
		if (!$param['pid'] || !$param['tid']) {
			return false;
		}
		$this->secLog('NEWTHREAD-TID', $param['tid']);
		$tid = $param['tid'];
		$pid = $param['pid'];

		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$sec->reportNewThread($tid, $pid);
		$this->_retryReport();
		return true;
	}

	function _reportNewPost($param) {
		global $_G;
		if (!$param['pid'] || !$param['tid']) {
			return false;
		}
		$this->secLog('NEWPOST-PID', $param['pid']);
		$tid = $param['tid'];
		$pid = $param['pid'];

		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$sec->reportNewPost($tid, $pid);
		$this->_retryReport();
		return true;
	}

	function _reportEditPost($param) {
		global $_G;
		if (!$param['pid'] || !$param['tid']) {
			return false;
		}
		$this->secLog('EDITPOST-PID', $param['pid']);
		$tid = $param['tid'];
		$pid = $param['pid'];

		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$sec->reportEditPost($tid, $pid);
		$this->_retryReport();
		return true;
	}

	function _retryReport() {
		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$sec->retryReportData();
	}

	function secLog($type, $data) {
		global $_G;
		if (!$this->debug) {
			return false;
		}
		$date = date("Y-m-d", $_G['timestamp']);
		$logfile = DISCUZ_ROOT."/data/EVIL" . $type . '-' . $date . ".log";
		$data = date("Y-m-d H:i:s", $_G['timestamp']) . "\t" . json_encode($data) . "\r\n";
		@file_put_contents($logfile, $data, FILE_APPEND);
	}

	function getMergeAction() {
		return array_merge($this->postReportAction, $this->userReportAction);
	}
}

class plugin_security_forum extends plugin_security {

	function post_security(){
		global $_G;
	}

	function post_report_message($param) {
		if (!$this->_secStatus) {
			return false;
		}
		global $_G, $extra, $redirecturl;
		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], $this->postReportAction)) {
			switch ($param['message']) {
				case 'post_newthread_succeed':
				case 'post_newthread_mod_succeed':
					$this->_reportNewThread($param['values']);
					break;
				case 'post_edit_succeed':
				case 'edit_reply_mod_succeed':
				case 'edit_newthread_mod_succeed':
					$this->_reportEditPost($param['values']);
					break;
				case 'post_reply_succeed':
				case 'post_reply_mod_succeed':
					$this->_reportNewPost($param['values']);
				default:break;
			}
		}
	}
}

class plugin_security_group extends plugin_security_forum {}

class plugin_security_member extends plugin_security {

	function logging_report_message($param) {
		if (!$this->_secStatus) {
			return false;
		}
		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], $this->userReportAction)) {
			if (!$param['values']['uid']) {
				$this->_reportLoginUser($param['values']);
			} else {
				$this->_reportMobileLoginUser($param['values']);
			}
		}
	}

	function register_report_message($param) {
		if (!$this->_secStatus) {
			return false;
		}
		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], $this->userReportAction)) {
			$this->_reportRegisterUser($param['values']);
		}
	}
	function connect_report_message($param) {
		if (!$this->_secStatus) {
			return false;
		}
		global $_G;
		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (($_G['gp_regsubmit'] || $_G['gp_loginsubmit']) && $_G['gp_formhash']) {
			if ($_G['gp_loginsubmit']) {
				$this->_reportLoginUser($_G['member']);
			} else {
				$this->_reportRegisterUser($param['values']);
			}
		}
	}
}

class mobileplugin_security extends plugin_security {}

class mobileplugin_security_forum extends plugin_security_forum {}

class mobileplugin_security_member extends plugin_security_member {}

class plugin_security_connect extends plugin_security_member {

	function login_report_message($param) {
		if (!$this->_secStatus) {
			return false;
		}
		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], $this->userReportAction)) {
			switch ($param['message']) {
				case login_succeed:
				case location_login_succeed:
				case location_login_succeed_mobile:
					$this->_reportMobileLoginUser($param['values']);
				default:break;
			}
		}
	}
}