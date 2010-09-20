<?

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: message.lang.php 4456 2010-09-14 13:45:18Z yexinhao $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

$mlang = array (
	'title' => '提示消息',
	'back' => '返回上一頁',
	'index' => '進入首頁',
	'confirm' => '確定',
	'close' => '關閉',
	'admin_login' => '您無權訪問管理面板，請重新登錄!',
	'do_success' => '進行的操作完成了',
	'comment_submit_error' => '對不起，咨詢信息或點評信息不符合填寫要求，請返回重新填寫',
	'site_close' => '站點臨時關閉，請稍後再訪問',
	'comment_fobidden' => '您好，站點未開啟評論',
	'not_found' => '您好，您訪問的頁面不存在，請返回',
	'not_found_msg' => '您好，您要查看的頁面內容信息沒有找到',
	'not_view' => '您好，您要查看的信息沒有公開發佈',
	'no_permission' => '對不起，您所在用戶組沒有權限進行本次操作',
	'noperm_forremark' => '對不起，站點設置為不允許點評',
	'noperm_forcomment' => '對不起，該對像被設置為不允許評論',
	'notcomment_allscoreoption' => '出錯了，您必須點評所有評分選項，請返回重新填寫。',
	'seccode_error' => '您好，您輸入的驗證碼不正確，請確認',
	'no_login' => '出錯了，請您先登錄系統後再進行本操作',
	'system_error' => '出錯了，您的操作不正確，請檢查您的操作',
	'message_length_error' => '出錯了，您輸入的內容長度不符合要求，請返回檢查',
	'no_reply' => '出錯了，您沒有權限對該主題進行評論，請返回',
	'login_error' => '出錯了，您輸入的賬號信息不正確，請嘗試重新登錄',
	'enter_the_password_is_incorrect' => '輸入的密碼不正確，請重新嘗試',
	'excessive_number_of_attempts_to_sign' => '您30分鐘內嘗試登錄管理平台的次數超過了3次，為了數據安全，請稍候再試',
	'user_delete' => '用戶被刪除，請聯繫管理員',
	'login_succeed' => '操作完成，您已經成功登錄站點系統了 {ucsynlogin}',
	'logout_succeed' => '操作完成，您已經成功退出站點系統了 {ucsynlogin}',
	'poll_repeat' => '出錯了，您已經投過票了，不能重複投票',
	'no_votekey' => '出錯了，您沒有選擇要投票的選項',
	'the_system_does_not_allow_searches'=>'您未登錄，系統不允許搜索',
	'inquiries_about_the_short_time_interval'=>'出錯了，您兩次查詢的時間間隔太短，請稍後再繼續搜索',
	'not_find_relevant_data'=>'沒有找到相關數據，請更換查詢關鍵字試試',
	'search_types_of_incorrect_information'=>'出錯了，搜索信息類型不正確',
	'keyword_import_inquiry'=>'出錯了，請輸入您要查詢的關鍵字',
	'kwyword_import_short' => '出錯了,輸入的關鍵字長度需大於2個字符',
	'page_limit' => '出錯了，您要查看頁數太大了，請選擇其他條件查看列表',
	'view_images_do_not_exist' => '出錯了，查看的圖片不存在',
	'error_view' => '出錯了，相冊不存在或者您沒有權限查看',
	'credit_not_enough' => '您的積分不足以支付此次操作。',

	//admincp.php
	'admincp_login' => '您沒有登錄站內系統，請先登錄',

	//attend.php
	'applicant_info_failed' => '<span style="font-size:14px;font-weight:700;">您填寫的信息有誤，請根據提示重新填寫。</span>',
	'user_info_failed' => '<span style="font-size:14px;font-weight:700;">您填寫的信息有誤，請根據提示重新填寫。</span>',
	'apm_panel_msg' => '<span style="color:#009900;font-size:14px;font-weight:700;">尊敬的商家，您的申請我們已經收到，請耐心等待工作人員審核！在此期間請您注意查收站內短信！</span>',
	'no_submit' => '沒有提交數據',
	'attend_register_success' => '<span style="color:#009900;font-size:14px;font-weight:700;">尊敬的商家，恭喜您已經成功入住。</span>',

	//batch.comment.php
	'words_can_not_publish_the_shield' => '出錯了，您輸入的內容中因為含有被屏蔽的詞語而不能發佈',
	'comment_too_much' => '您評論太快了，稍等半分鐘再試試',

	//register.php
	'start_listcount_error' => '出錯了，您要查看頁數不存在',
	'not_found_tag' => '暫時沒有找到指定的tag信息',

	//do_register.php
	'incorrect_code' => '驗證碼填寫錯誤，請重新填寫',
	'seccode_notwrite' => '沒有輸入驗證碼，請重新填寫',
	'submit_invalid' => '您的請求無法提交。請嘗試刷新本頁面。',
	'not_open_registration' => '非常抱歉，本站目前暫時不開放註冊',
	'registered' => '註冊成功了',
	'system_uc_error' => '系統錯誤，未找到UCenter Client文件',
	'password_inconsistency' => '兩次輸入的密碼不一致',
	'profile_passwd_illegal' => '密碼空或包含非法字符，請重新填寫。',
	'user_name_is_not_legitimate' => '用戶名不合法',
	'include_not_registered_words' => '用戶名包含不允許註冊的詞語',
	'email_format_is_wrong' => 'Email 格式有誤',
	'email_not_registered' => 'Email 不允許註冊',
	'email_has_been_registered' => 'Email 已經被註冊',
	'register_error' => '註冊失敗',
	'user_name_already_exists' => '用戶名已經存在',

	//batch.epitome.php,batch.thumb.php
	'parameter_chenged' => '禁止篡改參數',
	'GD_lib_no_load' => '沒有加載GD庫。',
	'image_little' => '圖片太小，無法裁切',

	//store.php
	'notfound_commentmodel' => '對不起！沒有找到該商舖的點評模型。',

	//batch.modeldownload.php
	'visit_the_channel_does_not_exist' => '您訪問的頻道不存在,請返回首頁.',
	'downloading_short_time_interval' => '出錯了，您下載的時間間隔太短,請稍後再繼續下載.',

	//viewpro.php
	'uc_client_dir_error' => 'UCenter連接有誤，請與管理員聯繫。',
	'space_does_not_exist' => '指定的用戶空間不存在',

	//blogdetail.php
	'blog_no_info' => '日誌不存在',

	//source/do_lostpasswd.php
	'user_does_not_exist' => '該用戶不存在',
	'getpasswd_illegal' => '您所用的 ID 不存在或已經過期，無法取回密碼。',
	'getpasswd_succeed' => '您的密碼已重新設置，請使用新密碼登錄。',
	'getpasswd_account_invalid' => '對不起，創始人、受保護用戶或有站點設置權限的用戶不能使用取回密碼功能，請返回。',
	'mail_send_fail' => '郵件發送失敗!請聯繫管理員',
	'email_username_does_not_match' => '輸入的Email地址與用戶名不匹配，請重新確認。',
	'email_send_success' => '取回密碼的方式已經發送到您的郵箱中，請於3天之內取回您的密碼',
    'link_failure' => '鏈接失效',

	//source/cp_click.php
	'click_error' => '沒有進行正常的表態操作',
	'click_item_error' => '要表態的對象不存在',
	'click_no_self' => '自己不能給自己表態',
	'click_have' => '您已經表過態了',
	'click_success' => '參與表態完成了',

	//source/cp_news.php
	'no_item' => '對不起，沒找到指定的信息。',

	//source/cp_credit.php
	'integral_convertible_unopened' => '系統目前沒有開啟積分兌換功能。',
	'extcredits_dataerror' => '兌換失敗，請與管理員聯繫。',
	'credits_balance_insufficient' => '對不起，您的積分餘額不足，兌換失敗，請返回。',
	'credits_password_invalid' => '您沒有輸入密碼或密碼錯誤，不能進行積分操作，請返回。',
	'credits_transaction_amount_invalid' => '您要轉賬或兌換的積分數量輸入有誤，請返回修改。',
	'credits_exchange_invalid' => '兌換的積分方案有錯，不能進行兌換，請返回修改。',

	//source/cp_profile.php
	'old_password_invalid' => '您沒有輸入舊密碼或舊密碼錯誤，請返回重新填寫。',
	'no_change' => '沒有做任何修改',
	'protection_of_users' => '受保護的用戶，沒有權限修改',
	'password_is_not_passed' => '輸入的登錄密碼不正確,請返回重新確認',

	//source/cp_models.php
	'space_suject_length_error' => '您輸入的標題長度不符合要求(2~80個字符)',
	'admin_func_catid_error' => '您沒有正確指定分類，請返回確認',
	'document_types_can_only_upload_pictures' => '標題圖片只能上傳圖片類型文件(.jpg .jpeg .gif .png).',
	'writing_success_online_please_wait_for_audit' => '提交成功,請等待審核通過.',
	'online_contributions_success' => '在線投稿成功.',
	'writing_success_online_please_wait_for_audit' => '提交成功,請等待審核通過.',
	'parameter_error' => '出錯了，參數錯誤,請返回',

	//店舖關閉
	'shop_close' => '您好，您訪問的店舖目前暫未開啟，請稍後再來！',
	'shop_optpass' => '您好，您所訪問的店舖正在接受管理員審核，請稍後再來',
	'no_perm' => '抱歉，您沒有權限進行此操作',
	'no_tagids' => '當前分類下沒有可以瀏覽的信息，請返回重新選擇！',
	'admin_no_perm_to_panel' => '您只能在站長管理中心進行相關操作。',
	'noperm_manageshop' => '對不起，該店舖未找到，或不屬於您。',

	'requirefiled_not_complate' => '請詳細填寫相關信息',
	'join_success' => '報名成功。',
	'groupbuy_end_join' => '抱歉，該團人數已滿',
	'already_joined'=> '抱歉，您已經報名過了，請勿重複報名',

);

?>