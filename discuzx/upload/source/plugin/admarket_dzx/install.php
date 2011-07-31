<?php
/*
	Advertisement Centre Database Struct For Discuz! X2 by sw08
	安裝程序
	最後修改:2011-7-20 16:58:49
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$timestamp = TIMESTAMP;

$sql = <<<EOF


DROP TABLE IF EXISTS pre_advmarket_operatelog;
CREATE TABLE `pre_advmarket_operatelog` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`uid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`username` VARCHAR( 255 ) NOT NULL ,
`dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`typeid` MEDIUMINT( 4 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '行爲分組',
`action` VARCHAR( 255 ) NOT NULL COMMENT '動作',
`extra` MEDIUMINT(8) NOT NULL DEFAULT '0',
PRIMARY KEY  (`id`)
) ENGINE = MYISAM COMMENT = '操作記錄';

DROP TABLE IF EXISTS pre_advmarket;
CREATE TABLE `pre_advmarket` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`desc` TEXT NOT NULL,
`allowedit` TINYINT( 1 ) NOT NULL DEFAULT '0',
`orders` mediumint(3) unsigned NOT NULL default '0',
`catagory` mediumint(3) unsigned NOT NULL default '0',
`type` mediumint(3) unsigned NOT NULL default '0',
`stats` smallint(3) NOT NULL default '0',
`verify` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
`bid` mediumint(8) unsigned NOT NULL default '0',
`pubuid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`pubusername` VARCHAR( 255 ) NOT NULL ,
`pubdateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',  
`buyuid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`buyusername` VARCHAR( 255 ) NOT NULL ,
`buydateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`minbuy` mediumint(8) unsigned NOT NULL default '0',
`maxbuy` mediumint(8) unsigned NOT NULL default '0',
`sellext` mediumint(3) unsigned NOT NULL default '0',
`price` mediumint(8) unsigned NOT NULL default '0',
`clickext` mediumint(3) unsigned NOT NULL default '0',
`clickpay` mediumint(8) unsigned NOT NULL default '0',
`totalfee` mediumint(8) unsigned NOT NULL default '0',
`payext` mediumint(3) unsigned NOT NULL default '0',
`payfee` mediumint(8) unsigned NOT NULL default '0',
`paycount` mediumint(3) unsigned NOT NULL default '0',
`count` mediumint(8) unsigned NOT NULL default '0',
`usecount` mediumint(8) unsigned NOT NULL default '0',
`paypolicy` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
`restcount` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`expire` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`redirectlink` text NOT NULL,
`clicktime` mediumint(3) unsigned NOT NULL default '15',
`maxpayday` mediumint(8) unsigned NOT NULL default '100',
`allowbuygroup` text NOT NULL,
`allowrefundgroup` text NOT NULL,
`allowsellgroup` text NOT NULL,
`allowautiongroup` text NOT NULL,
`showway` text NOT NULL,
`showplace` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

EOF;

runquery($sql);

DB::insert('common_cron', array('available' => 1, 'type' => 'user', 'name' => '自助廣告商城_數據處理', 'filename' => 'cron_admarket.php', 'nextrun' => ($timestamp - ($timestamp%86400) + 86400),'weekday' => -1, 'day' => -1, 'hour' => 0, 'minute' => '0'));

$finish = TRUE;
?>