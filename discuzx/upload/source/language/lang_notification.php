<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_notification.php 22303 2011-04-29 02:42:08Z maruitao $
 */

$lang = array
(

	'type_wall' => 'Message',
	'type_piccomment' => 'Picture Comments',
	'type_blogcomment' => 'Blogcomment',
	'type_clickblog' => 'Blog position',
	'type_clickarticle' => 'Posts position',
	'type_clickpic' => 'Picture position',
	'type_sharecomment' => 'Share Comments',
	'type_doing' => 'Record',
	'type_friend' => 'Friend',
	'type_credit' => 'Credit',
	'type_bbs' => 'Forum',
	'type_system' => 'System',
	'type_thread' => 'Thread',
	'type_task' => 'Task',
	'type_group' => 'Group',

	'mail_to_user' => 'A new notice',
	'showcredit' => '{actor} Gift to you {credit} A bid Credits﹐To help improve your <a href="misc.php?mod=ranklist&type=member" target="_blank">Auction list</a> In the ranking',
	'share_space' => '{actor} Share your space',
	'share_blog' => '{actor} Share your blog <a href="{url}" target="_blank">{subject}</a>',
	'share_album' => '{actor} Share your album <a href="{url}" target="_blank">{albumname}</a>',
	'share_pic' => '{actor} Share your pic {albumname} the <a href="{url}" target="_blank"> Image</a>',
	'share_thread' => '{actor} Share your thread <a href="{url}" target="_blank">{subject}</a>',
	'share_article' => '{actor} Share your article <a href="{url}" target="_blank">{subject}</a>',
	'magic_present_note' => 'Props to give you a <a href="{url}" target="_blank">{name}</a>',
	'friend_add' => '{actor} And you become a friend',
	'friend_request' => '{actor} Add your friend request to{note}&nbsp;&nbsp;<a onclick="showWindow(this.id, this.href, \'get\', 0);" class="xw1" id="afr_{uid}" href="{url}">Approve the application</a>',
	'doing_reply' => '{actor} Replied to your <a href="{url}" target="_blank">Record</a>',
	'wall_reply' => '{actor} Replied to your <a href="{url}" target="_blank">Message</a>',
	'pic_comment_reply' => '{actor} Replied to your <a href="{url}" target="_blank">Picture Comments</a>',
	'blog_comment_reply' => '{actor} Replied to your <a href="{url}" target="_blank">Blogcomment</a>',
	'share_comment_reply' => '{actor} Replied to your <a href="{url}" target="_blank">Share Comments</a>',
	'wall' => '{actor} In the message board to you <a href="{url}" target="_blank">Message</a>',
	'pic_comment' => '{actor} Your comments <a href="{url}" target="_blank">Picture</a>',
	'blog_comment' => '{actor} Your comments blog <a href="{url}" target="_blank">{subject}</a>',
	'share_comment' => '{actor} Your comments <a href="{url}" target="_blank">share</a>',
	'click_blog' => '{actor} Blog on to your <a href="{url}" target="_blank">{subject}</a> Made a statement',
	'click_pic' => '{actor} Your <a href="{url}" target="_blank">Picture</a> Made a statement',
	'click_article' => '{actor} Your article <a href="{url}" target="_blank">{subject}</a> Made a statement',
	'show_out' => '{actor} After a visit to your home page﹐PPC chart your last points are also consumed a',
	'puse_article' => 'Congratulations﹐You <a href="{url}" target="_blank">{subject}</a> has been added to the list of articles﹐ <a href="{newurl}" target="_blank">Click to view</a>',

	'myinvite_request' => 'A new application message<a href="home.php?mod=space&do=notice&view=userapp">Click here to enter the application information page related operations</a>',


	'group_member_join' => '{actor} Add your <a href="forum.php?mod=group&fid={fid}" target="_blank">{groupname}</a> Groups need to review﹐Please go to Groups<a href="{url}" target="_blank">Manage</a> Review',
	'group_member_invite' => '{actor} Invite you to join <a href="forum.php?mod=group&fid={fid}" target="_blank">{groupname}</a> group﹐<a href="{url}" target="_blank">Click here to Join</a>',
	'group_member_check' => 'You have passed the <a href="{url}" target="_blank">{groupname}</a> Group audit﹐Please <a href="{url}" target="_blank">Click here to visit</a>',
	'group_member_check_failed' => 'You did not pass <a href="{url}" target="_blank">{groupname}</a> Group audit。',

	'reason_moderate' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} {modaction} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_merge' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} {modaction} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_delete_post' => 'You <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> the post is {actor} Del <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_delete_comment' => 'You <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> Reviews are {actor} Del <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_ban_post' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} {modaction} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_warn_post' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} {modaction}<br />
Continuous {warningexpiration} the cumulative days {warninglimit} Warnings﹐You will be automatically banned speech {warningexpiration} days。<br />
As of now﹐You have been warned {authorwarnings} time﹐Note！<div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_move' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} move to <a href="forum.php?mod=forumdisplay&fid={tofid}" target="_blank">{toname}</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_copy' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} copy with <a href="forum.php?mod=viewthread&tid={threadid}" target="_blank">{subject}</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_remove_reward' => 'Thread of your reward <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} Revocation <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamp_update' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} Add a stamp {stamp} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamp_delete' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} Withdrawn stamp <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamplist_update' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} add icon {stamp} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamplist_delete' => 'Your thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} unadd icon <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stickreply' => 'You in the thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> reply Was {actor} top <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stickdeletereply' => 'You in the thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> reply Was {actor} untop <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modthreads_delete' => 'The subject of your post {threadsubject} Not approved﹐Has now been removed！<div class="quote"><blockquote>{reason}</blockquote></div>',

	'modthreads_validate' => 'The subject of your post <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{threadsubject}</a> Audited by！ &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modreplies_delete' => 'Post your reply is not approved﹐Has now been removed！ <p class="summary">Replies content：<span>{post}</span></p> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modreplies_validate' => 'Your reply has been reviewed by the published！ &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">view &rsaquo;</a> <p class="summary">Replies content：<span>{post}</span></p> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'transfer' => 'You received a from the {actor} Transfer of Credits {credit} &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=log&suboperation=creditslog" target="_blank" class="lit">view &rsaquo;</a>
<p class="summary">{actor} Say：<span>{transfermessage}</span></p>',

	'addfunds' => 'Your request has been submitted to complete the recharge Credits﹐Corresponding amount of points have been deposited into your Credits account &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=base" target="_blank" class="lit">view &rsaquo;</a>
<p class="summary">Order No.：<span>{orderid}</span></p><p class="summary">Expenditure：<span>RMB {price} </span></p><p class="summary">Income：<span>{value}</span></p>',

	'rate_reason' => 'You in the thread <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> The post is {actor} Score {ratescore} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'rate_removereason' => 'You in the thread <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> The score in the post {ratescore} <div class="quote"><blockquote>{reason}</blockquote></div> Was {actor} Revocation',

	'trade_seller_send' => '<a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> Purchase your products <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>﹐The other paid﹐Waiting for your shipment &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">view &rsaquo;</a>',

	'trade_buyer_confirm' => 'Your purchase of goods <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>﹐<a href="home.php?mod=space&uid={sellerid}" target="_blank">{seller}</a> Has shipped﹐Waiting for you to confirm the &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">view &rsaquo;</a>',

	'trade_fefund_success' => 'Merchandise <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> Refund has been successfully &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">Evaluate &rsaquo;</a>',

	'trade_success' => 'Merchandise <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> Has been trading successfully &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">Evaluate &rsaquo;</a>',

	'trade_order_update_sellerid' => 'Sellers <a href="home.php?mod=space&uid={sellerid}" target="_blank">{seller}</a> Modified products <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> Single transactions﹐Please confirm &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">view &rsaquo;</a>',

	'trade_order_update_buyerid' => 'Buyer <a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> Modified products <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> Single transactions﹐Please confirm &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">view &rsaquo;</a>',

	'eccredit' => 'You deal with {actor} Evaluation has been made for you &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">Comment back &rsaquo;</a>',

	'activity_notice' => '{actor} Apply to join your events <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a>﹐Please review the &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a>',

	'activity_apply' => 'Activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Sponsor {actor} Has approved your participation in this event &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_replenish' => 'Activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Sponsor {actor} Notify you need to complete enrollment information &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_delete' => 'Activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Sponsor {actor} Deny your participation in this event &nbsp; <a href="forum.php?mod=viewthread&tid={tid}"  target="_blank" class="lit">view &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_cancel' => '{actor} Canceled participate <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Activity &nbsp; <a href="forum.php?mod=viewthread&tid={tid}"  target="_blank" class="lit">view &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_notification' => 'Activity <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Sponsor {actor} Sent a notice &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">View activity &rsaquo;</a> <div class="quote"><blockquote>{msg}</blockquote></div>',

	'reward_question' => 'Thread of your reward <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Was {actor} Set the best answer &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a>',

	'reward_bestanswer' => 'Your reply is offering a reward of thread <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Author {actor} Selected as the best answer &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a>',

	'reward_bestanswer_moderator' => 'Reward your theme <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Response was selected as best answer &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a>',

	'comment_add' => '{actor} Comments have been the subject of your <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> Posts by &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">view &rsaquo;</a>',

	'reppost_noticeauthor' => '{actor} Reply to your post <a href="forum.php?mod=redirect&goto=findpost&ptid={tid}&pid={pid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">view</a>',

	'task_reward_credit' => 'Congratulations on completing the task：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>﹐Earn credits {creditbonus} &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=base" target="_blank" class="lit">View My Points &rsaquo;</a></p>',

	'task_reward_magic' => 'Congratulations on completing the task：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>﹐Get magics <a href="home.php?mod=magic&action=mybox" target="_blank">{rewardtext}</a> {bonus} Zhang',

	'task_reward_medal' => 'Congratulations on completing the task：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>﹐Get medal <a href="home.php?mod=medal" target="_blank">{rewardtext}</a> Period {bonus} days',

	'task_reward_medal_forever' => 'Congratulations on completing the task：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>﹐Get medal <a href="home.php?mod=medal" target="_blank">{rewardtext}</a> Never expires',

	'task_reward_invite' => 'Congratulations on completing the task：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>﹐Get<a href="home.php?mod=spacecp&ac=invite" target="_blank">Invitation code {rewardtext} </a> Period {bonus} days',

	'task_reward_group' => 'Congratulations on completing the task：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>﹐Get user group {rewardtext} Period {bonus} days &nbsp; <a href="home.php?mod=spacecp&ac=usergroup" target="_blank" class="lit">See what I can do &rsaquo;</a>',

	'user_usergroup' => 'Upgrade your user group {usergroup} &nbsp; <a href="home.php?mod=spacecp&ac=usergroup" target="_blank" class="lit">See what I can do &rsaquo;</a>',

	'grouplevel_update' => 'Congratulations﹐Your group {groupname} Has been upgraded to {newlevel}。',

	'thread_invite' => '{actor} Invite you to {invitename} <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">view &rsaquo;</a>',
	'invite_friend' => 'Congratulations to invite to {actor} And become your friend',

	'poke_request' => '<a href="{fromurl}" class="xi2">{fromusername}</a>: <span class="xw0">{pokemsg}&nbsp;</span><a href="home.php?mod=spacecp&ac=poke&op=reply&uid={fromuid}&from=notice" id="a_p_r_{fromuid}" class="xw1" onclick="showWindow(this.id, this.href, \'get\', 0);">Back to say hello</a><span class="pipe">|</span><a href="home.php?mod=spacecp&ac=poke&op=ignore&uid={fromuid}&from=notice" id="a_p_i_{fromuid}" onclick="showWindow(\'pokeignore\', this.href, \'get\', 0);">Ignore</a>',

	'profile_verify_error' => '{verify}Data review is denied the following fields need to refill:<br/>{profile}<br/>Reason for rejection:{reason}',
	'profile_verify_pass' => 'Congratulations﹐You fill in the {verify} Information audit has passed',
	'profile_verify_pass_refusal' => 'Unfortunately﹐You fill in the {verify} Data audit has been rejected',
	'member_ban_speak' => 'You have been {user} Prohibit speech﹐Deadline：{day} days(0：On behalf of the permanent gag)﹐Ban reason：{reason}',

	'member_moderate_invalidate' => 'Your account administrator can not audit by﹐Please<a href="home.php?mod=spacecp&ac=profile">Resubmit the registration information</a>。<br />Manager Message: <b>{remark}</b>',
	'member_moderate_validate' => 'Your account has been approved。<br />Manager Message: <b>{remark}</b>',
	'member_moderate_invalidate_no_remark' => 'Your account administrator can not audit by﹐Please<a href="home.php?mod=spacecp&ac=profile">Resubmit the registration information</a>。',
	'member_moderate_validate_no_remark' => 'Your account has been approved。',
	'manage_verifythread' => 'Pending a new thread。<a href="admin.php?action=moderate&operation=threads&dateline=all">Review now</a>',
	'manage_verifypost' => 'Pending a new reply。<a href="admin.php?action=moderate&operation=replies&dateline=all">Review now</a>',
	'manage_verifyuser' => 'Pending a new member。<a href="admin.php?action=moderate&operation=members">Review now</a>',
	'manage_verifyblog' => 'Pending a new blog。<a href="admin.php?action=moderate&operation=blogs">Review now</a>',
	'manage_verifydoing' => 'Pending a new Record。<a href="admin.php?action=moderate&operation=doings">Review now</a>',
	'manage_verifypic' => 'Pending a new pic。<a href="admin.php?action=moderate&operation=pictures">Review now</a>',
	'manage_verifyshare' => 'Pending a new share。<a href="admin.php?action=moderate&operation=shares">Review now</a>',
	'manage_verifycommontes' => 'Pending a new message/comment。<a href="admin.php?action=moderate&operation=comments">Review now</a>',
	'manage_verifyrecycle' => 'Recycle a new theme to be addressed。<a href="admin.php?action=recyclebin">Now processing</a>',
	'manage_verifyrecyclepost' => 'Replies pending a new Recycle Bin Replies。<a href="admin.php?action=recyclebinpost">Now processing</a>',
	'manage_verifyarticle' => 'Pending a new article。<a href="admin.php?action=moderate&operation=articles">Review now</a>',
	'manage_verifymedal' => 'Pending a new medal。<a href="admin.php?action=medals&operation=mod">Review now</a>',
	'manage_verifyacommont' => 'Pending a new article commont。<a href="admin.php?action=moderate&operation=articlecomments">Review now</a>',
	'manage_verifytopiccommont' => 'Pending a new thematic reviews。<a href="admin.php?action=moderate&operation=topiccomments">Review now</a>',
	'manage_verify_field' => 'Pending a new{verifyname}。<a href="admin.php?action=verify&operation=verify&do={doid}">Now processing</a>',
	'system_notice' => '{subject}<p class="summary">{message}</p>',
	'system_adv_expiration' => 'The following ad will be your site {day} Days after the expiration﹐Please deal with：<br />{advs}',
	'report_change_credits' => '{actor} Handled your complaint {creditchange} {msg}',
	'new_report' => 'There are new reports of pending﹐<a href="admin.php?action=report" target="_blank">Click here to enter the background processing</a>。',
	'new_post_report' => 'There are new reports of pending﹐<a href="forum.php?mod=modcp&action=report&fid={fid}" target="_blank">Click here to enter the administration panel</a>。',
	'magics_receive' => 'You receive {actor} Give you props {magicname}
<p class="summary">{actor} Say：<span>{msg}</span></p>
<p class="mbn"><a href="home.php?mod=magic" target="_blank">Rebate props</a><span class="pipe">|</span><a href="home.php?mod=magic&action=mybox" target="_blank">View my magic box</a></p>',

);

?>