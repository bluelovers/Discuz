<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile.class.php 27575 2012-02-06 07:00:31Z monkey $
 */

class mobile_core {

	function result($result) {
		global $_G;
		ob_end_clean();
		$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
		echo mobile_core::json($result);
		exit;
	}

	function json($encode) {
		if(!empty($_GET['debug']) && defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
			return debug($encode);
		}
		require_once 'source/plugin/mobile/json.class.php';
		return CJSON::encode($encode);
	}

	function getvalues($variables, $keys, $subkeys = array()) {
		$return = array();
		foreach($variables as $key => $value) {
			foreach($keys as $k) {
				if($k{0} == '/' && preg_match($k, $key) || $key == $k) {
					if($subkeys) {
						$return[$key] = mobile_core::getvalues($value, $subkeys);
					} else {
						$return[$key] = $value;
					}
				}
			}
		}
		return $return;
	}

	function variable($variables = array()) {
		global $_G;
		$globals = array(
			'cookiepre' => $_G['config']['cookie']['cookiepre'],
			'auth' => $_G['cookie']['auth'],
			'saltkey' => $_G['cookie']['saltkey'],
			'member_uid' => $_G['member']['uid'],
			'member_username' => $_G['member']['username'],
			'formhash' => FORMHASH,
			'ucenterurl' => $_G['setting']['ucenterurl'],
			'ismoderator' => $_G['forum']['ismoderator'],
		);
		if(!empty($_GET['submodule']) == 'checkpost') {
			$apifile = 'source/plugin/mobile/api/'.$_GET['version'].'/sub_checkpost.php';
			if(file_exists($apifile)) {
				require_once $apifile;
			}
			$globals = $globals + mobile_api_sub::getvariable();
		}
		$xml = array(
			'Version' => '2',
			'Charset' => strtoupper($_G['charset']),
			'Variables' => array_merge($globals, $variables),
		);
		if(!empty($_G['messageparam'])) {
			$message_result = lang('plugin/mobile', $_G['messageparam'][0], $_G['messageparam'][2]);
			if($message_result == $_G['messageparam'][0]) {
				$vars = explode(':', $_G['messageparam'][0]);
				if (count($vars) == 2) {
					$message_result = lang('plugin/' . $vars[0], $vars[1], $_G['messageparam'][2]);
				} else {
					$message_result = lang('message', $_G['messageparam'][0], $_G['messageparam'][2]);
				}
			}
			if($_G['messageparam'][4]) {
				$_G['messageparam'][0] = "custom";
			}
			if ($_G['messageparam'][3]['login'] && !$_G['uid']) {
				$_G['messageparam'][0] .= '//' . $_G['messageparam'][3]['login'];
			}
			$xml['Message'] = array("messageval" => $_G['messageparam'][0], "messagestr" => $message_result);
			if($_GET['mobilemessage']) {
				$return = mobile_core::json($variables);
				header("HTTP/1.1 301 Moved Permanently");
				header("Location:discuz://" . $_G['messageparam'][0] . "//" . rawurlencode(diconv($message_result, $_G['charset'], "utf-8")) . ($return ? "//" . rawurlencode($return) : '' ));
				exit;
			}
		}
		return $xml;
	}

}

class plugin_mobile {

	function common() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		global $_G;
		if(!empty($_GET['tpp'])) {
			$_G['tpp'] = intval($_GET['tpp']);
		}
		if(!empty($_GET['ppp'])) {
			$_G['ppp'] = intval($_GET['ppp']);
		}
		$_G['setting']['msgforward'] = '';
		if(class_exists('mobile_api', 'common')) {
			mobile_api::common();
		}
	}

	function global_mobile() {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		if(class_exists('mobile_api', 'global_mobile')) {
			mobile_api::output();
		}
	}

}

class plugin_mobile_forum extends plugin_mobile {

	function post_mobile_message($param) {
		if(!defined('IN_MOBILE_API')) {
			return;
		}
		if(class_exists('mobile_api', 'post_mobile_message')) {
			list($message, $url_forward, $values, $extraparam, $custom) = $param['param'];
			mobile_api::post_mobile_message($message, $url_forward, $values, $extraparam, $custom);
		}
	}

}

class plugin_mobile_misc extends plugin_mobile {

	function mobile() {
		global $_G;
		if(empty($_GET['view']) && !defined('MOBILE_API_OUTPUT')) {
			$_G['setting']['pluginhooks'] = array();
			$qrfile = DISCUZ_ROOT.'./data/cache/mobile_siteqrcode.png';
			if(!file_exists($qrfile) || $_G['adminid'] == 1) {
				require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
				QRcode::png($_G['siteurl'], $qrfile);
			}
			define('MOBILE_API_OUTPUT', 1);
			include template('mobile:mobile');exit;
		}
	}

}

?>