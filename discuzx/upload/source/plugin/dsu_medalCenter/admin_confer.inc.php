<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: admin_confer.inc.php 50 2011-02-03 12:32:41Z 6forget@gmail.com $
*/
(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && exit('Access Denied');

$search_condition = array_merge($_GET, $_POST);

foreach($search_condition as $k => $v) {
	if(in_array($k, array('action', 'operation', 'formhash', 'submit', 'page')) || $v === '') {
		unset($search_condition[$k]);
	}
}

$medals = '';
$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." WHERE available='1' ORDER BY displayorder");
while($medal = DB::fetch($query)) {
	$medals .= showtablerow('', array('class="td25"', 'class="td23"'), array(
		"<input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" />",
		"<img src=\"static/image/common/$medal[image]\" />",
		$medal['name']
	), TRUE);
}

if(!$medals) {
	cpmsg('members_edit_medals_nonexistence', 'plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage', 'error');
}

if(!submitcheck('confermedalsubmit', 1)) {

	showsubmenusteps('nav_members_confermedal', array(
		array('nav_members_select', !$_G['gp_submit']),
		array('nav_members_confermedal', $_G['gp_submit']),
	));

	showsearchform('confermedal');

	if(submitcheck('submit', 1)) {

		$membernum = countmembers($search_condition, $urladd);

		showtagheader('div', 'confermedal', TRUE);
		showformheader('plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_confer'.$urladd);
		echo '<table class="tb tb1">';

		if(!$membernum) {
			showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
			showtablefooter();
		} else {

			showtablerow('class="first"', array('class="th11"'), array(
				cplang('members_confermedal_members'),
				cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('confermedal').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
			));

			echo '<tr><td class="th12">'.cplang('members_confermedal').'</td><td>';
			showtableheader('', 'noborder');
			showsubtitle(array('medals_grant', 'medals_image', 'name'));
			echo $medals;
			showtablefooter();
			echo '</td></tr>';

			showtagheader('tbody', 'messagebody');
			shownewsletter();
			showtagfooter('tbody');
			showsubmit('confermedalsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymember" value="1" onclick="$(\'messagebody\').style.display = this.checked ? \'\' : \'none\'" id="grant_notify"/><label for="grant_notify">'.cplang('medals_grant_notify').'</label>');

		}

		showtablefooter();
		showformfooter();
		showtagfooter('div');

	}

} else {
	$membernum = countmembers($search_condition, $urladd);
	notifymembers('confermedal', 'medalletter');
}
function showsearchform($operation = '') {
	global $_G, $lang;

	$groupselect = array();
	$usergroupid = isset($_G['gp_usergroupid']) && is_array($_G['gp_usergroupid']) ? $_G['gp_usergroupid'] : array();
	$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." WHERE groupid NOT IN ('6', '7') ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
	while($group = DB::fetch($query)) {
		$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
		$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupid) ? 'selected' : '').">$group[grouptitle]</option>\n";
	}
	$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
		($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
		($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
		'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';

	showtagheader('div', 'searchmembers', !$_G['gp_submit']);
	echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
	showformheader("plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_confer", "onSubmit=\"if($('updatecredittype1') && $('updatecredittype1').checked && !window.confirm('$lang[members_reward_clean_alarm]')){return false;} else {return true;}\"");
	showtableheader();
	showsetting('members_search_user', 'username', $_G['gp_username'], 'text');
	showsetting('members_search_uid', 'uid', $_G['gp_uid'], 'text');
	showsetting('members_search_group', '', '', '<select name="groupid[]" multiple="multiple" size="10"><option value="all"'.(in_array('all', $usergroupid) ? ' selected' : '').'>'.cplang('unlimited').'</option>'.$groupselect.'</select>');

	showtagheader('tbody', 'advanceoption');
	showsetting('members_search_email', 'email', $_G['gp_email'], 'text');
	showsetting("$lang[credits] $lang[members_search_between]", array("credits_low", "credits_high"), array($_G['gp_credits_low'], $_G['gp_credtis_high']), 'range');

	if(!empty($_G['setting']['extcredits'])) {
		foreach($_G['setting']['extcredits'] as $id => $credit) {
			showsetting("$credit[title] $lang[members_search_between]", array("extcredits$id"."_low", "extcredits$id"."_high"), array($_G['gp_extcredits'.$id.'_low'], $_G['gp_extcredits'.$id.'_high']), 'range');
		}
	}

	showsetting('members_search_postsrange', array('posts_low', 'posts_high'), array($_G['gp_posts_high'], $_G['gp_posts_low']), 'range');
	showsetting('members_search_regip', 'regip', $_G['gp_regip'], 'text');
	showsetting('members_search_lastip', 'lastip', $_G['gp_lastip'], 'text');
	showsetting('members_search_regdaterange', array('regdate_after', 'regdate_before'), array($_G['gp_regdate_after'], $_G['gp_regdate_before']), 'daterange');
	showsetting('members_search_lastvisitrange', array('lastvisit_after', 'lastvisit_before'), array($_G['gp_lastvisit_after'], $_G['gp_lastvisit_before']), 'daterange');
	showsetting('members_search_lastpostrange', array('lastpost_after', 'lastpost_before'), array($_G['gp_lastpost_after'], $_G['gp_lastpost_before']), 'daterange');
	showsetting('members_search_lockstatus', array('lockstatus', array(
		array(-1, $lang['yes']),
		array(0, $lang['no']),
	)), $_G['gp_lockstatus'], 'mradio');
	showsetting('members_search_emailstatus', array('emailstatus', array(
		array(1, $lang['yes']),
		array(0, $lang['no']),
	)), $_G['gp_emailstatus'], 'mradio');
	showsetting('members_search_avatarstatus', array('avatarstatus', array(
		array(1, $lang['yes']),
		array(0, $lang['no']),
	)), $_G['gp_avatarstatus'], 'mradio');
	showsetting('members_search_videostatus', array('videostatus', array(
		array(1, $lang['yes']),
		array(0, $lang['no']),
	)), $_G['gp_videostatus'], 'mradio');
	$yearselect = $monthselect = $dayselect = "<option value=\"\">".cplang('nolimit')."</option>\n";
	$yy=dgmdate(TIMESTAMP, 'Y');
	for($y=$yy; $y>=$yy-100; $y--) {
		$y = sprintf("%04d", $y);
		$yearselect .= "<option value=\"$y\" ".($_G['gp_birthyear'] == $y ? 'selected' : '').">$y</option>\n";
	}
	for($m=1; $m<=12; $m++) {
		$m = sprintf("%02d", $m);
		$monthselect .= "<option value=\"$m\" ".($_G['gp_birthmonth'] == $m ? 'selected' : '').">$m</option>\n";
	}
	for($d=1; $d<=31; $d++) {
		$d = sprintf("%02d", $d);
		$dayselect .= "<option value=\"$d\" ".($_G['gp_birthday'] == $d ? 'selected' : '').">$d</option>\n";
	}
	showsetting('members_search_birthday', '', '', '<select class="txt" name="birthyear" style="width:75px; margin-right:0">'.$yearselect.'</select> '.$lang['year'].' <select class="txt" name="birthmonth" style="width:75px; margin-right:0">'.$monthselect.'</select> '.$lang['month'].' <select class="txt" name="birthday" style="width:75px; margin-right:0">'.$dayselect.'</select> '.$lang['day']);

	loadcache('profilesetting');
	unset($_G['cache']['profilesetting']['uid']);
	unset($_G['cache']['profilesetting']['birthyear']);
	unset($_G['cache']['profilesetting']['birthmonth']);
	unset($_G['cache']['profilesetting']['birthday']);
	foreach($_G['cache']['profilesetting'] as $fieldid=>$value) {
		if($fieldid == 'gender') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			$select .= "<option value=\"0\">".cplang('members_edit_gender_secret')."</option>\n";
			$select .= "<option value=\"1\">".cplang('members_edit_gender_male')."</option>\n";
			$select .= "<option value=\"2\">".cplang('members_edit_gender_female')."</option>\n";
			showsetting($value['title'], '', '', '<select class="txt" name="gender">'.$select.'</select>');
		} elseif($fieldid == 'constellation') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			for($i=1; $i<=12; $i++) {
				$name = lang('space', 'constellation_'.$i);
				$select .= "<option value=\"$name\">$name</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="constellation">'.$select.'</select>');
		} elseif($fieldid == 'zodiac') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			for($i=1; $i<=12; $i++) {
				$option = lang('space', 'zodiac_'.$i);
				$select .= "<option value=\"$option\">$option</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="zodiac">'.$select.'</select>');
		} elseif($value['formtype'] == 'select' || $value['formtype'] == 'list') {
			$select = "<option value=\"\">".cplang('nolimit')."</option>\n";
			$value['choices'] = explode("\n",$value['choices']);
			foreach($value['choices'] as $option) {
				$option = trim($option);
				$select .= "<option value=\"$option\">$option</option>\n";
			}
			showsetting($value['title'], '', '', '<select class="txt" name="'.$fieldid.'">'.$select.'</select>');
		} else {
			showsetting($value['title'], '', '', '<input class="txt" name="'.$fieldid.'" />');
		}
	}
	showtagfooter('tbody');
	showsubmit('submit', $operation == 'clean' ? 'members_delete' : 'search', '', 'more_options');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
}

function countmembers($condition, &$urladd) {

	$urladd = '';
	foreach($condition as $k => $v) {
		if(in_array($k, array('formhash', 'submit', 'page')) || $v === '') {
			continue;
		}
		if(is_array($v)) {
			foreach($v as $vk => $vv) {
				if($vv === '') {
					continue;
				}
				$urladd .= '&'.$k.'['.$vk.']='.rawurlencode($vv);
			}
		} else {
			$urladd .= '&'.$k.'='.rawurlencode($v);
		}
	}
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->getcount($condition);
}

function shownewsletter() {
	global $lang;

	showtablerow('', array('class="th11"', 'class="longtxt"'), array(
		$lang['members_newsletter_subject'],
		'<input type="text" class="txt" name="subject" size="80" value="" />'
	));
	showtablerow('', array('class="th12"', ''), array(
		$lang['members_newsletter_message'],
		'<textarea name="message" class="tarea" cols="80" rows="10"></textarea>'
	));
	showtablerow('', array('', 'class="td12"'), array(
		'',
		'<ul><li><input class="radio" type="radio" value="email" name="notifymembers" id="viaemail" /><label for="viaemail"> '.$lang['email'].'</label></li><li><input class="radio" type="radio" value="pm" checked="checked" name="notifymembers" id="viapm" /><label for="viapm"> '.$lang['notice'].'</label></li><li><span class="diffcolor2">'.$lang['members_newsletter_num'].'</span><input type="text" class="txt" name="pertask" value="100" size="10"></li></ul>'
	));
}

function searchmembers($condition, $limit=2000, $start=0) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->search($condition, $limit, $start);
}

function notifymembers($operation, $variable) {
	global $_G, $lang, $urladd, $conditions, $search_condition;

	if(!empty($_G['gp_current'])) {

		$subject = $message = '';
		if($settings = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='$variable'")) {
			$settings = unserialize($settings);
			$subject = $settings['subject'];
			$message = $settings['message'];
		}

	} else {

		$current = 0;
		$subject = $_G['gp_subject'];
		$message = $_G['gp_message'];
		$subject = trim($subject);
		$message = trim(str_replace("\t", ' ', $message));
		if(($_G['gp_notifymembers'] && $_G['gp_notifymember']) && !($subject && $message)) {
			cpmsg('members_newsletter_sm_invalid', '', 'error');
		}

		
		if ($operation == 'confermedal') {

			$medals = $_G['gp_medals'];
			if(!empty($medals)) {
				$medalids = $comma = '';
				foreach($medals as $key=> $medalid) {
					$medalids .= "$comma'$key'";
					$comma = ',';
				}

				$medalsnew = $comma = '';
				$medalsnewarray = array();
				$query = DB::query("SELECT medalid, expiration FROM ".DB::table('forum_medal')." WHERE medalid IN ($medalids) ORDER BY displayorder");
				while($medal = DB::fetch($query)) {
					$medal['status'] = empty($medal['expiration']) ? 0 : 1;
					$medal['expiration'] = empty($medal['expiration'])? 0 : TIMESTAMP + $medal['expiration'] * 86400;
					$medal['medal'] = $medal['medalid'].(empty($medal['expiration']) ? '' : '|'.$medal['expiration']);
					$medalsnew .= $comma.$medal['medal'];
					$medalsnewarray[] = $medal;
					$comma = "\t";
				}

				$uids = searchmembers($search_condition);
				if($uids) {
					$query = DB::query("SELECT uid, medals FROM ".DB::table('common_member_field_forum')." WHERE uid IN (".dimplode($uids).")");
					while($medalnew = DB::fetch($query)) {

						$addmedalnew = '';
						if(empty($medalnew['medals'])) {
							$addmedalnew = $medalsnew;
						} else {
							$medal_old_arr = explode("\t", $medalnew['medals']);
							
							foreach($medal_old_arr as $medaloldid) {
								if (strstr($medaloldid, '|')){
									$values = explode("|", $medaloldid);$medalid_old_arr []= $values[0];
								}else{
									$medalid_old_arr []= $medaloldid;
								} 
							}	
							foreach($medalsnewarray as $medalid) {
								if(!in_array($medalid['medalid'], $medalid_old_arr)){
									$addmedalnew .= $medalid['medal']."\t";
								}
							}
							$addmedalnew .= $medalnew['medals'];
						}
						DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='".$addmedalnew."' WHERE uid='".$medalnew['uid']."'", 'UNBUFFTERED');

						foreach($medalsnewarray as $medalnewarray) {
							$data = array(
								'uid' => $medalnew['uid'],
								'medalid' => $medalnewarray['medalid'],
								'type' => 0,
								'dateline' => $_G['timestamp'],
								'expiration' => $medalnewarray['expiration'],
								'status' => $medalnewarray['status'],
							);
							DB::insert('forum_medallog', $data);
						}
					}
				}
			}

			if(!$_G['gp_notifymembers']) {
				cpmsg('members_confermedal_succeed', '', 'succeed');
			}

		}

		DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('$variable', '".
			addslashes(serialize(array('subject' => $subject, 'message' => $message)))."')");
	}

	$pertask = intval($_G['gp_pertask']);
	$current = $_G['gp_current'] ? intval($_G['gp_current']) : 0;
	$continue = FALSE;

	if(!function_exists('sendmail')) {
		include libfile('function/mail');
	}

	if($_G['gp_notifymember'] && in_array($_G['gp_notifymembers'], array('pm', 'email'))) {

		$uids = searchmembers($search_condition, $pertask, $current);
		$conditions = $uids ? 'uid IN ('.dimplode($uids).')' : '0';

		require_once libfile('function/discuzcode');
		$message = discuzcode($message, 1, 0);
		$query = DB::query("SELECT uid, username, groupid, email FROM ".DB::table('common_member')." m WHERE $conditions");
		while($member = DB::fetch($query)) {
			$_G['gp_notifymembers'] == 'pm' ? notification_add($member['uid'], 'system', 'system_notice', array('subject' => $subject, 'message' => $message), 1) : sendmail("$member[username] <$member[email]>", $subject, $message);
			$continue = TRUE;
		}
	}

	if($continue) {

		$next = $current + $pertask;
		cpmsg("$lang[members_newsletter_send]: ".cplang('members_newsletter_processing', array('current' => $current, 'next' => $next, 'search_condition' => serialize($search_condition))), "action=members&operation=$operation&{$operation}submit=yes&current=$next&pertask=$pertask&notifymember={$_G['gp_notifymember']}&notifymembers=".rawurlencode($_G['gp_notifymembers']).$urladd, 'loadingform');
	} else {
		cpmsg('members'.($operation ? '_'.$operation : '').'_notify_succeed', '', 'succeed');
	}
}
?>