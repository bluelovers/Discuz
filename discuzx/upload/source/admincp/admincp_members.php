<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_members.php 17293 2010-09-29 05:29:02Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(600);
if($operation != 'export') {
	cpheader();
}

require_once libfile('function/delete');

$_G['setting']['memberperpage'] = 20;
$page = max(1, $_G['page']);
$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
$search_condition = array_merge($_GET, $_POST);

foreach($search_condition as $k => $v) {
	if(in_array($k, array('action', 'operation', 'formhash', 'submit', 'page')) || $v === '') {
		unset($search_condition[$k]);
	}
}

if($operation == 'search') {

	if(!submitcheck('submit', 1)) {

		shownav('user', 'nav_members');
		showsubmenu('nav_members', array(
			array('search', 'members&operation=search', 1),
			array('clean', 'members&operation=clean', 0),
			array('nav_repeat', 'members&operation=repeat', 0),
		));
		showtips('members_admin_tips');
		showsearchform('search');

	} else {

		$membernum = countmembers($search_condition, $urladd);

		$members = '';
		if($membernum > 0) {
			$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT."?action=members&operation=search&submit=yes".$urladd);

			$usergroups = array();
			$query = DB::query("SELECT groupid, type, grouptitle FROM ".DB::table('common_usergroup'));
			while($group = DB::fetch($query)) {
				switch($group['type']) {
					case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
					case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
				}
				$usergroups[$group['groupid']] = $group;
			}

			$uids = searchmembers($search_condition, $_G['setting']['memberperpage'], $start_limit);
			if($uids) {
				$conditions = 'm.uid IN ('.dimplode($uids).')';

				$query = DB::query("SELECT m.uid AS uid, m.username AS username, m.adminid AS adminid, m.groupid AS groupid, m.credits AS credits,
					mc.extcredits1 AS extcredits1, mc.extcredits2 AS extcredits2, mc.extcredits3 AS extcredits3, mc.extcredits4 AS extcredits4,
					mc.extcredits5 AS extcredits5, mc.extcredits6 AS extcredits6, mc.extcredits7 AS extcredits7, mc.extcredits8 AS extcredits8,
					mc.posts FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_count')." mc ON m.uid=mc.uid
					WHERE $conditions");

				while($member = DB::fetch($query)) {
					$memberextcredits = array();
					if($_G['setting']['extcredits']) {
						foreach($_G['setting']['extcredits'] as $id => $credit) {
							$memberextcredits[] = $_G['setting']['extcredits'][$id]['title'].': '.$member['extcredits'.$id].' ';
						}
					}
					$members .= showtablerow('', array('class="td25"', '', 'title="'.implode("\n", $memberextcredits).'"'), array(
						"<input type=\"checkbox\" name=\"uidarray[]\" value=\"$member[uid]\"".($member['adminid'] == 1 ? 'disabled' : '')." class=\"checkbox\">",
						"<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
						$member['credits'],
						$member['posts'],
						$usergroups[$member['adminid']]['grouptitle'],
						$usergroups[$member['groupid']]['grouptitle'],
						"<a href=\"".ADMINSCRIPT."?action=members&operation=group&uid=$member[uid]\" class=\"act\">$lang[usergroup]</a><a href=\"".ADMINSCRIPT."?action=members&operation=access&uid=$member[uid]\" class=\"act\">$lang[members_access]</a>".
						($_G['setting']['extcredits'] ? "<a href=\"".ADMINSCRIPT."?action=members&operation=credit&uid=$member[uid]\" class=\"act\">$lang[credits]</a>" : "<span disabled>$lang[edit]</span>").
						"<a href=\"".ADMINSCRIPT."?action=members&operation=medal&uid=$member[uid]\" class=\"act\">$lang[medals]</a>".
						"<a href=\"".ADMINSCRIPT."?action=members&operation=repeat&uid=$member[uid]\" class=\"act\">$lang[members_repeat]</a>".
						"<a href=\"".ADMINSCRIPT."?action=members&operation=edit&uid=$member[uid]\" class=\"act\">$lang[detail]</a>".
						"<a href=\"".ADMINSCRIPT."?action=members&operation=ban&uid=$member[uid]\" class=\"act\">$lang[members_ban]</a>"
					), TRUE);
				}
			}
		}

		shownav('user', 'nav_members');
		showsubmenu('nav_members');
		showtips('members_export_tips');
		showformheader("members&operation=clean");
		showtableheader(cplang('members_search_result', array('membernum' => $membernum)).'<a href="admin.php?action=members&operation=search" class="act lightlink normal">'.cplang('research').'</a>');
		showsubtitle(array('', 'username', 'credits', 'posts', 'admingroup', 'usergroup', ''));
		echo $members;
		foreach($search_condition as $k => $v) {
			$condition_str .= '&'.$k.'='.$v;
			if($k == 'groupid') {
				foreach($search_condition['groupid'] as $value ) {
					$condition_str .= '&groupid[]='.$value;
				}
			}
		}

		showsubmit('submit', 'submit', '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'uidarray\')" class="checkbox">'.cplang('del'), '<a href='.ADMINSCRIPT.'?action=members&operation=export'.$condition_str.'>'.$lang['members_search_export'].'</a>', $multipage);
		showtablefooter();
		showformfooter();

	}

} elseif($operation == 'export') {
	$uids = searchmembers($search_condition, 10000);
	$info = array();
	$detail = '';
	if($uids && is_array($uids)) {
		$conditions = 'p.uid IN ('.dimplode($uids).')';
		$query = DB::query("SELECT p.uid,m.username AS username,p.realname,p.gender,p.birthyear,p.birthmonth,p.birthday,p.constellation,
				p.zodiac,p.telephone,p.mobile,p.idcardtype,p.idcard,p.address,p.zipcode,p.nationality,p.birthprovince,p.birthcity,
				p.resideprovince,p.residecity,p.residedist,p.residecommunity,p.residesuite,p.graduateschool,p.education,p.company,
				p.occupation,p.position,p.revenue,p.affectivestatus,p.lookingfor,p.bloodtype,p.height,p.weight,p.alipay,p.icq,p.qq,
				p.yahoo,p.msn,p.taobao,p.site,p.bio,p.interest,p.field1,p.field2,p.field3,p.field4,p.field5,p.field6,p.field7,p.field8 FROM ".
				DB::table('common_member_profile')." p LEFT JOIN ".DB::table('common_member')." m ON p.uid =m.uid WHERE ".$conditions);
		while($value = DB::fetch($query)) {
			$info[$value['uid']] = $value;
		}

		foreach($info as $v) {
			foreach($v as $value) {
				$value = preg_replace('/\s+/', ' ', $value);
				$detail .= strlen($value) > 11 && is_numeric($value) ? '['.$value.'],' : $value.',';
			}
			$detail = $detail."\n";
		}
	}
	$query = DB::query("SELECT title FROM ".DB::table('common_member_profile_setting'));
	while($value = DB::fetch($query)) {
		$title[] = $value['title'];
	}

	foreach($title as $v) {
		$subject .= $v.",";
	}
	$detail = "UID,".$lang['username'].",".$subject."\n".$detail;
	$filename = date('Ymd', TIMESTAMP).'.csv';

	ob_end_clean();
	header('Content-Encoding: none');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Pragma: no-cache');
	header('Expires: 0');
	echo diconv($detail, $_G['charset'], 'UCS-2LE');
	exit();

} elseif($operation == 'repeat') {

	if(empty($_G['gp_uid']) && empty($_G['gp_username']) && empty($_G['gp_ip'])) {

		shownav('user', 'nav_members');
		showsubmenu('nav_members', array(
			array('search', 'members&operation=search', 0),
			array('clean', 'members&operation=clean', 0),
			array('nav_repeat', 'members&operation=repeat', 1),
		));

		showformheader("members&operation=repeat");
		showtableheader();
		showsetting('members_search_repeatuser', 'username', '', 'text');
		showsetting('members_search_uid', 'uid', '', 'text');
		showsetting('members_search_repeatip', 'ip', $_G['gp_inputip'], 'text');
		showsubmit('submit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$urladd = '';
		if(!empty($_G['gp_username'])) {
			$searchmember = DB::fetch_first("SELECT m.username AS username, ms.regip AS regip, ms.lastip AS lastip
				FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid
				WHERE m.username='$_G[gp_username]'");
			$urladd .= '&username='.$_G['gp_username'];
		} elseif(!empty($_G['gp_uid'])) {
			$searchmember = DB::fetch_first("SELECT m.username AS username, ms.regip AS regip, ms.lastip AS lastip
				FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid
				WHERE m.uid='$_G[gp_uid]'");
			unset($_G['gp_uid']);
			$urladd .= '&uid='.$_G['gp_uid'];
		} elseif(!empty($_G['gp_ip'])) {
			$ids = $regip = $lastip = $_G['gp_ip'];
			$ids = "'".$ids."'";
			$search_condition['lastip'] = $_G['gp_ip'];
			$urladd .= '&ip='.$_G['gp_ip'];
		}

		if($searchmember) {
			$ips = array();
			foreach(array('regip', 'lastip') as $iptype) {
				if($searchmember[$iptype] != '' && $searchmember[$iptype] != 'hidden') {
					$ips[] = $searchmember[$iptype];
				}
			}
			$ips = array_unique($ips);
			$ids = dimplode($ips);
			if(empty($ids)) {
				$ids = "'unknown'";
			}
		}
		if($ids) {
			$repeatip = " AND (ms.regip IN ($ids) OR ms.lastip IN ($ids))";
		}
		$searchmember['username'] .= ' (IP '.htmlspecialchars($ids).')';
		$membernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_status')." ms WHERE 1 $repeatip");

		$members = '';
		if($membernum) {
			$usergroups = array();
			$query = DB::query("SELECT groupid, type, grouptitle FROM ".DB::table('common_usergroup'));
			while($group = DB::fetch($query)) {
				switch($group['type']) {
					case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
					case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
				}
				$usergroups[$group['groupid']] = $group;
			}

			$uids = searchmembers($search_condition, $_G['setting']['memberperpage'], $start_limit);
			$conditions = 'm.uid IN ('.dimplode($uids).')';
			$_G['setting']['memberperpage'] = 100;
			$start_limit = ($page - 1) * $_G['setting']['memberperpage'];
			$multipage = multi($membernum, $_G['setting']['memberperpage'], $page, ADMINSCRIPT."?action=members&operation=repeat&submit=yes".$urladd);
			$query = DB::query("SELECT m.uid AS uid, m.username AS username, m.adminid AS adminid, m.groupid AS groupid, m.credits AS credits,
				mc.extcredits1 AS extcredits1, mc.extcredits2 AS extcredits2, mc.extcredits3 AS extcredits3, mc.extcredits4 AS extcredits4,
				mc.extcredits5 AS extcredits5, mc.extcredits6 AS extcredits6, mc.extcredits7 AS extcredits7, mc.extcredits8 AS extcredits8,
				mc.posts FROM ".DB::table('common_member')." m
				LEFT JOIN ".DB::table('common_member_count')." mc ON m.uid=mc.uid
				LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid WHERE 1 $repeatip LIMIT $start_limit, {$_G[setting][memberperpage]}");
			while($member = DB::fetch($query)) {
				$memberextcredits = array();
				foreach($_G['setting']['extcredits'] as $id => $credit) {
					$memberextcredits[] = $_G['setting']['extcredits'][$id]['title'].': '.$member['extcredits'.$id];
				}
				$members .= showtablerow('', array('class="td25"', '', 'title="'.implode("\n", $memberextcredits).'"'), array(
					"<input type=\"checkbox\" name=\"uidarray[]\" value=\"$member[uid]\"".($member['adminid'] == 1 ? 'disabled' : '')." class=\"checkbox\">",
					"<a href=\"home.php?mod=space&uid=$member[uid]\" target=\"_blank\">$member[username]</a>",
					$member['credits'],
					$member['posts'],
					$usergroups[$member['adminid']]['grouptitle'],
					$usergroups[$member['groupid']]['grouptitle'],
					"<a href=\"".ADMINSCRIPT."?action=members&operation=group&uid=$member[uid]\" class=\"act\">$lang[usergroup]</a><a href=\"".ADMINSCRIPT."?action=members&operation=access&uid=$member[uid]\" class=\"act\">$lang[members_access]</a>".
					($_G['setting']['extcredits'] ? "<a href=\"".ADMINSCRIPT."?action=members&operation=credit&uid=$member[uid]\" class=\"act\">$lang[credits]</a>" : "<span disabled>$lang[edit]</span>").
					"<a href=\"".ADMINSCRIPT."?action=members&operation=medal&uid=$member[uid]\" class=\"act\">$lang[medals]</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=repeat&uid=$member[uid]\" class=\"act\">$lang[members_repeat]</a>".
					"<a href=\"".ADMINSCRIPT."?action=members&operation=edit&uid=$member[uid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
			}
		}

		shownav('user', 'nav_repeat');
		showsubmenu($lang['nav_repeat'].' - '.$searchmember['username']);
		showformheader("members&operation=clean");
		$searchadd = '';
		if(is_array($ips)) {
			foreach($ips as $ip) {
				$searchadd .= '<a href="'.ADMINSCRIPT.'?action=members&operation=repeat&inputip='.rawurlencode($ip).'" class="act lightlink normal">'.cplang('search').'IP '.htmlspecialchars($ip).'</a>';
			}
		}
		showtableheader(cplang('members_search_result', array('membernum' => $membernum)).'<a href="'.ADMINSCRIPT.'?action=members&operation=repeat" class="act lightlink normal">'.cplang('research').'</a>'.$searchadd);
		showsubtitle(array('', 'username', 'credits', 'posts', 'admingroup', 'usergroup', ''));
		echo $members;
		showtablerow('', array('class="td25"', 'class="lineheight" colspan="7"'), array('', cplang('members_admin_comment')));
		showsubmit('submit', 'submit', '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'uidarray\')" class="checkbox">'.cplang('del'), '', $multipage);
		showtablefooter();
		showformfooter();

	}

} elseif($operation == 'clean') {

	if(!submitcheck('submit', 1)) {

		shownav('user', 'nav_members');
		showsubmenu('nav_members', array(
			array('search', 'members&operation=search', 0),
			array('clean', 'members&operation=clean', 1),
			array('nav_repeat', 'members&operation=repeat', 0),
		));

		showsearchform('clean');

	} else {

		$membernum = countmembers($search_condition, $urladd);

		$uids = 0;
		$extra = '';

		$uids = searchmembers($search_condition);
		$conditions = $uids ? 'm.uid IN ('.dimplode($uids).')' : '0';

		if(empty($_G['gp_uidarray'])) {
			$query = DB::query("SELECT uid, groupid, adminid FROM ".DB::table('common_member')." m WHERE $conditions AND adminid<>1 AND groupid<>1");
		} else {
			$uids = is_array($_G['gp_uidarray']) ? '\''.implode('\', \'', $_G['gp_uidarray']).'\'' : '0';
			$query = DB::query("SELECT uid, groupid, adminid FROM ".DB::table('common_member')." WHERE uid IN($uids) AND adminid<>1 AND groupid<>1");
		}

		$membernum = DB::num_rows($query);

		$uids = $comma = '';
		while($member = DB::fetch($query)) {
			if($membernum < 2000 || !empty($_G['gp_uidarray'])) {
				$extra .= '<input type="hidden" name="uidarray[]" value="'.$member['uid'].'" />';
			}
			$uids .= $comma.$member['uid'];
			$comma = ',';
		}

		if((empty($membernum) || empty($uids))) {
			cpmsg('members_no_find_deluser', '', 'error');
		}

		if(!$_G['gp_confirmed']) {

			cpmsg('members_delete_confirm', "action=members&operation=clean&submit=yes&confirmed=yes".$urladd, 'form', array('membernum' => $membernum), $extra.'<br /><label><input type="checkbox" name="includepost" value="1" class="checkbox" />'.$lang['members_delete_all'].'</label>'.($isfounder ? '&nbsp;<label><input type="checkbox" name="includeuc" value="1" class="checkbox" />'.$lang['members_delete_ucdata'].'</label>' : ''), '');

		} else {

			if($isfounder && !empty($_G['gp_includeuc'])) {
				loaducenter();
				uc_user_delete($_G['gp_uidarray']);
			}

			if(empty($_G['gp_includepost'])) {

				require_once libfile('function/delete');
				$numdeleted = deletemember($uids, 0);
				cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));

			} else {

				$numdeleted = $numdeleted ? $numdeleted : count($_G['gp_uidarray']);
				$pertask = 1000;
				$current = $_G['gp_current'] ? intval($_G['gp_current']) : 0;

				$next = $current + $pertask;
				$threads = $fids = $threadsarray = array();

				$query = DB::query("SELECT f.fid, t.tid FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_forum')." f ON t.fid=f.fid WHERE t.authorid IN ($uids) ORDER BY f.fid LIMIT $pertask");
				while($thread = DB::fetch($query)) {
					$threads[$thread['fid']] .= ($threads[$thread['fid']] ? ',' : '').$thread['tid'];
				}

				$nextlink = "action=members&operation=clean&confirmed=yes&submit=yes&includepost=yes&current=$next&pertask=$pertask&lastprocess=$processed".$urladd;
				if($threads) {
					foreach($threads as $fid => $tids) {
						$query = DB::query("SELECT attachment, thumb, remote FROM ".DB::table('forum_attachment')." WHERE tid IN ($tids)");
						while($attach = DB::fetch($query)) {
							dunlink($attach);
						}

						foreach(array('forum_thread', 'forum_threadmod', 'forum_relatedthread', 'forum_post', 'forum_poll',
							'forum_polloption', 'forum_trade', 'forum_activity', 'forum_activityapply', 'forum_debate',
							'forum_debatepost', 'forum_attachment', 'forum_typeoptionvar', 'forum_forumrecommend', 'forum_postposition') as $value) {
							if($value == 'forum_post') {
								deletepost("tid IN ($tids)");
								continue;
							}
							DB::query("DELETE FROM ".DB::table($value)." WHERE tid IN ($tids)", 'UNBUFFERED');
						}

						require_once libfile('function/post');
						updateforumcount($fid);
					}
					if($_G['setting']['globalstick']) {
						require_once libfile('function/cache');
						updatecache('globalstick');
					}

					cpmsg(cplang('members_delete_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loadingform', array(), $extra);

				} elseif($uids) {

					require_once libfile('function/delete');
					$numdeleted = deletemember($uids);
					cpmsg('members_delete_succeed', '', 'succeed', array('numdeleted' => $numdeleted));

				} else {

					cpmsg('members_no_find_deluser', '', 'error');

				}
			}

		}
	}

} elseif($operation == 'newsletter') {

	if(!submitcheck('newslettersubmit', 1)) {

		shownav('user', 'nav_members_newsletter');
		showsubmenusteps('nav_members_newsletter', array(
			array('nav_members_select', !$_G['gp_submit']),
			array('nav_members_notify', $_G['gp_submit']),
		));

		showsearchform('newsletter');

		if(submitcheck('submit', 1)) {

			$membernum = countmembers($search_condition, $urladd);

			showtagheader('div', 'newsletter', TRUE);
			showformheader('members&operation=newsletter'.$urladd);
			showhiddenfields(array('notifymember' => 1));
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
			} else {
				showtablerow('class="first"', array('class="th11"'), array(
					cplang('members_newsletter_members'),
					cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('newsletter').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				showtagheader('tbody', 'messagebody', TRUE);
				shownewsletter();
				showtagfooter('tbody');

				$search_condition = serialize($search_condition);
				showsubmit('newslettersubmit', 'submit', 'td', '<input type="hidden" name="conditions" value=\''.$search_condition.'\' />');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {

		$search_condition = unserialize(stripslashes($_POST['conditions']));
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('newsletter', 'newsletter');

	}

} elseif($operation == 'reward') {

	if(!submitcheck('rewardsubmit', 1)) {

		shownav('user', 'nav_members_reward');
		showsubmenusteps('nav_members_reward', array(
			array('nav_members_select', !$_G['gp_submit']),
			array('nav_members_reward', $_G['gp_submit']),
		));

		showsearchform('reward');

		if(submitcheck('submit', 1)) {

			$membernum = countmembers($search_condition, $urladd);
			showtagheader('div', 'reward', TRUE);
			showformheader('members&operation=reward'.$urladd);
			echo '<table class="tb tb1">';

			if(!$membernum) {
				showtablerow('', 'class="lineheight"', $lang['members_search_nonexistence']);
				showtablefooter();
			} else {

				$creditscols = array('credits_title');
				$creditsvalue = $resetcredits = array();
				$js_extcreditids = '';
				for($i=1; $i<=8; $i++) {
					$js_extcreditids .= (isset($_G['setting']['extcredits'][$i]) ? ($js_extcreditids ? ',' : '').$i : '');
					$creditscols[] = isset($_G['setting']['extcredits'][$i]) ? $_G['setting']['extcredits'][$i]['title'] : 'extcredits'.$i;
					$creditsvalue[] = isset($_G['setting']['extcredits'][$i]) ? '<input type="text" class="txt" size="3" id="addextcredits['.$i.']" name="addextcredits['.$i.']" value="0"> '.$_G['setting']['extcredits']['$i']['unit'] : '<input type="text" class="txt" size="3" value="N/A" disabled>';
					$resetcredits[] = isset($_G['setting']['extcredits'][$i]) ? '<input type="checkbox" id="resetextcredits['.$i.']" name="resetextcredits['.$i.']" value="1" class="radio" disabled> '.$_G['setting']['extcredits']['$i']['unit'] : '<input type="checkbox" disabled  class="radio">';
				}
				$creditsvalue = array_merge(array('<input type="radio" name="updatecredittype" id="updatecredittype0" value="0" class="radio" onclick="var extcredits = new Array('.$js_extcreditids.'); for(k in extcredits) {$(\'resetextcredits[\'+extcredits[k]+\']\').disabled = true; $(\'addextcredits[\'+extcredits[k]+\']\').disabled = false;}" checked="checked" /><label for="updatecredittype0">'.$lang['members_reward_value'].'</label>'), $creditsvalue);
				$resetcredits = array_merge(array('<input type="radio" name="updatecredittype" id="updatecredittype1" value="1" class="radio" onclick="var extcredits = new Array('.$js_extcreditids.'); for(k in extcredits) {$(\'addextcredits[\'+extcredits[k]+\']\').disabled = true; $(\'resetextcredits[\'+extcredits[k]+\']\').disabled = false;}" /><label for="updatecredittype1">'.$lang['members_reward_clean'].'</label>'), $resetcredits);

				showtablerow('class="first"', array('class="th11"'), array(
					cplang('members_reward_members'),
					cplang('members_search_result', array('membernum' => $membernum))."<a href=\"###\" onclick=\"$('searchmembers').style.display='';$('reward').style.display='none';$('step1').className='current';$('step2').className='';\" class=\"act\">$lang[research]</a>"
				));

				echo '<tr><td class="th12">'.cplang('nav_members_reward').'</td><td>';
				showtableheader('', 'noborder');
				showsubtitle($creditscols);
				showtablerow('', array('class="td23"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $creditsvalue);
				showtablerow('', array('class="td23"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $resetcredits);
				showtablefooter();
				echo '</td></tr>';
				showtagheader('tbody', 'messagebody');
				shownewsletter();
				showtagfooter('tbody');
				showsubmit('rewardsubmit', 'submit', 'td', '<input class="checkbox" type="checkbox" name="notifymember" value="1" onclick="$(\'messagebody\').style.display = this.checked ? \'\' : \'none\'" id="credits_notify" /><label for="credits_notify">'.cplang('members_reward_notify').'</label>');

			}

			showtablefooter();
			showformfooter();
			showtagfooter('div');

		}

	} else {
		if(!empty($_POST['conditions'])) $search_condition = unserialize(stripslashes($_POST['conditions']));
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('reward', 'creditsnotify');

	}

} elseif($operation == 'confermedal') {

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
		cpmsg('members_edit_medals_nonexistence', 'action=medals', 'error');
	}

	if(!submitcheck('confermedalsubmit', 1)) {

		shownav('user', 'nav_members_confermedal');
		showsubmenusteps('nav_members_confermedal', array(
			array('nav_members_select', !$_G['gp_submit']),
			array('nav_members_confermedal', $_G['gp_submit']),
		));

		showsearchform('confermedal');

		if(submitcheck('submit', 1)) {

			$membernum = countmembers($search_condition, $urladd);

			showtagheader('div', 'confermedal', TRUE);
			showformheader('members&operation=confermedal'.$urladd);
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
		if(!empty($_POST['conditions'])) $search_condition = unserialize(stripslashes($_POST['conditions']));
		$membernum = countmembers($search_condition, $urladd);
		notifymembers('confermedal', 'medalletter');

	}

} elseif($operation == 'add') {

	if(!submitcheck('addsubmit', 1)) {

		$groupselect = array();
		$query = DB::query("SELECT groupid, type, grouptitle, creditshigher, radminid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditshigher='0' OR (groupid NOT IN ('5', '6', '7') AND radminid<>'1' AND type<>'member') ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		while($group = DB::fetch($query)) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			if($group['type'] == 'member' && $group['creditshigher'] == 0) {
				$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" selected>$group[grouptitle]</option>\n";
			} else {
				$groupselect[$group['type']] .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
		}
		$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';
		shownav('user', 'nav_members_add');
		showsubmenu('members_add');
		showformheader('members&operation=add');
		showtableheader();
		showsetting('username', 'newusername', '', 'text');
		showsetting('password', 'newpassword', '', 'text');
		showsetting('email', 'newemail', '', 'text');
		showsetting('usergroup', '', '', '<select name="newgroupid">'.$groupselect.'</select>');
		showsetting('members_add_email_notify', 'emailnotify', '', 'radio');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$newusername = trim($_G['gp_newusername']);
		$newpassword = trim($_G['gp_newpassword']);
		$newemail = trim($_G['gp_newemail']);

		if(!$newusername || !isset($_G['gp_confirmed']) && !$newpassword || !isset($_G['gp_confirmed']) && !$newemail) {
			cpmsg('members_add_invalid', '', 'error');
		}

		if(DB::result_first("SELECT count(*) FROM ".DB::table('common_member')." WHERE username='$newusername'")) {
			cpmsg('members_add_username_duplicate', '', 'error');
		}

		loaducenter();

		$uid = uc_user_register($newusername, $newpassword, $newemail);
		if($uid <= 0) {
			if($uid == -1) {
				cpmsg('members_add_illegal', '', 'error');
			} elseif($uid == -2) {
				cpmsg('members_username_protect', '', 'error');
			} elseif($uid == -3) {
				if(empty($_G['gp_confirmed'])) {
					cpmsg('members_add_username_activation', 'action=members&operation=add&addsubmit=yes&newgroupid='.$_G['gp_newgroupid'].'&newusername='.rawurlencode($newusername), 'form');
				} else {
					list($uid,, $newemail) = uc_get_user($newusername);
				}
			} elseif($uid == -4) {
				cpmsg('members_email_illegal', '', 'error');
			} elseif($uid == -5) {
				cpmsg('members_email_domain_illegal', '', 'error');
			} elseif($uid == -6) {
				cpmsg('members_email_duplicate', '', 'error');
			} else {
				cpmsg('undefined_action', '', 'error');
			}
		}

		$query = DB::query("SELECT groupid, radminid, type FROM ".DB::table('common_usergroup')." WHERE groupid='$_G[gp_newgroupid]'");
		$group = DB::fetch($query);
		$newadminid = in_array($group['radminid'], array(1, 2, 3)) ? $group['radminid'] : ($group['type'] == 'special' ? -1 : 0);
		if($group['radminid'] == 1) {
			cpmsg('members_add_admin_none', '', 'error');
		}
		if(in_array($group['groupid'], array(5, 6, 7))) {
			cpmsg('members_add_ban_all_none', '', 'error');
		}

		$data = array(
			'uid' => $uid,
			'username' => $newusername,
			'password' => md5(random(10)),
			'email' => $newemail,
			'adminid' => $newadminid,
			'groupid' => $_G['gp_newgroupid'],
			'regdate' => $_G['timestamp'],
			'credits' => 0,
		);
		DB::insert('common_member', $data);
		DB::insert('common_member_profile', array('uid' => $uid));
		DB::insert('common_member_field_forum', array('uid' => $uid));
		DB::insert('common_member_field_home', array('uid' => $uid));
		DB::insert('common_member_status', array('uid' => $uid, 'regip' => 'Manual Acting', 'lastvisit' => $_G['timestamp'], 'lastactivity' => $_G['timestamp']));
		$profile = $verifyarr = array();
		loadcache('fields_register');
		$init_arr = explode(',', $_G['setting']['initcredits']);
		$count_data = array(
			'uid' => $uid,
			'extcredits1' => $init_arr[0],
			'extcredits2' => $init_arr[1],
			'extcredits3' => $init_arr[2],
			'extcredits4' => $init_arr[3],
			'extcredits5' => $init_arr[4],
			'extcredits6' => $init_arr[5],
			'extcredits7' => $init_arr[6],
			'extcredits8' => $init_arr[7]
			);
		DB::insert('common_member_count', $count_data);
		manyoulog('user', $uid, 'add');

		if($_G['gp_emailnotify']) {
			if(!function_exists('sendmail')) {
				include libfile('function/mail');
			}
			$add_member_subject = lang('email', 'add_member_subject');
			$add_member_message = lang('email', 'add_member_message', array(
				'newusername' => $newusername,
				'bbname' => $_G['setting']['bbname'],
				'adminusername' => $_G['member']['username'],
				'siteurl' => $_G['siteurl'],
				'newpassword' => $newpassword,
			));
			sendmail("$newusername <$newemail>", $add_member_subject, $add_member_message);
		}

		updatecache('setting');
		$newusername = dstripslashes($newusername);
		cpmsg('members_add_succeed', '', 'succeed', array('username' => $newusername, 'uid' => $uid));

	}

} elseif($operation == 'group') {

	if(empty($_G['gp_uid']) && empty($_G['gp_username'])) {
		cpmsg('members_nonexistence', 'action=members&operation=group'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : ''), 'form', array(), '<input type="text" name="username" value="" class="txt" />');
	} else {
		$condition = !empty($_G['gp_uid']) ? "m.uid='{$_G['gp_uid']}'" : "m.username='{$_G['gp_username']}'";
	}

	$member = DB::fetch_first("SELECT m.uid, m.username, m.adminid, m.groupid, m.groupexpiry, m.extgroupids, m.credits,
		mf.groupterms, u.type AS grouptype, u.grouptitle, u.radminid
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_field_forum')." mf ON mf.uid=m.uid
		LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=m.groupid
		WHERE $condition");

	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$checkadminid = array(($member['adminid'] >= 0 ? $member['adminid'] : 0) => 'checked');

		$member['groupterms'] = unserialize($member['groupterms']);

		if($member['groupterms']['main']) {
			$expirydate = dgmdate($member['groupterms']['main']['time'], 'Y-n-j');
			$expirydays = ceil(($member['groupterms']['main']['time'] - TIMESTAMP) / 86400);
			$selecteaid = array($member['groupterms']['main']['adminid'] => 'selected');
			$selectegid = array($member['groupterms']['main']['groupid'] => 'selected');
		} else {
			$expirydate = $expirydays = '';
			$selecteaid = array($member['adminid'] => 'selected');
			$selectegid = array(($member['grouptype'] == 'member' ? 0 : $member['groupid']) => 'selected');
		}

		$extgroups = $expgroups = '';
		$radmingids = 0;
		$extgrouparray = explode("\t", $member['extgroupids']);
		$groups = array('system' => '', 'special' => '', 'member' => '');
		$group = array('groupid' => 0, 'radminid' => 0, 'type' => '', 'grouptitle' => $lang['usergroups_system_0'], 'creditshigher' => 0, 'creditslower' => '0');
		$query = DB::query("SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower
			FROM ".DB::table('common_usergroup')." WHERE groupid NOT IN ('6', '7') ORDER BY creditshigher, groupid");
		do {
			if($group['groupid'] && !in_array($group['groupid'], array(4, 5, 6, 7, 8)) && ($group['type'] == 'system' || $group['type'] == 'special')) {
				$extgroups .= showtablerow('', array('class="td27"', 'style="width:70%"'), array(
					'<input class="checkbox" type="checkbox" name="extgroupidsnew[]" value="'.$group['groupid'].'" '.(in_array($group['groupid'], $extgrouparray) ? 'checked' : '').' id="extgid_'.$group['groupid'].'" /><label for="extgid_'.$group['groupid'].'"> '.$group['grouptitle'].'</label>',
					'<input type="text" class="txt" size="9" name="extgroupexpirynew['.$group['groupid'].']" value="'.(in_array($group['groupid'], $extgrouparray) && !empty($member['groupterms']['ext'][$group['groupid']]) ? dgmdate($member['groupterms']['ext'][$group['groupid']], 'Y-n-j') : '').'" onclick="showcalendar(event, this)" />'
				), TRUE);
			}
			if($group['groupid'] && $group['type'] == 'member' && !($member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) && $member['groupid'] != $group['groupid']) {
				continue;
			}

			$expgroups .= '<option name="expgroupidnew" value="'.$group['groupid'].'" '.$selectegid[$group['groupid']].'>'.$group['grouptitle'].'</option>';

			if($group['groupid'] != 0) {
				$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
				$groups[$group['type']] .= '<option value="'.$group['groupid'].'"'.($member['groupid'] == $group['groupid'] ? 'selected="selected"' : '').' gtype="'.$group['type'].'">'.$group['grouptitle'].'</option>';
				if($group['type'] == 'special' && !$group['radminid']) {
					$radmingids .= ','.$group['groupid'];
				}
			}

		} while($group = DB::fetch($query));

		if(!$groups['member']) {
			$group = DB::fetch_first("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditshigher>='0' ORDER BY creditshigher LIMIT 1");
			$groups['member'] = '<option value="'.$group['groupid'].'" gtype="member">'.$group['grouptitle'].'</option>';
		}

		shownav('user', 'members_group');
		showsubmenu('members_group_member', array(), '', array('username' => $member['username']));
		echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
		showformheader("members&operation=group&uid=$member[uid]");
		showtableheader('usergroup', 'nobottom');
		showsetting('members_group_group', '', '', '<select name="groupidnew" onchange="if(in_array(this.value, ['.$radmingids.'])) {$(\'relatedadminid\').style.display = \'\';$(\'adminidnew\').name=\'adminidnew[\' + this.value + \']\';} else {$(\'relatedadminid\').style.display = \'none\';$(\'adminidnew\').name=\'adminidnew[0]\';}"><optgroup label="'.$lang['usergroups_system'].'">'.$groups['system'].'<optgroup label="'.$lang['usergroups_special'].'">'.$groups['special'].'<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groups['specialadmin'].'<optgroup label="'.$lang['usergroups_member'].'">'.$groups['member'].'</select>');
		showtagheader('tbody', 'relatedadminid', $member['grouptype'] == 'special' && !$member['radminid'], 'sub');
		showsetting('members_group_related_adminid', '', '', '<select id="adminidnew" name="adminidnew['.$member['groupid'].']"><option value="0"'.($member['adminid'] == 0 ? ' selected' : '').'>'.$lang['none'].'</option><option value="3"'.($member['adminid'] == 3 ? ' selected' : '').'>'.$lang['usergroups_system_3'].'</option><option value="2"'.($member['adminid'] == 2 ? ' selected' : '').'>'.$lang['usergroups_system_2'].'</option><option value="1"'.($member['adminid'] == 1 ? ' selected' : '').'>'.$lang['usergroups_system_1'].'</option></select>');
		showtagfooter('tbody');
		showsetting('members_group_validity', 'expirydatenew', $expirydate, 'calendar');
		showsetting('members_group_orig_adminid', '', '', '<select name="expgroupidnew">'.$expgroups.'</select>');
		showsetting('members_group_orig_groupid', '', '', '<select name="expadminidnew"><option value="0" '.$selecteaid[0].'>'.$lang['usergroups_system_0'].'</option><option value="1" '.$selecteaid[1].'>'.$lang['usergroups_system_1'].'</option><option value="2" '.$selecteaid[2].'>'.$lang['usergroups_system_2'].'</option><option value="3" '.$selecteaid[3].'>'.$lang['usergroups_system_3'].'</option></select>');
		showtablefooter();

		showtableheader('members_group_extended', 'noborder fixpadding');
		showsubtitle(array('usergroup', 'validity'));
		echo $extgroups;
		showtablerow('', 'colspan="2"', cplang('members_group_extended_comment'));
		showtablefooter();

		showtableheader('members_edit_reason', 'notop');
		showsetting('members_group_ban_reason', 'reason', '', 'textarea');
		showsubmit('editsubmit');
		showtablefooter();

		showformfooter();

	} else {

		$group = DB::fetch_first("SELECT groupid, radminid, type FROM ".DB::table('common_usergroup')." WHERE groupid='$_G[gp_groupidnew]'");
		if(!$group) {
			cpmsg('undefined_action', '', 'error');
		}

		if(strlen(is_array($_G['gp_extgroupidsnew']) ? implode("\t", $_G['gp_extgroupidsnew']) : '') > 60) {
			cpmsg('members_edit_groups_toomany', '', 'error');
		}

		$_G['gp_adminidnew'] = $_G['gp_adminidnew'][$_G['gp_groupidnew']];
		switch($group['type']) {
			case 'member':
				$_G['gp_groupidnew'] = in_array($_G['gp_adminidnew'], array(1, 2, 3)) ? $_G['gp_adminidnew'] : $_G['gp_groupidnew'];
				break;
			case 'special':
				if($group['radminid']) {
					$_G['gp_adminidnew'] = $group['radminid'];
				} elseif(!in_array($_G['gp_adminidnew'], array(1, 2, 3))) {
					$_G['gp_adminidnew'] = -1;
				}
				break;
			case 'system':
				$_G['gp_adminidnew'] = in_array($_G['gp_groupidnew'], array(1, 2, 3)) ? $_G['gp_groupidnew'] : -1;
				break;
		}

		$groupterms = array();

		if($_G['gp_expirydatenew']) {

			$maingroupexpirynew = strtotime($_G['gp_expirydatenew']);

			$group = DB::fetch_first("SELECT groupid, radminid, type FROM ".DB::table('common_usergroup')." WHERE groupid='$_G[gp_expgroupidnew]'");
			if(!$group) {
				$_G['gp_expgroupidnew'] = in_array($_G['gp_expadminidnew'], array(1, 2, 3)) ? $_G['gp_expadminidnew'] : $_G['gp_expgroupidnew'];
			} else {
				switch($group['type']) {
					case 'special':
						if($group['radminid']) {
							$_G['gp_expadminidnew'] = $group['radminid'];
						} elseif(!in_array($_G['gp_expadminidnew'], array(1, 2, 3))) {
							$_G['gp_expadminidnew'] = -1;
						}
						break;
					case 'system':
						$_G['gp_expadminidnew'] = in_array($_G['gp_expgroupidnew'], array(1, 2, 3)) ? $_G['gp_expgroupidnew'] : -1;
						break;
				}
			}

			if($_G['gp_expgroupidnew'] == $_G['gp_groupidnew']) {
				cpmsg('members_edit_groups_illegal', '', 'error');
			} elseif($maingroupexpirynew > TIMESTAMP) {
				if($_G['gp_expgroupidnew'] || $_G['gp_expadminidnew']) {
					$groupterms['main'] = array('time' => $maingroupexpirynew, 'adminid' => $_G['gp_expadminidnew'], 'groupid' => $_G['gp_expgroupidnew']);
				} else {
					$groupterms['main'] = array('time' => $maingroupexpirynew);
				}
				$groupterms['ext'][$_G['gp_groupidnew']] = $maingroupexpirynew;
			}

		}

		if(is_array($_G['gp_extgroupexpirynew'])) {
			foreach($_G['gp_extgroupexpirynew'] as $extgroupid => $expiry) {
				if(is_array($_G['gp_extgroupidsnew']) && in_array($extgroupid, $_G['gp_extgroupidsnew']) && !isset($groupterms['ext'][$extgroupid]) && $expiry && ($expiry = strtotime($expiry)) > TIMESTAMP) {
					$groupterms['ext'][$extgroupid] = $expiry;
				}
			}
		}

		$grouptermsnew = addslashes(serialize($groupterms));
		$groupexpirynew = groupexpiry($groupterms);
		$extgroupidsnew = $_G['gp_extgroupidsnew'] && is_array($_G['gp_extgroupidsnew']) ? implode("\t", $_G['gp_extgroupidsnew']) : '';

		DB::query("UPDATE ".DB::table('common_member')." SET groupid='{$_G['gp_groupidnew']}', adminid='{$_G['gp_adminidnew']}', extgroupids='$extgroupidsnew', groupexpiry='$groupexpirynew' WHERE uid='$member[uid]'");
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_field_forum')." WHERE uid='$member[uid]'")) {
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='$grouptermsnew' WHERE uid='$member[uid]'");
		} else {
			DB::insert('common_member_field_forum', array('uid' => $member['uid'], 'groupterms' => $grouptermsnew));
		}

		if($_G['gp_groupidnew'] != $member['groupid'] && (in_array($_G['gp_groupidnew'], array(4, 5)) || in_array($member['groupid'], array(4, 5)))) {
			$my_opt = in_array($_G['gp_groupidnew'], array(4, 5)) ? 'banuser' : 'unbanuser';
			my_thread_log($my_opt, array('uid' => $member['uid']));
			banlog($member['username'], $member['groupid'], $_G['gp_groupidnew'], $groupexpirynew, $_G['gp_reason']);
		}

		cpmsg('members_edit_groups_succeed', "action=members&operation=group&uid=$member[uid]", 'succeed');

	}

} elseif($operation == 'credit' && $_G['setting']['extcredits']) {

	if(empty($_G['gp_uid']) && empty($_G['gp_username'])) {
		cpmsg('members_nonexistence', 'action=members&operation=credit'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : ''), 'form', array(), '<input type="text" name="username" value="" class="txt" />');
	} else {
		$condition = !empty($_G['gp_uid']) ? "m.uid='{$_G['gp_uid']}'" : "m.username='{$_G['gp_username']}'";
	}

	$member = DB::fetch_first("SELECT m.*, mc.*, u.grouptitle, u.type, u.creditslower, u.creditshigher
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_count')." mc ON m.uid=mc.uid
		LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=m.groupid
		WHERE $condition");
	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}

	if(!submitcheck('creditsubmit')) {

		eval("\$membercredit = @round({$_G[setting][creditsformula]});");

		if($jscreditsformula = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='creditsformula'")) {
			$jscreditsformula = str_replace(array('digestposts', 'posts', 'threads'), array($member['digestposts'], $member['posts'],$member['threads']), $jscreditsformula);
		}

		$creditscols = array('members_credit_ranges', 'credits');
		$creditsvalue = array($member['type'] == 'member' ? "$member[creditshigher]~$member[creditslower]" : 'N/A', '<input type="text" class="txt" name="jscredits" id="jscredits" value="'.$membercredit.'" size="3" disabled>');
		for($i = 1; $i <= 8; $i++) {
			$jscreditsformula = str_replace('extcredits'.$i, "extcredits[$i]", $jscreditsformula);
			$creditscols[] = isset($_G['setting']['extcredits'][$i]) ? $_G['setting']['extcredits'][$i]['title'] : 'extcredits'.$i;
			$creditsvalue[] = isset($_G['setting']['extcredits'][$i]) ? '<input type="text" class="txt" size="3" name="extcreditsnew['.$i.']" id="extcreditsnew['.$i.']" value="'.$member['extcredits'.$i].'" onkeyup="membercredits()"> '.$_G['setting']['extcredits']['$i']['unit'] : '<input type="text" class="txt" size="3" value="N/A" disabled>';
		}

		echo <<<EOT
<script language="JavaScript">
	var extcredits = new Array();
	function membercredits() {
		var credits = 0;
		for(var i = 1; i <= 8; i++) {
			e = $('extcreditsnew['+i+']');
			if(e && parseInt(e.value)) {
				extcredits[i] = parseInt(e.value);
			} else {
				extcredits[i] = 0;
			}
		}
		$('jscredits').value = Math.round($jscreditsformula);
	}
</script>
EOT;
		shownav('user', 'members_credit');
		showsubmenu('members_credit');
		showtips('members_credit_tips');
		showformheader("members&operation=credit&uid={$_G['gp_uid']}");
		showtableheader(cplang('members_credit').' - '.$member['username']."($member[grouptitle])", 'nobottom');
		showsubtitle($creditscols);
		showtablerow('', array('', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $creditsvalue);
		showtablefooter();
		showtableheader('', 'notop');
		showtitle('members_edit_reason');
		showsetting('members_credit_reason', 'reason', '', 'textarea');
		showsubmit('creditsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$diffarray = array();
		$sql = $comma = '';
		if(is_array($_G['gp_extcreditsnew'])) {
			foreach($_G['gp_extcreditsnew'] as $id => $value) {
				if($member['extcredits'.$id] != ($value = intval($value))) {
					$diffarray[$id] = $value - $member['extcredits'.$id];
					$sql .= $comma."extcredits$id='$value'";
					$comma = ', ';
				}
			}
		}

		if($diffarray) {
			if(empty($_G['gp_reason'])) {
				cpmsg('members_edit_reason_invalid', '', 'error');
			}

			foreach($diffarray as $id => $diff) {
				$logs[] = dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[adminid]\t$member[username]\t$id\t$diff\t0\t\t{$_G['gp_reason']}");
			}
			updatemembercount($_G['gp_uid'], $diffarray);
			writelog('ratelog', $logs);
		}

		cpmsg('members_edit_credits_succeed', "action=members&operation=credit&uid={$_G['gp_uid']}", 'succeed');

	}

} elseif($operation == 'medal') {

	if(empty($_G['gp_uid']) && empty($_G['gp_username'])) {
		cpmsg('members_nonexistence', 'action=members&operation=medal'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : ''), 'form', array(), '<input type="text" name="username" value="" class="txt" />');
	} else {
		$condition = !empty($_G['gp_uid']) ? "m.uid='{$_G['gp_uid']}'" : "m.username='{$_G['gp_username']}'";
	}

	$member = DB::fetch_first("SELECT m.uid, m.username, mf.medals
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_field_forum')." mf ON m.uid=mf.uid
		WHERE $condition");

	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$membermedals = array();
		loadcache('medals');
		foreach (explode("\t", $member['medals']) as $key => $membermedal) {
			list($medalid, $medalexpiration) = explode("|", $membermedal);
			if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
				$membermedals[$key] = $medalid;
			} else {
				unset($membermedals[$key]);
			}
		}

		$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." WHERE available='1' ORDER BY displayorder");
		while($medal = DB::fetch($query)) {
			$medals .= showtablerow('', array('class="td25"', 'class="td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" ".(in_array($medal['medalid'], $membermedals) ? 'checked' : '')." />",
				"<img src=\"static/image/common/$medal[image]\" />",
				$medal['name']

			), TRUE);
		}

		if(!$medals) {
			cpmsg('members_edit_medals_nonexistence', '', 'error');
		}

		shownav('user', 'nav_members_confermedal');
		showsubmenu('nav_members_confermedal');
		showformheader("members&operation=medal&uid={$_G['gp_uid']}");
		showtableheader("$lang[members_confermedal_to] <a href='home.php?mod=space&uid={$_G['gp_uid']}' target='_blank'>$member[username]</a>", 'fixpadding');
		showsubtitle(array('medals_grant', 'medals_image', 'name'));
		echo $medals;
		showsubmit('medalsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$medalids = $comma = '';
		$medalsdel = $medalsadd = $medalsnew = $origmedalsarray = $medalsarray = array();
		if(is_array($_G['gp_medals'])) {
			foreach($_G['gp_medals'] as $medalid => $newgranted) {
				if($newgranted) {
					$medalsarray[] = $medalid;
					$medalids .= "$comma'$medalid'";
					$comma = ',';
				}
			}
		}
		loadcache('medals');
		foreach($member['medals'] = explode("\t", $member['medals']) as $key => $modmedalid) {
			list($medalid, $medalexpiration) = explode("|", $modmedalid);
			if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
				$origmedalsarray[] = $medalid;
			}
		}
		foreach(array_unique(array_merge($origmedalsarray, $medalsarray)) as $medalid) {
			if($medalid) {
				$orig = in_array($medalid, $origmedalsarray);
				$new = in_array($medalid, $medalsarray);
				if($orig != $new) {
					if($orig && !$new) {
						$medalsdel[] = $medalid;
					} elseif(!$orig && $new) {
						$medalsadd[] = $medalid;
					}
				}
			}
		}
		if(!empty($medalids)) {
			$query = DB::query("SELECT * FROM ".DB::table('forum_medal')." WHERE medalid IN ($medalids) ORDER BY displayorder");
			while($modmedal = DB::fetch($query)) {
				if(empty($modmedal['expiration'])) {
					$medalsnew[] = $modmedal[medalid];
					$medalstatus = 0;
				} else {
					$modmedal['expiration'] = TIMESTAMP + $modmedal['expiration'] * 86400;
					$medalsnew[] = $modmedal[medalid].'|'.$modmedal['expiration'];
					$medalstatus = 1;
				}
				if(in_array($modmedal['medalid'], $medalsadd)) {
					$data = array(
						'uid' => $_G['gp_uid'],
						'medalid' => $modmedal[medalid],
						'type' => 0,
						'dateline' => $_G[timestamp],
						'expiration' => $modmedal['expiration'],
						'status' => $medalstatus,
					);
					DB::insert('forum_medallog', $data);
				}
			}
		}
		if(!empty($medalsdel)) {
			DB::query("UPDATE ".DB::table('forum_medallog')." SET type='4' WHERE uid='{$_G['gp_uid']}' AND medalid IN (".implode(',', $medalsdel).")");
		}
		$medalsnew = implode("\t", $medalsnew);

		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='$medalsnew' WHERE uid='{$_G['gp_uid']}'");

		cpmsg('members_edit_medals_succeed', "action=members&operation=medal&uid={$_G['gp_uid']}", 'succeed');

	}

} elseif($operation == 'ban') {

	$member = array();
	if(!empty($_G['gp_username']) || !empty($_G['gp_uid'])) {
		$member = DB::fetch_first("SELECT m.*, mf.*, u.grouptitle, u.type AS grouptype, uf.allowsigbbcode, uf.allowsigimgcode FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_forum')." mf ON mf.uid=m.uid
			LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=m.groupid
			LEFT JOIN ".DB::table('common_usergroup_field')." uf ON uf.groupid=m.groupid
			WHERE ".($_G['gp_uid'] ? "m.uid='$_G[gp_uid]'" : "m.username='$_G[gp_username]'"));

		if(!$member) {
			cpmsg('members_edit_nonexistence', '', 'error');
		} elseif(($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['grouptype'] == 'special') {
			cpmsg('members_edit_illegal', '', 'error', array('grouptitle' => $member['grouptitle'], 'uid' => $member['uid']));
		}

		$member['groupterms'] = unserialize($member['groupterms']);
		$member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? dgmdate($member['groupterms']['main']['time'], 'Y-n-j') : '';
	}

	if(!submitcheck('bansubmit')) {

		echo '<script src="include/js/calendar.js" type="text/javascript"></script>';
		shownav('user', 'members_ban_user');
		showsubmenu($lang['members_ban_user'].($member['username'] ? ' - '.$member['username'] : ''));
		showtips('members_ban_tips');
		showformheader('members&operation=ban');
		showtableheader();
		showsetting('members_ban_username', 'username', $member['username'], 'text');
		if($member) {
			showtablerow('', 'class="td27" colspan="2"', cplang('members_edit_current_status').'<span class="normal">: '.($member['groupid'] == 4 ? $lang['members_ban_post'] : ($member['groupid'] == 5 ? $lang['members_ban_visit'] : ($member['status'] == -1 ? $lang['members_ban_status'] : $lang['members_ban_none']))).'</span>');
		}
		showsetting('members_ban_type', array('bannew', array(
			array('', $lang['members_ban_none']),
			array('post', $lang['members_ban_post']),
			array('visit', $lang['members_ban_visit']),
			array('status', $lang['members_ban_status'])
		)), '', 'mradio');
		showsetting('members_ban_validity', '', '', selectday('banexpirynew', array(0, 1, 3, 5, 7, 14, 30, 60, 90, 180, 365)));
		showsetting('members_ban_delpost', 'delpost', '', 'radio');
		showsetting('members_ban_deldoing', 'deldoing', '', 'radio');
		showsetting('members_ban_delblog', 'delblog', '', 'radio');
		showsetting('members_ban_delalbum', 'delalbum', '', 'radio');
		showsetting('members_ban_delshare', 'delshare', '', 'radio');
		showsetting('members_ban_delcomment', 'delcomment', '', 'radio');
		showsetting('members_ban_reason', 'reason', '', 'textarea');
		showsubmit('bansubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(empty($member)) {
			cpmsg('members_edit_nonexistence');
		}

		$sql = 'uid=uid';
		$reason = trim($_G['gp_reason']);
		if(!$reason && ($_G['group']['reasonpm'] == 1 || $_G['group']['reasonpm'] == 3)) {
			cpmsg('members_edit_reason_invalid', '', 'error');
		}

		if($_G['gp_bannew'] == 'post' || $_G['gp_bannew'] == 'visit') {
			$groupidnew = $_G['gp_bannew'] == 'post' ? 4 : 5;
			$_G['gp_banexpirynew'] = !empty($_G['gp_banexpirynew']) ? TIMESTAMP + $_G['gp_banexpirynew'] * 86400 : 0;
			$_G['gp_banexpirynew'] = $_G['gp_banexpirynew'] > TIMESTAMP ? $_G['gp_banexpirynew'] : 0;
			if($_G['gp_banexpirynew']) {
				$member['groupterms']['main'] = array('time' => $_G['gp_banexpirynew'], 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
				$member['groupterms']['ext'][$groupidnew] = $_G['gp_banexpirynew'];
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$sql .= ', groupexpiry=0';
			}
			$adminidnew = -1;
			$my_data = array('uid' => $member['uid'], 'expiry' => groupexpiry($member['groupterms']));
			if($_G['gp_delpost']) {
				$my_data['otherid'] = 1;
			}
			my_thread_log('banuser', $my_data);
		} elseif($member['groupid'] == 4 || $member['groupid'] == 5) {
			my_thread_log('unbanuser', array('uid' => $member['uid']));
			if(!empty($member['groupterms']['main']['groupid'])) {
				$groupidnew = $member['groupterms']['main']['groupid'];
				$adminidnew = $member['groupterms']['main']['adminid'];
				unset($member['groupterms']['main']);
				unset($member['groupterms']['ext'][$member['groupid']]);
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			}
			$groupidnew = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
			$adminidnew = 0;
		} else {
			$update = false;
			$groupidnew = $member['groupid'];
			$adminidnew = $member['adminid'];
		}

		$sql .= ", adminid='$adminidnew', groupid='$groupidnew', status='".($_G['gp_bannew'] == 'status' ? -1 : 0)."'";
		DB::query("UPDATE ".DB::table('common_member')." SET $sql WHERE uid='$member[uid]'");

		if($_G['group']['allowbanuser'] && (DB::affected_rows())) {
			banlog($member['username'], $member['groupid'], $groupidnew, $_G['gp_banexpirynew'], $reason, $_G['gp_bannew'] == 'status' ? -1 : 0);
		}

		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");

		if($_G['gp_bannew'] && $_G['adminid'] == 1) {
			require_once libfile('function/delete');
			if($_G['gp_delpost']) {
				$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE uid='$member[uid]'");
				while($attach = DB::fetch($query)) {
					dunlink($attach);
				}

				if($member['uid']) {
					require_once libfile('function/post');

					$pidsdelete = $tidsdelete = '0';
					$postarray = getfieldsofposts('pid, fid, tid, first', "authorid='{$member['uid']}'");
					foreach($postarray as $post) {
						$prune['forums'][] = $post['fid'];
						$prune['thread'][$post['tid']]++;
						if($post['first']) {
							$tidsdelete .= ",$post[tid]";
						}
						$pidsdelete .= ",$post[pid]";
					}
					deletepost("pid IN ($pidsdelete)");
					deletepost("tid IN ($tidsdelete)");
					deletethread("tid IN ($tidsdelete)");

					if(!empty($prune)) {
						foreach($prune['thread'] as $tid => $decrease) {
							updatethreadcount($tid);
						}
						foreach(array_unique($prune['forums']) as $fid) {
							updateforumcount($fid);
						}
					}

					if($_G['setting']['globalstick']) {
						updatecache('globalstick');
					}
				}
			}
			if($_G['gp_delblog']) {
				DB::query("DELETE FROM ".DB::table('home_blog')." WHERE uid='$member[uid]'");
				DB::query("DELETE FROM ".DB::table('home_blogfield')." WHERE uid='$member[uid]'");
				DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid='$member[uid]' AND idtype='blogid'");
			}
			if($_G['gp_delalbum']) {
				DB::query("DELETE FROM ".DB::table('home_album')." WHERE uid='$member[uid]'");
				$query = DB::query("SELECT filepath, thumb, remote FROM ".DB::table('home_pic')." WHERE uid='$member[uid]'");
				while ($value = DB::fetch($query)) {
					deletepicfiles($value);
				}

				DB::query("DELETE FROM ".DB::table('home_pic')." WHERE uid='$member[uid]'");
				DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid='$member[uid]' AND idtype='albumid'");
			}
			if($_G['gp_delshare']) {
				DB::query("DELETE FROM ".DB::table('home_share')." WHERE uid='$member[uid]'");
				DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid='$member[uid]' AND idtype='sid'");
			}

			if($_G['gp_deldoing']) {
				$doids = array();
				$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE uid='$member[uid]'");
				while ($value = DB::fetch($query)) {
					$doids[$value['doid']] = $value['doid'];
				}

				DB::query("DELETE FROM ".DB::table('home_doing')." WHERE uid='$member[uid]'");

				$delsql = !empty($doids) ? "doid IN (".dimplode($doids).") OR " : "";
				DB::query("DELETE FROM ".DB::table('home_docomment')." WHERE $delsql uid='$member[uid]'");
				DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid='$member[uid]' AND idtype='doid'");
			}
			if($_G['gp_delcomment']) {
				DB::query("DELETE FROM ".DB::table('home_comment')." WHERE uid='$member[uid]' OR authorid='$member[uid]' OR (id='$member[uid]' AND idtype='uid')");
			}
		}

		cpmsg('members_edit_succeed', 'action=members&operation=ban', 'succeed');

	}

} elseif($operation == 'access') {

	if(empty($_G['gp_uid']) && empty($_G['gp_username'])) {
		cpmsg('members_nonexistence', 'action=members&operation=access'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : ''), 'form', array(), '<input type="text" name="username" value="" class="txt" />');
	} else {
		$condition = !empty($_G['gp_uid']) ? "uid='{$_G['gp_uid']}'" : "username='{$_G['gp_username']}'";
	}

	$member = DB::fetch_first("SELECT username, adminid, groupid FROM ".DB::table('common_member')." WHERE $condition");
	if(!$member) {
		cpmsg('undefined_action', '', 'error');
	}

	require_once libfile('function/forumlist');
	$forumlist = '<SELECT name="addfid">'.forumselect(FALSE, 0, 0, TRUE).'</select>';

	loadcache('forums');

	if(!submitcheck('accesssubmit')) {

		shownav('user', 'members_access_edit');
		showsubmenu('members_access_edit');
		showtips('members_access_tips');
		showtableheader(cplang('members_access_now').' - '.$member['username'], 'nobottom fixpadding');
		showsubtitle(array('forum', 'members_access_view', 'members_access_post', 'members_access_reply', 'members_access_getattach', 'members_access_postattach', 'members_access_postimage', 'members_access_adminuser', 'members_access_dateline'));

		$accessmasks = array();
		$query = DB::query("SELECT a.*, m.username as adminusername FROM ".DB::table('forum_access')." a LEFT JOIN ".DB::table('common_member')." m ON a.adminuser=m.uid WHERE a.uid='$_G[gp_uid]'");
		while($access = DB::fetch($query)) {
			$accessmasks[$access['fid']] = $access;
			$accessmasks[$access['fid']]['dateline'] = $access['dateline'] ? dgmdate($access['dateline']) : '';
		}

		foreach ($accessmasks as $id => $access) {
			$forum = $_G['cache']['forums'][$id];
			showtablerow('', '', array(
					($forum['type'] == 'forum' ? '' : '|-----')."&nbsp;<a href=\"".ADMINSCRIPT."?action=forums&operation=edit&fid=$forum[fid]&anchor=perm\">$forum[name]</a>",
					accessimg($access['allowview']),
					accessimg($access['allowpost']),
					accessimg($access['allowreply']),
					accessimg($access['allowgetattach']),
					accessimg($access['allowpostattach']),
					accessimg($access['allowpostimage']),
					$access['adminusername'],
					$access['dateline'],
			));
		}

		if(empty($accessmasks)) {
			showtablerow('', '', array(
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
			));
		}

		showtablefooter();
		showformheader("members&operation=access&uid={$_G['gp_uid']}");
		showtableheader(cplang('members_access_add'), 'notop fixpadding');
		showsetting('members_access_add_forum', '', '', $forumlist);
		foreach(array('view', 'post', 'reply', 'getattach', 'postattach', 'postimage') as $perm) {
			showsetting('members_access_add_'.$perm, array('allow'.$perm.'new', array(
				array(0, cplang('default')),
				array(1, cplang('members_access_allowed')),
				array(-1, cplang('members_access_disallowed')),
			), TRUE), 0, 'mradio');
		}
		showsubmit('accesssubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$addfid = intval($_G['gp_addfid']);
		if($addfid && $_G['cache']['forums'][$addfid]) {
			$allowviewnew = !$_G['gp_allowviewnew'] ? 0 : ($_G['gp_allowviewnew'] > 0 ? 1 : -1);
			$allowpostnew = !$_G['gp_allowpostnew'] ? 0 : ($_G['gp_allowpostnew'] > 0 ? 1 : -1);
			$allowreplynew = !$_G['gp_allowreplynew'] ? 0 : ($_G['gp_allowreplynew'] > 0 ? 1 : -1);
			$allowgetattachnew = !$_G['gp_allowgetattachnew'] ? 0 : ($_G['gp_allowgetattachnew'] > 0 ? 1 : -1);
			$allowpostattachnew = !$_G['gp_allowpostattachnew'] ? 0 : ($_G['gp_allowpostattachnew'] > 0 ? 1 : -1);
			$allowpostimagenew = !$_G['gp_allowpostimagenew'] ? 0 : ($_G['gp_allowpostimagenew'] > 0 ? 1 : -1);

			if($allowviewnew == -1) {
				$allowpostnew = $allowreplynew = $allowgetattachnew = $allowpostattachnew = $allowpostimagenew = -1;
			} elseif($allowpostnew == 1 || $allowreplynew == 1 || $allowgetattachnew == 1 || $allowpostattachnew == 1 || $allowpostimagenew == 1) {
				$allowviewnew = 1;
			}

			if(!$allowviewnew && !$allowpostnew && !$allowreplynew && !$allowgetattachnew && !$allowpostattachnew && !$allowpostimagenew) {
				DB::query("DELETE FROM ".DB::table('forum_access')." WHERE uid='{$_G['gp_uid']}' AND fid='$addfid'");
				if(!DB::result_first("SELECT count(*) FROM ".DB::table('forum_access')." WHERE uid='$_G[gp_uid]'")) {
					DB::query("UPDATE ".DB::table('common_member')." SET accessmasks='0' WHERE uid='$_G[gp_uid]'");
				}
			} else {
				DB::query("REPLACE INTO ".DB::table('forum_access')." SET
					uid='{$_G['gp_uid']}', fid='$addfid', allowview='$allowviewnew',
					allowpost='$allowpostnew', allowreply='$allowreplynew', allowgetattach='$allowgetattachnew',
					allowpostattach='$allowpostattachnew', allowpostimage='$allowpostimagenew', adminuser='$_G[uid]', dateline='$_G[timestamp]'");
				DB::query("UPDATE ".DB::table('common_member')." SET accessmasks='1' WHERE uid='{$_G['gp_uid']}'");
			}
			updatecache('forums');

		}
		cpmsg('members_access_succeed', 'action=members&operation=access&uid='.$_G['gp_uid'], 'succeed');

	}

} elseif($operation == 'edit') {

	if(empty($_G['gp_uid']) && empty($_G['gp_username'])) {
		cpmsg('members_nonexistence', 'action=members&operation=edit'.(!empty($_G['gp_highlight']) ? "&highlight={$_G['gp_highlight']}" : ''), 'form', array(), '<input type="text" name="username" value="" class="txt" />');
	} else {
		$condition = !empty($_G['gp_uid']) ? "m.uid='{$_G['gp_uid']}'" : "m.username='{$_G['gp_username']}'";
	}

	$member = DB::fetch_first("SELECT m.*, mf.*, mc.*, mh.*, ms.*, mp.*, m.uid AS muid, u.type, uf.allowsigbbcode, uf.allowsigimgcode
		FROM ".DB::table('common_member')." m
		LEFT JOIN ".DB::table('common_member_field_forum')." mf ON mf.uid=m.uid
		LEFT JOIN ".DB::table('common_member_field_home')." mh ON mh.uid=m.uid
		LEFT JOIN ".DB::table('common_usergroup')." u ON u.groupid=m.groupid
		LEFT JOIN ".DB::table('common_usergroup_field')." uf ON uf.groupid=m.groupid
		LEFT JOIN ".DB::table('common_member_count')." mc ON mc.uid=m.uid
		LEFT JOIN ".DB::table('common_member_status')." ms ON ms.uid=m.uid
		LEFT JOIN ".DB::table('common_member_profile')." mp ON mp.uid=m.uid
		WHERE $condition");

	if(!$member) {
		cpmsg('members_edit_nonexistence', '', 'error');
	}
	$uid = $member['muid'];

	loadcache(array('profilesetting'));
	$fields = array();
	foreach($_G['cache']['profilesetting'] as $fieldid=>$field) {
		if($field['available']) {
			$_G['cache']['profilesetting'][$fieldid]['unchangeable'] = 0;
			$fields[$fieldid] = $field['title'];
		}
	}

	if(!submitcheck('editsubmit')) {

		require_once libfile('function/editor');

		$styleselect = "<select name=\"styleidnew\">\n<option value=\"\">$lang[use_default]</option>";
		$query = DB::query("SELECT styleid, name FROM ".DB::table('common_style'));
		while($style = DB::fetch($query)) {
			$styleselect .= "<option value=\"$style[styleid]\" ".($style['styleid'] == $member['styleid'] ? 'selected="selected"' : '').">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		$tfcheck = array($member['timeformat'] => 'checked');
		$gendercheck = array($member['gender'] => 'checked');
		$pscheck = array($member['pmsound'] => 'checked');

		$member['regdate'] = dgmdate($member['regdate'], 'Y-n-j h:i A');
		$member['lastvisit'] = dgmdate($member['lastvisit'], 'Y-n-j h:i A');

		$member['bio'] = html2bbcode($member['bio']);
		$member['signature'] = html2bbcode($member['sightml']);

		shownav('user', 'members_edit');
		showsubmenu("$lang[members_edit] - $member[username]");
		showformheader("members&operation=edit&uid=$uid", 'enctype');
		showtableheader();
		$status = array($member['status'] => ' checked');
		showsetting('members_edit_username', '', '', ' '.$member['username']);
		showsetting('members_edit_avatar', '', '', ' '.avatar($uid).'<br /><br /><input name="clearavatar" class="checkbox" type="checkbox" value="1" /> '.$lang['members_edit_avatar_clear']);
		showsetting('members_edit_statistics', '', '', "<a href=\"".ADMINSCRIPT."?action=prune&detail=1&users=$member[username]&searchsubmit=1&perpage=50\" class=\"act\">$lang[posts]($member[posts])</a>".
				"<a href=\"".ADMINSCRIPT."?action=doing&detail=1&users=$member[username]&searchsubmit=1&perpage=50\" class=\"act\">$lang[doings]($member[doings])</a>".
				"<a href=\"".ADMINSCRIPT."?action=blog&detail=1&users=$member[username]&searchsubmit=1&perpage=50\" class=\"act\">$lang[blogs]($member[blogs])</a>".
				"<a href=\"".ADMINSCRIPT."?action=album&detail=1&users=$member[username]&searchsubmit=1&perpage=50\" class=\"act\">$lang[albums]($member[albums])</a>".
				"<a href=\"".ADMINSCRIPT."?action=share&detail=1&users=$member[username]&searchsubmit=1&perpage=50\" class=\"act\">$lang[shares]($member[sharings])</a> <br>&nbsp;$lang[setting_styles_viewthread_userinfo_oltime]: $member[oltime]$lang[hourtime]");
		showsetting('members_edit_password', 'passwordnew', '', 'text');
		showsetting('members_edit_clearquestion', 'clearquestion', !$member['secques'], 'radio');
		showsetting('members_edit_status', 'statusnew', $member['status'], 'radio');
		showsetting('members_edit_email', 'emailnew', $member['email'], 'text');
		showsetting('members_edit_email_emailstatus', 'emailstatusnew', $member['emailstatus'], 'radio');
		showsetting('members_edit_posts', 'postsnew', $member['posts'], 'text');
		showsetting('members_edit_digestposts', 'digestpostsnew', $member['digestposts'], 'text');
		showsetting('members_edit_regip', 'regipnew', $member['regip'], 'text');
		showsetting('members_edit_regdate', 'regdatenew', $member['regdate'], 'text');
		showsetting('members_edit_lastvisit', 'lastvisitnew', $member['lastvisit'], 'text');
		showsetting('members_edit_lastip', 'lastipnew', $member['lastip'], 'text');
		showsetting('members_edit_addsize', 'addsizenew', $member['addsize'], 'text');
		showsetting('members_edit_addfriend', 'addfriendnew', $member['addfriend'], 'text');

		showsetting('members_edit_timeoffset', 'timeoffsetnew', $member['timeoffset'], 'text');
		showsetting('members_edit_invisible', 'invisiblenew', $member['invisible'], 'radio');

		showtitle('members_edit_option');
		showsetting('members_edit_cstatus', 'cstatusnew', $member['customstatus'], 'text');
		showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');

		if($fields) {
			showtitle('profilefields_fields');
			include_once libfile('function/profile');
			foreach($fields as $fieldid=>$fieldtitle) {
				$html = profile_setting($fieldid, $member);
				if($html) {
					showsetting($fieldtitle, '', '', $html);
				}
			}
		}

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		loaducenter();
		require_once libfile('function/discuzcode');

		$questionid = $_G['gp_clearquestion'] ? 0 : '';
		$ucresult = uc_user_edit($member['username'], $_G['gp_passwordnew'], $_G['gp_passwordnew'], $_G['gp_emailnew'], 1, $questionid);

		if($_G['gp_clearavatar']) {
			uc_user_deleteavatar($member['uid']);
		}

		$creditsnew = intval($creditsnew);

		$regdatenew = strtotime($_G['gp_regdatenew']);
		$lastvisitnew = strtotime($_G['gp_lastvisitnew']);

		$secquesadd = $_G['gp_clearquestion'] ? ", secques=''" : '';

		$signaturenew = censor($_G['gp_signaturenew']);
		$sigstatusnew = $signaturenew ? 1 : 0;
		$sightmlnew = addslashes(discuzcode(dstripslashes($signaturenew), 1, 0, 0, 0, ($member['allowsigbbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowsigimgcode'], 0));

		$oltimenew = round($_G['gp_totalnew'] / 60);

		$fieldadd = '';
		$fieldarr = array();
		include_once libfile('function/profile');
		foreach($_POST as $field_key=>$field_val) {
			if(isset($fields[$field_key]) && profile_check($field_key, $field_val)) {
				$fieldarr[$field_key] = "$field_key='".$field_val."'";
			}
		}
		if($_G['gp_deletefile'] && is_array($_G['gp_deletefile'])) {
			foreach($_G['gp_deletefile'] as $key => $value) {
				@unlink(getglobal('setting/attachdir').'./profile/'.$member[$key]);
				$fieldarr[$key] = "$key=''";
			}

		}
		if($_FILES) {
			require_once libfile('class/upload');
			$upload = new discuz_upload();

			foreach($_FILES as $key => $file) {
				$upload->init($file, 'profile');
				$attach = $upload->attach;

				if(!$upload->error()) {
					$upload->save();

					if(!$upload->get_image_info($attach['target'])) {
						@unlink($attach['target']);
						continue;
					}
					$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
					@unlink(getglobal('setting/attachdir').'./profile/'.$member[$key]);
					$fieldarr[$key] = "$key='".$attach['attachment']."'";
				}
			}
		}

		$emailadd = $ucresult < 0 ? '' : "email='$_G[gp_emailnew]', ";
		$passwordadd = $ucresult < 0 ? '' : ", password='".md5(random(10))."'";

		$addsize = intval($_G['gp_addsizenew']);
		$addfriend = intval($_G['gp_addfriendnew']);
		$status = intval($_G['gp_statusnew']) ? -1 : 0;
		$emailstatusnew = intval($_G['gp_emailstatusnew']);
		DB::query("UPDATE ".DB::table('common_member')." SET $emailadd regdate='$regdatenew', emailstatus='$emailstatusnew', status='$status', timeoffset='{$_G['gp_timeoffsetnew']}' $passwordadd WHERE uid='{$_G['gp_uid']}'");
		DB::query("UPDATE ".DB::table('common_member_field_home')." SET addsize='$addsize', addfriend='$addfriend' WHERE uid='{$_G['gp_uid']}'");
		DB::query("UPDATE ".DB::table('common_member_count')." SET posts='{$_G['gp_postsnew']}', digestposts='{$_G['gp_digestpostsnew']}' WHERE uid='{$_G['gp_uid']}'");
		DB::query("UPDATE ".DB::table('common_member_status')." SET regip='{$_G['gp_regipnew']}', lastvisit='$lastvisitnew', lastip='{$_G['gp_lastipnew']}', invisible='{$_G['gp_invisiblenew']}' WHERE uid='{$_G['gp_uid']}'");
		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET customstatus='{$_G['gp_cstatusnew']}', sightml='$sightmlnew' WHERE uid='{$_G['gp_uid']}'");
		if($fieldarr) {
			$fieldadd = implode(',', $fieldarr);
			DB::query("UPDATE ".DB::table('common_member_profile')." SET $fieldadd WHERE uid='$uid'");
		}


		manyoulog('user', $uid, 'update');
		cpmsg('members_edit_succeed', 'action=members&operation=edit&uid='.$uid, 'succeed');

	}

} elseif($operation == 'ipban') {

	if(!submitcheck('ipbansubmit')) {

		require_once libfile('function/misc');

		$iptoban = explode('.', getgpc('ip'));

		$ipbanned = '';
		$query = DB::query("SELECT * FROM ".DB::table('common_banned')." ORDER BY dateline");
		while($banned = DB::fetch($query)) {
			for($i = 1; $i <= 4; $i++) {
				if($banned["ip$i"] == -1) {
					$banned["ip$i"] = '*';
				}
			}
			$disabled = $_G['adminid'] != 1 && $banned['admin'] != $_G['member']['username'] ? 'disabled' : '';
			$banned['dateline'] = dgmdate($banned['dateline'], 'd');
			$banned['expiration'] = dgmdate($banned['expiration'], 'd');
			$theip = "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4]";
			$ipbanned .= showtablerow('', array('class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$banned[id]]\" value=\"$banned[id]\" $disabled />",
				$theip,
				convertip($theip, "./"),
				$banned[admin],
				$banned[dateline],
				"<input type=\"text\" class=\"txt\" size=\"10\" name=\"expirationnew[$banned[id]]\" value=\"$banned[expiration]\" $disabled />"
			), TRUE);
		}
		shownav('user', 'nav_members_ipban');
		showsubmenu('nav_members_ipban');
		showtips('members_ipban_tips');
		showformheader('members&operation=ipban');
		showtableheader();
		showsubtitle(array('', 'ip', 'members_ipban_location', 'operator', 'start_time', 'end_time'));
		echo $ipbanned;
		showtablerow('', array('', 'class="td28" colspan="3"', 'class="td28" colspan="2"'), array(
			$lang['add_new'],
			'<input type="text" class="txt" name="ip1new" value="'.$iptoban[0].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip2new" value="'.$iptoban[1].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip3new" value="'.$iptoban[2].'" size="3" maxlength="3">.<input type="text" class="txt" name="ip4new" value="'.$iptoban[3].'" size="3" maxlength="3">',
			$lang['validity'].': <input type="text" class="txt" name="validitynew" value="30" size="3"> '.$lang['days']
		));
		showsubmit('ipbansubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if(@$ids = dimplode($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('common_banned')." WHERE id IN ($ids) AND ('$_G[adminid]'='1' OR admin='$_G[username]')");
		}

		if($_G['gp_ip1new'] != '' && $_G['gp_ip2new'] != '' && $_G['gp_ip3new'] != '' && $_G['gp_ip4new'] != '') {
			$own = 0;
			$ip = explode('.', $_G['clientip']);
			for($i = 1; $i <= 4; $i++) {
				if(!is_numeric($_G['gp_ip'.$i.'new']) || $_G['gp_ip'.$i.'new'] < 0) {
					if($_G['adminid'] != 1) {
						cpmsg('members_ipban_nopermission', '', 'error');
					}
					$_G['gp_ip'.$i.'new'] = -1;
					$own++;
				} elseif($_G['gp_ip'.$i.'new'] == $ip[$i - 1]) {
					$own++;
				}
				$_G['gp_ip'.$i.'new'] = intval($_G['gp_ip'.$i.'new']);
			}

			if($own == 4) {
				cpmsg('members_ipban_illegal', '', 'error');
			}

			$query = DB::query("SELECT * FROM ".DB::table('common_banned'));
			while($banned = DB::fetch($query)) {
				$exists = 0;
				for($i = 1; $i <= 4; $i++) {
					if($banned["ip$i"] == -1) {
						$exists++;
					} elseif($banned["ip$i"] == ${"ip".$i."new"}) {
						$exists++;
					}
				}
				if($exists == 4) {
					cpmsg('members_ipban_invalid', '', 'error');
				}
			}

			$expiration = TIMESTAMP + $_G['gp_validitynew'] * 86400;

			DB::query("UPDATE ".DB::table('common_session')." SET groupid='6' WHERE ('$ip1new'='-1' OR ip1='$ip1new') AND ('$ip2new'='-1' OR ip2='$ip2new') AND ('$ip3new'='-1' OR ip3='$ip3new') AND ('$ip4new'='-1' OR ip4='$ip4new')");
			$data = array(
				'ip1' => $_G['gp_ip1new'],
				'ip2' => $_G['gp_ip2new'],
				'ip3' => $_G['gp_ip3new'],
				'ip4' => $_G['gp_ip4new'],
				'admin' => $_G['username'],
				'dateline' => $_G['timestamp'],
				'expiration' => $expiration,
			);
			DB::insert('common_banned', $data);

		}

		if(is_array($_G['gp_expirationnew'])) {
			foreach($_G['gp_expirationnew'] as $id => $expiration) {
				DB::query("UPDATE ".DB::table('common_banned')." SET expiration='".strtotime($expiration)."' WHERE id='$id' AND ('$_G[adminid]'='1' OR admin='$_G[username]')");
			}
		}

		updatecache('ipbanned');
		cpmsg('members_ipban_succeed', 'action=members&operation=ipban', 'succeed');

	}

} elseif($operation == 'profile') {

	$fieldid = $_G['gp_fieldid'] ? $_G['gp_fieldid'] : '';
	shownav('user', 'nav_members_profile');
	if($fieldid) {
		$_G['setting']['privacy'] = !empty($_G['setting']['privacy']) ? $_G['setting']['privacy'] : array();
		$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : unserialize($_G['setting']['privacy']);

		$field = DB::fetch_first("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE fieldid='$fieldid'");
		$fixedfields1 = array('uid', 'constellation', 'zodiac');
		$fixedfields2 = array('realname', 'gender', 'birthday', 'birthcity', 'residecity');
		$field['isfixed1'] = in_array($fieldid, $fixedfields1);
		$field['isfixed2'] = $field['isfixed1'] || in_array($fieldid, $fixedfields2);
		$field['customable'] = preg_match('/^field[1-8]$/i', $fieldid);

		$profilevalidate = array();
		include libfile('spacecp/profilevalidate', 'include');
		$field['validate'] = $field['validate'] ? $field['validate'] : ($profilevalidate[$fieldid] ? $profilevalidate[$fieldid] : '');
		if(!submitcheck('editsubmit')) {
			showsubmenu("$lang[members_profile_edit] - $field[title]");
			showformheader('members&operation=profile&fieldid='.$fieldid);
			showtableheader();
			if(1 || $field['customable']) {
				showsetting('members_profile_edit_name', 'title', $field['title'], 'text');
				showsetting('members_profile_edit_desc', 'description', $field['description'], 'text');
			} else {
				showsetting('members_profile_edit_name', '', '', ' '.$field['title']);
				showsetting('members_profile_edit_desc', '', '', ' '.$field['description']);
			}
			if(1 || !$field['isfixed2']) {
				showsetting('members_profile_edit_form_type', array('formtype', array(
						array('text', $lang['members_profile_edit_text'], array('valuenumber' => '', 'fieldchoices' => 'none', 'fieldvalidate'=>'')),
						array('textarea', $lang['members_profile_edit_textarea'], array('valuenumber' => '', 'fieldchoices' => 'none', 'fieldvalidate'=>'')),
						array('radio', $lang['members_profile_edit_radio'], array('valuenumber' => 'none', 'fieldchoices' => '', 'fieldvalidate'=>'none')),
						array('checkbox', $lang['members_profile_edit_checkbox'], array('valuenumber' => '', 'fieldchoices' => '', 'fieldvalidate'=>'none')),
						array('select', $lang['members_profile_edit_select'], array('valuenumber' => 'none', 'fieldchoices' => '', 'fieldvalidate'=>'none')),
						array('list', $lang['members_profile_edit_list'], array('valuenumber' => '', 'fieldchoices' => '')),
						array('file', $lang['members_profile_edit_file'], array('valuenumber' => 'none', 'fieldchoices' => 'none', 'fieldvalidate'=>'none'))
					)), $field['formtype'], 'mradio');
				showtagheader('tbody', 'valuenumber', !in_array($field['formtype'], array('file','radio', 'select')), 'sub');
				showsetting('members_profile_edit_value_number', 'size', $field['size'], 'text');
				showtagfooter('tbody');

				showtagheader('tbody', 'fieldchoices', !in_array($field['formtype'], array('file','text', 'textarea')), 'sub');
				showsetting('members_profile_edit_choices', 'choices', $field['choices'], 'textarea');
				showtagfooter('tbody');

				showtagheader('tbody', 'fieldvalidate', in_array($field['formtype'], array('text', 'textarea')), 'sub');
				showsetting('members_profile_edit_validate', 'validate', $field['validate'], 'text');
				showtagfooter('tbody');
			}
			if(!$field['isfixed1']) {
				showsetting('members_profile_edit_available', 'available', $field['available'], 'radio');
				showsetting('members_profile_edit_unchangeable', 'unchangeable', $field['unchangeable'], 'radio');
				showsetting('members_profile_edit_needverify', 'needverify', $field['needverify'], 'radio');
				showsetting('members_profile_edit_required', 'required', $field['required'], 'radio');
			}
			showsetting('members_profile_edit_showinregister', 'showinregister', $field['showinregister'], 'radio');
			showsetting('members_profile_edit_invisible', 'invisible', $field['invisible'], 'radio');
			$privacyselect = array(
				array('0', cplang('members_profile_edit_privacy_public')),
				array('1', cplang('members_profile_edit_privacy_friend')),
				array('3', cplang('members_profile_edit_privacy_secret'))
			);
			showsetting('members_profile_edit_default_privacy', array('privacy', $privacyselect), $_G['setting']['privacy']['profile'][$fieldid], 'select');
			showsetting('members_profile_edit_showincard', 'showincard', $field['showincard'], 'radio');
			showsetting('members_profile_edit_showinthread', 'showinthread', $field['showinthread'], 'radio');
			showsetting('members_profile_edit_allowsearch', 'allowsearch', $field['allowsearch'], 'radio');
			showsetting('members_profile_edit_display_order', 'displayorder', $field['displayorder'], 'text');
			showsubmit('editsubmit');
			showtablefooter();
			showformfooter();

		} else {

			$setarr = array(
				'invisible' => intval($_POST['invisible']),
				'showincard' => intval($_POST['showincard']),
				'showinthread' => intval($_POST['showinthread']),
				'showinregister' => intval($_POST['showinregister']),
				'allowsearch' => intval($_POST['allowsearch']),
				'displayorder' => intval($_POST['displayorder'])
			);
			if($field['customable'] || (!$field['customable'] && !empty($_POST['title']))) {
				$_POST['title'] = dhtmlspecialchars(trim($_POST['title']));
				if(empty($_POST['title'])) {
					cpmsg('members_profile_edit_title_empty_error', 'action=members&operation=profile&fieldid='.$fieldid, 'error');
				}
				$setarr['title'] = $_POST['title'];
				$setarr['description'] = dhtmlspecialchars(trim($_POST['description']));
			}
			if(!$field['isfixed1']) {
				$setarr['required'] = intval($_POST['required']);
				$setarr['available'] = intval($_POST['available']);
				$setarr['unchangeable'] = intval($_POST['unchangeable']);
				$setarr['needverify'] = intval($_POST['needverify']);
			}
			if(!$field['isfixed2'] || (!$field['customable'] && !empty($_POST['choices']))) {
				$setarr['formtype'] = strtolower(trim($_POST['formtype']));
				$setarr['size'] = intval($_POST['size']);
				if($_POST['choices']) {
					$_POST['choices'] = trim($_POST['choices']);
					$ops = explode("\n", $_POST['choices']);
					$parts = array();
					foreach ($ops as $op) {
						$parts[] = dhtmlspecialchars(trim($op));
					}
					$_POST['choices'] = implode("\n", $parts);
				}
				$setarr['choices'] = $_POST['choices'];
				if($_POST['validate'] && $_POST['validate'] != $profilevalidate[$fieldid]) {
					$setarr['validate'] = $_POST['validate'];
				} elseif(empty($_POST['validate'])) {
					$setarr['validate'] = '';
				}
			}
			DB::update('common_member_profile_setting', $setarr, array('fieldid'=>$fieldid));
			if($_GET['fieldid'] == 'birthday') {
				DB::update('common_member_profile_setting', $setarr, array('fieldid'=>'birthmonth'));
				DB::update('common_member_profile_setting', $setarr, array('fieldid'=>'birthyear'));
			} elseif($_GET['fieldid'] == 'birthcity') {
				DB::update('common_member_profile_setting', $setarr, array('fieldid'=>'birthprovince'));
			} elseif($_GET['fieldid'] == 'residecity') {
				DB::update('common_member_profile_setting', $setarr, array('fieldid'=>'resideprovince'));
				$setarr['required'] = 0;
				DB::update('common_member_profile_setting', $setarr, array('fieldid'=>'residedist'));
				DB::update('common_member_profile_setting', $setarr, array('fieldid'=>'residecommunity'));
			}
			require_once libfile('function/cache');
			updatecache(array('profilesetting','fields_required', 'fields_optional', 'fields_register'));
			if(!isset($_G['setting']['privacy']['profile']) || $_G['setting']['privacy']['profile'][$fieldid] != $_POST['privacy']) {
				$_G['setting']['privacy']['profile'][$fieldid] = $_POST['privacy'];
				DB::insert('common_setting', array('skey'=>'privacy', 'svalue'=> addslashes(serialize($_G['setting']['privacy']))), false, true);
				updatecache('setting');
			}
			cpmsg('members_profile_edit_succeed', 'action=members&operation=profile'.($fieldid ? '&fieldid='.$fieldid : ''), 'succeed');
		}
	} else {

		$query = DB::query("SELECT title, fieldid, description, available, formtype, displayorder FROM ".DB::table('common_member_profile_setting')." ORDER BY available DESC, displayorder");
		$list = array();
		while($value = DB::fetch($query)) {
			$list[$value['fieldid']] = $value;
		}

		unset($list['birthyear']);
		unset($list['birthmonth']);
		unset($list['resideprovince']);
		unset($list['birthprovince']);
		unset($list['residedist']);
		unset($list['residecommunity']);

		if(!submitcheck('ordersubmit')) {
			showsubmenu("$lang[members_profile_edit]");
			showtips('members_profile_tips');
			showformheader('members&operation=profile');
			showtableheader('members_profile', 'nobottom', 'id="porfiletable"');
			echo '<tr><th>'.$lang['members_profile_edit_name'].'</th><th>'.$lang['members_profile_edit_field'].'</th><th>'.$lang['members_profile_edit_field_desc'].'</th><th>'.$lang['members_profile_edit_available'].'</th><th>'.$lang['members_profile_edit_field_type'].'</th><th>'.$lang['members_profile_edit_display_order'].'</th></tr>';
			foreach($list as $key => $value) {
				$value['available'] = $value['available'] ? $lang['yes'] : $lang['no'];
				$value['formtype'] = $lang['members_profile_edit_'.$value['formtype']];
				$value['displayorder'] = '<input type="text" name="displayorder['.$value['fieldid'].']" value="'.$value['displayorder'].'" size="5">';
				$value['edit'] = '<a href="'.ADMINSCRIPT.'?action=members&operation=profile&fieldid='.$value['fieldid'].'" title="" class="act">'.$lang[edit].'</a>';
				showtablerow('', array('width="40" class="td22"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"', 'class="td28"'), $value);
			}
			showsubmit('ordersubmit');
			showtablefooter();
			showformfooter();
		} else {
			if(!empty($_G['gp_displayorder'])) {
				foreach ($_G['gp_displayorder'] as $fieldid => $value) {
					$orders = $list[$fieldid]['displayorder'];
					if($orders != $value) {
						DB::update('common_member_profile_setting', array('displayorder'=>intval($value)), array('fieldid'=>$fieldid));
					}
				}
			}
			require_once libfile('function/cache');
			updatecache(array('profilesetting','fields_required', 'fields_optional'));
			cpmsg('members_profile_edit_succeed', 'action=members&operation=profile', 'succeed');
		}
	}

} elseif($operation == 'stat') {

	if($_GET['do'] == 'stepstat' && $_GET['t'] > 0 && $_GET['i'] > 0) {
		$t = intval($_GET['t']);
		$i = intval($_GET['i']);
		$o = $i - 1;
		$table = ($_GET['fieldid'] == 'groupid') ? DB::table('common_member') : DB::table('common_member_profile');
		$value = DB::fetch_first('SELECT optionid, fieldvalue FROM '.DB::table('common_member_stat_field')." WHERE fieldid='$_GET[fieldid]' LIMIT $o, 1");
		$optionid = intval($value['optionid']);
		$fieldvalue = daddslashes($value['fieldvalue']);
		$cnt = DB::result_first("SELECT COUNT(*) as cnt FROM $table WHERE $_GET[fieldid] = '$fieldvalue'");
		DB::update('common_member_stat_field', array('users'=>$cnt, 'updatetime'=>TIMESTAMP), array('optionid'=>$optionid));
		if($i < $t) {
			cpmsg('members_stat_do_stepstat', 'action=members&operation=stat&fieldid='.$_GET['fieldid'].'&do=stepstat&t='.$t.'&i='.($i+1), '', array('t'=>$t, 'i'=>$i));
		} else {
			cpmsg('members_stat_update_data_succeed', 'action=members&operation=stat&fieldid='.$_GET['fieldid'], 'succeed');
		}
	}

	$options = array('groupid'=>cplang('usergroup'));
	$fieldids = array('gender', 'birthyear', 'birthmonth', 'constellation', 'zodiac','birthprovince', 'resideprovince');
	loadcache('profilesetting');
	foreach($_G['cache']['profilesetting'] as $fieldid=>$value) {
		if($value['formtype']=='select'||$value['formtype']=='radio'||in_array($fieldid,$fieldids)) {
			$options[$fieldid] = $value['title'];
		}
	}

	if(!empty($_GET['fieldid']) && !isset($options[$_GET['fieldid']])) {
		cpmsg('members_stat_bad_fieldid', 'action=members&operation=stat', 'error');
	}

	if(!empty($_GET['fieldid']) && $_GET['fieldid'] == 'groupid') {
		$usergroups = array();
		$query = DB::query('SELECT groupid, grouptitle FROM '.DB::table('common_usergroup'));
		while($value=DB::fetch($query)) {
			$usergroups[$value['groupid']] = $value['grouptitle'];
		}
	}

	if(!submitcheck('statsubmit')) {

		shownav('user', 'nav_members_stat');
		showsubmenu('nav_members_stat');
		showtips('members_stat_tips');

		showformheader('members&operation=stat&fieldid='.$_GET['fieldid']);
		showtableheader('members_stat_options');
		$option_html = '<ul>';
		foreach($options as $key=>$value) {
			$extra_style = $_GET['fieldid'] == $key ? ' font-weight: 900;' : '';
			$option_html .= ""
				."<li style=\"float: left; width: 160px;$extra_style\">"
				. "<a href=\"".ADMINSCRIPT."?action=members&operation=stat&fieldid=$key\">$value</a>"
				. "</li>";
		}
		$option_html .= '</ul><br style="clear: both;" />';
		showtablerow('', array('colspan="5"'), array($option_html));

		if($_GET['fieldid']) {

			$list = array();
			$query = DB::query('SELECT * FROM '.DB::table('common_member_stat_field')." WHERE fieldid = '$_GET[fieldid]'");
			$total = 0;
			while($value = DB::fetch($query)) {
				$list[] = $value;
				$total += $value['users'];
			}
			for($i=0, $L=count($list); $i<$L; $i++) {
				if($total) {
					$list[$i]['percent'] = intval(10000 * $list[$i]['users'] / $total) / 100;
				} else {
					$list[$i]['percent'] = 0;
				}
				$list[$i]['width'] = $list[$i]['percent'] ? intval($list[$i]['percent'] * 2) : 1;
			}
			showtablerow('', array('colspan="4"'), array(cplang('members_stat_current_field').$options[$_GET['fieldid']].'; '.cplang('members_stat_members').$total));

			showtablerow('', array('width="200"', '', 'width="160"', 'width="160"'),array(
					cplang('members_stat_option'),
					cplang('members_stat_view'),
					cplang('members_stat_option_members'),
					cplang('members_stat_updatetime')
				));
			foreach($list as $value) {
				if($_GET['fieldid']=='groupid') {
					$value['fieldvalue'] = $usergroups[$value['fieldvalue']];
				} elseif($_GET['fieldid']=='gender') {
					$value['fieldvalue'] = lang('space', 'gender_'.$value['fieldvalue']);
				} elseif(empty($value['fieldvalue'])) {
					$value['fieldvalue'] = cplang('members_stat_null_fieldvalue');
				}
				showtablerow('', array('width="200"', '', 'width="160"', 'width="160"'),array(
					$value['fieldvalue'],
					'<div style="background-color: yellow; width: 200px; height: 20px;"><div style="background-color: red; height: 20px; width: '.$value['width'].'px;"></div></div>',
					$value['users'].' ('.$value['percent'].'%)',
					!empty($value['updatetime']) ? dgmdate($value['updatetime'], 'u') : 'N/A'
				));
			}

			showtablefooter();
			$optype_html = '<input type="radio" name="optype" id="optype_option" value="option" /><label for="optype_option">'.cplang('members_stat_update_option').'</label>&nbsp;&nbsp;'
					.'<input type="radio" name="optype" id="optype_data" value="data" /><label for="optype_data">'.cplang('members_stat_update_data').'</label>';
			showsubmit('statsubmit', 'submit', $optype_html);
			showformfooter();

		} else {
			showtablefooter();
			showformfooter();
		}

	} else {

		if($_POST['optype'] == 'option') {

			$options = $inserts = $hits = $deletes = array();
			$query = DB::query('SELECT optionid, fieldvalue FROM '.DB::table('common_member_stat_field')." WHERE fieldid = '$_GET[fieldid]'");
			while($value = DB::fetch($query)) {
				$options[$value['optionid']] = $value['fieldvalue'];
				$hits[$value['optionid']] = false;
			}

			if($_GET['fieldid'] == 'groupid'){
				$sql = "SELECT DISTINCT(groupid) FROM ".DB::table('common_member');
			} else {
				$sql = "SELECT DISTINCT($_GET[fieldid]) FROM ".DB::table('common_member_profile');
			}
			$query = DB::query($sql);
			while($value = DB::fetch($query)) {
				$fieldvalue = $value[$_GET[fieldid]];
				$optionid = array_search($fieldvalue, $options);
				if($optionid) {
					$hits[$optionid] = true;
				} else {
					$inserts[] = "('$_GET[fieldid]', '".daddslashes($fieldvalue)."')";
				}
			}
			foreach ($hits as $key=>$value) {
				if($value == false) {
					$deletes[] = $key;
				}
			}
			if($deletes) {
				DB::query('DELETE FROM '.DB::table('common_member_stat_field')." WHERE fieldid = '$_GET[fieldid]' AND optionid IN (".dimplode($deletes).")");
			}
			if($inserts) {
				DB::query('INSERT INTO '.DB::table('common_member_stat_field')."(fieldid, fieldvalue) VALUES ".implode(', ', $inserts));
			}

			cpmsg('members_stat_update_option_succeed', 'action=members&operation=stat&fieldid='.$_GET['fieldid'], 'succeed');

		} elseif($_POST['optype'] == 'data') {

			$table = ($_GET['fieldid'] == 'groupid') ? DB::table('common_member') : DB::table('common_member_profile');
			$t = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_member_stat_field')." WHERE fieldid='$_GET[fieldid]'");
			if($t > 0) {
				cpmsg('members_stat_do_stepstat_prepared', 'action=members&operation=stat&fieldid='.$_GET['fieldid'].'&do=stepstat&t='.$t.'&i=1', '', array('t'=>$t));
			} else {
				cpmsg('members_stat_update_data_succeed', 'action=members&operation=stat&fieldid='.$_GET['fieldid'], 'succeed');
			}

		} else {
			cpmsg('members_stat_null_operation', 'action=members&operation=stat', 'error');
		}
	}
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
	showformheader("members&operation=$operation", "onSubmit=\"if($('updatecredittype1') && $('updatecredittype1').checked && !window.confirm('$lang[members_reward_clean_alarm]')){return false;} else {return true;}\"");
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
//			$value['choices'] = explode("\n",$value['choices']);
			$value['choices'] = is_array($value['choices']) ? $value['choices'] : explode("\n",$value['choices']);
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

function searchmembers($condition, $limit=2000, $start=0) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->search($condition, $limit, $start);
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

		if($operation == 'reward') {

			$updatesql = '';
			if($_G['gp_updatecredittype'] == 0) {
				if(is_array($_G['gp_addextcredits']) && !empty($_G['gp_addextcredits'])) {
					foreach($_G['gp_addextcredits'] as $key => $value) {
						$value = intval($value);
						if(isset($_G['setting']['extcredits'][$key]) && !empty($value)) {
							$updatesql .= ", extcredits{$key}=extcredits{$key}+($value)";
						}
					}
				}
			} else {
				if(is_array($_G['gp_resetextcredits']) && !empty($_G['gp_resetextcredits'])) {
					foreach($_G['gp_resetextcredits'] as $key => $value) {
						$value = intval($value);
						if(isset($_G['setting']['extcredits'][$key]) && !empty($value)) {
							$updatesql .= ", extcredits{$key}=0";
						}
					}
				}
			}

			if(!empty($updatesql)) {
				$limit = 2000;
				set_time_limit(0);
				for($i=0; $i > -1; $i++) {
					$uids = searchmembers($search_condition, $limit, $i*$limit);
					$conditions = $uids ? 'm.uid IN ('.dimplode($uids).')' : '0';
					$uids_query = DB::query("SELECT m.uid AS m_uid, mc.uid AS mc_uid FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_count')." mc ON m.uid=mc.uid WHERE $conditions");
					while($uid_tmp = DB::fetch($uids_query)) {
						if(empty($uid_tmp['mc_uid'])) {
							DB::insert('common_member_count', array('uid' => $uid_tmp['m_uid']));
						}
					}
					$uids_conditions = dimplode($uids);
					DB::query("UPDATE ".DB::table('common_member_count')." SET uid=uid $updatesql WHERE uid IN ($uids_conditions)", 'UNBUFFTERED');
					if(count($uids) < $limit) break;
				}
			} else {
				cpmsg('members_reward_invalid', '', 'error');
			}

			if(!$_G['gp_notifymembers']) {
				cpmsg('members_reward_succeed', '', 'succeed');
			}

		} elseif ($operation == 'confermedal') {

			$medals = $_G['gp_medals'];
			if(!empty($medals)) {
				$medalids = $comma = '';
				foreach($medals as $key=> $medalid) {
					$medalids .= "$comma'$key'";
					$comma = ',';
				}

				$medalsnew = $comma = '';
				$medalsnewarray = $medalidarray = array();
				$query = DB::query("SELECT medalid, expiration FROM ".DB::table('forum_medal')." WHERE medalid IN ($medalids) ORDER BY displayorder");
				while($medal = DB::fetch($query)) {
					$medal['status'] = empty($medal['expiration']) ? 0 : 1;
					$medal['expiration'] = empty($medal['expiration'])? 0 : TIMESTAMP + $medal['expiration'] * 86400;
					$medal['medal'] = $medal['medalid'].(empty($medal['expiration']) ? '' : '|'.$medal['expiration']);
					$medalsnew .= $comma.$medal['medal'];
					$medalsnewarray[] = $medal;
					$medalidarray[] = $medal['medalid'];
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
							foreach($medalidarray as $medalid) {
								if(!in_array($medalid, explode("\t", $medalnew['medals']))){
									$addmedalnew .= $medalid."\t";
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

function banlog($username, $origgroupid, $newgroupid, $expiration, $reason, $status = 0) {
	global $_G;
	writelog('banlog', dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[groupid]\t$_G[clientip]\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason\t$status"));
}

function selectday($varname, $dayarray) {
	global $lang;
	$selectday = '<select name="'.$varname.'">';
	if($dayarray && is_array($dayarray)) {
		foreach($dayarray as $day) {
			$langday = $day.'_day';
			$daydate = $day ? '('.dgmdate(TIMESTAMP + $day * 86400).')' : '';
			$selectday .= '<option value='.$day.'>'.$lang[$langday].'&nbsp;'.$daydate.'</option>';
		}
	}
	$selectday .= '</select>';

	return $selectday;
}

function accessimg($access) {
	return $access == -1 ? '<img src="static/image/common/access_disallow.gif" />' :
		($access == 1 ? '<img src="static/image/common/access_allow.gif" />' : '<img src="static/image/common/access_normal.gif" />');
}


?>