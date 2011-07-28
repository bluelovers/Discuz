<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_misc.php 20740 2011-03-02 09:55:01Z liulanbo $
 */

$lang = array
(
	'discuz_lang' => 'misc',
	'contact' => 'contact:',
	'anonymous' => 'anonymous',
	'anonymoususer' => 'anonymoususer',
	'guestuser' => 'guestuser',
	'has_expired' => 'This message has expired.',
	'click_view' => 'click to view',
	'never_expired' => 'never expired',
	'sort_update' => 'update',
	'sort_upload' => 'upload',
  	'view_noperm' => 'view noperm',
'post_hidden'		=> '**** Hidden by Author ****',
'post_banned'		=> '**** Author was banned or deleted ****',
	'post_single_banned'	=> '**** This post was banned ****',
	'message_ishidden_hiddenreplies'	=> 'This post is visible only to author of the thread',
	'post_reply_quote'	=> '{author} replied at {time}',
	'post_edit'		=> '[i=s] Edited by {editor} at {edittime} [/i]\n\n',
	'post_edit_regexp'	=> '/^\[i=s\] Edited by .*? at .*? \[\/i\]\n\n/s',
	'post_edithtml'		=> '[i=s] Edited by {editor} at {edittime} [/i]<br /><br />',
	'post_edithtml_regexp'	=> '/^\[i=s\] Edited by .*? at .*? \[\/i\]&lt;br \/&gt;&lt;br \/&gt;/s',
	'post_editnobbcode'	=> '[ Post edited by {editor} at {edittime} ]\n\n',
	'post_editnobbcode_regexp'	=> '/^\[ Post edited by .*? at .*? \]\n\n/s',
	'post_reply'		=> 'Reply',
	'post_thread'		=> 'Add Thread',

	'price'			=> 'Price',
	'pay_view'		=> 'Payments Log',
	'attachment_buy'	=> 'Buy',

	'post_trade_yuan'		=> 'USD',
	'post_trade_seller'		=> 'Seller',
	'post_trade_name'		=> 'Product name',
	'post_trade_price'		=> 'Price',
	'post_trade_quality'		=> 'Quality',
	'post_trade_locus'		=> 'Location',
	'post_trade_transport_type'	=> 'Delivery tax paid by',
	'post_trade_transport_seller'	=> 'Seller',
	'post_trade_transport_buyer'	=> 'Buyer',
	'post_trade_transport_mail'	=> 'Snail Mail',
	'post_trade_transport_express'	=> 'Express Mail',
	'post_trade_transport_virtual'	=> 'Virtual or No delivery',
	'post_trade_transport_physical'	=> 'COD (Cash on delivery)',
	
	'post_trade_locus'		=> 'Location',
	'post_trade_description'	=> 'Product Description',
	'post_trade_pm_subject'		=> '[Bargain]',
	'post_trade_pm_buynum'		=> 'Buy amount',
	'post_trade_pm_wishprice'	=> 'Wish Price',
	'post_trade_pm_reason'		=> 'Reason of negotiation',
	
	'post_deleted' => 'Invalid floors, the post has been deleted',
	
	'postappend_content'		=> 'Add post',
	'payment_unit'			=> 'USD',

	'attach'			=> 'Attachment',
	'attach_pay'			=> 'Attachment payed',
	'attach_credits_policy'		=> 'View payment policy',
	'attach_img'			=> 'Attach Images',
	'attach_readperm'		=> 'Read permissions',
	
		'attach_img_zoom'		=> 'Click to view the full image in new window.\\nCTRL + mouse wheel to zoom in or out.',
	'attach_img_thumb'		=> 'Click to view the full image in new window',
	'attach_downloads'		=> 'Downloads',

	'post_trade_transport'		=> 'Transport fee',
	


	'post_trade_transport_mail'	=> 'Snail mail',
	'post_trade_quality'		=> 'Product type',
	'post_trade_quality_new'	=> 'New',
	'post_trade_quality_secondhand'	=> 'Secondhand',

	'trade_unstart'			=> '<font color="gray">Wait for processing</font>',
	'trade_waitbuyerpay'		=> 'Wait for payment',
	'trade_waitsellerconfirm'	=> 'Wait for seller confirm order',
	'trade_sysconfirmpay'		=> 'Wait for seller confirm payment',
	'trade_waitsellersend'		=> 'Payed. Wait for seller shipping',
	

	'trade_waitbuyerconfirm'	=> 'Shipped. Wait for buyer confirm',
	'trade_syspayseller'		=> 'Delivered. Wait for system payment to the seller',
	'trade_finished'		=> '<font color="green">Sale successfully finished</font>',
	'trade_closed'			=> '<font color="gray">Sale closed (not finished)</font>',
	'trade_waitselleragree'		=> 'Wait for seller agree the refund',
	'trade_sellerrefusebuyer'	=> 'Seller refused buyer conditions, waiting for buyer modify his conditions',
	'trade_waitbuyerreturn'		=> 'Seller approved refund, waiting for return products to buyer',
	'trade_waitsellerconfirmgoods'	=> 'Wait for the seller receiving refunded products',
	'trade_waitalipayrefund'	=> 'Both sides have agreed, wait for the money refund from payment system',
	'trade_alipaycheck'		=> 'Wait for payment system processed refund',
	'trade_overedrefund'		=> 'Refund overed',
	'trade_refundsuccess'		=> '<font color="green">Successfully refunded</font>',
	'trade_refundclosed'		=> '<font color="green">Refund closed</font>',

	'trade_offline_1'		=> 'Transaction initiated',
	'trade_offline_4'		=> 'I have paid and waiting for delivery.',
	'trade_offline_5'		=> 'I have shipped.',
	'trade_offline_7'		=> 'I have received products, the transaction successfully concluded',
	'trade_offline_8'		=> 'Cancel this transaction.',
	'trade_offline_10'		=> 'I want to return, waiting for the seller agreed to refund.',
	'trade_offline_11'		=> 'The seller refused to refund.',
	'trade_offline_12'		=> 'Seller agreed to refund.',
	'trade_offline_13'		=> 'I have returned, waiting for seller received products.',
	'trade_offline_17'		=> 'Seller received returned products, refunded.',

	'trade_message_4'		=> 'Enter the payment method and bank account info',
	'trade_message_5'		=> 'Enter shipping company, invoices, and other information',
	'trade_message_13'		=> 'Enter shipping company, invoices, and other information',
	

'credit_payment'		=> 'Recharge Points',
	'credit_forum_payment'		=> 'Recharge forum points',
	'credit_forum_royalty'		=> 'Transaction fee',

	'invite_payment' =>  'buy invitation',
	'invite_forum_payment' => 'buy invitation',
	'invite_forum_payment_unit' => 'unit',
	'invite_forum_royalty' => 'royalty',


	'formulaperm_regdate'		=> 'Registration date',
	'formulaperm_regday'		=> 'Days after register',
	'formulaperm_regip'		=> 'Reg IP',
	'formulaperm_lastip'		=> 'Last IP',
	'formulaperm_buyercredit'	=> 'Buyer rate',
	'formulaperm_sellercredit'	=> 'Seller rate',
	'formulaperm_digestposts'	=> 'Digests',
	'formulaperm_posts'		=> 'Posts',
	'formulaperm_threads'		=> 'Threads',
	'formulaperm_oltime'		=> 'Online time (hours)',
	'formulaperm_pageviews'		=> 'Page views',
	
	

	'formulaperm_and'		=> 'and',
	'formulaperm_or'		=> 'or',
	'formulaperm_extcredits'	=> 'Ext Points',

	'login_normal_mode'		=> 'Online',
	'login_switch_invisible_mode'	=> 'Toggle to stealth mode',
	'login_switch_normal_mode'	=> 'Toggle to online mode',
	'login_invisible_mode'		=> 'Stealth',

	'eccredit_explain'		=> 'Explanation',

	'google_site_0'			=> 'Search web',
	'google_site_1'			=> 'Search site',
	'google_sa'			=> 'Search',

	'modcp_logs_action_home'		=> 'CP home',
	'modcp_logs_action_moderate'		=> 'Moderate',
	'modcp_logs_action_members'		=> 'Edit User',
	'modcp_logs_action_forumaccess'		=> 'User Permissions',
	'modcp_logs_action_thread'		=> 'Threads Management',
	'modcp_logs_action_forum'		=> 'Forum Management',
	'modcp_logs_action_announcement'	=> 'Announcements',
	'modcp_logs_action_log'			=> 'Manage blogs',
	'modcp_logs_action_stat'		=> 'Moderator Statistics',

	'modcp_logs_action_login'	=> 'Login',

	'uch_selectalbum'		=> 'Select album',
	'uch_noalbum'			=> 'You have no albums©o ',
	'click_here'			=> 'Click here',
	'uch_createalbum'		=> ' to create new album!',
	


	'pm_from'		=> 'From',
	'pm_to'			=> 'To',
	'pm_date'		=> 'Date',

	'share_message'		=> 'Hello! I saw this thread at {$_G[setting][bbname]}©o I think it is valuable©o so I recommend it to you.\\n\\nTitle: $thread[subject]\\nURL: [url={$threadurl}]{$threadurl}[/url]\\n\\nHope you like it.',

	'week_0'	=> 'Sun',
	'week_1'	=> 'Mon',
	'week_2'	=> 'Tue',
	'week_3'	=> 'Wed',
	'week_4'	=> 'Thu',
	'week_5'	=> 'Fri',
	'week_6'	=> 'Sat',

	'notice_actor'		=> '©o and so on. Total: $actorcount people',

	'perms_allowvisit'	=> 'Forum access',
	'perms_readaccess'	=> 'Read permissions',
	'perms_allowviewpro'	=> 'View member info',
	'perms_allowinvisible'	=> 'Stealth',
	'perms_allowsearch'	=> 'Use search',
	'perms_allownickname'	=> 'Allow to use nickname',
	'perms_allowcstatus'	=> 'Custom status',
	'perms_allowpost'	=> 'Add thread',
	'perms_allowreply'	=> 'Reply post',
	'perms_allowpostpoll'	=> 'Add poll',
	'perms_allowvote'	=> 'Vote poll',
	'perms_allowpostreward'		=> 'Add award thread',
	

	'perms_allowpostactivity'	=> 'Add event',
	'perms_allowpostdebate'		=> 'Add debate',
	'perms_allowposttrade'		=> 'Add sale',
	'perms_maxsigsize'		=> 'Max signature length',
	'perms_allowsigbbcode'		=> 'Use BBCode in signature',
	'perms_allowsigimgcode'		=> 'Use [img] tag in signature',
	'perms_maxbiosize'		=> 'Max length of self-about',
	'perms_allowrecommend'		=> 'Allow to recomend topic',
	'perms_allowbiobbcode'		=> 'Use BBCode in self-about',
	'perms_allowbioimgcode'		=> 'Use [img] tag in self-about',
	'perms_allowgetattach'		=> 'Download/View Attachment',
	
	'perms_allowgetimage' => 'view image',
	
'perms_allowpostattach'		=> 'Upload attachment',
	'perms_allowsetattachperm'	=> 'Allow to set attachment permissions',
	'perms_maxspacesize'		=> 'Max Space Size',
	'perms_maxattachsize'		=> 'Max attach size',
	'perms_maxsizeperday'		=> 'Max attach size per day',
	'perms_maxattachnum'		=> 'Max number of attachments per day',
	'perms_allowbioimgcode'		=> 'Use [img] tag in self-about',
	'perms_attachextensions'	=> 'Attach type',
	'perms_allowstickthread'	=> 'Stick thread',
	'perms_allowdigestthread'	=> 'Digests',
	'perms_allowstickthread_value'	=> 'Stick',
	'perms_allowdigestthread_value'	=> 'Digests',
	'perms_allowbumpthread'		=> 'Bump',
	'perms_allowhighlightthread'	=> 'Hightlight',
	'perms_allowrecommendthread'	=> 'Recommend',
	'perms_allowstampthread'	=> 'Stamp',
	'perms_allowclosethread'	=> 'Close',
	'perms_allowmovethread'		=> 'Thread move',
	'perms_allowedittypethread'	=> 'Edit Type',
	

	'perms_allowcopythread'		=> 'Copy',
	'perms_allowmergethread'	=> 'Merge',
	'perms_allowsplitthread'	=> 'Split',
	'perms_allowrepairthread'	=> 'Repair',
	'perms_allowrefund'		=> 'Refund',
	'perms_alloweditpoll'		=> 'Edit poll',
	'perms_allowremovereward'	=> 'Remove Reward',
	'perms_alloweditactivity'	=> 'Event Edit',
	'perms_allowedittrade'		=> 'Product edit',
	'perms_alloweditpost'		=> 'Edit post',
	'perms_allowwarnpost'		=> 'Warn Posts',
	'perms_allowbanpost'		=> 'Ban posts',
	'perms_allowdelpost'		=> 'Delete post',
	'perms_allowviewreport'		=> 'View Report',
	'perms_allowmodpost'		=> 'Posts Moderation',
	'perms_allowmoduser'		=> 'User moderation',
	'perms_allowbanuser'		=> 'Ban user',
	'perms_allowbanip'		=> 'Ban IP',
	'perms_allowedituser'		=> 'Edit member',
	'perms_allowmassprune'		=> 'Bulk post delete',
	'perms_allowpostannounce'	=> 'Add announcement',
	'perms_disablepostctrl'		=> 'Not restricted',
	'perms_allowviewip'		=> 'View IP',
	'perms_viewperm'		=> 'View Forum',
	'perms_postperm'		=> 'Add thread',
	'perms_replyperm'		=> 'Reply post',
	'perms_getattachperm'		=> 'Download/View Attachment',
	'perms_postattachperm'		=> 'Upload Attachment',
	
	'perms_postimageperm' => 'Upload images',
'perms_allowblog'		=> 'Publish blog',
	'perms_allowdoing'		=> 'Write Twit',
	'perms_allowupload'		=> 'Upload image',
	'perms_allowshare'		=> 'Share',
	'perms_allowpoke'		=> 'Send Greeting',
	'perms_allowfriend'		=> 'Add friend',
	'perms_allowclick'		=> 'Rate',
	'perms_allowmyop'		=> 'Use apps',
	'perms_allowcomment'		=> 'Post Comment',
	'perms_allowstat'		=> 'View statistics',
	'perms_allowpostarticle'	=> 'Post article',
	
	'perms_raterange' => 'rate',
	'perms_allowsendpm' => 'allow send pm',
	'perms_maximagesize' => 'max image size',
	'perms_allowmediacode' => 'allow media code',


	'join_topic'		=> 'Join Topic',
	'join_poll'		=> 'Vote poll',
	'buy_trade'		=> 'Buy Product',
	'join_reward'		=> 'Join Reward',
	'join_activity'		=> 'Join Event',
	'join_debate'		=> 'Join Debate',

	'lower'			=> 'less than',
	'higher'		=> 'greater than',
	'report_msg_your' => 'you',
	'report_noreward' => 'no reward',
);

?>