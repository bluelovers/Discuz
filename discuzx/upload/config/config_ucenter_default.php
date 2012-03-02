<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_ucenter_default.php 11023 2010-05-20 02:23:09Z monkey $
 */

// ============================================================================
define('UC_CONNECT', 'mysql');				// 連接 UCenter 的方式: mysql/NULL, 默認為空時為 fscoketopen(), mysql 是直接連接的數據庫, 為了效率, 建議採用 mysql
// 數據庫相關 (mysql 連接時)
define('UC_DBHOST', 'localhost');			// UCenter 數據庫主機
define('UC_DBUSER', 'root');				// UCenter 數據庫用戶名
define('UC_DBPW', 'root');				// UCenter 數據庫密碼
define('UC_DBNAME', 'ucenter');				// UCenter 數據庫名稱
define('UC_DBCHARSET', 'utf8');				// UCenter 數據庫字符集
define('UC_DBTABLEPRE', '`ucenter`.uc_');		// UCenter 數據庫表前綴
define('UC_DBCONNECT', '0');				// UCenter 數據庫持久連接 0=關閉, 1=打開

// 通信相關
define('UC_KEY', 'yeN3g9EbNfiaYfodV63dI1j8Fbk5HaL7W4yaW4y7u2j4Mf45mfg2v899g451k576');	// 與 UCenter 的通信密鑰, 要與 UCenter 保持一致
define('UC_API', 'http://localhost/ucenter/branches/1.5.0/server'); // UCenter 的 URL 地址, 在調用頭像時依賴此常量
define('UC_CHARSET', 'utf-8');				// UCenter 的字符集
define('UC_IP', '127.0.0.1');				// UCenter 的 IP, 當 UC_CONNECT 為非 mysql 方式時, 並且當前應用服務器解析域名有問題時, 請設置此值
define('UC_APPID', '1');				// 當前應用的 ID

// ============================================================================

define('UC_PPP', '20');

?>