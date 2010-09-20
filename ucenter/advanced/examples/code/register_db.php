<?php
/**
 * UCenter 應用程序開發 Example
 *
 * 應用程序有自己的用戶表，用戶註冊、激活的 Example 代碼
 * 使用到的接口函數：
 * uc_get_user()	必須，獲取用戶的信息
 * uc_user_register()	必須，註冊用戶數據
 * uc_authcode()	可選，借用用戶中心的函數加解密激活字串和 Cookie
 */

if(empty($_POST['submit'])) {
	//註冊表單
	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?example=register">';

	if($_GET['action'] == 'activation') {
		echo '激活:';
		list($activeuser) = explode("\t", uc_authcode($_GET['auth'], 'DECODE'));
		echo '<input type="hidden" name="activation" value="'.$activeuser.'">';
		echo '<dl><dt>用戶名</dt><dd>'.$activeuser.'</dd></dl>';
	} else {
		echo '註冊:';
		echo '<dl><dt>用戶名</dt><dd><input name="username"></dd>';
		echo '<dt>密碼</dt><dd><input name="password"></dd>';
		echo '<dt>Email</dt><dd><input name="email"></dd></dl>';
	}
	echo '<input name="submit" type="submit">';
	echo '</form>';
} else {
	//在UCenter註冊用戶信息
	$username = '';
	if(!empty($_POST['activation']) && ($activeuser = uc_get_user($_POST['activation']))) {
		list($uid, $username) = $activeuser;
	} else {
		if(uc_get_user($_POST['username']) && !$db->result_first("SELECT uid FROM {$tablepre}members WHERE username='$_POST[username]'")) {
			//判斷需要註冊的用戶如果是需要激活的用戶，則需跳轉到登錄頁面驗證
			echo '該用戶無需註冊，請激活該用戶<br><a href="'.$_SERVER['PHP_SELF'].'?example=login">繼續</a>';
			exit;
		}

		$uid = uc_user_register($_POST['username'], $_POST['password'], $_POST['email']);
		if($uid <= 0) {
			if($uid == -1) {
				echo '用戶名不合法';
			} elseif($uid == -2) {
				echo '包含要允許註冊的詞語';
			} elseif($uid == -3) {
				echo '用戶名已經存在';
			} elseif($uid == -4) {
				echo 'Email 格式有誤';
			} elseif($uid == -5) {
				echo 'Email 不允許註冊';
			} elseif($uid == -6) {
				echo '該 Email 已經被註冊';
			} else {
				echo '未定義';
			}
		} else {
			$username = $_POST['username'];
		}
	}
	if($username) {
		$db->query("INSERT INTO {$tablepre}members (uid,username,admin) VALUES ('$uid','$username','0')");
		//註冊成功，設置 Cookie，加密直接用 uc_authcode 函數，用戶使用自己的函數
		setcookie('Example_auth', uc_authcode($uid."\t".$username, 'ENCODE'));
		echo '註冊成功<br><a href="'.$_SERVER['PHP_SELF'].'">繼續</a>';
		exit;
	}
}

?>