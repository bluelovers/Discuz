<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$version = '9.4.2';
$sql = <<<EOF
DROP TABLE IF EXISTS kfsm_apply;
CREATE TABLE kfsm_apply (
  aid smallint(6) NOT NULL AUTO_INCREMENT,
  sid smallint(6) unsigned zerofill NOT NULL DEFAULT '000000',
  stockname varchar(20) NOT NULL DEFAULT '',
  userid int(10) NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  stockprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  stocknum int(20) unsigned NOT NULL DEFAULT '0',
  surplusnum int(20) unsigned NOT NULL DEFAULT '0',
  capitalisation decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  comphoto varchar(20) NOT NULL DEFAULT '0.jpg',
  comintro varchar(255) NOT NULL DEFAULT '',
  applytime int(10) unsigned NOT NULL DEFAULT '0',
  issuetime int(10) unsigned NOT NULL DEFAULT '0',
  state tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (aid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS kfsm_customer;
CREATE TABLE kfsm_customer (
  cid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  sid smallint(6) unsigned zerofill NOT NULL DEFAULT '000000',
  stocknum int(20) unsigned NOT NULL DEFAULT '0',
  buyprice decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  averageprice decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  buytime int(10) unsigned NOT NULL DEFAULT '0',
  selltime int(10) unsigned NOT NULL DEFAULT '0',
  ip VARCHAR( 20 ) NOT NULL DEFAULT ''
) ENGINE=MyISAM;


DROP TABLE IF EXISTS kfsm_deal;
CREATE TABLE kfsm_deal (
  did smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  sid int(6) unsigned zerofill NOT NULL DEFAULT '000000',
  price_deal decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant_deal int(10) unsigned NOT NULL DEFAULT '0',
  time_deal int(10) unsigned NOT NULL DEFAULT '0',
  price_tran decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant_tran int(10) unsigned NOT NULL DEFAULT '0',
  time_tran int(10) unsigned NOT NULL DEFAULT '0',
  direction tinyint(1) NOT NULL DEFAULT '0',
  ok tinyint(1) NOT NULL DEFAULT '0',
  hide tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (did)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS kfsm_news;
CREATE TABLE kfsm_news (
  nid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL DEFAULT '',
  content mediumtext,
  color char(6) NOT NULL DEFAULT '',
  author varchar(15) NOT NULL DEFAULT '',
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (nid)
) ENGINE=MyISAM;

INSERT INTO kfsm_news (subject, content, color, author, addtime) VALUES ('欢迎使用 Kilofox StockIns V{$version} for Discuz! X2', '欢迎使用千狐 StockIns 虚拟股市系统！\nStockIns 是一款运用面向对象思想编写的 PHP 软件，是专为国内主流 PHP 论坛而开发的插件产品。该版本为 Discuz! 插件版。\n获得更多资讯，请您关注官方网站——[url=http://www.kilofox.net]Kilofox.Net[/url]', '', 'Kilofox.Net', '{$_G[timestamp]}');


DROP TABLE IF EXISTS kfsm_sminfo;
CREATE TABLE kfsm_sminfo (
  id smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  todaybuy int(20) unsigned NOT NULL DEFAULT '0',
  todaysell int(20) unsigned NOT NULL DEFAULT '0',
  todaytotal decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  todaydate int(10) NOT NULL DEFAULT '0',
  ain_y decimal(14,3) unsigned NOT NULL DEFAULT '0.000',
  ain_t decimal(14,3) unsigned NOT NULL DEFAULT '0.000',
  stampduty decimal(14,3) unsigned NOT NULL DEFAULT '0.000',
  KEY id (id)
) ENGINE=MyISAM;

INSERT INTO kfsm_sminfo (todaybuy, todaysell, todaytotal, todaydate, ain_y, ain_t, stampduty) VALUES(0, 0, 0, '{$_G[timestamp]}',1000, 1000, 0.000);


DROP TABLE IF EXISTS kfsm_smlog;
CREATE TABLE kfsm_smlog (
  id int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL DEFAULT '',
  username1 varchar(20) NOT NULL DEFAULT '',
  username2 varchar(20) NOT NULL DEFAULT '',
  field varchar(20) NOT NULL DEFAULT '',
  descrip varchar(80) NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS kfsm_stock;
CREATE TABLE kfsm_stock (
  sid smallint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  stockname varchar(20) NOT NULL DEFAULT '',
  openprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  currprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  lowprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  highprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  todaywave decimal(6,2) NOT NULL DEFAULT '0.00',
  totalwave decimal(6,2) NOT NULL DEFAULT '0.00',
  todaybuynum int(20) unsigned NOT NULL DEFAULT '0',
  todaysellnum int(20) unsigned NOT NULL DEFAULT '0',
  todaytradenum int(20) unsigned NOT NULL DEFAULT '0',
  issueprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  issuenum int(20) unsigned NOT NULL DEFAULT '0',
  issuer_id mediumint(8) NOT NULL DEFAULT '0',
  issuer_name varchar(20) NOT NULL DEFAULT '',
  holder_id mediumint(8) NOT NULL DEFAULT '0',
  holder_name varchar(20) NOT NULL DEFAULT '',
  issuetime int(10) unsigned NOT NULL DEFAULT '0',
  comphoto varchar(20) NOT NULL DEFAULT '0.jpg',
  comintro varchar(255) NOT NULL DEFAULT '',
  pricedata varchar(255) NOT NULL DEFAULT '',
  state tinyint(1) NOT NULL DEFAULT '0',
  cid int(6) NOT NULL DEFAULT '0',
  uptime int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (sid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS kfsm_transaction;
CREATE TABLE kfsm_transaction (
  tid int(10) unsigned NOT NULL AUTO_INCREMENT,
  sid int(6) unsigned zerofill NOT NULL DEFAULT '000000',
  stockname varchar(20) NOT NULL DEFAULT '',
  direction tinyint(1) unsigned NOT NULL DEFAULT '0',
  did int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  price decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant int(10) unsigned NOT NULL DEFAULT '0',
  amount decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  ttime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS kfsm_user;
CREATE TABLE kfsm_user (
  uid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  forumuid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  capital decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  capital_ava decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  asset decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  stocksort smallint(6) unsigned NOT NULL DEFAULT '0',
  stocknum int(20) unsigned NOT NULL DEFAULT '0',
  stockcost decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  stockvalue decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  todaybuy int(10) unsigned NOT NULL DEFAULT '0',
  todaysell int(10) unsigned NOT NULL DEFAULT '0',
  regtime int(10) unsigned NOT NULL DEFAULT '0',
  lasttradetime int(10) unsigned NOT NULL DEFAULT '0',
  locked boolean NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

EOF;
runquery($sql);
$finish = TRUE;
?>
