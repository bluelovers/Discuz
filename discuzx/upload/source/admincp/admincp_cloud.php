<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: admincp_cloud.php 29038 2012-03-23 06:22:39Z songlixin $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(600);
cpheader();

require_once libfile('function/cloud');

if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
	exit('Access Denied');
}

$adminscript = ADMINSCRIPT;

$cloudDomain = 'http://cp.discuz.qq.com';
if($operation == 'doctor' || $operation == 'siteinfo') {
	$cloudstatus = checkcloudstatus(false);
} else {
	$cloudstatus = checkcloudstatus();
}
$forceOpen = $_GET['force_open'] == 1 ? true : false;

if(!$operation || $operation == 'open') {

	if($cloudstatus == 'cloud' && !$forceOpen) {
		cpmsg('cloud_turnto_applist', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=applist\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=applist\'", 3000);</script>');
	} else {
		if ($_GET['getConfirmInfo']) {
			ajaxshowheader();
			ajaxshowfooter();
		}

		$step = max(1, intval($_G['gp_step']));
		$type = $cloudstatus == 'upgrade' ? 'upgrade' : 'open';

		if($step == 1) {

			cloud_init_uniqueid();

			if($cloudstatus == 'upgrade' || ($cloudstatus == 'cloud' &&  $forceOpen)) {
				shownav('navcloud', 'menu_cloud_upgrade');
				$itemtitle = cplang('menu_cloud_upgrade');
			} else {
				shownav('navcloud', 'menu_cloud_open');
				$itemtitle = cplang('menu_cloud_open');
			}

			echo '
				<div class="itemtitle">
				<h3>'.$itemtitle.'</h3>
				<ul style="margin-right: 10px;" class="tab1"></ul>
				<ul class="stepstat" id="nav_steps"></ul>
				<ul class="tab1"></ul>
				</div>

				<div id="loading">
				<div id="loadinginner" style="display: block; padding: 100px 0; text-align: center; color: #999;">
				<img src="'.$_G['style']['imgdir'].'/loading.gif" alt="loading..." style="vertical-align: middle;" /> '.$lang['cloud_page_loading'].'
				</div>
				</div>
				<div style="display:none;" id="title"></div>';

			showformheader('', 'onsubmit="return submitForm();"');

			if($cloudstatus == 'upgrade' || ($cloudstatus == 'cloud' &&  $forceOpen)) {
				echo '<div style="margin-top:10px; color: red; padding-left: 10px;" id="manyou_update_tips"></div>';
			}

			showtableheader('', '', 'id="mainArea" style="display:none;"');

			echo '
				<tr><td id="" style="border:none;"><div id="msg" class="tipsblock"></div></td></tr>
				<tr><td style="border-top:none;"><br />
				<label><input onclick="if(this.checked) {$(\'submit_submit\').disabled=false; $(\'submit_submit\').style.color=\'#000\';} else {$(\'submit_submit\').disabled=true; $(\'submit_submit\').style.color=\'#aaa\';}" id="agreeProtocal" class="checkbox" type="checkbox" checked="checked" value="1" />' . cplang('cloud_agree_protocal') . '</label><a id="protocal_url" href="javascript:;" target="_blank">' . cplang('read_protocal') . '</a>
				</td>
				</tr>';

			showsubmit('submit', 'cloud_will_open');
			showtablefooter();
			showformfooter();

			echo '
				<div id="siteInfo" style="display:none;;">
				<h3 class="flb"><em>'.cplang('message_title').'</em><span><a href="javascript:;" class="flbc" onclick="hideWindow(\'open_cloud\');" title="'.cplang('close').'">'.cplang('close').'</a></span></h3>';

			showformheader('cloud&operation=open&step=2'.(($cloudstatus == 'cloud' && $forceOpen) ? '&force_open=1' : ''), '');

			echo '
				<div class="c">
				<div class="tplw">
				<p class="mbn tahfx">
				<strong>'.cplang('jump_to_cloud').'</strong><input type="hidden" id="cloud_api_ip" name="cloud_api_ip" value="" />
				</p>
				</div>
				</div>

				<div class="o pns"><button type="submit" class="pn pnc" id="btn_1"><span>'.cplang('continue').'</span></button></div>';

			showformfooter();
			echo "</div>";

			echo <<<EOT
<link rel="stylesheet" type="text/css" href="static/image/admincp/cloud/cloud.css" />
<script type="text/javascript" src="static/image/admincp/cloud/cloud.js"></script>
<script type="text/JavaScript">
var cloudStatus = "$cloudstatus";
var disallowfloat = 'siteInfo';
var cloudApiIp = '';
var dialogHtml = '';
var getMsg = false;

var millisec = 10 * 1000; //10秒
var expirationText = '{$lang['cloud_time_out']}';
expirationTimeout = setTimeout("expiration()", millisec);
</script>
EOT;
			$introUrl = $cloudDomain.'/cloud/introduction';
			if($cloudstatus == 'upgrade') {
				$params = array('type' => 'upgrade');

				if ($_G['setting']['my_app_status']) {
					$params['apps']['manyou'] = array('status' => true);
				}

				if (isset($_G['setting']['my_search_status'])) {

					$params['apps']['search'] = array('status' => !empty($_G['setting']['my_search_status']) ? true : false);

					$oldSiteId = empty($_G['setting']['my_siteid_old'])?'':$_G['setting']['my_siteid_old'];
					$oldSitekeySign = empty($_G['setting']['my_sitekey_sign_old'])?'':$_G['setting']['my_sitekey_sign_old'];

					if($oldSiteId && $oldSiteId != $_G['setting']['my_siteid'] && $oldSitekeySign) {
						$params['apps']['search']['oldSiteId'] = $oldSiteId;
						$params['apps']['search']['searchSig'] = $oldSitekeySign;
					}

				}

				if (isset($_G['setting']['connect'])) {
					$params['apps']['connect'] = array('status' => !empty($_G['setting']['connect']['allow']) ? true : false);

					$oldSiteId = empty($_G['setting']['connectsiteid'])?'':$_G['setting']['connectsiteid'];
					$oldSitekey = empty($_G['setting']['connectsitekey'])?'':$_G['setting']['connectsitekey'];

					if($oldSiteId && $oldSiteId != $_G['setting']['my_siteid'] && $oldSitekey) {
						$params['apps']['connect']['oldSiteId'] = $oldSiteId;
						$params['apps']['connect']['connectSig'] = substr(md5(substr(md5($oldSiteId.'|'.$oldSitekey), 0, 16)), 16, 16);
					}
				}

				$params['ADTAG'] = 'CP.DISCUZ.INTRODUCTION';

				$signUrl = generateSiteSignUrl($params);
				$introUrl .= '?'.$signUrl;
			}

			echo '<script type="text/JavaScript" charset="UTF-8" src="'.$introUrl.'"></script>';

		} elseif($step == 2) {

			$statsUrl = $cloudDomain . '/cloud/stats/registerclick';
			echo '<script type="text/JavaScript" charset="UTF-8" src="'.$statsUrl.'"></script>';

			if($_G['setting']['my_siteid'] && $_G['setting']['my_sitekey']) {

				if($_G['setting']['my_app_status']) {
					manyouSync();
				}

				$registerResult = upgrademanyou($_G['gp_cloud_api_ip']);

			} else {
				$registerResult = registercloud($_G['gp_cloud_api_ip']);
			}

			if($registerResult['errCode'] === 0) {
				$bindUrl = $cloudDomain.'/bind/index?'.generateSiteSignUrl(array('ADTAG' => 'CP.CLOUD.BIND.INDEX'));
				die('<script>top.location="' . $bindUrl . '";</script>');
			} elseif($registerResult['errCode'] == 1) {
				cpmsg('cloud_unknown_dns', '', 'error');
			} elseif($registerResult['errCode'] == 2) {
				cpmsg('cloud_network_busy', '', 'error', $registerResult);
			} else {
				$checkUrl = preg_match('/<a.+?>.+?<\/a>/i', $registerResult['errMessage'], $results);
				if($checkUrl) {
					foreach($results as $key => $result) {
						$registerResult['errMessage'] = str_replace($result, '{replace_' . $key . '}', $registerResult['errMessage']);
						$msgValues = array('replace_' . $key => $result);
					}
				}
				cpmsg($registerResult['errMessage'], '', 'error', $msgValues);
			}
		}
	}

} elseif($operation == 'applist') {

	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'", 3000);</script>');
	}

	$signParams = array('refer' => $_G['siteurl'], 'ADTAG' => 'CP.DISCUZ.APPLIST');
	$signUrl = generateSiteSignUrl($signParams);
	headerLocation($cloudDomain.'/cloud/appList/?'.$signUrl);

} elseif(in_array($operation, array('siteinfo', 'doctor'))) {

	require libfile("cloud/$operation", 'admincp');

} elseif(in_array($operation, array('manyou', 'connect', 'security', 'stats', 'search',
									'smilies', 'qqgroup', 'union', 'storage'))) {
	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'", 3000);</script>');
	}

	$apps = getcloudapps();
	if(empty($apps) || empty($apps[$operation]) || $apps[$operation]['status'] == 'close') {
		cpmsg('cloud_application_close', 'action=cloud&operation=applist', 'error');
	}
	if($apps[$operation]['status'] == 'disable') {
		cpmsg('cloud_application_disable', 'action=cloud&operation=applist', 'error');
	}

	require libfile("cloud/$operation", 'admincp');

} else {
	exit('Access Denied');
}

function manyouSync() {
	global $_G;
	$setting = $_G['setting'];
	$my_url = 'http://api.manyou.com/uchome.php';

	$mySiteId = empty($_G['setting']['my_siteid'])?'':$_G['setting']['my_siteid'];
	$siteName = $_G['setting']['bbname'];
	$siteUrl = $_G['siteurl'];
	$ucUrl = rtrim($_G['setting']['ucenterurl'], '/').'/';
	$siteCharset = $_G['charset'];
	$siteTimeZone = $_G['setting']['timeoffset'];
	$mySiteKey = empty($_G['setting']['my_sitekey'])?'':$_G['setting']['my_sitekey'];
	$siteKey = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
	$siteLanguage = $_G['config']['output']['language'];
	$siteVersion = $_G['setting']['version'];
	$myVersion = cloud_get_api_version();
	$productType = 'DISCUZX';
	$siteRealNameEnable = '';
	$siteRealAvatarEnable = '';
	$siteEnableApp = intval($setting['my_app_status']);

	$key = $mySiteId . $siteName . $siteUrl . $ucUrl . $siteCharset . $siteTimeZone . $siteRealNameEnable . $mySiteKey . $siteKey;
	$key = md5($key);
	$siteTimeZone = urlencode($siteTimeZone);
	$siteName = urlencode($siteName);

	$register = false;
	$postString = sprintf('action=%s&productType=%s&key=%s&mySiteId=%d&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteEnableRealName=%s&siteEnableRealAvatar=%s&siteKey=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s&siteEnableApp=%s&from=cloud', 'siteRefresh', $productType, $key, $mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteKey, $siteLanguage, $siteVersion, $myVersion, $siteEnableApp);

	$response = @dfsockopen($my_url, 0, $postString, '', false, $setting['my_ip']);
	$res = unserialize($response);
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}
	if($res['errCode']) {
		cpmsg('cloud_sync_failure', '', 'error', array('errCode'=>$res['errCode'], 'errMessage'=>$res['errMessage']));
	}
}

?>