<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_global_default.php 29404 2012-04-11 02:21:21Z cnteacher $
 */

$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
// ----------------------------  數據庫相關設置---------------------------- //

/**
 * 數據庫主服務器設置, 支持多組服務器設置, 當設置多組服務器時, 則會根據分佈式策略使用某個服務器
 * @example
 * $_config['db']['1']['dbhost'] = 'localhost'; // 服務器地址
 * $_config['db']['1']['dbuser'] = 'root'; // 用戶
 * $_config['db']['1']['dbpw'] = 'root';// 密碼
 * $_config['db']['1']['dbcharset'] = 'gbk';// 字符集
 * $_config['db']['1']['pconnect'] = '0';// 是否持續連接
 * $_config['db']['1']['dbname'] = 'x1';// 數據庫
 * $_config['db']['1']['tablepre'] = 'pre_';// 表名前綴
 *
 * $_config['db']['2']['dbhost'] = 'localhost';
 * ...
 *
 */
$_config['db'][1]['dbhost']  		= 'localhost';
$_config['db'][1]['dbuser']  		= 'root';
$_config['db'][1]['dbpw'] 	 	= 'root';
$_config['db'][1]['dbcharset'] 		= 'utf8';
$_config['db'][1]['pconnect'] 		= 0;
$_config['db'][1]['dbname']  		= 'ultrax';
$_config['db'][1]['tablepre'] 		= 'pre_';

/**
 * 數據庫從服務器設置( slave, 只讀 ), 支持多組服務器設置, 當設置多組服務器時, 系統每次隨機使用
 * @example
 * $_config['db']['slave']['1']['dbhost'] = 'localhost';
 * $_config['db']['slave']['1']['dbuser'] = 'root';
 * $_config['db']['slave']['1']['dbpw'] = 'root';
 * $_config['db']['slave']['1']['dbcharset'] = 'gbk';
 * $_config['db']['slave']['1']['pconnect'] = '0';
 * $_config['db']['slave']['1']['dbname'] = 'x1';
 * $_config['db']['slave']['1']['tablepre'] = 'pre_';
 *
 * $_config['db']['slave']['2']['dbhost'] = 'localhost';
 * ...
 *
 */
$_config['db']['slave'] = array();

/**
 * 數據庫 分佈部署策略設置
 *
 * @example 將 common_member 部署到第二服務器, common_session 部署在第三服務器, 則設置為
 * $_config['db']['map']['common_member'] = 2;
 * $_config['db']['map']['common_session'] = 3;
 *
 * 對於沒有明確聲明服務器的表, 則一律默認部署在第一服務器上
 *
 */
$_config['db']['map'] = array();

/**
 * 數據庫 公共設置, 此類設置通常對針對每個部署的服務器
 */
$_config['db']['common'] = array();

/**
 *  禁用從數據庫的數據表, 表名字之間使用逗號分割
 *
 * @example common_session, common_member 這兩個表僅從主服務器讀寫, 不使用從服務器
 * $_config['db']['common']['slave_except_table'] = 'common_session, common_member';
 *
 */
$_config['db']['common']['slave_except_table'] = '';

/**
 * 內存服務器優化設置
 * 以下設置需要PHP擴展組件支持，其中 memcache 優先於其他設置，
 * 當 memcache 無法啟用時，會自動開啟另外的兩種優化模式
 */

//內存變量前綴, 可更改,避免同服務器中的程序引用錯亂
$_config['memory']['prefix'] = 'discuz_';

/* reids設置, 需要PHP擴展組件支持, timeout參數的作用沒有查證 */
$_config['memory']['redis']['server'] = '';
$_config['memory']['redis']['port'] = 6379;
$_config['memory']['redis']['pconnect'] = 1;
$_config['memory']['redis']['timeout'] = 0;
/**
 * 是否使用 Redis::SERIALIZER_IGBINARY選項,需要igbinary支持,windows下測試時請關閉，否則會出>現錯誤Reading from client: Connection reset by peer
 * 支持以下選項，默認使用PHP的serializer
 * [重要] 該選項已經取代原來的 $_config['memory']['redis']['igbinary'] 選項
 * Redis::SERIALIZER_IGBINARY =2
 * Redis::SERIALIZER_PHP =1
 * Redis::SERIALIZER_NONE =0 //則不使用serialize,即無法保存array
 */
$_config['memory']['redis']['serializer'] = 1;

$_config['memory']['memcache']['server'] = '';			// memcache 服務器地址
$_config['memory']['memcache']['port'] = 11211;			// memcache 服務器端口
$_config['memory']['memcache']['pconnect'] = 1;			// memcache 是否長久連接
$_config['memory']['memcache']['timeout'] = 1;			// memcache 服務器連接超時

$_config['memory']['apc'] = 1;							// 啟動對 apc 的支持
$_config['memory']['xcache'] = 1;						// 啟動對 xcache 的支持
$_config['memory']['eaccelerator'] = 1;					// 啟動對 eaccelerator 的支持
// 服務器相關設置
$_config['server']['id']		= 1;			// 服務器編號，多webserver的時候，用於標識當前服務器的ID

// 附件下載相關
//
// 本地文件讀取模式; 模式2為最節省內存方式，但不支持多線程下載
// 1=fread 2=readfile 3=fpassthru 4=fpassthru+multiple
$_config['download']['readmod'] = 2;

// 是否啟用 X-Sendfile 功能（需要服務器支持）0=close 1=nginx 2=lighttpd 3=apache
$_config['download']['xsendfile']['type'] = 0;

// 啟用 nginx X-sendfile 時，論壇附件目錄的虛擬映射路徑，請使用 / 結尾
$_config['download']['xsendfile']['dir'] = '/down/';

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
$_config['output']['iecompatible']		= 0;		// 頁面 IE 兼容模式

// COOKIE 設置
$_config['cookie']['cookiepre'] 		= 'discuz_'; 	// COOKIE前綴
$_config['cookie']['cookiedomain'] 		= ''; 		// COOKIE作用域
$_config['cookie']['cookiepath'] 		= '/'; 		// COOKIE作用路徑

// 站點安全設置
$_config['security']['authkey']			= 'asdfasfas';	// 站點加密密鑰
$_config['security']['urlxssdefend']		= true;		// 自身 URL XSS 防禦
$_config['security']['attackevasive']		= 0;		// CC 攻擊防禦 1|2|4|8

$_config['security']['querysafe']['status']	= 1;		// 是否開啟SQL安全檢測，可自動預防SQL注入攻擊
$_config['security']['querysafe']['dfunction']	= array('load_file','hex','substring','if','ord','char');
$_config['security']['querysafe']['daction']	= array('intooutfile','intodumpfile','unionselect','(select', 'unionall', 'uniondistinct');
$_config['security']['querysafe']['dnote']	= array('/*','*/','#','--','"');
$_config['security']['querysafe']['dlikehex']	= 1;
$_config['security']['querysafe']['afullnote']	= 0;

$_config['admincp']['founder']			= '1';		// 站點創始人：擁有站點管理後台的最高權限，每個站點可以設置 1名或多名創始人
								// 可以使用uid，也可以使用用戶名；多個創始人之間請使用逗號「,」分開;
$_config['admincp']['forcesecques']		= 0;		// 管理人員必須設置安全提問才能進入系統設置 0=否, 1=是[安全]
$_config['admincp']['checkip']			= 1;		// 後台管理操作是否驗證管理員的 IP, 1=是[安全], 0=否。僅在管理員無法登陸後台時設置 0。
$_config['admincp']['runquery']			= 0;		// 是否允許後台運行 SQL 語句 1=是 0=否[安全]
$_config['admincp']['dbimport']			= 1;		// 是否允許後台恢復論壇數據  1=是 0=否[安全]

/**
 * 系統遠程調用功能模塊
 */

// 遠程調用: 總開關 0=關  1=開
$_config['remote']['on'] = 0;

// 遠程調用: 程序目錄名. 出於安全考慮,您可以更改這個目錄名, 修改完畢, 請手工修改程序的實際目錄
$_config['remote']['dir'] = 'remote';

// 遠程調用: 通信密鑰. 用於客戶端和本服務端的通信加密. 長度不少於 32 位
//          默認值是 $_config['security']['authkey']	的 md5, 您也可以手工指定
$_config['remote']['appkey'] = md5($_config['security']['authkey']);

// 遠程調用: 開啟外部 cron 任務. 系統內部不再執行cron, cron任務由外部程序激活
$_config['remote']['cron'] = 0;

// $_GET|$_POST的兼容處理，0為關閉，1為開啟；開啟後即可使用$_G['gp_xx'](xx為變量名，$_GET和$_POST集合的所有變量名)，值為已經addslashes()處理過
$_config['input']['compatible'] = 1;

?>