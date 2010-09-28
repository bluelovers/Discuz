<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_pm.php 17092 2010-09-21 02:22:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
if($uid) {
	$touid = $uid;
} else {
	$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
}
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);

loaducenter();

if($_GET['op'] == 'checknewpm') {

	if($_G['uid'] && !$_G['member']['newpm']) {
		$ucnewpm = intval(uc_pm_checknew($_G['uid']));
		if($_G['member']['newpm'] != $ucnewpm) {
			DB::query("UPDATE ".DB::table('common_member')." SET newpm='$ucnewpm' WHERE uid='$_G[uid]'");
		}
	}
	dsetcookie('checkpm', 1, 30);
	exit();

} elseif($_GET['op'] == 'getpmuser') {
	$otherpm = $json = array();
	$result = uc_pm_list($_G['uid'], 1, 30, 'inbox', 'privatepm');
	foreach($result['data'] as $key => $value) {
		$value['msgfrom'] = daddslashes($value['msgfrom']);
		$value['avatar'] = avatar($value['msgfromid'], 'small', true);
		if($value['new']) {
			$json[$value['msgfromid']] = "$value[msgfromid]:{'uid':$value[msgfromid], 'username':'$value[msgfrom]', 'avatar':'$value[avatar]', 'pmid':$value[pmid], 'new':$value[new], 'daterange':$value[daterange]}";
		} else {
			$otherpm[$value['msgfromid']] = "$value[msgfromid]:{'uid':$value[msgfromid], 'username':'$value[msgfrom]', 'avatar':'$value[avatar]', 'pmid':$value[pmid], 'new':$value[new], 'daterange':$value[daterange]}";
		}
	}
	if(!empty($otherpm)) {
		$json = array_merge($json, $otherpm);
	}
	$jsstr = "{'userdata':{".implode(',', $json)."}}";

} elseif($_GET['op'] == 'showmsg') {

	$msgonly = empty($_G['gp_msgonly']) ? 0 : intval($_G['gp_msgonly']);
	$pmid = empty($_G['gp_pmid']) ? 0 : intval($_G['gp_pmid']);
	$touid = empty($_G['gp_touid']) ? 0: intval($_G['gp_touid']);
	$daterange = empty($_G['gp_daterange']) ? 1 : intval($_G['gp_daterange']);
	$result = uc_pm_view($_G['uid'], 0, $touid, $daterange);
	$msglist = array();
	$msguser = $messageappend = '';
	$online = 0;
	foreach($result as $key => $value) {
		if($value['msgfromid'] != $_G['uid']) {
			$msguser = $value['msgfrom'];
		}
		$daykey = dgmdate($value['dateline'], 'Y-m-d');
		$msglist[$daykey][$key] = $value;
	}
	if($touid && empty($msguser)) {
		$member = DB::fetch_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$touid' LIMIT 1");
		$msguser = $member['username'];
	}
	if(!$msgonly) {
		$online = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_session')." b WHERE uid='$touid'"),0);
		if($_G['member']['newpm']) {
			DB::update('common_member', array('newpm' => 0), array('uid' => $_G['uid']));
			uc_pm_ignore($_G['uid']);
		}
	}
	if(!empty($_G['gp_tradeid'])) {
		$trade = DB::fetch_first("SELECT tid, subject, pid FROM ".DB::table('forum_trade')." WHERE pid='$_G[gp_tradeid]'");
		if($trade) {
			$messageappend = htmlspecialchars('[url='.$_G['siteurl'].'forum.php?mod=viewthread&tid='.$trade['tid'].'&do=tradeinfo&pid='.$trade['pid'].'][b]'.$trade['subject'].'[/b][/url]');
		}
	} elseif(!empty($_G['gp_commentid'])) {
		$comment = DB::fetch_first("SELECT comment, tid, pid FROM ".DB::table('forum_postcomment')." WHERE id='$_G[gp_commentid]'");
		if($comment) {
			$comment['comment'] = str_replace(array('[b]', '[/b]', '[/color]'), array(''), preg_replace("/\[color=([#\w]+?)\]/i", '', strip_tags($comment['comment'])));
			$messageappend = htmlspecialchars('[url='.$_G['siteurl'].'forum.php?mod=redirect&goto=findpost&pid='.$comment['pid'].'&ptid='.$comment['tid'].'][b]'.lang('spacecp', 'pm_comment').'[/b][/url][quote]'.$comment['comment'].'[/quote]');
		}
	} elseif(!empty($_G['gp_pid'])) {
		$thread = DB::fetch_first("SELECT t.tid, t.subject, p.pid FROM ".DB::table('forum_post')." p
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid WHERE p.pid='$_G[gp_pid]'");
		if($thread) {
			$messageappend = htmlspecialchars('[url='.$_G['siteurl'].'forum.php?mod=redirect&goto=findpost&pid='.$thread['pid'].'&ptid='.$thread['tid'].'][b]'.lang('spacecp', 'pm_thread_about', array('subject' => $thread['subject'])).'[/b][/url]');
		}
	}

} elseif($_GET['op'] == 'delete') {

	$pmid = empty($_G['gp_pmid'])?0:floatval($_G['gp_pmid']);
	$deluid = empty($_G['gp_deluid'])?0:floatval($_G['gp_deluid']);
	$folder = $_G['gp_folder'] == 'inbox' ? 'inbox' : 'outbox';

	if(submitcheck('deletesubmit')) {
		if($deluid) {
			$retrun = uc_pm_deleteuser($_G['uid'], array($deluid));
			$pmid = $deluid;
		} else {
			$retrun = uc_pm_delete($_G['uid'], $folder, array($pmid));
		}
		if($retrun>0) {
			showmessage('do_success', dreferer(), array('pmid' => $pmid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => true));
		} else {
			showmessage('this_message_could_not_be_deleted');
		}
	}

} elseif($_GET['op'] == 'send') {

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	cknewuser();

	if(!checkperm('allowsendpm')) {
		showmessage('no_privilege', '', array(), array('return' => true));
	}

	if($touid) {
		if(isblacklist($touid)) {
			showmessage('is_blacklist', '', array(), array('return' => true));
		}
	}

	if(submitcheck('pmsubmit')) {
		$username = empty($_POST['username']) ? '' : $_POST['username'];
		$coef = 1;
		if(!empty($username)) {
			$users = $userarr = daddslashes(explode(',', dstripslashes($username)));
			foreach($users as $key => $value) {
				if(!empty($value)) {
					$users[$key] = $value;
				}
			}
			$coef = count($users);
		}

		!($_G['group']['exempt'] & 1) && checklowerlimit('sendpm', 0, $coef);

		$message = (!empty($_POST['messageappend']) ? $_POST['messageappend']."\n" : '').trim($_POST['message']);
		if(empty($message)) {
			showmessage('unable_to_send_air_news', '', array(), array('return' => true));
		}
		$message = censor($message);
		loadcache(array('smilies', 'smileytypes'));
		foreach($_G['cache']['smilies']['replacearray'] AS $key => $smiley) {
			$_G['cache']['smilies']['replacearray'][$key] = '[img]'.$_G['siteurl'].'static/image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'[/img]';
		}
		$message = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], $message);
		$subject = '';

		$return = 0;
		if($touid) {
			$return = uc_pm_send($_G['uid'], $touid, $subject, $message, 1, $pmid, 0);

		} elseif($username) {
			$newusers = array();
			if($users) {
				$query = DB::query('SELECT uid, username FROM '.DB::table('common_member')." WHERE username IN (".dimplode($users).')');
				while($value = DB::fetch($query)) {
					$newusers[$value['uid']] = $value['username'];
					unset($users[array_search($value['username'], $users)]);
				}
			}
			if(empty($newusers)) {
				showmessage('message_bad_touser', dreferer(), array(), array('return' => true));
			}
			if(isset($newusers[$_G['uid']])) {
				showmessage('message_can_not_send_to_self', dreferer(), array(), array('return' => true));
			}

			foreach($newusers as $key=>$value) {
				if(isblacklist($key)) {
					showmessage('is_blacklist', dreferer(), array(), array('return' => true));
				}
			}
			$coef = count($newusers);
			$return = uc_pm_send($_G['uid'], implode(',', $newusers), $subject, $message, 1, $pmid, 1);
		}

		if($return > 0) {
			DB::query("UPDATE ".DB::table('common_member_status')." SET lastpost='$_G[timestamp]' WHERE uid='$_G[uid]'");
			!($_G['group']['exempt'] & 1) && updatecreditbyaction('sendpm', 0, array(), '', $coef);
			if(!empty($username)) {
				showmessage(count($users) ? 'message_send_result' : 'do_success', 'home.php?mod=space&do=pm', array('users' => implode(',', $users), 'succeed' => count($newusers)));
			} else {
				showmessage('do_success', 'home.php?mod=space&do=pm&subop=view&pmid='.$_GET['pmid'].'&touid='.$_GET['touid'].'&daterange='.$_GET['daterange'].'#bottom', array('pmid' => $return), array('msgtype' => 3, 'showmsg' => false));
			}
		} else {
			if(in_array($return, array(-1,-2,-3,-4))) {
				showmessage('message_can_not_send'.abs($return), '', array(), array('return' => true));
			} else {
				showmessage('message_can_not_send', '', array(), array('return' => true));
			}
		}
	}

} elseif($_GET['op'] == 'ignore') {

	if(submitcheck('ignoresubmit')) {
		$single = intval($_G['gp_single']);
		if($single) {
			uc_pm_blackls_add($_G['uid'], $_POST['ignoreuser']);
			showmessage('do_success', dreferer(), array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		} else {
			uc_pm_blackls_set($_G['uid'], $_POST['ignorelist']);
			showmessage('do_success', 'home.php?mod=space&do=pm&view=ignore', array(), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		}
	}

} elseif($_GET['op'] == 'viewpmid') {

	$list = uc_pm_view($_G['uid'], $_GET['pmid']);
	$value = $list[0];
	include template('common/header_ajax');
	include template('home/space_pm_node');
	include template('common/footer_ajax');
	exit;

} elseif($_GET['op'] == 'export') {

	if(!$touid) {
		showmessage('pm_export_touser_not_exists');
	}

	$list = uc_pm_view($_G['uid'], 0, $touid, 5);

	if(count($list) == 0) {
		showmessage('pm_emport_banned_export');
	}
	$filename = lang('space', 'export_pm').'.txt';
	if($touser = uc_get_user($touid, 1)) {
		$tousername = $touser[1];
		$filename = $touser[1].'.txt';
	}
	$contents = lang('space', 'pm_export_header');
	$contents .= "\r\n\r\n================================================================\r\n";
	if($touser) {
		$contents .= lang('space', 'pm_export_touser', array('touser' => $touser[1]));
		$contents .= "\r\n================================================================\r\n";
	}
	$contents .= "\r\n";
	foreach($list as $key => $val) {
		$contents .= $val['msgfrom']."\t".dgmdate($val['dateline'])."\r\n";
		$contents .= str_replace(array('<br>', '<br />', '&nbsp;'), array("\r\n", "\r\n", ' '), $val['message'])."\r\n\r\n";
	}

	$filesize = strlen($contents);
	$filename = '"'.(strtolower(CHARSET) == 'utf-8' && strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? urlencode($filename) : $filename).'"';

	dheader('Date: '.gmdate('D, d M Y H:i:s', $val['dateline']).' GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $val['dateline']).' GMT');
	dheader('Content-Encoding: none');
	dheader('Content-Disposition: attachment; filename='.$filename);

	dheader('Content-Type: application/octet-stream');
	dheader('Content-Length: '.$filesize);

	echo $contents;
	die;

} else {

	cknewuser();

	if(!checkperm('allowsendpm')) {
		showmessage('no_privilege');
	}
	$friends = array();
	if($space['friendnum']) {
		$query = DB::query("SELECT fuid AS uid, fusername AS username FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' ORDER BY num DESC, dateline DESC LIMIT 0,100");
		while ($value = DB::fetch($query)) {
			$value['username'] = daddslashes($value['username']);
			$friends[] = $value;
		}
	}
}

include_once template("home/spacecp_pm");

?>