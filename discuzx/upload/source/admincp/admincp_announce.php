<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_announce.php 22868 2011-05-27 07:09:50Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(empty($operation)) {

	if(!submitcheck('announcesubmit')) {

		shownav('extended', 'announce', 'admin');
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
			$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'Y-n-j H:i') : $lang['unlimited'];
			$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'Y-n-j H:i') : $lang['unlimited'];
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$announce[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" name=\"displayordernew[{$announce[id]}]\" value=\"$announce[displayorder]\" size=\"2\" $disabled>",
				"<a href=\"./home.php?mod=space&username=".rawurlencode($announce['author'])."\" target=\"_blank\">$announce[author]</a>",
				$announce['subject'],
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

		$newstarttime = dgmdate(TIMESTAMP, 'Y-n-j H:i');
		$newendtime = dgmdate(TIMESTAMP + 86400* 7, 'Y-n-j H:i');

		shownav('extended', 'announce', 'add');
		showsubmenu('announce', array(
			array('admin', 'announce', 0),
			array('add', 'announce&operation=add', 1)
		));
		showformheader('announce&operation=add');
		showtableheader('announce_add');
		showsetting($lang[subject], 'newsubject', '',
		"<input type=\"text\" class=\"txt\" id=\"newsubject\" name=\"newsubject\" style=\"float:left; width:160px;\" value=\"\">
		<input id=\"announce_color\" onclick=\"change_title_color('announce_color')\" type=\"button\" class=\"colorwd\" value=\"\">
		<div class=\"fwin\"><div class=\"ss\">
		<em id=\"announce_bold\" onclick=\"change_title('bold');change_choose(this.id);\"><b>B</b></em>
		<em id=\"announce_italic\" onclick=\"change_title('italic');change_choose(this.id);\"><i>I</i></em>
		<em id=\"announce_underline\" onclick=\"change_title('underline');change_choose(this.id);\"><u>U</u></em>
		</div></div>
		"
		);
		showsetting($lang['start_time'], 'newstarttime', $newstarttime, 'calendar', '', 0, '', 1);
		showsetting($lang['end_time'], 'newendtime', $newendtime, 'calendar', '', 0, '', 1);
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
		if($newendtime && $newstarttime > $newendtime) {
			cpmsg('announce_time_invalid', '', 'error');
		}
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

		$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'Y-n-j H:i') : "";
		$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'Y-n-j H:i') : "";
		$b = $i = $u = $colorselect = $colorcheck = '';
		if(preg_match('/<b>(.*?)<\/b>/i', $announce['subject'])) {
			$b = 'class="a"';
		}
		if(preg_match('/<i>(.*?)<\/i>/i', $announce['subject'])) {
			$i = 'class="a"';
		}
		if(preg_match('/<u>(.*?)<\/u>/i', $announce['subject'])) {
			$u = 'class="a"';
		}
		$colorselect = preg_replace('/<font color=(.*?)>(.*?)<\/font>/i', '$1', $announce['subject']);
		$colorselect = strip_tags($colorselect);
		$_G['forum_colorarray'] = array(1=>'#EE1B2E', 2=>'#EE5023', 3=>'#996600', 4=>'#3C9D40', 5=>'#2897C5', 6=>'#2B65B7', 7=>'#8F2A90', 8=>'#EC1282');
		if(in_array($colorselect, $_G['forum_colorarray'])) {
			$colorcheck = "style=\"background: $colorselect\"";
		}

		shownav('extended', 'announce');
		showsubmenu('announce', array(
			array('admin', 'announce', 0),
			array('add', 'announce&operation=add', 0)
		));
		showformheader("announce&operation=edit&announceid={$_G['gp_announceid']}");
		showtableheader();
		showtitle('announce_edit');
		showsetting($lang[subject], 'newsubject', '',
		"<input type=\"text\" class=\"txt\" id=\"newsubject\" name=\"newsubject\" style=\"float:left; width:160px;\" value=\"$announce[subject]\">
		<input id=\"announce_color\" onclick=\"change_title_color('announce_color')\" type=\"button\" class=\"colorwd\" value=\"\" $colorcheck]>
		<div class=\"fwin\"><div class=\"ss\">
		<em id=\"announce_bold\" onclick=\"change_title('bold');change_choose(this.id);\" $b><b>B</b></em>
		<em id=\"announce_italic\" onclick=\"change_title('italic');change_choose(this.id);\" $i><i>I</i></em>
		<em id=\"announce_underline\" onclick=\"change_title('underline');change_choose(this.id);\" $u><u>U</u></em>
		</div></div>
		"
		);

		showsetting('start_time', 'starttimenew', $announce['starttime'], 'calendar', '', 0, '', 1);
		showsetting('end_time', 'endtimenew', $announce['endtime'], 'calendar', '', 0, '', 1);
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
			$starttimenew = strtotime($_G['gp_starttimenew']);
		} else {
			$starttimenew = 0;
		}
		if(strpos($_G['gp_endtimenew'], '-')) {
			$endtimenew = strtotime($_G['gp_endtimenew']);
		} else {
			$endtimenew = 0;
		}
		$subjectnew = trim($_G['gp_newsubject']);
		$messagenew = trim($_G['gp_messagenew']);
		if(!$starttimenew || ($endtimenew && $endtimenew <= TIMESTAMP) || $endtimenew && $starttimenew > $endtimenew) {
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
			updatecache(array('announcements', 'announcements_forum'));
			cpmsg('announce_succeed', 'action=announce', 'succeed');
		}
	}

}
echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function change_title(type) {
	if(type == 'bold') {
		old = $('newsubject').value.replace(/<b>(.*?)<\/b>/i, '$1');
		if(old == $('newsubject').value) {
			$('newsubject').value = '<b>'+old+'</b>';
		} else {
			$('newsubject').value = old;
		}
	} else if(type == 'italic') {
		old = $('newsubject').value.replace(/<i>(.*?)<\/i>/i, '$1');
		if(old == $('newsubject').value) {
			$('newsubject').value = '<i>'+old+'</i>';
		} else {
			$('newsubject').value = old;
		}
	} else if(type == 'underline') {
		old = $('newsubject').value.replace(/<u>(.*?)<\/u>/i, '$1');
		if(old == $('newsubject').value) {
			$('newsubject').value = '<u>'+old+'</u>';
		} else {
			$('newsubject').value = old;
		}
	}
}

function change_choose(id) {
	className = $(id).className;
	if(className == '') {
		$(id).className = 'a';
	} else {
		$(id).className = '';
	}
}

function title_replace(a) {
	old = $('newsubject').value;
	old = old.replace(/<font(.*?)>(.*?)<\/font>/i, '$2');
	if(a) {
		$('newsubject').value = '<font color='+a+'>'+old+'</font>';
	} else {
		$('newsubject').value = old;
	}
}

function change_title_color(hlid) {
	var showid = hlid;
	if(!$(showid + '_menu')) {
		var str = '';
		var coloroptions = {'0' : '#000', '1' : '#EE1B2E', '2' : '#EE5023', '3' : '#996600', '4' : '#3C9D40', '5' : '#2897C5', '6' : '#2B65B7', '7' : '#8F2A90', '8' : '#EC1282'};
		var menu = document.createElement('div');
		menu.id = showid + '_menu';
		menu.className = 'cmen';
		menu.style.display = 'none';
		for(var i in coloroptions) {
			str += '<a href="javascript:;" onclick="title_replace(\'' + coloroptions[i] + '\');$(\'' + showid + '\').style.backgroundColor=\'' + coloroptions[i] + '\';hideMenu(\'' + menu.id + '\')" style="background:' + coloroptions[i] + ';color:' + coloroptions[i] + ';">' + coloroptions[i] + '</a>';
		}
		menu.innerHTML = str;
		$('append_parent').appendChild(menu);
	}
	showMenu({'ctrlid':hlid + '_ctrl','evt':'click','showid':showid});

}

</script>
EOT;


?>