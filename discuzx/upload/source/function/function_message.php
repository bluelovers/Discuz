<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_message.php 22775 2011-05-20 05:43:23Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 顯示提示信息
 *
 * @param $message - 提示信息，可中文也可以是 lang_message.php 中的數組 key 值
 * @param $url_forward - 提示後跳轉的 url
 * @param $values - 提示信息中可替換的變量值 array(key => value ...) 形式
 * @param $extraparam - 擴展參數 array(key => value ...) 形式
 * @param $custom - 0 | 1
 **/
function dshowmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G, $show_message;
	$_G['messageparam'] = func_get_args();
	if(!empty($_G['gp_mobiledata'])) {
		require_once libfile('class/mobiledata');
		$mobiledata = new mobiledata();
		if($mobiledata->validator()) {
			$mobiledata->outputvariables();
		}
	}
	if(empty($_G['inhookscript']) && defined('CURMODULE')) {
		hookscript(CURMODULE, $_G['basescript'], 'messagefuncs', array('param' => $_G['messageparam']));
	}
	$_G['inshowmessage'] = true;

	$param = array(
		/**
		 * 跳轉控制
		 **/
		'header'	=> false,
		/**
		 * header跳轉
		 **/
		'timeout'	=> null,
		/**
		 * 定時跳轉
		 * 自定義跳轉時間
		 **/
		'refreshtime'	=> null,
		/**
		 * 自定義關閉時間，限於 msgtype = 2
		 **/
		'closetime'	=> null,
		/**
		 * 自定義跳轉時間，限於 msgtype = 2
		 **/
		'locationtime'	=> null,
		/**
		 * 內容控制
		 * alert 圖標樣式 right/info/error
		 **/
		'alert'		=> null,
		/**
		 * 顯示請返回
		 **/
		'return'	=> false,
		/**
		 * 下載時用的提示信息，當跳轉時顯示的信息樣式
		 *
		 * 0:如果您的瀏覽器沒有自動跳轉，請點擊此鏈接
		 * 1:如果 n 秒後下載仍未開始，請點擊此鏈接
		 **/
		'redirectmsg'	=> 0,
		/**
		 * 信息樣式
		 *
		 * 1:非 Ajax
		 * 2:Ajax 彈出框
		 * 3:Ajax 只顯示信息文本
		 **/
		'msgtype'	=> 1,
		/**
		 * 顯示信息文本
		 **/
		'showmsg'	=> true,
		/**
		 * 關閉原彈出框顯示 showDialog 信息，限於 msgtype = 2
		 **/
		'showdialog'	=> false,
		/**
		 * 未登錄時顯示登錄鏈接
		 **/
		'login'		=> false,
		/**
		 * Ajax 控制
		 * 執行 js 回調函數
		 **/
		'handle'	=> false,
		/**
		 * 額外顯示的 JS
		 **/
		'extrajs'	=> '',
		/**
		 * 去除 html 標籤，適用於顯示 JS 時
		 **/
		'striptags'	=> true,
	);

	$navtitle = lang('core', 'title_board_message');

	// bluelovers
	// Event: Func_dshowmessage:Before_custom
	if (discuz_core::$plugin_support['Scorpio_Event']) {
		Scorpio_Event::instance('Func_'.__FUNCTION__.':Before_custom')
			->run(array(array(
				'message' => &$message,
				'url_forward' => &$url_forward,
				'values' => &$values,
				'extraparam' => &$extraparam,
				'custom' => &$custom,
				'param' => &$param,
				'navtitle' => &$navtitle,
		)));
	}
	// bluelovers

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template('common/showmessage');
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @unserialize($_G['setting']['msgforward']);
	$handlekey = $leftmsg = '';

	if(defined('IN_MOBILE')) {
		$_G['inajax'] = 0;
		if(!$url_forward && dreferer()) {
			$url_forward = $referer = dreferer();
		}
		if(!empty($url_forward) && strpos($url_forward, 'mobile') === false) {
			$url_forward_arr = explode("#", $url_forward);
			if(strpos($url_forward_arr[0], '?') !== false) {
				$url_forward_arr[0] = $url_forward_arr[0].'&mobile=yes';
			} else {
				$url_forward_arr[0] = $url_forward_arr[0].'?mobile=yes';
			}
			$url_forward = implode("#", $url_forward_arr);
		}
	}


	if(empty($_G['inajax']) && (!empty($_G['gp_quickforward']) || $_G['setting']['msgforward']['quick'] && $_G['setting']['msgforward']['messages'] && @in_array($message, $_G['setting']['msgforward']['messages']))) {
		$param['header'] = true;
	}
	$_G['gp_handlekey'] = !empty($_G['gp_handlekey']) && preg_match('/^\w+$/', $_G['gp_handlekey']) ? $_G['gp_handlekey'] : '';
	if(!empty($_G['inajax'])) {
		$handlekey = $_G['gp_handlekey'] = !empty($_G['gp_handlekey']) ? htmlspecialchars($_G['gp_handlekey']) : '';
		$param['handle'] = true;
	}
	if(!empty($_G['inajax'])) {
		$param['msgtype'] = empty($_G['gp_ajaxmenu']) && (empty($_POST) || !empty($_G['gp_nopost'])) ? 2 : 3;
	}
	// 如果有 $url_forward 則啟用 $param['timeout']
	if($url_forward) {
		$param['timeout'] = true;
		if($param['handle'] && !empty($_G['inajax'])) {
			$param['showmsg'] = false;
		}
	}

	// 將 $extraparam 與 $param 合併
	foreach($extraparam as $k => $v) {
		$param[$k] = $v;
	}
	// 如果存在 $extraparam['set'] 則將 $setdata[$extraparam['set']] 與 $param 合併
	if(array_key_exists('set', $extraparam)) {
		// 定義 $setdata
		$setdata = array('1' => array('msgtype' => 3));
		if($setdata[$extraparam['set']]) {
			foreach($setdata[$extraparam['set']] as $k => $v) {
				$param[$k] = $v;
			}
		}
	}

	// 取得提示信息停留時間(秒)
	$timedefault = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
	if($param['timeout'] !== null) {
		// $refreshsecond 只出現在模板語言 attach_forward
		$refreshsecond = !empty($timedefault) ? $timedefault : 3;
		// 將 $refreshsecond 轉為毫秒
		$refreshtime = $refreshsecond * 1000;
	} else {
		$refreshtime = $refreshsecond = 0;
	}

	// 如果 $param['login'] 但是使用者已經登入或者有 $url_forward 則不顯示登入表單
	if($param['login'] && $_G['uid'] || $url_forward) {
		$param['login'] = false;
	}

	// 決定是否使用 header 轉向
	$param['header'] = $url_forward && $param['header'] ? true : false;

	if($param['header']) {
		/**
		 * 301:
		 * 永久轉移(Permanently Moved)
		 *
		 * 要求的網頁已經永久改變網址。
		 * 此狀態要求用戶端未來在連結此網址時應該導向至指定的 URI
		 *
		 * @example header('HTTP/1.1 301 Moved Permanently');
		 * @example header('Location: '.$url_forward);
		 *
		 * 302:
		 * 暫時轉移(Temporarily Moved)
		 *
		 * 物件已移動，並告知移動過去的網址。
		 * 針對表單架構驗證，這通常表示為「物件已移動」。
		 * 要求的資源暫時存於不同的 URI 底下。
		 * 由於重新導向可能偶而改變，用戶端應繼續使用要求 URI 來執行未來的要求。
		 * 除非以 Cache-Control 或 Expires 標頭欄位表示，此回應才能夠快取
		 *
		 * @example header('Location: '.$url_forward);
		 **/
		header("HTTP/1.1 301 Moved Permanently");
		dheader("location: ".str_replace('&amp;', '&', $url_forward));
	}
	// 如果有 $param['location'] 並且在 $_G['inajax'] 時則直接轉向
	if($param['location'] && !empty($_G['inajax'])) {
		include template('common/header_ajax');
		echo '<script type="text/javascript" reload="1">window.location.href=\''.str_replace("'", "\'", $url_forward).'\';</script>';
		include template('common/footer_ajax');
		dexit();
	}

	// 儲存原始 $message, $values 提供 hook 使用
	$_G['hookscriptmessage'] = $message;
	$_G['hookscriptvalues'] = $values;
	// 如果有 : 則代表為 plugin 訊息
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}
	if($param['msgtype'] == 2 && $param['login']) {
		dheader('location: member.php?mod=logging&action=login&handlekey='.$handlekey.'&infloat=yes&inajax=yes&guestmessage=yes');
	}

	// 處理提供給 JS 顯示的 $show_message 如果有 $param['striptags'] 則去除 HTML 標籤
	$show_jsmessage = str_replace("'", "\\'", $param['striptags'] ? strip_tags($show_message) : $show_message);

	if((!$param['showmsg'] || $param['showid']) && !defined('IN_MOBILE') ) {
		$show_message = '';
	}

	$allowreturn = !$param['timeout'] && !$url_forward && !$param['login'] || $param['return'] ? true : false;
	if($param['alert'] === null) {
		$alerttype = $url_forward ? (preg_match('/\_(succeed|success)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
	} else {
		$alerttype = 'alert_'.$param['alert'];
	}

	// 額外顯示的 JS $extra 與 $param['extrajs'] 不衝突
	$extra = '';
	if($param['showid']) {
		$extra .= 'if($(\''.$param['showid'].'\')) {$(\''.$param['showid'].'\').innerHTML = \''.$show_jsmessage.'\';}';
	}
	if($param['handle']) {
		$valuesjs = $comma = $subjs = '';
		foreach($values as $k => $v) {
			$v = daddslashes($v);
			if(is_array($v)) {
				$subcomma = '';
				foreach ($v as $subk => $subv) {
					$subjs .= $subcomma.'\''.$subk.'\':\''.$subv.'\'';
					$subcomma = ',';
				}
				$valuesjs .= $comma.'\''.$k.'\':{'.$subjs.'}';
			} else {
				$valuesjs .= $comma.'\''.$k.'\':\''.$v.'\'';
			}
			$comma = ',';
		}
		$valuesjs = '{'.$valuesjs.'}';
		if($url_forward) {
			$extra .= 'if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}
	if($param['closetime'] !== null) {
		$param['closetime'] = $param['closetime'] === true ? $timedefault : $param['closetime'];
	}
	if($param['locationtime'] !== null) {
		$param['locationtime'] = $param['locationtime'] === true ? $timedefault : $param['locationtime'];
	}
	if($handlekey) {
		if($param['showdialog']) {
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \'notice\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward.'\'; }' : 'null').', 0, null, null, null, null, '.($param['closetime'] ? $param['closetime'] : 'null').', '.($param['locationtime'] ? $param['locationtime'] : 'null').');';
			$param['closetime'] = null;
			$st = '';
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	} else {
		$st = $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
	}
	if(!$extra && $param['timeout'] && !defined('IN_MOBILE')) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.$st.'</script>' : '';
	$show_message .= $param['extrajs'] ? $param['extrajs'] : '';
	include template('common/showmessage');

	dexit();
}

?>