<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_forum.php 16686 2010-09-13 03:12:35Z liulanbo $
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
	$aid = $attach['aid'];
	if($remote) {
		ftpcmd('delete', $_G['setting']['ftp']['attachdir'].'/forum/'.$filename);
		$havethumb && ftpcmd('delete', $_G['setting']['ftp']['attachdir'].'/forum/'.$filename.'.thumb.jpg');
	} else {
		@unlink($_G['setting']['attachdir'].'/forum/'.$filename);
		$havethumb && @unlink($_G['setting']['attachdir'].'/forum/'.$filename.'.thumb.jpg');
	}
	@unlink($_G['setting']['attachdir'].'image/'.$aid.'_140_140.jpg');
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
					$formula = preg_replace("/\{(\d{4})\-(\d{1,2})\-(\d{1,2})\}/e", "'\\1-'.sprintf('%02d', '\\2').'-'.sprintf('%02d', '\\3')", $formula);
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
					$formula = preg_replace("/\{(\d{4})\-(\d{1,2})\-(\d{1,2})\}/e", "'\\1-'.sprintf('%02d', '\\2').'-'.sprintf('%02d', '\\3')", $formula);
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

function setstatus($position, $value, $baseon = null) {
	$t = pow(2, $position - 1);
	if($value) {
		$t = $baseon | $t;
	} elseif ($baseon !== null) {
		$t = $baseon & ~$t;
	} else {
		$t = ~$t;
	}
	return $t & 0xFFFF;
}

function getstatus($status, $position) {
	$t = $status & pow(2, $position - 1) ? 1 : 0;
	return $t;
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
	loadcache(array('nopermission', 'usergroups'));
	if($formula) {
		$formula = unserialize($formula);
		$permmessage = stripslashes($formula['message']);
	}
	$v = $_G['cache']['nopermission'][$fid][$type][$_G['groupid']][0];
	$gids = $_G['cache']['nopermission'][$fid][$type][$_G['groupid']][1];
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
	if($fid) {
		$fid = is_numeric($fid) ? intval($fid) : (!empty($_G['setting']['forumfids'][$fid]) ? $_G['setting']['forumfids'][$fid] : 0);
	}

	$modthreadkey = isset($_G['gp_modthreadkey']) && $_G['gp_modthreadkey'] == modauthkey($tid) ? $_G['gp_modthreadkey'] : '';
	$_G['forum_auditstatuson'] = $modthreadkey ? true : false;

	$accessadd1 = $accessadd2 = $modadd1 = $modadd2 = $metadescription = $hookscriptmessage = '';
	$adminid = $_G['adminid'];
	if($_G['uid']) {
		if($_G['member']['accessmasks']) {
			$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowpostattach, a.allowpostimage';
			$accessadd2 = "LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid";
		}

		if($adminid == 3) {
			$modadd1 = ', m.uid AS ismoderator';
			$modadd2 = "LEFT JOIN ".DB::table('forum_moderator')." m ON m.uid='$_G[uid]' AND m.fid=f.fid";
		}
	}

	if(!empty($tid) || !empty($fid)) {
		if(empty($tid)) {
			$forum = DB::fetch_first("SELECT f.fid, f.*, ff.* $accessadd1 $modadd1, f.fid AS fid
			FROM ".DB::table('forum_forum')." f
			LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid $accessadd2 $modadd2
			WHERE f.fid='$fid'");
		} else {
			loadcache('threadtableids');
			$threadtableids = array(0);
			if(!empty($_G['cache']['threadtableids'])) {
				$threadtableids = array_merge($threadtableids, $_G['cache']['threadtableids']);
			}
			$archiveid = intval($_REQUEST['archiveid']);
			if(!empty($archiveid) && in_array($archiveid, $threadtableids)) {
				$threadtable = $archiveid ? "forum_thread_{$archiveid}" : 'forum_thread';
				$forum = DB::fetch_first("SELECT t.tid, t.closed,".(defined('SQL_ADD_THREAD') ? SQL_ADD_THREAD : '')." f.*, ff.* $accessadd1 $modadd1, f.fid AS fid
					FROM ".DB::table($threadtable)." t
					INNER JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
					LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid $accessadd2 $modadd2
					WHERE t.tid='$tid'".($_G['forum_auditstatuson'] ? '' : " AND (t.displayorder>='0' OR (t.displayorder IN ('-4', '-3', '-2') AND t.authorid='$_G[uid]'))")." LIMIT 1");
				$forum['threadtableid'] = $archiveid;
			} else {
				foreach($threadtableids as $tableid) {
					$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
					$forum = DB::fetch_first("SELECT t.tid, t.closed,".(defined('SQL_ADD_THREAD') ? SQL_ADD_THREAD : '')." f.*, ff.* $accessadd1 $modadd1, f.fid AS fid
					FROM ".DB::table($threadtable)." t
					INNER JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
					LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid $accessadd2 $modadd2
					WHERE t.tid='$tid'".($_G['forum_auditstatuson'] ? '' : " AND (t.displayorder>='0' OR (t.displayorder IN ('-4', '-3', '-2') AND t.authorid='$_G[uid]'))")." LIMIT 1");
					if(!empty($forum)) {
						$forum['threadtableid'] = $tableid;
						break;
					}
				}
			}
			$tid = $forum['tid'];
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
	if(!$_G['setting']['my_search_status'] && ($_G['setting']['my_search_closetime'] < time() - 5184000)) return;
	$data['action'] = $opt;
	$data['dateline'] = time();
	DB::insert('forum_threadlog', $data, false, true);
}

function my_post_log($opt, $data) {
	global $_G;
	if(!$_G['setting']['my_search_status'] && ($_G['setting']['my_search_closetime'] < time() - 5184000)) return;
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
	if($data['status'] == 3 || $type == 'banner') {
		$imgwh = array('icon' => array('48', '48'), 'banner' => array('720', '168'));
		require_once libfile('class/image');
		$img = new image;
		$img->Thumb($upload->attach['target'], './'.$uploadtype.'/'.$upload->attach['attachment'], $imgwh[$type][0], $imgwh[$type][1], 'fixwr');
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
		showmessage('forum_archiver_disabled');
	}
	$filename = $path . '.php';
	return DISCUZ_ROOT . "./source/archiver/$filename";
}
?>