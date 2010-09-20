<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang.inc.php 3959 2010-07-27 03:05:46Z yexinhao $
 */

define('UC_VERNAME', '中文版');

$lang = array(

	'SC_GBK' => '簡體中文版',
	'TC_BIG5' => '繁體中文版',
	'SC_UTF8' => '簡體中文 UTF8 版',
	'TC_UTF8' => '繁體中文 UTF8 版',
	'EN_ISO' => 'ENGLISH ISO8859',
	'EN_UTF8' => 'ENGLIST UTF-8',

	'brand' => '品牌空間',
	'install_config' => '請將config.new.php修改為config.php',
	'title_install' => '品牌空間 安裝嚮導',
	'agreement_yes' => '我同意',
	'agreement_no' => '我不同意',
	'notset' => '不限制',
	'advice_iconv' => '請修改php.ini打開此項擴展',

	'message_title' => '提示信息',
	'error_message' => '錯誤信息',
	'message_return' => '返回',
	'return' => '返回',
	'install_wizard' => '安裝嚮導',
	'config_nonexistence' => '配置文件不存在',
	'nodir' => '目錄不存在',
	'short_open_tag_invalid' => '對不起，請將 php.ini 中的 short_open_tag 設置為 On，否則無法繼續安裝。',
	'redirect' => '瀏覽器會自動跳轉頁面，無需人工干預。<br>除非當您的瀏覽器沒有自動跳轉時，請點擊這裡',
	'auto_redirect' => '瀏覽器會自動跳轉頁面，無需人工干預',
	'database_errno_2003' => '無法連接數據庫，請檢查數據庫是否啟動，數據庫服務器地址是否正確',
	'database_errno_1044' => '無法創建新的數據庫，請檢查數據庫名稱填寫是否正確',
	'database_errno_1045' => '無法連接數據庫，請檢查數據庫用戶名或者密碼是否正確',
	'database_errno_1064' => 'SQL 語法錯誤',
	'mysqld_required_version' => 'MySQL服務器版本需高於 4.1',

	'dbpriv_createtable' => '沒有CREATE TABLE權限，無法繼續安裝',
	'dbpriv_insert' => '沒有INSERT權限，無法繼續安裝',
	'dbpriv_select' => '沒有SELECT權限，無法繼續安裝',
	'dbpriv_update' => '沒有UPDATE權限，無法繼續安裝',
	'dbpriv_delete' => '沒有DELETE權限，無法繼續安裝',
	'dbpriv_droptable' => '沒有DROP TABLE權限，無法安裝',

	'db_not_null' => '數據庫中已經安裝過 UCenter, 繼續安裝會清空原有數據。',
	'db_drop_table_confirm' => '繼續安裝會清空全部原有數據，您確定要繼續嗎?',

	'writeable' => '可寫',
	'unwriteable' => '不可寫',
	'old_step' => '上一步',
	'new_step' => '下一步',

	'database_errno_2003' => '無法連接數據庫，請檢查數據庫是否啟動，數據庫服務器地址是否正確',
	'database_errno_1044' => '無法創建新的數據庫，請檢查數據庫名稱填寫是否正確',
	'database_errno_1045' => '無法連接數據庫，請檢查數據庫用戶名或者密碼是否正確',
	'database_connect_error' => '數據庫連接錯誤',

	'step_env_check_title' => '開始安裝',
	'step_env_check_desc' => '環境以及文件目錄權限檢查',
	'step_db_init_title' => '安裝數據庫',
	'step_db_init_desc' => '正在執行數據庫安裝',

	'step1_file' => '目錄文件',
	'step1_need_status' => '所需狀態',
	'step1_status' => '當前狀態',
	'not_continue' => '請將以上紅叉部分修正再試',

	'tips_dbinfo' => '填寫數據庫信息',
	'tips_dbinfo_comment' => '',
	'tips_admininfo' => '填寫管理員信息',
	'step_ext_info_title' => '安裝成功',
	'step_ext_info_comment' => '點擊進入登陸',

	'ext_info_succ' => '安裝成功',
	'install_submit' => '提交',
	'install_locked' => '安裝鎖定，已經安裝過了<br /> 如果您確定要重新安裝，請到服務器上刪除data/install.lock文件',
	'error_quit_msg' => '您必須解決以上問題，安裝才可以繼續',

	'step_app_reg_title' => '設置運行環境',
	'step_app_reg_desc' => '檢測服務器環境以及設置 UCenter',
	'tips_ucenter' => '請填寫 UCenter 相關信息',
	'tips_ucenter_comment' => 'UCenter 是 Comsenz 公司產品的核心服務程序，品牌空間的安裝和運行依賴此程序。如果您已經安裝了 UCenter，請填寫以下信息。否則，請到 <a href="http://www.discuz.com/" target="blank">Comsenz 產品中心</a> 下載並且安裝，然後再繼續。',

	'advice_mysql_connect' => '請檢查 mysql 模塊是否正確加載',
	'advice_fsockopen' => '該函數需要 php.ini 中 allow_url_fopen 選項開啟。請聯繫空間商，確定開啟了此項功能',
	'advice_gethostbyname' => '是否php配置中禁止了gethostbyname函數。請聯繫空間商，確定開啟了此項功能',
	'advice_file_get_contents' => '該函數需要 php.ini 中 allow_url_fopen 選項開啟。請聯繫空間商，確定開啟了此項功能',
	'advice_xml_parser_create' => '該函數需要 PHP 支持 XML。請聯繫空間商，確定開啟了此項功能',

	'ucurl' => 'UCenter 的 URL',
	'ucpw' => 'UCenter 創始人密碼',
	'ucip' => 'UCenter 的IP地址',
	'ucenter_ucip_invalid' => '格式錯誤，請填寫正確的 IP 地址',
	'ucip_comment' => '絕大多數情況下您可以不填',

	'tips_siteinfo' => '請填寫站點信息',
	'sitename' => '站點名稱',
	'siteurl' => '站點 URL',

	'forceinstall' => '強制安裝',
	'dbinfo_forceinstall_invalid' => '當前數據庫當中已經含有同樣表前綴的數據表，您可以修改「表名前綴」來避免刪除舊的數據，或者選擇強制安裝。強制安裝會刪除舊數據，且無法恢復',

	'click_to_back' => '點擊返回上一步',
	'adminemail' => '系統信箱 Email',
	'adminemail_comment' => '用於發送程序錯誤報告',
	'dbhost_comment' => '數據庫服務器地址, 一般為 localhost',
	'tablepre_comment' => '同一數據庫運行多個論壇時，請修改前綴',
	'forceinstall_check_label' => '我要刪除數據，強制安裝 !!!',

	'uc_url_empty' => '您沒有填寫 UCenter 的 URL，請返回填寫',
	'uc_url_invalid' => 'URL 格式錯誤',
	'uc_url_unreachable' => 'UCenter 的 URL 地址可能填寫錯誤，請檢查',
	'uc_ip_invalid' => '無法解析該域名，請填寫站點的 IP',
	'uc_admin_invalid' => 'UCenter 創始人密碼錯誤，請重新填寫',
	'uc_data_invalid' => '通信失敗，請檢查 UCenter 的URL 地址是否正確 ',
	'uc_dbcharset_incorrect' => 'UCenter 數據庫字符集與當前應用字符集不一致',
	'uc_api_add_app_error' => '向 UCenter 添加應用錯誤',
	'uc_dns_error' => 'UCenter DNS解析錯誤，請返回填寫一下 UCenter 的 IP地址',

	'ucenter_ucurl_invalid' => 'UCenter 的URL為空，或者格式錯誤，請檢查',
	'ucenter_ucpw_invalid' => 'UCenter 的創始人密碼為空，或者格式錯誤，請檢查',
	'siteinfo_siteurl_invalid' => '站點URL為空，或者格式錯誤，請檢查',
	'siteinfo_sitename_invalid' => '站點名稱為空，或者格式錯誤，請檢查',
	'dbinfo_dbhost_invalid' => '數據庫服務器為空，或者格式錯誤，請檢查',
	'dbinfo_dbname_invalid' => '數據庫名為空，或者格式錯誤，請檢查',
	'dbinfo_dbuser_invalid' => '數據庫用戶名為空，或者格式錯誤，請檢查',
	'dbinfo_dbpw_invalid' => '數據庫密碼為空，或者格式錯誤，請檢查',
	'dbinfo_adminemail_invalid' => '系統郵箱為空，或者格式錯誤，請檢查',
	'dbinfo_tablepre_invalid' => '數據表前綴為空，或者格式錯誤，請檢查',
	'admininfo_username_invalid' => '管理員用戶名為空，或者格式錯誤，請檢查',
	'admininfo_email_invalid' => '管理員Email為空，或者格式錯誤，請檢查',
	'admininfo_password_invalid' => '管理員密碼為空，請填寫',
	'admininfo_password2_invalid' => '兩次密碼不一致，請檢查',

	'username' => '管理員賬號',
	'email' => '管理員 Email',
	'password' => '管理員密碼',
	'password_comment' => '管理員密碼不能為空',
	'password2' => '重複密碼',

	'admininfo_invalid' => '管理員信息不完整，請檢查管理員賬號，密碼，郵箱',
	'dbname_invalid' => '數據庫名為空，請填寫數據庫名稱',
	'tablepre_invalid' => '數據表前綴為空，或者格式錯誤，請檢查',
	'admin_username_invalid' => '非法用戶名，用戶名長度不應當超過 15 個英文字符，且不能包含特殊字符，一般是中文，字母或者數字',
	'admin_password_invalid' => '密碼和上面不一致，請重新輸入',
	'admin_email_invalid' => 'Email 地址錯誤，此郵件地址已經被使用或者格式無效，請更換為其他地址',
	'admin_invalid' => '您的信息管理員信息沒有填寫完整，請仔細填寫每個項目',
	'admin_exist_password_error' => '該用戶已經存在，如果您要設置此用戶為品牌空間的管理員，請正確輸入該用戶的密碼，或者請更換品牌空間管理員的名字',

	'uc_version_incorrect' => '您的 UCenter 服務端版本過低，請升級 UCenter 服務端到最新版本，並且升級，下載地址：http://www.comsenz.com/ 。',
	'config_unwriteable' => '安裝嚮導無法寫入配置文件, 請設置 config.php 程序屬性為可寫狀態(777)',

	'install_in_processed' => '正在安裝...',
	'install_succeed' => '安裝成功，點擊進入',
	'install_founder_contact' => '進入下一步填寫聯繫方式',

	'init_default_style' => '默認風格',
	'init_default_template' => '默認模板套系',
	'init_default_template_copyright' => '康盛創想（北京）科技有限公司',

	'init_dataformat' => 'Y-n-j',
	'init_link' => 'Discuz! 官方論壇',
	'init_link_note' => '提供最新 Comsenz 產品新聞、軟件下載與技術交流',

	'license' => '<div class="license"><h1>中文版授權協議 適用於中文用戶</h1>

<p>版權所有 (c) 2009-2010，康盛創想（北京）科技有限公司保留所有權利。</p>

<p>感謝您選擇品牌空間產品。希望我們的努力能為您提供一個高效快速和強大的品牌空間解決方案。</p>

<p>康盛創想（北京）科技有限公司為品牌空間產品的開發商，依法獨立擁有品牌空間產品著作權。康盛創想（北京）科技有限公司網址為 http://www.comsenz.com，品牌空間官方討論區網址為 http://www.discuz.net。</p>

<p>本授權協議適用且僅適用於品牌空間，康盛創想（北京）科技有限公司擁有對本授權協議的最終解釋權。</p>

<h3>I. 協議許可的權利</h3>
<ol>
<li>您可以在完全遵守本最終用戶授權協議的基礎上，將本軟件應用於非商業用途，而不必支付軟件版權授權費用。</li>
<li>您擁有使用本軟件構建的論壇中全部會員資料、文章及相關信息的所有權，並獨立承擔與文章內容的相關法律義務。</li>
</ol>

<h3>II. 協議規定的約束和限制</h3>
<ol>
<li>未獲商業授權之前，不得將本軟件用於商業用途（包括但不限於企業網站、經營性網站、以營利為目或實現盈利的網站）。購買商業授權請登陸http://www.discuz.com參考相關說明，也可以致電8610-51657885瞭解詳情。</li>
<li>不得對本軟件或與之關聯的商業授權進行出租、出售、抵押或發放子許可證。</li>
<li>禁止在品牌空間的整體或任何部分基礎上以發展任何派生版本、修改版本或第三方版本用於重新分發。</li>
<li>如果您未能遵守本協議的條款，您的授權將被終止，所被許可的權利將被收回，並承擔相應法律責任。</li>
</ol>

<h3>III. 有限擔保和免責聲明</h3>
<ol>
<li>本軟件及所附帶的文件是作為不提供任何明確的或隱含的賠償或擔保的形式提供的。</li>
<li>用戶出於自願而使用本軟件，您必須瞭解使用本軟件的風險，在尚未購買產品技術服務之前，我們不承諾提供任何形式的技術支持、使用擔保，也不承擔任何因使用本軟件而產生問題的相關責任。</li>
<li>康盛創想（北京）科技有限公司不對使用本軟件構建的文章或信息承擔責任。</li>
</ol>

<p>有關品牌空間最終用戶授權協議、商業授權與技術服務的詳細內容，均由康盛創想（北京）科技有限公司獨家提供。康盛創想（北京）科技有限公司擁有在不事先通知的情況下，修改授權協議和服務價目表的權力，修改後的協議或價目表對自改變之日起的新授權用戶生效。</p>

<p>電子文本形式的授權協議如同雙方書面簽署的協議一樣，具有完全的和等同的法律效力。您一旦開始安裝品牌空間，即被視為完全理解並接受本協議的各項條款，在享有上述條款授予的權力的同時，受到相關的約束和限制。協議許可範圍以外的行為，將直接違反本授權協議並構成侵權，我們有權隨時終止授權，責令停止損害，並保留追究相關責任的權力。</p></div>',

	'uc_installed' => '您已經安裝過 UCenter，如果需要重新安裝，請刪除 data/install.lock 文件',
	'i_agree' => '我已仔細閱讀，並同意上述條款中的所有內容',
	'supportted' => '支持',
	'unsupportted' => '不支持',
	'max_size' => '支持/最大尺寸',
	'project' => '項目',
	'ucenter_required' => '品牌空間所需配置',
	'ucenter_best' => '品牌空間最佳',
	'curr_server' => '當前服務器',
	'env_check' => '環境檢查',
	'os' => '操作系統',
	'mysql' => 'MySQL服務器',
	'php' => 'PHP 版本',
	'attachmentupload' => '附件上傳',
	'short_open_tag' => 'php短標籤',
	'unlimit' => '不限制',
	'version' => '版本',
	'gdversion' => 'GD 庫',
	'allow' => '允許',
	'unix' => '類Unix',
	'diskspace' => '磁盤空間',
	'priv_check' => '目錄、文件權限檢查',
	'func_depend' => '函數依賴性檢查',
	'func_name' => '函數名稱',
	'check_result' => '檢查結果',
	'suggestion' => '建議',
	'advice_mysql' => '請檢查 mysql 模塊是否正確加載',
	'advice_fopen' => '該函數需要 php.ini 中 allow_url_fopen 選項開啟。請聯繫空間商，確定開啟了此項功能',
	'advice_file_get_contents' => '該函數需要 php.ini 中 allow_url_fopen 選項開啟。請聯繫空間商，確定開啟了此項功能',
	'advice_xml' => '該函數需要 PHP 支持 XML。請聯繫空間商，確定開啟了此項功能',
	'none' => '無',

	'dbhost' => '數據庫服務器',
	'dbuser' => '數據庫用戶名',
	'dbpw' => '數據庫密碼',
	'dbname' => '數據庫名',
	'tablepre' => '數據表前綴',

	'ucfounderpw' => '創始人密碼',
	'ucfounderpw2' => '重複創始人密碼',

	'init_log' => '初始化記錄',
	'clear_dir' => '清空目錄',
	'select_db' => '選擇數據庫',
	'create_table' => '建立數據表',
	'succeed' => '成功',

	'testdata' => '安裝測試數據',
	'testdata_check_label' => '是',
	'install_test_data' => '正在安裝測試數據',

	'method_undefined' => '未定義方法',
	'database_nonexistence' => '數據庫操作對像不存在',
	'skip_current' => '跳過本步',

);

$msglang = array(

	'config_nonexistence' => '您的 config.php 不存在, 無法繼續安裝, 請用 FTP 將該文件上傳後再試。',
);

?>