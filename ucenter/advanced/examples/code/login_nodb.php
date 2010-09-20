<?php
/**
 * UCenter 應用程序開發 Example
 *
 * 應用程序無數據庫，用戶登錄的 Example 代碼
 * 使用到的接口函數：
 * uc_user_login()	必須，判斷登錄用戶的有效性
 * uc_authcode()	可選，借用用戶中心的函數加解密 Cookie
 * uc_user_synlogin()	可選，生成同步登錄的代碼
 */

if(empty($_POST['submit'])) {
	//登錄表單
	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?example=login">';
	echo '登錄:';
	echo '<dl><dt>用戶名</dt><dd><input name="username"></dd>';
	echo '<dt>密碼</dt><dd><input name="password" type="password"></dd></dl>';
	echo '<input name="submit" type="submit"> ';
	echo '</form>';
} else {
	//通過接口判斷登錄帳號的正確性，返回值為數組
	list($uid, $username, $password, $email) = uc_user_login($_POST['username'], $_POST['password']);

	setcookie('Example_auth', '', -86400);
	if($uid > 0) {
		//用戶登陸成功，設置 Cookie，加密直接用 uc_authcode 函數，用戶使用自己的函數
		setcookie('Example_auth', uc_authcode($uid."\t".$username, 'ENCODE'));
		//生成同步登錄的代碼
		$ucsynlogin = uc_user_synlogin($uid);
		echo '登錄成功'.$ucsynlogin.'<br><a href="'.$_SERVER['PHP_SELF'].'">繼續</a>';
		exit;
	} elseif($uid == -1) {
		echo '用戶不存在,或者被刪除';
	} elseif($uid == -2) {
		echo '密碼錯';
	} else {
		echo '未定義';
	}
}

?>