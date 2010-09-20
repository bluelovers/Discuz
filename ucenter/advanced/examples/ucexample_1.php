<?php
/**
 * UCenter 應用程序開發 Example
 *
 * UCenter 簡易應用程序，應用程序無數據庫
 * 使用到的接口函數：
 * uc_authcode()	可選，借用用戶中心的函數加解密 Cookie
 * uc_pm_checknew()	可選，用於全局判斷是否有新短消息，返回 $newpm 變量
 */

include './config.inc.php';
include './uc_client/client.php';

/**
 * 獲取當前用戶的 UID 和 用戶名
 * Cookie 解密直接用 uc_authcode 函數，用戶使用自己的函數
 */
if(!empty($_COOKIE['Example_auth'])) {
	list($Example_uid, $Example_username) = explode("\t", uc_authcode($_COOKIE['Example_auth'], 'DECODE'));
} else {
	$Example_uid = $Example_username = '';
}

/**
 * 獲取最新短消息
 */
$newpm = uc_pm_checknew($Example_uid);

/**
 * 各個功能的 Example 代碼
 */
switch(@$_GET['example']) {
	case 'login':
		//UCenter 用戶登錄的 Example 代碼
		include 'code/login_nodb.php';
	break;
	case 'logout':
		//UCenter 用戶退出的 Example 代碼
		include 'code/logout.php';
	break;
	case 'register':
		//UCenter 用戶註冊的 Example 代碼
		include 'code/register_nodb.php';
	break;
	case 'pmlist':
		//UCenter 未讀短消息列表的 Example 代碼
		include 'code/pmlist.php';
	break;
	case 'pmwin':
		//UCenter 短消息中心的 Example 代碼
		include 'code/pmwin.php';
	break;
	case 'friend':
		//UCenter 好友的 Example 代碼
		include 'code/friend.php';
	break;
	case 'avatar':
		//UCenter 設置頭像的 Example 代碼
		include 'code/avatar.php';
	break;
}

echo '<hr />';
if(!$Example_username) {
	//用戶未登錄
	echo '<a href="'.$_SERVER['PHP_SELF'].'?example=login">登錄</a> ';
	echo '<a href="'.$_SERVER['PHP_SELF'].'?example=register">註冊</a> ';
} else {
	//用戶已登錄
	echo '<script src="ucexample.js"></script><div id="append_parent"></div>';
	echo $Example_username.' <a href="'.$_SERVER['PHP_SELF'].'?example=logout">退出</a> ';
	echo ' <a href="'.$_SERVER['PHP_SELF'].'?example=pmlist">短消息列表</a> ';
	echo $newpm ? '<font color="red">New!('.$newpm.')</font> ' : NULL;
	echo '<a href="###" onclick="pmwin(\'open\')">進入短消息中心</a> ';
	echo ' <a href="'.$_SERVER['PHP_SELF'].'?example=friend">好友</a> ';
	echo ' <a href="'.$_SERVER['PHP_SELF'].'?example=avatar">頭像</a> ';
}

?>