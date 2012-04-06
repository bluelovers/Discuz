<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_ajax.php 27348 2012-01-17 07:34:00Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if($_G['gp_action'] == 'checkusername') {


	$username = trim($_G['gp_username']);
	$usernamelen = dstrlen($username);
	if($usernamelen < 3) {
		showmessage('profile_username_tooshort', '', array(), array('handle' => false));
	} elseif($usernamelen > 15) {
		showmessage('profile_username_toolong', '', array(), array('handle' => false));
	}

	loaducenter();
	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('profile_username_illegal', '', array(), array('handle' => false));
	} elseif($ucresult == -2) {
		showmessage('profile_username_protect', '', array(), array('handle' => false));
	} elseif($ucresult == -3) {
		if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$username'")) {
			showmessage('register_check_found', '', array(), array('handle' => false));
		} else {
			showmessage('register_activation', '', array(), array('handle' => false));
		}
	}

} elseif($_G['gp_action'] == 'checkemail') {

	require_once libfile('function/member');
	checkemail($_G['gp_email']);

} elseif($_G['gp_action'] == 'checkinvitecode') {

	$invitecode = trim($_G['gp_invitecode']);
	if(!$invitecode) {
		showmessage('no_invitation_code', '', array(), array('handle' => false));
	}
	$result = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_invite')." WHERE code='$invitecode'");
	if($invite = DB::fetch($query)) {
		if(empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
			$result['uid'] = $invite['uid'];
			$result['id'] = $invite['id'];
			$result['appid'] = $invite['appid'];
		}
	}
	if(empty($result)) {
		showmessage('wrong_invitation_code', '', array(), array('handle' => false));
	}

} elseif($_G['gp_action'] == 'checkuserexists') {

	$check = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".trim($_G['gp_username'])."'");
	$check ? showmessage('<img src="'.$_G['style']['imgdir'].'/check_right.gif" width="13" height="13">', '', array(), array('msgtype' => 3))
		: showmessage('username_nonexistence', '', array(), array('msgtype' => 3));

} elseif($_G['gp_action'] == 'attachlist') {

	require_once libfile('function/post');
	loadcache('groupreadaccess');
	$attachlist = getattach($_G['gp_pid'], intval($_G['gp_posttime']), $_G['gp_aids']);
	$attachlist = $attachlist['attachs']['unused'];
	$_G['group']['maxprice'] = isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]) ? $_G['group']['maxprice'] : 0;

	include template('common/header_ajax');
	include template('forum/ajax_attachlist');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'imagelist') {

	require_once libfile('function/post');
	$attachlist = getattach($_G['gp_pid'], intval($_G['gp_posttime']), $_G['gp_aids']);
	$imagelist = $attachlist['imgattachs']['unused'];

	include template('common/header_ajax');
	include template('forum/ajax_imagelist');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'deleteattach') {

	$count = 0;
	if($_G['gp_aids']) {
		foreach($_G['gp_aids'] as $aid) {
			$attach = DB::fetch_first("SELECT * FROM ".DB::table(getattachtablebyaid($aid))." WHERE aid='$aid'");
			if($attach && ($attach['pid'] && $attach['pid'] == $_G['gp_pid'] && $_G['uid'] == $attach['uid'] || $_G['forum']['ismoderator'] || !$attach['pid'] && $_G['uid'] == $attach['uid'])) {
				DB::delete(getattachtablebyaid($aid), "aid='$aid'");
				DB::delete('forum_attachment', "aid='$aid'");
				dunlink($attach);
				$count++;
			}
		}
	}
	include template('common/header_ajax');
	echo $count;
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'secondgroup') {

	require_once libfile('function/group');
	$groupselect = get_groupselect($_G['gp_fupid'], $_G['gp_groupid']);
	include template('common/header_ajax');
	include template('forum/ajax_secondgroup');
	include template('common/footer_ajax');
	dexit();

} elseif($_G['gp_action'] == 'displaysearch_adv') {
	$display = $_G['gp_display'] == 1 ? 1 : '';
	dsetcookie('displaysearch_adv', $display);
} elseif($_G['gp_action'] == 'checkgroupname') {
	$groupname = stripslashes(trim($_G['gp_groupname']));
	if(empty($groupname)) {
		showmessage('group_name_empty', '', array(), array('msgtype' => 3));
	}
	$tmpname = cutstr($groupname, 20, '');
	if($tmpname != $groupname) {
		showmessage('group_name_oversize', '', array(), array('msgtype' => 3));
	}
	if(DB::result_first("SELECT fid FROM ".DB::table('forum_forum')." WHERE name='".addslashes($groupname)."'")) {
		showmessage('group_name_exist', '', array(), array('msgtype' => 3));
	}
	showmessage('', '', array(), array('msgtype' => 3));
	include template('common/header_ajax');
	include template('common/footer_ajax');
	dexit();
} elseif($_G['gp_action'] == 'getthreadtypes') {
	include template('common/header_ajax');
	if(empty($_G['gp_selectname'])) $_G['gp_selectname'] = 'threadtypeid';
	echo '<select name="'.$_G['gp_selectname'].'">';
	if(!empty($_G['forum']['threadtypes']['types'])) {
		if(!$_G['forum']['threadtypes']['required']) {
			echo '<option value="0"></option>';
		}
		foreach($_G['forum']['threadtypes']['types'] as $typeid => $typename) {
			if($_G['forum']['threadtypes']['moderators'][$typeid] && $_G['forum'] && !$_G['forum']['ismoderator']) {
				continue;
			}
			echo '<option value="'.$typeid.'">'.$typename.'</option>';
		}
	} else {
		echo '<option value="0" /></option>';
	}
	echo '</select>';
	include template('common/footer_ajax');
} elseif($_G['gp_action'] == 'getimage') {
	$_G['gp_aid'] = intval($_G['gp_aid']);
	$image = DB::fetch_first('SELECT * FROM '.DB::table(getattachtablebyaid($_G['gp_aid']))." WHERE aid='$_G[gp_aid]' AND isimage='1'");
	include template('common/header_ajax');
	if($image['aid']) {
		echo '<img src="'.getforumimg($image['aid'], 1, 300, 300, 'fixnone').'" id="image_'.$image['aid'].'" onclick="insertAttachimgTag(\''.$image['aid'].'\')" width="'.($image['width'] < 110 ? $image['width'] : 110).'" cwidth="'.($image['width'] < 300 ? $image['width'] : 300).'" />';
	}
	include template('common/footer_ajax');
	dexit();
} elseif($_G['gp_action'] == 'setthreadcover') {
	$aid = intval($_G['gp_aid']);
	require_once libfile('function/post');
	if($_G['forum'] && $aid) {
		$threadimage = DB::fetch_first("SELECT tid, pid, attachment, remote FROM ".DB::table(getattachtablebyaid($aid))." WHERE aid='$aid'");
		if($threadimage['tid'] && $threadimage['pid']) {
			$firstpost = DB::result_first("SELECT first FROM ".DB::table(getposttablebytid($threadimage['tid']))." WHERE pid='$threadimage[pid]'");
			if(empty($firstpost)) {
				$trade_aid = DB::result_first("SELECT aid FROM ".DB::table('forum_trade')." WHERE pid='$threadimage[pid]'");
				if($trade_aid == $aid ) {
					$firstpost = 1;
				}
			}

		} else {
			$firstpost = 0;
		}
		if(empty($firstpost)) {
			showmessage('set_cover_faild', '', array(), array('closetime' => 3));
		}
		if(setthreadcover(0, 0, $aid)) {
			$threadimage = daddslashes($threadimage);
			DB::delete('forum_threadimage', "tid='$threadimage[tid]'");
			DB::insert('forum_threadimage', array(
				'tid' => $threadimage['tid'],
				'attachment' => $threadimage['attachment'],
				'remote' => $threadimage['remote'],
			));
			showmessage('set_cover_succeed', '', array(), array('alert' => 'right', 'closetime' => 1));
		}
	}
	showmessage('set_cover_faild', '', array(), array('closetime' => 3));

} elseif($_G['gp_action'] == 'editor') {
	$editorid = 'e';
	$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 2, 0, STR_PAD_LEFT);
	$editormode = $_G['setting']['editoroptions']{0};
	$allowswitcheditor = $_G['setting']['editoroptions']{1};
	$editor = array(
		'editormode' => $editormode,
		'allowswitcheditor' => $allowswitcheditor,
		'allowhtml' => 0,
		'allowsmilies' => $_G['forum']['allowsmilies'],
		'allowbbcode' => $_G['forum']['allowbbcode'],
		'allowimgcode' => $_G['forum']['allowimgcode'],
		'allowcustombbcode' => 0,
		'allowchecklength' => 0,
		'allowtopicreset' => 0,
		'allowresize' => 1,
		'textarea' => 'message',
		'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? $_G['setting']['editoroptions']{2} : $_G['cookie']['editormode_'.$editorid],
	);
	loadcache('bbcodes_display');

	$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
	$allowpostattach = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
	if(!$allowpostattach) {
		$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
		$allowpostattach = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
	}

	include template('forum/editor_ajax');
	exit;

} elseif($_G['gp_action'] == 'updateattachlimit') {

	$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
	$_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
	$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
	$_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));

	$allowuploadnum = $allowuploadtoday = TRUE;
	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		if($_G['group']['maxattachnum']) {
			$allowuploadnum = $_G['group']['maxattachnum'] - getuserprofile('todayattachs');
			$allowuploadnum = $allowuploadnum < 0 ? 0 : $allowuploadnum;
			if(!$allowuploadnum) {
				$allowuploadtoday = false;
			}
		}
		if($_G['group']['maxsizeperday']) {
			$allowuploadsize = $_G['group']['maxsizeperday'] - getuserprofile('todayattachsize');
			$allowuploadsize = $allowuploadsize < 0 ? 0 : $allowuploadsize;
			if(!$allowuploadsize) {
				$allowuploadtoday = false;
			}
			$allowuploadsize = $allowuploadsize / 1048576 >= 1 ? round(($allowuploadsize / 1048576), 1).'MB' : round(($allowuploadsize / 1024)).'KB';
		}
	}
	include template('common/header_ajax');
	include template('forum/post_attachlimit');
	include template('common/footer_ajax');
	exit;

} elseif($_G['gp_action'] == 'forumchecknew' && !empty($_G['gp_fid']) && !empty($_G['gp_time'])) {
	$fid = intval($_G['gp_fid']);
	$time = intval($_G['gp_time']);

	if(!$_G['gp_uncheck']) {
		if($lastpost_str = DB::result_first("SELECT lastpost FROM ".DB::table('forum_forum')." WHERE fid = '$fid' LIMIT 1")) {
			$lastpost = explode("\t", $lastpost_str);
			unset($lastpost_str);
		}
		include template('common/header_ajax');
		echo $lastpost['2'] > $time ? 1 : 0 ;
		include template('common/footer_ajax');
		exit;
	} else {
		$forum_field = DB::fetch_first("SELECT threadtypes AS tt, threadsorts AS ts FROM ".DB::table('forum_forumfield')." WHERE fid = '$fid' LIMIT 1");
		$forum_field['threadtypes'] = unserialize($forum_field['tt']);
		$forum_field['threadsorts'] = unserialize($forum_field['ts']);
		unset($forum_field['tt'], $forum_field['ts']);
		$forum_field = daddslashes($forum_field);
		$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));
		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE fid = '$fid' AND displayorder = 0 AND lastpost > '$time'  AND lastpost < '".TIMESTAMP."' ORDER BY lastpost DESC LIMIT 100");
		while($thread = DB::fetch($query)) {
			list($thread['subject'], $thread['author'], $thread['lastposter']) = daddslashes(array($thread['subject'], $thread['author'], $thread['lastposter']));
			$thread['dateline'] = $thread['dateline'] > $todaytime ? "<span class=\"xi1\">".dgmdate($thread['dateline'], 'd')."</span>" : "<span>".dgmdate($thread['dateline'], 'd')."</span>";
			$thread['lastpost'] = dgmdate($thread['lastpost']);
			if($forum_field['threadtypes']['prefix']) {
				if($forum_field['threadtypes']['prefix'] == 1) {
					$thread['threadtype'] = $forum_field['threadtypes']['types'][$thread['typeid']] ? '<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&filter=typeid&typeid='.$thread['typeid'].'">'.$forum_field['threadtypes']['types'][$thread['typeid']].'</a>]</em> ' : '' ;
				} elseif($forum_field['threadtypes']['prefix'] == 2) {
					$thread['threadtype'] = $forum_field['threadtypes']['icons'][$thread['typeid']] ? '<em><a href="forum.php?mod=forumdisplay&fid='.$fid.'&filter=typeid&typeid='.$thread['typeid'].'"><img src="'.$forum_field['threadtypes']['icons'][$thread['typeid']].'"/></a></em> ' : '' ;
				}
			}
			if($forum_field['threadsorts']['prefix']) {
				$thread['threadsort'] = $forum_field['threadsorts']['types'][$thread['sortid']] ? '<em>[<a href="forum.php?mod=forumdisplay&fid='.$fid.'&filter=sortid&typeid='.$thread['sortid'].'">'.$forum_field['threadsorts']['types'][$thread['sortid']].'</a>]</em>' : '' ;
			}
			if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
				$thread['threadurl'] = '<a href="'.rewriteoutput('forum_viewthread', 1, '', $thread['tid'], 1, '', '').'" class="xst" onclick="atarget(this)">'.$thread['subject'].'</a>';
			} else {
				$thread['threadurl'] = '<a href="forum.php?mod=viewthread&amp;tid='.$thread['tid'].'" class="xst" onclick="atarget(this)">'.$thread['subject'].'</a>';
			}
			$thread['threadurl'] = $thread['threadtype'].$thread['threadsort'].$thread['threadurl'];
			if(in_array('home_space', $_G['setting']['rewritestatus'])) {
				$thread['authorurl'] = '<a href="'.rewriteoutput('home_space', 1, '', $thread['authorid'], '', '').'">'.$thread['author'].'</a>';
				$thread['lastposterurl'] = '<a href="'.rewriteoutput('home_space', 1, '', '', rawurlencode($thread['lastposter']), '').'">'.$thread['lastposter'].'</a>';
			} else {
				$thread['authorurl'] = '<a href="home.php?mod=space&uid='.$thread['authorid'].'">'.$thread['author'].'</a>';
				$thread['lastposterurl'] = '<a href="home.php?mod=space&username='.rawurlencode($thread['lastposter']).'">'.$thread['lastposter'].'</a>';
			}
			$threadlist[] = $thread;
		}
		if($threadlist) {
			krsort($threadlist);
		}
		include template('forum/ajax_threadlist');

	}
} elseif($_G['gp_action'] == 'downremoteimg') {
	$_G['gp_message'] = dstripslashes($_G['gp_message']);
	$_G['gp_message'] = str_replace(array("\r", "\n"), array($_G['gp_wysiwyg'] ? '<br />' : '', "\\n"), $_G['gp_message']);
	preg_match_all("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", $_G['gp_message'], $image1, PREG_SET_ORDER);
	preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $_G['gp_message'], $image2, PREG_SET_ORDER);
	$temp = $aids = $existentimg = array();
	if(is_array($image1) && !empty($image1)) {
		foreach($image1 as $value) {
			$temp[] = array(
				'0' => $value[0],
				'1' => trim(!empty($value[1]) ? $value[1] : $value[2])
			);
		}
	}
	if(is_array($image2) && !empty($image2)) {
		foreach($image2 as $value) {
			$temp[] = array(
				'0' => $value[0],
				'1' => trim($value[2])
			);
		}
	}
	require_once libfile('class/image');
	if(is_array($temp) && !empty($temp)) {
		require_once libfile('class/upload');
		$upload = new discuz_upload();
		$attachaids = array();

		foreach($temp as $value) {
			$imageurl = $value[1];
			$hash = md5($imageurl);
			if(strlen($imageurl)) {
				$imagereplace['oldimageurl'][] = $value[0];
				if(!isset($existentimg[$hash])) {
					$existentimg[$hash] = $imageurl;
					$attach['ext'] = $upload->fileext($imageurl);
					if(!$upload->is_image_ext($attach['ext'])) {
						continue;
					}
					$content = '';
					if(preg_match('/^(http:\/\/|\.)/i', $imageurl)) {
						$content = dfsockopen($imageurl);
					} elseif(preg_match('/^('.preg_quote(getglobal('setting/attachurl'), '/').')/i', $imageurl)) {
						$imagereplace['newimageurl'][] = $value[0];
					}
					if(empty($content)) continue;
					$patharr = explode('/', $imageurl);
					$attach['name'] =  trim($patharr[count($patharr)-1]);
					$attach['thumb'] = '';

					$attach['isimage'] = $upload -> is_image_ext($attach['ext']);
					$attach['extension'] = $upload -> get_target_extension($attach['ext']);
					$attach['attachdir'] = $upload -> get_target_dir('forum');
					$attach['attachment'] = $attach['attachdir'] . $upload->get_target_filename('forum').'.'.$attach['extension'];
					$attach['target'] = getglobal('setting/attachdir').'./forum/'.$attach['attachment'];

					if(!@$fp = fopen($attach['target'], 'wb')) {
						continue;
					} else {
						flock($fp, 2);
						fwrite($fp, $content);
						fclose($fp);
					}
					if(!$upload->get_image_info($attach['target'])) {
						@unlink($attach['target']);
						continue;
					}
					$attach['size'] = filesize($attach['target']);
					$upload->attach = $attach;
					$thumb = $width = 0;
					if($upload->attach['isimage']) {
						if($_G['setting']['thumbstatus']) {
							$image = new image();
							$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], $_G['setting']['thumbsource']) ? 1 : 0;
							$width = $image->imginfo['width'];
						}
						if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
							list($width) = @getimagesize($upload->attach['target']);
						}
						if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
							$image = new image();
							$image->Watermark($attach['target'], '', 'forum');
							$upload->attach['size'] = $image->imginfo['size'];
						}
					}
					$aids[] = $aid = getattachnewaid();
					$setarr = array(
						'aid' => $aid,
						'dateline' => $_G['timestamp'],
						'filename' => daddslashes($upload->attach['name']),
						'filesize' => $upload->attach['size'],
						'attachment' => $upload->attach['attachment'],
						'isimage' => $upload->attach['isimage'],
						'uid' => $_G['uid'],
						'thumb' => $thumb,
						'remote' => '0',
						'width' => $width
					);
					DB::insert("forum_attachment_unused", $setarr);
					$attachaids[$hash] = $imagereplace['newimageurl'][] = '[attachimg]'.$aid.'[/attachimg]';

				} else {
					$imagereplace['newimageurl'][] = $attachaids[$hash];
				}
			}
		}
		if(!empty($aids)) {
			require_once libfile('function/post');
		}
		$_G['gp_message'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_G['gp_message']);
		$_G['gp_message'] = addcslashes($_G['gp_message'], '/"');

	}
	print <<<EOF
		<script type="text/javascript">
			parent.ATTACHORIMAGE = 1;
			parent.updateDownImageList('$_G[gp_message]');
		</script>
EOF;
	dexit();
}

showmessage('succeed', '', array(), array('handle' => false));

?>