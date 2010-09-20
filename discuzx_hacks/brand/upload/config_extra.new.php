<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_extra.new.php 3986 2010-07-28 02:32:16Z yexinhao $
 */

//--------------- 品牌空間memcache緩存設置 ---------------
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
							//品牌空間memcache緩存服務器配置，如需分佈式多服務器，請按以上格式添加多個數組


//--------------- 品牌空間TokyoCabinet緩存設置 ---------------
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
							//品牌空間TokyoCabinet緩存服務器配置，如需分佈式多服務器，請按以上格式添加多個數組

?>