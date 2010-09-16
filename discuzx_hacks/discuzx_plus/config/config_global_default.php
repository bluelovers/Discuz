<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_global_default.php 548 2010-09-01 09:59:56Z yexinhao $
 */

$_config = array();

// 数据库服务器设置
$_config['db']['map'] = array();
$_config['db'][1]['dbhost']  		= 'localhost';		// 服务器地址
$_config['db'][1]['dbuser']  		= 'root';		// 用户
$_config['db'][1]['dbpw'] 	 	= 'root';		// 密码
$_config['db'][1]['dbcharset'] 		= 'utf8';		// 字符集
$_config['db'][1]['pconnect'] 		= 0;			// 是否持续连接
$_config['db'][1]['dbname']  		= 'ultrax';		// 数据库
$_config['db'][1]['tablepre'] 		= 'xplus_';		// 表名前缀

// 内存服务器优化设置（以下设置需要PHP扩展组件支持，其中 memcache 优先于其他设置，当 memcache 无法启用时，会自动开启另外的两种优化模式）
//  -----------------  CONFIG MEMORY  ----------------- //
$_config['memory']['prefix'] = 'xplus_';
$_config['memory']['eaccelerator'] = 1;				// 启动对 eaccelerator 的支持
$_config['memory']['xcache'] = 0;				// 启动对 xcache 的支持
$_config['memory']['memcache']['server'] = '';			// memcache 服务器地址
$_config['memory']['memcache']['port'] = 11211;			// memcache 服务器端口
$_config['memory']['memcache']['pconnect'] = 1;			// memcache 是否长久连接
$_config['memory']['memcache']['timeout'] = 1;			// memcache 服务器连接超时

//  -----------------  CONFIG CACHE  ----------------- //
$_config['cache']['main']['type'] = '';

//  -----------------  CONFIG default CACHE  ----------------- //
$_config['cache']['main']['file']['path'] = 'data/xpluscache';
$_config['cache']['type'] 			= 'sql';	// 缓存类型 file=文件缓存, sql=数据库缓存

// 页面输出设置
$_config['output']['charset'] 			= 'utf-8';	// 页面字符集
$_config['output']['forceheader']		= 1;		// 强制输出页面字符集，用于避免某些环境乱码
$_config['output']['gzip'] 			= 0;		// 是否采用 Gzip 压缩输出
$_config['output']['tplrefresh'] 		= 1;		// 模板自动刷新开关 0=关闭, 1=打开
$_config['output']['language'] 			= 'zh_cn';	// 页面语言 zh_cn/zh_tw
$_config['output']['staticurl'] 		= 'static/';	// 站点静态文件路径，“/”结尾

// COOKIE 设置
$_config['cookie']['cookiepre'] 		= 'uchome_'; 	// COOKIE前缀
$_config['cookie']['cookiedomain'] 		= ''; 		// COOKIE作用域
$_config['cookie']['cookiepath'] 		= '/'; 		// COOKIE作用路径

// 默认程序以及域名绑定设置
$_config['app']['default']			= '';
$_config['app']['domain']['default']		= '';		// 除去上面的域名之外的地址绑定的域名

// 站点安全设置
$_config['security']['authkey']			= 'c46b90xHlZAj7FuH';	// 站点加密密钥
$_config['security']['urlxssdefend']		= true;		// 自身 URL XSS 防御
$_config['security']['attackevasive']		= 0;		// CC 攻击防御 1|2|4

$_config['admincp']['founder']			= '1';		// 站点创始人：拥有站点管理后台的最高权限，每个站点可以设置 1名或多名创始人
								// 可以使用uid，也可以使用用户名；多个创始人之间请使用逗号",”分开;
$_config['admincp']['forcesecques']		= 0;		// 管理人员必须设置安全提问才能进入系统设置 0=否, 1=是[安全]
$_config['admincp']['checkip']			= 1;		// 后台管理操作是否验证管理员的 IP, 1=是[安全], 0=否。仅在管理员无法登陆后台时设置 0。
$_config['admincp']['runquery']			= 1;		// 是否允许后台运行 SQL 语句 1=是 0=否[安全]
$_config['admincp']['dbimport']			= 1;		// 是否允许后台恢复论坛数据  1=是 0=否[安全]

?>