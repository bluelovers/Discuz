<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_extra.new.php 3986 2010-07-28 02:32:16Z yexinhao $
 */

//--------------- 品牌空间memcache缓存设置 ---------------
$_SC['memcache'] = array();
$_SC['memcache'][] = array(
	'host' => 'localhost',
	'port' => '11211'
);
/*
$_SC['memcache'][] = array(
	'host' => 'localhost',
	'port' => '11211'
);
*/
							//品牌空间memcache缓存服务器配置，如需分布式多服务器，请按以上格式添加多个数组


//--------------- 品牌空间TokyoCabinet缓存设置 ---------------
$_SC['tokyocabinet'] = array();
$_SC['tokyocabinet'][] = array(
	'host' => 'localhost',
	'port' => '1978'
);
/*
$_SC['tokyocabinet'][] = array(
	'host' => 'localhost',
	'port' => '11211'
);
*/
							//品牌空间TokyoCabinet缓存服务器配置，如需分布式多服务器，请按以上格式添加多个数组

?>