<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_notification.php 17019 2010-09-19 04:36:09Z liulanbo $
 */

$lang = array
(

	'type_wall' => '留言',
	'type_piccomment' => '圖片評論',
	'type_blogcomment' => '日誌評論',
	'type_clickblog' => '日誌表態',
	'type_clickarticle' => '文章表態',
	'type_clickpic' => '圖片表態',
	'type_sharecomment' => '分享評論',
	'type_doing' => '記錄',
	'type_friend' => '好友',
	'type_credit' => '積分',
	'type_bbs' => '論壇',
	'type_system' => '系統',
	'type_thread' => '主題',
	'type_task' => '任務',
	'type_group' => '群組',

	'mail_to_user' => '有新的通知',
	'showcredit' => '{actor} 贈送給你 {credit} 個競價積分，幫助提升在 <a href="misc.php?mod=ranklist&type=member" target="_blank">競價排行榜</a> 中的名次',
	'share_space' => '{actor} 分享了你的空間',
	'share_blog' => '{actor} 分享了你的日誌 <a href="{url}" target="_blank">{subject}</a>',
	'share_album' => '{actor} 分享了你的相冊 <a href="{url}" target="_blank">{albumname}</a>',
	'share_pic' => '{actor} 分享了你的相冊 {albumname} 中的 <a href="{url}" target="_blank"> 圖片</a>',
	'share_thread' => '{actor} 分享了你的帖子 <a href="{url}" target="_blank">{subject}</a>',
	'share_article' => '{actor} 分享了你的文章 <a href="{url}" target="_blank">{subject}</a>',
	'magic_present_note' => '送給你一個道具 <a href="{url}" target="_blank">{name}</a>',
	'friend_add' => '{actor} 和你成為了好友',
	'doing_reply' => '{actor} 在 <a href="{url}" target="_blank">記錄</a> 中對你進行了回復',
	'wall_reply' => '{actor} 回復了你的 <a href="{url}" target="_blank">留言</a>',
	'pic_comment_reply' => '{actor} 回復了你的 <a href="{url}" target="_blank">圖片評論</a>',
	'blog_comment_reply' => '{actor} 回復了你的 <a href="{url}" target="_blank">日誌評論</a>',
	'share_comment_reply' => '{actor} 回復了你的 <a href="{url}" target="_blank">分享評論</a>',
	'wall' => '{actor} 在留言板上給你 <a href="{url}" target="_blank">留言</a>',
	'pic_comment' => '{actor} 評論了你的 <a href="{url}" target="_blank">圖片</a>',
	'blog_comment' => '{actor} 評論了你的日誌 <a href="{url}" target="_blank">{subject}</a>',
	'share_comment' => '{actor} 評論了你的 <a href="{url}" target="_blank">分享</a>',
	'click_blog' => '{actor} 對你的日誌 <a href="{url}" target="_blank">{subject}</a> 做了表態',
	'click_pic' => '{actor} 對你的 <a href="{url}" target="_blank">圖片</a> 做了表態',
	'click_article' => '{actor} 對你的文章 <a href="{url}" target="_blank">{subject}</a> 做了表態',
	'show_out' => '{actor} 訪問了你的主頁後，你在競價排名榜中最後一個積分也被消費掉了',
	'puse_article' => '恭喜你，你的<a href="{url}" target="_blank">{subject}</a>已被添加到文章列表， <a href="{newurl}" target="_blank">點擊查看</a>',


	'group_member_join' => '{actor} 加入你的 <a href="forum.php?mod=group&fid={fid}" target="_blank">{groupname}</a> 群組需要審核，請到群組<a href="{url}" target="_blank">管理後台</a> 進行審核',
	'group_member_invite' => '{actor} 邀請你加入 <a href="forum.php?mod=group&fid={fid}" target="_blank">{groupname}</a> 群組，<a href="{url}" target="_blank">點此馬上加入</a>',
	'group_member_check' => '你已經通過了 <a href="{url}" target="_blank">{groupname}</a> 群組的審核，請 <a href="{url}" target="_blank">點擊訪問</a>',
	'group_member_check_failed' => '你沒有通過 <a href="{url}" target="_blank">{groupname}</a> 群組的審核。',

	'reason_moderate' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} {modaction} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_merge' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} {modaction} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_delete_post' => '你在 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的帖子被 {actor} 刪除 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_delete_comment' => '你在 <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> 的點評被 {actor} 刪除 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_ban_post' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} {modaction} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_warn_post' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} {modaction}<br />
連續 {warningexpiration} 天內累計 {warninglimit} 次警告，你將被自動禁止發帖 {warningexpiration} 天。<br />
截至目前，你已被警告 {authorwarnings} 次，請注意！<div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_move' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 移動到 <a href="forum.php?mod=forumdisplay&fid={tofid}" target="_blank">{toname}</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_copy' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 複製為 <a href="forum.php?mod=viewthread&tid={threadid}" target="_blank">{subject}</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_remove_reward' => '你的懸賞主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 撤銷 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamp_update' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 添加了圖章 {stamp} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamp_delete' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 撤銷了圖章 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamplist_update' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 添加了圖標 {stamp} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stamplist_delete' => '你的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 撤銷了圖標 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stickreply' => '你在主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的回帖被 {actor} 置頂 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'reason_stickdeletereply' => '你在主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的回帖被 {actor} 撤銷置頂 <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modthreads_delete' => '你發表的主題 {threadsubject} 沒有通過審核，現已被刪除！<div class="quote"><blockquote>{reason}</blockquote></div>',

	'modthreads_validate' => '你發表的主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{threadsubject}</a> 已經審核通過！ &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modreplies_delete' => '你發表回覆沒有通過審核，現已被刪除！ <p class="summary">回復內容：<span>{post}</span></p> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'modreplies_validate' => '你發表的回復已經審核通過！ &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">查看 &rsaquo;</a> <p class="summary">回復內容：<span>{post}</span></p> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'transfer' => '你收到一筆來自 {actor} 的積分轉賬 {credit} &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=log&suboperation=creditslog" target="_blank" class="lit">查看 &rsaquo;</a>
<p class="summary">{actor} 說：<span>{transfermessage}</span></p>',

	'addfunds' => '你提交的積分充值請求已成功完成，相應數額的積分已經存入你的積分賬戶 &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=base" target="_blank" class="lit">查看 &rsaquo;</a>
<p class="summary">訂單號：<span>{orderid}</span></p><p class="summary">支出：<span>人民幣 {price} 元</span></p><p class="summary">收入：<span>{value}</span></p>',

	'rate_reason' => '你在主題 <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> 的帖子被 {actor} 評分 {ratescore} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'rate_removereason' => '{actor} 撤銷了你在主題 <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> 中帖子的評分 {ratescore} <div class="quote"><blockquote>{reason}</blockquote></div>',

	'trade_seller_send' => '<a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> 購買你的商品 <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>，對方已經付款，等待你發貨 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'trade_buyer_confirm' => '你購買的商品 <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a>，<a href="home.php?mod=space&uid={sellerid}" target="_blank">{seller}</a> 已發貨，等待你確認 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'trade_fefund_success' => '商品 <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> 已退款成功 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">評價 &rsaquo;</a>',

	'trade_success' => '商品 <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> 已交易成功 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">評價 &rsaquo;</a>',

	'trade_order_update_sellerid' => '賣家 <a href="home.php?mod=space&uid={sellerid}" target="_blank">{seller}</a> 修改了商品 <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> 的交易單，請確認 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'trade_order_update_buyerid' => '買家 <a href="home.php?mod=space&uid={buyerid}" target="_blank">{buyer}</a> 修改了商品 <a href="forum.php?mod=trade&orderid={orderid}" target="_blank">{subject}</a> 的交易單，請確認 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'eccredit' => '與你交易的 {actor} 已經給你作了評價 &nbsp; <a href="forum.php?mod=trade&orderid={orderid}" target="_blank" class="lit">回評 &rsaquo;</a>',

	'activity_notice' => '{actor} 申請加入你舉辦的活動 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a>，請審核 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'activity_apply' => '活動 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的發起人 {actor} 已批准你參加此活動 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_replenish' => '活動 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的發起人 {actor} 通知你需要完善活動報名信息 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_delete' => '活動 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的發起人 {actor} 拒絕你參加此活動 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}"  target="_blank" class="lit">查看 &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_cancel' => '{actor} 取消了參加 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 活動 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}"  target="_blank" class="lit">查看 &rsaquo;</a> <div class="quote"><blockquote>{reason}</blockquote></div>',

	'activity_notification' => '活動 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的發起人 {actor} 發來通知&nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看活動 &rsaquo;</a> <div class="quote"><blockquote>{msg}</blockquote></div>',

	'reward_question' => '你的懸賞主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 被 {actor} 設置了最佳答案 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'reward_bestanswer' => '你的回復被懸賞主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 的作者 {actor} 選為懸賞最佳答案 &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'comment_add' => '{actor} 點評了你曾經在主題 <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> 發表的帖子 &nbsp; <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank" class="lit">查看 &rsaquo;</a>',

	'repquote_noticeauthor' => '{actor} 引用了你的帖子 <a href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">{subject}</a> &nbsp; <a class="lit" href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">查看</a>',

	'reppost_noticeauthor' => '{actor} 答覆了你的帖子 <a href="forum.php?mod=redirect&goto=findpost&ptid={tid}&pid={pid}" target="_blank">{subject}</a> &nbsp; <a class="lit" href="forum.php?mod=redirect&goto=findpost&pid={pid}&ptid={tid}" target="_blank">查看</a>',

	'task_reward_credit' => '恭喜你完成任務：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>，獲得積分 {creditbonus} &nbsp; <a href="home.php?mod=spacecp&ac=credit&op=base" target="_blank" class="lit">查看我的積分 &rsaquo;</a></p>',

	'task_reward_magic' => '恭喜你完成任務：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>，獲得道具 <a href="home.php?mod=magic&action=mybox" target="_blank">{rewardtext}</a> {bonus} 張',

	'task_reward_medal' => '恭喜你完成任務：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>，獲得勳章 <a href="forum.php?mod=medal" target="_blank">{rewardtext}</a> 有效期 {bonus} 天',

	'task_reward_invite' => '恭喜你完成任務：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>，獲得<a href="home.php?mod=spacecp&ac=invite" target="_blank">邀請碼 {rewardtext}個</a> 有效期 {bonus} 天',

	'task_reward_group' => '恭喜你完成任務：<a href="home.php?mod=task&do=view&id={taskid}" target="_blank">{name}</a>，獲得用戶組 {rewardtext} 有效期 {bonus} 天 &nbsp; <a href="home.php?mod=spacecp&ac=usergroup" target="_blank" class="lit">看看我能做什麼 &rsaquo;</a>',

	'user_usergroup' => '你的用戶組升級為 {usergroup} &nbsp; <a href="home.php?mod=spacecp&ac=usergroup" target="_blank" class="lit">看看我能做什麼 &rsaquo;</a>',

	'grouplevel_update' => '恭喜你，你的群組 {groupname} 已經升級到了 {newlevel}。',

	'thread_invite' => '{actor} 邀請你{invitename} <a href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a> &nbsp; <a href="forum.php?mod=viewthread&tid={tid}" target="_blank" class="lit">查看 &rsaquo;</a>',
	'invite_friend' => '恭喜你成功邀請到 {actor} 並成為你的好友',

	'profile_verify_error' => '{verify}資料審核被拒絕,以下字段需要重新填寫:<br/>{profile}<br/>拒絕理由:{reason}',
	'profile_verify_pass' => '恭喜你，你填寫的{verify}資料審核通過了',
	'profile_verify_pass_refusal' => '很遺憾，你填寫的{verify}資料審核被拒絕了',
	'member_ban_speak' => '你已被 {user} 禁止發言，期限：{day}天(0：代表永久禁言)，禁言理由：{reason}',

	'member_moderate_invalidate' => '你的賬號未能通過管理員的審核，請<a href="home.php?mod=spacecp&ac=profile">重新提交註冊信息</a>。<br />管理員留言: <b>{remark}</b>',
	'member_moderate_validate' => '你的賬號已經通過審核。<br />管理員留言: <b>{remark}</b>',
	'member_moderate_invalidate_no_remark' => '你的賬號未能通過管理員的審核，請<a href="home.php?mod=spacecp&ac=profile">重新提交註冊信息</a>。',
	'member_moderate_validate_no_remark' => '你的賬號已經通過審核。',

	'system_notice' => '{subject}<p class="summary">{message}</p>',
	'report_change_credits' => '{actor} 處理了你的舉報，你的 {creditchange}',
	'new_report' => '有新的舉報等待處理，<a href="admin.php?action=report" target="_blank">點此進入後台處理</a>。',
	'magics_receive' => '你收到 {actor} 送給你的道具 {magicname}
<p class="summary">{actor} 說：<span>{msg}</span></p>
<p class="mbn"><a href="home.php?mod=magic" target="_blank">回贈道具</a><span class="pipe">|</span><a href="home.php?mod=magic&action=mybox" target="_blank">查看我的道具箱</a></p>',

);

?>