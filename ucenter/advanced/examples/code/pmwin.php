<?php
/**
 * UCenter 應用程序開發 Example
 *
 * 開啟短消息中心的 Example 代碼
 * 使用到的接口函數：
 * uc_pm()		必須，跳轉到短消息中心的 URL
 * uc_pm_checknew()	可選，用於全局判斷是否有新短消息，返回 $newpm 變量
 */

//打開短消息中心的窗口
uc_pm_location($Example_uid, $newpm);
exit;

?>