<?php

/**
 *	  [Discuz! X] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: spacecp.inc.php 19663 2011-01-13 05:55:18Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

require_once libfile('function/connect');

$pluginop = !empty($_G['gp_pluginop']) ? $_G['gp_pluginop'] : 'config';
if (!in_array($pluginop, array('config', 'share', 'new'))) {
	showmessage('undefined_action');
}
$sh_type = trim(intval($_G['gp_sh_type']));
$tid = trim(intval($_G['gp_thread_id']));

if ($pluginop == 'config') {

	connect_merge_member();

	$_G['connect']['is_oauth_user'] = true;
	if (empty($_G['member']['conuinsecret'])) {
		$_G['connect']['is_oauth_user'] = false;
	}

	$referer = str_replace($_G['siteurl'], '', dreferer());
	if(!empty($_G['gp_connect_autoshare'])) {
		if(strpos($referer, '?') !== false) {
			$referer .= '&connect_autoshare=1';
		} else {
			$referer .= '?connect_autoshare=1';
		}
	}

	$_G['connect']['loginbind_url'] = $_G['siteurl'].'connect.php?mod=login&op=init&type=loginbind&referer='.urlencode($_G['connect']['referer'] ? $_G['connect']['referer'] : 'index.php');

} elseif ($pluginop == 'share') {

	$_G['gp_share_url'] = $_G['connect']['discuz_new_share_url'];

	$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid = '$tid' AND displayorder >= 0");
	$posttable = 'forum_post'.($thread['posttableid'] ? "_$thread[posttableid]" : '');
	$post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid = '$tid' AND first='1' AND invisible='0'");

	if ($_G['group']['allowgetimage'] && $thread['price'] == 0 && $post['pid']) {
		connect_parse_bbcode($post['message'], $thread['fid'], $post['pid'], $post['htmlon'], $attach_images);
		if ($attach_images && is_array($attach_images)) {
			$_G['gp_share_images'] = array_slice($attach_images, 0, 3);

			$attach_images = array();
			foreach ($_G['gp_share_images'] as $image) {
				$attach_images[] = $image['big'];
			}
			$_G['gp_attach_image'] = implode('|', $attach_images);
			unset($attach_images);
		}
	}

} elseif ($pluginop == 'new') {

	$sh_type = trim(intval($_G['gp_sh_type']));
	$tid = trim(intval($_G['gp_thread_id']));
	$dialog_id = $_G['gp_dialog_id'];
	$sync_post = $_G['gp_sync_post'];

	connect_merge_member();

	if (!$_G['member']['conuinsecret']) {
		$message = lang('plugin/qqconnect', 'connect_access_token_out_of_date_share');
	} else {

		$api_url = $_G['connect']['api_url'] . '/connect/share/new';

		$extra = array();
		$extra['oauth_token'] = $_G['member']['conuin'];
		$sig_params = connect_get_oauth_signature_params($extra);
		$oauth_token_secret = $_G['member']['conuinsecret'];
		$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);

		$params['sh_type'] = $sh_type;
		$params['subject'] = $_G['gp_subject'];
		$params['share_subject'] = $_G['gp_share_subject'];
		$params['thread_id'] = $_G['gp_thread_id'];
		$params['author'] = $_G['gp_author'];
		$params['author_id'] = $_G['gp_author_id'];
		$params['forum_id'] = $_G['gp_forum_id'];
		$params['p_id'] = $_G['gp_post_id'];
		$parmas['u_id'] = $_G['uid'];
		$params['reason'] = $_G['gp_reason'];
		$params['content'] = $_G['gp_html_content'];
		$params['client_ip'] = $_G['clientip'];
		$params['attach_images'] = $_G['gp_attach_image'];
		$params = array_merge($sig_params, $params);

		$response = connect_output_php($api_url . '?', cloud_http_build_query($params, '', '&'));
		if(!isset($response['status'])) {
			$code = 100;
			connect_errlog($code, lang('connect', 'connect_errlog_server_no_response'));
			$message = lang('connect', 'server_busy');
		} else {
			if ($response['status'] == 0) {
				$code = $response['status'];
				if ($sh_type == 2) {
					$message = lang('connect', 'broadcast_success');
				} else {
					$message = lang('connect', 'share_success');
				}
			} else {
				$message = lang('connect', 'server_busy');
				$code = $response['status'];
				if ($response['status'] == 6022 || $response['status'] == 6023 || $response['status'] == 6029) {
					$message = $response['result'];
					connect_errlog($code, $message);
				} elseif ($response['status'] == 20000) {
					$message = lang('connect', 'user_unauthorized');
				} elseif ($response['status'] == 30000) {
					$message = lang('connect', 'weibo_account_not_signup');
				} elseif ($response['status'] == 40000) {
					$message = lang('plugin/qqconnect', 'connect_access_token_out_of_date_share');
				}
			}
		}
	}
}
?>