<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_doing.php 17282 2010-09-28 09:04:15Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$id = empty($_GET['id'])?0:intval($_GET['id']);

// bluelovers
//discuz_core::instance()->debug_log(CURSCRIPT, $_GET);

/**
 * 回應的最大字數
 * @var int
 **/
$message_strlen = 255;

/**
 * 回應的最小字數
 * @var int
 **/
$message_strlen_min = 3;

$tree_level = 3;

// bluelovers

if(submitcheck('addsubmit')) {

	$add_doing = 1;
	if(!checkperm('allowdoing')) {
		showmessage('no_privilege');
	}

	//實名認證
	ckrealname('doing');

	//視頻認證
	ckvideophoto('doing');

	//新用戶見習
	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}

	// bluelovers
	$_POST['message'] = scotext::lf($_POST['message']);
	// bluelovers

//	$message = getstr($_POST['message'], 200, 1, 1, 1);
	$message = getstr($_POST['message'], $message_strlen, 1, 1, 1);
	$message = preg_replace("/\<br.*?\>/i", ' ', $message);
//	if(strlen($message) < 1) {
//		showmessage('should_write_that');
//	}

	// bluelovers
	$message = trim($message, '　');

	$_message = trim(preg_replace("/^@[^\s]*[\s　]*/i", '', $message));
	if(
		strlen($message) < $message_strlen_min
		|| strlen($_message) < $message_strlen_min
	) {
		showmessage('should_write_that');
	}

	$message = trim(preg_replace("/(@[^\s]*)[\s　]*/i", '\\1 ', $message), '　');
	// bluelovers

	if(censormod($message) || $_G['group']['allowdoingmod']) {
		$doing_status = 1;
	} else {
		$doing_status = 0;
	}

	if($add_doing) {
		$setarr = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'message' => $message,
			'ip' => $_G['clientip'],
			'status' => $doing_status,
		);
		$newdoid = DB::insert('home_doing', $setarr, 1);
	}

	$setarr = array('recentnote'=>$message, 'spacenote'=>$message);
	$credit = $experience = 0;
	$extrasql = array('doings' => 1);

	updatecreditbyaction('doing', 0, $extrasql);

	DB::update('common_member_field_home', $setarr, "uid='$_G[uid]'");

	if($_POST['to_signhtml'] && $_G['group']['maxsigsize']) {
		$message = cutstr($message, $_G['group']['maxsigsize']);
		DB::update('common_member_field_forum', array('sightml'=>$message), "uid='$_G[uid]'");
	}

	if($add_doing && ckprivacy('doing', 'feed') && $doing_status == '0') {
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

	//統計
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
		showmessage('no_privilege');
	}

	//實名認證
	ckrealname('doing');

	//新用戶見習
	cknewuser();

	//判斷是否操作太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime));
	}

	// bluelovers
	$_POST['message'] = scotext::lf($_POST['message']);
	// bluelovers

//	$message = getstr($_POST['message'], 200, 1, 1, 1);
	$message = getstr($_POST['message'], $message_strlen, 1, 1, 1);
	$message = preg_replace("/\<br.*?\>/i", ' ', $message);

//	if(strlen($message) < 1) {
//		showmessage('should_write_that');
//	}

	// bluelovers
	$message = trim($message, '　');

	$_message = trim(preg_replace("/@[^\s]*[\s　]*/i", '', $message), '　');

//	discuz_core::instance()->debug_log('should_write_that', array($message, $_message));

	if(
		strlen($message) < $message_strlen_min
		|| strlen($_message) < $message_strlen_min
	) {
		showmessage('should_write_that');
	}
	$message = trim(preg_replace("/(@[^\s]*)[\s　]*/i", '\\1 ', $message), '　');
	// bluelovers

	$message = censor($message);

	$updo = array();

	// bluelovers
	$topupdo = array();
	// bluelovers

	if($id) {
		$query = DB::query("SELECT * FROM ".DB::table('home_docomment')." WHERE id='$id'");
		$updo = DB::fetch($query);
	}
	if(empty($updo) && $doid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid='$doid'");
		$updo = DB::fetch($query);

	// bluelovers
	//TODO: fix here - docomment
		$topupdo = $updo;
	} elseif ($id && $updo['upid']) {
		$topupdo = DB::query_first("SELECT * FROM ".DB::table('home_docomment')." WHERE id='$updo[upid]'");
	// bluelovers

	}
	if(empty($updo)) {
		showmessage('docomment_error');
	} else {
		//黑名單
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

	// bluelovers
	if ($updo['uid'] == $_G['uid'] && $updo['grade']) {
		// 如果回應的評論是自己的則保持在同級
		$setarr['upid'] = $updo['upid'];
		$setarr['grade'] = $updo['grade'];
	} elseif (!empty($topupdo) && $topupdo['uid'] == $_G['uid'] && $updo['grade']) {
		$setarr['upid'] = $topupdo['upid'];
		$setarr['grade'] = $updo['grade'];
	}
	// bluelovers

	//最多層級
	if($updo['grade'] >= $tree_level) {
		$setarr['upid'] = $updo['upid'];
		//更母一個級別
	}

	$newid = DB::insert('home_docomment', $setarr, 1);

	//更新回複數
	DB::query("UPDATE ".DB::table('home_doing')." SET replynum=replynum+1 WHERE doid='$updo[doid]'");

	//通知
	if($updo['uid'] != $_G['uid']) {
		notification_add($updo['uid'], 'doing', 'doing_reply', array(
			'url'=>"home.php?mod=space&uid=$updo[uid]&do=doing&doid=$updo[doid]&highlight=$newid",
			'from_id'=>$updo['doid'],
			'from_idtype'=>'doid'));

		//獎勵積分
		updatecreditbyaction('comment', 0, array(), 'doing'.$updo['doid']);
	}

	//統計
	include_once libfile('function/stat');
	updatestat('docomment');
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	showmessage('do_success', dreferer(), array('doid' => $updo['doid']));
}

//刪除
if($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		if($id) {
			$allowmanage = checkperm('managedoing');
			$query = DB::query("SELECT dc.*, d.uid as duid FROM ".DB::table('home_docomment')." dc, ".DB::table('home_doing')." d WHERE dc.id='$id' AND dc.doid=d.doid");
			if($value = DB::fetch($query)) {
				if($allowmanage || $value['uid'] == $_G['uid'] || $value['duid'] == $_G['uid'] ) {
					//更新內容
					DB::update('home_docomment', array('uid'=>0, 'username'=>'', 'message'=>''), "id='$id'");
					if($value['uid'] != $_G['uid'] && $value['duid'] != $_G['uid']) {
						//扣除積分
						batchupdatecredit('comment', $value['uid'], array(), -1);
					}
					DB::query("UPDATE ".DB::table('home_doing')." SET replynum=replynum-'1' WHERE doid='$doid'");
				}
			}
		} else {
			require_once libfile('function/delete');
			deletedoings(array($doid));
		}

		header('location: '.dreferer());
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

// bluelovers
} elseif ($_GET['op'] == 'docomment') {
	$query = DB::fetch_first("SELECT dc.* FROM ".DB::table('home_docomment')." dc WHERE dc.id='$id' AND dc.doid='$doid'");
//	!$query && $query = DB::fetch_first("SELECT dc.* FROM ".DB::table('home_doing')." dc WHERE dc.doid='$doid'");

	if (empty($_G['gp_message']) && DB::result_first("SELECT count(*) FROM ".DB::table('home_docomment')." dc WHERE dc.doid='$doid' AND uid NOT IN ('{$_G[uid]}', '{$query[uid]}')") && $query['uid'] != $_G['uid'] && $query['username']) {
		$_G['gp_message'] = '@'.dhtmlspecialchars($query['username']).' ';
	} else {
		$_G['gp_message'] = !empty($_G['gp_message']) ? dhtmlspecialchars($_G['gp_message']) : '';
	}

//	dexit($_G['gp_message']);

// bluelovers

}

include template('home/spacecp_doing');

?>