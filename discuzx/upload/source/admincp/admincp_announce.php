<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_announce.php 15149 2010-08-19 08:02:46Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
echo '<script type="text/javascript" src="static/js/calendar.js"></script>';

if(empty($operation)) {

	if(!submitcheck('announcesubmit')) {

		shownav('tools', 'announce', 'admin');
		showsubmenu('announce', array(
			array('admin', 'announce', 1),
			array('add', 'announce&operation=add', 0)
		));
		showtips('announce_tips');
		showformheader('announce');
		showtableheader();
		showsubtitle(array('del', 'display_order', 'author', 'subject', 'message', 'announce_type', 'start_time', 'end_time', ''));

		$announce_type = array(0=>$lang['announce_words'], 1=>$lang['announce_url']);
		$query = DB::query("SELECT * FROM ".DB::table('forum_announcement')." ORDER BY displayorder, starttime DESC, id DESC");
		while($announce = DB::fetch($query)) {
			$disabled = $_G['adminid'] != 1 && $announce['author'] != $_G['member']['username'] ? 'disabled' : NULL;
			$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'd') : $lang['unlimited'];
			$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'd') : $lang['unlimited'];
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$announce[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" name=\"displayordernew[{$announce[id]}]\" value=\"$announce[displayorder]\" size=\"2\" $disabled>",
				"<a href=\"./home.php?mod=space&username=".rawurlencode($announce['author'])."\" target=\"_blank\">$announce[author]</a>",
				dhtmlspecialchars($announce['subject']),
				cutstr(strip_tags($announce['message']), 20),
				$announce_type[$announce['type']],
				$announce['starttime'],
				$announce['endtime'],
				"<a href=\"".ADMINSCRIPT."?action=announce&operation=edit&announceid=$announce[id]\" $disabled>$lang[edit]</a>"
			));
		}
		showsubmit('announcesubmit', 'submit', 'select_all');
		showtablefooter();
		showformfooter();

	} else {

		if(is_array($_G['gp_delete'])) {
			$ids = $comma = '';
			foreach($_G['gp_delete'] as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			DB::query("DELETE FROM ".DB::table('forum_announcement')." WHERE id IN ($ids) AND ('$_G[adminid]'='1' OR author='$_G[username]')");
		}

		if(is_array($_G['gp_displayordernew'])) {
			foreach($_G['gp_displayordernew'] as $id => $displayorder) {
				DB::query("UPDATE ".DB::table('forum_announcement')." SET displayorder='$displayorder' WHERE id='$id' AND ('$_G[adminid]'='1' OR author='$_G[username]')");
			}
		}

		updatecache(array('announcements', 'announcements_forum'));
		cpmsg('announce_update_succeed', 'action=announce', 'succeed');

	}

} elseif($operation == 'add') {

	if(!submitcheck('addsubmit')) {

		$newstarttime = dgmdate(TIMESTAMP, 'Y-n-j');
		$newendtime = dgmdate(TIMESTAMP + 86400* 7, 'Y-n-j');

		shownav('tools', 'announce', 'add');
		showsubmenu('announce', array(
			array('admin', 'announce', 0),
			array('add', 'announce&operation=add', 1)
		));
		showformheader('announce&operation=add');
		showtableheader('announce_add');
		showsetting($lang[subject], 'newsubject', '', 'text');
		showsetting($lang['start_time'], 'newstarttime', $newstarttime, 'calendar');
		showsetting($lang['end_time'], 'newendtime', $newendtime, 'calendar');
		showsetting('announce_type', array('newtype', array(
			array(0, $lang['announce_words']),
			array(1, $lang['announce_url']))), 0, 'mradio');
		showsetting('announce_message', 'newmessage', '', 'textarea');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newstarttime = $_G['gp_newstarttime'] ? strtotime($_G['gp_newstarttime']) : 0;
		$newendtime = $_G['gp_newendtime'] ? strtotime($_G['gp_newendtime']) : 0;
		$newsubject = trim($_G['gp_newsubject']);
		$newmessage = trim($_G['gp_newmessage']);
		if(!$newstarttime) {
			cpmsg('announce_time_invalid', '', 'error');
		} elseif(!$newsubject || !$newmessage) {
			cpmsg('announce_invalid', '', 'error');
		} else {
			$newmessage = $_G['gp_newtype'] == 1 ? explode("\n", $_G['gp_newmessage']) : array(0 => $_G['gp_newmessage']);
			$data = array(
				'author' => $_G['username'],
				'subject' => $newsubject,
				'type' => $_G['gp_newtype'],
				'starttime' => $newstarttime,
				'endtime' => $newendtime,
				'message' => $newmessage[0],
			);
			DB::insert('forum_announcement', $data);
			updatecache(array('announcements', 'announcements_forum'));
			cpmsg('announce_succeed', 'action=announce', 'succeed');
		}

	}

} elseif($operation == 'edit' && $_G['gp_announceid']) {

	$announce = DB::fetch_first("SELECT * FROM ".DB::table('forum_announcement')." WHERE id='{$_G['gp_announceid']}' AND ('$_G[adminid]'='1' OR author='$_G[username]')");
	if(!$announce) {
		cpmsg('announce_nonexistence', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'Y-n-j') : "";
		$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'Y-n-j') : "";

		shownav('tools', 'announce');
		showsubmenu('announce', array(
			array('admin', 'announce', 0),
			array('add', 'announce&operation=add', 0)
		));
		showformheader("announce&operation=edit&announceid={$_G['gp_announceid']}");
		showtableheader();
		showtitle('announce_edit');
		showsetting('subject', 'subjectnew', $announce['subject'], 'text');
		showsetting('start_time', 'starttimenew', $announce['starttime'], 'calendar');
		showsetting('end_time', 'endtimenew', $announce['endtime'], 'calendar');
		showsetting('announce_type', array('typenew', array(
			array(0, $lang['announce_words']),
			array(1, $lang['announce_url'])
		)), $announce['type'], 'mradio');
		showsetting('announce_message', 'messagenew', $announce['message'], 'textarea');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(strpos($_G['gp_starttimenew'], '-')) {
			$time = explode('-', $_G['gp_starttimenew']);
			$starttimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $_G['setting']['timeoffset'] * 3600;
		} else {
			$starttimenew = 0;
		}
		if(strpos($_G['gp_endtimenew'], '-')) {
			$time = explode('-', $_G['gp_endtimenew']);
			$endtimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $_G['setting']['timeoffset'] * 3600;
		} else {
			$endtimenew = 0;
		}
		$subjectnew = trim($_G['gp_subjectnew']);
		$messagenew = trim($_G['gp_messagenew']);
		if(!$starttimenew || ($endtimenew && $endtimenew <= TIMESTAMP)) {
			cpmsg('announce_time_invalid', '', 'error');
		} elseif(!$subjectnew || !$messagenew) {
			cpmsg('announce_invalid', '', 'error');
		} else {
			$messagenew = $_G['gp_typenew'] == 1 ? explode("\n", $messagenew) : array(0 => $messagenew);
			DB::update('forum_announcement', array(
				'subject' => $subjectnew,
				'type' => $_G['gp_typenew'],
				'starttime' => $starttimenew,
				'endtime' => $endtimenew,
				'message' => $messagenew[0],
			), "id='{$_G['gp_announceid']}' AND ('$_G[adminid]'='1' OR author='$_G[username]')");
			updatecache('announcements', 'announcements_forum');
			cpmsg('announce_succeed', 'action=announce', 'succeed');
		}
	}

}

?>