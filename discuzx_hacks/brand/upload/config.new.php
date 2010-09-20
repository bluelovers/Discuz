<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config.new.php 4397 2010-09-10 10:07:01Z fanshengshuai $
 */

$_SC = array();

//--------------- 品牌空間設置 ---------------
$_SC['dbhost'] = 'localhost';					//品牌空間數據庫服務器(一般為本地localhost)
$_SC['dbuser'] = 'root';					//品牌空間數據庫用戶名
$_SC['dbpw'] = '';						//品牌空間數據庫密碼
$_SC['dbname'] = 'brand';						//品牌空間數據庫名
$_SC['tablepre'] = 'brand_';					//品牌空間表名前綴(不能與論壇的表名前綴相同)
$_SC['pconnect'] = 0;						//品牌空間數據庫持久連接 0=關閉, 1=打開
$_SC['dbcharset'] = 'utf8';					//品牌空間數據庫字符集

$_SC['siteurl'] = '';						//品牌空間程序文件所在目錄的URL訪問地址。可以填寫以 http:// 開頭的完整URL，也可以填寫相對URL。末尾不要加 /。如果程序無法自動獲取，請務必手工修改為 http://www.yourwebsite.com/brand 形式
$_SC['local'] = '';                         //所在的地區，地圖服務默認顯示地區。

//--------------- Discuz!論壇設置（從論壇導入圖片相冊時使用） ---------------
$_SC['bbs_dbhost'] = 'localhost';					//論壇數據庫服務器(一般為本地localhost)
$_SC['bbs_dbuser'] = 'root';					//論壇數據庫用戶名
$_SC['bbs_dbpw'] = '';					//論壇數據庫密碼
$_SC['bbs_dbname'] = 'discuz';					//論壇數據庫名
$_SC['bbs_dbpre'] = 'cdb_';					//論壇表名前綴
$_SC['bbs_dbcharset'] = 'utf8';				//論壇數據庫字符集
$_SC['bbs_pconnect'] = 0;					//論壇數據庫持久連接 0=關閉, 1=打開
$_SC['bbs_url'] = '';					//論壇程序文件所在目錄的URL訪問地址。末尾不要加 /
$_SC['bbs_version'] = '';					//論壇的版本 Discuz! X1 為　discuzx , 其它之前版本為 discuz

//安全相關
$_SC['founder'] = '1';						//品牌空間管理員UID，可以支持多個，之間使用英文半角「,」 分隔。

//--------------- COOKIE設置 ---------------
$_SC['cookiepre'] = 'BrwD_';					//Cookie前綴
$_SC['cookiedomain'] = '';					//cookie 作用域。請設置為 .yourdomain.com 形式
$_SC['cookiepath'] = '/';					//cookie 作用路徑

//--------------- 字符集設置 ---------------
$_SC['charset'] = 'utf-8';					//頁面字符集(可選 'gbk', 'big5', 'utf-8')

//--------------- 其他系統參數 ---------------
$_SC['tplrefresh'] = 1;						//風格模板自動刷新開關。關閉後，你修改模板頁面後，需要手工進入管理員後台=>緩存更新 進行一下模板文件緩存清空，才能看到修改的效果。
$_SC['cachegrade'] = 1;						//系統緩存分表等級(默認為1，級別每增加1，分表數目增加255個，級別越大，單個表的尺寸越小)

//--------------- UCenter設置 ---------------
define('UC_CONNECT', 'mysql');					// 連接 UCenter 的方式: mysql/NULL, 默認為空時為 fscoketopen(), mysql 是直接連接的數據庫, 為了效率, 建議採用 mysql

// 數據庫相關 (mysql 連接時)
define('UC_DBHOST', 'localhost');				// UCenter 數據庫主機
define('UC_DBUSER', 'root');					// UCenter 數據庫用戶名
define('UC_DBPW', '');						// UCenter 數據庫密碼
define('UC_DBNAME', 'uc');					// UCenter 數據庫名稱
define('UC_DBCHARSET', 'gbk');					// UCenter 數據庫字符集
define('UC_DBTABLEPRE', '`uc`.uc_');					// UCenter 數據庫表前綴
define('UC_DBCONNECT', '0');					// UCenter 數據庫持久連接 0=關閉, 1=打開

// 通信相關
define('UC_KEY', '');						// 與 UCenter 的通信密鑰, 要與 UCenter 保持一致
define('UC_API', '');						// UCenter 的 URL 地址, 在調用頭像時依賴此常量
define('UC_CHARSET', 'gbk');					// UCenter 的字符集
define('UC_IP', '');						// UCenter 的 IP, 當 UC_CONNECT 為非 mysql 方式時, 並且當前應用服務器解析域名有問題時, 請設置此值
define('UC_APPID', '1');						// 當前應用的 ID
define('UC_PPP', '20');

//-------------------------------------------