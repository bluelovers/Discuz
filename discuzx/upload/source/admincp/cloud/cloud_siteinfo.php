<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_siteinfo.php 24570 2011-09-26 09:18:58Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('syncsubmit')) {

	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'", 3000);</script>');
	}

	require_once DISCUZ_ROOT.'/api/manyou/Manyou.php';
	$cloudClient = new Discuz_Cloud_Client();
	if ($_G['setting']['my_app_status']) {
		manyouSync();
	}

	$res = $cloudClient->sync();

	if(!$res) {
		cpmsg('cloud_sync_failure', '', 'error', array('errCode' => $cloudClient->errno, 'errMessage' => $cloudClient->errmsg));
	} else {
		cpmsg('cloud_sync_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=siteinfo\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=siteinfo\'", 3000);</script>');
	}
} elseif(submitcheck('resetsubmit')) {

	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=open\'", 3000);</script>');
	}

	require_once DISCUZ_ROOT.'/api/manyou/Manyou.php';
	$cloudClient = new Discuz_Cloud_Client();

	$res = $cloudClient->resetKey();

	if(!$res) {
		cpmsg($cloudClient->errmsg, '', 'error');
	} else {
		$sId = $res['sId'];
		$sKey = $res['sKey'];

		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
					VALUES ('my_siteid', '$sId'), ('my_sitekey', '$sKey'), ('cloud_status', '1')");
		updatecache('setting');

		cpmsg('cloud_reset_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=siteinfo\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=siteinfo\'", 3000);</script>');
	}
} elseif(submitcheck('ipsubmit')) {

	$_G['gp_cloud_api_ip'] = trim($_G['gp_cloud_api_ip']);
	$_G['gp_my_ip'] = trim($_G['gp_my_ip']);

	if($_G['setting']['cloud_api_ip'] != $_G['gp_cloud_api_ip'] || $_G['setting']['my_ip'] != $_G['gp_my_ip']) {
		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
					VALUES ('cloud_api_ip', '{$_G['gp_cloud_api_ip']}'), ('my_ip', '{$_G['gp_my_ip']}')");
		updatecache('setting');
	}

	$locationUrl = $_G['gp_callback'] == 'doctor' ? ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor' : ADMINSCRIPT.'?frames=yes&action=cloud&operation=siteinfo';

	cpmsg('cloud_ipsetting_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.$locationUrl.'\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.$locationUrl.'\'", 3000);</script>');

} elseif ($_G['gp_anchor'] == 'cloud_ip') {
	ajaxshowheader();
	echo '
		<h3 class="flb" id="fctrl_showblock" style="cursor: move;">
			<em id="return_showblock" fwin="showblock">'.$lang['cloud_api_ip_btn'].'</em>
			<span><a title="'.$lang['close'].'" onclick="hideWindow(\'cloudApiIpWin\');return false;" class="flbc" href="javascript:;">'.$lang['close'].'</a></span>
		</h3>
		';
	echo '<div style="margin: 0 10px; width: 700px;">';
	showformheader('cloud');
	showhiddenfields(array('operation' => $operation));
	if($_G['gp_callback']) {
		showhiddenfields(array('callback' => $_G['gp_callback']));
	}
	showtableheader();
	showsetting('cloud_api_ip', 'cloud_api_ip', $_G['setting']['cloud_api_ip'], 'text');
	showsetting('cloud_manyou_ip', 'my_ip', $_G['setting']['my_ip'], 'text');
	showsubmit('ipsubmit');
	showtablefooter();
	showformfooter();
	echo '</div>';
	ajaxshowfooter();
} else {
	shownav('navcloud', 'menu_cloud_siteinfo');
	showsubmenu('menu_cloud_siteinfo');
	showtips('cloud_siteinfo_tips');
	echo '<script type="text/javascript">var disallowfloat = "";</script>';
	showformheader('cloud');
	showhiddenfields(array('operation' => $operation));
	showtableheader();
	showtitle('menu_cloud_siteinfo');
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_name').'</strong>',
		$_G['setting']['bbname']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_url').'</strong>',
		$_G['siteurl']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_id').'</strong>',
		$_G['setting']['my_siteid']
	));
	showsubmit('syncsubmit', 'cloud_sync', '', '<input type="submit" class="btn" id="submit_resetsubmit" name="resetsubmit" value="'.$lang['cloud_resetkey'].'" />&nbsp; <input type="button" class="btn" onClick="showWindow(\'cloudApiIpWin\', \''.ADMINSCRIPT.'?action=cloud&operation=siteinfo&anchor=cloud_ip\'); return false;" value="'.$lang['cloud_api_ip_btn'].'" />');
	showtablefooter();
	showformfooter();
}

?>