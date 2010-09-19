<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp.php 619 2010-09-09 02:15:10Z tiger $
 */

//note 後台語言包規範：為了方便後台搜索，除通用名詞外，設置類語言項目統一使用 「action_operation_*******」的方式命名

$lang = array(

	'display_order' => '顯示順序',
	'available' => '可用',
	'name' => '名稱',
	'del' => '刪',
	'submit' => '提交',
	'username' => '用戶名',
	'usergroup' => '用戶組',
	'email' => '電子郵件',
	'password' => '密碼',
	'detail' => '詳情',
	'type' => '類型',
	'unit' => '單位',
	'add_new' => '新增',
	'edit' => '編輯',
	'discuz_message' => 'Discuz! 提示',
	'avatar' => '頭像',
	'regip' => '註冊IP',
	'lastip' => '最後訪問IP',
	'regdate' => '註冊日期',
	'avatar_clear' => '刪除頭像',
	'description' => '描述',
	'tips_textarea' => '雙擊輸入框可擴大/縮小',
	'required' => '必填',
	'all_module' => '所有模塊',
	'yes' => '是',
	'no' => '否',
	'message_redirect' => '如果您的瀏覽器沒有自動跳轉，請點擊這裡',
	'copyright' => '版權信息',
	'all' => '全部',
	'home' => '首頁',
	'preview' => '預覽',

	'db_runquery_comment' => '注意: 為確保升級成功，請不要修改 SQL 語句的任何部分。',
	'db_runquery_createcompatible' => '轉換建表語句格式和字符集',

	'founder_cpgroupname' => '職務名稱',
	'founder_username' => '成員用戶',
	'founder_usergname' => '職務',
	'founder_admin' => '創始人',
	'founder_master' => '副站長',
	'founder_usergname_comment' => '設置當前管理團隊成員的職務',
	'founder_perm_setting' => '基本權限',
	'founder_perm_all' => '<span title="設置成員擁有全部權限(創始人特定權限除外)">擁有全部權限</span>',
	'founder_perm_allowpost' => '<span title="設置成員能修改設置的內容">允許修改設置</span>',
	'founder_group_switch' => '切換團隊職務',

	'home' => '首頁',
	'home_welcome' => '{bbname} 管理中心',
	'home_sys_info' => '系統信息',
	'home_discuz_version' => 'Discuz XPlus! 程序版本',
	'home_ucclient_version' => 'UCenter 客戶端版本',
	'home_check_newversion' => '查看最新版本',
	'home_environment' => '服務器系統及 PHP',
	'home_serversoftware' => '服務器軟件',
	'home_database' => '服務器 MySQL 版本',
	'home_upload_perm' => '上傳許可',
	'home_database_size' => '當前數據庫尺寸',
	'home_attach_size' => '當前附件尺寸',
	'home_dev' => 'Discuz! 開發團隊',
	'home_dev_copyright' => '版權所有',
	'home_dev_manager' => '總策劃兼項目經理',
	'home_dev_team' => '開發與支持團隊',
	'home_dev_addons' => '插件與模板設計',
	'home_dev_skins' => '界面與用戶體驗團隊',
	'home_dev_thanks' => '感謝貢獻者',
	'home_dev_supportwebs' => '第三方支持網站',
	'home_dev_links' => '相關鏈接',
	'home_security_founder' => '<li>您可以制定多種團隊職務分配給您網站管理團隊的各個成員，讓他們管理網站的不同事務<li>「<strong>副站長</strong>」擁有除「創始人（站長）」專有權限以外的所有後台權限，僅次於「創始人（站長）」</li>',
	'home_security_nofounder' => '<li>您尚未設置 <u>站點創始人</u>，所有在管理員用戶組的用戶均可以登錄管理中心。請修改 config/config_global.php 添加創始人，以有效控制管理團隊成員的權限範圍',

	'vars_type_number' => '數字(number)',
	'vars_type_text' => '字串(text)',
	'vars_type_textarea' => '文本(textarea)',
	'vars_type_radio' => '單選(radio)',
	'vars_type_checkbox' => '多選(checkbox)',
	'vars_type_select' => '選擇(select)',
	'vars_type_calendar' => '日曆(calendar)',
	'vars_type_url' => '超級鏈接(url)',
	'vars_type_image' => '上傳圖片(image)',
	'vars_type_email' => '電子郵件(email)',
	'vars_type_upload' => '上傳(upload)',
	'vars_type_range' => '範圍(range)',
	'vars_type_area' => '地區(area)',

	'profile_edit_maxnum' => '數值最大值（可選）',
	'profile_edit_minnum' => '數值最小值（可選）',
	'profile_edit_textmax' => '內容最大長度（可選）',
	'profile_edit_inputsize' => '表單顯示長度（可選）',
	'profile_edit_searchtxt' => '搜索範圍預置值(可選)',
	'profile_edit_searchtxt_comment' => '設置項目的快速搜索預置數值，逗號分隔，例如：<i>0,50,10</i>，將顯示 0 - 50、50 - 100 的搜索快捷鏈接',
	'profile_edit_rowsize' => '輸入框行數（可選）：',
	'profile_edit_colsize' => '輸入框寬度（可選）：',
	'profile_edit_choices' => '選項內容',
	'profile_edit_choices_comment' => '只在項目為可選時有效，每行一個選項，等號前面為選項索引(建議用數字)，後面為內容，例如: <br /><i>1 = 光電鼠標<br />2 = 機械鼠標<br />3 = 沒有鼠標</i><br />注意: 選項確定後請勿修改索引和內容的對應關係，但仍可以新增選項。如需調換顯示順序，可以通過移動整行的上下位置來實現',
	'profile_add' => '添加用戶欄目',

	'template_add' => '添加新模板',

);

$adminextend = array();
if(file_exists($adminextendfile = DISCUZ_ROOT.'./data/cache/cache_adminextend.php')) {
	@include_once $adminextendfile;
	foreach($adminextend as $extend) {
		$extend_lang = array();
		@include_once DISCUZ_ROOT.'./source/language/lang_admincp_'.$extend;
		$lang = array_merge($lang, $extend_lang);
	}
}

?>
