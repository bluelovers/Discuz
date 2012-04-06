<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_delete.php 29030 2012-03-23 02:35:21Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');

function deletemember($uids, $delpost = true) {
	if(!$uids) {
		return;
	}
	require_once libfile('function/sec');
	updateMemberOperate($uids, 2);

	if($delpost) {
		deleteattach($uids, 'uid');
		deletepost($uids, 'authorid');
	}
	require_once libfile('function/forum');
	foreach($uids as $uid) {
		my_thread_log('deluser', array('uid' => $uid));
	}

	$uids = dimplode($uids);
	$numdeleted = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
	foreach(array('common_member_field_forum', 'common_member_field_home', 'common_member_count', 'common_member_log', 'common_member_profile',
		'common_member_verify', 'common_member_verify_info', 'common_member_status', 'common_member_validate', 'common_member_magic',
		'forum_access', 'forum_moderator', 'common_member_action_log') as $table) {
		DB::delete($table, "uid IN ($uids)");
	}

	$doids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE uid IN ($uids)");
	while($value = DB::fetch($query)) {
		$doids[$value['doid']] = $value['doid'];
	}
	$delsql = !empty($doids) ? "doid IN (".dimplode($doids).") OR " : "";

	DB::delete('home_docomment', "$delsql uid IN ($uids)");
	DB::delete('common_domain', "id IN ($uids) AND idtype='home'");
	DB::delete('home_feed', "uid IN ($uids) OR (id IN ($uids) AND idtype='uid')");
	DB::delete('home_notification', "uid IN ($uids) OR authorid IN ($uids)");
	DB::delete('home_poke', "uid IN ($uids) OR fromuid IN ($uids)");
	DB::delete('home_comment', "(uid IN ($uids) OR authorid IN ($uids) OR (id IN ($uids) AND idtype='uid'))");
	DB::delete('home_visitor', "uid IN ($uids) OR vuid IN ($uids)");
	DB::delete('home_friend', "uid IN ($uids) OR fuid IN ($uids)");
	DB::delete('home_friend_request', "uid IN ($uids) OR fuid IN ($uids)");
	DB::delete('common_invite', "uid IN ($uids) OR fuid IN ($uids)");
	DB::delete('common_myinvite', "touid IN ($uids) OR fromuid IN ($uids)");
	DB::delete('common_moderate', "id IN (".$uids.") AND idtype='uid_cid'");

	$query = DB::query("SELECT filepath, thumb, remote FROM ".DB::table('home_pic')." WHERE uid IN ($uids)");
	while($value = DB::fetch($query)) {
		$pics[] = $value;
	}
	deletepicfiles($pics);

	include_once libfile('function/home');
	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE uid IN ($uids)");
	while($value = DB::fetch($query)) {
		pic_delete($value['pic'], 'album', 0, ($value['picflag'] == 2 ? 1 : 0));
	}

	DB::query("DELETE FROM ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." USING ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." WHERE ".DB::table('common_mailcron').".touid IN ($uids) AND ".DB::table('common_mailcron').".cid=".DB::table('common_mailqueue').".cid", 'UNBUFFERED');

	foreach(array('home_doing', 'home_share', 'home_album', 'common_credit_rule_log', 'common_credit_rule_log_field',
		'home_pic', 'home_blog', 'home_blogfield', 'home_class', 'home_clickuser',
		'home_userapp', 'home_userappfield', 'home_show', 'common_member') as $table) {
		DB::delete($table, "uid IN ($uids)");
	}

	manyoulog('user', $uids, 'delete');
	return $numdeleted;
}

function deletepost($ids, $idtype = 'pid', $credit = false, $posttableid = false, $recycle = false) {
	global $_G;
	$recycle = $recycle && $idtype == 'pid' ? true : false;
	if($_G['setting']['plugins'][HOOKTYPE.'_deletepost']) {
		$_G['deletepostids'] = & $ids;
		$hookparam = func_get_args();
		hookscript('deletepost', 'global', 'funcs', array('param' => $hookparam, 'step' => 'check'), 'deletepost');
	}
	if(!$ids || !in_array($idtype, array('authorid', 'tid', 'pid'))) {
		return 0;
	}

	loadcache('posttableids');
	$posttableids = !empty($_G['cache']['posttableids']) ? ($posttableid !== false && in_array($posttableid, $_G['cache']['posttableids']) ? array($posttableid) : $_G['cache']['posttableids']): array('0');

	if($idtype == 'pid') {
		require_once libfile('function/forum');
		foreach($ids as $pid) {
			my_post_log('delete', array('pid' => $pid));
		}
	}
	$count = count($ids);
	$idsstr = dimplode($ids);

	if($credit) {
		$tuidarray = $ruidarray = array();
		foreach($posttableids as $id) {
			$query = DB::query('SELECT tid, pid, first, authorid, replycredit, invisible FROM '.DB::table(getposttable($id))." WHERE $idtype IN ($idsstr)");
			while($post = DB::fetch($query)) {
				if($post['invisible'] != -1 && $post['invisible'] != -5) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
						if($post['authorid'] > 0 && $post['replycredit'] > 0) {
							$replycredit_list[$post['authorid']][$post['tid']] += $post['replycredit'];
						}
					}
					$tids[] = $post['tid'];
				}
			}
		}

		if($tuidarray || $ruidarray) {
			require_once libfile('function/post');
		}
		if($tuidarray) {
			foreach($tuidarray as $fid => $tuids) {
				updatepostcredits('-', $tuids, 'post', $fid);
			}
		}
		if($ruidarray) {
			foreach($ruidarray as $fid => $ruids) {
				updatepostcredits('-', $ruids, 'reply', $fid);
			}
		}
	}

	foreach($posttableids as $id) {
		if($recycle) {
			DB::query("UPDATE ".DB::table(getposttable($id))." SET invisible='-5' WHERE pid IN ($idsstr)");
		} else {
			foreach(array(getposttable($id), 'forum_postcomment') as $table) {
				DB::delete($table, "$idtype IN ($idsstr)");
			}
			DB::delete('forum_trade', ($idtype == 'authorid' ? 'sellerid' : $idtype)." IN ($idsstr)");
			DB::delete('home_feed', "id IN ($idsstr) AND idtype='".($idtype == 'authorid' ? 'uid' : $idtype)."'");
		}
	}
	if(!$recycle && $idtype != 'authorid') {
		foreach(array('forum_postposition', 'forum_poststick') as $table) {
			DB::delete($table, "$idtype IN ($idsstr)");
		}
	}
	if($idtype == 'pid') {
		DB::delete('forum_postcomment', "rpid IN ($idsstr)");
		DB::delete('common_moderate', "id IN ($idsstr) AND idtype='pid'");
	}
	if($replycredit_list) {
		$query = DB::query("SELECT tid, extcreditstype FROM ".DB::table('forum_replycredit')." WHERE tid IN (".dimplode($tids).")");
		while($rule = DB::fetch($query)) {
			$rule['extcreditstype'] = $rule['extcreditstype'] ? $rule['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
			$replycredity_rule[$rule['tid']] = $rule;
		}
		foreach($replycredit_list AS $uid => $tid_credit) {
			foreach($tid_credit AS $tid => $credit) {
				$uid_credit[$replycredity_rule[$tid]['extcreditstype']] -= $credit;
			}
			updatemembercount($uid, $uid_credit, true);
		}
	}
	if(!$recycle) {
		deleteattach($ids, $idtype);
	}
	if($_G['setting']['plugins'][HOOKTYPE.'_deletepost']) {
		hookscript('deletepost', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletepost');
	}
	return $count;
}

function deletethreadcover($tids) {
	global $_G;
	loadcache(array('threadtableids', 'posttableids'));
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array(0);
	$deletecover = array();
	foreach($threadtableids as $tableid) {
		if(!$tableid) {
			$threadtable = "forum_thread";
		} else {
			$threadtable = "forum_thread_$tableid";
		}
		$query = DB::query("SELECT cover, tid FROM ".DB::table($threadtable)." WHERE tid IN ($tids)");
		while($row = DB::fetch($query)) {
			if($row['cover']) {
				$deletecover[$row['tid']] = $row['cover'];
			}
		}
	}
	if($deletecover) {
		foreach($deletecover as $tid => $cover) {
			$filename = getthreadcover($tid, 0, 1);
			$remote = $cover < 0 ? 1 : 0;
			dunlink(array('attachment' => $filename, 'remote' => $remote, 'thumb' => 0));
		}
	}
}

function deletethread($tids, $membercount = false, $credit = false, $ponly = false) {
	global $_G;
	if($_G['setting']['plugins'][HOOKTYPE.'_deletethread']) {
		$_G['deletethreadtids'] = & $tids;
		$hookparam = func_get_args();
		hookscript('deletethread', 'global', 'funcs', array('param' => $hookparam, 'step' => 'check'), 'deletethread');
	}
	if(!$tids) {
		return 0;
	}
	require_once libfile('function/forum');
	foreach($tids as $tid) {
		my_post_log('delete', array('tid' => $tid));
	}
	$count = count($tids);
	$tids = dimplode($tids);

	loadcache(array('threadtableids', 'posttableids'));
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
	$posttableids = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : array('0');
	if(!in_array(0, $threadtableids)) {
		$threadtableids = array_merge(array(0), $threadtableids);
	}

	DB::delete('common_moderate', "id IN ($tids) AND idtype='tid'");

	$atids = $fids = $postids = $threadtables = array();
	foreach($threadtableids as $tableid) {
		$threadtable = !$tableid ? "forum_thread" : "forum_thread_$tableid";
		$query = DB::query("SELECT cover, tid, fid, posttableid FROM ".DB::table($threadtable)." WHERE tid IN ($tids)");
		while($row = DB::fetch($query)) {
			$atids[] = $row['tid'];
			$row['posttableid'] = !empty($row['posttableid']) && in_array($row['posttableid'], $posttableids) ? $row['posttableid'] : '0';
			$postids[$row['posttableid']][$row['tid']] = $row['tid'];
			if($tableid) {
				$fids[$row['fid']][] = $tableid;
			}
		}
		if(!$tableid && !$ponly) {
			$threadtables[] = $threadtable;
		}
	}

	if($credit || $membercount) {
		$losslessdel = $_G['setting']['losslessdel'] > 0 ? TIMESTAMP - $_G['setting']['losslessdel'] * 86400 : 0;

		$postlist = $uidarray = $tuidarray = $ruidarray = array();
		foreach($postids as $posttableid => $posttabletids) {
			$query = DB::query('SELECT tid, first, authorid, dateline, replycredit, invisible FROM '.DB::table(getposttable($posttableid)).' WHERE tid IN ('.dimplode($posttabletids).')');
			while($post = DB::fetch($query)) {
				if($post['invisible'] != -1 && $post['invisible'] != -5) {
					$postlist[] = $post;
				}
			}
		}
		$query = DB::query("SELECT tid, extcreditstype FROM ".DB::table('forum_replycredit')." WHERE tid IN ($tids)");
		while($rule = DB::fetch($query)) {
			$rule['extcreditstype'] = $rule['extcreditstype'] ? $rule['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
			$replycredit_rule[$rule['tid']] = $rule;
		}

		foreach($postlist as $post) {
			if($post['dateline'] < $losslessdel) {
				if($membercount) {
					if($post['first']) {
						updatemembercount($post['authorid'], array('threads' => -1, 'post' => -1), false);
					} else {
						updatemembercount($post['authorid'], array('posts' => -1), false);
					}
				}
			} else {
				if($credit) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
					}
				}
			}
			if($credit || $membercount) {
				if($post['authorid'] > 0 && $post['replycredit'] > 0) {
					if($replycredit_rule[$post['tid']]['extcreditstype']) {
						updatemembercount($post['authorid'], array($replycredit_rule[$post['tid']]['extcreditstype'] => (int)('-'.$post['replycredit'])));
					}
				}
			}
		}

		if($credit) {
			if($tuidarray || $ruidarray) {
				require_once libfile('function/post');
			}
			if($tuidarray) {
				foreach($tuidarray as $fid => $tuids) {
					updatepostcredits('-', $tuids, 'post', $fid);
				}
			}
			if($ruidarray) {
				foreach($ruidarray as $fid => $ruids) {
					updatepostcredits('-', $ruids, 'reply', $fid);
				}
			}
			$auidarray = $attachtables = array();
			foreach($atids as $tid) {
				$attachtables[getattachtablebytid($tid)][] = $tid;
			}
			foreach($attachtables as $attachtable => $attachtids) {
				$query = DB::query("SELECT uid, dateline FROM ".DB::table($attachtable)." WHERE tid IN (".dimplode($attachtids).")");
				while($attach = DB::fetch($query)) {
					if($attach['dateline'] > $losslessdel) {
						$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
					}
				}
			}
			if($auidarray) {
				$postattachcredits = !empty($_G['forum']['postattachcredits']) ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];
				updateattachcredits('-', $auidarray, $postattachcredits);
			}
		}
	}

	if($ponly) {
		if($_G['setting']['plugins'][HOOKTYPE.'_deletethread']) {
			hookscript('deletethread', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletethread');
		}
		DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-1', digest='0', moderated='1' WHERE tid IN ($tids)");
		foreach($postids as $posttableid=>$oneposttids) {
			updatepost(array('invisible' => '-1'), "tid IN ($tids)");
		}
		return $count;
	}

	DB::delete('forum_replycredit', "tid IN ($tids)");
	DB::delete('common_credit_log', "operation IN ('RCT', 'RCA', 'RCB') AND relatedid IN ($tids)");

	deletethreadcover($tids);
	foreach($threadtables as $threadtable) {
		DB::delete($threadtable, "tid IN ($tids)");
	}

	if($atids) {
		foreach($postids as $posttableid=>$oneposttids) {
			deletepost($oneposttids, 'tid', false, $posttableid);
		}
		deleteattach($atids, 'tid');
	}
	if($fids) {
		foreach($fids as $fid => $tableids) {
			$tableids = array_unique($tableids);
			foreach($tableids as $tableid) {
				$query = DB::query("SELECT COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM ".DB::table("forum_thread_$tableid")." WHERE fid='$fid'");
				while($row = DB::fetch($query)) {
					DB::insert('forum_forum_threadtable', array('fid' => $fid, 'threadtableid' => $tableid, 'threads' => intval($row['threads']), 'posts' => intval($row['posts'])), false, true);
				}
			}
		}
	}

	foreach(array('forum_forumrecommend', 'forum_polloption', 'forum_poll', 'forum_activity', 'forum_activityapply', 'forum_debate',
		'forum_debatepost', 'forum_threadmod', 'forum_relatedthread', 'forum_typeoptionvar',
		'forum_postposition', 'forum_poststick', 'forum_pollvoter', 'forum_threadimage') as $table) {
		DB::delete($table, "tid IN ($tids)");
	}
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN ($tids) AND idtype='tid'", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_tagitem')." WHERE idtype='tid' AND itemid IN ($tids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_threadrush')." WHERE tid IN ($tids)", 'UNBUFFERED');
	if($_G['setting']['plugins'][HOOKTYPE.'_deletethread']) {
		hookscript('deletethread', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletethread');
	}
	return $count;
}

function deleteattach($ids, $idtype = 'aid') {
	global $_G;
	if(!$ids || !in_array($idtype, array('authorid', 'uid', 'tid', 'pid'))) {
		return;
	}
	$idtype = $idtype == 'authorid' ? 'uid' : $idtype;
	$ids = dimplode($ids);

	$pics = $attachtables = array();
	$query = DB::query("SELECT aid, tableid FROM ".DB::table('forum_attachment')." WHERE $idtype IN ($ids) AND pid>0");
	while($attach = DB::fetch($query)) {
		$attachtables[$attach['tableid']][] = $attach['aid'];
	}

	foreach($attachtables as $attachtable => $aids) {
		if($attachtable == 127) {
			continue;
		}
		$attachtable = 'forum_attachment_'.$attachtable;
		$aids = dimplode($aids);
		$query = DB::query("SELECT attachment, thumb, remote, aid, picid FROM ".DB::table($attachtable)." WHERE aid IN ($aids) AND pid>0");
		while($attach = DB::fetch($query)) {
			if($attach['picid']) {
				$pics[] = $attach['picid'];
			}
			dunlink($attach);
		}
		DB::delete($attachtable, "aid IN ($aids) AND pid>0");
	}
	DB::delete('forum_attachment', "$idtype IN ($ids) AND pid>0");
	if($pics) {
		$albumids = array();
		$query = DB::query("SELECT albumid FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($pics).") GROUP BY albumid");
		DB::delete('home_pic', 'picid IN ('.dimplode($pics).')', 0);
		while($album = DB::fetch($query)) {
			DB::update('home_album', array('picnum' => getcount('home_pic', array('albumid' => $album['albumid']))), array('albumid' => $album['albumid']));
		}
	}
}

function deletecomments($cids) {
	global $_G;

	$deltypes = $blognums = $newcids = $dels = $counts = array();
	$allowmanage = checkperm('managecomment');

	$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid IN (".dimplode($cids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['authorid'] == $_G['uid'] || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newcids[] = $value['cid'];
			$deltypes[$value['idtype']] = $value['idtype'].'_cid';
			if($value['authorid'] != $_G['uid'] && $value['uid'] != $_G['uid']) {
				$counts[$value['authorid']]['coef'] -= 1;
			}
			if($value['idtype'] == 'blogid') {
				$blognums[$value['id']]++;
			}
		}
	}

	if(empty($dels)) return array();

	DB::delete('home_comment', "cid IN (".dimplode($newcids).")");
	DB::delete('common_moderate', "id IN (".dimplode($newcids).") AND idtype IN(".dimplode($deltypes).")");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('comment', $uid, array(), $setarr['coef']);
		}
	}
	if($blognums) {
		$nums = renum($blognums);
		foreach ($nums[0] as $num) {
			DB::query("UPDATE ".DB::table('home_blog')." SET replynum=replynum-$num WHERE blogid IN (".dimplode($nums[1][$num]).")");
		}
	}
	return $dels;
}

function deleteblogs($blogids) {
	global $_G;

	$blogs = $newblogids = $counts = array();
	$allowmanage = checkperm('manageblog');

	$query = DB::query("SELECT * FROM ".DB::table('home_blog')." WHERE blogid IN (".dimplode($blogids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$blogs[] = $value;
			$newblogids[] = $value['blogid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['blogs'] -= 1;
		}
	}
	if(empty($blogs)) return array();

	DB::delete('home_blog', "blogid IN (".dimplode($newblogids).")");
	DB::delete('home_blogfield', "blogid IN (".dimplode($newblogids).")");
	DB::delete('home_comment', "id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::delete('home_feed', "id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::delete('home_clickuser', "id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::delete('common_moderate', "id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::delete('common_moderate', "id IN (".dimplode($newblogids).") AND idtype='blogid_cid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('publishblog', $uid, array('blogs' => $setarr['blogs']), $setarr['coef']);
		}
	}

	DB::query("DELETE FROM ".DB::table('common_tagitem')." WHERE idtype='blogid' AND itemid IN (".dimplode($newblogids).")");

	return $blogs;
}

function deletefeeds($feedids) {
	global $_G;

	$allowmanage = checkperm('managefeed');

	$feeds = $newfeedids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_feed')." WHERE feedid IN (".dimplode($feedids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$newfeedids[] = $value['feedid'];
			$feeds[] = $value;
		}
	}

	if(empty($newfeedids)) return array();

	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE feedid IN (".dimplode($newfeedids).")");

	return $feeds;
}

function deleteshares($sids) {
	global $_G;

	$allowmanage = checkperm('manageshare');

	$shares = $newsids = $counts = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid IN (".dimplode($sids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$shares[] = $value;
			$newsids[] = $value['sid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['sharings'] -= 1;
		}
	}
	if(empty($shares)) return array();

	DB::delete('home_share', "sid IN (".dimplode($newsids).")");
	DB::delete('home_comment', "id IN (".dimplode($newsids).") AND idtype='sid'");
	DB::delete('home_feed', "id IN (".dimplode($newsids).") AND idtype='sid'");
	DB::delete('common_moderate', "id IN (".dimplode($newsids).") AND idtype='sid'");
	DB::delete('common_moderate', "id IN (".dimplode($newsids).") AND idtype='sid_cid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('createshare', $uid, array('sharings' => $setarr['sharings']), $setarr['coef']);
		}
	}

	return $shares;
}

function deletedoings($ids) {
	global $_G;

	$allowmanage = checkperm('managedoing');

	$doings = $newdoids = $counts = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid IN (".dimplode($ids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$doings[] = $value;
			$newdoids[] = $value['doid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['doings'] -= 1;
		}
	}

	if(empty($doings)) return array();

	DB::delete('home_doing', "doid IN (".dimplode($newdoids).")");
	DB::delete('home_docomment', "doid IN (".dimplode($newdoids).")");
	DB::delete('home_feed', "id IN (".dimplode($newdoids).") AND idtype='doid'");
	DB::delete('common_moderate', "id IN (".dimplode($newdoids).") AND idtype='doid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('doing', $uid, array('doings' => $setarr['doings']), $setarr['coef']);
		}
	}

	return $doings;
}

function deletespace($uid) {
	global $_G;

	$allowmanage = checkperm('managedelspace');

	if($allowmanage) {
		DB::query("UPDATE ".DB::table('common_member')." SET status='1' WHERE uid='$uid'");
		manyoulog('user', $uid, 'delete');
		return true;
	} else {
		return false;
	}
}

function deletepics($picids) {
	global $_G;

	$albumids = $sizes = $pics = $newids = array();
	$allowmanage = checkperm('managealbum');

	$haveforumpic = false;
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($picids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$pics[] = $value;
			$newids[] = $value['picid'];
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];
			if(!$haveforumpic && $value['remote'] > 1) {
				$haveforumpic = true;
			}
		}
	}
	if(empty($pics)) return array();

	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($newids).")");
	if($haveforumpic) {
		for($i = 0;$i < 10;$i++) {
			DB::query("UPDATE ".DB::table('forum_attachment_'.$i)." SET picid='0' WHERE picid IN (".dimplode($newids).")");
		}
	}

	DB::delete('home_comment', "id IN (".dimplode($newids).") AND idtype='picid'");
	DB::delete('home_feed', "id IN (".dimplode($newids).") AND idtype='picid'");
	DB::delete('home_clickuser', "id IN (".dimplode($newids).") AND idtype='picid'");
	DB::delete('common_moderate', "id IN (".dimplode($newids).") AND idtype='picid'");
	DB::delete('common_moderate', "id IN (".dimplode($newids).") AND idtype='picid_cid'");

	if($sizes) {
		foreach ($sizes as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array('attachsize' => -$attachsize), false);
		}
	}

	require_once libfile('function/spacecp');
	foreach ($albumids as $albumid) {
		if($albumid) {
			album_update_pic($albumid);
		}
	}

	deletepicfiles($pics);

	return $pics;
}

function deletepicfiles($pics) {
	global $_G;
	$remotes = array();
	include_once libfile('function/home');
	foreach ($pics as $pic) {
		pic_delete($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
	}
}

function deletealbums($albumids) {
	global $_G;

	$sizes = $dels = $newids = $counts = array();
	$allowmanage = checkperm('managealbum');

	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid IN (".dimplode($albumids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newids[] = $value['albumid'];
			if(!empty($value['pic'])) {
				include_once libfile('function/home');
				pic_delete($value['pic'], 'album', 0, ($value['picflag'] == 2 ? 1 : 0));
			}
		}
		$counts[$value['uid']]['albums'] -= 1;
	}
	if(empty($dels)) return array();

	$pics = $picids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE albumid IN (".dimplode($newids).")");
	while ($value = DB::fetch($query)) {
		$pics[] = $value;
		$picids[] = $value['picid'];
		$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
	}

	if($picids) {
		deletepics($picids);
	}
	DB::query("DELETE FROM ".DB::table('home_album')." WHERE albumid IN (".dimplode($newids).")");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newids).") AND idtype='albumid'");
	if($picids) DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($picids).") AND idtype='picid'");

	if($sizes) {
		foreach ($sizes as $uid => $value) {
			$attachsize = intval($sizes[$uid]);
			$albumnum = $counts[$uid]['albums'] ? $counts[$uid]['albums'] : 0;
			updatemembercount($uid, array('albums' => $albumnum, 'attachsize' => -$attachsize), false);
		}
	}

	return $dels;
}

function deletepolls($pids) {
	global $_G;


	$counts = $polls = $newpids = array();
	$allowmanage = checkperm('managepoll');

	$query = DB::query("SELECT * FROM ".DB::table('home_poll')." WHERE pid IN (".dimplode($pids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$polls[] = $value;
			$newpids[] = $value['pid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['polls'] -= 1;
		}
	}
	if(empty($polls)) return array();

	DB::query("DELETE FROM ".DB::table('home_poll')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_pollfield')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_polloption')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_polluser')." WHERE pid IN (".dimplode($newpids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newpids).") AND idtype='pid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newpids).") AND idtype='pid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('createpoll', $uid, array('polls' => $setarr['polls']), $setarr['coef']);
		}
	}

	return $polls;

}


function deletetrasharticle($aids) {
	global $_G;

	require_once libfile('function/home');
	$articles = $trashid = $pushs = $dels = array();
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_trash')." WHERE aid IN (".dimplode($aids).")");
	while ($value = DB::fetch($query)) {
		$dels[$value['aid']] = $value['aid'];
		$article = unserialize($value['content']);
		$articles[$article['aid']] = $article;
		if(!empty($article['idtype'])) $pushs[$article['idtype']][] = $article['id'];
		if($article['pic']) {
			pic_delete($article['pic'], 'portal', $article['thumb'], $article['remote']);
		}
	}

	if($dels) {
		DB::query('DELETE FROM '.DB::table('portal_article_trash')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');
		deletearticlepush($pushs);
		deletearticlerelated($dels);
	}

	return $articles;
}

function deletearticle($aids, $istrash = true) {
	global $_G;

	if(empty($aids)) return false;
	$trasharr = $article = $bids = $dels = $attachment = $attachaid = $catids = $pushs = array();
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid IN (".dimplode($aids).")");
	while ($value = DB::fetch($query)) {
		$catids[] = intval($value['catid']);
		$dels[$value['aid']] = $value['aid'];
		$article[] = $value;
		if(!empty($value['idtype'])) $pushs[$value['idtype']][] = $value['id'];
	}
	if($dels) {
		foreach($article as $key => $value) {
			if($istrash) {
				$valstr = daddslashes(serialize($value));
				$trasharr[] = "('$value[aid]', '$valstr')";
			} elseif($value['pic']) {
				pic_delete($value['pic'], 'portal', $value['thumb'], $value['remote']);
				$attachaid[] = $value['aid'];
			}
		}
		if($istrash) {
			if($trasharr) {
				DB::query("INSERT INTO ".DB::table('portal_article_trash')." (`aid`, `content`) VALUES ".implode(',', $trasharr));
			}
		} else {
			deletearticlepush($pushs);
			deletearticlerelated($dels);
		}

		DB::delete('portal_article_title', "aid IN(".dimplode($dels).")");
		DB::delete('common_moderate', "id IN (".dimplode($dels).") AND idtype='aid'");

		$catids = array_unique($catids);
		if($catids) {
			foreach($catids as $catid) {
				$cnt = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_article_title')." WHERE catid = '$catid'");
				DB::update('portal_category', array('articles'=>$cnt), array('catid'=>$catid));
			}
		}
	}
	return $article;
}

function deletearticlepush($pushs) {
	if(!empty($pushs) && is_array($pushs)) {
		foreach($pushs as $idtype=> $fromids) {
			switch ($idtype) {
				case 'blogid':
					if(!empty($fromids)) DB::update('home_blogfield',array('pushedaid'=>'0'), 'blogid IN ('.dimplode($fromids).')');
					break;
				case 'tid':
					if(!empty($fromids)) $a = DB::update('forum_thread',array('pushedaid'=>'0'), 'tid IN ('.dimplode($fromids).')');
					break;
			}
		}
	}
}

function deletearticlerelated($dels) {

	DB::delete('portal_article_count', "aid IN(".dimplode($dels).")");
	DB::delete('portal_article_content', "aid IN(".dimplode($dels).")");

	$query = DB::query("SELECT * FROM ".DB::table('portal_attachment')." WHERE aid IN (".dimplode($dels).")");
	while ($value = DB::fetch($query)) {
		$attachment[] = $value;
		$attachdel[] = $value['attachid'];
	}
	require_once libfile('function/home');
	foreach ($attachment as $value) {
		pic_delete($value['attachment'], 'portal', $value['thumb'], $value['remote']);
	}
	DB::delete('portal_attachment', "aid IN (".dimplode($dels).")");

	DB::delete('portal_comment', "id IN(".dimplode($dels).") AND idtype='aid'");
	DB::delete('common_moderate', "id IN (".dimplode($dels).") AND idtype='aid_cid'");

	DB::delete('portal_article_related', "aid IN(".dimplode($dels).")");

}

function deleteportaltopic($dels) {
	if(empty($dels)) return false;
	$targettplname = array();
	foreach ((array)$dels as $key => $value) {
		$targettplname[] = 'portal/portal_topic_content_'.$value;
	}
	DB::delete('common_diy_data', "targettplname IN (".dimplode($targettplname).")", 0, true);

	require_once libfile('class/blockpermission');
	$tplpermission = & template_permission::instance();
	$templates = array();
	$tplpermission->delete_allperm_by_tplname($targettplname);

	deletedomain($dels, 'topic');
	DB::delete('common_template_block', 'targettplname IN ('.dimplode($targettplname).')', 0, true);

	require_once libfile('function/home');

	$picids = array();
	$query = DB::query('SELECT * FROM '.DB::table('portal_topic').' WHERE topicid IN ('.dimplode($dels).')');
	while ($value = DB::fetch($query)) {
		if($value['picflag'] != '0') pic_delete(str_replace('portal/', '', $value['cover']), 'portal', 0, $value['picflag'] == '2' ? '1' : '0');
	}

	$picids = array();
	$query = DB::query('SELECT * FROM '.DB::table('portal_topic_pic').' WHERE topicid IN ('.dimplode($dels).')');
	while ($value = DB::fetch($query)) {
		$picids[] = $value['picid'];
		pic_delete($value['filepath'], 'portal', $value['thumb'], $value['remote']);
	}
	if (!empty($picids)) {
		DB::delete('portal_topic_pic', 'picid IN ('.dimplode($picids).')', 0, true);
	}

	foreach ($targettplname as $key => $value) {
		@unlink(DISCUZ_ROOT.'./data/diy/'.$value.'.htm');
		@unlink(DISCUZ_ROOT.'./data/diy/'.$value.'.htm.bak');
		@unlink(DISCUZ_ROOT.'./data/diy/'.$value.'_preview.htm');
	}

	DB::delete('portal_topic', 'topicid IN ('.dimplode($dels).')');
	DB::delete('portal_comment', "id IN(".dimplode($dels).") AND idtype='topicid'");
	DB::delete('common_moderate', "id IN (".dimplode($dels).") AND idtype='topicid_cid'");

	include_once libfile('function/block');
	block_clear();

	include_once libfile('function/cache');
	updatecache('diytemplatename');
}

function deletedomain($ids, $idtype) {
	if($ids && $idtype) {
		$ids = !is_array($ids) ? array($ids) : $ids;
		DB::query('DELETE FROM '.DB::table('common_domain')." WHERE id IN(".dimplode($ids).") AND idtype='$idtype'", 'UNBUFFERED');
	}
}

?>