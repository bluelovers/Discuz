<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_message.php 650 2010-09-13 07:25:03Z yexinhao $
 */

$lang = array
(

	//poll/poll_index.php
	'poll_inexistence' => '對不起，您訪問的投票不存在！',
	'poll_status_unopened' => '對不起，您訪問的投票已關閉！',
	'poll_unstart' => '對不起，投票尚未開始，請您稍後再來！',
	'poll_expired' => '對不起，投票已過期，您不可以再投票！',
	'poll_data_invalid' => '對不起，您提交的數據不合法！',
	'poll_vote_error' => '對不起，投票失敗，您可能已經投過票，詳情請聯繫管理員',
	'poll_per_once' => '對不起，每個用戶只允許投票一次！',
	'poll_limittime_short' => '對不起，您的兩次投票間隔時間太短！請{utime}後再來投票，當前驗證方式為：{check}',
	'poll_choice_null' => '請您至少選擇一個選項！',
	'poll_choicenum_more' => '對不起，你選擇項數過多，本投票最多只允許選擇{num}項！',
	'poll_vote_succeed' => '投票成功 ，感謝您的參與！' ,
	'poll_vote_succeed_login' => '投票成功，感謝您的參與！請您登錄後查看投票結果。',
	'poll_vote_so_error' => '數據校驗失敗，請嘗試從標準瀏覽器提交數據',
	'poll_guest_unallowed' => '對不起，本投票不允許遊客投票！',

	'poll_repeattype_cookie' => 'cookie驗證',
	'poll_repeattype_username' => '用戶名驗證',
	'poll_repeattype_ip' => 'IP地址驗證',
	'poll_repeattype_so' => 'Flash驗證',

);

?>