<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_feed.php 19827 2011-01-19 07:07:40Z monkey $
 */

$lang = array
(

	'feed_blog_password' => '{actor} posted a new password blog {subject}',
	'feed_blog_title' => '{actor} published a new blog',
	'feed_blog_body' => '<b>{subject}</b><br />{summary}',
	'feed_album_title' => '{actor} updated album',
	'feed_album_body' => '<b>{album}</b><br />Total {picnum} images',
	'feed_pic_title' => '{actor} uploaded new images',
	'feed_pic_body' => '{title}',



	'feed_poll' => '{actor} posted a new poll',

	'feed_comment_space' => '{actor} left a message at {touser}\'s message board',
	'feed_comment_image' => '{actor} left a comment to {touser}\'s image',
	'feed_comment_blog' => '{actor} left a comment to {touser}\'s blog {blog}',
	'feed_comment_poll' => '{actor} left a comment to {touser}\'s poll {poll}',
	'feed_comment_event' => '{actor} replied {touser}\'s activity {event}',
	'feed_comment_share' => '{actor} left a comment to {touser}\'s share {share}',

	'feed_showcredit' => '{actor} present {credit} bidding points to {fusername}, help friends raised the rank of <a href="home.php?mod=space&do=top" target="_blank">Bidding List</a>',
	'feed_showcredit_self' => '{actor} added {credit} bidding points, raised his rank of <a href="home.php?mod=space&do=top" target="_blank">Bidding List</a>',
	'feed_doing_title' => '{actor}: {message}',
	'feed_friend_title' => '{actor} and {touser} become friends',



	'feed_click_blog' => '{actor} sent a "{click}" to {touser}\'s blog {subject}',
	'feed_click_thread' => '{actor} sent a "{click}" to {touser}\'s thread {subject}',
	'feed_click_pic' => '{actor} sent a "{click}" to {touser}\'s image',
	'feed_click_article' => '{actor} sent a "{click}" to {touser}\'s article {subject}',


	'feed_task' => '{actor} completed task {task}',
	'feed_task_credit' => '{actor} completed task {task}, got points {credit}',

	'feed_profile_update_base' => '{actor} updated basic profile',
	'feed_profile_update_contact' => '{actor} updated contact',
	'feed_profile_update_edu' => '{actor} updated education',
	'feed_profile_update_work' => '{actor} updated work information',
	'feed_profile_update_info' => '{actor} updated personal information',
	'feed_profile_update_bbs' => '{actor} updated forum information',
	'feed_profile_update_verify' => '{actor} updated verified information',

	'feed_add_attachsize' => '{actor} used points {credit} to exchange {size} attachment space.(<a href="home.php?mod=spacecp&ac=credit&op=addsize">I want to exchange</a>)',

	'feed_invite' => '{actor} sent an invitation and become friends with {username}',

	'magicuse_thunder_announce_title' => '<strong>{username} used a thunder card</strong>',
	'magicuse_thunder_announce_body' => 'Hello everyone, I\'m online now.<br /><a href="home.php?mod=space&uid={uid}" target="_blank">Welcome to visit my space</a>',


	'feed_thread_title' =>			'{actor} posted a new thread',
	'feed_thread_message' =>		'<b>{subject}</b><br />{message}',

	'feed_reply_title' =>			'{actor} replied {author}\'s thread {subject}',
	'feed_reply_title_anonymous' =>		'{actor} replied thread {subject}',
	'feed_reply_message' =>			'',

	'feed_thread_poll_title' =>		'{actor} posted a new poll',
	'feed_thread_poll_message' =>		'<b>{subject}</b><br />{message}',

	'feed_thread_votepoll_title' =>		'{actor} joined a poll about {subject}',
	'feed_thread_votepoll_message' =>	'',

	'feed_thread_goods_title' =>		'{actor} added a new goods',
	'feed_thread_goods_message_1' =>	'<b>{itemname}</b><br />Price {itemprice} USD and {itemcredit}{creditunit}',
	'feed_thread_goods_message_2' =>	'<b>{itemname}</b><br />Price {itemprice} USD',
	'feed_thread_goods_message_3' =>	'<b>{itemname}</b><br />Price {itemcredit}{creditunit}',

	'feed_thread_reward_title' =>		'{actor} posted a new reward',
	'feed_thread_reward_message' =>		'<b>{subject}</b><br />Reward {rewardprice}{extcredits}',

	'feed_reply_reward_title' =>		'{actor} replied a reward about {subject}',
	'feed_reply_reward_message' =>		'',

	'feed_thread_activity_title' =>		'{actor} posted a new activity',
	'feed_thread_activity_message' =>	'<b>{subject}</b><br />Time: {starttimefrom}<br />Location: {activityplace}<br />{message}',

	'feed_reply_activity_title' =>		'{actor} joined activity about {subject}',
	'feed_reply_activity_message' =>	'',

	'feed_thread_debate_title' =>		'{actor} posted a new debate',
	'feed_thread_debate_message' =>		'<b>{subject}</b><br />Square: {affirmpoint}<br />Opponent: {negapoint}<br />{message}',

	'feed_thread_debatevote_title_1' =>	'{actor} joined a debate about {subject} as square',
	'feed_thread_debatevote_title_2' =>	'{actor} joined a debate about {subject} as opponent',
	'feed_thread_debatevote_title_3' =>	'{actor} joined a debate about {subject} as neutral',
	'feed_thread_debatevote_message_1' =>	'',
	'feed_thread_debatevote_message_2' =>	'',
	'feed_thread_debatevote_message_3' =>	'',

);

?>