<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_spacecp.php 16371 2010-09-06 02:26:47Z wangjinbo $
 */

$lang = array(

	'by' => '通過',
	'tab_space' => ' ',

	'share' => '分享',
	'share_action' => '分享了',

	'pm_comment' => '答覆點評',
	'pm_thread_about' => '關於你在「{subject}」的帖子',

	'wall_pm_subject' => '你好，我給你留言了',
	'wall_pm_message' => '我在你的留言板給你留言了，[url=\\1]點擊這裡去留言板看看吧[/url]',
	'reward' => '懸賞',
	'reward_info' => '參與投票可獲得  \\1 積分',
	'poll_separator' => '"、"',


	'friend_subject' => '<a href="{url}" target="_blank">{username} 請求加你為好友</a>',
	'comment_friend' =>'<a href="\\2" target="_blank">\\1 給你留言了</a>',
	'photo_comment' => '<a href="\\2" target="_blank">\\1 評論了你的照片</a>',
	'blog_comment' => '<a href="\\2" target="_blank">\\1 評論了你的日誌</a>',
	'poll_comment' => '<a href="\\2" target="_blank">\\1 評論了你的投票</a>',
	'share_comment' => '<a href="\\2" target="_blank">\\1 評論了你的分享</a>',
	'friend_pm' => '<a href="\\2" target="_blank">\\1 給你發私信了</a>',
	'poke_subject' => '<a href="\\2" target="_blank">\\1 向你打招呼</a>',
	'mtag_reply' => '<a href="\\2" target="_blank">\\1 回復了你的話題</a>',
	'event_comment' => '<a href="\\2" target="_blank">\\1 評論了你的活動</a>',

	'friend_pm_reply' => '\\1 回復了你的私信',
	'comment_friend_reply' => '\\1 回復了你的留言',
	'blog_comment_reply' => '\\1 回復了你的日誌評論',
	'photo_comment_reply' => '\\1 回復了你的照片評論',
	'poll_comment_reply' => '\\1 回復了你的投票評論',
	'share_comment_reply' => '\\1 回復了你的分享評論',
	'event_comment_reply' => '\\1 回復了你的活動評論',

	'invite_subject' => '{username}邀請你加入{sitename}，並成為好友',
	'invite_massage' => '<table border="0">
		<tr>
		<td valign="top">{avatar}</td>
		<td valign="top">
		<h3>Hi，我是{username}，邀請你也加入{sitename}並成為我的好友</h3><br>
		請加入到我的好友中，你就可以瞭解我的近況，與我一起交流，隨時與我保持聯繫。<br>
		<br>
		邀請附言：<br>{saymsg}
		<br><br>
		<strong>請你點擊以下鏈接，接受好友邀請：</strong><br>
		<a href="{inviteurl}">{inviteurl}</a><br>
		<br>
		<strong>如果你擁有{sitename}上面的賬號，請點擊以下鏈接查看我的個人主頁：</strong><br>
		<a href="{siteurl}home.php?mod=space&uid={uid}">{siteurl}home.php?mod=space&uid={uid}</a><br>
		</td></tr></table>',

	'app_invite_subject' => '{username}邀請你加入{sitename}，一起來玩{appname}',
	'app_invite_massage' => '<table border="0">
		<tr>
		<td valign="top">{avatar}</td>
		<td valign="top">
		<h3>Hi，我是{username}，在{sitename}上玩 {appname}，邀請你也加入一起玩</h3><br>
		<br>
		邀請附言：<br>
		{saymsg}
		<br><br>
		<strong>請你點擊以下鏈接，接受好友邀請一起玩{appname}：</strong><br>
		<a href="{inviteurl}">{inviteurl}</a><br>
		<br>
		<strong>如果你擁有{sitename}上面的賬號，請點擊以下鏈接查看我的個人主頁：</strong><br>
		<a href="{siteurl}home.php?mod=space&uid={uid}">{siteurl}home.php?mod=space&uid={uid}</a><br>
		</td></tr></table>',

	'person' => '人',
	'delete' => '刪除',

	'space_update' => '{actor} 被SHOW了一下',

	'active_email_subject' => '你的郵箱激活郵件',
	'active_email_msg' => '請複製下面的激活鏈接到瀏覽器進行訪問，以便激活你的郵箱。<br>郵箱激活鏈接:<br><a href="{url}" target="_blank">{url}</a>',
	'share_space' => '分享了一個用戶',
	'share_blog' => '分享了一篇日誌',
	'share_album' => '分享了一個相冊',
	'default_albumname' => '默認相冊',
	'share_image' => '分享了一張圖片',
	'share_article' => '分享了一篇文章',
	'album' => '相冊',
	'share_thread' => '分享了一個帖子',
	'mtag' => '{$_G[setting][navs][3][navname]}',
	'share_mtag' => '分享了一個{$_G[setting][navs][3][navname]}',
	'share_mtag_membernum' => '現有 {membernum} 名成員',
	'share_tag' => '分享了一個標籤',
	'share_tag_blognum' => '現有 {blognum} 篇日誌',
	'share_link' => '分享了一個網址',
	'share_video' => '分享了一個視頻',
	'share_music' => '分享了一個音樂',
	'share_flash' => '分享了一個 Flash',
	'share_event' => '分享了一個活動',
	'share_poll' => '分享了一個\\1投票',
	'event_time' => '活動時間',
	'event_location' => '活動地點',
	'event_creator' => '發起人',
	'the_default_style' => '默認風格',
	'the_diy_style' => '自定義風格',

	'thread_edit_trail' => '<ins class="modify">[本話題由 \\1 於 \\2 編輯]</ins>',
	'create_a_new_album' => '創建了新相冊',
	'not_allow_upload' => '你現在沒有權限上傳圖片',
	'get_passwd_subject' => '取回密碼郵件',
	'get_passwd_message' => '你只需在提交請求後的三天之內，通過點擊下面的鏈接重置你的密碼：<br />\\1<br />(如果上面不是鏈接形式，請將地址手工粘貼到瀏覽器地址欄再訪問)<br />上面的頁面打開後，輸入新的密碼後提交，之後你即可使用新的密碼登錄了。',
	'file_is_too_big' => '文件過大',

	'take_part_in_the_voting' => '{actor} 參與了 {touser} 的{reward}投票 <a href="{url}" target="_blank">{subject}</a>',
	'lack_of_access_to_upload_file_size' => '無法獲取上傳文件大小',
	'only_allows_upload_file_types' => '只允許上傳jpg、jpeg、gif、png標準格式的圖片',
	'unable_to_create_upload_directory_server' => '服務器無法創建上傳目錄',
	'inadequate_capacity_space' => '空間容量不足，不能上傳新附件',
	'mobile_picture_temporary_failure' => '無法轉移臨時文件到服務器指定目錄',
	'ftp_upload_file_size' => '遠程上傳圖片失敗',
	'comment' => '評論',
	'upload_a_new_picture' => '上傳了新圖片',
	'upload_album' => '更新了相冊',
	'the_total_picture' => '共 \\1 張圖片',

	'space_open_subject' => '快來打理一下你的個人主頁吧',
	'space_open_message' => 'hi，我今天去拜訪了一下你的個人主頁，發現你自己還沒有打理過呢。趕快來看看吧。地址是：\\1space.php',



	'apply_mtag_manager' => '想申請成為 <a href="\\1" target="_blank">\\2</a> 的群主，理由如下:\\3。<a href="\\1" target="_blank">(點擊這裡進入管理)</a>',


	'magicunit' => '個',
	'magic_note_wall' => '{actor}在留言板上給你<a href="{url}" target="_blank">留言</a>',
	'magic_call' => '在日誌中點了你的名，<a href="{url}" target="_blank">快去看看吧</a>',


	'present_user_magics' => '你收到了管理員贈送的道具：\\1',
	'has_not_more_doodle' => '你沒有塗鴉板了',

	'do_stat_login' => '來訪用戶',
	'do_stat_register' => '新註冊用戶',
	'do_stat_invite' => '好友邀請',
	'do_stat_appinvite' => '應用邀請',
	'do_stat_add' => '信息發佈',
	'do_stat_comment' => '信息互動',
	'do_stat_space' => '用戶互動',
	'do_stat_login' => '來訪用戶',
	'do_stat_doing' => '記錄',
	'do_stat_blog' => '日誌',
	'do_stat_activity' => '活動',
	'do_stat_reward' => '懸賞',
	'do_stat_debate' => '辯論',
	'do_stat_trade' => '商品',
	'do_stat_group' => '群組',
	'do_stat_groupthread' => '群組主題',
	'do_stat_post' => '主題回復',
	'do_stat_grouppost' => '群組回復',
	'do_stat_pic' => '圖片',
	'do_stat_poll' => '投票',
	'do_stat_event' => '活動',
	'do_stat_share' => '分享',
	'do_stat_thread' => '主題',
	'do_stat_docomment' => '記錄回復',
	'do_stat_blogcomment' => '日誌評論',
	'do_stat_piccomment' => '圖片評論',
	'do_stat_pollcomment' => '投票評論',
	'do_stat_pollvote' => '參與投票',
	'do_stat_eventcomment' => '活動評論',
	'do_stat_eventjoin' => '參加活動',
	'do_stat_sharecomment' => '分享評論',
	'do_stat_post' => '主題回帖',
	'do_stat_click' => '表態',
	'do_stat_wall' => '留言',
	'do_stat_poke' => '打招呼',
	'do_stat_post_number' => '發帖量',

	'profile_unchangeable' => '此項資料提交後不可修改',
	'profile_is_verifying' => '此項資料正在審核中',
	'profile_mypost' => '我提交的內容',
	'profile_need_verifying' => '此項資料提交後需要審核',
	'profile_edit' => '修改',
	'profile_censor' => '(含有敏感詞彙)',
	'profile_verify_modify_error' => '{verify}已經認證通過不允許修改',

	'district_level_1' => '-省份-',
	'district_level_2' => '-城市-',
	'district_level_3' => '-州縣-',
	'district_level_4' => '-鄉鎮-',

	'spacecp_message_prompt' => '(支持 {msg} 代碼,最大 1000 字)',

);

?>