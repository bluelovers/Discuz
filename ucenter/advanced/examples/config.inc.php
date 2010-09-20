<?php

define('UC_CONNECT', 'mysql');				// 連接 UCenter 的方式: mysql/NULL, 默認為空時為 fscoketopen()
							// mysql 是直接連接的數據庫, 為了效率, 建議採用 mysql

//數據庫相關 (mysql 連接時, 並且沒有設置 UC_DBLINK 時, 需要配置以下變量)
define('UC_DBHOST', 'localhost');			// UCenter 數據庫主機
define('UC_DBUSER', 'root');				// UCenter 數據庫用戶名
define('UC_DBPW', '');					// UCenter 數據庫密碼
define('UC_DBNAME', 'ucenter');				// UCenter 數據庫名稱
define('UC_DBCHARSET', 'gbk');				// UCenter 數據庫字符集
define('UC_DBTABLEPRE', 'ucenter.uc_');			// UCenter 數據庫表前綴

//通信相關
define('UC_KEY', '123456789');				// 與 UCenter 的通信密鑰, 要與 UCenter 保持一致
define('UC_API', 'http://yourwebsite/uc_server');	// UCenter 的 URL 地址, 在調用頭像時依賴此常量
define('UC_CHARSET', 'gbk');				// UCenter 的字符集
define('UC_IP', '');					// UCenter 的 IP, 當 UC_CONNECT 為非 mysql 方式時, 並且當前應用服務器解析域名有問題時, 請設置此值
define('UC_APPID', 1);					// 當前應用的 ID

//ucexample_2.php 用到的應用程序數據庫連接參數
$dbhost = 'localhost';			// 數據庫服務器
$dbuser = 'root';			// 數據庫用戶名
$dbpw = '';				// 數據庫密碼
$dbname = 'ucenter';			// 數據庫名
$pconnect = 0;				// 數據庫持久連接 0=關閉, 1=打開
$tablepre = 'example_';   		// 表名前綴, 同一數據庫安裝多個論壇請修改此處
$dbcharset = 'gbk';			// MySQL 字符集, 可選 'gbk', 'big5', 'utf8', 'latin1', 留空為按照論壇字符集設定

//同步登錄 Cookie 設置
$cookiedomain = ''; 			// cookie 作用域
$cookiepath = '/';			// cookie 作用路徑
