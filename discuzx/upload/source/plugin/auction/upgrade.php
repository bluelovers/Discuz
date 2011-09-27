<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-01 16:01
 *	Filename: upgrade.php
 *	Description: 升级程序
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<SQL
ALTER TABLE pre_plugin_auction ADD `virtual` TINYINT(1) NOT NULL AFTER `typeid`;
ALTER TABLE pre_plugin_auction CHANGE `extra` `extra` TINYINT(1) NOT NULL;
CREATE TABLE  pre_plugin_auction_message (
 `mid` MEDIUMINT( 8 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `tid` INT( 10 ) UNSIGNED NOT NULL ,
 `message` TEXT NULL ,
 `uid` MEDIUMINT( 8 ) UNSIGNED NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE pre_plugin_auction_xml (
  `clientid` smallint(8) unsigned NOT NULL auto_increment,
  `sign` char(32) NOT NULL,
  PRIMARY KEY  (`clientid`)
) ENGINE=MyISAM;
SQL;
$upgrade_pluginid = DB::result_first("SELECT pluginid FROM ".DB::table('common_plugin')." WHERE identifier='auction'");

$sql .= <<<SQL
UPDATE pre_common_pluginvar SET value='竞价规则：参与用户数不超过商品数，出价为底价或者高于底价。参与用户数超过商品数，出价须高于其出价前时刻能获得商品的最低价格。相同出价先到先得。 <br /> 出价成功后，系统将自动冻结你最后一次出价的金币，竞拍结束后再扣除或返还。虚拟物品的卡密将会自动发送，竞价成功的用户可以在竞价页面查看卡密。' WHERE pluginid='$upgrade_pluginid' AND variable='auc_type2_tips';
UPDATE pre_common_pluginvar SET value='抽奖规则：用户按照发布的价格出价，物品件数有几件，最终就在出价用户中随机抽取几位中奖。<br />出价成功后，系统将自动冻结出价的金币，竞拍结束后再扣除或返还。虚拟物品的卡密将会自动发送，中奖用户可以在抽奖页面查看卡密。' WHERE pluginid='$upgrade_pluginid' AND variable='auc_type1_tips_1';
UPDATE pre_common_pluginvar SET value='兑换规则：用户按照发布的价格出价兑换，物品件数有几件，最先出价的几位用户即兑换成功。虚拟物品的卡密将会自动发送，成功兑换的用户可以在兑换页面查看卡密' WHERE pluginid='$upgrade_pluginid' AND variable='auc_type1_tips_2';
UPDATE pre_common_plugin SET name='积分商城' WHERE pluginid='$upgrade_pluginid';
SQL;

if(strtolower(CHARSET) != 'gbk') {
	include_once libfile('class/chinese');
	$c = new Chinese('GBK', 'UTF-8', true);
	$sql = $c->Convert($sql);
}
runquery($sql);
$finish = true;
?>
