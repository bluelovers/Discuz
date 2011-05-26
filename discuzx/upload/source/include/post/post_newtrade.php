<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newtrade.php 20885 2011-03-07 07:36:57Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if($special != 2 || !submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {
	showmessage('submitcheck_error', NULL);
}

if(!$_G['group']['allowposttrade']) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessage('post_forum_newthread_nopermission', NULL);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if($post_invalid = checkpost($subject, $message, 1)) {
	showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
}

if($time_left = checkflood()) {
	showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl'], 'time_left' => $time_left));
} elseif(checkmaxpostsperhour()) {
		showmessage('post_flood_ctrl_posts_per_hour', '', array('posts_per_hour' => $_G['group']['maxpostsperhour']));
	}

$item_price = floatval($_G['gp_item_price']);
$item_credit = intval($_G['gp_item_credit']);
$_G['gp_item_name'] = censor($_G['gp_item_name']);
if(!trim($_G['gp_item_name'])) {
	showmessage('trade_please_name');
} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
	showmessage('trade_price_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
	showmessage('trade_credit_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
	showmessage('trade_price_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
	showmessage('trade_credit_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
} elseif($item_price <= 0 && $item_credit <= 0) {
	showmessage('trade_pricecredit_need');
} elseif($_G['gp_item_number'] < 1) {
	showmessage('tread_please_number');
}

if(!empty($_FILES['tradeattach']['tmp_name'][0])) {
	$_FILES['attach'] = array_merge_recursive((array)$_FILES['attach'], $_FILES['tradeattach']);
}

if(($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && is_array($_FILES['attach'])) {
	foreach($_FILES['attach']['name'] as $attachname) {
		if($attachname != '') {
			checklowerlimit('postattach', 0, 1, $_G['forum']['fid']);
			break;
		}
	}
}

$_G['gp_save'] = $_G['uid'] ? $_G['gp_save'] : 0;
$typeid = isset($typeid) ? $typeid : 0;
$displayorder = $modnewthreads ? -2 : (($_G['forum']['ismoderator'] && !empty($_G['gp_sticktopic'])) ? 1 : (empty($_G['gp_save']) ? 0 : -4));
if($displayorder == -2) {
	DB::update('forum_forum', array('modworks' => '1'), "fid='{$_G['fid']}'");
} elseif($displayorder == -4) {
	$_G['gp_addfeed'] = 0;
}
$digest = ($_G['forum']['ismoderator'] && !empty($addtodigest)) ? 1 : 0;
$readperm = $_G['group']['allowsetreadperm'] ? $readperm : 0;
$isanonymous = $_G['gp_isanonymous'] && $_G['group']['allowanonymous'] ? 1 : 0;

$author = !$isanonymous ? $_G['username'] : '';

$moderated = $digest || $displayorder > 0 ? 1 : 0;
$isgroup = $_G['forum']['status'] == 3 ? 1 : 0;
DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, readperm, price, typeid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, replies, status, isgroup)
	VALUES ('$_G[fid]', '0', '$readperm', '$price', '$typeid', '$author', '$_G[uid]', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author', '$displayorder', '$digest', '$special', '$attachment', '$moderated', '1', '$thread[status]', '$isgroup')");
$tid = DB::insert_id();
useractionlog($_G['uid'], 'tid');

if($moderated) {
	updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
	updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
}

$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
$parseurloff = !empty($_G['gp_parseurloff']);
$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;
$attentionon = empty($_G['gp_attention_add']) ? 0 : 1;

$pinvisible = $modnewthreads ? -2 : (empty($_G['gp_save']) ? 0 : -3);

$tagstr = addthreadtag($_G['gp_tags'], $tid);
insertpost(array(
	'fid' => $_G['fid'],
	'tid' => $tid,
	'first' => '1',
	'author' => $_G['username'],
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'message' => '',
	'useip' => $_G['clientip'],
	'invisible' => $pinvisible,
	'anonymous' => $isanonymous,
	'usesig' => $_G['gp_usesig'],
	'htmlon' => $htmlon,
	'bbcodeoff' => $bbcodeoff,
	'smileyoff' => $smileyoff,
	'parseurloff' => $parseurloff,
	'attachment' => '0',
	'tags' => $tagstr,
));

$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
$pid = insertpost(array(
	'fid' => $_G['fid'],
	'tid' => $tid,
	'first' => '0',
	'author' => $_G['username'],
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'message' => $message,
	'useip' => $_G['clientip'],
	'invisible' => $pinvisible,
	'anonymous' => $isanonymous,
	'usesig' => $_G['gp_usesig'],
	'htmlon' => $htmlon,
	'bbcodeoff' => $bbcodeoff,
	'smileyoff' => $smileyoff,
	'parseurloff' => $parseurloff,
	'attachment' => $attachment,
	'tags' => $tagstr,
	'status' => (defined('IN_MOBILE') ? 8 : 0)
));

($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $_G['gp_tradeaid']) && updateattach($displayorder == -4 || $modnewthreads, $tid, $pid, $_G['gp_attachnew']);

require_once libfile('function/trade');
trade_create(array(
	'tid' => $tid,
	'pid' => $pid,
	'aid' => $_G['gp_tradeaid'],
	'item_expiration' => $_G['gp_item_expiration'],
	'thread' => $thread,
	'discuz_uid' => $_G['uid'],
	'author' => $author,
	'seller' => empty($_G['gp_paymethod']) && $_G['gp_seller'] ? dhtmlspecialchars(trim($_G['gp_seller'])) : '',
	'tenpayaccount' => $_G['gp_tenpay_account'],
	'item_name' => $_G['gp_item_name'],
	'item_price' => $_G['gp_item_price'],
	'item_number' => $_G['gp_item_number'],
	'item_quality' => $_G['gp_item_quality'],
	'item_locus' => $_G['gp_item_locus'],
	'transport' => $_G['gp_transport'],
	'postage_mail' => $_G['gp_postage_mail'],
	'postage_express' => $_G['gp_postage_express'],
	'postage_ems' => $_G['gp_postage_ems'],
	'item_type' => $_G['gp_item_type'],
	'item_costprice' => $_G['gp_item_costprice'],
	'item_credit' => $_G['gp_item_credit'],
	'item_costcredit' => $_G['gp_item_costcredit']
));

if(!empty($_G['gp_tradeaid'])) {
	convertunusedattach($_G['gp_tradeaid'], $tid, $pid);
}

$param = array('fid' => $_G['fid'], 'tid' => $tid, 'pid' => $pid);

include_once libfile('function/stat');
updatestat($isgroup ? 'groupthread' : 'trade');

dsetcookie('clearUserdata', 'forum');

if($modnewthreads) {

	updatemoderate('tid', $tid);
	DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
	manage_addnotify('verifythread');
	showmessage('post_newthread_mod_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);

} else {
	$feed = array();
	if(!empty($_G['gp_addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
		$feed['icon'] = 'goods';
		$feed['title_template'] = 'feed_thread_goods_title';
		if($_G['gp_item_price'] > 0) {
			if($_G['setting']['creditstransextra'][5] != -1 && $_G['gp_item_credit']) {
				$feed['body_template'] = 'feed_thread_goods_message_1';
			} else {
				$feed['body_template'] = 'feed_thread_goods_message_2';
			}
		} else {
			$feed['body_template'] = 'feed_thread_goods_message_3';
		}
		$feed['body_data'] = array(
			'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid\">$_G[gp_item_name]</a>",
			'itemprice'=> $_G['gp_item_price'],
			'itemcredit'=> $_G['gp_item_credit'],
			'creditunit'=> $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title']
		);
		if($_G['gp_tradeaid']) {
			$feed['images'] = array(getforumimg($_G['gp_tradeaid']));
			$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
		}
		if($_G['gp_tradeaid']) {
			$attachment = DB::fetch_first("SELECT * FROM ".DB::table(getattachtablebytid($tid))." WHERE aid='$_G[gp_tradeaid]'");
			if(in_array($attachment['filetype'], array('image/gif', 'image/jpeg', 'image/png'))) {
				$imgurl = $_G['setting']['attachurl'].'forum/'.($attachment['thumb'] && $attachment['filetype'] != 'image/gif' ? getimgthumbname($attachment['attachment']) : $attachment['attachment']);
				$feed['images'][] = $attachment['attachment'] ? $imgurl : '';
				$feed['image_links'][] = $attachment['attachment'] ? "forum.php?mod=viewthread&tid=$tid" : '';
			}
		}

		$feed['title_data']['hash_data'] = "tid{$tid}";
		$feed['id'] = $tid;
		$feed['idtype'] = 'tid';
		postfeed($feed);
	}

	if($displayorder != -4) {
		if($digest) {
			updatepostcredits('+',  $_G['uid'], 'digest', $_G['fid']);
		}
		updatepostcredits('+',  $_G['uid'], 'post', $_G['fid']);
		if($isgroup) {
			DB::query("UPDATE ".DB::table('forum_groupuser')." SET threads=threads+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");
		}

		$lastpost = "$tid\t$subject\t$_G[timestamp]\t$author";
		DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+2, todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		if($_G['forum']['type'] == 'sub') {
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='".$_G['forum']['fup']."'", 'UNBUFFERED');
		}
	}

	if($_G['forum']['status'] == 3) {
		require_once libfile('function/group');
		updateactivity($_G['fid'], 0);
		require_once libfile('function/grouplog');
		updategroupcreditlog($_G['fid'], $_G['uid']);
	}

	if(!empty($_G['gp_continueadd'])) {
		dheader("location: forum.php?mod=post&action=reply&fid=$_G[fid]&tid=$tid&addtrade=yes");
	} else {
		showmessage('post_newthread_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);
	}

}

?>