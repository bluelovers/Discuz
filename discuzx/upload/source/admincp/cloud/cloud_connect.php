<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_connect.php 24520 2011-09-23 02:08:15Z yangli $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/connect');

$op = $_G['gp_op'];

$signUrl = generateSiteSignUrl();

$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('setting', 'service')) ? $_G['gp_anchor'] : 'setting';
$current = array($_G['gp_anchor'] => 1);

$connectnav = array();

$connectnav[0] = array('connect_menu_setting', 'cloud&operation=connect&anchor=setting', $current['setting']);
$connectnav[1] = array('connect_menu_service', 'cloud&operation=connect&anchor=service', $current['service']);

if (!$_G['inajax']) {
	cpheader();
}

if($_G['gp_anchor'] == 'service') {
	headerLocation($cloudDomain.'/connect/service/?'.$signUrl);

} elseif ($_G['gp_anchor'] == 'setting') {
	$query = DB::query("SELECT * FROM ".DB::table('common_setting')." WHERE skey IN ('extcredits', 'connect', 'connectsiteid', 'connectsitekey', 'regconnect')");
	while($row = DB::fetch($query)) {
		$setting[$row['skey']] = $row['svalue'];
	}
	$setting['connect'] = (array)unserialize($setting['connect']);

	$params = array(
		's_id' => $setting['connectsiteid'],
		'response_type' => 'php'
	);
	$params['sig'] = connect_get_sig($params, $setting['connectsiteid'].'|'.$setting['connectsitekey']);
	$staturl = $setting['connectsiteid'] ? 'http://connect.manyou.com/site/stats/index?'.http_build_query($params) : '';

	if(!submitcheck('connectsubmit')) {

		shownav('navcloud', 'menu_setting_qqconnect');

		include_once libfile('function/forumlist');
		$forumselect = array();
		foreach(array('feed', 't') as $k) {
			$forumselect[$k] = '<select name="connectnew['.$k.'][fids][]" multiple="multiple" size="10">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
			if($setting['connect'][$k]['fids']) {
				foreach($setting['connect'][$k]['fids'] as $v) {
					$forumselect[$k] = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $forumselect[$k]);
				}
			}
		}

		$connectrewardcredits = $connectgroup = '';
		$setting['extcredits'] = unserialize($setting['extcredits']);
		for($i = 0; $i <= 8; $i++) {
			if($setting['extcredits'][$i]['available']) {
				$extcredit = 'extcredits'.$i.' ('.$setting['extcredits'][$i]['title'].')';
				$connectrewardcredits .= '<option value="'.$i.'" '.($i == intval($setting['connect']['register_rewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			}
		}

		$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." WHERE type='special'");
		while($group = DB::fetch($query)) {
			$connectgroup .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['connect']['register_groupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		showformheader('cloud&operation=connect');
		showtableheader();
		showsetting('connect_setting_allow', 'connectnew[allow]', $setting['connect']['allow'], 'radio', 0, 1);
		showsetting('setting_access_register_connect_birthday', 'connectnew[register_birthday]', $setting['connect']['register_birthday'], 'radio');
		showsetting('setting_access_register_connect_gender', 'connectnew[register_gender]', $setting['connect']['register_gender'], 'radio');
		showsetting('setting_access_register_connect_uinlimit', 'connectnew[register_uinlimit]', $setting['connect']['register_uinlimit'], 'text');
		showsetting('setting_access_register_connect_credit', '', '', '<select name="connectnew[register_rewardcredit]">'.$connectrewardcredits.'</select>');
		showsetting('setting_access_register_connect_addcredit', 'connectnew[register_addcredit]', $setting['connect']['register_addcredit'], 'text');
		showsetting('setting_access_register_connect_group', '', '', '<select name="connectnew[register_groupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$connectgroup.'</select>');
		showsetting('setting_access_register_connect_regverify', 'connectnew[register_regverify]', $setting['connect']['register_regverify'], 'radio');
		showsetting('setting_access_register_connect_invite', 'connectnew[register_invite]', $setting['connect']['register_invite'], 'radio');
		showsetting('setting_access_register_connect_newbiespan', 'connectnew[newbiespan]', $setting['connect']['newbiespan'], 'text');
		showtagfooter('tbody');
		showsetting('connect_setting_feed_allow', 'connectnew[feed][allow]', $setting['connect']['feed']['allow'], 'radio', 0, 1);
		showsetting('connect_setting_feed_fids', '', '', $forumselect['feed']);
		showsetting('connect_setting_feed_group', 'connectnew[feed][group]', $setting['connect']['feed']['group'], 'radio');
		showtagfooter('tbody');
		showsubmenu('menu_cloud_connect', $connectnav);
		showsetting('connect_setting_t_allow', 'connectnew[t][allow]', $setting['connect']['t']['allow'], 'radio', 0, 1);
		showsetting('connect_setting_t_fids', '', '', $forumselect['t']);
		showsetting('connect_setting_t_group', 'connectnew[t][group]', $setting['connect']['t']['group'], 'radio');
		showtagfooter('tbody');
		showsetting('connect_setting_like_allow', 'connectnew[like_allow]', $setting['connect']['like_allow'], 'radio', 0, 1);
		showsetting('connect_setting_like_url', 'connectnew[like_qq]', $setting['connect']['like_qq'], 'text');
		showtagfooter('tbody');
		showsetting('connect_setting_turl_allow', 'connectnew[turl_allow]', $setting['connect']['turl_allow'], 'radio', 0, 1);
		showsetting('connect_setting_turl_qq', 'connectnew[turl_qq]', $setting['connect']['turl_qq'], 'text');
		showtagfooter('tbody');
		showsetting('connect_setting_qshare_allow', 'connectnew[qshare_allow]', $setting['connect']['qshare_allow'], 'radio', 0, 1);
		showsetting('connect_setting_qshare_appkey', 'connectnew[qshare_appkey]', $setting['connect']['qshare_appkey'], 'text');
		showtagfooter('tbody');
		showsubmit('connectsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_connectnew']['turl_qq'] && !is_numeric($_G['gp_connectnew']['turl_qq'])) {
			cpmsg('connect_setting_turl_qq_failed', '', 'error');
		}

		if($_G['gp_connectnew']['like_url']) {
			$url = parse_url($_G['gp_connectnew']['like_url']);
			if(!preg_match('/\.qq\.com$/i', $url['host'])) {
				cpmsg('connect_like_url_error', '', 'error');
			}
		}
		if($_G['gp_connectnew']['like_allow'] && $_G['gp_connectnew']['like_url'] === '') {
			cpmsg('connect_like_url_miss', '', 'error');
		}
		$_G['gp_connectnew'] = array_merge($setting['connect'], $_G['gp_connectnew']);
		$_G['gp_connectnew']['like_url'] = $_G['gp_connectnew']['like_qq'] ? 'http://open.qzone.qq.com/like?url=http%3A%2F%2Fuser.qzone.qq.com%2F'.$_G['gp_connectnew']['like_qq'].'&width=100&height=21&type=button_num' : '';
		$_G['gp_connectnew']['turl_code'] = '';
		$connectnew = addslashes(serialize(dstripslashes($_G['gp_connectnew'])));
		$regconnectnew = !$setting['connect']['allow'] && $_G['gp_connectnew']['allow'] ? 1 : $setting['regconnect'];
		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES
			('regconnect', '$regconnectnew'),
			('connect', '$connectnew')");

		require_once(DISCUZ_ROOT.'./api/manyou/Manyou.php');
		$client = new Discuz_Cloud_Client();
		$res = $client->connectSync($_G['gp_connectnew']['like_qq'], $_G['gp_connectnew']['turl_qq']);
		if($client->errno) {
			$res = array('status' => false, 'msg' => cplang('qqgroup_msg_remote_exception', array('errmsg' => $client->errmsg, 'errno' => $client->errno)));
		} elseif(!is_array($res)) {
			$res = array('status' => false, 'msg' => 'qqgroup_msg_remote_error');
		}
		if($res['msg']) {
			cpmsg($res['msg'], '', 'error');
		}
		if($res['mblogCode']) {
			$_G['gp_connectnew']['turl_code'] = $res['mblogCode'];
			$connectnew = addslashes(serialize(dstripslashes($_G['gp_connectnew'])));
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ('connect', '$connectnew')");
		}

		updatecache(array('setting', 'fields_register', 'fields_connect_register'));
		cpmsg('connect_update_succeed', 'action=cloud&operation=connect', 'succeed');

	}
}

?>