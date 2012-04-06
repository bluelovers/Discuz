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

		if ($_G['member']['conisbind']) {
			connect_merge_member();
			if($_G['member']['conuinsecret'] && ($_G['cookie']['connect_last_report_time'] != dgmdate(TIMESTAMP, 'Y-m-d'))) {
				$connect_login_times = DB::result_first("SELECT skey FROM ".DB::table('common_setting')." WHERE skey='connect_login_times'");
				if ($connect_login_times) {
					DB::query("UPDATE ".DB::table('common_setting')." SET svalue=svalue+1 WHERE skey='connect_login_times'");
				} else {
					DB::query("INSERT INTO ".DB::table('common_setting')." SET skey='connect_login_times', svalue='1'");
				}
				$current_date = dgmdate(TIMESTAMP, 'Y-m-d');
				$life = 86400;
				dsetcookie('connect_last_report_time', $current_date, $life);
			}
		}

		$settings = array();
		$query = DB::query("SELECT skey, svalue FROM ".DB::table('common_setting')." WHERE skey IN ('connect_login_times', 'connect_login_report_date')");
		while ($setting = DB::fetch($query)) {
			$settings[$setting['skey']] = $setting['svalue'];
		}

		if ($settings['connect_login_times'] && (empty($settings['connect_login_report_date']) || dgmdate(TIMESTAMP, 'Y-m-d') != $settings['connect_login_report_date'])) {
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
		if($_G['gp_action'] == 'reply' || substr($param['param'][0], -8) != '_succeed' || $_G['gp_action'] == 'edit' && !$GLOBALS['isfirstpost'] || !$this->_allowconnectfeed() && !$this->_allowconnectt() || empty($_G['gp_connect_publish_feed']) && empty($_G['gp_connect_publish_t'])) {
			return;
		}

		$newstatus = 0;
		if ($_G['gp_connect_publish_feed'] && $this->_allowconnectfeed()) {
			$newstatus = setstatus(1, 1, $newstatus);
		}

		if ($_G['gp_connect_publish_t'] && $this->_allowconnectt()) {
			$newstatus = setstatus(3, 1, $newstatus);
		}
		$tid = $param['param'][2]['tid'];
		DB::query("REPLACE INTO ".DB::table('connect_feedlog')." (tid, uid, lastpublished, dateline, status) VALUES ('$tid', '$_G[uid]', '0', '$_G[timestamp]', '$newstatus')");
	}

	function _viewthread_share_method_output() {
		global $_G, $postlist;

		require_once libfile('function/connect');

		if($GLOBALS['page'] == 1 && $_G['forum_firstpid'] && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0 && TIMESTAMP - $_G['forum_thread']['dateline'] < 43200) {
			$_G['connect']['feed_js'] = $_G['connect']['t_js'] = $feedlogstatus = $tlogstatus = false;
			if((!getstatus($_G['forum_thread']['status'], 7) || !getstatus($_G['forum_thread']['status'], 8))
				 && $_G['forum_thread']['displayorder'] >= 0 && $_G['member']['conisbind']
				 && $_G['uid'] == $_G['forum_thread']['authorid']) {
				$_G['connect']['feed_log'] = DB::fetch_first("SELECT * FROM ".DB::table('connect_feedlog')." WHERE tid='$_G[tid]'");
				if($_G['connect']['feed_log']) {
					$_G['connect']['feed_interval'] = 300;
					$_G['connect']['feed_publish_max'] = 1000;
					if(getstatus($_G['connect']['feed_log']['status'], 1) || (getstatus($_G['connect']['feed_log']['status'], 2)
						&& TIMESTAMP - $_G['connect']['feed_log']['lastpublished'] > $_G['connect']['feed_interval']
						&& $_G['connect']['feed_log']['publishtimes'] < $_G['connect']['feed_publish_max'])) {
						$_G['connect']['feed_js'] = $feedlogstatus = true;
					}

					if(getstatus($_G['connect']['feed_log']['status'], 3) || (getstatus($_G['connect']['feed_log']['status'], 4)
						&& TIMESTAMP - $_G['connect']['feed_log']['lastpublished'] > $_G['connect']['feed_interval']
						&& $_G['connect']['feed_log']['publishtimes'] < $_G['connect']['feed_publish_max'])) {
						$_G['connect']['t_js'] = $tlogstatus = true;
					}

					if($feedlogstatus || $tlogstatus) {
						$status = $feedlogstatus ? setstatus(2, 1, $status) : $status;
						$status = $tlogstatus ? setstatus(4, 1, $status) : $status;
						DB::query("UPDATE ".DB::table('connect_feedlog')." SET status='$status', lastpublished='$_G[timestamp]', publishtimes=publishtimes+1 WHERE tid='$_G[tid]'");
					}
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

			if (trim($_G['forum']['viewperm'])) {
				$allowViewPermGroupIds = explode("\t", trim($_G['forum']['viewperm']));
			}
			if (trim($_G['forum']['getattachperm'])) {
				$allowViewAttachGroupIds = explode("\t", trim($_G['forum']['getattachperm']));
			}
			$bigWidth = '400';
			$bigHeight = '400';
			$share_images = array();
			foreach ($postlist[$_G['connect']['first_post']['pid']]['attachments'] as $attachment) {
				if ($attachment['isimage'] == 0 || $attachment['price'] > 0
					|| $attachment['readperm'] > $_G['group']['readaccess']
					|| ($allowViewPermGroupIds && !in_array($_G['groupid'], $allowViewPermGroupIds))
					|| ($allowViewAttachGroupIds && !in_array($_G['groupid'], $allowViewAttachGroupIds))) {
					continue;
				}
				$key = md5($attachment['aid'].'|'.$bigWidth.'|'.$bigHeight);
				$bigImageURL = $_G['siteurl'] . 'forum.php?mod=image&aid='.$attachment['aid'] . '&size=' . $bigWidth . 'x' . $bigHeight . '&key=' . rawurlencode($key) . '&type=fixnone&nocache=1';
				$share_images[] = urlencode($bigImageURL);
			}
			$_G['connect']['share_images'] = implode('|', $share_images);

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
			return tpl_spacecp_profile_bottom();
		}
	}
}

?>