<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_delete.php 16722 2010-09-13 09:38:42Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');

function deletemember($uids, $other = 1) {
	$numdeleted = DB::result_first("SELECT count(*) FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
	DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_field_home')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_count')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_log')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_profile')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_verify')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_verify_info')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_status')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_domain')." WHERE id IN ($uids) AND idtype='home'", 'UNBUFFERED');

	DB::query("DELETE FROM ".DB::table('forum_access')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE uid IN ($uids)", 'UNBUFFERED');

	if($other) {
		deleteattach("uid IN ($uids)");
		deletepost("authorid IN ($uids)", true, false);
	}

	//note 刪除空間信息
	//feed
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid IN ($uids) OR (id IN ($uids) AND idtype='uid')", 'UNBUFFERED');

	//note 記錄
	$doids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE uid IN ($uids)");
	while ($value = DB::fetch($query)) {
		$doids[$value['doid']] = $value['doid'];
	}

	DB::query("DELETE FROM ".DB::table('home_doing')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 刪除記錄回復
	$delsql = !empty($doids) ? "doid IN (".dimplode($doids).") OR " : "";
	DB::query("DELETE FROM ".DB::table('home_docomment')." WHERE $delsql uid IN ($uids)", 'UNBUFFERED');

	//note 分享
	DB::query("DELETE FROM ".DB::table('home_share')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 相冊數據
	DB::query("DELETE FROM ".DB::table('home_album')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 刪除積分記錄
	DB::query("DELETE FROM ".DB::table('common_credit_rule_log')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_credit_rule_log_field')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 刪除通知
	DB::query("DELETE FROM ".DB::table('home_notification')." WHERE (uid IN ($uids) OR authorid IN ($uids))", 'UNBUFFERED');

	//note 刪除打招呼
	DB::query("DELETE FROM ".DB::table('home_poke')." WHERE (uid IN ($uids) OR fromuid IN ($uids))", 'UNBUFFERED');

	//note 刪除圖片附件
	$query = DB::query("SELECT filepath, thumb, remote FROM ".DB::table('home_pic')." WHERE uid IN ($uids)");
	while ($value = DB::fetch($query)) {
		deletepicfiles($value);
	}

	//note 數據
	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note blog
	//note 數據刪除
	DB::query("DELETE FROM ".DB::table('home_blog')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('home_blogfield')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 評論
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE (uid IN ($uids) OR authorid IN ($uids) OR (id IN ($uids) AND idtype='uid'))", 'UNBUFFERED');

	//note 訪客
	DB::query("DELETE FROM ".DB::table('home_visitor')." WHERE (uid IN ($uids) OR vuid IN ($uids))", 'UNBUFFERED');

	//note class
	DB::query("DELETE FROM ".DB::table('home_class')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 好友
	DB::query("DELETE FROM ".DB::table('home_friend')." WHERE (uid IN ($uids) OR fuid IN ($uids))", 'UNBUFFERED');

	//note 刪除腳印
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//刪除邀請記錄
	DB::query("DELETE FROM ".DB::table('common_invite')." WHERE (uid IN ($uids) OR fuid IN ($uids))", 'UNBUFFERED');

	//note 刪除郵件隊列
	DB::query("DELETE FROM ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." USING ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." WHERE ".DB::table('common_mailcron').".touid IN ($uids) AND ".DB::table('common_mailcron').".cid=".DB::table('common_mailqueue').".cid", 'UNBUFFERED');

	//note 漫遊邀請
	DB::query("DELETE FROM ".DB::table('common_myinvite')." WHERE (touid IN ($uids) OR fromuid IN ($uids))", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('home_userapp')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('home_userappfield')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 排行榜
	DB::query("DELETE FROM ".DB::table('home_show')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note Manyou Log
	manyoulog('user', $uids, 'delete');

	require_once libfile('function/forum');
	foreach(explode(',', $uids) as $uid) {
		my_thread_log('deluser', array('uid' => $uid));
	}

	DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($uids)", 'UNBUFFERED');
	return $numdeleted;
}

function deletepost($condition, $unbuffered = true, $deleteattach = true) {
	global $_G;
	loadcache('posttableids');
	$num = 0;
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	foreach($posttableids as $id) {
		if($id == 0) {
			DB::delete('forum_post', $condition, 0, $unbuffered);
		} else {
			DB::delete("forum_post_$id", $condition, 0, $unbuffered);
		}
		$num += DB::affected_rows();
	}
	if($deleteattach) {
		deleteattach($condition, $unbuffered);
	}
	!strstr($condition, 'authorid') && DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE $condition", 'UNBUFFERED');
	!strstr($condition, 'authorid') && DB::query("DELETE FROM ".DB::table('forum_poststick')." WHERE $condition", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_postcomment')." WHERE $condition", 'UNBUFFERED');
	return $num;
}

function deletethread($condition, $unbuffered = true) {
	$deletedthreads = 0;
	deleteattach($condition, $unbuffered);
	foreach(array(
		'forum_thread', 'forum_polloption', 'forum_poll', 'forum_trade', 'forum_activity', 'forum_activityapply',
		'forum_debate', 'forum_debatepost', 'forum_threadmod', 'forum_relatedthread',
		'forum_typeoptionvar', 'forum_postposition', 'forum_poststick', 'forum_pollvoter') as $table) {
		DB::delete($table, $condition, 0, $unbuffered);
		if($table == 'forum_thread') {
			$deletedthreads = DB::affected_rows();
		}
	}
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE ".str_replace('tid', 'id', $condition)." AND idtype='tid'", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_postcomment')." WHERE $condition", 'UNBUFFERED');
	return $deletedthreads;
}

function deleteattach($condition, $unbuffered = true) {
	$pics = array();
	$query = DB::query("SELECT attachment, thumb, remote, aid, picid FROM ".DB::table('forum_attachment')." WHERE $condition AND pid>0");
	while($attach = DB::fetch($query)) {
		if($attach['picid']) {
			$pics[] = $attach['picid'];
		}
		dunlink($attach);
	}
	if($pics) {
		$albumids = array();
		$query = DB::query("SELECT albumid FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($pics).") GROUP BY albumid");
		DB::delete('home_pic', 'picid IN ('.dimplode($pics).')', 0);
		while($album = DB::fetch($query)) {
			DB::update('home_album', array('picnum' => getcount('home_pic', array('albumid' => $album['albumid']))), array('albumid' => $album['albumid']));
		}
	}
	DB::delete('forum_attachment', $condition.' AND pid>0', 0, $unbuffered);
	DB::delete('forum_attachmentfield', $condition.' AND pid>0', 0, $unbuffered);
}

function deletecomments($cids) {
	global $_G;

	$blognums = $newcids = $dels = $counts = array();
	$allowmanage = checkperm('managecomment');

	$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid IN (".dimplode($cids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['authorid'] == $_G['uid'] || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newcids[] = $value['cid'];
			if($value['authorid'] != $_G['uid'] && $value['uid'] != $_G['uid']) {
				$counts[$value['authorid']]['coef'] -= 1;
			}
			if($value['idtype'] == 'blogid') {
				$blognums[$value['id']]++;
			}
		}
	}

	if(empty($dels)) return array();

	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE cid IN (".dimplode($newcids).")");

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

	DB::query("DELETE FROM ".DB::table('home_blog')." WHERE blogid IN (".dimplode($newblogids).")");
	DB::query("DELETE FROM ".DB::table('home_blogfield')." WHERE blogid IN (".dimplode($newblogids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newblogids).") AND idtype='blogid'");
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($newblogids).") AND idtype='blogid'");

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('publishblog', $uid, array('blogs' => $setarr['blogs']), $setarr['coef']);
		}
	}

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

	DB::query("DELETE FROM ".DB::table('home_share')." WHERE sid IN (".dimplode($newsids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newsids).") AND idtype='sid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newsids).") AND idtype='sid'");

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

	DB::query("DELETE FROM ".DB::table('home_doing')." WHERE doid IN (".dimplode($newdoids).")");
	DB::query("DELETE FROM ".DB::table('home_docomment')." WHERE doid IN (".dimplode($newdoids).")");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newdoids).") AND idtype='doid'");

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
		if($_G['setting']['my_app_status']) manyoulog('user', $uid, 'delete');
		return true;
	} else {
		return false;
	}
}

function deletepics($picids) {
	global $_G;

	$sizes = $pics = $newids = array();
	$allowmanage = checkperm('managealbum');

	$albumids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($picids).")");
	while ($value = DB::fetch($query)) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$pics[] = $value;
			$newids[] = $value['picid'];

			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];
		}
	}
	if(empty($pics)) return array();

	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($newids).")");
	DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE picid IN (".dimplode($newids).")");
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE id IN (".dimplode($newids).") AND idtype='picid'");

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

	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE albumid IN (".dimplode($newids).")");
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

	if($pics) {
		deletepicfiles($pics);
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

	$articles = $trashid = $pushs = $dels = array();
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_trash')." WHERE aid IN (".dimplode($aids).")");
	while ($value = DB::fetch($query)) {
		$dels[$value['aid']] = $value['aid'];
		$article = unserialize($value['content']);
		$articles[$article['aid']] = $article;
		if(!empty($article['idtype'])) $pushs[$article['idtype']][] = $article['id'];
		if($article['pic']) {
			@unlink($_G['config']['attachdir'].'./'.$article['pic']);
		}
	}

	if($dels) {
		DB::query('DELETE FROM '.DB::table('portal_article_trash')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');
		deletearticlepush($pushs);
		deletearticlerelated($dels);
	}

	return $articles;
}


function deletearticle($aids, $istrash = 1) {
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

		DB::query('DELETE FROM '.DB::table('portal_article_title')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

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

	DB::query('DELETE FROM '.DB::table('portal_article_count')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');
	DB::query('DELETE FROM '.DB::table('portal_article_content')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

	$query = DB::query("SELECT * FROM ".DB::table('portal_attachment')." WHERE aid IN (".dimplode($dels).")");
	while ($value = DB::fetch($query)) {
		$attachment[] = $value;
		$attachdel[] = $value['attachid'];
	}
	foreach ($attachment as $value) {
		pic_delete($value['attachment'], 'portal', $value['thumb'], $value['remote']);
	}
	DB::query("DELETE FROM ".DB::table('portal_attachment')." WHERE aid IN (".dimplode($dels).")", 'UNBUFFERED');

	DB::query('DELETE FROM '.DB::table('portal_comment')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

	DB::query('DELETE FROM '.DB::table('portal_article_related')." WHERE aid IN(".dimplode($dels).")", 'UNBUFFERED');

}

function deleteportaltopic($dels) {
	if(empty($dels)) return false;
	$targettplname = array();
	foreach ((array)$dels as $key => $value) {
		$targettplname[] = 'portal/portal_topic_content_'.$value;
	}
	DB::delete('common_diy_data', "targettplname IN (".dimplode($targettplname).")", 0, true);

	deletedomain($dels, 'topic');
	DB::delete('common_template_permission', "targettplname IN (".dimplode($targettplname).")", 0, true);

	$bids = array();
	$query = DB::query('SELECT bid FROM '.DB::table('common_template_block').' WHERE targettplname IN ('.dimplode($targettplname).')');
	while ($value = DB::fetch($query)) {
		$bids[] = $value['bid'];
	}
	$bids = dimplode($bids);
	if(!empty($bids)) {
		DB::query('DELETE FROM '.DB::table('common_block').' WHERE bid IN ('.$bids.')', 'UNBUFFERED');
		DB::query('DELETE FROM '.DB::table('common_block_permission').' WHERE bid IN ('.$bids.')', 'UNBUFFERED');
	}
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
	}

	DB::delete('portal_topic', 'topicid IN ('.dimplode($dels).')', 0, true);

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