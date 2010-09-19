<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp_msg.php 646 2010-09-13 03:37:40Z yexinhao $
 */

$lang = array
(
	'undefined_action' => ' 未定義操作。',
	'noaccess' => '您沒有權限訪問管理中心。',
	'noaccess_isfounder' => '您沒有權限訪問該設置，出於安全考慮此設置只有站點創始人可以使用，請檢查 config/config_global.php 文件內創始人的設置。',
	'noaccess_ip' => '對不起，站長設定了只有特定 IP 地址範圍才能訪問管理中心，您的地址不在被允許的範圍內。',
	'action_noaccess' => '對不起，站長限制您無權使用本功能。',
	'action_noaccess_config' => '對不起，出於系統安全考慮，站長關閉了該功能，如需要打開請自行修改 config/config_global.php 文件內對應的相關安全配置信息。',
	'action_access_noexists' => '站點缺少安全設置，請對照標準程序的 config/config_global.php 仔細修改您的配置文件。否則無法使用本功能。',
	'function_config' => '此功能已經關閉，請到config/config_global.php裡面打開',

	'basic_succeed' => '基本設置完成',
	'module_succeed' => '模塊管理完成',
	'credit_succeed' => '積分設置完成',
	'cache_succeed' => '緩存更新完畢',
	'member_succeed' => '會員更新完畢',
	'template_succeed' => '模板更新完畢',
	'template_nav_succeed' => '導航更新完畢',
	'credit_add_invalid' => '每個模塊只能設置一種積分，請返回',
	'attachment_succeed' => '附件管理完成',
	'attachment_setting_succeed' => '附件設置完成',
	'members_add_succeed' => '用戶 {username}(UID {uid}) 添加成功。',
	'members_edit_succeed' => '編輯用戶完成',
	'memberprofile_succeed' => '用戶自定義欄目完成',

	'members_add_toolong' => '對不起，您的用戶名超過 15 個字符，請返回輸入一個較短的用戶名。',
	'members_add_tooshort' => '對不起，您輸入的用戶名小於3個字符, 請返回輸入一個較長的用戶名。',
	'members_add_illegal' => '用戶名包含敏感字符或被系統屏蔽，請返回重新填寫。',
	'members_username_protect' => '用戶名包含被系統屏蔽的字符，請返回重新填寫。',
	'members_add_invalid' => '您沒有填寫完整用戶資料，請返回修改。',
	'members_add_username_duplicate' => '用戶名已經存在，請返回修改。',
	'members_add_username_activation' => '用戶名已經存在，但尚未激活，請返回修改。',
	'members_email_duplicate' => '該 Email 地址已經被註冊，請返回重新填寫。',
	'members_email_illegal' => 'Email 地址無效，請返回重新填寫。',
	'members_email_domain_illegal' => 'Email 包含不可使用的郵箱域名，請返回重新填寫。',

	'founder_perm_group_update_succeed' => '管理團隊職務資料已成功更新。',
	'founder_perm_group_name_duplicate' => '團隊職務 {name} 已經存在，請返回更改。',
	'founder_perm_groupperm_update_succeed' => '職務權限成功更新。',
	'founder_perm_member_update_succeed' => '管理團隊成員資料已成功更新。',
	'founder_perm_member_noexists' => '指定的用戶 {name} 不存在，請返回更改。',
	'founder_perm_member_duplicate' => '用戶 {name} 已經存在，請返回更改。',
	'founder_perm_gperm_update_succeed' => '管理團隊權限資料已成功更新。',
	'founder_perm_member_noexists' => '用戶不存在，請返回。',

	'database_run_query_invalid' => '升級錯誤，MySQL 提示: {sqlerror} ，請返回。',
	'database_run_query_succeed' => 'Discuz! 數據結構成功升級，影響的記錄行數 {affected_rows}，請返回。',

	'profile_option_invalid' => '項目名稱或變量名為空，請返回修改。',
	'profile_optionvariable_invalid' => '變量名重複，請返回修改',

);

?>