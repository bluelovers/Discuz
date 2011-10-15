<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_post.php 24761 2011-10-11 00:16:14Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getattach($pid, $posttime = 0, $aids = '') {
	global $_G;

	require_once libfile('function/attachment');
	$attachs = $imgattachs = array();
	$aids = $aids ? explode('|', $aids) : array();
	if($aids) {
		$aidsnew = array();
		foreach($aids as $aid) {
			if($aid) {
				$aidsnew[] = intval($aid);
			}
		}
		$aids = "aid IN (".dimplode($aidsnew).") AND";
	} else {
		$aids = '';
	}
	$sqladd1 = $posttime > 0 ? "AND af.dateline>'$posttime'" : '';
	if(!empty($_G['fid']) && $_G['forum']['attachextensions']) {
		$allowext = str_replace(' ', '', strtolower($_G['forum']['attachextensions']));
		$allowext = explode(',', $allowext);
	} else {
		$allowext = '';
	}
	$query = DB::query("SELECT a.*, af.*
		FROM ".DB::table('forum_attachment')." a
		LEFT JOIN ".DB::table('forum_attachment_unused')." af USING(aid)
		WHERE $aids (af.uid='$_G[uid]' AND a.tid='0' $sqladd1) ORDER BY a.aid DESC");
	while($attach = DB::fetch($query)) {
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = strtolower(fileext($attach['filename']));
		if($allowext && !in_array($attach['ext'], $allowext)) {
			continue;
		}
		getattach_row($attach, $attachs, $imgattachs);
	}
	if($pid > 0) {
		$query = DB::query("SELECT a.*, af.*
			FROM ".DB::table('forum_attachment')." a
			LEFT JOIN ".DB::table(getattachtablebytid($_G['tid']))." af USING(aid)
			WHERE a.pid='$pid' ORDER BY a.aid DESC");
		while($attach = DB::fetch($query)) {
			$attach['filenametitle'] = $attach['filename'];
			$attach['ext'] = fileext(strtolower($attach['filename']));
			if($allowext && !in_array($attach['ext'], $allowext)) {
				continue;
			}
			getattach_row($attach, $attachs, $imgattachs);
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}

function getattach_row($attach, &$attachs, &$imgattachs) {
	global $_G;
	$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
	$attach['attachsize'] = sizecount($attach['filesize']);
	$attach['dateline'] = dgmdate($attach['dateline']);
	$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
	if($attach['isimage'] < 1) {
		if($attach['isimage']) {
			$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
			$attach['width'] = $attach['width'] > 300 ? 300 : $attach['width'];
		}
		if($attach['pid']) {
			$attachs['used'][] = $attach;
		} else {
			$attachs['unused'][] = $attach;
		}
	} else {
		$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'/forum';
		$attach['width'] = $attach['width'] > 300 ? 300 : $attach['width'];
		if($attach['pid']) {
			$imgattachs['used'][] = $attach;
		} else {
			$imgattachs['unused'][] = $attach;
		}
	}
}

function parseattachmedia($attach) {
	$attachurl = 'attach://'.$attach['aid'].'.'.$attach['ext'];
	switch(strtolower($attach['ext'])) {
		case 'mp3':
		case 'wma':
		case 'ra':
		case 'ram':
		case 'wav':
		case 'mid':
			return '[audio]'.$attachurl.'[/audio]';
		case 'wmv':
		case 'rm':
		case 'rmvb':
		case 'avi':
		case 'asf':
		case 'mpg':
		case 'mpeg':
		case 'mov':
		case 'flv':
		case 'swf':
			return '[media='.$attach['ext'].',400,300]'.$attachurl.'[/media]';
		default:
			return;
	}
}

function ftpupload($aids, $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];

	if(!$aids || !$_G['setting']['ftp']['on']) {
		return;
	}
	$attachtables = $pics = array();
	$query = DB::query("SELECT aid, tableid FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($aids).") AND uid='$uid'");
	while($attach = DB::fetch($query)) {
		$attachtables[$attach['tableid']][] = $attach['aid'];
	}
	foreach($attachtables as $attachtable => $aids) {
		$attachtable = 'forum_attachment_'.$attachtable;
		$query = DB::query("SELECT aid, thumb, attachment, filename, filesize, picid FROM ".DB::table($attachtable)." WHERE aid IN (".dimplode($aids).") AND remote='0'");
		$aids = array();
		while($attach = DB::fetch($query)) {
			$attach['ext'] = fileext(strtolower($attach['filename']));
			if(((!$_G['setting']['ftp']['allowedexts'] && !$_G['setting']['ftp']['disallowedexts']) || ($_G['setting']['ftp']['allowedexts'] && in_array($attach['ext'], $_G['setting']['ftp']['allowedexts'])) || ($_G['setting']['ftp']['disallowedexts'] && !in_array($attach['ext'], $_G['setting']['ftp']['disallowedexts']))) && (!$_G['setting']['ftp']['minsize'] || $attach['filesize'] >= $_G['setting']['ftp']['minsize'] * 1024)) {
				if(ftpcmd('upload', 'forum/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'forum/'.getimgthumbname($attach['attachment'])))) {
					dunlink($attach);
					$aids[] = $attach['aid'];
					if($attach['picid']) {
						$pics[] = $attach['picid'];
					}
				}
			}
		}

		if($aids) {
			DB::update($attachtable, array('remote' => 1), "aid IN (".dimplode($aids).")");
		}
	}
	if($pics) {
		DB::update('home_pic', array('remote' => 3), "picid IN (".dimplode($pics).")");
	}
}

function updateattach($modnewthreads, $tid, $pid, $attachnew, $attachupdate = array(), $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];
	$uidadd = $_G['forum']['ismoderator'] ? '' : " AND uid='$uid'";
	if($attachnew) {
		$newaids = array_keys($attachnew);
		$newattach = $newattachfile = $albumattach = array();
		$query = DB::query("SELECT * FROM ".DB::table('forum_attachment_unused')." WHERE aid IN (".dimplode($newaids).")$uidadd");
		while($attach = DB::fetch($query)) {
			$newattach[$attach['aid']] = daddslashes($attach);
			if($attach['isimage']) {
				$newattachfile[$attach['aid']] = $attach['attachment'];
			}
		}
		if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
			require_once libfile('class/image');
			$image = new image;
		}
		if(!empty($_G['gp_albumaid'])) {
			array_unshift($_G['gp_albumaid'], '');
			$_G['gp_albumaid'] = array_unique($_G['gp_albumaid']);
			unset($_G['gp_albumaid'][0]);
			foreach($_G['gp_albumaid'] as $aid) {
				if(isset($newattach[$aid])) {
					$albumattach[$aid] = $newattach[$aid];
				}
			}
		}
		foreach($attachnew as $aid => $attach) {
			$update = array();
			$update['readperm'] = $_G['group']['allowsetattachperm'] ? $attach['readperm'] : 0;
			$update['price'] = $_G['group']['maxprice'] ? (intval($attach['price']) <= $_G['group']['maxprice'] ? intval($attach['price']) : $_G['group']['maxprice']) : 0;
			$update['tid'] = $tid;
			$update['pid'] = $pid;
			$update['uid'] = $uid;
			$update['description'] = cutstr(dhtmlspecialchars($attach['description']), 100);
			DB::update(getattachtablebytid($tid), $update, "aid='$aid'");
			if(!$newattach[$aid]) {
				continue;
			}
			$update = array_merge($update, $newattach[$aid]);
			if(!empty($newattachfile[$aid])) {
				if($_G['setting']['thumbstatus'] && $_G['forum']['disablethumb']) {
					$update['thumb'] = 0;
					@unlink($_G['setting']['attachdir'].'/forum/'.getimgthumbname($newattachfile[$aid]));
					if(!empty($albumattach[$aid])) {
						$albumattach[$aid]['thumb'] = 0;
					}
				}
				if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
					$image->Watermark($_G['setting']['attachdir'].'/forum/'.$newattachfile[$aid], '', 'forum');
					$update['filesize'] = $image->imginfo['size'];
				}
			}
			if(!empty($_G['gp_albumaid']) && isset($albumattach[$aid])) {
				$newalbum = 0;
				if(!$_G['gp_uploadalbum']) {
					require_once libfile('function/spacecp');
					$_G['gp_uploadalbum'] = album_creat(array('albumname' => $_G['gp_newalbum']));
					$newalbum = 1;
				}
				$picdata = array(
					'albumid' => $_G['gp_uploadalbum'],
					'uid' => $_G['uid'],
					'username' => $_G['username'],
					'dateline' => $albumattach[$aid]['dateline'],
					'postip' => $_G['clientip'],
					'filename' => $albumattach[$aid]['filename'],
					'title' => cutstr(dhtmlspecialchars($attach['description']), 100),
					'type' => fileext($albumattach[$aid]['attachment']),
					'size' => $albumattach[$aid]['filesize'],
					'filepath' => $albumattach[$aid]['attachment'],
					'thumb' => $albumattach[$aid]['thumb'],
					'remote' => $albumattach[$aid]['remote'] + 2,
				);

				$update['picid'] = DB::insert('home_pic', $picdata, 1);

				if($newalbum) {
					require_once libfile('function/home');
					require_once libfile('function/spacecp');
					album_update_pic($_G['gp_uploadalbum']);
				}
			}
			DB::insert(getattachtablebytid($tid), $update, false, true);
			DB::update('forum_attachment', array('tid' => $tid, 'pid' => $pid, 'tableid' => getattachtableid($tid)), "aid='$aid'");
			DB::delete('forum_attachment_unused', "aid='$aid'");
		}
		if(!empty($_G['gp_albumaid'])) {
			$albumdata = array(
				'picnum' => DB::result_first("SELECT count(*) FROM ".DB::table('home_pic')." WHERE albumid='$_G[gp_uploadalbum]'"),
				'updatetime' => $_G['timestamp'],
			);
			DB::update('home_album', $albumdata, "albumid='$_G[gp_uploadalbum]'");
		}
		if($newattach) {
			ftpupload($newaids, $uid);
		}
	}

	if(!$modnewthreads && $newattach && $uid == $_G['uid']) {
		updatecreditbyaction('postattach', $uid, array(), '', count($newattach), 1, $_G['fid']);
	}

	if($attachupdate) {
		$query = DB::query("SELECT pid, aid, attachment, thumb, remote FROM ".DB::table(getattachtablebytid($tid))." WHERE aid IN (".dimplode(array_keys($attachupdate)).")");
		while($attach = DB::fetch($query)) {
			if(array_key_exists($attach['aid'], $attachupdate) && $attachupdate[$attach['aid']]) {
				dunlink($attach);
			}
		}
		$uaids = dimplode($attachupdate);
		$query = DB::query("SELECT aid, width, filename, filesize, attachment, isimage, thumb, remote FROM ".DB::table('forum_attachment_unused')." WHERE aid IN ($uaids)$uidadd");
		DB::query("DELETE FROM ".DB::table('forum_attachment_unused')." WHERE aid IN ($uaids)$uidadd");
		$attachupdate = array_flip($attachupdate);
		while($attach = DB::fetch($query)) {
			$update = $attach;
			$update['dateline'] = TIMESTAMP;
			$update['remote'] = 0;
			unset($update['aid']);
			if($attach['isimage'] && $_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
				$image->Watermark($_G['setting']['attachdir'].'/forum/'.$attach['attachment'], '', 'forum');
				$update['filesize'] = $image->imginfo['size'];
			}
			DB::update(getattachtablebytid($tid), $update, "aid='".$attachupdate[$attach['aid']]."'");
			ftpupload(array($attachupdate[$attach['aid']]), $uid);
		}
	}

	$attachcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getattachtablebytid($tid))." WHERE tid='$tid'".($pid > 0 ? " AND pid='$pid'" : ''));
	$attachment = $attachcount ? (DB::result_first("SELECT COUNT(*) FROM ".DB::table(getattachtablebytid($tid))." WHERE tid='$tid'".($pid > 0 ? " AND pid='$pid'" : '')." AND isimage != 0") ? 2 : 1) : 0;

	DB::query("UPDATE ".DB::table('forum_thread')." SET attachment='$attachment' WHERE tid='$tid'", 'UNBUFFERED');
	if(!$attachment) {
		DB::delete('forum_threadimage', "tid='$tid'");
	}
	$posttable = getposttablebytid($tid);
	DB::query("UPDATE ".DB::table($posttable)." SET attachment='$attachment' WHERE pid='$pid'", 'UNBUFFERED');
	$_G['forum_attachexist'] = $attachment;
}

function checkflood() {
	global $_G;
	if(!$_G['group']['disablepostctrl'] && $_G['uid']) {
		$isflood = $_G['setting']['floodctrl'] && (TIMESTAMP - $_G['setting']['floodctrl'] <= getuserprofile('lastpost'));

		if(empty($isflood)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	return FALSE;
}

function checkmaxpostsperhour() {
	global $_G;
	$morepostsperhour = false;
	if(!$_G['group']['disablepostctrl'] && $_G['uid']) {

		if($_G['group']['maxpostsperhour']) {
			$timestamp = $_G['timestamp']-3600;
			$userposts = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_member_action_log')." WHERE dateline>$timestamp AND (`action`='".getuseraction('tid')."' OR `action`='".getuseraction('pid')."') AND uid='$_G[uid]'");
			$isflood = $userposts && ($userposts >= $_G['group']['maxpostsperhour']);
			if($isflood) {
				$morepostsperhour = true;
			}
		}
	}
	return $morepostsperhour;
}

function checkpost($subject, $message, $special = 0) {
	global $_G;
	if(dstrlen($subject) > 80) {
		return 'post_subject_toolong';
	}
	if(!$_G['group']['disablepostctrl'] && !$special) {
		if($_G['setting']['maxpostsize'] && strlen($message) > $_G['setting']['maxpostsize']) {
			return 'post_message_toolong';
		} elseif($_G['setting']['minpostsize'] && strlen(preg_replace("/\[quote\].+?\[\/quote\]/is", '', $message)) < $_G['setting']['minpostsize']) {
			return 'post_message_tooshort';
		}
	}
	return FALSE;
}

function checkbbcodes($message, $bbcodeoff) {
	return !$bbcodeoff && (!strpos($message, '[/') && !strpos($message, '[hr]')) ? -1 : $bbcodeoff;
}

function checksmilies($message, $smileyoff) {
	global $_G;

	if($smileyoff) {
		return 1;
	} else {
		if(!empty($_G['cache']['smileycodes']) && is_array($_G['cache']['smileycodes'])) {
			$message = dstripslashes($message);
			foreach($_G['cache']['smileycodes'] as $id => $code) {
				if(strpos($message, $code) !== FALSE) {
					return 0;
				}
			}
		}
		return -1;
	}
}

function updatepostcredits($operator, $uidarray, $action, $fid = 0) {
	global $_G;
	$val = $operator == '+' ? 1 : -1;
	$extsql = array();
	if($action == 'reply') {
		$extsql = array('posts' => $val);
	} elseif($action == 'post') {
		$extsql = array('threads' => $val, 'posts' => $val);
	}
	$uidarray = (array)$uidarray;

	foreach($uidarray as $uid) {
		if($uid == $_G['uid']) {
			updatecreditbyaction($action, $uid, $extsql, '', $val, 1, $fid);
		} elseif(empty($uid)) {
			continue;
		} else {
			batchupdatecredit($action, $uid, $extsql, $val, $fid);
		}
	}
	if($operator == '+' && ($action == 'reply' || $action == 'post')) {
		$uids = implode(',', $uidarray);
		DB::query("UPDATE ".DB::table('common_member_status')." SET lastpost='".TIMESTAMP."' WHERE uid IN ('$uids')", 'UNBUFFERED');
	}
}

function updateattachcredits($operator, $uidarray) {
	global $_G;
	foreach($uidarray as $uid => $attachs) {
		updatecreditbyaction('postattach', $uid, array(), '', $operator == '-' ? -$attachs : $attachs, 1, $_G['fid']);
	}
}

function updateforumcount($fid) {

	extract(DB::fetch_first("SELECT COUNT(*) AS threadcount, SUM(t.replies)+COUNT(*) AS replycount
		FROM ".DB::table('forum_thread')." t, ".DB::table('forum_forum')." f
		WHERE f.fid='$fid' AND t.fid=f.fid AND t.displayorder>='0'"));

	$thread = DB::fetch_first("SELECT tid, subject, author, lastpost, lastposter, closed FROM ".DB::table('forum_thread')."
		WHERE fid='$fid' AND displayorder='0' ORDER BY lastpost DESC LIMIT 1");

	$thread['subject'] = addslashes($thread['subject']);
	$thread['lastposter'] = $thread['author'] ? addslashes($thread['lastposter']) : lang('forum/misc', 'anonymous');
	$tid = $thread['closed'] > 1 ? $thread['closed'] : $thread['tid'];
	DB::query("UPDATE ".DB::table('forum_forum')." SET posts='$replycount', threads='$threadcount', lastpost='$tid\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]' WHERE fid='$fid'", 'UNBUFFERED');
}

function updatethreadcount($tid, $updateattach = 0) {
	$posttable = getposttablebytid($tid);
	$replycount = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$tid' AND invisible='0'") - 1;
	$lastpost = DB::fetch_first("SELECT author, anonymous, dateline FROM ".DB::table($posttable)." WHERE tid='$tid' AND invisible='0' ORDER BY dateline DESC LIMIT 1");

	$lastpost['author'] = $lastpost['anonymous'] ? lang('forum/misc', 'anonymous') : addslashes($lastpost['author']);
	$lastpost['dateline'] = !empty($lastpost['dateline']) ? $lastpost['dateline'] : TIMESTAMP;

	if($updateattach) {
		$attach = DB::result_first("SELECT attachment FROM ".DB::table($posttable)." WHERE tid='$tid' AND invisible='0' AND attachment>0 LIMIT 1");
		$attachadd = ', attachment=\''.($attach ? 1 : 0).'\'';
	} else {
		$attachadd = '';
	}
	DB::query("UPDATE ".DB::table('forum_thread')." SET replies='$replycount', lastposter='$lastpost[author]', lastpost='$lastpost[dateline]' $attachadd WHERE tid='$tid'", 'UNBUFFERED');
}

function updatemodlog($tids, $action, $expiration = 0, $iscron = 0, $reason = '', $stamp = 0) {
	global $_G;

	$uid = empty($iscron) ? $_G['uid'] : 0;
	$username = empty($iscron) ? $_G['member']['username'] : 0;
	$expiration = empty($expiration) ? 0 : intval($expiration);

	$data = $comma = '';
	$stampadd = $stampaddvalue = '';
	if($stamp) {
		$stampadd = ', stamp';
		$stampaddvalue = ", '$stamp'";
	}
	foreach(explode(',', str_replace(array('\'', ' '), array('', ''), $tids)) as $tid) {
		if($tid) {
			$data .= "{$comma} ('$tid', '$uid', '$username', '$_G[timestamp]', '$action', '$expiration', '1', '$reason'$stampaddvalue)";
			$comma = ',';
		}
	}

	!empty($data) && DB::query("INSERT INTO ".DB::table('forum_threadmod')." (tid, uid, username, dateline, action, expiration, status, reason$stampadd) VALUES $data", 'UNBUFFERED');

}

function isopera() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(strpos($useragent, 'opera') !== false) {
		preg_match('/opera(\/| )([0-9\.]+)/', $useragent, $regs);
		return $regs[2];
	}
	return FALSE;
}

function deletethreadcaches($tids) {
	global $_G;
	if(!$_G['setting']['cachethreadon']) {
		return FALSE;
	}
	require_once libfile('function/forumlist');
	if(!empty($tids)) {
		foreach(explode(',', $tids) as $tid) {
			$fileinfo = getcacheinfo($tid);
			@unlink($fileinfo['filename']);
		}
	}
	return TRUE;
}


function disuploadedfile($file) {
	return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
}

function postfeed($feed) {
	global $_G;
	if($feed) {
		require_once libfile('function/feed');
		feed_add($feed['icon'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', $feed['images'], $feed['image_links'], '', '', '', 0, $feed['id'], $feed['idtype']);
	}
}

function messagecutstr($str, $length = 0, $dot = ' ...') {
	global $_G;
	$sppos = strpos($str, chr(0).chr(0).chr(0));
	if($sppos !== false) {
		$str = substr($str, 0, $sppos);
	}
	$language = lang('forum/misc');
	loadcache(array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes', 'domainwhitelist'));
	$bbcodes = 'b|i|u|p|color|size|font|align|list|indent|float';
	$bbcodesclear = 'email|code|free|table|tr|td|img|swf|flash|attach|media|audio|payto'.($_G['cache']['bbcodes_display'][$_G['groupid']] ? '|'.implode('|', array_keys($_G['cache']['bbcodes_display'][$_G['groupid']])) : '');
	$str = strip_tags(preg_replace(array(
			"/\[hide=?\d*\](.*?)\[\/hide\]/is",
			"/\[quote](.*?)\[\/quote]/si",
			$language['post_edit_regexp'],
			"/\[url=?.*?\](.+?)\[\/url\]/si",
			"/\[($bbcodesclear)=?.*?\].+?\[\/\\1\]/si",
			"/\[($bbcodes)=?.*?\]/i",
			"/\[\/($bbcodes)\]/i",
		), array(
			"[b]$language[post_hidden][/b]",
			'',
			'',
			'\\1',
			'',
			'',
			'',
		), $str));
	if($length) {
		$str = cutstr($str, $length, $dot);
	}
	$str = preg_replace($_G['cache']['smilies']['searcharray'], '', $str);
	if($_G['setting']['plugins'][HOOKTYPE.'_discuzcode']) {
		$_G['discuzcodemessage'] = & $str;
		$param = func_get_args();
		hookscript('discuzcode', 'global', 'funcs', array('param' => $param, 'caller' => 'messagecutstr'), 'discuzcode');
	}
	return trim($str);
}

function savepostposition($tid, $pid, $returnposition = false) {
	$res = DB::query("INSERT INTO ".DB::table('forum_postposition')." SET tid='$tid', pid='$pid'");
	if(!$returnposition) {
		return $res;
	} else {
		return DB::insert_id();
	}
}

function setthreadcover($pid, $tid = 0, $aid = 0) {
	global $_G;
	$cover = 0;
	if(empty($_G['uid']) || !intval($_G['setting']['forumpicstyle']['thumbwidth']) || !intval($_G['setting']['forumpicstyle']['thumbwidth'])) {
		return false;
	}
	if(($pid || $aid) && empty($tid)) {
		if($aid) {
			$attachtable = getattachtablebyaid($aid);
			$wheresql = "aid='$aid' AND isimage IN ('1', '-1')";
		} else {
			$attachtable = getattachtablebypid($pid);
			$wheresql = "pid='$pid' AND isimage IN ('1', '-1') ORDER BY width DESC LIMIT 1";
		}
		$query = DB::query("SELECT * FROM ".DB::table($attachtable)." WHERE $wheresql");
		if(!$attach = DB::fetch($query)) {
			return false;
		}
		if(empty($_G['forum']['ismoderator']) && $_G['uid'] != $attach['uid']) {
			return false;
		}
		$pid = empty($pid) ? $attach['pid'] : $pid;
		$tid = empty($tid) ? $attach['tid'] : $tid;

		$basedir = !$_G['setting']['attachdir'] ? (DISCUZ_ROOT.'./data/attachment/') : $_G['setting']['attachdir'];
		$coverdir = 'threadcover/'.substr(md5($tid), 0, 2).'/'.substr(md5($tid), 2, 2).'/';
		dmkdir($basedir.'./forum/'.$coverdir);
		$picsource = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];

		require_once libfile('class/image');
		$image = new image();
		if($image->Thumb($picsource, 'forum/'.$coverdir.$tid.'.jpg', $_G['setting']['forumpicstyle']['thumbwidth'], $_G['setting']['forumpicstyle']['thumbheight'], 2)) {
			$remote = '';
			if(getglobal('setting/ftp/on')) {
				if(ftpcmd('upload', 'forum/'.$coverdir.$tid.'.jpg')) {
					$remote = '-';
				}
			}
			$cover = DB::result_first("SELECT COUNT(*) FROM ".DB::table($attachtable)." WHERE pid='$pid' AND isimage IN ('1', '-1')");
			$cover = $remote.$cover;
		} else {
			return false;
		}
	}
	if($tid || $cover) {
		if(empty($cover)) {
			$oldcover = DB::result_first("SELECT cover FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
			$cover = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getattachtablebytid($tid))." WHERE pid='$pid' AND isimage IN ('1', '-1')");
			$cover = $cover && $oldcover < 0 ? '-'.$cover : $cover;
		}
		DB::update('forum_thread', array('cover' => $cover), array('tid'=>$tid));
	}
	return true;
}

?>