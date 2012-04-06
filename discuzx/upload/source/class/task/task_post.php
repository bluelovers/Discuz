<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_post.php 20396 2011-02-23 03:21:40Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_post {

	var $version = '1.0';
	var $name = 'post_name';
	var $description = 'post_desc';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array(
		'act' => array(
			'title' => 'post_complete_var_act',
			'type' => 'mradio',
			'value' => array(
				array('newthread', 'post_complete_var_act_newthread'),
				array('newreply', 'post_complete_var_act_newreply'),
				array('newpost', 'post_complete_var_act_newpost'),
			),
			'default' => 'newthread',
			'sort' => 'complete',
		),
		'forumid' => array(
			'title' => 'post_complate_var_forumid',
			'type' => 'select',
			'value' => array(),
			'sort' => 'complete',
		),
		'threadid' => array(
			'title' => 'post_complate_var_threadid',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		),
		'author' => array(
			'title' => 'post_complate_var_author',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		),
		'num' => array(
			'title' => 'post_complete_var_num',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		),
		'time' => array(
			'title' => 'post_complete_var_time',
			'type' => 'text',
			'value' => '',
			'sort' => 'complete',
		)
	);

	function task_post() {
		global $_G;
		loadcache('forums');
		$this->conditions['forumid']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$this->conditions['forumid']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name'], $forum['type'] == 'group' ? 1 : 0);
		}
	}

	function csc($task = array()) {
		global $_G;

		$taskvars = array('num' => 0);
		$query = DB::query("SELECT variable, value FROM ".DB::table('common_taskvar')." WHERE taskid='$task[taskid]'");
		while($taskvar = DB::fetch($query)) {
			if($taskvar['value']) {
				$taskvars[$taskvar['variable']] = $taskvar['value'];
			}
		}
		$taskvars['num'] = $taskvars['num'] ? $taskvars['num'] : 1;

		$tbladd = $sqladd = '';
		if($taskvars['act'] == 'newreply' && $taskvars['threadid']) {
			$sqladd .= " AND p.tid='$taskvars[threadid]'";
		} else {
			if($taskvars['forumid']) {
				$sqladd .= " AND p.fid='$taskvars[forumid]'";
			}
			if($taskvars['author']) {
				$taskvars['authorid'] = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".addslashes($taskvars['author'])."'");
				$tbladd .= ", ".DB::table('forum_thread')." t";
				$sqladd .= " AND p.tid=t.tid AND t.authorid='$taskvars[authorid]'";
			}
		}
		if($taskvars['act']) {
			if($taskvars['act'] == 'newthread') {
				$sqladd .= " AND p.first='1'";
			} elseif($taskvars['act'] == 'newreply') {
				$sqladd .= " AND p.first='0'";
			}
		}

		$sqladd .= ($taskvars['time'] = floatval($taskvars['time'])) ? " AND p.dateline BETWEEN $task[applytime] AND $task[applytime]+3600*$taskvars[time]" : " AND p.dateline>$task[applytime]";

		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable())." p $tbladd WHERE p.authorid='{$_G['uid']}' $sqladd");

		if($num && $num >= $taskvars['num']) {
			return TRUE;
		} elseif($taskvars['time'] && TIMESTAMP >= $task['applytime'] + 3600 * $taskvars['time'] && (!$num || $num < $taskvars['num'])) {
			return FALSE;
		} else {
			return array('csc' => $num > 0 && $taskvars['num'] ? sprintf("%01.2f", $num / $taskvars['num'] * 100) : 0, 'remaintime' => $taskvars['time'] ? $task['applytime'] + $taskvars['time'] * 3600 - TIMESTAMP : 0);
		}
	}

	function view($task, $taskvars) {
		global $_G;
		$return = $value = '';
		if(!empty($taskvars['complete']['forumid'])) {
			$value = intval($taskvars['complete']['forumid']['value']);
			loadcache('forums');
			$value = '<a href="forum.php?mod=forumdisplay&fid='.$value.'"><strong>'.$_G['cache']['forums'][$value]['name'].'</strong></a>';
		} elseif(!empty($taskvars['complete']['threadid'])) {
			$value = intval($taskvars['complete']['threadid']['value']);
			$subject = DB::result_first("SELECT subject FROM ".DB::table('forum_thread')." WHERE tid='$value'");
			$value = '<a href="forum.php?mod=viewthread&tid='.$value.'"><strong>'.($subject ? $subject : 'TID '.$value).'</strong></a>';
		} elseif(!empty($taskvars['complete']['author'])) {
			$value = addslashes($taskvars['complete']['author']['value']);
			$authorid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".$value."'");
			$value = '<a href="home.php?mod=space&uid='.$authorid.'"><strong>'.$value.'</strong></a>';
		}
		$taskvars['complete']['num']['value'] = intval($taskvars['complete']['num']['value']);
		$taskvars['complete']['num']['value'] = $taskvars['complete']['num']['value'] ? $taskvars['complete']['num']['value'] : 1;
		if($taskvars['complete']['act']['value'] == 'newreply') {
			if($taskvars['complete']['threadid']) {
				$return .= lang('task/post', 'task_complete_act_newreply_thread', array('value' => $value, 'num' => $taskvars['complete']['num']['value']));
			} else {
				$return .= lang('task/post', 'task_complete_act_newreply_author', array('value' => $value, 'num' => $taskvars['complete']['num']['value']));
			}
		} else {
			if($taskvars['complete']['forumid']) {
				$return .= lang('task/post', 'task_complete_forumid', array('value' => $value));
			}
			if($taskvars['complete']['act']['value'] == 'newthread') {
				$return .= lang('task/post', 'task_complete_act_newthread', array('num' => $taskvars['complete']['num']['value']));
			} else {
				$return .= lang('task/post', 'task_complete_act_newpost', array('num' => $taskvars['complete']['num']['value']));
			}
		}
		return $return;
	}

	function sufprocess($task) {
	}

}

?>