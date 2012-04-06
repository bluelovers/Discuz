<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_attachment.php 29038 2012-03-23 06:22:39Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);
@list($_G['gp_aid'], $_G['gp_k'], $_G['gp_t'], $_G['gp_uid'], $_G['gp_tableid']) = daddslashes(explode('|', base64_decode($_G['gp_aid'])));

$aid = intval($_G['gp_aid']);
if(!empty($_G['gp_findpost']) && ($attach = DB::fetch_first("SELECT pid, tid FROM ".DB::table('forum_attachment')." WHERE aid='$aid'"))) {
	dheader('location: forum.php?mod=redirect&goto=findpost&pid='.$attach['pid'].'&ptid='.$attach['tid']);
}

if($_G['gp_uid'] != $_G['uid'] && $_G['gp_uid']) {
	$_G['gp_uid'] = intval($_G['gp_uid']);
	$groupid = DB::result_first("SELECT groupid FROM ".DB::table('common_member')." WHERE uid='$_G[gp_uid]'");
	loadcache('usergroup_'.$groupid);
	$_G['group'] = $_G['cache']['usergroup_'.$groupid];
}

$requestmode = !empty($_G['gp_request']) && empty($_G['gp_uid']);

$tableid = !empty($_G['gp_tableid']) ? getattachtableid($_G['gp_tableid']) : DB::result_first("SELECT tableid FROM ".DB::table('forum_attachment')." WHERE aid='$aid'");
if(!is_numeric($tableid)) {
	if(!$requestmode) {
		showmessage('attachment_nonexistence');
	} else {
		exit;
	}
}

if($_G['setting']['attachexpire']) {
	$k = $_G['gp_k'];
	$t = $_G['gp_t'];
	$authk = !$requestmode ? substr(md5($aid.md5($_G['config']['security']['authkey']).$t.$_G['gp_uid']), 0, 8) : md5($aid.md5($_G['config']['security']['authkey']).$t);
	if(empty($k) || empty($t) || $k != $authk || TIMESTAMP - $t > $_G['setting']['attachexpire'] * 3600) {
		$aid = intval($aid);
		if($attach = DB::fetch_first("SELECT pid, tid, isimage FROM ".DB::table('forum_attachment_'.$tableid)." WHERE aid='$aid'")) {
			if($attach['isimage']) {
				dheader('location: '.$_G['siteurl'].'static/image/common/none.gif');
			} else {
				if(!$requestmode) {
					showmessage('attachment_expired', '', array('aid' => aidencode($aid, 0, $attach['tid']), 'pid' => $attach['pid'], 'tid' => $attach['tid']));
				} else {
					exit;
				}
			}
		} else {
			if(!$requestmode) {
				showmessage('attachment_nonexistence');
			} else {
				exit;
			}
		}
	}
}

$readmod = getglobal('config/download/readmod');
$readmod = $readmod > 0 && $readmod < 5 ? $readmod : 2;

$refererhost = parse_url($_SERVER['HTTP_REFERER']);
$serverhost = $_SERVER['HTTP_HOST'];
if(($pos = strpos($serverhost, ':')) !== FALSE) {
	$serverhost = substr($serverhost, 0, $pos);
}

if(!$requestmode && $_G['setting']['attachrefcheck'] && $_SERVER['HTTP_REFERER'] && !($refererhost['host'] == $serverhost)) {
	showmessage('attachment_referer_invalid', NULL);
}

periodscheck('attachbanperiods');

loadcache('threadtableids');
$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
if(!in_array(0, $threadtableids)) {
	$threadtableids = array_merge(array(0), $threadtableids);
}
$archiveid = intval($_G['gp_archiveid']);
if(in_array($archiveid, $threadtableids)) {
	$threadtable = $archiveid ? "forum_thread_{$archiveid}" : 'forum_thread';
} else {
	$threadtable = 'forum_thread';
}

$attachexists = FALSE;
if(!empty($aid) && is_numeric($aid)) {
	$attach = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment_'.$tableid)." WHERE aid='$aid'");
	$thread = DB::fetch_first("SELECT tid, fid, posttableid, price, special FROM ".DB::table($threadtable)." WHERE tid='$attach[tid]' AND displayorder>='0'");
	if($attach) {
		$posttable = $thread['posttableid'] ? "forum_post_{$thread['posttableid']}" : 'forum_post';
		$attach['invisible'] = DB::result_first("SELECT invisible FROM ".DB::table($posttable)." WHERE pid='$attach[pid]'");
	}
	if($attach && $attach['invisible'] == 0) {
		$thread && $attachexists = TRUE;
	}
}

if(!$attachexists) {
	if(!$requestmode) {
		showmessage('attachment_nonexistence');
	} else {
		exit;
	}
}

if(!$requestmode) {
	$forum = DB::fetch_first("SELECT f.fid, f.viewperm, f.getattachperm, a.allowgetattach, a.allowgetimage FROM ".DB::table('forum_forumfield')." f
		LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid
		WHERE f.fid='$thread[fid]'");

	$_GET['fid'] = $forum['fid'];

	if($attach['isimage']) {
		$allowgetattach = !empty($forum['allowgetimage']) || (($_G['group']['allowgetimage'] || $_G['uid'] == $attach['uid']) && !$forum['getattachperm']) || forumperm($forum['getattachperm']);
	} else {
		$allowgetattach = !empty($forum['allowgetattach']) || (($_G['group']['allowgetattach']  || $_G['uid'] == $attach['uid']) && !$forum['getattachperm']) || forumperm($forum['getattachperm']);
	}
	if($allowgetattach && ($attach['readperm'] && $attach['readperm'] > $_G['group']['readaccess']) && $_G['adminid'] <= 0 && !($_G['uid'] && $_G['uid'] == $attach['uid'])) {
		showmessage('attachment_forum_nopermission', NULL, array(), array('login' => 1));
	}

	$ismoderator = in_array($_G['adminid'], array(1, 2)) ? 1 : ($_G['adminid'] == 3 ? DB::result_first("SELECT uid FROM ".DB::table('forum_moderator')." m INNER JOIN ".DB::table($threadtable)." t ON t.tid='$attach[tid]' AND t.fid=m.fid WHERE m.uid='$_G[uid]'") : 0);

	$ispaid = FALSE;
	$exemptvalue = $ismoderator ? 128 : 16;
	if(!$thread['special'] && $thread['price'] > 0 && (!$_G['uid'] || ($_G['uid'] != $attach['uid'] && !($_G['group']['exempt'] & $exemptvalue)))) {
		if(!$_G['uid'] || $_G['uid'] && !($ispaid = DB::result_first("SELECT uid FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND operation='BTC' AND relatedid='$attach[tid]'"))) {
			showmessage('attachment_payto', 'forum.php?mod=viewthread&tid='.$attach['tid']);
		}
	}

	$exemptvalue = $ismoderator ? 64 : 8;
	if($attach['price'] && (!$_G['uid'] || ($_G['uid'] != $attach['uid'] && !($_G['group']['exempt'] & $exemptvalue)))) {
		$payrequired = $_G['uid'] ? !DB::result_first("SELECT uid FROM ".DB::table('common_credit_log')." WHERE uid='$_G[uid]' AND relatedid='$attach[aid]' AND operation='BAC'") : 1;
		$payrequired && showmessage('attachement_payto_attach', 'forum.php?mod=misc&action=attachpay&aid='.$attach['aid'].'&tid='.$attach['tid']);
	}
}

$isimage = $attach['isimage'];
$_G['setting']['ftp']['hideurl'] = $_G['setting']['ftp']['hideurl'] || ($isimage && !empty($_G['gp_noupdate']) && $_G['setting']['attachimgpost'] && strtolower(substr($_G['setting']['ftp']['attachurl'], 0, 3)) == 'ftp');

if(empty($_G['gp_nothumb']) && $attach['isimage'] && $attach['thumb']) {
	$db = DB::object();
	$db->close();
	!$_G['config']['output']['gzip'] && ob_end_clean();
	dheader('Content-Disposition: inline; filename='.getimgthumbname($attach['filename']));
	dheader('Content-Type: image/pjpeg');
	if($attach['remote']) {
		$_G['setting']['ftp']['hideurl'] ? getremotefile(getimgthumbname($attach['attachment'])) : dheader('location:'.$_G['setting']['ftp']['attachurl'].'forum/'.getimgthumbname($attach['attachment']));
	} else {
		getlocalfile($_G['setting']['attachdir'].'/forum/'.getimgthumbname($attach['attachment']));
	}
	exit();
}

$filename = $_G['setting']['attachdir'].'/forum/'.$attach['attachment'];
if(!$attach['remote'] && !is_readable($filename)) {
	if(!$requestmode) {
		showmessage('attachment_nonexistence');
	} else {
		exit;
	}
}

if(!$requestmode) {
	if(!$ispaid && !$forum['allowgetattach']) {
		if(!$forum['getattachperm'] && !$allowgetattach) {
			showmessage('getattachperm_none_nopermission', NULL, array(), array('login' => 1));
		} elseif(($forum['getattachperm'] && !forumperm($forum['getattachperm'])) || ($forum['viewperm'] && !forumperm($forum['viewperm']))) {
			showmessagenoperm('getattachperm', $forum['fid']);
		}
	}

	$exemptvalue = $ismoderator ? 32 : 4;
	if(!$isimage && !($_G['group']['exempt'] & $exemptvalue)) {
		$creditlog = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 0, $thread['fid']);
		if($creditlog['updatecredit']) {
			if($_G['uid']) {
				$k = $_G['gp_ck'];
				$t = $_G['gp_t'];
				if(empty($k) || empty($t) || $k != substr(md5($aid.$t.md5($_G['config']['security']['authkey'])), 0, 8) || TIMESTAMP - $t > 3600) {
					dheader('location: forum.php?mod=misc&action=attachcredit&aid='.$attach['aid'].'&formhash='.FORMHASH);
					exit();
				}
			} else {
				showmessage('attachment_forum_nopermission', NULL, array(), array('login' => 1));
			}
		}
	}

	if(empty($_G['gp_noupdate'])) {
		if($_G['setting']['delayviewcount'] == 2 || $_G['setting']['delayviewcount'] == 3) {
			$_G['forum_logfile'] = './data/cache/forum_attachviews_'.intval(getglobal('config/server/id')).'.log';
			if(substr(TIMESTAMP, -1) == '0') {
				require_once libfile('function/misc');
				updateviews('forum_attachment', 'aid', 'downloads', $_G['forum_logfile']);
			}

			if(@$fp = fopen(DISCUZ_ROOT.$_G['forum_logfile'], 'a')) {
				fwrite($fp, "$aid\n");
				fclose($fp);
			} elseif($_G['adminid'] == 1) {
				showmessage('view_log_invalid', '', array('logfile' => $_G['forum_logfile']));
			}
		} else {
			DB::query("UPDATE ".DB::table('forum_attachment')." SET downloads=downloads+'1' WHERE aid='$aid'", 'UNBUFFERED');
		}
	}
}

$db = DB::object();
$db->close();
!$_G['config']['output']['gzip'] && ob_end_clean();

$range = 0;
if($readmod == 4 && !empty($_SERVER['HTTP_RANGE'])) {
	list($range) = explode('-',(str_replace('bytes=', '', $_SERVER['HTTP_RANGE'])));
}


if($attach['remote'] && !$_G['setting']['ftp']['hideurl'] && $isimage) {
	dheader('location:'.$_G['setting']['ftp']['attachurl'].'forum/'.$attach['attachment']);
} else {
	if($attach['sha1'] && $attach['attachment'] == '') {
		if($_G['gp_inajax']){ //qqdl 链接更新数
			include template('common/header');
			include template('common/footer');
			exit;
        }
        require_once libfile('function/ftn');
		$downurl = make_downloadurl($attach['sha1'],$attach['filesize'],$attach['filename']);
		dheader('location:'.$downurl);
	} else {
		dheader('Content-Disposition: attachment; filename='.$attach['filename']);
	}
}

$filesize = !$attach['remote'] ? filesize($filename) : $attach['filesize'];
$attach['filename'] = '"'.(strtolower(CHARSET) == 'utf-8' && strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? urlencode($attach['filename']) : $attach['filename']).'"';

dheader('Date: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
dheader('Content-Encoding: none');

if($isimage && !empty($_G['gp_noupdate']) || !empty($_G['gp_request'])) {
	dheader('Content-Disposition: inline; filename='.$attach['filename']);
} else {
	dheader('Content-Disposition: attachment; filename='.$attach['filename']);
}
if($isimage) {
	dheader('Content-Type: image');
} else {
	dheader('Content-Type: application/octet-stream');
}

dheader('Content-Length: '.$filesize);

$xsendfile = getglobal('config/download/xsendfile');
if(!empty($xsendfile)) {
	$type = intval($xsendfile['type']);
	$cmd = '';
	switch ($type) {
		case 1: $cmd = 'X-Accel-Redirect'; $url = $xsendfile['dir'].$attach['attachment']; break;
		case 2: $cmd = $_SERVER['SERVER_SOFTWARE'] <'lighttpd/1.5' ? 'X-LIGHTTPD-send-file' : 'X-Sendfile'; $url = $filename; break;
		case 3: $cmd = 'X-Sendfile'; $url = $filename; break;
	}
	if($cmd) {
		dheader("$cmd: $url");
		exit();
	}
}

if($readmod == 4) {
	dheader('Accept-Ranges: bytes');
	if(!empty($_SERVER['HTTP_RANGE'])) {
		$rangesize = ($filesize - $range) > 0 ?  ($filesize - $range) : 0;
		dheader('Content-Length: '.$rangesize);
		dheader('HTTP/1.1 206 Partial Content');
		dheader('Content-Range: bytes='.$range.'-'.($filesize-1).'/'.($filesize));
	}
}

$attach['remote'] ? getremotefile($attach['attachment']) : getlocalfile($filename, $readmod, $range);

function getremotefile($file) {
	global $_G;
	@set_time_limit(0);
	if(!@readfile($_G['setting']['ftp']['attachurl'].'forum/'.$file)) {
		$ftp = ftpcmd('object');
		$tmpfile = @tempnam($_G['setting']['attachdir'], '');
		if($ftp->ftp_get($tmpfile, 'forum/'.$file, FTP_BINARY)) {
			@readfile($tmpfile);
			@unlink($tmpfile);
		} else {
			@unlink($tmpfile);
			return FALSE;
		}
	}
	return TRUE;
}

function getlocalfile($filename, $readmod = 2, $range = 0) {
	if($readmod == 1 || $readmod == 3 || $readmod == 4) {
		if($fp = @fopen($filename, 'rb')) {
			@fseek($fp, $range);
			if(function_exists('fpassthru') && ($readmod == 3 || $readmod == 4)) {
				@fpassthru($fp);
			} else {
				echo @fread($fp, filesize($filename));
			}
		}
		@fclose($fp);
	} else {
		@readfile($filename);
	}
	@flush(); @ob_flush();
}

?>