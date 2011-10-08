<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_doing.php 22037 2011-04-20 08:34:44Z cnteacher $
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

		// bluelovers
		'lastpost' => $_G['timestamp'],
		// bluelovers
	);
	$newdoid = DB::insert('home_doing', $setarr, 1);

	$setarr = array('recentnote'=>$message, 'spacenote'=>$message);
	$credit = $experience = 0;
	$extrasql = array('doings' => 1);

	updatecreditbyaction('doing', 0, $extrasql);

	DB::update('common_member_field_home', $setarr, "uid='$_G[uid]'");

	// bluelovers
	/**
	 * 不使用將記錄取代簽名的功能
	 */
	unset($_POST['to_signhtml']);
	// bluelovers

	if($_POST['to_signhtml'] && $_G['group']['maxsigsize']) {
		$signhtml = cutstr(strip_tags($message), $_G['group']['maxsigsize']);
		DB::update('common_member_field_forum', array('sightml'=>$signhtml), "uid='$_G[uid]'");
	}

	if(ckprivacy('doing', 'feed') && $doing_status == '0') {
		$feedarr = array(
			'appid' => '',
			'icon' => 'doing',
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'title_template' => array('feed', 'feed_doing_title'),
			/*
			'title_data' => daddslashes(serialize(dstripslashes(array('message'=>$message)))),
			*/
			'title_data' => array('message'=>$message),
			'body_template' => '',
			'body_data' => '',
			'id' => $newdoid,
			'idtype' => 'doid'
		);
		/*
		DB::insert('home_feed', $feedarr);
		*/

		// bluelovers
		// Fatal error: Call to undefined function feed_add() in source\include\spacecp\spacecp_doing.php on line 90
		require_once libfile('function/feed');

		feed_add(
			$feedarr['icon'],

			$feedarr['title_template'],
			$feedarr['title_data'],

			$feedarr['body_template'],
			$feedarr['body_data'],

			'', '', '',

			'', '',

			$feedarr['appid'],
			0,

			$feedarr['id'],
			$feedarr['idtype'],
			$feedarr['uid'],
			$feedarr['username']
		);
		// bluelovers
	}
	if($doing_status == '1') {
		updatemoderate('doid', $newdoid);
		manage_addnotify('verifydoing');
	}

	// 統計
	require_once libfile('function/stat');
	updatestat('doing');
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	if(!empty($_G['gp_fromcard'])) {
		showmessage($message.lang('spacecp','card_update_doing'));
	} else {
		showmessage('do_success', $_G['gp_referer'] == -1 ? null : dreferer(), array('doid' => $newdoid), $_G['gp_spacenote'] ? array('showmsg' => false):array('header' => true));
	}

} elseif (submitcheck('commentsubmit')) {

	if(!checkperm('allowdoing')) {
		showmessage('no_privilege_doing_comment');
	}
	cknewuser();

	// 判斷是否操作太快
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

		// bluelover
		// 最頂層的 doing
		$top_updo = $updo;
		// bluelovers
	}

	// bluelovers
	// 最頂層的 doing
	if (empty($top_updo)) {
		$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid='{$updo[doid]}'");
		$top_updo = DB::fetch($query);
	}
	// bluelovers

	if(empty($updo)) {
		showmessage('docomment_error');
	} else {
		// 黑名單
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

		// bluelovers
		,
		// 最後被回覆的時間
		'lastpost' => $_G['timestamp'],
		// bluelovers
	);

	if($updo['grade'] >= 3) {
		$setarr['upid'] = $updo['upid'];
	}

	$newid = DB::insert('home_docomment', $setarr, 1);

	// 更新回複數
	DB::query("UPDATE ".DB::table('home_doing')."
		SET replynum=replynum+1
		, lastpost='{$_G[timestamp]}'
		WHERE doid='$updo[doid]'");

	// bluelovers
	if ($updo['id']) {
		DB::query("UPDATE ".DB::table('home_docomment')." SET lastpost='{$_G[timestamp]}' WHERE id='$updo[id]'");
	}
	// bluelovers

	if($updo['uid'] != $_G['uid']) {
		notification_add($updo['uid'], 'doing', 'doing_reply', array(
			'url'=>"home.php?mod=space&uid=$updo[uid]&do=doing&doid=$updo[doid]&highlight=$newid",
			'from_id'=>$updo['doid'],
			'from_idtype'=>'doid'));
		// 獎勵積分
		updatecreditbyaction('comment', 0, array(), 'doing'.$updo['doid']);
	}

	// bluelovers
	$top_updo = daddslashes($top_updo);

	if(ckprivacy('doing', 'feed')) {

		$feed_hash_data = 'doid'.$top_updo['doid'];

		$feedarr = array(
			'appid' => '',
			'icon' => 'doing',
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => $_G['timestamp'],
			'title_template' => array('feed', 'feed_reply_doing_title'),
			'title_data' => array(
				'touser' => "<a href=\"home.php?mod=space&uid=$top_updo[uid]\">$top_updo[username]</a>",
				'message' => $top_updo['message'],

				'url' => "home.php?mod=space&uid=$top_updo[uid]&do=doing&doid=$top_updo[doid]",

				'hash_data' => $feed_hash_data,
			),
			'body_template' => array('feed', 'feed_reply_doing_title_message'),
			'body_data' => array(
				'touser' => "<a href=\"home.php?mod=space&uid=$top_updo[uid]\">$top_updo[username]</a>",
				'message' => $top_updo['message'],
			),
			'id' => $updo['doid'],
			'idtype' => 'doid'
		);
		require_once libfile('function/feed');

		feed_add(
			$feedarr['icon'],

			$feedarr['title_template'],
			$feedarr['title_data'],

			$feedarr['body_template'],
			$feedarr['body_data'],

			'', '', '',

			'', '',

			$feedarr['appid'],
			0,

			$feedarr['id'],
			$feedarr['idtype'],
			$feedarr['uid'],
			$feedarr['username']
		);
	}
	// bluelovers

	// bluelovers
	// 改良當回覆紀錄時可以同時提醒該紀錄最原始的發表者
	if($top_updo['uid'] != $updo['uid'] && $top_updo['uid'] != $_G['uid']) {
		notification_add($top_updo['uid'], 'doing', 'doing_reply', array(
			'url'=>"home.php?mod=space&uid=$top_updo[uid]&do=doing&doid=$top_updo[doid]&highlight=$newid",
			'from_id'=>$top_updo['doid'],
			'from_idtype'=>'doid'));
	}
	// bluelovers

	include_once libfile('function/stat');
	updatestat('docomment');
	DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
	showmessage('do_success', dreferer(), array('doid' => $updo['doid']));
}

// 刪除
if($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		if($id) {
			$allowmanage = checkperm('managedoing');
			$query = DB::query("SELECT dc.*, d.uid as duid FROM ".DB::table('home_docomment')." dc, ".DB::table('home_doing')." d WHERE dc.id='$id' AND dc.doid=d.doid");
			if($value = DB::fetch($query)) {
				if($allowmanage || $value['uid'] == $_G['uid'] || $value['duid'] == $_G['uid'] ) {
					// 更新內容
					DB::update('home_docomment', array('uid'=>0, 'username'=>'', 'message'=>''), "id='$id'");
					if($value['uid'] != $_G['uid'] && $value['duid'] != $_G['uid']) {
						// 扣除積分
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

	include_once libfile('class/tree');
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