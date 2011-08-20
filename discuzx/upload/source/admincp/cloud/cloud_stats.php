<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_stats.php 23929 2011-08-17 02:33:35Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$_G['gp_anchor'] = in_array($_G['gp_anchor'], array('base', 'summary')) ? $_G['gp_anchor'] : 'summary';
$current = array($_G['gp_anchor'] => 1);

$statsnav = array();
$statsnav[0] = array('cloud_stats_summary', 'cloud&operation=stats&anchor=summary', $current['summary']);
$statsnav[1] = array('cloud_stats_setting', 'cloud&operation=stats&anchor=base', $current['base']);

if(!$_G['inajax']) {
	cpheader();
}

if($_G['gp_anchor'] == 'base') {

	if(!submitcheck('settingsubmit')) {

		shownav('navcloud', 'cloud_stats');
		showsubmenu('cloud_stats', $statsnav);

		showtips('cloud_stats_tips');
		showformheader('cloud&edit=yes');
		showhiddenfields(array('operation' => $operation));
		showtableheader();

		$myicon = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey = 'cloud_staticon'");
		if ($myicon === false) {
			$myicon = 9;
		}

		$checkicon[$myicon] = ' checked';
		$icons = '<table style="margin-bottom: 3px; margin-top:3px;"><tr><td>';
		for($i=1;$i<=11;$i++) {
			if ($i < 9) {
				$icons .= '<input class="radio" type="radio" id="stat_icon_'.$i.'" name="settingnew[cloud_staticon]" value="'.$i.'"'.$checkicon[$i].' /><label for="stat_icon_'.$i.'">&nbsp;<img src="http://tcss.qq.com/icon/toss_'.$i.'.gif" /></label>&nbsp;&nbsp;';
				if ($i % 4 == 0) {
					$icons .= '</td></tr><tr><td>';
				}
			} elseif ($i < 11) {
				$icons .= '<input class="radio" type="radio" id="stat_icon_'.$i.'" name="settingnew[cloud_staticon]" value="'.$i.'"'.$checkicon[$i].' /><label for="stat_icon_'.$i.'">&nbsp;'.$lang['cloud_stats_icon_word'.$i].'</label>&nbsp;&nbsp;';
			} else {
				$icons .= '</td></tr><tr><td><input class="radio" type="radio" id="stat_icon_'.$i.'" name="settingnew[cloud_staticon]" value="0"'.$checkicon[0].' /><label for="stat_icon_'.$i.'">&nbsp;'.$lang['cloud_stats_icon_none'].'</label></td></tr>';
			}
		}
		$icons .= '</table>';
		showsetting('cloud_stats_icon_set', '', '', $icons);

		showsubmit('settingsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$settingnew = $_G['gp_settingnew'];
		$settingnew['cloud_staticon'] = intval($settingnew['cloud_staticon']);

		DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ('cloud_staticon', '$settingnew[cloud_staticon]')");
		updatecache('setting');

		cpmsg('setting_update_succeed', 'action=cloud&operation='.$operation.(!empty($_G['gp_anchor']) ? '&anchor='.$_G['gp_anchor'] : ''), 'succeed');
	}

} elseif($_G['gp_anchor'] == 'summary') {

	shownav('navcloud', 'cloud_stats');
	showsubmenu('cloud_stats', $statsnav);

	$statsDomain = 'http://stats.discuz.qq.com';
	$signUrl = generateSiteSignUrl(array('v' => 2));

	headerLocation($statsDomain.'/statsSummary/?'.$signUrl);
}

?>