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

echo '<iframe id="frame_content" src="http://www.3g4k.com/wbkversion.html" scrolling="no" frameborder="0" onload="this.height=this.contentWindow.document.documentElement.scrollHeight" style="position:absolute; left:0px; top:50px; width:100%; border:0px;"></iframe>';

?>
