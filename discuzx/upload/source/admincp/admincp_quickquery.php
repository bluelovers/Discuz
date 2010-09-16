<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_quickquery.php 14776 2010-08-16 03:09:39Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$simplequeries = array(
	array('comment' => '快速開啟論壇版塊功能', 'sql' => ''),
	array('comment' => '開啟 所有版塊 主題回收站', 'sql' => 'UPDATE {tablepre}forum_forum SET recyclebin=\'1\' WHERE status<\'3\''),
	array('comment' => '開啟 所有版塊 Discuz! 代碼」', 'sql' => 'UPDATE {tablepre}forum_forum SET allowbbcode=\'1\' WHERE status<\'3\''),
	array('comment' => '開啟 所有版塊 [IMG] 代碼」', 'sql' => 'UPDATE {tablepre}forum_forum SET allowimgcode=\'1\' WHERE status<\'3\''),
	array('comment' => '開啟 所有版塊 Smilies 代碼', 'sql' => 'UPDATE {tablepre}forum_forum SET allowsmilies=\'1\' WHERE status<\'3\''),
	array('comment' => '開啟 所有版塊 內容干擾碼', 'sql' => 'UPDATE {tablepre}forum_forum SET jammer=\'1\' WHERE status<\'3\''),
	array('comment' => '開啟 所有版塊 允許匿名發貼」', 'sql' => 'UPDATE {tablepre}forum_forum SET allowanonymous=\'1\' WHERE status<\'3\''),

	array('comment' => '快速關閉論壇版塊功能', 'sql' => ''),
	array('comment' => '關閉 所有版塊 主題回收站', 'sql' => 'UPDATE {tablepre}forum_forum SET recyclebin=\'0\' WHERE status<\'3\''),
	array('comment' => '關閉 所有版塊 HTML 代碼', 'sql' => 'UPDATE {tablepre}forum_forum SET allowhtml=\'0\' WHERE status<\'3\''),
	array('comment' => '關閉 所有版塊 Discuz! 代碼', 'sql' => 'UPDATE {tablepre}forum_forum SET allowbbcode=\'0\' WHERE status<\'3\''),
	array('comment' => '關閉 所有版塊 [IMG] 代碼', 'sql' => 'UPDATE {tablepre}forum_forum SET allowimgcode=\'0\' WHERE status<\'3\''),
	array('comment' => '關閉 所有版塊 Smilies 代碼', 'sql' => 'UPDATE {tablepre}forum_forum SET allowsmilies=\'0\' WHERE status<\'3\''),
	array('comment' => '關閉 所有版塊 內容干擾碼', 'sql' => 'UPDATE {tablepre}forum_forum SET jammer=\'0\' WHERE status<\'3\''),
	array('comment' => '關閉 所有版塊 允許匿名發貼', 'sql' => 'UPDATE {tablepre}forum_forum SET allowanonymous=\'0\' WHERE status<\'3\''),

	array('comment' => '會員操作相關', 'sql' => ''),
	array('comment' => '清空 所有會員 積分交易記錄', 'sql' => 'TRUNCATE {tablepre}common_credit_log'),
);

?>