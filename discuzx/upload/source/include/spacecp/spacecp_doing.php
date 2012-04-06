<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_doing.php 27232 2012-01-11 10:00:04Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$id = empty($_GET['id'])?0:intval($_GET['id']);

if(submitcheck('addsubmit')) {

	if(!checkperm('allowdoing')) {
		showmessage('no_privilege_doing');
	}

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}

	$message = getstr($_POST['message'], 200, 1, 1, 1);
	$message = preg_replace("/\<br.*?\>/i", ' ', $message);
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}

	$message = censor($message, NULL, TRUE);
	if(is_array($message) && $message['message']) {
		showmessage('do_success', dreferer(), array('message'=>$message['message']));
	}

	if(censormod($message) || $_G['group']['allowdoingmod']) {
		$doing_status = 1;
	} else {
		$doing_status = 0;
	}


	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'ip' => $_G['clientip'],
		'status' => $doing_status,
	);
	$newdoid = DB::insert('home_doing', $setarr, 1);

	$setarr = array('recentnote'=>$message, 'spacenote'=>$message);
	$credit = $experience = 0;
	$extrasql = array('doings' => 1);

	updatecreditbyaction('doing', 0, $extrasql);

	DB::update('common_member_field_home', $setarr, "uid='$_G[uid]'");

	if($_POST['to_signhtml'] && $_G['group']['maxsigsize']) {
		if($_G['group']['maxsigsize'] < 200) {
			$signhtml = getstr($message, $_G['group']['maxsigsize'], 0, 0, 1);
			$signhtml = preg_replace("/\<br.*?\>/i", ' ', $signhtml);
		} else {
			$signhtml = $message;
		}
		DB::update('common_member_field_forum', array('sightml'=>$signhtml), "uid='$_G[uid]'");
	}

	if(ckprivacy('doing', 'feed') && $doing_status == '0') {
		$feedarr = array(
			'appid' => '',
			'icon' => 'doing',
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'title_template' => lang('feed', 'feed_doing_title'),
			'title_data' => daddslashes(serialize(dstripslashes(array('message'=>$message)))),
			'body_template' => '',
			'body_data' => '',
			'id' => $newdoid,
			'idtype' => 'doid'
		);
		DB::insert('home_feed', $feedarr);
	}
	if($doing_status == '1') {
		updatemoderate('doid', $newdoid);
		manage_addnotify('verifydoing');
	}

	require_once libfile('function/stat');
	updatestat('doing');
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	if(!empty($_G['gp_fromcard'])) {
		showmessage($message.lang('spacecp','card_update_doing'));
	} else {
		showmessage('do_success', dreferer(), array('doid' => $newdoid), $_G['gp_spacenote'] ? array('showmsg' => false):array('header' => true));
	}

} elseif (submitcheck('commentsubmit')) {

	if(!checkperm('allowdoing')) {
		showmessage('no_privilege_doing_comment');
	}
	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}

	$message = getstr($_POST['message'], 200, 1, 1, 1);
	$message = preg_replace("/\<br.*?\>/i", ' ', $message);
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}
	$message = censor($message);


	$updo = array();
	if($id) {
		$query = DB::query("SELECT * FROM ".DB::table('home_docomment')." WHERE id='$id'");
		$updo = DB::fetch($query);
	}
	if(empty($updo) && $doid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid='$doid'");
		$updo = DB::fetch($query);
	}
	if(empty($updo)) {
		showmessage('docomment_error');
	} else {
		if(isblacklist($updo['uid'])) {
			showmessage('is_blacklist');
		}
	}

	$updo['id'] = intval($updo['id']);
	$updo['grade'] = intval($updo['grade']);

	$setarr = array(
		'doid' => $updo['doid'],
		'upid' => $updo['id'],
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'ip' => $_G['clientip'],
		'grade' => $updo['grade']+1
	);

	if($updo['grade'] >= 3) {
		$setarr['upid'] = $updo['upid'];
	}

	$newid = DB::insert('home_docomment', $setarr, 1);

	DB::query("UPDATE ".DB::table('home_doing')." SET replynum=replynum+1 WHERE doid='$updo[doid]'");

	if($updo['uid'] != $_G['uid']) {
		notification_add($updo['uid'], 'doing', 'doing_reply', array(
			'url'=>"home.php?mod=space&uid=$updo[uid]&do=doing&doid=$updo[doid]&highlight=$newid",
			'from_id'=>$updo['doid'],
			'from_idtype'=>'doid'));
		updatecreditbyaction('comment', 0, array(), 'doing'.$updo['doid']);
	}

	include_once libfile('function/stat');
	updatestat('docomment');
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	showmessage('do_success', dreferer(), array('doid' => $updo['doid']));
}

if($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		if($id) {
			$allowmanage = checkperm('managedoing');
			$query = DB::query("SELECT dc.*, d.uid as duid FROM ".DB::table('home_docomment')." dc, ".DB::table('home_doing')." d WHERE dc.id='$id' AND dc.doid=d.doid");
			if($value = DB::fetch($query)) {
				if($allowmanage || $value['uid'] == $_G['uid'] || $value['duid'] == $_G['uid'] ) {
					DB::update('home_docomment', array('uid'=>0, 'username'=>'', 'message'=>''), "id='$id'");
					if($value['uid'] != $_G['uid'] && $value['duid'] != $_G['uid']) {
						batchupdatecredit('comment', $value['uid'], array(), -1);
					}
					DB::query("UPDATE ".DB::table('home_doing')." SET replynum=replynum-'1' WHERE doid='$doid'");
				}
			}
		} else {
			require_once libfile('function/delete');
			deletedoings(array($doid));
		}

		dheader('location: '.dreferer());
		exit();
	}

} elseif ($_GET['op'] == 'getcomment') {

	include_once(DISCUZ_ROOT.'./source/class/class_tree.php');
	$tree = new tree();

	$list = array();
	$highlight = 0;
	$count = 0;

	if(empty($_GET['close'])) {
		$query = DB::query("SELECT * FROM ".DB::table('home_docomment')." WHERE doid='$doid' ORDER BY dateline");
		while ($value = DB::fetch($query)) {
			$tree->setNode($value['id'], $value['upid'], $value);
			$count++;
			if($value['authorid'] == $space['uid']) $highlight = $value['id'];
		}
	}

	if($count) {
		$values = $tree->getChilds();
		foreach ($values as $key => $vid) {
			$one = $tree->getValue($vid);
			$one['layer'] = $tree->getLayer($vid) * 2;
			$one['style'] = "padding-left:{$one['layer']}em;";
			if($one['layer'] > 0){
				if($one['layer']%3 == 2) {
					$one['class'] = ' dtls';
				} else {
					$one['class'] = ' dtll';
				}
			}
			if($one['id'] == $highlight && $one['uid'] == $space['uid']) {
				$one['style'] .= 'color:#F60;';
			}
			$list[] = $one;
		}
	}
} elseif ($_GET['op'] == 'spacenote') {
	space_merge($space, 'field_home');
}

include template('home/spacecp_doing');

?>