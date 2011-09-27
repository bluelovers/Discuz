<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-06 17:09
 *	Filename: mod.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//可以使用的模版名,添加模版请修改此变量
//'{文件名}' => '中文名称'
$tltpls = array(
	'tl_pic' => pl('default_tpl'),
);


$allowgroups = unserialize($_G['cache']['plugin']['threadlink']['allowgroups']);
$allowaction = array(
	'addbase',
	'addlink',
	'getthreadlist',
	'delete',
	'edit',
);
$action = in_array($_G['gp_action'], $allowaction) ? $_G['gp_action'] : '';
$tid = intval($_G['gp_tid']);

//权限判断
if(!in_array($_G['groupid'], $allowgroups) || empty($action)) {
	showmessage(pl('no_perm'));
}




include libfile('function/forum');

//设置为聚合贴
if($action == 'addbase') {
	if(empty($tid)) {
		showmessage(pl('no_tid'));
	} else {
		$thread = get_thread_by_tid($tid);
	}

	if(empty($thread)) {
		showmessage(pl('no_tid'));
	}
	if(!submitcheck('addbasesubmit')) {
		include template('threadlink:addbase');
		exit;
	} else {
		$tltpl = trim($_G['gp_tltpl']);
		if(!in_array($tltpl, array_keys($tltpls))) {
			showmessage(pl('no_template'));
		}

		$_G['gp_maxrow'] = intval($_G['gp_maxrow']);
		$maxrow = $_G['gp_maxrow'] ? $_G['gp_maxrow'] : 20;
		$summarylength = intval($summarylength);
		$picwidth = intval($_G['gp_picwidth']);
		$picwidth = $picwidth > 0 ? $picwidth : 100;
		$picheight = intval($_G['gp_picheight']);
		$picheight = $picheight > 0 ? $picheight : 100;
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_base')." WHERE tid='$tid'")) {
			showmessage(pl('is_base'));
		}
		//入库
		DB::query("INSERT INTO ".DB::table('threadlink_base')." (tid,pid,tltpl,maxrow,subject,dateline,summarylength,picwidth,picheight) VALUES ('$tid', '{$thread[pid]}', '$tltpl', '$maxrow', '".daddslashes($thread['subject'])."','{$_G[timestamp]}','{$summarylength}','$picwidth','$picheight')");
		$msg = pl('addbase_success');
		$alert_info = pl('tip');
		$extrajs = <<<JS
<script type="text/javascript" reload="1">hideWindow('threadlink');showDialog('$msg', 'right', '$alert_info', 'relload()', true, null, '', '', '', 3);function relload(){window.location.reload();}</script>
JS;
		showmessage('', '', '', array('extrajs' => $extrajs));
	}


//聚合到帖子内
} elseif($action == 'addlink') {
	if(!submitcheck('addlinksubmit')) {
		include_once libfile('function/post');
		$thread_count = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base'));
		$thread_count['summarylength'] = $thread_count['summarylength'] ? $thread_count['summarylength'] : 300;
		$threads = array();
		$pid = intval($_G['gp_pid']);
		if($pid) {
			$linkthread = get_post_by_pid($pid);
			if(empty($linkthread)) {
				showmessage(pl('no_pid'));
			}
			$linkthread_src = get_thread_by_tid($linkthread['tid']);
			$linkthread['message'] = messagecutstr($linkthread['message'], $thread_count['summarylength']);
			//获取图片
			if($linkthread['attachment'] == 2) {
				$attach = DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='{$linkthread[pid]}'");
				$attach_query = DB::query("SELECT * FROM ".DB::table('forum_attachment_'.$attach['tableid'])." WHERE pid='{$linkthread[pid]}' LIMIT 10");
				while($attach = DB::fetch($attach_query)) {
					$attach['attachment'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['siteurl'].$_G['setting']['attachurl']).'forum/'.$attach['attachment'];
					$attachs[] = $attach;
				}
			}
		} else {
			showmessage(pl('no_pid'));
		}
		if($thread_count) {
			$thread_query = DB::query("SELECT * FROM ".DB::table('threadlink_base')." ORDER BY dateline DESC LIMIT 20");
			while($thread = DB::fetch($thread_query)) {
				//可以聚合进去的帖子列表
				$threads[] = $thread;
			}

			include template('threadlink:addlink');
		} else {
			showmessage(pl('no_tid'));
		}
	} else {
		empty($tid) && showmessage(pl('no_tid'));
		$thread_base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='$tid'");///////////////////////////
		$thread_base['summarylength'] = $thread_base['summarylength'] ? $thread_base['summarylength']  : 300;
		$pid = intval($_G['gp_pid']);
		$subject = daddslashes(cutstr(trim($_G['gp_subject']), 80));
		$message = daddslashes(cutstr(trim($_G['gp_message']), $thread_base['summarylength']));
		$url = daddslashes(trim($_G['gp_url']));
		$pic = daddslashes(trim($_G['gp_pic']));
		$aid = intval($_G['gp_aid']);

		empty($pid) && showmessage(pl('no_pid'));
		empty($subject) && showmessage(pl('no_subject'));
		empty($message) && showmessage(pl('no_message'));
		$aid = empty($pic) ? $aid : 0;

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('threadlink_link')." WHERE tid='{$tid}' AND pid='{$pid}'")) {
			showmessage(pl('exists_already'));
		} else {
			$linkid = DB::insert('threadlink_link', 
				array('tid' => $tid, 'pid' => $pid, 'subject' => $subject, 'message' => $message, 'url' => $url, 'pic' => $pic, 'aid' => $aid),
				true
			);
			if($linkid) {
				$msg = pl('addlink_success');
				$alert_info = pl('tip');
				$extrajs = <<<JS
<script type="text/javascript" reload="1">hideWindow('threadlink');showDialog('$msg', 'right', '$alert_info', 'relload()', true, null, '', '', '', 3);function relload(){window.location.reload();}</script>
JS;
				showmessage('', '', '', array('extrajs' => $extrajs));
			} else {
				showmessage(pl('unknown_error'));
			}

		}
	}
} elseif($action == 'delete') {
	$lid = intval($_G['gp_lid']);
	$link = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");

	if($link) {
		if(!submitcheck('deletesubmit')) {
			include template('threadlink:addbase');
			exit;
		} else {
			DB::query("DELETE FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");
			$msg = pl('delete_success');
			$alert_info = pl('tip');
			$extrajs = <<<JS
<script type="text/javascript" reload="1">hideWindow('threadlink');showDialog('$msg', 'right', '$alert_info', 'relload()', true, null, '', '', '', 3);function relload(){window.location.reload();}</script>
JS;
			showmessage('', '', '', array('extrajs' => $extrajs));
		}
	} else {
		showmessage(pl('no_pid'));
	}
} elseif($action == 'edit') {
	$lid = intval($_G['gp_lid']);
	$link = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_link')." WHERE lid='{$lid}'");
	if($link) {
		if(!submitcheck('editsubmit')) {
			$thread_count = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base'));
			if($thread_count) {
				$thread_query = DB::query("SELECT * FROM ".DB::table('threadlink_base')." ORDER BY dateline DESC LIMIT 20");
				while($thread = DB::fetch($thread_query)) {
					//可以聚合进去的帖子列表
					$threads[] = $thread;
				}

			}

			include template('threadlink:addlink');
			exit;
		} else {
			$tid = !$_G['gp_change_base'] ? $link['tid'] : intval($_G['gp_tid_hand']);
			$thread_base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='$tid'");
			$subject = daddslashes(cutstr(trim($_G['gp_subject']), 80));
			$message = daddslashes(cutstr(trim($_G['gp_message']), 200));
			$url = daddslashes(trim($_G['gp_url']));
			$pic = daddslashes(trim($_G['gp_pic']));

			empty($tid) && showmessage(pl('no_tid'));
			empty($subject) && showmessage(pl('no_subject'));
			empty($message) && showmessage(pl('no_message'));

			if(!$thread_base) {
				showmessage(pl('no_tid'));
			} else {
				DB::query("UPDATE ".DB::table('threadlink_link')." SET
					tid='$tid',
					subject='$subject',
					message='$message',
					url='$url'
					".(!empty($pic) ? ",pic='$pic'" : '')." WHERE lid='$lid'
					");
				$msg = pl('edit_success');
				$alert_info = pl('tip');
				$extrajs = <<<JS
<script type="text/javascript" reload="1">hideWindow('threadlink');showDialog('$msg', 'right', '$alert_info', 'relload()', true, null, '', '', '', 3);function relload(){window.location.reload();}</script>
JS;
				showmessage('', '', '', array('extrajs' => $extrajs));
			}
		}
	} else {
		showmessage(pl('no_pid'));
	}
}


/**
 * 插件语言包
 */
function pl($str) {
	return lang('plugin/threadlink', $str);
}

?>
