<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_moderate.php 16941 2010-09-17 05:12:38Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


$modact = empty($_G['gp_modact']) || !in_array($_G['gp_modact'] , array('delete', 'ignore', 'validate')) ? 'ignore' : $_G['gp_modact'];

if($op == 'members') {

	$filter = isset($_G['gp_filter']) ? intval($_G['gp_filter']) : 0;
	$filtercheck = array('', '', '');
	$filtercheck[$filter] = 'selected';

	if(submitcheck('dosubmit', 1) || submitcheck('modsubmit')) {

		if(empty($modact) || !in_array($modact, array('ignore', 'validate', 'delete'))) {
			showmessage('modcp_noaction');
		}

		$list = array();
		if($_G['gp_moderate'] && is_array($_G['gp_moderate'])) {
			foreach($_G['gp_moderate'] as $val) {
				if(is_numeric($val) && $val) {
					$list[] = $val;
				}
			}
		}

		if(submitcheck('dosubmit', 1)) {

			$_G['gp_handlekey'] = 'mods';
			include template('forum/modcp_moderate_float');
			dexit();

		} elseif ($uids = dimplode($list)) {

			$members = $uidarray = array();

			$query = DB::query("SELECT v.*, m.uid, m.username, m.email, m.regdate FROM ".DB::table('common_member_validate')." v, ".DB::table('common_member')." m
				WHERE v.uid IN ($uids) AND m.uid=v.uid AND m.groupid='8' AND m.status='$filter'");
			while($member = DB::fetch($query)) {
				$members[$member['uid']] = $member;
				$uidarray[] = $member['uid'];
			}

			if($uids = dimplode($uidarray)) {

				$reason = dhtmlspecialchars(trim($_G['gp_reason']));

				if($_G['gp_modact'] == 'delete') {
					DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
					DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($uids)");
					DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)");
				}

				if($_G['gp_modact'] == 'validate') {
					$newgroupid = DB::result_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE creditshigher<=0 AND 0<creditslower LIMIT 1");
					DB::query("UPDATE ".DB::table('common_member')." SET adminid='0', groupid='$newgroupid' WHERE uid IN ($uids)");
					DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)");
				}

				if($_G['gp_modact'] == 'ignore') {
					DB::query("UPDATE ".DB::table('common_member_validate')." SET moddate='$_G[timestamp]', admin='$_G[username]', status='1', remark='$reason' WHERE uid IN ($uids)");
				}

				if($sendemail) {
					if(!function_exists('sendmail')) {
						include libfile('function/mail');
					}
					foreach($members as $uid => $member) {
						$member['regdate'] = dgmdate($member['regdate']);
						$member['submitdate'] = dgmdate($member['submitdate']);
						$member['moddate'] = dgmdate(TIMESTAMP);
						$member['operation'] = $_G['gp_modact'];
						$member['remark'] = $reason ? $reason : 'N/A';
						$moderate_member_message = lang('email', 'moderate_member_message', array(
							'username' => $member['username'],
							'bbname' => $_G['setting']['bbname'],
							'regdate' => $member['regdate'],
							'submitdate' => $member['submitdate'],
							'submittimes' => $member['submittimes'],
							'message' => $member['message'],
							'modresult' => lang('email', 'moderate_member_'.$member['operation']),
							'moddate' => $member['moddate'],
							'adminusername' => $_G['member']['username'],
							'remark' => $member['remark'],
							'siteurl' => $_G['siteurl'],
						));
						sendmail("$member[username] <$member[email]>", lang('email', 'moderate_member_subject'), $moderate_member_message);
					}
				}
			}

			showmessage('modcp_mod_succeed', "{$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&filter=$filter");

		} else {
			showmessage('modcp_moduser_invalid');
		}

	} else {
		$count =  array(0, 0, 0);
		$query = DB::query("SELECT status, COUNT(*) AS count FROM ".DB::table('common_member_validate')." GROUP BY status");
		while($num = DB::fetch($query)) {
			$count[$num['status']] = $num['count'];
		}

		$page = max(1, intval($_G['page']));
		$_G['setting']['memberperpage'] = 20;
		$start_limit = ($page - 1) * $_G['setting']['memberperpage'];

		$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_validate')." WHERE status='0'");
		$multipage = multi(DB::result($query, 0), $_G['setting']['memberperpage'], $page, "{$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&fid=$_G[fid]&filter=$filter");

		$vuids = '0';
		$memberlist = array();
		$query = DB::query("SELECT m.uid, m.username, m.groupid, m.email, m.regdate, ms.regip, v.message, v.submittimes, v.submitdate, v.moddate, v.admin, v.remark
				FROM ".DB::table('common_member_validate')." v
				INNER JOIN ".DB::table('common_member')." m ON m.uid=v.uid
				LEFT JOIN ".DB::table('common_member_status')." ms ON ms.uid=m.uid
				WHERE v.status='$filter' ORDER BY v.submitdate DESC LIMIT $start_limit, ".$_G['setting']['memberperpage']	);
		while($member = DB::fetch($query)) {
			if($member['groupid'] != 8) {
				$vuids .= ','.$member['uid'];
				continue;
			}
			$member['regdate'] = dgmdate($member['regdate']);
			$member['submitdate'] = dgmdate($member['submitdate']);
			$member['moddate'] = $member['moddate'] ? dgmdate($member['moddate']) : $lang['none'];
			$member['message'] = dhtmlspecialchars($member['message']);
			$member['admin'] = $member['admin'] ? "<a href=\"home.php?mod=space&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
			$memberlist[] = $member;
		}

		if($vuids) {
			DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($vuids)", 'UNBUFFERED');
		}

		return true;
	}
}

if(empty($modforums['fids'])) {
	return false;
} elseif($_G['fid'] && ($_G['forum']['type'] == 'group' || !$_G['forum']['ismoderator'])) {
	return false;
} else {
	if($_G['fid']) {
		$modfidsadd = "fid='$_G[fid]'";
	} elseif($_G['adminid'] == 1) {
		$modfidsadd = "";
	} else {
		$modfidsadd = "fid in ($modforums[fids])";
	}
}

$updatestat = false;

$op = !in_array($op , array('replies', 'threads')) ? 'threads' : $op;

$filter = !empty($_G['gp_filter']) ? -3 : 0;
$filtercheck = array(0 => '', '-3' => '');
$filtercheck[$filter] = 'selected="selected"';

$pstat = $filter == -3 ? -3 : -2;

$tpp = 10;
$page = max(1, intval($_G['page']));
$start_limit = ($page - 1) * $tpp;

$postlist = array();

$modpost = array('validate' => 0, 'delete' => 0, 'ignore' => 0);
$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());

require_once libfile('function/post');

if(submitcheck('dosubmit', 1) || submitcheck('modsubmit')) {

	$list = array();
	if($_G['gp_moderate'] && is_array($_G['gp_moderate'])) {
		foreach($_G['gp_moderate'] as $val) {
			if(is_numeric($val) && $val) {
				$moderation[$modact][] = $val;
			}
		}
	}

	if(submitcheck('dosubmit', 1)) {

		$_G['gp_handlekey'] = 'mods';
		$list = $moderation[$modact];
		include template('forum/modcp_moderate_float');
		dexit();

	} else {

		$updatestat = $op == 'replies' ? 1 : 2;
		$modpost = array(
			'ignore' => count($moderation['ignore']),
			'delete' => count($moderation['delete']),
			'validate' => count($moderation['validate'])
		);
	}
}

if($op == 'replies') {

	if(submitcheck('modsubmit')) {

		$pmlist = array();
		if($ignorepids = dimplode($moderation['ignore'])) {
			updatepost(array('invisible' => '-3'), "pid IN ($ignorepids) AND invisible='-2' AND first='0' AND ".($modfidsadd ? $modfidsadd : '1'));
		}

		if($deletepids = dimplode($moderation['delete'])) {
			$postarray = getfieldsofposts('pid, authorid, tid, message', "pid IN ($deletepids) AND invisible='$pstat' AND first='0' AND ".($modfidsadd ? $modfidsadd : '1'));
			$pids = '0';
			foreach($postarray as $post) {
				$pids .= ','.$post['pid'];
				if($_G['gp_reason'] != '' && $post['authorid'] && $post['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => 'modreplies_delete',
						'notevar' => array('reason' => dhtmlspecialchars($_G['gp_reason']), 'post' => messagecutstr($post['message'], 30)),
						'authorid' => $post['authorid'],
					);
				}
			}

			if($pids) {
				$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE pid IN ($pids)");
				while($attach = DB::fetch($query)) {
					dunlink($attach);
				}
				DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE pid IN ($pids)", 'UNBUFFERED');
				DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE pid IN ($pids)", 'UNBUFFERED');
				require_once libfile('function/delete');
				deletepost("pid IN ($pids)");
				DB::query("DELETE FROM ".DB::table('forum_trade')." WHERE pid IN ($pids)", 'UNBUFFERED');
			}
			updatemodworks('DLP', count($moderation['delete']));
		}

		$repliesmod = 0;
		if($validatepids = dimplode($moderation['validate'])) {

			$threads = $lastpost = $attachments = $pidarray = array();
			$postarray = getallwithposts(array(
				'select' => 't.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, p.message, p.anonymous',
				'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid",
				'where' => "p.pid IN ($validatepids) AND p.invisible='$pstat' AND p.first='0' AND ".($modfidsadd ? "p.{$modfidsadd}" : '1')
			));
			foreach($postarray as $post) {
				$repliesmod ++;
				$pidarray[] = $post['pid'];
				updatepostcredits('+', $post['authorid'], 'reply', $post['fid']);
				my_post_log('validate', array('pid' => $post['pid']));

				$threads[$post['tid']]['posts']++;
				$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
				", lastpost='$post[dateline]', lastposter='".($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]))."'" : '';
				$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';

				$pm = 'pm_'.$post['pid'];
				if($_G['gp_reason'] != '' && $post['authorid'] && $post['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => 'modreplies_validate',
						'notevar' => array('reason' => dhtmlspecialchars($_G['gp_reason']), 'pid' => $post['pid'], 'tid' => $post['tid'], 'post' => messagecutstr($post['message'], 30)),
						'authorid' => $post['authorid'],
					);
				}
			}
			foreach($threads as $tid => $thread) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
			}
			if($_G['fid']) {
				updateforumcount($_G['fid']);
			} else {
				$fids = array_keys($modforums['list']);
				foreach($fids as $f) {
					updateforumcount($f);
				}
			}

			if(!empty($pidarray)) {
				updatepost(array('invisible' => '0'), "pid IN (0,".implode(',', $pidarray).")");
				$repliesmod = DB::affected_rows();
				updatemodworks('MOD', $repliesmod);
			} else {
				updatemodworks('MOD', 1);
			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$post = $pm['post'];
				$_G['tid'] = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['act'], $pm['notevar'], 1);
			}
		}

		showmessage('modcp_mod_succeed', "{$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&filter=$filter&fid=$_G[fid]");
	}

	$attachlist = array();

	require_once libfile('function/discuzcode');
	require_once libfile('function/attachment');

	$ppp = 10;
	$page = max(1, intval($_G['page']));
	$start_limit = ($page - 1) * $ppp;

	$modcount = getcountofposts(DB::table('forum_post'), "invisible='$pstat' AND first='0' AND ".($modfidsadd ? $modfidsadd : '1'));
	$multipage = multi($modcount, $ppp, $page, "{$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&filter=$filter&fid=$_G[fid]");

	if($modcount) {
		$postarray = getallwithposts(array(
			'select' => 'f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode, p.pid, p.fid, p.tid, p.authorid, p.author, p.subject, p.dateline, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject',
			'from' => DB::table('forum_post')." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=p.fid",
			'where' => "p.invisible='$pstat' AND p.first='0' AND ".($modfidsadd ? "p.{$modfidsadd}" : '1'),
			'order' => 'p.dateline DESC',
			'limit' => "$start_limit, $ppp",
		));

		foreach($postarray as $post) {
			$post['id'] = $post['pid'];
			$post['dateline'] = dgmdate($post['dateline']);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$post['message'] = nl2br(dhtmlspecialchars($post['message']));

			if($post['attachment']) {
				$queryattach = DB::query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM ".DB::table('forum_attachment')." WHERE pid='$post[pid]'");
				while($attach = DB::fetch($queryattach)) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"{$_G[setting][attachurl]}forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					: "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$post['message'] .= "<br /><br />File: ".attachtype(fileext($attach['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}
			$postlist[] = $post;
		}
	}


} else {

	if(submitcheck('modsubmit')) {
		if($ignoretids = dimplode($moderation['ignore'])) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-3' WHERE tid IN ($ignoretids) AND displayorder='-2' AND ".($modfidsadd ? $modfidsadd : '1'));
		}
		$threadsmod = 0;
		$pmlist = array();
		$reason = trim($_G['gp_reason']);

		if($ids = dimplode($moderation['delete'])) {
			$deletetids = '0';
			$recyclebintids = '0';
			$query = DB::query("SELECT tid, fid, authorid, subject FROM ".DB::table('forum_thread')." WHERE tid IN ($ids) AND displayorder='$pstat' AND ".($modfidsadd ? $modfidsadd : '1'));
			while($thread = DB::fetch($query)) {
				if($modforums['recyclebins'][$thread['fid']]) {
					$recyclebintids .= ','.$thread['tid'];
				} else {
					$deletetids .= ','.$thread['tid'];
				}

				if($_G['gp_reason'] != '' && $thread['authorid'] && $thread['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => 'modthreads_delete',
						'notevar' => array('reason' => dhtmlspecialchars($_G['gp_reason']), 'threadsubject' => $thread['subject']),
						'authorid' => $thread['authorid'],
					);
				}
			}

			if($recyclebintids) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-1', moderated='1' WHERE tid IN ($recyclebintids)");
				updatemodworks('MOD', DB::affected_rows());

				updatepost(array('invisible' => '-1'), "tid IN ($recyclebintids)");
				updatemodlog($recyclebintids, 'DEL');
			}

			$query = DB::query("SELECT attachment, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE tid IN ($deletetids)");
			while($attach = DB::fetch($query)) {
				dunlink($attach);
			}

			DB::query("DELETE FROM ".DB::table('forum_thread')." WHERE tid IN ($deletetids)", 'UNBUFFERED');
			require_once libfile('function/delete');
			deletepost("tid IN ($deletetids)");
			DB::query("DELETE FROM ".DB::table('forum_polloption')." WHERE tid IN ($deletetids)");
			DB::query("DELETE FROM ".DB::table('forum_poll')." WHERE tid IN ($deletetids)", 'UNBUFFERED');
			DB::query("DELETE FROM ".DB::table('forum_trade')." WHERE tid IN ($deletetids)", 'UNBUFFERED');
			DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE tid IN ($deletetids)", 'UNBUFFERED');
			DB::query("DELETE FROM ".DB::table('forum_attachmentfield')." WHERE tid IN ($deletetids)", 'UNBUFFERED');
		}

		foreach($moderation['validate'] as $tid) {
			my_thread_log('validate', array('tid' => $tid));
		}
		if($validatetids = dimplode($moderation['validate'])) {

			$tids = $comma = $comma2 = '';
			$moderatedthread = array();
			$query = DB::query("SELECT t.fid, t.tid, t.authorid, t.subject, t.author, t.dateline FROM ".DB::table('forum_thread')." t
				WHERE t.tid IN ($validatetids) AND t.displayorder='$pstat' AND ".($modfidsadd ? "t.{$modfidsadd}" : '1'));
			while($thread = DB::fetch($query)) {
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				updatepostcredits('+', $thread['authorid'], 'post', $thread['fid']);
				$validatedthreads[] = $thread;

				if($_G['gp_reason'] != '' && $thread['authorid'] && $thread['authorid'] != $_G['uid']) {
					$pmlist[] = array(
						'act' => 'modthreads_validate',
						'notevar' => array('reason' => dhtmlspecialchars($_G['gp_reason']), 'tid' => $thread['tid'], 'threadsubject' => $thread['subject']),
						'authorid' => $thread['authorid'],
					);
				}
			}

			if($tids) {

				updatepost(array('invisible' => '0'), "tid IN ($tids) AND first='1'");
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
				$threadsmod = DB::affected_rows();

				if($_G['fid']) {
					updateforumcount($_G['fid']);
				} else {
					$fids = array_keys($modforums['list']);
					foreach($fids as $f) {
						updateforumcount($f);
					}
				}
				updatemodworks('MOD', $threadsmod);
				updatemodlog($tids, 'MOD');

			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$threadsubject = $pm['thread'];
				$_G['tid'] = intval($pm['tid']);
				notification_add($pm['authorid'], 'system', $pm['act'], $pm['notevar'], 1);
			}
		}

		showmessage('modcp_mod_succeed', "{$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&filter=$filter&fid=$_G[fid]");

	}

	$modcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE ".($modfidsadd ? " $modfidsadd AND " : '')." displayorder='$pstat'");
	$multipage = multi($modcount, $_G['tpp'], $page, "{$cpscript}?mod=modcp&action=$_G[gp_action]&op=$op&filter=$filter&fid=$_G[fid]");

	if($modcount) {

		$query = DB::query("SELECT t.tid, t.fid, t.posttableid, t.author, t.sortid, t.authorid, t.subject as tsubject, t.dateline, t.attachment
			FROM ".DB::table('forum_thread')." t
			WHERE ".($modfidsadd ? " t.{$modfidsadd} AND " : '')." t.displayorder='$pstat'
			ORDER BY t.tid DESC LIMIT $start_limit, $_G[tpp]");

		while($thread = DB::fetch($query)) {

			$thread['id'] = $thread['tid'];

			if($thread['authorid'] && $thread['author'] != '') {
				$thread['author'] = "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>";
			} elseif($thread['authorid']) {
				$thread['author'] = "<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">UID $thread[uid]</a>";
			} else {
				$thread['author'] = 'guest';
			}

			$thread['dateline'] = dgmdate($thread['dateline']);
			$posttable = $thread['posttableid'] ? "forum_post_{$thread['posttableid']}" : 'forum_post';
			$post = DB::fetch_first("SELECT pid, message, useip, attachment FROM ".DB::table($posttable)." WHERE tid='{$thread['tid']}' AND first='1'");
			$thread = array_merge($thread, $post);
			$thread['message'] = nl2br(dhtmlspecialchars($thread['message']));

			if($thread['attachment']) {
				require_once libfile('function/attachment');

				$queryattach = DB::query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM ".DB::table('forum_attachment')." WHERE tid='$thread[tid]'");
				while($attach = DB::fetch($queryattach)) {
					$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
					$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					: "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$thread['attach'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			} else {
				$thread['attach'] = '';
			}

			$_G['forum_optiondata'] = $_G['forum_optionlist'] = array();
			if($thread['sortid']) {
				if(@include_once DISCUZ_ROOT.'./data/cache/forum_threadsort_'.$thread['sortid'].'.php') {
					$sortquery = DB::query("SELECT optionid, value FROM ".DB::table('forum_typeoptionvar')." WHERE tid='$thread[tid]'");
					while($option = DB::fetch($sortquery)) {
						$_G['forum_optiondata'][$option['optionid']] = $option['value'];
					}

					foreach($_G['forum_dtype'] as $optionid => $option) {
						$_G['forum_optionlist'][$option['identifier']]['title'] = $_G['forum_dtype'][$optionid]['title'];
						if($_G['forum_dtype'][$optionid]['type'] == 'checkbox') {
							$_G['forum_optionlist'][$option['identifier']]['value'] = '';
							foreach(explode("\t", $_G['forum_optiondata'][$optionid]) as $choiceid) {
								$_G['forum_optionlist'][$option['identifier']]['value'] .= $_G['forum_dtype'][$optionid]['choices'][$choiceid].'&nbsp;';
							}
						} elseif(in_array($_G['forum_dtype'][$optionid]['type'], array('radio', 'select'))) {
							$_G['forum_optionlist'][$option['identifier']]['value'] = $_G['forum_dtype'][$optionid]['choices'][$_G['forum_optiondata'][$optionid]];
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'image') {
							$maxwidth = $_G['forum_dtype'][$optionid]['maxwidth'] ? 'width="'.$_G['forum_dtype'][$optionid]['maxwidth'].'"' : '';
							$maxheight = $_G['forum_dtype'][$optionid]['maxheight'] ? 'height="'.$_G['forum_dtype'][$optionid]['maxheight'].'"' : '';
							$_G['forum_optionlist'][$option['identifier']]['value'] = $_G['forum_optiondata'][$optionid] ? "<a href=\"".$_G['forum_optiondata'][$optionid]."\" target=\"_blank\"><img src=\"".$_G['forum_optiondata'][$optionid]."\"  $maxwidth $maxheight border=\"0\"></a>" : '';
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'url') {
							$_G['forum_optionlist'][$option['identifier']]['value'] = $_G['forum_optiondata'][$optionid] ? "<a href=\"".$_G['forum_optiondata'][$optionid]."\" target=\"_blank\">".$_G['forum_optiondata'][$optionid]."</a>" : '';
						} elseif($_G['forum_dtype'][$optionid]['type'] == 'textarea') {
							$_G['forum_optionlist'][$option['identifier']]['value'] = $_G['forum_optiondata'][$optionid] ? nl2br($_G['forum_optiondata'][$optionid]) : '';
						} else {
							$_G['forum_optionlist'][$option['identifier']]['value'] = $_G['forum_optiondata'][$optionid];
						}
					}
				}

				foreach($_G['forum_optionlist'] as $option) {
					$thread['sortinfo'] .= '<br />'.$option['title'].' '.$option['value'];
				}
			} else {
				$thread['sortinfo'] = '';
			}

			$postlist[] = $thread;
		}
	}

}

?>