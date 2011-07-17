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
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$_GET['finish'] = (isset($_GET['finish']) && $_GET['finish'] == 1) ? 1 : 0;
if (!$_GET['finish']) {
    //第一步，安装并显示环境测试
    $sql = <<<EOF
DROP TABLE IF EXISTS `cdb_share_actions`;
CREATE TABLE `cdb_share_actions` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `actions` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `cdb_share_keys`;
CREATE TABLE `cdb_share_keys` (
  `uid` mediumint(8) unsigned DEFAULT NULL,
  `apiname` varchar(20) DEFAULT NULL,
  `keyarr` varchar(255) DEFAULT NULL,
  UNIQUE KEY `uid` (`uid`,`apiname`)
) TYPE=MyISAM;
EOF;
runquery($sql);
    echo '<iframe  src="source/plugin/wb_share_dzx/checkenv.php?installtype='.$_GET['installtype'].'" scrolling="no" frameborder="0" onload="this.height=this.contentWindow.document.documentElement.scrollHeight" style="position:absolute; left:0px; top:50px; width:100%; border:0px;"></iframe>';
} else {
    //第二步，显示安装完成
    $finish = TRUE;
}
?>