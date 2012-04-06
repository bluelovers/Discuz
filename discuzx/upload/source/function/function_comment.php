<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_comment.php 20630 2011-03-01 02:45:58Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function add_comment($message, $id, $idtype, $cid = 0) {
	global $_G, $bbcode;

	$summay = getstr($message, 150, 1, 1, 0, -1);


	$comment = array();
	if($cid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE cid='$cid' AND id='$id' AND idtype='$idtype'");
		$comment = DB::fetch($query);
		if($comment && $comment['authorid'] != $_G['uid']) {
			$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote\>.*?\<\/blockquote\>\<\/div\>/is", '', $comment['message']);
			$comment['message'] = $bbcode->html2bbcode($comment['message']);
			$message = addslashes("<div class=\"quote\"><blockquote><b>".$comment['author']."</b>: ".getstr($comment['message'], 150, 0, 0, 2, 1).'</blockquote></div>').$message;
			if($comment['idtype'] == 'uid') {
				$id = $comment['authorid'];
			}
		} else {
			$comment = array();
		}
	}

	$hotarr = array();
	$stattype = '';
	$tospace = $pic = $blog = $album = $share = $poll = array();

	switch($idtype) {
		case 'uid':
			$tospace = getspace($id);
			$stattype = 'wall';
			break;
		case 'picid':
			$query = DB::query("SELECT p.*, pf.hotuser
				FROM ".DB::table('home_pic')." p
				LEFT JOIN ".DB::table('home_picfield')." pf
				USING(picid)
				WHERE p.picid='$id'");
			$pic = DB::fetch($query);
			if(empty($pic)) {
				showmessage('view_images_do_not_exist');
			}

			$tospace = getspace($pic['uid']);

			$album = array();
			if($pic['albumid']) {
				$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$pic[albumid]'");
				if(!$album = DB::fetch($query)) {
					DB::update('home_pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));
				}
			}

			if(!ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
				showmessage('no_privilege_ckfriend_pic');
			} elseif(!$tospace['self'] && $album['friend'] == 4) {
				$cookiename = "view_pwd_album_$album[albumid]";
				$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
				if($cookievalue != md5(md5($album['password']))) {
					showmessage('no_privilege_ckpassword_pic');
				}
			}

			$hotarr = array('picid', $pic['picid'], $pic['hotuser']);
			$stattype = 'piccomment';
			break;
		case 'blogid':
			$query = DB::query("SELECT b.*, bf.target_ids, bf.hotuser
				FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid)
				WHERE b.blogid='$id'");
			$blog = DB::fetch($query);
			if(empty($blog)) {
				showmessage('view_to_info_did_not_exist');
			}

			$tospace = getspace($blog['uid']);

			if(!ckfriend($blog['uid'], $blog['friend'], $blog['target_ids'])) {
				showmessage('no_privilege_ckfriend_blog');
			} elseif(!$tospace['self'] && $blog['friend'] == 4) {
				$cookiename = "view_pwd_blog_$blog[blogid]";
				$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
				if($cookievalue != md5(md5($blog['password']))) {
					showmessage('no_privilege_ckpassword_blog');
				}
			}

			if(!empty($blog['noreply'])) {
				showmessage('do_not_accept_comments');
			}
			if($blog['target_ids']) {
				$blog['target_ids'] .= ",$blog[uid]";
			}

			$hotarr = array('blogid', $blog['blogid'], $blog['hotuser']);
			$stattype = 'blogcomment';
			break;
		case 'sid':
			$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid='$id'");
			$share = DB::fetch($query);
			if(empty($share)) {
				showmessage('sharing_does_not_exist');
			}

			$tospace = getspace($share['uid']);

			$hotarr = array('sid', $share['sid'], $share['hotuser']);
			$stattype = 'sharecomment';
			break;
		default:
			showmessage('non_normal_operation');
			break;
	}
	if(empty($tospace)) {
		showmessage('space_does_not_exist', '', array(), array('return' => true));
	}

	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	if($hotarr && $tospace['uid'] != $_G['uid']) {
		hot_update($hotarr[0], $hotarr[1], $hotarr[2]);
	}

	$fs = array();
	$fs['icon'] = 'comment';
	$fs['target_ids'] = '';
	$fs['friend'] = '';
	$fs['body_template'] = '';
	$fs['body_data'] = array();
	$fs['body_general'] = '';
	$fs['images'] = array();
	$fs['image_links'] = array();

	switch ($idtype) {
		case 'uid':
			$fs['icon'] = 'wall';
			$fs['title_template'] = 'feed_comment_space';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">$tospace[username]</a>");
			break;
		case 'picid':
			$fs['title_template'] = 'feed_comment_image';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>");
			$fs['body_template'] = '{pic_title}';
			$fs['body_data'] = array('pic_title'=>$pic['title']);
			$fs['body_general'] = $summay;
			$fs['images'] = array(pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']));
			$fs['image_links'] = array("home.php?mod=space&uid=$tospace[uid]&do=album&picid=$pic[picid]");
			$fs['target_ids'] = $album['target_ids'];
			$fs['friend'] = $album['friend'];
			break;
		case 'blogid':
			DB::query("UPDATE ".DB::table('home_blog')." SET replynum=replynum+1 WHERE blogid='$id'");
			$fs['title_template'] = 'feed_comment_blog';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'blog'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]&do=blog&id=$id\">$blog[subject]</a>");
			$fs['target_ids'] = $blog['target_ids'];
			$fs['friend'] = $blog['friend'];
			break;
		case 'sid':
			$fs['title_template'] = 'feed_comment_share';
			$fs['title_data'] = array('touser'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]\">".$tospace['username']."</a>", 'share'=>"<a href=\"home.php?mod=space&uid=$tospace[uid]&do=share&id=$id\">".str_replace(lang('spacecp', 'share_action'), '', $share['title_template'])."</a>");
			break;
	}

	$message = censor($message);
	if(censormod($message)) {
		$comment_status = 1;
	} else {
		$comment_status = 0;
	}

	$setarr = array(
		'uid' => $tospace['uid'],
		'id' => $id,
		'idtype' => $idtype,
		'authorid' => $_G['uid'],
		'author' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'ip' => $_G['clientip'],
		'status' => $comment_status,
	);
	$cid = DB::insert('home_comment', $setarr, 1);

	$action = 'comment';
	$becomment = 'getcomment';
	$note = $q_note = '';
	$note_values = $q_values = array();

	switch ($idtype) {
		case 'uid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=wall&cid=$cid";

			$note_type = 'wall';
			$note = 'wall';
			$note_values = array('url'=>$n_url);
			$q_note = 'wall_reply';
			$q_values = array('url'=>$n_url);

			if($comment) {
				$msg = 'note_wall_reply_success';
				$magvalues = array('username' => $tospace['username']);
				$becomment = '';
			} else {
				$msg = 'do_success';
				$magvalues = array();
				$becomment = 'getguestbook';
			}

			$action = 'guestbook';
			break;
		case 'picid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=album&picid=$id&cid=$cid";

			$note_type = 'piccomment';
			$note = 'pic_comment';
			$note_values = array('url'=>$n_url);
			$q_note = 'pic_comment_reply';
			$q_values = array('url'=>$n_url);

			$msg = 'do_success';
			$magvalues = array();

			break;
		case 'blogid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=blog&id=$id&cid=$cid";

			$note_type = 'blogcomment';
			$note = 'blog_comment';
			$note_values = array('url'=>$n_url, 'subject'=>$blog['subject']);
			$q_note = 'blog_comment_reply';
			$q_values = array('url'=>$n_url);

			$msg = 'do_success';
			$magvalues = array();

			break;
		case 'sid':
			$n_url = "home.php?mod=space&uid=$tospace[uid]&do=share&id=$id&cid=$cid";

			$note_type = 'sharecomment';
			$note = 'share_comment';
			$note_values = array('url'=>$n_url);
			$q_note = 'share_comment_reply';
			$q_values = array('url'=>$n_url);

			$msg = 'do_success';
			$magvalues = array();

			break;
	}

	if(empty($comment)) {

		if($tospace['uid'] != $_G['uid']) {
			if(ckprivacy('comment', 'feed')) {
				require_once libfile('function/feed');
				$fs['title_data']['hash_data'] = "{$idtype}{$id}";
				feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links'], $fs['target_ids'], $fs['friend']);
			}

			$note_values['from_id'] = $id;
			$note_values['from_idtype'] = $idtype;
			$note_values['url'] .= "&goto=new#comment_{$cid}_li";

			notification_add($tospace['uid'], $note_type, $note, $note_values);
		}

	} elseif($comment['authorid'] != $_G['uid']) {
		notification_add($comment['authorid'], $note_type, $q_note, $q_values);
	}

	if($comment_status == 1) {
		updatemoderate($idtype.'_cid', $cid);
		manage_addnotify('verifycommontes');
	}
	if($stattype) {
		include_once libfile('function/stat');
		updatestat($stattype);
	}
	if($tospace['uid'] != $_G['uid']) {
		$needle = $id;
		if($idtype != 'uid') {
			$needle = $idtype.$id;
		} else {
			$needle = $tospace['uid'];
		}
		updatecreditbyaction($action, 0, array(), $needle);
		if($becomment) {
			if($idtype == 'uid') {
				$needle = $_G['uid'];
			}
			updatecreditbyaction($becomment, $tospace['uid'], array(), $needle);
		}
	}

	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	$magvalues['cid'] = $cid;

	return array('cid' => $cid, 'msg' => $msg, 'magvalues' => $magvalues);
}

?>