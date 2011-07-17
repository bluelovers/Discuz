<?php
/**
 +--------------------------------------------------
 |discuz!x2.0 插件： 微博控
 +--------------------------------------------------
 |author：luofei614<www.3g4k.com>
 +--------------------------------------------------
 * 用户能绑定新浪、腾讯、网易、搜狐的微博。
 * 绑定后，用户以后不需要再登录微博，就可以向多个微博同步信息。
 + -------------------------------------------------
 * 重新封装了OAuth类，四个微博使用共同的接口，减少了冗余代码，同时避免了类名冲突。
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//读取当前用户绑定的微博。
$query = DB::query("select apiname from " . DB::table("share_keys") . " where uid='{$_G['uid']}'");
$apis = array();
while ($arr = DB::fetch($query)) {
    $apis[] = $arr['apiname']; //已建立连接的api
}
//读取设置的需要同步信息的操作
$actions_str = DB::result_first("select actions from " . DB::table("share_actions") . " where uid='{$_G['uid']}'");
if ($actions_str === false && !empty($apis)) {
//默认全选操作
    $actions_str = "topicsubmit|replysubmit|addsubmit|viewAlbumid|blogsubmit";
    $setarr = array(
        'uid' => $_G['uid'],
        'actions' => $actions_str
    );
    DB::insert("share_actions", $setarr, false, true);
}

foreach (explode("|", $actions_str) as $val) {
    $actions[$val] = ' checked="checked"'; //标记勾选
}
?>
