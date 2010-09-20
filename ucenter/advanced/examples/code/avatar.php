<?php
/**
 * UCenter 應用程序開發 Example
 *
 * 設置頭像的 Example 代碼
 */

echo '
<img src="'.UC_API.'/avatar.php?uid='.$Example_uid.'&size=big">
<img src="'.UC_API.'/avatar.php?uid='.$Example_uid.'&size=middle">
<img src="'.UC_API.'/avatar.php?uid='.$Example_uid.'&size=small">
<br><br>'.uc_avatar($Example_uid);

?>