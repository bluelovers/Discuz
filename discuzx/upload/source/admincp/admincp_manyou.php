<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_manyou.php 15870 2010-08-27 08:28:07Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if($_G['gp_anchor'] == 'base') {

	showtips('setting_manyou_tips');

	showtableheader('', 'nobottom', 'id="base"');
	$actives = $checkarr = array();
	$actives[$setting['my_app_status']] = ' class="checked"';
	$checkarr[$setting['my_app_status']] = ' checked';

	$str = <<<EOF
	<ul onmouseover="altStyle(this);">
		<li$actives[1]><input type="radio" onclick="hiddenShareInfo(0, 1);$('hidden_setting_manyou_base_status').style.display = '';" $checkarr[1] value="1" name="settingnew[my_app_status]" class="radio">&nbsp;$lang[yes]</li>
		<li$actives[0]><input type="radio" onclick="hiddenShareInfo(0, 0);$('hidden_setting_manyou_base_status').style.display = 'none';" $checkarr[0] value="0" name="settingnew[my_app_status]" class="radio">&nbsp;$lang[no]</li>
	</ul>
EOF;
	showsetting('setting_manyou_base_status', 'settingnew[my_app_status]', $setting['my_app_status'], $str, '', 1);
	showsetting('setting_manyou_base_close_prompt', 'settingnew[my_closecheckupdate]', $setting['my_closecheckupdate'], 'radio');
	showtagfooter('tbody');

	$setting['my_search_status'] = intval($setting['my_search_status']);
	$haveinvitecode = !empty($setting['my_search_invite']) ? 1 : 0;
	$searchstate = !empty($setting['my_search_status']) ? 1 : 0;
	$appstate = !empty($setting['my_app_status']) ? 1 : 0;
	$actives = $checkarr = array();
	$actives[$setting['my_search_status']] = ' class="checked"';
	$checkarr[$setting['my_search_status']] = ' checked';

	$str = <<<EOF
	<script type="text/javascript">
		var haveinvitecode = $haveinvitecode;
		var searchState = $searchstate;
		var appState = $appstate;
		function checkInviteCode(value) {
			if(value) {
				if($setting[my_search_status]==0) {
					var x = new Ajax();
					x.get('misc.php?mod=manyou&action=inviteCode&inajax=1', function(s){
						if(parseInt(s)) {
							$('hidden_setting_manyou_search_status').style.display = '';
						}
					});
				} else if(haveinvitecode) {
					$('hidden_setting_manyou_search_status').style.display = '';
				}
			}
		}
		function hiddenShareInfo(type, state) {
			if(type) {
				searchState = state ? 1 : 0;
			} else {
				appState = state ? 1 : 0;
			}
			$('shareinfo').style.display = searchState || appState ? '' : 'none';
		}
	</script>
	<ul onmouseover="altStyle(this);">
		<li$actives[1]><input type="radio" onclick="hiddenShareInfo(1, 1);checkInviteCode(this.value);" $checkarr[1] value="1" name="settingnew[my_search_status]" class="radio">&nbsp;$lang[yes]</li>
		<li$actives[0]><input type="radio" onclick="hiddenShareInfo(1, 0);$('hidden_setting_manyou_search_status').style.display = 'none';" $checkarr[0] value="0" name="settingnew[my_search_status]" class="radio">&nbsp;$lang[no]</li>
	</ul>
EOF;
	showsetting('setting_manyou_search_status', 'settingnew[my_search_status]', $setting['my_search_status'] && $haveinvitecode, $str, '', 1);
	showsetting('setting_manyou_search_invite', 'settingnew[my_search_invite]', $setting['my_search_invite'], 'text', $setting['my_search_status'] && $haveinvitecode ? 'readonly' : '');
	showtagfooter('tbody');

	showtagheader('tbody', 'shareinfo', $setting['my_app_status'] || $setting['my_search_status']);
	showsetting('setting_manyou_base_ip', 'settingnew[my_ip]', $setting['my_ip'], 'text');
	showsetting('setting_manyou_base_refresh', 'settingnew[my_refresh]', '0', 'radio');
	showtagfooter('tbody');
	showtablefooter();

} elseif($_G['setting']['my_app_status'] || $_G['setting']['my_search_status']) {


	$uchUrl = $_G['siteurl'].'/'.ADMINSCRIPT.'?action=setting&operation=manyou&anchor=' . $_G['gp_anchor'];

	if ($_G['gp_anchor'] == 'manage') {
		if(empty($_GET['my_suffix'])) {
			$_GET['my_suffix'] = '/appadmin/list';
		}
		$my_prefix = 'http://uchome.manyou.com';
		$my_suffix = urlencode($_GET['my_suffix']);
		$tmp_suffix = $_GET['my_suffix']?urldecode($_GET['my_suffix']):'/appadmin/list';
		$myUrl = $my_prefix.$tmp_suffix;

		$timestamp = time();
		$hash = md5($_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$_G['setting']['my_sitekey'].'|'.$timestamp);

		$delimiter = strrpos($myUrl, '?') ? '&' : '?';

		$url = $myUrl.$delimiter.'s_id='.$_G['setting']['my_siteid'].'&uch_id='.$_G['uid'].'&uch_url='.urlencode($uchUrl).'&my_suffix='.$my_suffix.'&timestamp='.$timestamp.'&my_sign='.$hash;

	} elseif ($_G['gp_anchor'] == 'search') {
		if(empty($_GET['my_suffix'])) {
			$_GET['my_suffix'] = '/admin/view';
		}
		$my_prefix = 'http://search.manyou.com';
		$my_suffix = urlencode($_GET['my_suffix']);
		$tmp_suffix = $_GET['my_suffix']?urldecode($_GET['my_suffix']):'/admin/view';
		$myUrl = $my_prefix.$tmp_suffix;

		$timestamp = time();
		$hash = md5($_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$_G['setting']['my_sitekey'].'|'.$timestamp);

		$delimiter = strrpos($myUrl, '?') ? '&' : '?';

		$url = $myUrl.$delimiter.'s_id='.$_G['setting']['my_siteid'].'&dz_id='.$_G['uid'].'&dz_url='.urlencode($uchUrl).'&my_suffix='.$my_suffix.'&timestamp='.$timestamp.'&my_sign='.$hash;
	}
	print <<<EOF
	<script type="text/javascript" src="http://static.manyou.com/scripts/my_iframe.js"></script>
	<script language="javascript">
	var prefixURL = "$my_prefix";
	var suffixURL = "$my_suffix";
	var queryString = '';
	var url = "{$url}";
	var oldHash = null;
	var timer = null;
	var server = new MyXD.Server("ifm0");
	server.registHandler('iframeHasLoaded');
	server.registHandler('setTitle');
	server.start();
	function iframeHasLoaded(ifm_id) {
		MyXD.Util.showIframe(ifm_id);
		document.getElementById('loading').style.display = 'none';
	}
	function setTitle(x) {
		document.title = x;
	}
	</script>

	<div id="loading" style="display:block; padding:100px 0 100px 0;text-align:center;color:#999999;font-size:12px;">
	<img src="static/image/common/loading.gif" alt="loading..." align="absmiddle" />  {$lang['loading']}...
	</div>
	<div style="margin-top:8px;">
	<center>
		<iframe id="ifm0" frameborder="0" width="810px" scrolling="no" height="810px" style="position:absolute; top:-5000px; left:-5000px;" src="{$url}"></iframe>
	</center>
	</div>
	</body></html>
EOF;
	exit();

} else {
	cpmsg('my_app_status_off', 'action=setting&operation=manyou&anchor=base', 'error');
}

?>