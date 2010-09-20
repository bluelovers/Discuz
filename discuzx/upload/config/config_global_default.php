<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_global_default.php 17034 2010-09-19 07:38:41Z cnteacher $
 */

$_config = array();

// 數據庫服務器設置
$_config['db']['map'] = array();
$_config['db'][1]['dbhost']  		= 'localhost';		// 服務器地址
$_config['db'][1]['dbuser']  		= 'root';		// 用戶
$_config['db'][1]['dbpw'] 	 	= 'root';		// 密碼
$_config['db'][1]['dbcharset'] 		= 'utf8';		// 字符集
$_config['db'][1]['pconnect'] 		= 0;			// 是否持續連接
$_config['db'][1]['dbname']  		= 'ultrax';		// 數據庫
$_config['db'][1]['tablepre'] 		= 'pre_';		// 表名前綴

// 內存服務器優化設置（以下設置需要PHP擴展組件支持，其中 memcache 優先於其他設置，當 memcache 無法啟用時，會自動開啟另外的兩種優化模式）
$_config['memory']['prefix'] = 'discuz_';
$_config['memory']['eaccelerator'] = 1;				// 啟動對 eaccelerator 的支持
$_config['memory']['xcache'] = 1;				// 啟動對 xcache 的支持
$_config['memory']['memcache']['server'] = '';			// memcache 服務器地址
$_config['memory']['memcache']['port'] = 11211;			// memcache 服務器端口
$_config['memory']['memcache']['pconnect'] = 1;			// memcache 是否長久連接
$_config['memory']['memcache']['timeout'] = 1;			// memcache 服務器連接超時

// 服務器相關設置
$_config['server']['id']		= 1;			// 服務器編號，多webserver的時候，用於標識當前服務器的ID

// 附件下載相關
$_config['download']['readmod'] = 2;				// 本地文件讀取模式; 模式2為最節省內存方式，但不支持多線程下載
								// 1=fread 2=readfile 3=fpassthru 4=fpassthru+multiple
$_config['download']['xsendfile']['type'] = 0;			// 是否啟用 X-Sendfile 功能（需要服務器支持）0=close 1=nginx 2=lighttpd 3=apache
$_config['download']['xsendfile']['dir'] = '/down/';		// 啟用 nginx X-sendfile 時，論壇附件目錄的虛擬映射路徑，請使用 / 結尾

//  CONFIG CACHE
$_config['cache']['type'] 			= 'sql';	// 緩存類型 file=文件緩存, sql=數據庫緩存

// 頁面輸出設置
$_config['output']['charset'] 			= 'utf-8';	// 頁面字符集
$_config['output']['forceheader']		= 1;		// 強制輸出頁面字符集，用於避免某些環境亂碼
$_config['output']['gzip'] 			= 0;		// 是否採用 Gzip 壓縮輸出
$_config['output']['tplrefresh'] 		= 1;		// 模板自動刷新開關 0=關閉, 1=打開
$_config['output']['language'] 			= 'zh_tw';	// 頁面語言 zh_cn/zh_tw
$_config['output']['staticurl'] 		= 'static/';	// 站點靜態文件路徑，「/」結尾
$_config['output']['ajaxvalidate']		= 0;		// 是否嚴格驗證 Ajax 頁面的真實性 0=關閉，1=打開

// COOKIE 設置
$_config['cookie']['cookiepre'] 		= 'uchome_'; 	// COOKIE前綴
$_config['cookie']['cookiedomain'] 		= ''; 		// COOKIE作用域
$_config['cookie']['cookiepath'] 		= '/'; 		// COOKIE作用路徑

// 站點安全設置
$_config['security']['authkey']			= 'asdfasfas';	// 站點加密密鑰
$_config['security']['urlxssdefend']		= true;		// 自身 URL XSS 防禦
$_config['security']['attackevasive']		= 0;		// CC 攻擊防禦 1|2|4

$_config['security']['querysafe']['status']	= 1;		// 是否開啟SQL安全檢測，可自動預防SQL注入攻擊
$_config['security']['querysafe']['dfunction']	= array('load_file','hex','substring','if','ord','char');
$_config['security']['querysafe']['daction']	= array('intooutfile','intodumpfile','unionselect','(select');
$_config['security']['querysafe']['dnote']	= array('/*','*/','#','--','"');
$_config['security']['querysafe']['dlikehex']	= 1;
$_config['security']['querysafe']['afullnote']	= 1;

$_config['admincp']['founder']			= '1';		// 站點創始人：擁有站點管理後台的最高權限，每個站點可以設置 1名或多名創始人
								// 可以使用uid，也可以使用用戶名；多個創始人之間請使用逗號",」分開;
$_config['admincp']['forcesecques']		= 0;		// 管理人員必須設置安全提問才能進入系統設置 0=否, 1=是[安全]
$_config['admincp']['checkip']			= 1;		// 後台管理操作是否驗證管理員的 IP, 1=是[安全], 0=否。僅在管理員無法登陸後台時設置 0。
$_config['admincp']['runquery']			= 1;		// 是否允許後台運行 SQL 語句 1=是 0=否[安全]
$_config['admincp']['dbimport']			= 1;		// 是否允許後台恢復論壇數據  1=是 0=否[安全]

?>