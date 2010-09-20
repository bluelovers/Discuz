<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config.new.php 4397 2010-09-10 10:07:01Z fanshengshuai $
 */

$_SC = array();

//--------------- 品牌空间设置 ---------------
$_SC['dbhost'] = 'localhost';					//品牌空间数据库服务器(一般为本地localhost)
$_SC['dbuser'] = 'root';					//品牌空间数据库用户名
$_SC['dbpw'] = '';						//品牌空间数据库密码
$_SC['dbname'] = 'brand';						//品牌空间数据库名
$_SC['tablepre'] = 'brand_';					//品牌空间表名前缀(不能与论坛的表名前缀相同)
$_SC['pconnect'] = 0;						//品牌空间数据库持久连接 0=关闭, 1=打开
$_SC['dbcharset'] = 'utf8';					//品牌空间数据库字符集

$_SC['siteurl'] = '';						//品牌空间程序文件所在目录的URL访问地址。可以填写以 http:// 开头的完整URL，也可以填写相对URL。末尾不要加 /。如果程序无法自动获取，请务必手工修改为 http://www.yourwebsite.com/brand 形式
$_SC['local'] = '';                         //所在的地区，地图服务默认显示地区。

//--------------- Discuz!论坛设置（从论坛导入图片相册时使用） ---------------
$_SC['bbs_dbhost'] = 'localhost';					//论坛数据库服务器(一般为本地localhost)
$_SC['bbs_dbuser'] = 'root';					//论坛数据库用户名
$_SC['bbs_dbpw'] = '';					//论坛数据库密码
$_SC['bbs_dbname'] = 'discuz';					//论坛数据库名
$_SC['bbs_dbpre'] = 'cdb_';					//论坛表名前缀
$_SC['bbs_dbcharset'] = 'utf8';				//论坛数据库字符集
$_SC['bbs_pconnect'] = 0;					//论坛数据库持久连接 0=关闭, 1=打开
$_SC['bbs_url'] = '';					//论坛程序文件所在目录的URL访问地址。末尾不要加 /
$_SC['bbs_version'] = '';					//论坛的版本 Discuz! X1 为　discuzx , 其它之前版本为 discuz

//安全相关
$_SC['founder'] = '1';						//品牌空间管理员UID，可以支持多个，之间使用英文半角“,” 分隔。

//--------------- COOKIE设置 ---------------
$_SC['cookiepre'] = 'BrwD_';					//Cookie前缀
$_SC['cookiedomain'] = '';					//cookie 作用域。请设置为 .yourdomain.com 形式
$_SC['cookiepath'] = '/';					//cookie 作用路径

//--------------- 字符集设置 ---------------
$_SC['charset'] = 'utf-8';					//页面字符集(可选 'gbk', 'big5', 'utf-8')

//--------------- 其他系统参数 ---------------
$_SC['tplrefresh'] = 1;						//风格模板自动刷新开关。关闭后，你修改模板页面后，需要手工进入管理员后台=>缓存更新 进行一下模板文件缓存清空，才能看到修改的效果。
$_SC['cachegrade'] = 1;						//系统缓存分表等级(默认为1，级别每增加1，分表数目增加255个，级别越大，单个表的尺寸越小)

//--------------- UCenter设置 ---------------
define('UC_CONNECT', 'mysql');					// 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen(), mysql 是直接连接的数据库, 为了效率, 建议采用 mysql

// 数据库相关 (mysql 连接时)
define('UC_DBHOST', 'localhost');				// UCenter 数据库主机
define('UC_DBUSER', 'root');					// UCenter 数据库用户名
define('UC_DBPW', '');						// UCenter 数据库密码
define('UC_DBNAME', 'uc');					// UCenter 数据库名称
define('UC_DBCHARSET', 'gbk');					// UCenter 数据库字符集
define('UC_DBTABLEPRE', '`uc`.uc_');					// UCenter 数据库表前缀
define('UC_DBCONNECT', '0');					// UCenter 数据库持久连接 0=关闭, 1=打开

// 通信相关
define('UC_KEY', '');						// 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', '');						// UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'gbk');					// UCenter 的字符集
define('UC_IP', '');						// UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', '1');						// 当前应用的 ID
define('UC_PPP', '20');

//-------------------------------------------