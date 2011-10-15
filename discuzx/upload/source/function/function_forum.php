<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_forum.php 24723 2011-10-09 12:50:14Z yangli $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function discuz_uc_avatar($uid, $size = '', $returnsrc = FALSE) {
	global $_G;
	return avatar($uid, $size, $returnsrc, FALSE, $_G['setting']['avatarmethod'], $_G['setting']['ucenterurl']);
}

function dunlink($attach) {
	global $_G;
	$filename = $attach['attachment'];
	$havethumb = $attach['thumb'];
	$remote = $attach['remote'];
	if($remote) {
		ftpcmd('delete', $_G['setting']['ftp']['attachdir'].'/forum/'.$filename);
		$havethumb && ftpcmd('delete', $_G['setting']['ftp']['attachdir'].'/forum/'.getimgthumbname($filename));
	} else {
		@unlink($_G['setting']['attachdir'].'/forum/'.$filename);
		$havethumb && @unlink($_G['setting']['attachdir'].'/forum/'.getimgthumbname($filename));
	}
	if($attach['aid']) {
		@unlink($_G['setting']['attachdir'].'image/'.$attach['aid'].'_140_140.jpg');
	}
}

function formulaperm($formula) {
	global $_G;

	$formula = unserialize($formula);
	$medalperm = $formula['medal'];
	$permusers = $formula['users'];
	$permmessage = $formula['message'];
	if($_G['setting']['medalstatus'] && $medalperm) {
		$exists = 1;
		$_G['forum_formulamessage'] = '';
		$medalpermc = $medalperm;
		if($_G['uid']) {
			$medals = explode("\t", DB::result_first("SELECT medals FROM ".DB::table('common_member_field_forum')." WHERE uid='$_G[uid]'"));
			foreach($medalperm as $k => $medal) {
				foreach($medals as $r) {
					list($medalid) = explode("|", $r);
					if($medalid == $medal) {
						$exists = 0;
						unset($medalpermc[$k]);
					}
				}
			}
		} else {
			$exists = 0;
		}
		if($medalpermc) {
			loadcache('medals');
			foreach($medalpermc as $medal) {
				if($_G['cache']['medals'][$medal]) {
					$_G['forum_formulamessage'] .= '<img src="'.STATICURL.'image/common/'.$_G['cache']['medals'][$medal]['image'].'" style="vertical-align:middle;" />&nbsp;'.$_G['cache']['medals'][$medal]['name'].'&nbsp; ';
				}
			}
			showmessage('forum_permforum_nomedal', NULL, array('forum_permforum_nomedal' => $_G['forum_formulamessage']), array('login' => 1));
		}
	}
	$formulatext = $formula[0];
	$formula = $formula[1];
	if($_G['adminid'] == 1 || $_G['forum']['ismoderator'] || in_array($_G['groupid'], explode("\t", $_G['forum']['spviewperm']))) {
		return FALSE;
	}
	if($permusers) {
		$permusers = str_replace(array("\r\n", "\r"), array("\n", "\n"), $permusers);
		$permusers = explode("\n", trim($permusers));
		if(!in_array($_G['member']['username'], $permusers)) {
			showmessage('forum_permforum_disallow', NULL, array(), array('login' => 1));
		}
	}
	if(!$formula) {
		return FALSE;
	}
	if(strexists($formula, '$memberformula[')) {
		preg_match_all("/\\\$memberformula\['(\w+?)'\]/", $formula, $a);
		$fields = $profilefields = array();
		$mfadd = array();
		foreach($a[1] as $field) {
			switch($field) {
				case 'regdate':
					$formula = preg_replace("/\{(\d{4})\-(\d{1,2})\-(\d{1,2})\}/e", "'\'\\1-'.sprintf('%02d', '\\2').'-'.sprintf('%02d', '\\3').'\''", $formula);
				case 'regday':
					$fields[] = 'm.regdate';break;
				case 'regip':
				case 'lastip':
					$formula = preg_replace("/\{([\d\.]+?)\}/", "'\\1'", $formula);
					$formula = preg_replace('/(\$memberformula\[\'(regip|lastip)\'\])\s*=+\s*\'([\d\.]+?)\'/', "strpos(\\1, '\\3')===0", $formula);
				case 'buyercredit':
				case 'sellercredit':
					$mfadd['ms'] = " LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid";
					$fields[] = 'ms.'.$field;break;
				case substr($field, 0, 5) == 'field':
					$mfadd['mp'] = " LEFT JOIN ".DB::table('common_member_profile')." mp ON m.uid=mp.uid";
					$fields[] = 'mp.field'.intval(substr($field, 5));
					$profilefields[] = $field;break;
			}
		}
		$memberformula = array();
		if($_G['uid']) {
			$memberformula = DB::fetch_first("SELECT ".implode(',', $fields)." FROM ".DB::table('common_member')." m ".implode('', $mfadd)." WHERE m.uid='$_G[uid]'");
			if(in_array('regday', $a[1])) {
				$memberformula['regday'] = intval((TIMESTAMP - $memberformula['regdate']) / 86400);
			}
			if(in_array('regdate', $a[1])) {
				$memberformula['regdate'] = date('Y-m-d', $memberformula['regdate']);
			}
			$memberformula['lastip'] = $memberformula['lastip'] ? $memberformula['lastip'] : $_G['clientip'];
		} else {
			if(isset($memberformula['regip'])) {
				$memberformula['regip'] = $_G['clientip'];
			}
			if(isset($memberformula['lastip'])) {
				$memberformula['lastip'] = $_G['clientip'];
			}
		}
	}
	@eval("\$formulaperm = ($formula) ? TRUE : FALSE;");
	if(!$formulaperm) {
		if(!$permmessage) {
			$language = lang('forum/misc');
			$search = array('regdate', 'regday', 'regip', 'lastip', 'buyercredit', 'sellercredit', 'digestposts', 'posts', 'threads', 'oltime');
			$replace = array($language['formulaperm_regdate'], $language['formulaperm_regday'], $language['formulaperm_regip'], $language['formulaperm_lastip'], $language['formulaperm_buyercredit'], $language['formulaperm_sellercredit'], $language['formulaperm_digestposts'], $language['formulaperm_posts'], $language['formulaperm_threads'], $language['formulaperm_oltime']);
			for($i = 1; $i <= 8; $i++) {
				$search[] = 'extcredits'.$i;
				$replace[] = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : $language['formulaperm_extcredits'].$i;
			}
			if($profilefields) {
				loadcache(array('fields_required', 'fields_optional'));
				foreach($profilefields as $profilefield) {
					$search[] = $profilefield;
					$replace[] = !empty($_G['cache']['fields_optional']['field_'.$profilefield]) ? $_G['cache']['fields_optional']['field_'.$profilefield]['title'] : $_G['cache']['fields_required']['field_'.$profilefield]['title'];
				}
			}
			$i = 0;$_G['forum_usermsg'] = '';
			foreach($search as $s) {
				if(in_array($s, array('digestposts', 'posts', 'threads', 'oltime', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8'))) {
					$_G['forum_usermsg'] .= strexists($formulatext, $s) ? '<br />&nbsp;&nbsp;&nbsp;'.$replace[$i].': '.(@eval('return intval(getuserprofile(\''.$s.'\'));')) : '';
				} elseif(in_array($s, array('regdate', 'regip'))) {
					$_G['forum_usermsg'] .= strexists($formulatext, $s) ? '<br />&nbsp;&nbsp;&nbsp;'.$replace[$i].': '.(@eval('return $memberformula[\''.$s.'\'];')) : '';
				}
				$i++;
			}
			$search = array_merge($search, array('and', 'or', '>=', '<=', '=='));
			$replace = array_merge($replace, array('&nbsp;&nbsp;<b>'.$language['formulaperm_and'].'</b>&nbsp;&nbsp;', '&nbsp;&nbsp;<b>'.$language['formulaperm_or'].'</b>&nbsp;&nbsp;', '&ge;', '&le;', '='));
			$_G['forum_formulamessage'] = str_replace($search, $replace, $formulatext);
		} else {
			$_G['forum_formulamessage'] = $permmessage;
		}

		if(!$permmessage) {
			showmessage('forum_permforum_nopermission', NULL, array('formulamessage' => $_G['forum_formulamessage'], 'usermsg' => $_G['forum_usermsg']), array('login' => 1));
		} else {
			showmessage('forum_permforum_nopermission_custommsg', NULL, array('formulamessage' => $_G['forum_formulamessage']), array('login' => 1));
		}
	}
	return TRUE;
}

function medalformulaperm($formula, $type) {
	global $_G;

	$formula = unserialize($formula);
	$permmessage = $formula['message'];
	$formula = $formula['medal'];
	if(!empty($formula['usergroupallow']) && is_array($formula['usergroups']) && !in_array($_G['groupid'], $formula['usergroups'])) {
		loadcache('usergroups');
		$message = array();
		foreach($formula['usergroups'] as $groupid) {
			$message[] = $_G['cache']['usergroups'][$groupid]['grouptitle'].' ';
		}
		$_G['forum_formulamessage'] = implode(', ', $message);
		$_G['forum_usermsg'] = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
		return FALSE;
	}
	$formulatext = $formula[0];
	$formula = $formula[1];
	if(!$formula) {
		return FALSE;
	}
	if(strexists($formula, '$memberformula[')) {
		preg_match_all("/\\\$memberformula\['(\w+?)'\]/", $formula, $a);
		$fields = $profilefields = array();
		$mfadd = array();
		foreach($a[1] as $field) {
			switch($field) {
				case 'regdate':
					$formula = preg_replace("/\{(\d{4})\-(\d{1,2})\-(\d{1,2})\}/e", "'\'\\1-'.sprintf('%02d', '\\2').'-'.sprintf('%02d', '\\3').'\''", $formula);
				case 'regday':
					$fields[] = 'm.regdate';break;
				case 'regip':
				case 'lastip':
					$formula = preg_replace("/\{([\d\.]+?)\}/", "'\\1'", $formula);
					$formula = preg_replace('/(\$memberformula\[\'(regip|lastip)\'\])\s*=+\s*\'([\d\.]+?)\'/', "strpos(\\1, '\\3')===0", $formula);
				case 'buyercredit':
				case 'sellercredit':
					$mfadd['ms'] = " LEFT JOIN ".DB::table('common_member_status')." ms ON m.uid=ms.uid";
					$fields[] = 'ms.'.$field;break;
				case substr($field, 0, 5) == 'field':
					$mfadd['mp'] = " LEFT JOIN ".DB::table('common_member_profile')." mp ON m.uid=mp.uid";
					$fields[] = 'mp.field'.intval(substr($field, 5));
					$profilefields[] = $field;break;
			}
		}
		$memberformula = array();
		if($_G['uid']) {
			$memberformula = DB::fetch_first("SELECT ".implode(',', $fields)." FROM ".DB::table('common_member')." m ".implode('', $mfadd)." WHERE m.uid='$_G[uid]'");
			if(in_array('regday', $a[1])) {
				$memberformula['regday'] = intval((TIMESTAMP - $memberformula['regdate']) / 86400);
			}
			if(in_array('regdate', $a[1])) {
				$memberformula['regdate'] = date('Y-m-d', $memberformula['regdate']);
			}
			$memberformula['lastip'] = $memberformula['lastip'] ? $memberformula['lastip'] : $_G['clientip'];
		} else {
			if(isset($memberformula['regip'])) {
				$memberformula['regip'] = $_G['clientip'];
			}
			if(isset($memberformula['lastip'])) {
				$memberformula['lastip'] = $_G['clientip'];
			}
		}
	}
	@eval("\$formulaperm = ($formula) ? TRUE : FALSE;");
	if(!$formulaperm || $type == 2) {
		if(!$permmessage) {
			$language = lang('forum/misc');
			$search = array('regdate', 'regday', 'regip', 'lastip', 'buyercredit', 'sellercredit', 'digestposts', 'posts', 'threads', 'oltime');
			$replace = array($language['formulaperm_regdate'], $language['formulaperm_regday'], $language['formulaperm_regip'], $language['formulaperm_lastip'], $language['formulaperm_buyercredit'], $language['formulaperm_sellercredit'], $language['formulaperm_digestposts'], $language['formulaperm_posts'], $language['formulaperm_threads'], $language['formulaperm_oltime']);
			for($i = 1; $i <= 8; $i++) {
				$search[] = 'extcredits'.$i;
				$replace[] = $_G['setting']['extcredits'][$i]['title'] ? $_G['setting']['extcredits'][$i]['title'] : $language['formulaperm_extcredits'].$i;
			}
			if($profilefields) {
				loadcache(array('fields_required', 'fields_optional'));
				foreach($profilefields as $profilefield) {
					$search[] = $profilefield;
					$replace[] = !empty($_G['cache']['fields_optional']['field_'.$profilefield]) ? $_G['cache']['fields_optional']['field_'.$profilefield]['title'] : $_G['cache']['fields_required']['field_'.$profilefield]['title'];
				}
			}
			$i = 0;$_G['forum_usermsg'] = '';
			foreach($search as $s) {
				if(in_array($s, array('digestposts', 'posts', 'threads', 'oltime', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8'))) {
					$_G['forum_usermsg'] .= strexists($formulatext, $s) ? '<br />&nbsp;&nbsp;&nbsp;'.$replace[$i].': '.(@eval('return intval(getuserprofile(\''.$s.'\'));')) : '';
				} elseif(in_array($s, array('regdate', 'regip'))) {
					$_G['forum_usermsg'] .= strexists($formulatext, $s) ? '<br />&nbsp;&nbsp;&nbsp;'.$replace[$i].': '.(@eval('return $memberformula[\''.$s.'\'];')) : '';
				}
				$i++;
			}
			$search = array_merge($search, array('and', 'or', '>=', '<=', '=='));
			$replace = array_merge($replace, array('&nbsp;&nbsp;<b>'.$language['formulaperm_and'].'</b>&nbsp;&nbsp;', '&nbsp;&nbsp;<b>'.$language['formulaperm_or'].'</b>&nbsp;&nbsp;', '&ge;', '&le;', '='));
			$_G['forum_formulamessage'] = str_replace($search, $replace, $formulatext);
		} else {
			$_G['forum_formulamessage'] = $permmessage;
		}

		return $_G['forum_formulamessage'];
	} elseif($formulaperm && $type == 1) {
		return FALSE;
	}
	return TRUE;
}

function groupexpiry($terms) {
	$terms = is_array($terms) ? $terms : unserialize($terms);
	$groupexpiry = isset($terms['main']['time']) ? intval($terms['main']['time']) : 0;
	if(is_array($terms['ext'])) {
		foreach($terms['ext'] as $expiry) {
			if((!$groupexpiry && $expiry) || $expiry < $groupexpiry) {
				$groupexpiry = $expiry;
			}
		}
	}
	return $groupexpiry;
}

function site() {
	return $_SERVER['HTTP_HOST'];
}


function typeselect($curtypeid = 0) {
	global $_G;
	if($threadtypes = $_G['forum']['threadtypes']) {
		$html = '<select name="typeid" id="typeid"><option value="0">&nbsp;</option>';
		foreach($threadtypes['types'] as $typeid => $name) {
			$html .= '<option value="'.$typeid.'" '.($curtypeid == $typeid ? 'selected' : '').'>'.strip_tags($name).'</option>';
		}
		$html .= '</select>';
		return $html;
	} else {
		return '';
	}
}

function updatemodworks($modaction, $posts = 1) {
	global $_G;
	$today = dgmdate(TIMESTAMP, 'Y-m-d');
	if($_G['setting']['modworkstatus'] && $modaction && $posts) {
		DB::query("UPDATE ".DB::table('forum_modwork')." SET count=count+1, posts=posts+'$posts' WHERE uid='$_G[uid]' AND modaction='$modaction' AND dateline='$today'");
		if(!DB::affected_rows()) {
			DB::query("INSERT INTO ".DB::table('forum_modwork')." (uid, modaction, dateline, count, posts) VALUES ('$_G[uid]', '$modaction', '$today', 1, '$posts')");
		}
	}
}

function buildbitsql($fieldname, $position, $value) {
	$t = " `$fieldname`=`$fieldname`";
	if($value) {
		$t .= ' | '.setstatus($position, 1);
	} else {
		$t .= ' & '.setstatus($position, 0);
	}
	return $t.' ';
}

function showmessagenoperm($type, $fid, $formula = '') {
	global $_G;
	loadcache('usergroups');
	if($formula) {
		$formula = unserialize($formula);
		$permmessage = stripslashes($formula['message']);
	}

	$usergroups = $nopermgroup = $forumnoperms = array();
	$nopermdefault = array(
		'viewperm' => array(),
		'getattachperm' => array(),
		'postperm' => array(7),
		'replyperm' => array(7),
		'postattachperm' => array(7),
	);
	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');

	foreach($_G['cache']['usergroups'] as $gid => $usergroup) {
		$usergroups[$gid] = $usergroup['type'];
		$grouptype = $usergroup['type'] == 'member' ? 0 : 1;
		$nopermgroup[$grouptype][] = $gid;
	}
	if($fid == $_G['forum']['fid']) {
		$forum = $_G['forum'];
	} else {
		$forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");
	}

	foreach($perms as $perm) {
		$permgroups = explode("\t", $forum[$perm]);
		$membertype = $forum[$perm] ? array_intersect($nopermgroup[0], $permgroups) : TRUE;
		$forumnoperm = $forum[$perm] ? array_diff(array_keys($usergroups), $permgroups) : $nopermdefault[$perm];
		foreach($forumnoperm as $groupid) {
			$nopermtype = $membertype && $groupid == 7 ? 'login' : ($usergroups[$groupid] == 'system' || $usergroups[$groupid] == 'special' ? 'none' : ($membertype ? 'upgrade' : 'none'));
			$forumnoperms[$fid][$perm][$groupid] = array($nopermtype, $permgroups);
		}
	}

	$v = $forumnoperms[$fid][$type][$_G['groupid']][0];
	$gids = $forumnoperms[$fid][$type][$_G['groupid']][1];
	$comma = $permgroups = '';
	if(is_array($gids)) {
		foreach($gids as $gid) {
			if($gid && $_G['cache']['usergroups'][$gid]) {
				$permgroups .= $comma.$_G['cache']['usergroups'][$gid]['grouptitle'];
				$comma = ', ';
			} elseif($_G['setting']['verify']['enabled'] && substr($gid, 0, 1) == 'v') {
				$vid = substr($gid, 1);
				$permgroups .= $comma.$_G['setting']['verify'][$vid]['title'];
				$comma = ', ';
			}

		}
	}

	$custom = 0;
	if($permmessage) {
		$message = $permmessage;
		$custom = 1;
	} else {
		if($v) {
			$message = $type.'_'.$v.'_nopermission';
		} else {
			$message = 'group_nopermission';
		}
	}

	showmessage($message, NULL, array('fid' => $fid, 'permgroups' => $permgroups, 'grouptitle' => $_G['group']['grouptitle']), array('login' => 1), $custom);
}

function loadforum() {
	global $_G;
	$tid = intval(getgpc('tid'));
	$fid = getgpc('fid');
	if(!$fid && getgpc('gid')) {
		$fid = intval(getgpc('gid'));
	}
	if(!empty($_G['gp_archiver'])) {//X1.5的Archiver兼容
		if($fid) {
			dheader('location: archiver/?fid-'.$fid.'.html');
		} elseif($tid) {
			dheader('location: archiver/?tid-'.$tid.'.html');
		} else {
			dheader('location: archiver/');
		}
	}
	if(defined('IN_ARCHIVER') && $_G['setting']['archiverredirect'] && !IS_ROBOT) {
		dheader('location: ../forum.php'.($_G['mod'] ? '?mod='.$_G['mod'].(!empty($_GET['fid']) ? '&fid='.$_GET['fid'] : (!empty($_GET['tid']) ? '&tid='.$_GET['tid'] : '')) : ''));
	}
	if($_G['setting']['forumpicstyle']) {
		$_G['setting']['forumpicstyle'] = unserialize($_G['setting']['forumpicstyle']);
		empty($_G['setting']['forumpicstyle']['thumbwidth']) && $_G['setting']['forumpicstyle']['thumbwidth'] = 214;
		empty($_G['setting']['forumpicstyle']['thumbheight']) && $_G['setting']['forumpicstyle']['thumbheight'] = 160;
	} else {
		$_G['setting']['forumpicstyle'] = array('thumbwidth' => 214, 'thumbheight' => 160);
	}
	if($fid) {
		$fid = is_numeric($fid) ? intval($fid) : (!empty($_G['setting']['forumfids'][$fid]) ? $_G['setting']['forumfids'][$fid] : 0);
	}

	$modthreadkey = isset($_G['gp_modthreadkey']) && $_G['gp_modthreadkey'] == modauthkey($tid) ? $_G['gp_modthreadkey'] : '';
	$_G['forum_auditstatuson'] = $modthreadkey ? true : false;

	$accessadd1 = $accessadd2 = $modadd1 = $modadd2 = $metadescription = $hookscriptmessage = '';
	$adminid = $_G['adminid'];
	if($_G['uid']) {
		if($_G['member']['accessmasks']) {
			$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowgetimage, a.allowpostattach, a.allowpostimage';
			$accessadd2 = "LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid";
		}

		if($adminid == 3) {
			$modadd1 = ', m.uid AS ismoderator';
			$modadd2 = "LEFT JOIN ".DB::table('forum_moderator')." m ON m.uid='$_G[uid]' AND m.fid=f.fid";
		}
	}

	if(!empty($tid) || !empty($fid)) {

		if(!empty ($tid)) {
			$archiveid = !empty($_G['gp_archiveid']) ? intval($_G['gp_archiveid']) : null;
			$_G['thread'] = get_thread_by_tid($tid, '*', '', $archiveid);
			if(!$_G['forum_auditstatuson'] && !empty($_G['thread'])
					&& !($_G['thread']['displayorder'] >= 0 || (in_array($_G['thread']['displayorder'], array(-4,-3,-2)) && $_G['thread']['authorid'] == $_G['uid']))) {
				$_G['thread'] = null;
			}

			$_G['forum_thread'] = & $_G['thread'];

			if(empty($_G['thread'])) {
				$fid = $tid = 0;
			} else {
				$fid = $_G['thread']['fid'];
				$tid = $_G['thread']['tid'];
			}
		}

		if($fid) {
			$forum = DB::fetch_first("SELECT f.fid, f.*, ff.* $accessadd1 $modadd1, f.fid AS fid
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid $accessadd2 $modadd2
			WHERE f.fid='$fid'");
		}

		if($forum) {
			$forum['ismoderator'] = !empty($forum['ismoderator']) || $adminid == 1 || $adminid == 2 ? 1 : 0;
			$fid = $forum['fid'];
			$gorup_admingroupids = $_G['setting']['group_admingroupids'] ? unserialize($_G['setting']['group_admingroupids']) : array('1' => '1');

			if($forum['status'] == 3) {
				if(!$_G['setting']['groupstatus']) {
					showmessage('group_status_off');
				}
				if(!empty($forum['moderators'])) {
					$forum['moderators'] = unserialize($forum['moderators']);
				} else {
					require_once libfile('function/group');
					$forum['moderators'] = update_groupmoderators($fid);
				}
				if($_G['uid'] && $_G['adminid'] != 1) {
					$forum['ismoderator'] = !empty($forum['moderators'][$_G['uid']]) ? 1 : 0;
					$_G['adminid'] = 0;
					if($forum['ismoderator'] || $gorup_admingroupids[$_G['groupid']]) {
						$_G['adminid'] = $_G['adminid'] ? $_G['adminid'] : 3;
						if(!empty($gorup_admingroupids[$_G['groupid']])) {
							$forum['ismoderator'] = 1;
							$_G['adminid'] = 2;
						}

						$group_userperm = unserialize($_G['setting']['group_userperm']);
						if(is_array($group_userperm)) {
							$_G['group'] = array_merge($_G['group'], $group_userperm);
							$_G['group']['allowmovethread'] = $_G['group']['allowcopythread'] = $_G['group']['allowedittypethread']= 0;
						}
					}
				}
			}
			foreach(array('threadtypes', 'threadsorts', 'creditspolicy', 'modrecommend') as $key) {
				$forum[$key] = !empty($forum[$key]) ? unserialize($forum[$key]) : array();
				if(!is_array($forum[$key])) {
					$forum[$key] = array();
				}
			}

			if($forum['status'] == 3) {
				$_G['isgroupuser'] = 0;
				$_G['basescript'] = 'group';
				if(empty($forum['level'])) {
					$levelid = DB::result_first("SELECT levelid FROM ".DB::table('forum_grouplevel')." WHERE creditshigher<='$forum[commoncredits]' AND '$forum[commoncredits]'<creditslower LIMIT 1");
					$forum['level'] = $levelid;
					DB::query("UPDATE ".DB::table('forum_forum')." SET level='$levelid' WHERE fid='$fid'");
				}
				loadcache('grouplevels');
				$grouplevel = $_G['grouplevels'][$forum['level']];
				if(!empty($grouplevel['icon'])) {
					$valueparse = parse_url($grouplevel['icon']);
					if(!isset($valueparse['host'])) {
						$grouplevel['icon'] = $_G['setting']['attachurl'].'common/'.$grouplevel['icon'];
					}
				}

				$group_postpolicy = $grouplevel['postpolicy'];
				if(is_array($group_postpolicy)) {
					$forum = array_merge($forum, $group_postpolicy);
				}
				$forum['allowfeed'] = $_G['setting']['group_allowfeed'];
				if($_G['uid']) {
					if(!empty($forum['moderators'][$_G['uid']])) {
						$_G['isgroupuser'] = 1;
					} else {
						$_G['isgroupuser'] = DB::result_first("SELECT level FROM ".DB::table('forum_groupuser')." WHERE fid='$fid' AND uid='$_G[uid]' LIMIT 1");
						if($_G['isgroupuser'] <= 0 && empty($forum['ismoderator'])) {
							$_G['group']['allowrecommend'] = $_G['cache']['usergroup_'.$_G['groupid']]['allowrecommend'] = 0;
							$_G['group']['allowcommentpost'] = $_G['cache']['usergroup_'.$_G['groupid']]['allowcommentpost'] = 0;
							$_G['group']['allowcommentitem'] = $_G['cache']['usergroup_'.$_G['groupid']]['allowcommentitem'] = 0;
							$_G['group']['raterange'] = $_G['cache']['usergroup_'.$_G['groupid']]['raterange'] = array();
							$_G['group']['allowvote'] = $_G['cache']['usergroup_'.$_G['groupid']]['allowvote'] = 0;
						} else {
							$_G['isgroupuser'] = 1;
						}
					}
				}
			}
		} else {
			$fid = 0;
		}
	}

	$_G['fid'] = $fid;
	$_G['tid'] = $tid;
	$_G['forum'] = &$forum;
	$_G['current_grouplevel'] = &$grouplevel;

	if(isset($_G['cookie']['widthauto']) && $_G['setting']['switchwidthauto'] && empty($_G['forum']['widthauto'])) {
		$_G['forum_widthauto'] = $_G['cookie']['widthauto'] > 0;
	} else {
		$_G['forum_widthauto'] = empty($_G['forum']['widthauto']) ? !$_G['setting']['allowwidthauto'] : $_G['forum']['widthauto'] > 0;
		if(!empty($_G['forum']['widthauto'])) {
			$_G['setting']['switchwidthauto'] = 0;
		}
	}
}

function get_thread_by_tid($tid, $fields = '*', $addcondiction = '', $forcetableid = null) {
	global $_G;

	$ret = array();
	if(!is_numeric($tid)) {
		return $ret;
	}
	loadcache('threadtableids');
	$threadtableids = array(0);
	if(!empty($_G['cache']['threadtableids'])) {
		if($forcetableid === null || ($forcetableid > 0 && !in_array($forcetableid, $_G['cache']['threadtableids']))) {
			$threadtableids = array_merge($threadtableids, $_G['cache']['threadtableids']);
		} else {
			$threadtableids = array(intval($forcetableid));
		}
	}

	foreach($threadtableids as $tableid) {
		$table = $tableid > 0 ? "forum_thread_{$tableid}" : 'forum_thread';
		$ret = DB::fetch_first("SELECT $fields FROM ".DB::table($table)." WHERE tid='$tid' $addcondiction LIMIT 1");
		if($ret) {
			$ret['threadtable'] = $table;
			$ret['threadtableid'] = $tableid;
			$ret['posttable'] = 'forum_post'.($ret['posttableid'] ? '_'.$ret['posttableid'] : '');
			break;
		}
	}

	if(!is_array($ret)) {
		$ret = array();
	}

	return $ret;
}

function get_post_by_pid($pid, $fields = '*', $addcondiction = '', $forcetable = null) {
	global $_G;

	$ret = array();
	if(!is_numeric($pid)) {
		return $ret;
	}

	loadcache('posttable_info');

	$posttableids = array(0);
	if($_G['cache']['posttable_info']) {
		if(isset($forcetable)) {
			if(is_numeric($forcetable) && array_key_exists($forcetable, $_G['cache']['posttable_info'])) {
				$posttableids[] = $forcetable;
			} elseif(substr($forcetable, 0, 10) == 'forum_post') {
				$posttableids[] = $forcetable;
			}
		} else {
			$posttableids = array_keys($_G['cache']['posttable_info']);
		}
	}

	foreach ($posttableids as $id) {
		$table = empty($id) ? 'forum_post' : (is_numeric($id) ? 'forum_post_'.$id : $id);
		$ret = DB::fetch_first("SELECT $fields FROM ".DB::table($table)." WHERE pid='$pid' $addcondiction LIMIT 1");
		if($ret) {
			$ret['posttable'] = $table;
			break;
		}
	}

	if(!is_array($ret)) {
		$ret = array();
	}

	return $ret;
}

function set_rssauth() {
	global $_G;
	if($_G['setting']['rssstatus'] && $_G['uid']) {
		$auth = authcode($_G['uid']."\t".($_G['fid'] ? $_G['fid'] : '').
		"\t".substr(md5($_G['member']['password']), 0, 8), 'ENCODE', md5($_G['config']['security']['authkey']));
	} else {
		$auth = '0';
	}
	$_G['rssauth'] = rawurlencode($auth);
}

function my_thread_log($opt, $data) {
	global $_G;
    $my_search_data = $_G['setting']['my_search_data'];
    if ($my_search_data && !is_array($my_search_data)) {
        $my_search_data = unserialize($my_search_data);
    }
	if(!$_G['setting']['my_search_data']) return;
	$data['action'] = $opt;
	$data['dateline'] = time();
	DB::insert('forum_threadlog', $data, false, true);
}

function my_post_log($opt, $data) {
	global $_G;
    $my_search_data = $_G['setting']['my_search_data'];
    if ($my_search_data && !is_array($my_search_data)) {
        $my_search_data = unserialize($my_search_data);
    }
	if(!$_G['setting']['my_search_data']) return;
	$data['action'] = $opt;
	$data['dateline'] = time();
	DB::insert('forum_postlog', $data, false, true);
}

function rssforumperm($forum) {
	$is_allowed = $forum['type'] != 'group' && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'], 7)));
	return $is_allowed;
}

function upload_icon_banner(&$data, $file, $type) {
	global $_G;
	$data['extid'] = empty($data['extid']) ? $data['fid'] : $data['extid'];
	if(empty($data['extid'])) return '';

	if($data['status'] == 3 && $_G['setting']['group_imgsizelimit']) {
		$file['size'] > ($_G['setting']['group_imgsizelimit'] * 1024) && showmessage('file_size_overflow', '', array('size' => $_G['setting']['group_imgsizelimit'] * 1024));
	}
	require_once libfile('class/upload');
	$upload = new discuz_upload();
	$uploadtype = $data['status'] == 3 ? 'group' : 'common';

	if(!$upload->init($file, $uploadtype, $data['extid'], $type)) {
		return false;
	}

	if(!$upload->save()) {
		if(!defined('IN_ADMINCP')) {
			showmessage($upload->errormessage());
		} else {
			cpmsg($upload->errormessage(), '', 'error');
		}
	}
	if($data['status'] == 3 && $type == 'icon') {
		require_once libfile('class/image');
		$img = new image;
		$img->Thumb($upload->attach['target'], './'.$uploadtype.'/'.$upload->attach['attachment'], 48, 48, 'fixwr');
	}
	return $upload->attach['attachment'];
}

function arch_multi($total, $perpage, $page, $link) {
	$pages = @ceil($total / $perpage) + 1;
	$pagelink = '';
	if($pages > 1) {
		$pagelink .= lang('forum/archiver', 'page') . ": \n";
		$pagestart = $page - 10 < 1 ? 1 : $page - 10;
		$pageend = $page + 10 >= $pages ? $pages : $page + 10;
		for($i = $pagestart; $i < $pageend; $i++) {
			$pagelink .= ($i == $page ? "<strong>[$i]</strong>" : "<a href=\"$link&page=$i\">$i</a>")." \n";
		}
	}
	return $pagelink;
}

function loadarchiver($path) {
	global $_G;
	if(!$_G['setting']['archiver']) {
		require_once DISCUZ_ROOT . "./source/archiver/common/header.php";
		echo '<div id="content">'.lang('message', 'forum_archiver_disabled').'</div>';
		require_once DISCUZ_ROOT . "./source/archiver/common/footer.php";
		exit;
	}
	$filename = $path . '.php';
	return DISCUZ_ROOT . "./source/archiver/$filename";
}

function update_threadpartake($tid) {
	global $_G;
	if($_G['uid'] && $tid) {
		if($_G['setting']['heatthread']['period']) {
			$partaked = DB::result_first("SELECT uid FROM ".DB::table('forum_threadpartake')." WHERE tid='$tid' AND uid='$_G[uid]'");
			if(!$partaked) {
				DB::query("INSERT INTO ".DB::table('forum_threadpartake')." (tid, uid, dateline) VALUES ('$tid', '$_G[uid]', ".TIMESTAMP.")");
				DB::query("UPDATE ".DB::table('forum_thread')." SET heats=heats+1 WHERE tid='$tid'", 'UNBUFFERED');
			}
		} else {
			DB::query("UPDATE ".DB::table('forum_thread')." SET heats=heats+1 WHERE tid='$tid'", 'UNBUFFERED');

		}
	}
}

function getthreadcover($tid, $cover = 0, $getfilename = 0) {
	global $_G;
	if(empty($tid)) {
		return '';
	}
	$coverpath = '';
	$covername = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/'.$tid.'.jpg';
	if($getfilename) {
		return $covername;
	}
	if($cover) {
		$coverpath = ($cover < 0 ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$covername;
	}
	return $coverpath;
}

function addthreadtag($tags, $itemid , $typeid = 'tid') {
	global $_G;

	if($tags == '') {
		return;
	}

	$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
	if(strexists($tags, ',')) {
		$tagarray = array_unique(explode(',', $tags));
	} else {
		$langcore = lang('core');
		$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
		$tagarray = array_unique(explode(' ', $tags));
	}
	$tagcount = 0;
	foreach($tagarray as $tagname) {
		$tagname = trim($tagname);
		if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname)) {
			$result = DB::fetch_first("SELECT tagid, status FROM ".DB::table('common_tag')." WHERE tagname='$tagname'");
			if($result['tagid']) {
				if(!$result['status']) {
					$tagid = $result['tagid'];
				}
			} else {
				DB::query("INSERT INTO ".DB::table('common_tag')." (tagname, status) VALUES ('$tagname', '0')");
				$tagid = DB::insert_id();
			}
			if($tagid) {
				DB::query("INSERT INTO ".DB::table('common_tagitem')." (tagid, tagname, itemid, idtype) VALUES ('$tagid', '$tagname', '$itemid', '$typeid')");
				$tagcount++;
				$tagstr .= $tagid.','.$tagname.'\t';
			}
			if($tagcount > 4) {
				unset($tagarray);
				break;
			}
		}
	}
	return $tagstr;
}

function modthreadtag($tags, $itemid) {
	global $_G;

	$thread = & $_G['forum_thread'];
	$posttable = $thread['posttable'];
	$tagstr = DB::result_first("SELECT tags FROM ".DB::table($posttable)." WHERE tid='$itemid' AND first=1");

	$threadtagarray = $threadtagidarray = $threadtagarraynew = array();
	$query = DB::query("SELECT tagid, tagname FROM ".DB::table('common_tagitem')." WHERE idtype='tid' AND itemid='$itemid'");
	while($result = DB::fetch($query)) {
		$threadtagarray[] = $result['tagname'];
		$threadtagidarray[] = $result['tagid'];
	}

	$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
	if(strexists($tags, ',')) {
		$tagarray = array_unique(explode(',', $tags));
	} else {
		$langcore = lang('core');
		$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
		$tagarray = array_unique(explode(' ', $tags));
	}

	$tagcount = 0;
	foreach($tagarray as $tagname) {
		$tagname = trim($tagname);
		if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname)) {
			$threadtagarraynew[] = $tagname;
			if(!in_array($tagname, $threadtagarray)) {
				$result = DB::fetch_first("SELECT tagid, status FROM ".DB::table('common_tag')." WHERE tagname='$tagname'");
				if($result['tagid']) {
					if(!$result['status']) {
						$tagid = $result['tagid'];
					}
				} else {
					DB::query("INSERT INTO ".DB::table('common_tag')." (tagname, status) VALUES ('$tagname', '0')");
					$tagid = DB::insert_id();
				}
				if($tagid) {
					DB::query("INSERT INTO ".DB::table('common_tagitem')." (tagid, tagname, itemid, idtype) VALUES ('$tagid', '$tagname', '$itemid', 'tid')");
					$tagstr = $tagstr.$tagid.','.$tagname.'\t';
				}
			}
		}
		$tagcount++;
		if($tagcount > 4) {
			unset($tagarray);
			break;
		}
	}
	foreach($threadtagarray as $key => $tagname) {
		if(!in_array($tagname, $threadtagarraynew)) {
			DB::query("DELETE FROM	".DB::table('common_tagitem')." WHERE idtype='tid' AND itemid = '$itemid' AND tagname='$tagname'");
			$tagid = $threadtagidarray[$key];
			$tagstr = str_replace("$tagid,$tagname\t", '', $tagstr);
		}
	}
	return $tagstr;
}

function convertunusedattach($aid, $tid, $pid) {
	if(!$aid) {
		return;
	}
	global $_G;
	$attach = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment_unused')." WHERE aid='$aid' AND uid='$_G[uid]'");
	if(!$attach) {
		return;
	}
	$attach = daddslashes($attach);
	$attach['tid'] = $tid;
	$attach['pid'] = $pid;
	DB::insert(getattachtablebytid($tid), $attach, false);
	DB::update('forum_attachment', array('tid' => $tid, 'pid' => $pid, 'tableid' => getattachtableid($tid)), "aid='$attach[aid]'");
	DB::delete('forum_attachment_unused', "aid='$attach[aid]'");
}

function updateattachtid($where, $oldtid, $newtid) {
	$oldattachtable = getattachtablebytid($oldtid);
	$newattachtable = getattachtablebytid($newtid);
	if($oldattachtable != $newattachtable) {
		$query = DB::query("SELECT * FROM ".DB::table($oldattachtable)." WHERE $where");
		while($attach = DB::fetch($query)) {
			$attach = daddslashes($attach);
			$attach['tid'] = $newtid;
			DB::insert($newattachtable, $attach);
		}
		DB::delete($oldattachtable, $where);
	}
	DB::query("UPDATE ".DB::table('forum_attachment')." SET tid='$newtid',tableid='".getattachtableid($newtid)."' WHERE $where");
}

function updatepost($data, $condition, $unbuffered = false, $posttableid = false) {
	global $_G;
	loadcache('posttableids');
	$affected_rows = 0;
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $posttableid !== false && in_array($posttableid, $_G['cache']['posttableids']) ? array($posttableid) : $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	foreach($posttableids as $id) {
		DB::update(getposttable($id), $data, $condition, $unbuffered);
		$affected_rows += DB::affected_rows();
	}
	return $affected_rows;
}

function insertpost($data) {
	if(isset($data['tid'])) {
		$tableid = DB::result_first("SELECT posttableid FROM ".DB::table('forum_thread')." WHERE tid='{$data['tid']}'");
	} else {
		$tableid = $data['tid'] = 0;
	}
	$pid = DB::insert('forum_post_tableid', array('pid' => null), true);

	if(!$tableid) {
		$tablename = 'forum_post';
	} else {
		$tablename = "forum_post_$tableid";
	}

	$data = array_merge($data, array('pid' => $pid));

	DB::insert($tablename, $data);
	if($pid % 1024 == 0) {
		DB::delete('forum_post_tableid', "pid<$pid");
	}
	save_syscache('max_post_id', $pid);
	return $pid;
}

?>