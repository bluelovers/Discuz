<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_message.php 650 2010-09-13 07:25:03Z yexinhao $
 */

$lang = array
(

	//poll/poll_index.php
	'poll_inexistence' => '对不起，您访问的投票不存在！',
	'poll_status_unopened' => '对不起，您访问的投票已关闭！',
	'poll_unstart' => '对不起，投票尚未开始，请您稍后再来！',
	'poll_expired' => '对不起，投票已过期，您不可以再投票！',
	'poll_data_invalid' => '对不起，您提交的数据不合法！',
	'poll_vote_error' => '对不起，投票失败，您可能已经投过票，详情请联系管理员',
	'poll_per_once' => '对不起，每个用户只允许投票一次！',
	'poll_limittime_short' => '对不起，您的两次投票间隔时间太短！请{utime}后再来投票，当前验证方式为：{check}',
	'poll_choice_null' => '请您至少选择一个选项！',
	'poll_choicenum_more' => '对不起，你选择项数过多，本投票最多只允许选择{num}项！',
	'poll_vote_succeed' => '投票成功 ，感谢您的参与！' ,
	'poll_vote_succeed_login' => '投票成功，感谢您的参与！请您登录后查看投票结果。',
	'poll_vote_so_error' => '数据校验失败，请尝试从标准浏览器提交数据',
	'poll_guest_unallowed' => '对不起，本投票不允许游客投票！',

	'poll_repeattype_cookie' => 'cookie验证',
	'poll_repeattype_username' => '用户名验证',
	'poll_repeattype_ip' => 'IP地址验证',
	'poll_repeattype_so' => 'Flash验证',

);

?>