<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_spacecp.php 22088 2011-04-21 07:50:14Z zhengqingpeng $
 */

$lang = array(

	'by' => 'by',
	'tab_space' => ' ',

	'share' => 'Share',
	'share_action' => 'shared',

	'pm_comment' => 'Reply comment',
	'pm_thread_about' => 'About your post in "{subject}"',

	'wall_pm_subject' => 'Hello﹐ I leave a message to you',
	'wall_pm_message' => 'I leave a message to you﹐ [url=\\1]click here to view[/url]',
	'reward' => 'Reward',
	'reward_info' => 'Vote: + \\1',
	'poll_separator' => '"﹑"',
	
	'pm_report_content' => '<a href="home.php?mod=space&uid={reporterid}" target="_blank">{reportername}</a>Report Short Message:<br>From<a href="home.php?mod=space&uid={uid}" target="_blank">{username}</a>Short message<br> content:{message}',
	'message_can_not_send_1' => 'Failed to send you within 24 hours of the current beyond the upper limit of two sessions',
	'message_can_not_send_2' => 'Send a short message twice too fast﹐ please wait before sending',
	'message_can_not_send_3' => 'Sorry﹐ you can send a short message of non-friends-volume',
	'message_can_not_send_4' => 'Sorry﹐ you currently can not use the Send Message feature',
	'message_can_not_send_5' => 'You have exceeded the group chat session within 24 hours maximum',
	'message_can_not_send_6' => 'Others shielding your short message',
	'message_can_not_send_7' => 'Exceeded the maximum number of messages',
	'message_can_not_send_8' => 'Sorry﹐ you cannot send yourself',
	'message_can_not_send_9' => 'Empty message',
	'message_can_not_send_10' => 'Not be less than the number of group chat initiated by two',
	'message_can_not_send_11' => 'The session does not exist',
	'message_can_not_send_12' => 'Sorry﹐ you do not have permission to operate',
	'message_can_not_send_13' => 'This is not a group message',
	'message_can_not_send_14' => 'This is not a Private Message',
	'message_can_not_send_15' => 'Data is incorrect',
	'message_can_not_send_onlyfriend' => 'The user will only accept friend a short message to send',


	'friend_subject' => '<a href="{url}" target="_blank">{username} want to add you as friend</a>',
	'friend_request_note' => '﹐ PS:{note}',
	'comment_friend' =>'<a href="\\2" target="_blank">\\1 left a message to you</a>',
	'photo_comment' => '<a href="\\2" target="_blank">\\1 left a comment on your image</a>',
	'blog_comment' => '<a href="\\2" target="_blank">\\1 left a comment on your blog</a>',
	'poll_comment' => '<a href="\\2" target="_blank">\\1 left a comment on your poll</a>',
	'share_comment' => '<a href="\\2" target="_blank">\\1 left a comment on your share</a>',
	'friend_pm' => '<a href="\\2" target="_blank">\\1 sent you a message</a>',
	'poke_subject' => '<a href="\\2" target="_blank">\\1 poke to you</a>',
	'mtag_reply' => '<a href="\\2" target="_blank">\\1 replied your thread</a>',
	'event_comment' => '<a href="\\2" target="_blank">\\1 left a comment on your activity</a>',

	'friend_pm_reply' => '\\1 replied your message',
	'comment_friend_reply' => '\\1 replied your message',
	'blog_comment_reply' => '\\1 replied your comment of blog',
	'photo_comment_reply' => '\\1 replied your comment of photo',
	'poll_comment_reply' => '\\1 replied your comment of poll',
	'share_comment_reply' => '\\1 replied your comment of share',
	'event_comment_reply' => '\\1 replied your comment of activity',
	
	'mail_my' => 'Interact with my friends to remind',
  	'mail_system' => 'System alerts',

	'invite_subject' => '{username} invited you to join {sitename} and become friends',
	'invite_massage' => '<table border="0">
		<tr>
		<td valign="top">{avatar}</td>
		<td valign="top">
		<h3>Hi﹐ I am {username}. I invite you to join {sitename} and become my friend</h3><br>
		Please add me as your friends﹐ you can receiver my latest feeds﹐ discus and contact me at any time.<br>
		<br>
		Additional message:<br>{saymsg}
		<br><br>
		<strong>Please click the link below to accept your friend\'s invitation:</strong><br>
		<a href="{inviteurl}">{inviteurl}</a><br>
		<br>
		<strong>If you have an acount in {sitename}﹐ please click the link below to view my personal home page:</strong><br>
		<a href="{siteurl}home.php?mod=space&uid={uid}">{siteurl}home.php?mod=space&uid={uid}</a><br>
		</td></tr></table>',

	'app_invite_subject' => '{username} invite you to join {sitename}﹐ and play {appname} together',
	'app_invite_massage' => '<table border="0">
		<tr>
		<td valign="top">{avatar}</td>
		<td valign="top">
		<h3>Hi﹐ I am {username}﹐ I am playing {appname} on {sitename}﹐ I invite you to play together</h3><br>
		<br>
		Additional message:<br>
		{saymsg}
		<br><br>
		<strong>Please click the link below to accept your friend\'s invitation of playing {appname}:</strong><br>
		<a href="{inviteurl}">{inviteurl}</a><br>
		<br>
		<strong>If you have an account in {sitename}﹐ please click the link below to view my personal home page:</strong><br>
		<a href="{siteurl}home.php?mod=space&uid={uid}">{siteurl}home.php?mod=space&uid={uid}</a><br>
		</td></tr></table>',

	'person' => ' person(s)',
	'delete' => 'Delete',

	'space_update' => '{actor} was shown',

	'active_email_subject' => 'Activate your email',
	'active_email_msg' => 'Please copy and paste it into the address bar of your browser to activate your email.<br>Link:<br><a href="{url}" target="_blank">{url}</a>',
	'share_space' => ' shared a member',
	'share_blog' => ' shared a blog',
	'share_album' => ' shared an album',
	'default_albumname' => 'Default album',
	'share_image' => ' shared an image',
	'share_article' => ' shared an article',
	'album' => 'Album',
	'share_thread' => ' shared a thread',
	'mtag' => '{$_G[setting][navs][3][navname]}',
	'share_mtag' => ' shared a {$_G[setting][navs][3][navname]}',
	'share_mtag_membernum' => 'has {membernum} members',
	'share_tag' => ' shared a tag',
	'share_tag_blognum' => 'has {blognum} blog(s)',
	'share_link' => ' shared a URL',
	'share_video' => ' shared a video',
	'share_music' => ' shared a music',
	'share_flash' => ' shared a Flash',
	'share_event' => ' shared an activity',
	'share_poll' => ' shared a \\1 poll',
	'event_time' => 'Time',
	'event_location' => 'Location',
	'event_creator' => 'Creator',
	'the_default_style' => 'Default Style',
	'the_diy_style' => 'Customize Style',

	'thread_edit_trail' => '<ins class="modify">[This thread was edited by \\1 on \\2]</ins>',
	'create_a_new_album' => ' created a new album',
	'not_allow_upload' => 'You are not allowed to upload image now',
	'not_allow_upload_extend' => 'Not allowed to upload pictures of type {extend}',
	'files_can_not_exceed_size' => '{extend} files can not exceed{size}',
	'get_passwd_subject' => 'Reset your password',
	'get_passwd_message' => 'You need to reset your password by clicking the link below within 3 days:<br />\\1<br />(If it is not a link﹐ please paste it to your browser manually)<br />Please reset your password according the tips.',
	'file_is_too_big' => 'Too big',

	'take_part_in_the_voting' => '{actor} joined {touser}\'s {reward} poll <a href="{url}" target="_blank">{subject}</a>',
	'lack_of_access_to_upload_file_size' => 'Cannot get size of file',
	'only_allows_upload_file_types' => 'Only jpg﹐ jpeg﹐ gif﹐ png is allowed',
	'unable_to_create_upload_directory_server' => 'Cannot create directory on server',
	'inadequate_capacity_space' => 'Space is not enough﹐ cannot upload attachments',
	'mobile_picture_temporary_failure' => 'Cannot move temporary files to specific directory',
	'ftp_upload_file_size' => 'Upload image to remote server failed',
	'comment' => 'Comment',
	'upload_a_new_picture' => ' uploaded new image',
	'upload_album' => ' updated album',
	'the_total_picture' => 'Total \\1 iamge(s)',

	'space_open_subject' => 'Come to DIY your space',
	'space_open_message' => 'hi, today I visited your personal home page and found out you do not DIY it. Take a look! The address is:\\1space.php',



	'apply_mtag_manager' => ' want to be the owner of group <a href="\\1" target="_blank">\\2</a>, reason:\\3.<a href="\\1" target="_blank">(click here to manage)</a>',


	'magicunit' => ' ',
	'magic_note_wall' => ' left a message on your <a href="{url}" target="_blank">Message Board</a>',
	'magic_call' => ' called you in blog﹐ <a href="{url}" target="_blank">click here to view</a>',


	'present_user_magics' => 'You received props from manager:\\1',
	'has_not_more_doodle' => 'You don\'t have any doodle board.',

	'do_stat_login' => 'Guest',
	'do_stat_mobilelogin' => 'Mobile Access',
	'do_stat_connectlogin' => 'QQ Login',
	'do_stat_register' => 'New registeration',
	'do_stat_invite' => 'Friends invitation',
	'do_stat_appinvite' => 'Apps invitation',
	'do_stat_add' => 'Publish information',
	'do_stat_comment' => 'Information interactive',
	'do_stat_space' => 'User interaction',
	'do_stat_doing' => 'Doing',
	'do_stat_blog' => 'Blog',
	'do_stat_activity' => 'Activity',
	'do_stat_reward' => 'Reward',
	'do_stat_debate' => 'Debate',
	'do_stat_trade' => 'Trade',
	'do_stat_group' => "Create {$_G[setting][navs][3][navname]}",
	'do_stat_tgroup' => "{$_G[setting][navs][3][navname]}",
	'do_stat_home' => "{$_G[setting][navs][4][navname]}",
	'do_stat_forum' => "{$_G[setting][navs][2][navname]}",
	'do_stat_groupthread' => 'Group threads',
	'do_stat_post' => 'Replies',
	'do_stat_grouppost' => 'Group replies',
	'do_stat_pic' => 'Image',
	'do_stat_poll' => 'Poll',
	'do_stat_event' => 'Activity',
	'do_stat_share' => 'Share',
	'do_stat_thread' => 'Thread',
	'do_stat_docomment' => 'Doing replies',
	'do_stat_blogcomment' => 'Blog comments',
	'do_stat_piccomment' => 'Image comments',
	'do_stat_pollcomment' => 'Poll comments',
	'do_stat_pollvote' => 'Vote',
	'do_stat_eventcomment' => 'Activity comments',
	'do_stat_eventjoin' => 'Join activity',
	'do_stat_sharecomment' => 'Share comments',
	'do_stat_post' => 'posts',
	'do_stat_click' => 'Attitude',
	'do_stat_wall' => 'Message',
	'do_stat_poke' => 'Poke',
	'do_stat_sendpm' => 'Send PM',
	'do_stat_addfriend' => 'Friend request',
	'do_stat_friend' => 'Become friends',
	'do_stat_post_number' => 'Posts',
	'do_stat_statistic' => 'Combine statistic',
	'logs_credit_update_TRC' => 'Quest',
	'logs_credit_update_RTC' => 'Reward',
	'logs_credit_update_RAC' => 'Best answer',
	'logs_credit_update_MRC' => 'Random props',
	'logs_credit_update_BMC' => 'Buy Props',
	'logs_credit_update_TFR' => 'Transfer roll-out',
	'logs_credit_update_RCV' => 'Transfer received',
	'logs_credit_update_CEC' => 'Redeem',
	'logs_credit_update_ECU' => 'UCenter expenditures',
	'logs_credit_update_SAC' => 'Sale accessories',
	'logs_credit_update_BAC' => 'Buy Accessories',
	'logs_credit_update_PRC' => 'Posts by rating',
	'logs_credit_update_RSC' => 'Post Rating',
	'logs_credit_update_STC' => 'Topics for sale',
	'logs_credit_update_BTC' => 'Buy Theme',
	'logs_credit_update_AFD' => 'Buy Points',
	'logs_credit_update_UGP' => 'Buy Extended User Group',
	'logs_credit_update_RPC' => 'Incentive to report',
	'logs_credit_update_ACC' => 'Participate in activities',
	'logs_credit_update_RCT' => 'Replies Awards',
	'logs_credit_update_RCA' => 'Replies winning',
	'logs_credit_update_RCB' => 'Replies return of bonus points',
	'logs_credit_update_CDC' => 'Recharge card secret',

	'logs_credit_update_RGC' => 'Recycled red envelopes',
	'logs_credit_update_BGC' => 'Planted red envelope',
	'logs_credit_update_AGC' => 'Get red packets',
	'logs_credit_update_RKC' => 'PPC',
	'logs_select_operation' => 'Please choose an operation',
	'task_credit' => 'Task reward points',
	'special_3_credit' => 'Theme reward points deducted',
	'special_3_best_answer' => 'Reward points for best answer',
	'magic_credit' => 'Random props to earn points',
	'magic_space_gift' => 'Home planted a red envelope in their own space',
	'magic_space_re_gift' => 'Recovery has not run out of the red envelope',
	'magic_space_get_gift' => 'Access to space to receive the red envelope',
	'credit_transfer' => 'Integrating the transfer',
	'credit_transfer_tips' => 'Income transfers',
	'credit_exchange_tips_1' => 'Implementation of points on the conversion operation ',
	'credit_exchange_to' => 'Converted into',
	'credit_exchange_center' => 'Redeem Points by UCenter',
	'attach_sell' => 'Sale',
	'attach_sell_tips' => 'Annex to earn points',
	'attach_buy' => 'Buy',
	'attach_buy_tips' => 'Annex spending integral',
	'grade_credit' => 'Score points were obtained',
	'grade_credit2' => 'Post score points deducted',
	'thread_credit' => 'Subject access points',
	'thread_credit2' => 'Topics spending integral',
	'buy_credit' => 'Recharge the integral',
	'buy_usergroup' => 'Spending points to buy extended user group',
	'report_credit' => 'Function of the incentive to report',
	'join' => 'Participation',
	'activity_credit' => 'Activities﹐ net of points',
	'thread_send' => 'Net released',
	'replycredit' => 'Distribution points',
	'add_credit' => 'Rewards Points',
	'recovery' => 'Recycling',
	'replycredit_post' => 'Replies Awards',
	'replycredit_thread' => 'Distribution of posts',
	'card_credit' => 'Recharge card access points close',
	'ranklist_top' => 'Consumer points to participate in PPC',

	'profile_unchangeable' => 'Unchangeable after submit',
	'profile_is_verifying' => 'Pending moderate',
	'profile_mypost' => 'I sumbit',
	'profile_need_verifying' => 'Need moderation',
	'profile_edit' => 'Edit',
	'profile_censor' => '(Contian banned words)',
	'profile_verify_modify_error' => '{verify} has verfied﹐ cannot be edited',
	'profile_verify_verifying' => 'Your information has been submitted {verify}﹐ please be patient verification.',

	'district_level_1' => '-Country-',
	'district_level_2' => '-Province-',
	'district_level_3' => '-City-',
	'district_level_4' => '-Town-',
	'invite_you_to_visit' => '{user} invites you to visit {bbname}',

	'spacecp_message_prompt' => '(Support {msg} code﹐ max 1000 characters)',
	'card_update_doing' => ' <a class="xi2" href="###">[Update doings]</a>',
	'email_acitve_message' => '<img src="{imgdir}/mail_inactive.png" alt="Unverified" class="vm" /> <span class="xi1">New Email({newemail})Pending Verification...</span><br />
								System has sent a verification email to activate email﹐Please check your email﹐Activation validation。<br>
								If you do not receive a confirmation email﹐You can replace a mailbox﹐or<a href="home.php?mod=spacecp&ac=profile&op=password&resend=1" class="xi2">Receive verification email again</a>',

);

?>