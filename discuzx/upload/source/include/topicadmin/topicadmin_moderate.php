<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_moderate.php 26389 2011-12-12 07:23:37Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!empty($_G['tid'])) {
	$_G['gp_moderate'] = array($_G['tid']);
}

$allow_operation = array('delete', 'highlight', 'open', 'close', 'stick', 'digest', 'bump', 'down', 'recommend', 'type', 'move', 'recommend_group');

$operations = empty($_G['gp_operations']) ? array() : $_G['gp_operations'];
if($operations && $operations != array_intersect($operations, $allow_operation) || (!$_G['group']['allowdelpost'] && in_array('delete', $operations)) || (!$_G['group']['allowstickthread'] && in_array('stick', $operations))) {
	showmessage('admin_moderate_invalid');
}

$threadlist = $loglist = $posttablearr = array();
$recommend_group_count = 0;
$operation = getgpc('operation');
loadcache('threadtableids');
$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
if(!in_array(0, $threadtableids)) {
	$threadtableids = array_merge(array(0), $threadtableids);
}

if($tids = dimplode($_G['gp_moderate'])) {
	foreach($threadtableids as $tableid) {
		$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
		$query = DB::query("SELECT * FROM ".DB::table($threadtable)." WHERE tid IN ($tids) AND fid='$_G[fid]' LIMIT $_G[tpp]");
		if(DB::num_rows($query)) {
			break;
		}
	}
	while($thread = DB::fetch($query)) {
		if($thread['closed'] > 1 && $operation && !in_array($operation, array('delete', 'highlight', 'stick', 'digest', 'bump', 'down')) || $thread['displayorder'] < 0 && $thread['displayorder'] != -4) {
			if($operation == 'recommend_group') {
				$recommend_group_count ++;
			}
			continue;
		}
		$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
		$thread['dblastpost'] = $thread['lastpost'];
		$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
		$posttablearr[$thread['posttableid'] ? $thread['posttableid'] : 0][] = $thread['tid'];
		$threadlist[$thread['tid']] = $thread;
		$_G['tid'] = empty($_G['tid']) ? $thread['tid'] : $_G['tid'];
	}
}
if(empty($threadlist)) {
	if($recommend_group_count) {
		showmessage('recommend_group_invalid');
	}
	showmessage('admin_moderate_invalid');
}

$modpostsnum = count($threadlist);
$single = $modpostsnum == 1 ? TRUE : FALSE;
$frommodcp = getgpc('frommodcp');
switch($frommodcp) {
	case '1':
		$_G['referer'] = "forum.php?mod=modcp&action=thread&fid=$_G[fid]&op=thread&do=".($frommodcp == 1 ? '' : 'list');
		break;
	case '2':
		$_G['referer'] = "forum.php?mod=modcp&action=forum&op=recommend".(getgpc('show') ? "&show=getgpc('show')" : '')."&fid=$_G[fid]";
		break;
	default:
		if((in_array('delete', $operations) || in_array('move', $operations)) && !strpos($_SERVER['HTTP_REFERER'], 'search.php?mod=forum')) {
			$_G['referer'] = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_G['gp_listextra']) ? '&'.rawurldecode($_G['gp_listextra']) : '');
		} else {
			$_G['referer'] = $_G['gp_redirect'];
		}
}

$optgroup = $_G['gp_optgroup'] = isset($_G['gp_optgroup']) ? intval($_G['gp_optgroup']) : 0;
$expirationstick = getgpc('expirationstick');

$defaultcheck = array();
foreach ($allow_operation as $v) {
	$defaultcheck[$v] = '';
}
$defaultcheck[$operation] = 'checked="checked"';

if(!submitcheck('modsubmit')) {

	$stickcheck  = $closecheck = $digestcheck = array('', '', '', '', '');
	$expirationdigest = $expirationhighlight = $expirationclose = '';

	if($_G['gp_optgroup'] == 1 && $single) {
		empty($threadlist[$_G['tid']]['displayorder']) ? $stickcheck[0] ='selected="selected"' : $stickcheck[$threadlist[$_G['tid']]['displayorder']] = 'selected="selected"';
		empty($threadlist[$_G['tid']]['digest']) ? $digestcheck[0] = 'selected="selected"' : $digestcheck[$threadlist[$_G['tid']]['digest']] = 'selected="selected"';
		$string = sprintf('%02d', $threadlist[$_G['tid']]['highlight']);
		$stylestr = sprintf('%03b', $string[0]);
		for($i = 1; $i <= 3; $i++) {
			$stylecheck[$i] = $stylestr[$i - 1] ? 1 : 0;
		}
		$colorcheck = $string[1];
		$_G['forum']['modrecommend'] = is_array($_G['forum']['modrecommend']) ? $_G['forum']['modrecommend'] : array();
	} elseif($_G['gp_optgroup'] == 2 || $_G['gp_optgroup'] == 5) {
		require_once libfile('function/forumlist');
		$forumselect = forumselect(FALSE, 0, $threadlist[$_G['tid']]['fid']);
		$typeselect = typeselect($single ? $threadlist[$_G['tid']]['typeid'] : 0);
	} elseif($_G['gp_optgroup'] == 4 && $single) {
		empty($threadlist[$_G['tid']]['closed']) ? $closecheck[0] = 'checked="checked"' : $closecheck[1] = 'checked="checked"';
	}

	$imgattach = array();
	if(count($threadlist) == 1 && $operation == 'recommend') {
		$query = DB::query("SELECT * FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE tid='$_G[tid]' AND isimage IN ('1', '-1')");
		while($row = DB::fetch($query)) {
			$imgattach[] = $row;
		}
		$query = DB::query("SELECT * FROM ".DB::table('forum_forumrecommend')." WHERE tid='$_G[tid]'");
		if($oldthread = DB::fetch($query)) {
			$threadlist[$_G['tid']]['subject'] = $oldthread['subject'];
			$selectposition[$oldthread['position']] = ' selected="selected"';
			$selectattach = $oldthread['aid'];
		} else {
			$selectattach = $imgattach[0]['aid'];
			$selectposition[0] = ' selected="selected"';
		}
	}
	include template('forum/topicadmin');

} else {

	$moderatetids = dimplode(array_keys($threadlist));
	$reason = checkreasonpm();
	$stampstatus = 0;
	$stampaction = 'SPA';
	if(empty($operations)) {
		showmessage('admin_nonexistence');
	} else {
		$posts = $images = array();
		foreach($operations as $operation) {

			$updatemodlog = TRUE;
			if($operation == 'stick') {
				$sticklevel = intval($_G['gp_sticklevel']);
				if($sticklevel < 0 || $sticklevel > 3 || $sticklevel > $_G['group']['allowstickthread']) {
					showmessage('no_privilege_stickthread');
				}
				$expiration = checkexpiration($_G['gp_expirationstick'], $operation);
				$expirationstick = $sticklevel ? $_G['gp_expirationstick'] : 0;

				$forumstickthreads = $_G['setting']['forumstickthreads'];
				$forumstickthreads = isset($forumstickthreads) ? unserialize($forumstickthreads) : array();
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='$sticklevel', moderated='1' WHERE tid IN ($moderatetids)");
				$delkeys = array_keys($threadlist);
				foreach($delkeys as $k) {
					unset($forumstickthreads[$k]);
				}
				$forumstickthreads = addslashes(serialize($forumstickthreads));
				DB::query("UPDATE ".DB::table('common_setting')." SET svalue='$forumstickthreads' WHERE skey='forumstickthreads'");

				$stickmodify = 0;
				foreach($threadlist as $thread) {
					$stickmodify = (in_array($thread['displayorder'], array(2, 3)) || in_array($sticklevel, array(2, 3))) && $sticklevel != $thread['displayorder'] ? 1 : $stickmodify;
				}

				if($_G['setting']['globalstick'] && $stickmodify) {
					require_once libfile('function/cache');
					updatecache('globalstick');
				}

				$modaction = $sticklevel ? ($expiration ? 'EST' : 'STK') : 'UST';
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('STK', 'UST', 'EST', 'UES')", 'UNBUFFERED');

				if(!$sticklevel) {
					$stampaction = 'SPD';
				}

				$stampstatus = 1;

			} elseif($operation == 'highlight') {
				if(!$_G['group']['allowhighlightthread']) {
					showmessage('no_privilege_highlightthread');
				}
				$highlight_style = $_G['gp_highlight_style'];
				$highlight_color = $_G['gp_highlight_color'];
				$expiration = checkexpiration($_G['gp_expirationhighlight'], $operation);
				$stylebin = '';
				for($i = 1; $i <= 3; $i++) {
					$stylebin .= empty($highlight_style[$i]) ? '0' : '1';
				}

				$highlight_style = bindec($stylebin);
				if($highlight_style < 0 || $highlight_style > 7 || $highlight_color < 0 || $highlight_color > 8) {
					showmessage('parameters_error ');
				}

				DB::query("UPDATE ".DB::table('forum_thread')." SET highlight='$highlight_style$highlight_color', moderated='1' WHERE tid IN ($moderatetids)", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('forum_forumrecommend')." SET highlight='$highlight_style$highlight_color' WHERE tid IN ($moderatetids)", 'UNBUFFERED');

				$modaction = ($highlight_style + $highlight_color) ? ($expiration ? 'EHL' : 'HLT') : 'UHL';
				$expiration = $modaction == 'UHL' ? 0 : $expiration;
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('HLT', 'UHL', 'EHL', 'UEH')", 'UNBUFFERED');

			} elseif($operation == 'digest') {
				$digestlevel = intval($_G['gp_digestlevel']);
				if($digestlevel < 0 || $digestlevel > 3 || $digestlevel > $_G['group']['allowdigestthread']) {
					showmessage('no_privilege_digestthread');
				}
				$expiration = checkexpiration($_G['gp_expirationdigest'], $operation);
				$expirationdigest = $digestlevel ? $expirationdigest : 0;

				DB::query("UPDATE ".DB::table('forum_thread')." SET digest='$digestlevel', moderated='1' WHERE tid IN ($moderatetids)");

				foreach($threadlist as $thread) {
					if($thread['digest'] != $digestlevel) {
						if($digestlevel == $thread['digest']) continue;
						$extsql = array();
						if($digestlevel > 0 && $thread['digest'] == 0) {
							$extsql = array('digestposts' => 1);
						}
						if($digestlevel == 0 && $thread['digest'] > 0) {
							$extsql = array('digestposts' => -1);
						}
						if($digestlevel == 0) {
							$stampaction = 'SPD';
						}
						updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $digestlevel - $thread['digest']);
					}
				}

				$modaction = $digestlevel ? ($expiration ? 'EDI' : 'DIG') : 'UDG';
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('DIG', 'UDI', 'EDI', 'UED')", 'UNBUFFERED');

				$stampstatus = 2;

			} elseif($operation == 'recommend') {
				if(!$_G['group']['allowrecommendthread']) {
					showmessage('no_privilege_recommendthread');
				}
				$isrecommend = $_G['gp_isrecommend'];
				$modrecommend = !empty($_G['forum']['modrecommend']) ? $_G['forum']['modrecommend'] : array();
				$imgw = $modrecommend['imagewidth'] ? intval($modrecommend['imagewidth']) : 200;
				$imgh = $modrecommend['imageheight'] ? intval($modrecommend['imageheight']) : 150;
				$expiration = checkexpiration($_G['gp_expirationrecommend'], $operation);
				DB::query("UPDATE ".DB::table('forum_thread')." SET moderated='1' WHERE tid IN ($moderatetids)");
				$modaction = $isrecommend ? 'REC' : 'URE';
				$thread = daddslashes($thread, 1);
				$selectattach = $_G['gp_selectattach'];

				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('REC')", 'UNBUFFERED');
				if($isrecommend) {
					$addthread = $comma = '';
					$oldrecommendlist = array();
					$query = DB::query("SELECT * FROM ".DB::table('forum_forumrecommend')." WHERE tid IN ($moderatetids)");
					while($row = DB::fetch($query)) {
						$oldrecommendlist[$row['tid']] = $row;
					}
					foreach($threadlist as $thread) {
						if(count($threadlist) > 1) {
							if($oldrecommendlist[$thread['tid']]) {
								$oldthread = $oldrecommendlist[$thread['tid']];
								$reducetitle = $oldthread['subject'];
								$selectattach = $oldthread['aid'];
								$typeid = $oldthread['typeid'];
								$position = $oldthread['position'];
							} else {
								$reducetitle = $thread['subject'];
								$typeid = 0;
								$position = 0;
							}
						} else {
							if(empty($_G['gp_reducetitle'])) {
								$reducetitle = $thread['subject'];
							} else {
								$reducetitle = $_G['gp_reducetitle'];
							}
							$typeid = $selectattach ? 1 : 0;
							empty($_G['gp_position']) && $position = 0;
						}
						if($selectattach) {
							$key = md5($selectattach.'|'.$imgw.'|'.$imgh);
							$filename = $selectattach."\t".$imgw."\t".$imgh."\t".$key;
						} else {
							$selectattach = 0;
							$filename = '';
						}

						$addthread .= $comma."('$thread[fid]', '$thread[tid]', '$typeid', '0', '".addslashes($reducetitle)."', '".addslashes($thread['author'])."', '$thread[authorid]', '$_G[uid]', '$expiration', '$position', '$selectattach', '$filename', '$thread[highlight]')";
						$comma = ', ';
						$reducetitle = '';
					}
					if($addthread) {
						DB::query("REPLACE INTO ".DB::table('forum_forumrecommend')." (fid, tid, typeid, displayorder, subject, author, authorid, moderatorid, expiration, position, aid, filename, highlight) VALUES $addthread");
					}

				} else {
					DB::query("DELETE FROM ".DB::table('forum_forumrecommend')." WHERE fid='$_G[fid]' AND tid IN ($moderatetids)");
					$stampaction = 'SPD';
				}
				$stampstatus = 3;

			} elseif($operation == 'bump') {
				if(!$_G['group']['allowbumpthread']) {
					showmessage('no_privilege_bumpthread');
				}
				$modaction = 'BMP';
				$thread = $threadlist;
				$thread = array_pop($thread);
				$thread['subject'] = addslashes($thread['subject']);
				$thread['lastposter'] = addslashes($thread['lastposter']);

				DB::query("UPDATE ".DB::table('forum_thread')." SET lastpost='$_G[timestamp]', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$thread[tid]\t$thread[subject]\t$_G[timestamp]\t$thread[lastposter]' WHERE fid='$_G[fid]'");

				$_G['forum']['threadcaches'] && deletethreadcaches($thread['tid']);
			} elseif($operation == 'down') {
				if(!$_G['group']['allowbumpthread']) {
					showmessage('no_privilege_downthread');
				}
				$modaction = 'DWN';
				$downtime = TIMESTAMP - 86400 * 730;
				DB::query("UPDATE ".DB::table('forum_thread')." SET lastpost='$downtime', moderated='1' WHERE tid IN ($moderatetids)");

				$_G['forum']['threadcaches'] && deletethreadcaches($thread['tid']);
			} elseif($operation == 'delete') {
				if(!$_G['group']['allowdelpost']) {
					showmessage('no_privilege_delpost');
				}
				loadcache('threadtableids');
				$stickmodify = 0;
				$deleteredirect = $remarkclosed = array();
				foreach($threadlist as $thread) {
					if($thread['digest']) {
						updatecreditbyaction('digest', $thread['authorid'], array('digestposts' => -1), '', -$thread['digest']);
					}
					if(in_array($thread['displayorder'], array(2, 3))) {
						$stickmodify = 1;
					}
					if($_G['forum']['status'] == 3 && $thread['closed'] > 1) {
						$deleteredirect[] = $thread['closed'];
					}
					if($thread['isgroup'] == 1 && $thread['closed'] > 1) {
						$remarkclosed[] = $thread['closed'];
					}
				}

				$modaction = 'DEL';
				require_once libfile('function/delete');
				$tids = array_keys($threadlist);
				if($_G['forum']['recyclebin']) {

					deletethread($tids, true, true, true);
					manage_addnotify('verifyrecycle', $modpostsnum);
				} else {

					deletethread($tids, true, true);
					$updatemodlog = FALSE;
				}

				$forumstickthreads = $_G['setting']['forumstickthreads'];
				$forumstickthreads = !empty($forumstickthreads) ? unserialize($forumstickthreads) : array();
				$delkeys = array_keys($threadlist);
				foreach($delkeys as $k) {
					unset($forumstickthreads[$k]);
				}
				$forumstickthreads = addslashes(serialize($forumstickthreads));
				DB::query("UPDATE ".DB::table('common_setting')." SET svalue='$forumstickthreads' WHERE skey='forumstickthreads'");

				DB::delete('forum_forum_threadtable', "threads='0'");
				if(!empty($deleteredirect)) {
					deletethread($deleteredirect);
				}
				if(!empty($remarkclosed)) {
					DB::update('forum_thread', array('closed' => 0), "tid IN (".dimplode($remarkclosed).")");
				}

				if($_G['setting']['globalstick'] && $stickmodify) {
					require_once libfile('function/cache');
					updatecache('globalstick');
				}

				updateforumcount($_G['fid']);
			} elseif($operation == 'close') {
				if(!$_G['group']['allowclosethread']) {
					showmessage('no_privilege_closethread');
				}
				$expiration = checkexpiration($_G['gp_expirationclose'], $operation);
				$modaction = $expiration ? 'ECL' : 'CLS';

				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN','ECL','UCL','EOP','UEO')", 'UNBUFFERED');
			} elseif($operation == 'open') {
				if(!$_G['group']['allowclosethread']) {
					showmessage('no_privilege_openthread');
				}
				$expiration = checkexpiration($_G['gp_expirationopen'], $operation);
				$modaction = $expiration ? 'EOP' : 'OPN';

				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='0', moderated='1' WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN','ECL','UCL','EOP','UEO')", 'UNBUFFERED');
			} elseif($operation == 'move') {
				if(!$_G['group']['allowmovethread']) {
					showmessage('no_privilege_movethread');
				}
				$moveto = $_G['gp_moveto'];
				$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, f.allowpostspecial, ff.threadplugin FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$moveto' AND f.status='1' AND f.type<>'group'");

				if(!$toforum) {
					showmessage('admin_move_invalid');
				} elseif($_G['fid'] == $toforum['fid']) {
					continue;
				} else {
					$moveto = $toforum['fid'];
					$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
					$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
					if($modnewthreads || $modnewreplies) {
						showmessage('admin_move_have_mod');
					}
				}

				if($_G['adminid'] == 3) {
					if($_G['member']['accessmasks']) {
						$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowgetimage, a.allowpostattach';
						$accessadd2 = "LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid='$moveto'";
					}
					$priv = DB::fetch_first("SELECT ff.postperm, m.uid AS istargetmod $accessadd1
							FROM ".DB::table('forum_forumfield')." ff
							$accessadd2
							LEFT JOIN ".DB::table('forum_moderator')." m ON m.fid='$moveto' AND m.uid='$_G[uid]'
							WHERE ff.fid='$moveto'");
					if((($priv['postperm'] && !in_array($_G['groupid'], explode("\t", $priv['postperm']))) || ($_G['member']['accessmasks'] && ($priv['allowview'] || $priv['allowreply'] || $priv['allowgetattach'] || $priv['allowpostattach']) && !$priv['allowpost'])) && !$priv['istargetmod']) {
						showmessage('admin_move_nopermission');
					}
				}

				$moderate = array();
				$stickmodify = 0;
				$toforumallowspecial = array(
					1 => $toforum['allowpostspecial'] & 1,
					2 => $toforum['allowpostspecial'] & 2,
					3 => isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($toforum['allowpostspecial'] & 4),
					4 => $toforum['allowpostspecial'] & 8,
					5 => $toforum['allowpostspecial'] & 16,
					127 => $_G['setting']['threadplugins'] ? unserialize($toforum['threadplugin']) : array(),
				);
				foreach($threadlist as $tid => $thread) {
					$allowmove = 0;
					if(!$thread['special']) {
						$allowmove = 1;
					} else {
						if($thread['special'] != 127) {
							$allowmove = $toforum['allowpostspecial'] ? $toforumallowspecial[$thread['special']] : 0;
						} else {
							if($toforumallowspecial[127]) {
								$posttable = getposttablebytid($thread['tid']);
								$message = DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
								$sppos = strrpos($message, chr(0).chr(0).chr(0));
								$specialextra = substr($message, $sppos + 3);
								$allowmove = in_array($specialextra, $toforumallowspecial[127]);
							} else {
								$allowmove = 0;
							}
						}
					}

					if($allowmove) {
						$moderate[] = $tid;
						if(in_array($thread['displayorder'], array(2, 3))) {
							$stickmodify = 1;
						}
						if($_G['gp_type'] == 'redirect') {
							$thread = daddslashes($thread, 1);
							DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment, typeid)
								VALUES ('$thread[fid]', '$thread[readperm]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '$thread[dblastpost]', '".addslashes($thread['lastposter'])."', '0', '0', '0', '0', '$thread[tid]', '0', '0', '$_G[gp_threadtypeid]')");
						}
					}
				}

				if(!$moderatetids = implode(',', $moderate)) {
					showmessage('admin_moderate_invalid');
				}

				$displayorderadd = $_G['adminid'] == 3 ? ', displayorder=\'0\'' : '';
				DB::query("UPDATE ".DB::table('forum_thread')." SET fid='$moveto', moderated='1', isgroup='0', typeid='$_G[gp_threadtypeid]' $displayorderadd WHERE tid IN ($moderatetids)");
				DB::query("UPDATE ".DB::table('forum_forumrecommend')." SET fid='$moveto' WHERE tid IN ($moderatetids)");
				updatepost(array('fid' => $moveto), "tid IN ($moderatetids)");

				if($_G['setting']['globalstick'] && $stickmodify) {
					require_once libfile('function/cache');
					updatecache('globalstick');
				}
				$modaction = 'MOV';

				updateforumcount($moveto);
				updateforumcount($_G['fid']);
			} elseif($operation == 'type') {
				if(!$_G['group']['allowedittypethread']) {
					showmessage('no_privilege_edittypethread');
				}
				if(!isset($_G['forum']['threadtypes']['types'][$_G['gp_typeid']]) && ($_G['gp_typeid'] != 0 || $_G['forum']['threadtypes']['required'])) {
					showmessage('admin_type_invalid');
				}

				DB::query("UPDATE ".DB::table('forum_thread')." SET typeid='$_G[gp_typeid]', moderated='1' WHERE tid IN ($moderatetids)");
				$modaction = 'TYP';
			} elseif($operation == 'recommend_group') {
				if($_G['forum']['status'] != 3 || !in_array($_G['adminid'], array(1, 2))) {
					showmessage('undefined_action');
				}
				$moveto = $_G['gp_moveto'];
				$toforum = DB::fetch_first("SELECT f.fid, f.name, f.modnewposts, f.allowpostspecial, ff.threadplugin FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.fid='$moveto' AND f.status='1' AND f.type<>'group'");

				if(!$toforum) {
					showmessage('admin_move_invalid');
				} elseif($_G['fid'] == $toforum['fid']) {
					continue;
				}
				$moderate = array();
				$toforumallowspecial = array(
					1 => $toforum['allowpostspecial'] & 1,
					2 => $toforum['allowpostspecial'] & 2,
					3 => isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && ($toforum['allowpostspecial'] & 4),
					4 => $toforum['allowpostspecial'] & 8,
					5 => $toforum['allowpostspecial'] & 16,
					127 => $_G['setting']['threadplugins'] ? unserialize($toforum['threadplugin']) : array(),
				);
				foreach($threadlist as $tid => $thread) {
					$allowmove = 0;
					if($thread['closed']) {
						continue;
					}
					if(!$thread['special']) {
						$allowmove = 1;
					} else {
						if($thread['special'] != 127) {
							$allowmove = $toforum['allowpostspecial'] ? $toforumallowspecial[$thread['special']] : 0;
						} else {
							if($toforumallowspecial[127]) {
								$posttable = getposttablebytid($thread['tid']);
								$message = DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
								$sppos = strrpos($message, chr(0).chr(0).chr(0));
								$specialextra = substr($message, $sppos + 3);
								$allowmove = in_array($specialextra, $toforumallowspecial[127]);
							} else {
								$allowmove = 0;
							}
						}
					}

					if($allowmove) {
						$moderate[] = $tid;
						$thread = daddslashes($thread, 1);
						DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, readperm, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment, isgroup)
							VALUES ('$moveto', '$thread[readperm]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '".TIMESTAMP."', '".addslashes($thread['lastposter'])."', '$thread[views]', '$thread[replies]', '0', '$thread[digest]', '$thread[tid]', '$thread[special]', '$thread[attachment]', '$thread[isgroup]')");
						$newtid = DB::insert_id();
						DB::query("UPDATE ".DB::table('forum_thread')." SET closed='$newtid' WHERE tid='$thread[tid]'");
					}
				}
				if(!$moderatetids = implode(',', $moderate)) {
					showmessage('admin_succeed', $_G['referer']);
				}
				$modaction = 'REG';
			}

			if(in_array($operation, array('stick', 'highlight', 'digest', 'bump', 'down', 'delete', 'move', 'close', 'open'))) {

				foreach($_G['gp_moderate'] as $tid) {
					if(!$tid = max(0, intval($tid))) continue;
					$my_opt = $operation == 'stick' ? 'sticky' : $operation;
					$data = array('tid' => $tid);
					if($my_opt == 'move') $data['otherid'] = $toforum['fid'];

					my_thread_log($my_opt, $data);
				}
			}

			if($updatemodlog) {
				if($operation != 'delete') {
					updatemodlog($moderatetids, $modaction, $expiration);
				} else {
					updatemodlog($moderatetids, $modaction, $expiration, 0, $reason);
				}
			}

			updatemodworks($modaction, $modpostsnum);
			foreach($threadlist as $thread) {
				modlog($thread, $modaction);
			}

			if($sendreasonpm) {
				$modactioncode = lang('forum/modaction');
				$modaction = $modactioncode[$modaction];
				foreach($threadlist as $thread) {
					if($operation == 'move') {
						sendreasonpm($thread, 'reason_move', array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason), 'tofid' => $toforum['fid'], 'toname' => $toforum['name']));
					} else {
						sendreasonpm($thread, 'reason_moderate', array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason)));
					}
				}
			}

			if($stampstatus) {
				set_stamp($stampstatus, $stampaction, $threadlist, $expiration);
			}

		}
		showmessage('admin_succeed', $_G['referer']);
	}

}

function checkexpiration($expiration, $operation) {
	global $_G;
	if(!empty($expiration) && in_array($operation, array('recommend', 'stick', 'digest', 'highlight', 'close'))) {
		$expiration = strtotime($expiration) - $_G['setting']['timeoffset'] * 3600 + date('Z');
		if(dgmdate($expiration, 'Ymd') <= dgmdate(TIMESTAMP, 'Ymd') || ($expiration > TIMESTAMP + 86400 * 180)) {
			showmessage('admin_expiration_invalid');
		}
	} else {
		$expiration = 0;
	}
	return $expiration;
}

function set_stamp($typeid, $stampaction, &$threadlist, $expiration) {
	global $_G;
	$moderatetids = dimplode(array_keys($threadlist));
	if(empty($threadlist)) {
		return false;
	}
	if(array_key_exists($typeid, $_G['cache']['stamptypeid'])) {
		if($stampaction == 'SPD') {
			DB::query("UPDATE ".DB::table('forum_thread')." SET stamp='-1' WHERE tid IN ($moderatetids)");
		} else {
			DB::query("UPDATE ".DB::table('forum_thread')." SET stamp='".$_G['cache']['stamptypeid'][$typeid]."' WHERE tid IN ($moderatetids)");
		}
		!empty($moderatetids) && updatemodlog($moderatetids, $stampaction, $expiration, 0, '', $_G['cache']['stamptypeid'][$typeid]);
	}
}

?>