<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_connect.php 27998 2012-02-20 09:33:38Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(

	'feed_sync_success' => '同步發 Feed 成功',
	'deletethread_sync_success' => '刪除主題同步成功',
	'deletethread_sync_failed' => '刪除主題同步失敗',
	'server_busy' => '抱歉，當前存在網絡問題或服務器繁忙，請您稍候再試。謝謝。',
    'share_token_outofdate' => '為了您的賬號安全，請使用QQ帳號重新登錄，將為您升級帳號安全機制<br/><br/>點擊<a href={login_url}><img src=static/image/common/qq_login.gif class=vm alt=QQ登錄 /></a>頁面將發生跳轉',
	'share_success' => '分享成功',
	'broadcast_success' => '轉播成功',

	'qzone_title' => '標題',
	'qzone_reason' => '理由',
	'qzone_picture' => '圖片',
	'qzone_shareto' => '分享到QQ空間',
	'qzone_to_friend' => '分享給好友',
	'qzone_reason_default' => '可以在這裡輸入分享原因或詳細內容',
	'qzone_subject_is_empty' => '分享標題不能為空',
	'qzone_subject_is_long' => '分享標題超過了長度限制',
	'qzone_reason_is_long' => '分享理由超過了長度限制',
    'qzone_share_same_url' => '該帖子您已經分享過，不需要重複分享',

	'weibo_title' => '分享到我的微博，順便說點什麼吧',
	'weibo_input' => '還能輸入<strong id=checklen></strong>字',
	'weibo_select_picture' => '請選擇分享圖片',
	'weibo_share' => '轉播',
	'weibo_share_to' => '轉播到騰訊微博',
	'weibo_reason_is_long' => '微博內容超過了長度限制',
    'weibo_same_content' => '該帖子您已經轉播過，不需要重複轉播',
	'weibo_account_not_signup' => '抱歉，您還未開通微博賬號，無法分享內容，<a href=http://t.qq.com/reg/index.php target=_blank style=color:#336699>點擊這裡馬上開通</a>',
	'user_unauthorized' => '抱歉，您未授權分享主題到QQ空間、騰訊微博和騰訊朋友，點擊<a href={login_url}><img src=static/image/common/qq_login.gif class=vm alt=QQ登錄 /></a>，馬上完善授權',

	'connect_errlog_server_no_response' => '服務器無響應',
	'connect_errlog_access_token_incomplete' => '接口返回的AccessToken數據不完整',
	'connect_errlog_request_token_not_authorized' => '用戶TmpToken未授權或返回的數據不完整',
	'connect_errlog_sig_incorrect' => 'URL簽名不正確',

	'connect_tthread_broadcast' => '轉播微博',
	'connect_tthread_message' => '<br><br><img class="vm" src="static/image/common/weibo.png">&nbsp;<a href="http://t.qq.com/{username}" target="_blank">來自 {nick} 的騰訊微博</a>',
	'connect_tthread_comment' => '微博評論',
);

?>