<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_post.php 17074 2010-09-20 07:44:23Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

cknewuser();

require_once libfile('class/credit');
require_once libfile('function/post');


$pid = intval(getgpc('pid'));
$sortid = intval(getgpc('sortid'));
$typeid = intval(getgpc('typeid'));
$special = intval(getgpc('special'));

$postinfo = array('subject' => '');
$thread = array('readperm' => '', 'pricedisplay' => '', 'hiddenreplies' => '');

$_G['forum_dtype'] = $_G['forum_checkoption'] = $_G['forum_optionlist'] = $tagarray = $_G['forum_typetemplate'] = array();
if($sortid) {
	require_once libfile('function/threadsort');
	threadsort_checkoption($sortid);
}

if($_G['forum']['status'] == 3) {
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid'], 'post');
	if($status == -1) {
		showmessage('forum_not_group', 'index.php');
	} elseif($status == 1) {
		showmessage('forum_group_status_off');
	} elseif($status == 2) {
		showmessage('forum_group_noallowed', "forum.php?mod=group&fid=$_G[fid]");
	} elseif($status == 3) {
		showmessage('forum_group_moderated', "forum.php?mod=group&fid=$_G[fid]");
	} elseif($status == 4) {
		if($_G['uid']) {
			showmessage('forum_group_not_groupmember', "", array('fid' => $_G['fid']), array('showmsg' => 1));
		} else {
			showmessage('forum_group_not_groupmember_guest', "", array('fid' => $_G['fid']), array('showmsg' => 1, 'login' => 1));
		}
	}
}

if(empty($_G['gp_action'])) {
	showmessage('undefined_action', NULL);
} elseif($_G['gp_action'] == 'threadsorts') {
	require_once libfile('function/threadsort');
	loadcache(array('threadsort_option_'.$_G['gp_sortid'], 'threadsort_template_'.$_G['gp_sortid']));
	threadsort_optiondata($_G['gp_pid'], $_G['gp_sortid'], $_G['cache']['threadsort_option_'.$_G['gp_sortid']], $_G['cache']['threadsort_template_'.$_G['gp_sortid']]);
	$template = intval($_G['gp_operate']) ? 'forum/search_sortoption' : 'forum/post_sortoption';
	include template($template);
	exit;
} elseif($_G['gp_action'] == 'albumphoto') {
	include_once libfile('function/home');
	$perpage = 8;
	$page = max(1, $_G['gp_page']);
	$start_limit = ($page - 1) * $perpage;
	$aid = intval($_G['gp_aid']);
	$photolist = array();
	$count= DB::result_first("SELECT picnum FROM ".DB::table('home_album')." WHERE albumid='$aid' AND uid='$_G[uid]'");
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE albumid='$aid' ORDER BY dateline DESC LIMIT $start_limit,$perpage");
	while($value = DB::fetch($query)) {
		$value['bigpic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
		$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
		$value['count'] = $count;
		$value['url'] = (substr(strtolower($value['bigpic']), 0, 7) == 'http://' ? '' : $_G['siteurl']).$value['bigpic'];
		$value['thumburl'] = (substr(strtolower($value['pic']), 0, 7) == 'http://' ? '' : $_G['siteurl']).$value['pic'];
		$photolist[] = $value;
	}
	$_G['gp_ajaxtarget'] = 'albumphoto';
	$multi = multi($count, $perpage, $page, "forum.php?mod=post&action=albumphoto&aid=$aid");
	include template('forum/ajax_albumlist');
	exit;

} elseif(($_G['forum']['simple'] & 1) || $_G['forum']['redirect']) {
	showmessage('forum_disablepost');
}

require_once libfile('function/discuzcode');

$space = array();
space_merge($space, 'field_home');

if($_G['gp_action'] == 'reply') {
	$addfeedcheck = !empty($space['privacy']['feed']['newreply']) ? 'checked="checked"': '';
} else {
	$addfeedcheck = !empty($space['privacy']['feed']['newthread']) ? 'checked="checked"': '';
}


$navigation = $navtitle = '';

if(!empty($_G['gp_cedit'])) {
	unset($_G['inajax'], $_G['gp_infloat'], $_G['gp_ajaxtarget'], $_G['gp_handlekey']);
}

if($_G['gp_action'] == 'edit' || $_G['gp_action'] == 'reply') {

	if($thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$_G[tid]'".($_G['forum_auditstatuson'] ? '' : " AND (displayorder>='0' OR (displayorder IN ('-4', '-2') AND authorid='$_G[uid]'))"))) {

		if($thread['readperm'] && $thread['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $thread['authorid'] != $_G['uid']) {
			showmessage('thread_nopermission', NULL, array('readperm' => $thread['readperm']), array('login' => 1));
		}

		$_G['fid'] = $thread['fid'];
		$special = $thread['special'];

	} else {
		showmessage('thread_nonexistence');
	}

	if($_G['gp_action'] == 'reply' && ($thread['closed'] == 1) && !$_G['forum']['ismoderator']) {
		showmessage('post_thread_closed');
	}

}

if($_G['forum']['status'] == 3) {
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_G['gp_extra']) ? '&action=list&'.preg_replace("/^(&)*/", '', $_G['gp_extra']) : '').'#groupnav';
	$navigation = ' <em>&rsaquo;</em> <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.get_groupnav($_G['forum']);
} else {
	$returnurl = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].(!empty($_G['gp_extra']) ? '&'.preg_replace("/^(&)*/", '', $_G['gp_extra']) : '');
	$navigation = '<em>&rsaquo;</em> <a href="'.$returnurl.'">'.$_G['forum']['name'].'</a> '.$navigation;

	if($_G['forum']['type'] == 'sub') {
		$fup = DB::fetch_first("SELECT name, fid FROM ".DB::table('forum_forum')." WHERE fid='".$_G['forum']['fup']."'");
		$navigation = '<em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$fup['fid'].'">'.$fup['name'].'</a> '.$navigation;
	}
	$navigation = ' <em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a> '.$navigation;
}

periodscheck('postbanperiods');

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	showmessage('forum_passwd', "forum.php?mod=forumdisplay&fid=$_G[fid]");
}

if(empty($_G['forum']['allowview'])) {
	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessagenoperm('viewperm', $_G['fid']);
	}
} elseif($_G['forum']['allowview'] == -1) {
	showmessage('forum_access_view_disallow');
}

formulaperm($_G['forum']['formulaperm']);

if(!$_G['adminid'] && $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60)) {
	if(TIMESTAMP - (DB::result_first("SELECT regdate FROM ".DB::table('common_member')." WHERE uid='$_G[uid]'")) < $_G['setting']['newbiespan'] * 60) {
		showmessage('post_newbie_span', '', array('newbiespan' => $_G['setting']['newbiespan']));
	}
}

$special = $special > 0 && $special < 7 || $special == 127 ? intval($special) : 0;

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
$_G['group']['attachextensions'] = $_G['forum']['attachextensions'] ? $_G['forum']['attachextensions'] : $_G['group']['attachextensions'];
if($_G['group']['attachextensions']) {
	$imgexts = explode(',', str_replace(' ', '', $_G['group']['attachextensions']));
	$imgexts = array_intersect(array('jpg','jpeg','gif','png','bmp'), $imgexts);
	$imgexts = implode(', ', $imgexts);
} else {
	$imgexts = 'jpg, jpeg, gif, png, bmp';
}
$allowuploadnum = TRUE;
if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
	if($_G['group']['maxattachnum']) {
		$allowuploadnum = $_G['group']['maxattachnum'] - DB::result_first("SELECT count(*) FROM ".DB::table('forum_attachment')." WHERE uid='$_G[uid]' AND pid>'0' AND dateline>'$_G[timestamp]'-86400");
		$allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
	}
	if($_G['group']['maxsizeperday']) {
		$allowuploadsize = $_G['group']['maxsizeperday'] - intval(DB::result_first("SELECT SUM(filesize) FROM ".DB::table('forum_attachment')." WHERE uid='$_G[uid]' AND dateline>'$_G[timestamp]'-86400"));
		$allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
		$allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
	}
}
$allowpostimg = $_G['group']['allowpostimage'] && $imgexts;
$enctype = ($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) ? 'enctype="multipart/form-data"' : '';
$maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1).'MB' : round(($_G['group']['maxattachsize'] / 1024)).'KB';

$postcredits = $_G['forum']['postcredits'] ? $_G['forum']['postcredits'] : $_G['setting']['creditspolicy']['post'];
$replycredits = $_G['forum']['replycredits'] ? $_G['forum']['replycredits'] : $_G['setting']['creditspolicy']['reply'];
$digestcredits = $_G['forum']['digestcredits'] ? $_G['forum']['digestcredits'] : $_G['setting']['creditspolicy']['digest'];
$postattachcredits = $_G['forum']['postattachcredits'] ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];

$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

$extra = !empty($_G['gp_extra']) ? rawurlencode($_G['gp_extra']) : '';
$notifycheck = empty($emailnotify) ? '' : 'checked="checked"';
$stickcheck = empty($sticktopic) ? '' : 'checked="checked"';
$digestcheck = empty($addtodigest) ? '' : 'checked="checked"';

$subject = isset($_G['gp_subject']) ? dhtmlspecialchars(censor(trim($_G['gp_subject']))) : '';
$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
$message = isset($_G['gp_message']) ? censor($_G['gp_message']) : '';
$polloptions = isset($polloptions) ? censor(trim($polloptions)) : '';
$readperm = isset($_G['gp_readperm']) ? intval($_G['gp_readperm']) : 0;
$price = isset($_G['gp_price']) ? intval($_G['gp_price']) : 0;
$_G['setting']['tagstatus'] = $_G['setting']['tagstatus'] && $_G['forum']['allowtag'] ? ($_G['setting']['tagstatus'] == 2 ? 2 : $_G['forum']['allowtag']) : 0;

if(empty($bbcodeoff) && !$_G['group']['allowhidecode'] && !empty($message) && preg_match("/\[hide=?\d*\].+?\[\/hide\]/is", preg_replace("/(\[code\](.+?)\[\/code\])/is", ' ', $message))) {
	showmessage('post_hide_nopermission');
}

if(periodscheck('postmodperiods', 0)) {
	$modnewthreads = $modnewreplies = 1;
} else {
	$censormod = censormod($subject."\t".$message);
	$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $_G['forum']['modnewposts'] || $censormod ? 1 : 0;
	$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $_G['forum']['modnewposts'] == 2 || $censormod ? 1 : 0;
}

require_once libfile('class/censor');
$censor = & discuz_censor::instance();
if(!empty($_G['gp_attachnew'])) {
	foreach($_G['gp_attachnew'] as $key => $attachnew) {
		censor($attachnew['description']);
		$censor->check($_G['gp_attachnew'][$key]['description']);
		if($censor->modmoderated()) {
			if(!$modnewthreads || !$modnewreplies) {
				$modnewthreads = $modnewreplies = 1;
			}
		}
	}
}

if($_G['forum']['status'] == 3) {
	$modnewthreads = !$_G['group']['allowgroupdirectpost'] || $_G['group']['allowgroupdirectpost'] == 1 || $censormod ? 1 : 0;
	$modnewposts = !$_G['group']['allowgroupdirectpost'] || $_G['group']['allowgroupdirectpost'] == 2 || $censormod ? 1 : 0;
}
$allowposturl = $_G['forum']['status'] != 3 ? $_G['group']['allowposturl'] : $_G['group']['allowgroupposturl'];
if($allowposturl < 3 && $message) {
	$urllist = get_url_list($message);
	if(is_array($urllist[1])) foreach($urllist[1] as $key => $val) {
		if(!$val = trim($val)) continue;
		if(!iswhitelist($val)) {
			if($allowposturl == 0) {
				showmessage('post_url_nopermission');
			} elseif($allowposturl == 1) {
				$modnewthreads = $modnewreplies = 1;
				break;
			} elseif($allowposturl == 2) {
				$message = str_replace('[url]'.$urllist[0][$key].'[/url]', $urllist[0][$key], $message);
				$message = preg_replace("@\[url={$urllist[0][$key]}\](.*?)\[/url\]@i", '\\1', $message);
			}
		}
	}
}

$urloffcheck = $usesigcheck = $smileyoffcheck = $codeoffcheck = $htmloncheck = $emailcheck = '';

$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);

$_G['group']['allowpostpoll'] = $_G['group']['allowpost'] && $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
$_G['group']['allowposttrade'] = $_G['group']['allowpost'] && $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
$_G['group']['allowpostreward'] = $_G['group']['allowpost'] && $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
$_G['group']['allowpostactivity'] = $_G['group']['allowpost'] && $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
$_G['group']['allowpostdebate'] = $_G['group']['allowpost'] && $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
$usesigcheck = $_G['uid'] && $_G['group']['maxsigsize'] ? 'checked="checked"' : '';
$ordertypecheck = !empty($thread['tid']) && getstatus($thread['status'], 4) ? 'checked="checked"' : '';
$specialextra = !empty($_G['gp_specialextra']) ? $_G['gp_specialextra'] : '';

if($specialextra && $_G['group']['allowpost'] && $_G['setting']['threadplugins'] &&
	(!array_key_exists($specialextra, $_G['setting']['threadplugins']) ||
	!@in_array($specialextra, is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : unserialize($_G['forum']['threadplugin'])) ||
	!@in_array($specialextra, $_G['group']['allowthreadplugin']))) {
	$specialextra = '';
}

$_G['group']['allowanonymous'] = $_G['forum']['allowanonymous'] || $_G['group']['allowanonymous'] ? 1 : 0;

if($_G['gp_action'] == 'newthread' && $_G['forum']['allowspecialonly'] && !$special) {
	if($_G['group']['allowpostpoll']) {
		$special = 1;
	} elseif($_G['group']['allowposttrade']) {
		$special = 2;
	} elseif($_G['group']['allowpostreward']) {
		$special = 3;
	} elseif($_G['group']['allowpostactivity']) {
		$special = 4;
	} elseif($_G['group']['allowpostdebate']) {
		$special = 5;
	} elseif($_G['group']['allowpost'] && $_G['setting']['threadplugins'] && $_G['group']['allowthreadplugin'] && ($_G['forum']['threadplugin'] = unserialize($_G['forum']['threadplugin']))) {
		$threadpluginary = array_intersect($_G['forum']['threadplugin'], $_G['group']['allowthreadplugin']);
		$specialextra = $threadpluginary[0] ? $threadpluginary[0] : '';
	}

	if(!$special && !$specialextra) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}
}

$editorid = 'e';
$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 2, 0, STR_PAD_LEFT);
$editormode = $_G['setting']['editoroptions']{0};
$allowswitcheditor = $_G['setting']['editoroptions']{1};
$editor = array(
	'editormode' => $editormode,
	'allowswitcheditor' => $allowswitcheditor,
	'allowhtml' => $_G['group']['allowhtml'],
	'allowhtml' => $_G['forum']['allowhtml'],
	'allowsmilies' => $_G['forum']['allowsmilies'],
	'allowbbcode' => $_G['forum']['allowbbcode'],
	'allowimgcode' => $_G['forum']['allowimgcode'],
	'allowresize' => 1,
	'textarea' => 'message',
	'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? 1 : $_G['cookie']['editormode_'.$editorid],
);
if($specialextra) {
	$special = 127;
}

if($_G['gp_action'] == 'newthread') {
	$policykey = 'post';
} elseif($_G['gp_action'] == 'reply') {
	$policykey = 'reply';
} else {
	$policykey = '';
}
if($policykey) {
	$postcredits = $_G['forum'][$policykey.'credits'] ? $_G['forum'][$policykey.'credits'] : $_G['setting']['creditspolicy'][$policykey];
}

$albumlist = array();
if($_G['uid']) {
	$query = DB::query("SELECT albumid, albumname, picnum FROM ".DB::table('home_album')." WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
	while($value = DB::fetch($query)) {
		if($value['picnum']) {
			$albumlist[] = $value;
		}
	}
}

$posturl = "action=$_G[gp_action]&fid=$_G[fid]".
	(!empty($_G['tid']) ? "&tid=$_G[tid]" : '').
	(!empty($pid) ? "&pid=$pid" : '').
	(!empty($special) ? "&special=$special" : '').
	(!empty($sortid) ? "&sortid=$sortid" : '').
	(!empty($typeid) ? "&typeid=$typeid" : '').
	(!empty($_G['gp_firstpid']) ? "&firstpid=$firstpid" : '').
	(!empty($_G['gp_addtrade']) ? "&addtrade=$addtrade" : '');

if($_G['gp_action'] == 'reply') {
	check_allow_action('allowreply');
} else {
	check_allow_action('allowpost');
}

if($_G['gp_action'] == 'newthread') {
	$savethreads = array();
	$savethreadothers = array();
	$query = DB::query("SELECT dateline, fid, tid, pid, subject FROM ".DB::table('forum_post')." WHERE authorid='$_G[uid]' AND invisible='-3' AND first='1'");
	while($savethread = DB::fetch($query)) {
		$savethread['dateline'] = dgmdate($savethread['dateline'], 'u');
		if($_G['fid'] == $savethread['fid']) {
			$savethreads[] = $savethread;
		} else {
			$savethreadothers[] = $savethread;
		}
	}
	$savethreadcount = count($savethreads);
	$savethreadothercount = count($savethreadothers);
	if($savethreadothercount) {
		loadcache('forums');
	}
	unset($savethread);
}

if($special == 4) {
	$_G['setting']['activityfield'] = $_G['setting']['activityfield'] ? unserialize($_G['setting']['activityfield']) : array();
}

$navtitle = lang('core', 'title_'.$_G['gp_action'].'_post');

if($_G['gp_action'] == 'newthread') {
	loadcache('groupreadaccess');
	$navtitle .= ' - '.$_G['forum']['name'];
	require_once libfile('post/newthread', 'include');
} elseif($_G['gp_action'] == 'reply') {
	$navtitle .= ' - '.$thread['subject'].' - '.$_G['forum']['name'];
	require_once libfile('post/newreply', 'include');
} elseif($_G['gp_action'] == 'edit') {
	loadcache('groupreadaccess');
	$navtitle .= ' - '.$thread['subject'].' - '.$_G['forum']['name'];
	require_once libfile('post/editpost', 'include');
} elseif($_G['gp_action'] == 'newtrade') {
	$navtitle .= ' - '.$_G['forum']['name'];
	require_once libfile('post/newtrade', 'include');
}

function check_allow_action($action = 'allowpost') {
	global $_G;
	if(isset($_G['forum'][$action]) && $_G['forum'][$action] == -1) {
		showmessage('forum_access_disallow');
	}
}

?>