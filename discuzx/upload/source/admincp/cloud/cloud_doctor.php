<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_doctor.php 29038 2012-03-23 06:22:39Z songlixin $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(0);

$op = trim($_G['gp_op']);

if(submitcheck('setidkeysubmit')) {

	$siteId = intval(trim($_G['gp_my_siteid']));
	if($siteId && strcmp($_G['gp_my_siteid'], $siteId) !== 0) {
		cpmsg('cloud_idkeysetting_siteid_failure', '', 'error');
	}

	$_G['gp_my_sitekey'] = trim($_G['gp_my_sitekey']);
	if(empty($_G['gp_my_sitekey'])) {
		$siteKey = '';
	} elseif(strpos($_G['gp_my_sitekey'], '***')) {
		$siteKey = false;
	} elseif(preg_match('/^[0-9a-f]{32}$/', $_G['gp_my_sitekey'])) {
		$siteKey = $_G['gp_my_sitekey'];
	} else {
		cpmsg('cloud_idkeysetting_sitekey_failure', '', 'error');
	}

	if($siteKey === false) {
		$siteKeySQL = '';
	} else {
		$siteKeySQL = "('my_sitekey', '{$siteKey}'), ";
	}

	if($_G['setting']['my_siteid'] != $siteId || $siteKeySQL || $_G['setting']['cloud_status'] != $_G['gp_cloud_status']) {
		$_G['gp_cloud_status'] = intval(trim($_G['gp_cloud_status']));
		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
					VALUES ('my_siteid', '{$siteId}'), $siteKeySQL ('cloud_status', '{$_G['gp_cloud_status']}')");
		updatecache('setting');
	}

	$locationUrl = ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor';

	cpmsg('cloud_idkeysetting_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.$locationUrl.'\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.$locationUrl.'\'", 3000);</script>');

} elseif($op == 'apitest') {

	$APIType = intval($_G['gp_api_type']);
	$APIIP = trim($_G['gp_api_ip']);

	$startTime = cloudGetMicroTime();
	$testStatus = cloudAPIConnectTest($APIType, $APIIP);
	$endTime = cloudGetMicroTime();

	$otherTips = '';
	if($APIIP) {
		if ($_G['gp_api_description']) {
			$otherTips = diconv(trim($_G['gp_api_description']), 'UTF-8');
		}
	} else {
		if($APIType == 1) {
			$otherTips = '<a href="javascript:;" onClick="display(\'cloud_tbody_api_test\')">'.$lang['cloud_doctor_api_test_other'].'</a>';
		} elseif($APIType == 2) {
			$otherTips = '<a href="javascript:;" onClick="display(\'cloud_tbody_manyou_test\')">'.$lang['cloud_doctor_manyou_test_other'].'</a>';
		} elseif($APIType == 3) {
			$otherTips = '<a href="javascript:;" onClick="display(\'cloud_tbody_qzone_test\')">'.$lang['cloud_doctor_qzone_test_other'].'</a>';
		}
	}

	ajaxshowheader();
	if($testStatus) {
		printf($lang['cloud_doctor_api_test_success'], $lang['cloud_doctor_result_success'], $APIIP, $endTime - $startTime, $otherTips);
	} else {
		printf($lang['cloud_doctor_api_test_failure'], $lang['cloud_doctor_result_failure'], $APIIP, $otherTips);
	}
	ajaxshowfooter();

} elseif($op == 'setidkey') {

	ajaxshowheader();
	echo '
		<h3 class="flb" id="fctrl_showblock" style="cursor: move;">
			<em id="return_showblock" fwin="showblock">'.$lang['cloud_doctor_setidkey'].'</em>
			<span><a title="'.$lang['close'].'" onclick="hideWindow(\'cloudApiIpWin\');return false;" class="flbc" href="javascript:;">'.$lang['close'].'</a></span>
		</h3>
		';
	echo '<div style="margin: 0 10px; width: 700px;">';
	showtips('cloud_doctor_setidkey_tips');
	showformheader('cloud');
	showhiddenfields(array('operation' => $operation));
	showhiddenfields(array('op' => $op));
	showtableheader();
	showsetting('cloud_site_id', 'my_siteid', $_G['setting']['my_siteid'], 'text');
	showsetting('cloud_site_key', 'my_sitekey', preg_replace('/(\w{2})\w*(\w{2})/', '\\1****\\2', $_G['setting']['my_sitekey']), 'text');
	showsetting('cloud_site_status', array('cloud_status', array(array('0', $lang['cloud_doctor_status_0']), array('1', $lang['cloud_doctor_status_1']), array('2', $lang['cloud_doctor_status_2']))), $_G['setting']['cloud_status'], 'select');
	showsubmit('setidkeysubmit');
	showtablefooter();
	showformfooter();
	echo '</div>';
	ajaxshowfooter();

} else {

	require_once DISCUZ_ROOT.'./source/discuz_version.php';

	shownav('navcloud', 'menu_cloud_doctor');
	showsubmenu('menu_cloud_doctor');
	showtips('cloud_doctor_tips');
	echo '<script type="text/javascript">var disallowfloat = "";</script>';

	showtableheader();

	showtagheader('tbody', '', true);
	showtitle('cloud_doctor_title_status');
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_url').'</strong>',
		$_G['siteurl']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_id').'</strong>',
		$_G['setting']['my_siteid']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_key').'</strong>',
		preg_replace('/(\w{2})\w*(\w{2})/', '\\1****\\2', $_G['setting']['my_sitekey']).' '.$lang['cloud_site_key_safetips']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_status').'</strong>',
		cloudStatusResult().' <a href="javascript:;" onClick="showWindow(\'cloudApiIpWin\', \''.ADMINSCRIPT.'?action=cloud&operation=doctor&op=setidkey\'); return false;">'.$lang['cloud_doctor_modify_siteidkey'].'</a>'
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('setting_basic_bbclosed').'</strong>',
		$_G['setting']['bbclosed'] ? $lang['cloud_doctor_close_yes'] : $lang['no']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_version').'</strong>',
		DISCUZ_VERSION.' '.DISCUZ_RELEASE
	));
	showtagfooter('tbody');

	showtagheader('tbody', '', true);
	showtitle('cloud_doctor_title_result');

	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_gethostbyname_function').'</strong>',
		function_exists('gethostbyname') ? $lang['cloud_doctor_result_success'].' '.$lang['available'] : $lang['cloud_doctor_result_failure'].$lang['cloud_doctor_function_disable']
	));

	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_api').'</strong>',
		cloudDNSCheckResult(1)
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_api_test').'</strong>',
		cloudGetAPIConnectJS(1)
	));
	showtagfooter('tbody');

	showtagheader('tbody', 'cloud_tbody_api_test', false);
	showtagfooter('tbody');

	showtagheader('tbody', '', true);
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_manyou').'</strong>',
		cloudDNSCheckResult(2)
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_manyou_test').'</strong>',
		cloudGetAPIConnectJS(2)
	));
	showtagfooter('tbody');

	showtagheader('tbody', 'cloud_tbody_manyou_test', false);
	showtagfooter('tbody');

	showtagheader('tbody', '', true);
	showtitle('cloud_doctor_title_plugin');
	cloudShowPlugin();
	showtagfooter('tbody');

	if(getcloudappstatus('connect')) {
		showtagheader('tbody', '', true);
		showtitle('cloud_doctor_title_connect');
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_doctor_connect_app_id').'</strong>',
			!empty($_G['setting']['connectappid']) ? $_G['setting']['connectappid'] : $lang['cloud_doctor_connect_reopen']
		));
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_doctor_connect_app_key').'</strong>',
			!empty($_G['setting']['connectappkey']) ? preg_replace('/(\w{2})\w*(\w{2})/', '\\1****\\2', $_G['setting']['connectappkey']).' '.$lang['cloud_site_key_safetips'] : $lang['cloud_doctor_connect_reopen']
		));
		showtagfooter('tbody');
	}

	showtablefooter();
	showGetCloudAPIIPJS();

}

function cloudShowPlugin() {
	$plugins = array();
	$query = DB::query("SELECT pluginid, available, name, identifier, modules, version FROM ".DB::table('common_plugin')." WHERE identifier IN ('qqconnect', 'cloudstat', 'soso_smilies', 'cloudsearch', 'security', 'xf_storage')");
	while($plugin = DB::fetch($query)) {
		$plugins[$plugin['identifier']] = $plugin;
	}

	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_system_plugin_status').'</strong>',
		count($plugins) >= 6 ? cplang('cloud_doctor_result_success').' '.cplang('available').' '.cplang('cloud_doctor_system_plugin_list') : cplang('cloud_doctor_result_failure').cplang('cloud_doctor_system_plugin_status_false')
	));
	foreach($plugins as $plugin) {
		$moduleStatus = cplang('cloud_doctor_plugin_module_error');
		$plugin['modules'] = @unserialize($plugin['modules']);
		if(is_array($plugin['modules']) && $plugin['modules']) {
			$moduleStatus = '';
		}

		showtablerow('', array('class="td24"'), array(
			'<strong>'.$plugin['name'].'</strong>',
			cplang('version').' '.$plugin['version'].' '.$moduleStatus
		));
	}
}

function cloudDNSCheckResult($type = 1) {
	global $_G;
	switch ($type) {
		case 1:
			$setIP = ($_G['setting']['cloud_api_ip'] ? cplang('cloud_doctor_setting_ip').$_G['setting']['cloud_api_ip'] : '');
			$host = 'api.discuz.qq.com';
			break;
		case 2:
			$setIP = ($_G['setting']['my_ip'] ? cplang('cloud_doctor_setting_ip').$_G['setting']['my_ip'] : '');
			$host = 'api.manyou.com';
			break;
		case 3:
			$setIP = ($_G['setting']['connect_api_ip'] ? cplang('cloud_doctor_setting_ip').$_G['setting']['connect_api_ip'] : '');
			$host = 'openapi.qzone.qq.com';
			break;
	}
	$ip = cloudDNSCheck($host);
	if ($ip) {
		return sprintf(cplang('cloud_doctor_dns_success'), $host, $ip, $setIP, ADMINSCRIPT);
	} else {
		return sprintf(cplang('cloud_doctor_dns_failure'), $host, $setIP, ADMINSCRIPT);
	}
}

function cloudDNSCheck($url) {
	if (!$url) {
		return false;
	}
	$matches = parse_url($url);
	$host = $matches['host'] ? $matches['host'] : $matches['path'];
	if (!$host) {
		return false;
	}
	$ip = gethostbyname($host);
	if ($ip == $host) {
		return false;
	} else {
		return $ip;
	}
}

function cloudStatusResult() {
	global $_G;
	if (empty($_G['setting']['cloud_status'])) {
		return cplang('cloud_doctor_status_0');
	} elseif ($_G['setting']['cloud_status'] == 1) {
		return cplang('cloud_doctor_status_1');
	} elseif ($_G['setting']['cloud_status'] == 2) {
		return cplang('cloud_doctor_status_2');
	}
}

function cloudAPIConnectTest($type = 1, $ip = '') {
	global $_G;

	if($type == 1) {
		$url = 'http://api.discuz.qq.com/site.php';
		$result = dfsockopen($url, 0, '', '', false, $ip ? $ip : $_G['setting']['cloud_api_ip'], 5);
	} elseif($type == 2) {
		$url = 'http://api.manyou.com/uchome.php';
		$result = dfsockopen($url, 0, 'action=siteRefresh', '', false, $ip ? $ip : $_G['setting']['my_ip'], 5);
	} elseif($type == 3) {
		$url = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token';
		$result = dfsockopen($url, 0, '', '', false, $ip ? $ip : $_G['setting']['connect_api_ip'], 5);
		if($result) {
			return true;
		}
	}

	$result = trim($result);

	if(!$result) {
		return false;
	}

	$result = @unserialize($result);
	if(!$result) {
		return false;
	}
	return true;
}

function cloudGetMicroTime() {
	list($usec, $sec) = explode(' ', microtime());
	return (floatval($usec) + floatval($sec));
}

function cloudGetAPIConnectJS($type = 1, $ip = '') {
	$html = sprintf('<div id="_doctor_apitest_%1$s_%2$s"></div><script type="text/javascript">ajaxget("%3$s?action=cloud&operation=doctor&op=apitest&api_type=%1$s&api_ip=%2$s", "_doctor_apitest_%1$s_%2$s");</script>', $type, $ip, ADMINSCRIPT);
	return $html;
}

function cloudSeparatorOutputCheck() {
	if(!function_exists('ini_get')) {
		return false;
	}
	$separatorOutput = @ini_get('arg_separator.output');
	if(empty($separatorOutput) || $separatorOutput == '&') {
		return true;
	}
	return false;
}

function showGetCloudAPIIPJS() {

	echo
<<<EOT
<script type="text/javascript" src="static/image/admincp/cloud/cloud.js"></script>
<script type="text/javascript" src="http://cp.discuz.qq.com/cloud/apiIp" charset="utf-8"></script>
EOT;
}

?>