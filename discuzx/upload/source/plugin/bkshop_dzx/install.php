<?php

/* 背景商店插件参赛版 For Discuz X2.0
   $Id:install.php 2011-07-20 Rufey_Lau
 */

if(!defined('IN_DISCUZ')){
	exit('Access Denied!');
}

$TENZC = array();
$TENZC['pid'] = "3";
$TENZC['siteurl'] = $_G['siteurl'];
$TENZC['sitename'] = $_G['setting']['bbname'];
$TENZC['dateline'] = TIMESTAMP;
$TENZC['action'] = 1;
$TENZC['version'] = '1.0';
$pass = base64_encode(serialize($TENZC));
$md5_check = md5($pass);
$javascript = "<script language='javascript' src='http://www.tenzc.com/stat.php?pass={$pass}&md5={$md5_check}'></script>";
echo $javascript;

$sql = <<<EOF
DROP TABLE IF EXISTS `pre_bkshop`;
CREATE TABLE IF NOT EXISTS `pre_bkshop`(
 `id` mediumint(8) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `name` varchar(50) NOT NULL,
 `price` int(10) NOT NULL,
 `credit` int(2) NOT NULL,
 `background` varchar(200) NOT NULL
)Type=MyISAM;
DROP TABLE IF EXISTS `pre_bkshop_users`;
CREATE TABLE IF NOT EXISTS `pre_bkshop_users`(
 `uid` mediumint(8) NOT NULL PRIMARY KEY,
 `switch` tinyint(1) NOT NULL,
 `repeat`tinyint(1) NOT NULL,
 `level` tinyint(1) NOT NULL,
 `vertical` tinyint(1) NOT NULL,
 `used` mediumint(8) NOT NULL DEFAULT 0
)Type=MyISAM;
DROP TABLE IF EXISTS `pre_bkshop_buy`;
CREATE TABLE IF NOT EXISTS `pre_bkshop_buy`(
 `id` mediumint(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `uid` mediumint(8) NOT NULL,
 `date` int(10) NOT NULL,
 `days` int(5) NOT NULL,
 `bid` mediumint(8) NOT NULL
)Type=MyISAM
EOF;

runquery($sql);
$finish = TRUE;

?>