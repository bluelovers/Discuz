<?php
/**
 * UCenter 應用程序開發 Example
 *
 * 用戶退出的 Example 代碼
 * 使用到的接口函數：
 * uc_user_synlogout()	可選，生成同步退出的代碼
 */

setcookie('Example_auth', '', -86400);
//生成同步退出的代碼
$ucsynlogout = uc_user_synlogout();
echo '退出成功'.$ucsynlogout.'<br><a href="'.$_SERVER['PHP_SELF'].'">繼續</a>';
exit;

?>