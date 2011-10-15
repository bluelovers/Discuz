<?php

/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect.class.php 291 2011-04-11 01:49:22Z fengning $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_qqconnect_base {

	function init() {
		global $_G;
		include_once template('qqconnect:module');
		if(!$_G['setting']['connect']['allow'] || $_G['setting']['bbclosed']) {
			return;
		}
		$this->allow = true;
	}

	function common_base() {
		global $_G;

		if(!isset($_G['connect'])) {
			$_G['connect']['url'] = 'http://connect.discuz.qq.com';
			$_G['connect']['api_url'] = 'http://api.discuz.qq.com';
			$_G['connect']['avatar_url'] = 'http://avatar.connect.discuz.qq.com';

			$_G['connect']['qzone_public_share_url'] = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey';
			$_G['connect']['referer'] = !$_G['inajax'] && CURSCRIPT != 'member' ? $_G['basefilename'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '') : dreferer();
			$_G['connect']['weibo_public_appkey'] = 'ce7fb946290e4109bdc9175108b6db3a';

			$_G['connect']['login_url'] = $_G['siteurl'].'connect.php?mod=login&op=init&referer='.urlencode($_G['connect']['referer'] ? $_G['connect']['referer'] : 'index.php');
			$_G['connect']['callback_url'] = $_G['siteurl'].'connect.php?mod=login&op=callback';
			$_G['connect']['discuz_new_feed_url'] = $_G['siteurl'].'connect.php?mod=feed&op=new';
			$_G['connect']['discuz_remove_feed_url'] = $_G['siteurl'].'connect.php?mod=feed&op=remove';
			$_G['connect']['discuz_new_share_url'] = $_G['siteurl'].'connect.php?mod=share&op=new';
			$_G['connect']['discuz_new_share_url'] = $_G['siteurl'].'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=new';
			$_G['connect']['discuz_change_qq_url'] = $_G['siteurl'].'connect.php?mod=login&op=change';
			$_G['connect']['auth_fields'] = array(
				'is_user_info' => 1,
				'is_feed' => 2,
			);

			if($_G['uid']) {
				dsetcookie('connect_is_bind', $_G['member']['conisbind'], 31536000);
				if(!$_G['member']['conisbind'] && $_G['cookie']['connect_login']) {
					$_G['cookie']['connect_login'] = 0;
					dsetcookie('connect_login');
				}
			}

			if(!$_G['uid'] && !defined('IN_MOBILE')) {
				$_G['setting']['pluginhooks']['global_login_text'] = tpl_login_bar();
			}
		}
	}

}

class plugin_qqconnect extends plugin_qqconnect_base {

	var $allow = false;

	function plugin_qqconnect() {
		$this->init();
	}

	function common() {
		$this->common_base();
	}

	function discuzcode($param) {
		global $_G;
		if($param['caller'] == 'discuzcode') {
			$_G['discuzcodemessage'] = preg_replace('/\[wb=(.+?)\](.+?)\[\/wb\]/', '<a href="http://t.qq.com/\\1" target="_blank"><img src="\\2" /></a>', $_G['discuzcodemessage']);
		}
	}

	function global_login_extra() {
		if(!$this->allow) {
			return;
		}
		return tpl_global_login_extra();
	}

	function global_usernav_extra1() {
		global $_G;
		if(!$this->allow || !$_G['uid']) {
			return;
		}
		if(!$_G['member']['conisbind']) {
			return tpl_global_usernav_extra1();
		}
		return;
	}

	function global_footer() {
		global $_G;

		if(!$this->allow || !empty($_G['inshowmessage'])) {
			return;
		}

		$footerjs = '';

		require_once libfile('function/connect');

		if(defined('CURSCRIPT') && CURSCRIPT == 'forum' && defined('CURMODULE') && CURMODULE == 'viewthread'
			&& $_G['setting']['connect']['allow'] && $_G['setting']['connect']['qshare_allow']) {

			$appkey = $_G['setting']['connect']['qshare_appkey'] ? $_G['setting']['connect']['qshare_appkey'] : '';
			$footerjs .= connect_load_qshare_js($appkey);
		}

		if(!empty($_G['cookie']['connect_js_name']) && $_G['cookie']['connect_js_name'] == 'user_bind') {
			$params = array('openid' => $_G['cookie']['connect_uin']);
			$footerjs .= connect_user_bind_js($params);
		}elseif($_G['cookie']['connect_js_name'] == 'feed_resend') {
			$footerjs .= connect_feed_resend_js();
		}

		connect_merge_member();
		if(!$_G['cookie']['connect_check_token'] && $_G['member']['conuinsecret']) {
			$footerjs .= connect_check_token_js();
		}

		if($_G['member']['conuinsecret'] && ($_G['cookie']['connect_last_report_time'] != date('Y-m-d') || $_G['cookie']['connect_report_times'] <= 4)) {
			$footerjs .= connect_cookie_login_js();
		}

		return $footerjs;
	}

	function _allowconnectfeed() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		return $_G['uid'] && $_G['setting']['connect']['allow'] && $_G['setting']['connect']['feed']['allow'] && ($_G['forum']['status'] == 3 && $_G['setting']['connect']['feed']['group'] || $_G['forum']['status'] != 3 && (!$_G['setting']['connect']['feed']['fids'] || in_array($_G['fid'], $_G['setting']['connect']['feed']['fids'])));
	}

	function _allowconnectt() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		return $_G['uid'] && $_G['setting']['connect']['allow'] && $_G['setting']['connect']['t']['allow'] && ($_G['forum']['status'] == 3 && $_G['setting']['connect']['t']['group'] || $_G['forum']['status'] != 3 && (!$_G['setting']['connect']['t']['fids'] || in_array($_G['fid'], $_G['setting']['connect']['t']['fids'])));
	}

	function _forumdisplay_fastpost_sync_method_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$allowconnectfeed = $this->_allowconnectfeed();
		$allowconnectt = $this->_allowconnectt();
		if($GLOBALS['fastpost'] && ($allowconnectfeed || $allowconnectt)) {
			require_once libfile('function/connect');
			connect_merge_member();
			if ($_G['member']['is_feed']) {
				return tpl_sync_method($allowconnectfeed, $allowconnectt);
			}
		}
	}

	function _post_sync_method_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$allowconnectfeed = $this->_allowconnectfeed();
		$allowconnectt = $this->_allowconnectt();
		if(!$_G['inajax'] && ($allowconnectfeed || $allowconnectt) && ($_G['gp_action'] == 'newthread' || $_G['gp_action'] == 'edit' && $GLOBALS['isfirstpost'] && $GLOBALS['thread']['displayorder'] == -4)) {
			require_once libfile('function/connect');
			connect_merge_member();
			if ($_G['member']['is_feed']) {
				return tpl_sync_method($allowconnectfeed, $allowconnectt);
			}
		}
	}

	function _post_infloat_btn_extra_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$allowconnectfeed = $this->_allowconnectfeed();
		$allowconnectt = $this->_allowconnectt();
		if(($allowconnectfeed || $allowconnectt) && $_G['gp_action'] == 'newthread') {
			require_once libfile('function/connect');
			connect_merge_member();
			if ($_G['member']['is_feed']) {
				return tpl_infloat_sync_method($allowconnectfeed, $allowconnectt);
			}
		}
	}

	function _post_feedlog_message($param) {
		if(!$this->allow) {
			return;
		}
		global $_G;
		if(empty($_G['gp_connect_publish_feed']) || $_G['gp_action'] == 'reply' || substr($param['param'][0], -8) != '_succeed' || $_G['gp_action'] == 'edit' && !$GLOBALS['isfirstpost'] || !$this->_allowconnectfeed()) {
			return;
		}

		$tid = $param['param'][2]['tid'];
		DB::query("REPLACE INTO ".DB::table('connect_feedlog')." (tid, uid, lastpublished, dateline, status) VALUES ('$tid', '$_G[uid]', '0', '$_G[timestamp]', '1')");
	}

	function _post_tlog_message($param) {
		if(!$this->allow) {
			return;
		}
		global $_G;
		if(empty($_G['gp_connect_publish_t']) || $_G['gp_action'] == 'reply' || substr($param['param'][0], -8) != '_succeed' || $_G['gp_action'] == 'edit' && !$GLOBALS['isfirstpost'] || !$this->_allowconnectt()) {
			return;
		}

		$tid = $param['param'][2]['tid'];
		DB::query("REPLACE INTO ".DB::table('connect_tlog')." (tid, uid, lastpublished, dateline, status) VALUES ('$tid', '$_G[uid]', '0', '$_G[timestamp]', '1')");
	}

	function _viewthread_share_method_output() {
		global $_G;

		require_once libfile('function/connect');

		if($GLOBALS['page'] == 1 && $_G['forum_firstpid'] && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0) {
			$_G['connect']['feed_js'] = $_G['connect']['t_js'] = false;
			if(!getstatus($_G['forum_thread']['status'], 7) && $_G['forum_thread']['displayorder'] >= 0) {
				$feedlogstatus = false;
				$_G['connect']['feed_log'] = DB::fetch_first("SELECT * FROM ".DB::table('connect_feedlog')." WHERE tid='$_G[tid]'");
				if($_G['connect']['feed_log']) {
					$_G['connect']['feed_interval'] = 300;
					$_G['connect']['feed_publish_max'] = 1000;
					if($_G['connect']['feed_log'] && $_G['member']['conisbind'] && $_G['uid'] == $_G['forum_thread']['authorid']) {
						if($_G['connect']['feed_log']['status'] == 1 || ($_G['connect']['feed_log']['status'] == 2
							&& TIMESTAMP - $_G['connect']['feed_log']['lastpublished'] > $_G['connect']['feed_interval']
							&& $_G['connect']['feed_log']['publishtimes'] < $_G['connect']['feed_publish_max'])) {
							DB::query("UPDATE ".DB::table('connect_feedlog')." SET status='2', lastpublished='$_G[timestamp]', publishtimes=publishtimes+1 WHERE tid='$_G[tid]' AND status!=4");
							$_G['connect']['feed_js'] = $feedlogstatus = true;
						}
					}
				} else {
					$feedlogstatus = false;
				}
			}

			if(!getstatus($_G['forum_thread']['status'], 8) && $_G['forum_thread']['displayorder'] >= 0) {
				$_G['connect']['t_log'] = DB::fetch_first("SELECT * FROM ".DB::table('connect_tlog')." WHERE tid='$_G[tid]'");
				if($_G['connect']['t_log']) {
					$_G['connect']['t_interval'] = 300;
					$_G['connect']['t_publish_max'] = 1000;
					if($_G['connect']['t_log'] && $_G['member']['conisbind'] && $_G['uid'] == $_G['forum_thread']['authorid']) {
						if($_G['connect']['t_log']['status'] == 1 || ($_G['connect']['t_log']['status'] == 2
							&& TIMESTAMP - $_G['connect']['t_log']['lastpublished'] > $_G['connect']['t_interval']
							&& $_G['connect']['t_log']['publishtimes'] < $_G['connect']['t_publish_max'])) {
							DB::query("UPDATE ".DB::table('connect_tlog')." SET status='2', lastpublished='$_G[timestamp]', publishtimes=publishtimes+1 WHERE tid='$_G[tid]' AND status!=4");
							$_G['connect']['t_js'] = $tlogstatus = true;
						}
					}
				} else {
					$tlogstatus = false;
				}
			}

			if($feedlogstatus || $tlogstatus){
				$newstatus = $_G['forum_thread']['status'];
				$newstatus = $feedlogstatus ? setstatus(7, 1, $newstatus) : $newstatus;
				$newstatus = $tlogstatus ? setstatus(8, 1, $newstatus) : $newstatus;
				DB::query("UPDATE ".DB::table('forum_thread')." SET status='$newstatus' WHERE tid='$_G[tid]'");
			}

			$_G['connect']['thread_url'] = $_G['siteurl'].$GLOBALS['canonical'];

			$_G['connect']['qzone_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=1&thread_id=' . $_G['tid'];
			$_G['connect']['weibo_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=2&thread_id=' . $_G['tid'];
			$_G['connect']['pengyou_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=3&thread_id=' . $_G['tid'];

			$_G['connect']['qzone_share_api'] = $_G['connect']['qzone_public_share_url'].'?url='.urlencode($_G['connect']['thread_url']);
			$_G['connect']['pengyou_share_api'] = $_G['connect']['qzone_public_share_url'].'?to=pengyou&url='.urlencode($_G['connect']['thread_url']);
			$params = array('oauth_consumer_key' => $_G['setting']['connectappid'], 'title' => $GLOBALS['postlist'][$_G['forum_firstpid']]['subject'], 'url' => $_G['connect']['thread_url']);
			$params['sig'] = connect_get_sig($params, connect_get_sig_key());
			$_G['connect']['t_share_api'] =	$_G['connect']['url'].'/mblog/redirect?'.cloud_http_build_query($params, '', '&');

			$_G['connect']['first_post'] = daddslashes($GLOBALS['postlist'][$_G['forum_firstpid']]);
			$_G['gp_connect_autoshare'] = !empty($_G['gp_connect_autoshare']) ? 1 : 0;

			$_G['connect']['weibo_appkey'] = $_G['connect']['weibo_public_appkey'];
			if($this->allow && $_G['setting']['connect']['mblog_app_key']) {
				$_G['connect']['weibo_appkey'] = $_G['setting']['connect']['mblog_app_key'];
			}

			$extrajs = '';
			if($_G['connect']['feed_js'] || $_G['connect']['t_js']) {
				$params = array();
				$params['thread_id'] = $_G['tid'];
				$params['ts'] = TIMESTAMP;
				$params['type'] = bindec(($_G['connect']['t_js'] ? '1' : '0').($_G['connect']['feed_js'] ? '1' : '0'));
				$params['sig'] = connect_get_sig($params, connect_get_sig_key());

				$jsurl = $_G['connect']['discuz_new_feed_url'].'&'.cloud_http_build_query($params, '', '&');
				$extrajs = connect_output_javascript($jsurl);
			}

			if (!$_G['member']['conisbind'] && $_G['group']['allowgetimage'] && $_G['thread']['price'] == 0) {
				if ($_G['connect']['first_post']['message']) {
					require_once libfile('function/connect');
					$post['html_content'] = connect_parse_bbcode($_G['connect']['first_post']['message'], $_G['connect']['first_post']['fid'], $_G['connect']['first_post']['pid'], $_G['connect']['first_post']['htmlon'], $attach_images);
					if($attach_images && is_array($attach_images)) {
						$attach_images = array_slice($attach_images, 0, 3);
						$share_images = array();
						foreach ($attach_images as $attach_image) {
							$share_images[] = urlencode($attach_image['big']);
						}
						$_G['connect']['share_images'] = implode('|', $share_images);
						unset($share_images);
					}
				}
			}
			connect_merge_member();
			return tpl_viewthread_share_method().$extrajs;
		}
	}

	function _viewthread_bottom_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		if($GLOBALS['page'] == 1 && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0) {
			return tpl_viewthread_bottom();
		}
	}

	function _viewthread_nonexistence_message($param) {
		if(!$this->allow) {
			return;
		}
		global $_G;
		if($param['param'][0] == 'thread_nonexistence') {
			require_once libfile('function/connect');
			$extrajs = '';
			if($jsurl = connect_feed_remove($_G['gp_tid'])) {
				$extrajs = connect_output_javascript($jsurl);
			}
			showmessage('thread_nonexistence', '', array(), array('extrajs' => $extrajs));
		}
	}

}

class plugin_qqconnect_member extends plugin_qqconnect {

	function logging_method() {
		if(!$this->allow) {
			return;
		}
		return tpl_login_bar();
	}

	function register_logging_method() {
		if(!$this->allow) {
			return;
		}
		return tpl_login_bar();
	}

	function connect_input_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$_G['setting']['pluginhooks']['register_input'] = tpl_register_input();
	}

	function connect_bottom_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$_G['setting']['pluginhooks']['register_bottom'] = tpl_register_bottom();
	}

}

class plugin_qqconnect_forum extends plugin_qqconnect {

	function index_status_extra() {
		global $_G;
		if(!$this->allow) {
			return;
		}
		if($_G['setting']['connect']['like_allow'] && $_G['setting']['connect']['like_url'] || $_G['setting']['connect']['turl_allow'] && $_G['setting']['connect']['turl_code']) {
			return tpl_index_status_extra();
		}
	}

	function forumdisplay_fastpost_sync_method_output() {
		return $this->_forumdisplay_fastpost_sync_method_output();
	}

	function post_sync_method_output() {
		return $this->_post_sync_method_output();
	}

	function post_infloat_btn_extra_output() {
		return $this->_post_infloat_btn_extra_output();
	}

	function post_feedlog_message($param) {
		return $this->_post_feedlog_message($param);
	}

	function post_tlog_message($param) {
		return $this->_post_tlog_message($param);
	}

	function viewthread_share_method_output() {
		return $this->_viewthread_share_method_output();
	}

	function viewthread_bottom_output() {
		return $this->_viewthread_bottom_output();
	}

	function viewthread_nonexistence_message($param) {
		return $this->_viewthread_nonexistence_message($param);
	}

}

class plugin_qqconnect_group extends plugin_qqconnect {

	function forumdisplay_fastpost_sync_method_output() {
		return $this->_forumdisplay_fastpost_sync_method_output();
	}

	function post_sync_method_output() {
		return $this->_post_sync_method_output();
	}

	function post_infloat_btn_extra_output() {
		return $this->_post_infloat_btn_extra_output();
	}

	function post_feedlog_message($param) {
		return $this->_post_feedlog_message($param);
	}

	function post_tlog_message($param) {
		return $this->_post_tlog_message($param);
	}

	function viewthread_share_method_output() {
		return $this->_viewthread_share_method_output();
	}

	function viewthread_bottom_output() {
		return $this->_viewthread_bottom_output();
	}

	function viewthread_nonexistence_message($param) {
		return $this->_viewthread_nonexistence_message($param);
	}

}

class plugin_qqconnect_home extends plugin_qqconnect {

	function spacecp_profile_bottom() {
		global $_G;

		if(submitcheck('profilesubmit')) {
			$_G['group']['maxsigsize'] = $_G['group']['maxsigsize'] < 200 ? 200 : $_G['group']['maxsigsize'];
			return;
		}
		if($_G['uid'] && $_G['setting']['connect']['allow']) {

			require_once libfile('function/connect');
			connect_merge_member();

			if($_G['member']['conuin'] && $_G['member']['conuinsecret']) {

				$arr = array();
				$arr['oauth_consumer_key'] = $_G['setting']['connectappid'];
				$arr['oauth_nonce'] = mt_rand();
				$arr['oauth_timestamp'] = TIMESTAMP;
				$arr['oauth_signature_method'] = 'HMAC_SHA1';
				$arr['oauth_token'] = $_G['member']['conuin'];
				ksort($arr);
				$arr['oauth_signature'] = connect_get_oauth_signature('http://cp.discuz.qq.com/connect/getSignature', $arr, 'GET', $_G['member']['conuinsecret']);
				$result = connect_output_php('http://cp.discuz.qq.com/connect/getSignature?' . http_build_query($arr, '', '&'));
				if($result['status'] == 0) {
					$js = 'a.onclick = function () { seditor_insertunit(\'sightml\', \'[wb='.$result['result']['username'].']'.$result['result']['signature_url'].'[/wb]\'); };';
				} else {
					$js = 'a.onclick = function () { showDialog(\''.lang('plugin/qqconnect', 'connect_wbsign_no_account').'\'); };';
				}
			} else {
				$js = 'a.onclick = function () { showDialog(\''.lang('plugin/qqconnect', 'connect_wbsign_no_bind').'\'); };';
			}
			return '<script type="text/javascript">if($(\'sightmlsml\')) {'.
				'var a = document.createElement(\'a\');a.href = \'javascript:;\';a.style.background = \'url(\' + STATICURL + \'image/common/weibo.png) no-repeat 0 2px\';'.
				'a.onmouseover = function () { showTip(this); };a.setAttribute(\'tip\', \''.lang('plugin/qqconnect', 'connect_wbsign_tip').'\');'.
				$js.
				'$(\'sightmlsml\').parentNode.appendChild(a);'.
				'}</script>';

		}

	}
}

?>