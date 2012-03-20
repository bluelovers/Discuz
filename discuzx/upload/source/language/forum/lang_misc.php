<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_misc.php 28828 2012-03-14 07:55:21Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'discuz_lang' => 'misc',
	'contact' => '聯繫方式:',
	'anonymous' => '匿名',
	'anonymoususer' => '匿名者',
	'guestuser' => '遊客',
	'has_expired' => '該信息已經過期',
	'click_view' => '點擊查看',
	'never_expired' => '永不過期',
	'sort_update' => '更新',
	'sort_upload' => '上傳',
  	'view_noperm' => '隱藏內容',
	'post_hidden' => '**** 本內容被作者隱藏 ****',
	'post_banned' => '**** 作者被禁止或刪除 內容自動屏蔽 ****',
	'post_single_banned' => '**** 該帖被屏蔽 ****',
	'message_ishidden_hiddenreplies' => '此帖僅作者可見',
	'post_reply_quote' => '{author} 發表於 {time}',
	'post_edit' => "[i=s] 本帖最後由 {editor} 於 {edittime} 編輯 [/i]\n\n",
	'post_edit_regexp' => '/^\[i=s\] 本帖最後由 .*? 於 .*? 編輯 \[\/i\]\n\n/s',
	'post_edithtml' => '[i=s] 本帖最後由 {editor} 於 {edittime} 編輯 [/i]<br /><br />',
	'post_edithtml_regexp' => '/^\[i=s\] 本帖最後由 .*? 於 .*? 編輯 \[\/i\]&lt;br \/&gt;&lt;br \/&gt;/s',
	'post_editnobbcode' => '[ 本帖最後由 {editor} 於 {edittime} 編輯 ]\n\n',
	'post_editnobbcode_regexp' => '/^\[ 本帖最後由 .*? 於 .*? 編輯 \]\n\n/s',
	'post_reply' => '回復',
	'post_thread' => '的帖子',

	'price' => '售價',
	'pay_view' => '記錄',
	'attachment_buy' => '購買',

	'post_trade_yuan' => '元',
	'post_trade_seller' => '賣家',
	'post_trade_name' => '商品名稱',
	'post_trade_price' => '商品價格',
	'post_trade_quality' => '商品成色',
	'post_trade_locus' => '所在地點',
	'post_trade_transport_type' => '物流方式',
	'post_trade_transport_seller' => '賣家承擔運費',
	'post_trade_transport_buyer' => '買家承擔運費',
	'post_trade_transport_mail' => '平郵',
	'post_trade_transport_express' => '快遞',
	'post_trade_transport_virtual' => '虛擬物品或無需郵遞',
	'post_trade_transport_physical' => '買家收到貨物後直接支付給物流公司',
	'post_trade_locus' => '所在地點',
	'post_trade_description' => '商品描述',
	'post_trade_pm_subject' => '[議價]',
	'post_trade_pm_buynum' => '購買數量',
	'post_trade_pm_wishprice' => '我期望的價格是',
	'post_trade_pm_reason' => '我議價的理由是',
	'postappend_content' => '補充內容',
	'payment_unit' => '元',

	'attach' => '附件',
	'attach_pay' => '收費附件',
	'attach_credits_policy' => '查看積分策略說明',
	'attach_img' => '圖片附件',
	'attach_readperm' => '閱讀權限',
	'attach_img_zoom' => '點擊在新窗口查看全圖\\nCTRL+鼠標滾輪放大或縮小',
	'attach_img_thumb' => '點擊在新窗口查看全圖',
	'attach_downloads' => '下載次數',

	'post_trade_transport' => '郵費',
	'post_trade_transport_mail' => '平郵',
	'post_trade_quality' => '商品成色',
	'post_trade_quality_new' => '全新',
	'post_trade_quality_secondhand' => '二手',

	'trade_unstart' => '<font color="gray">未生效的交易</font>',
	'trade_waitbuyerpay' => '等待買家付款',
	'trade_waitsellerconfirm' => '交易已創建，等待賣家確認',
	'trade_sysconfirmpay' => '確認買家付款中，暫勿發貨',
	'trade_waitsellersend' => '買家已付款，等待賣家發貨',
	'trade_waitbuyerconfirm' => '賣家已發貨，買家確認中',
	'trade_syspayseller' => '買家確認收到貨，等待支付寶打款給賣家',
	'trade_finished' => '<font color="green">交易成功結束</font>',
	'trade_closed' => '<font color="gray">交易中途關閉(未完成)</font>',
	'trade_waitselleragree'  => '等待賣家同意退款',
	'trade_sellerrefusebuyer' => '賣家拒絕買家條件，等待買家修改條件',
	'trade_waitbuyerreturn' => '賣家同意退款，等待買家退貨',
	'trade_waitsellerconfirmgoods' => '等待賣家收貨',
	'trade_waitalipayrefund' => '雙方已經一致，等待支付寶退款',
	'trade_alipaycheck' => '支付寶處理中',
	'trade_overedrefund' => '結束的退款',
	'trade_refundsuccess' => '<font color="green">退款成功</font>',
	'trade_refundclosed' => '<font color="green">退款關閉</font>',

	'trade_offline_1' => '交易單生效',
	'trade_offline_4' => '我已付款，等待賣家發貨',
	'trade_offline_5' => '我已發貨',
	'trade_offline_7' => '我收到貨，交易成功結束',
	'trade_offline_8' => '取消此次交易',
	'trade_offline_10' => '我要退貨，等待賣家同意退款',
	'trade_offline_11' => '賣家拒絕退款',
	'trade_offline_12' => '賣家同意退款',
	'trade_offline_13' => '我已退貨，等待賣家收貨',
	'trade_offline_17' => '賣家收到退貨，已退款',

	'trade_message_4' => '可輸入付款方式、銀行賬號等信息',
	'trade_message_5' => '可輸入發貨公司、發貨單號等信息',
	'trade_message_13' => '可輸入發貨公司、發貨單號等信息',

	'credit_payment' => '積分充值',
	'credit_forum_payment' => '論壇積分充值',
	'credit_forum_royalty' => '交易手續費',

	'credit_total' => '總積分',

	'invite_payment' => '購買邀請碼',
	'invite_forum_payment' => '購買邀請碼',
	'invite_forum_payment_unit' => '個',
	'invite_forum_royalty' => '交易手續費',

	'formulaperm_regdate' => '註冊時間',
	'formulaperm_regday' => '註冊天數',
	'formulaperm_regip' => '註冊 IP',
	'formulaperm_lastip' => '最後登錄 IP',
	'formulaperm_buyercredit' => '買家信用評價',
	'formulaperm_sellercredit' => '賣家信用評價',
	'formulaperm_digestposts' => '精華帖數',
	'formulaperm_posts' => '發帖數',
	'formulaperm_threads' => '主題數',
	'formulaperm_oltime' => '在線時間(小時)',
	'formulaperm_pageviews' => '頁面瀏覽量',
	'formulaperm_and' => '並且',
	'formulaperm_or' => '或者',
	'formulaperm_extcredits' => '自定義積分',

	'login_normal_mode' => '在線',
	'login_switch_invisible_mode' => '切換在線狀態',
	'login_switch_normal_mode' => '我要上線',
	'login_invisible_mode' => '隱身',

	'eccredit_explain' => '解釋',

	'google_site_0' => '網頁搜索',
	'google_site_1' => '站內搜索',
	'google_sa' => '搜索',

	'modcp_logs_action_home' => '內部留言',
	'modcp_logs_action_moderate' => '審核',
	'modcp_logs_action_member' => '用戶管理',
	'modcp_logs_action_forumaccess' => '用戶權限',
	'modcp_logs_action_thread' => '主題管理',
	'modcp_logs_action_forum' => '版塊管理',
	'modcp_logs_action_announcement' => '公告',
	'modcp_logs_action_log' => '管理日誌',
	'modcp_logs_action_stat' => '管理統計',

	'modcp_logs_action_login' => '登錄',

	'uch_selectalbum' => '請選擇相冊',
	'uch_noalbum' => '抱歉，您還沒有相冊，',
	'click_here' => '點擊這裡',
	'uch_createalbum' => '創建自己的相冊吧！',

	'pm_from' => '發件人',
	'pm_to' => '收件人',
	'pm_date' => '日期',

	'share_message' => '您好！我在 {$_G[setting][bbname]} 看到了這篇帖子，認為很有價值，特推薦給您。\\n\\n$thread[subject]\\n地址 [url={$threadurl}]{$threadurl}[/url]\\n\\n希望您能喜歡',

	'week_0' => '星期日',
	'week_1' => '星期一',
	'week_2' => '星期二',
	'week_3' => '星期三',
	'week_4' => '星期四',
	'week_5' => '星期五',
	'week_6' => '星期六',

	'notice_actor' => '等 $actorcount 人',

	'perms_allowvisit' => '訪問論壇',
	'perms_readaccess' => '閱讀權限',
	'perms_allowviewpro' => '查看用戶資料',
	'perms_allowinvisible' => '隱身',
	'perms_allowsearch' => '使用搜索',
	'perms_allownickname' => '使用暱稱',
	'perms_allowcstatus' => '自定義頭銜',
	'perms_allowpost' => '發新話題',
	'perms_allowreply' => '發表回復',
	'perms_allowpostpoll' => '發起投票',
	'perms_allowvote' => '參與投票',
	'perms_allowpostreward' => '發表懸賞',
	'perms_allowpostactivity' => '發表活動',
	'perms_allowpostdebate' => '發表辯論',
	'perms_allowposttrade' => '發表交易',
	'perms_maxsigsize' => '最大簽名長度',
	'perms_allowsigbbcode' => '簽名中使用編輯器代碼',
	'perms_allowsigimgcode' => '簽名中使用 [img] 代碼',
	'perms_maxbiosize' => '自我介紹最大長度',
	'perms_allowrecommend' => '主題評價影響值',
	'perms_allowbiobbcode' => '自我介紹中使用編輯器代碼',
	'perms_allowbioimgcode' => '自我介紹中使用 [img] 代碼',
	'perms_allowgetattach' => '下載附件',
	'perms_allowgetimage' => '查看圖片',
	'perms_allowpostattach' => '上傳附件',
	'perms_allowpostimage' => '上傳圖片',
	'perms_allowsetattachperm' => '允許設置附件權限',
	'perms_maxspacesize' => '空間大小',
	'perms_maxattachsize' => '單個最大附件尺寸',
	'perms_maxsizeperday' => '每天最大附件總尺寸',
	'perms_maxattachnum' => '每天最大附件數量',
	'perms_allowbioimgcode' => '自我介紹中使用 [img] 代碼',
	'perms_attachextensions' => '附件類型',
	'perms_allowstickthread' => '主題置頂',
	'perms_allowdigestthread' => '主題精華',
	'perms_allowstickthread_value' => '置頂',
	'perms_allowdigestthread_value' => '精華',
	'perms_allowbumpthread' => '提升主題',
	'perms_allowhighlightthread' => '主題高亮',
	'perms_allowrecommendthread' => '主題推薦',
	'perms_allowstampthread' => '主題鑒定',
	'perms_allowclosethread' => '主題關閉',
	'perms_allowmovethread' => '主題移動',
	'perms_allowedittypethread' => '編輯主題分類',
	'perms_allowcopythread' => '主題複製',
	'perms_allowmergethread' => '主題合併',
	'perms_allowsplitthread' => '主題分割',
	'perms_allowrepairthread' => '主題修復',
	'perms_allowrefund' => '強制退款',
	'perms_alloweditpoll' => '編輯投票',
	'perms_allowremovereward' => '移除懸賞',
	'perms_alloweditactivity' => '管理活動',
	'perms_allowedittrade' => '管理商品',
	'perms_alloweditpost' => '編輯帖子',
	'perms_allowwarnpost' => '警告帖子',
	'perms_allowbanpost' => '屏蔽帖子',
	'perms_allowdelpost' => '刪除帖子',
	'perms_allowviewreport' => '查看用戶報告',
	'perms_allowmodpost' => '審核帖子',
	'perms_allowmoduser' => '審核用戶',
	'perms_allowbanuser' => '禁止用戶',
	'perms_allowbanip' => '禁止 IP',
	'perms_allowedituser' => '編輯用戶',
	'perms_allowmassprune' => '批量刪帖',
	'perms_allowpostannounce' => '發佈公告',
	'perms_disablepostctrl' => '發帖不受限制',
	'perms_allowviewip' => '允許查看 IP',
	'perms_viewperm' => '瀏覽版塊',
	'perms_postperm' => '發新話題',
	'perms_replyperm' => '發表回復',
	'perms_getattachperm' => '下載附件',
	'perms_postattachperm' => '上傳附件',
	'perms_postimageperm' => '上傳圖片',
	'perms_allowblog' => '發表日誌',
	'perms_allowdoing' => '發表記錄',
	'perms_allowupload' => '上傳圖片',
	'perms_allowshare' => '發佈分享',
	'perms_allowpoke' => '允許打招呼',
	'perms_allowfriend' => '允許加好友',
	'perms_allowclick' => '允許表態',
	'perms_allowmyop' => '允許使用應用',
	'perms_allowcomment' => '發表留言/評論',
	'perms_allowstatdata' => '查看統計數據報表',
	'perms_allowstat' => '允許查看趨勢統計',
	'perms_allowpostarticle' => '發表文章',
	'perms_raterange' => '允許參與評分',
	'perms_allowcommentpost' => '允許參與點評',
	'perms_allowat' => '允許 @ 的人數',
	'perms_allowreplycredit' => '允許設置回帖獎勵',
	'perms_allowposttag' => '允許使用標籤',
	'perms_allowcreatecollection' => '允許創建淘專輯的數量',
	'perms_allowsendpm' => '允許發短消息',
	'perms_maximagesize' => '單張圖片最大尺寸',
	'perms_allowmediacode' => '允許使用多媒體代碼',

	'join_topic' => '參與話題',
	'join_poll' => '參與投票',
	'buy_trade' => '購買商品',
	'join_reward' => '參與懸賞',
	'join_activity' => '參與活動',
	'join_debate' => '參與辯論',
	'at_invite' => '@我的好友',

	'lower' => '低於',
	'higher' => '高於',
	'report_msg_your' => '您的 ',
	'report_noreward' => '不獎懲',
	'activity_viewimg' => '點擊查看',

	'crime_postreason' => '{reason} &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="xi2">查看詳情</a>',
	'crime_reason' => '{reason}',

	'connectguest_message_search' => array('尚未登錄', '先登錄'),
	'connectguest_message_replace' => array('尚未 <a href="member.php?mod=connect">完善帳號信息</a> 或 <a href="member.php?mod=connect&ac=bind">綁定已有帳號</a> ', '您需要先 <a href="member.php?mod=connect">完善帳號信息</a> 或 <a href="member.php?mod=connect&ac=bind">綁定已有帳號</a> '),

	'avatar' => '頭像',
	'signature' => '簽名',
	'custom_title' => '自定義頭銜',

	'forum_guide' => '導讀',

	'patch_site_have' => '您的網站有',
	'patch_is_fixed' => '個安全漏洞，已修復',
	'patch_need_fix' => '個安全漏洞，請盡快修復',
	'patch_fixed_status' => '已修復',
	'patch_unfix_status' => '未修復',
	'patch_fix_failed_status' => '修復失敗',
	'patch_fix_right_now' => '立即修復',
	'patch_view_fix_detail' => '查看詳情',
	'patch_name' => '漏洞名稱',
	'patch_dateline' => '發佈日期',
	'patch_status' => '當前狀態',
	'patch_close' => '關閉',

);

?>