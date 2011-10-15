<?php

/**
 *	  [Discuz!] (C)2001-2009 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/connect');
require_once libfile('function/cloud');

$op = !empty($_G['gp_op']) ? $_G['gp_op'] : '';
if (!in_array($op, array('token', 'cookie'))) {
	connect_ajax_ouput_message('', 'undefined_action', 1);
}

if ($op == 'token') {

	connect_merge_member();

	if($_G['setting']['connect']['allow'] && !$_G['cookie']['connect_check_token'] && $_G['member']['conuinsecret']) {

		dsetcookie('connect_check_token', '1', 14400);

		$api_url = $_G['connect']['api_url'] . '/connect/discuz/validateToken';

		$extra = array(
			'oauth_token' => $_G['member']['conuin'],
		);
		$sig_params = connect_get_oauth_signature_params($extra);
		$oauth_token_secret = $_G['member']['conuinsecret'];
		$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);

		$params = array(
			'client_ip' => $_G['clientip'],
			'response_type' => 'PHP',
		);

		$params = array_merge($sig_params, $params);
		$response = connect_output_php($api_url . '?', cloud_http_build_query($params, '', '&'));
		if(isset($response['status']) && ($response['status'] === 2024 || $response['status'] === 2025)) {

			DB::query('UPDATE '.DB::table('common_member_connect')." SET conuinsecret='' WHERE conopenid='".$_G['member']['conopenid']."'");

			connect_ajax_ouput_message('2', 'token_outofdate', 0);
		}
	}

	connect_ajax_ouput_message('', 'token_not_outofdate', 2);

} elseif ($op == 'cookie') {

	$now = time();
	$life = 86400;
	$response = '';
	$api_url = $_G['connect']['api_url'].'/connect/discuz/cookieReport';
	$params = connect_cookie_login_params();

	if($params) {
		$last_report_time = getcookie('connect_last_report_time');
        $current_date = date('Y-m-d');

        if(getcookie('connect_report_times')) {
            $retry = intval(getcookie('connect_report_times'));
        } else {
            $retry = 1;
        }

        if($last_report_time == $current_date) {
            if($retry <= 4) {
                $response = connect_output_php($api_url.'?', cloud_http_build_query($params, '', '&'));
                $retry++;
            }
        } else {
            $response = connect_output_php($api_url.'?', cloud_http_build_query($params, '', '&'));
            dsetcookie('connect_last_report_time', $current_date, $life);
            $retry = 1;
        }

        if($response['status'] === 0) {
        	$retry = 5;
        }

		dsetcookie('connect_report_times', $retry, $life);
	}
}
?>