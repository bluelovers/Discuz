<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_post.php by Valery Votintsev at sources.ru
 */

$lang = array
(
	'post_name'				=> 'Forum post task',//'论坛帖子类任务',
	'post_desc'				=> 'Make a Posting to complete the task, activate the forum atmosphere.',//'通过发帖回帖完成任务，活跃论坛的氛围。',
	'post_complete_var_act'			=> 'Action',//'动作',
	'post_complete_var_act_newthread'	=> 'Create New Thread',//'发新主题',
	'post_complete_var_act_newreply'	=> 'Post new reply',//'发新回复',
	'post_complete_var_act_newpost'		=> 'Post new thread/reply',//'发新主题/回复',
	'post_complate_var_forumid'		=> 'Target Forums',//'版块限制',
	'post_complate_var_forumid_comment'	=> 'Set the Forums where members can do this for complete the task',//'设置会员只能在某个版块完成任务',
	'post_complate_var_threadid'		=> 'Target threads',//'回复指定主题',
	'post_complate_var_threadid_comment'	=> 'Enter the threads ID  where members can do this for complete the task',//'设置会员只有回复该主题才能完成任务，请填写主题的 TID',
	'post_complate_var_author'		=> 'Target Author',//'回复指定作者',
	'post_complate_var_author_comment'	=> 'Set the author names, replying for whose threads only to complete the task.',//'设置会员只有回复该作者发表的主题才能完成任务，请填写作者的用户名',
	'post_complete_var_num'			=> 'Minimum number of times to perform action',//'执行动作次数下限',
	'post_complete_var_num_comment'		=> 'Members need to perform the appropriate action at least this number of times',//'会员需要执行相应动作的最少次数',
	'post_complete_var_time'		=> 'Time limit (hours)',//'时间限制(小时)',
	'post_complete_var_time_comment'	=> 'Set the time restrictions to comlete the task. If a member can not complete the task in this time range, the task marked as failed and no award given. Set to 0 or leave blank for no limits.',//'设置会员从申请任务到完成任务的时间限制，会员在此时间内未能完成任务则不能领取奖励并标记任务失败，0 或留空为不限制',

	'task_complete_forumid'			=> 'at Forum {value} ',//'在版块 {value} ',
	'task_complete_act_newthread'		=> 'Created {num} new threads.',//'发新主题 {num} 次。',
	'task_complete_act_newpost'		=> 'Posted new thread/reply {num} times.',//'发新主题/回复 {num} 次。',
	'task_complete_act_newreply_thread'	=> 'Replied the thread "{value}" {num} times.',//'回复主题“{value}” {num} 次。',
	'task_complete_act_newreply_author'	=> 'Replied the thread of "{value}" {num} times.',//'回复作者“{value}”的主题 {num} 次。',
);

?>