<?php

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_onexin_sendmail {

	var $onexin_sendmail = FALSE;

	function plugin_onexin_sendmail() {
		global $_G;
		$this->onexin_sendmail = $_G['cache']['plugin']['onexin_sendmail']['isopen'] ? TRUE : FALSE;
		$this->onexin_verify = $_G['cache']['plugin']['onexin_sendmail']['verify'] ? TRUE : FALSE;
	}

	function global_usernav_extra2() {
		global $_G;

		if ($this->onexin_sendmail) {
			if (!is_array($_G['setting']['mail'])) {
				$_G['setting']['mail'] = unserialize($_G['setting']['mail']);
			}
			if ($_G['setting']['mail']['mailsend'] == 1) {
				ini_set("sendmail_from", $_G['setting']['adminemail']);
			}
		}

		if ($this->onexin_verify && empty($_G['member']['emailstatus'])) {
			//$url = 'home.php?mod=spacecp&ac=profile&op=password&resend='.$_G['uid'].'';
			$url = 'home.php?mod=spacecp&ac=profile&op=password';
			return '<span class="pipe">|</span><a id="myprompt" class="new" href="' . $url . '">' . lang('plugin/onexin_sendmail', 'verify_email') . '</a> ';
		}
	}

}

?>