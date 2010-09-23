-- phpMyAdmin SQL Dump
-- version 3.3.0
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 生成日期: 2010 年 05 月 04 日 19:42
-- 服務器版本: 5.5.2
-- PHP 版本: 5.2.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 數據庫: `trunk`
--

-- --------------------------------------------------------

--
-- 表的結構 'brand_adminsession'
--

DROP TABLE IF EXISTS brand_adminsession;
CREATE TABLE brand_adminsession (
  uid mediumint(8) unsigned NOT NULL default '0',
  ip char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  errorcount tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MEMORY;

-- --------------------------------------------------------

--
-- 表的結構 `brand_albumitems`
--

DROP TABLE IF EXISTS `brand_albumitems`;
CREATE TABLE `brand_albumitems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `catid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '分類id',
  `frombbs` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否為從論壇導入的相冊',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayorder` smallint(6) NOT NULL DEFAULT '100',
  `displayorder_s` smallint(6) NOT NULL DEFAULT '100',
  `username` char(15) NOT NULL,
  `subject` char(80) NOT NULL DEFAULT '',
  `subjectimage` char(160) NOT NULL DEFAULT '',
  `description` char(255) NOT NULL COMMENT '相冊描述',
  `picnum` smallint(6) NOT NULL DEFAULT '0' COMMENT '相冊圖片數',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reportnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `grade_s` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `grade` tinyint(1) unsigned NOT NULL DEFAULT '3',
  `updateverify` tinyint(1) unsigned NULL DEFAULT '0',
  `bbstid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `shopid` (`shopid`, `grade_s`, `grade`),
  KEY `catid` (`catid`, `grade_s`, `grade`, `displayorder`),
  KEY `catid_s` (`catid`, `grade_s`, `grade`, `displayorder_s`),
  KEY `frombbs` (`frombbs`, `grade`),
  KEY `tid` (`tid`),
  KEY `grade_s` (`grade_s`, `grade`, `displayorder`),
  KEY `grade` (`grade`, `displayorder`),
  KEY `updateverify` (`updateverify`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_attachments`
--

DROP TABLE IF EXISTS `brand_attachments`;
CREATE TABLE `brand_attachments` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `isavailable` tinyint(1) NOT NULL DEFAULT '0',
  `type` char(30) NOT NULL DEFAULT '',
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` char(150) NOT NULL DEFAULT '',
  `subject` char(80) NOT NULL DEFAULT '',
  `attachtype` char(10) NOT NULL DEFAULT '',
  `isimage` tinyint(1) NOT NULL DEFAULT '0',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `filepath` char(200) NOT NULL DEFAULT '',
  `thumbpath` char(200) NOT NULL DEFAULT '',
  `downloads` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `hash` char(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`aid`),
  KEY `hash` (`hash`),
  KEY `itemid` (`itemid`),
  KEY `uid` (`uid`,`type`,`dateline`),
  KEY `type` (`type`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_attribute`
--

DROP TABLE IF EXISTS `brand_attribute`;
CREATE TABLE `brand_attribute` (
  `attr_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '屬性id',
  `cat_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '所屬分類id',
  `attr_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '屬性類型',
  `attr_name` varchar(50) DEFAULT NULL COMMENT '屬性名稱',
  `attr_row` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '屬性所在itemattr表的列數',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100' COMMENT '顯示順序',
  PRIMARY KEY (`attr_id`),
  UNIQUE KEY `attr_cat` (`cat_id`, `attr_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 COMMENT='屬性列表';

-- --------------------------------------------------------

--
-- 表的結構 `brand_attrvalue`
--

DROP TABLE IF EXISTS `brand_attrvalue`;
CREATE TABLE `brand_attrvalue` (
  `attr_valueid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '屬性值id',
  `attr_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '屬性id',
  `attr_text` varchar(255) DEFAULT NULL COMMENT '屬性值名稱',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100' COMMENT '顯示順序',
  PRIMARY KEY (`attr_valueid`),
  UNIQUE KEY `attr_id` (`attr_id`, `attr_text`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 COMMENT='屬性可選值列表';

-- --------------------------------------------------------

--
-- 表的結構 `brand_attrvalue_text`
--

DROP TABLE IF EXISTS `brand_attrvalue_text`;
CREATE TABLE `brand_attrvalue_text` (
  `attr_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '屬性id',
  `item_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '信息id',
  `attr_text` varchar(255) DEFAULT NULL COMMENT '文本屬性值',
  UNIQUE KEY `item_id` (`item_id`, `attr_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 COMMENT='文本類型屬性值列表';

-- --------------------------------------------------------

--
-- 表的結構 `brand_blocks`
--

DROP TABLE IF EXISTS `brand_blocks`;
CREATE TABLE `brand_blocks` (
  `blockid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `blocktype` varchar(20) NOT NULL DEFAULT '',
  `blockname` varchar(80) NOT NULL DEFAULT '',
  `blockmodel` tinyint(1) NOT NULL DEFAULT '1',
  `blocktext` text NOT NULL,
  `blockcode` text NOT NULL,
  `tplname` varchar(50) NULL DEFAULT '',
  PRIMARY KEY (`blockid`)
) ENGINE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_brandlinks`
--

DROP TABLE IF EXISTS `brand_brandlinks`;
CREATE TABLE `brand_brandlinks` (
  `linkid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `displayorder` tinyint(3) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`linkid`),
  KEY `shopid` (`shopid`, `displayorder`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_cache`
--

DROP TABLE IF EXISTS `brand_cache`;
CREATE TABLE `brand_cache` (
  `cachekey` varchar(16) NOT NULL DEFAULT '',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `cachename` varchar(20) NOT NULL DEFAULT '',
  `value` mediumtext NOT NULL,
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cachekey`)
) ENGINE=MyISAM;

-- --------------------------------------------------------
--
-- 表的結構 `brand_cachenotes`
--

DROP TABLE IF EXISTS `brand_cachenotes`;
CREATE TABLE `brand_cachenotes` (
  `cachekey` varchar(16) NOT NULL COMMENT '緩存key',
  `pagetype` varchar(10) NOT NULL COMMENT '頁面類別',
  `usetype` varchar(10) NOT NULL COMMENT '緩存功能類別',
  `shopid` mediumint(8) unsigned NOT NULL COMMENT '緩存店舖id',
  `infoid` mediumint(8) unsigned NOT NULL COMMENT '信息id',
  PRIMARY KEY (`cachekey`),
  KEY `type` (`pagetype`,`usetype`,`shopid`,`infoid`),
  KEY `shopid` (`shopid`),
  KEY `subid` (`infoid`),
  KEY `usetype` (`usetype`)
) ENGINE=MEMORY COMMENT='緩存類型關係對應表';

--
-- 轉存表中的數據 `brand_cachenotes`
--

-- --------------------------------------------------------
--
-- 表的結構 `brand_categories`
--

DROP TABLE IF EXISTS `brand_categories`;
CREATE TABLE `brand_categories` (
  `catid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '分類id',
  `upid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '上級分類id',
  `type` char(10) NOT NULL DEFAULT '' COMMENT '分類類型',
  `name` char(20) NOT NULL DEFAULT '' COMMENT '分類名稱',
  `note` char(20) NOT NULL COMMENT '分類別名',
  `displayorder` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '顯示順序',
  `subcatid` text NOT NULL COMMENT '查詢子分類id',
  `cmid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '關聯點評模型id',
  `commtmodel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否開啟點評模型',
  PRIMARY KEY (`catid`),
  KEY `type` (`type`, `displayorder`),
  KEY `upid` (`upid`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=6 ;

--
-- 轉存表中的數據 `brand_categories`
--

INSERT INTO `brand_categories` (`catid`, `upid`, `type`, `name`, `note`, `displayorder`, `subcatid`, `cmid`, `commtmodel`) VALUES
(1, 0, 'shop', '家居街', '', 0, '1, 14, 15, 16, 17', 0, 0),
(2, 0, 'album', '婚嫁街', '', 0, '2, 64, 65, 66, 67', 0, 0),
(3, 0, 'good', '家居街', '', 0, '3, 45, 46, 47, 48', 0, 0),
(4, 0, 'consume', '家居街', '', 0, '4, 86, 87, 88, 89', 0, 0),
(5, 0, 'notice', '促銷信息', '', 0, '5', 0, 0),
(6, 0, 'region', '默認地區設置', '', 0, '6', 0, 0),
(7, 0, 'groupbuy', '婚嫁街', '', 0, '7, 112, 113, 114, 115', 0, 0),
(8, 0, 'shop', '婚嫁街', '', 0, '8, 18, 19, 20, 21', 0, 0),
(9, 0, 'shop', '汽車街', '', 0, '9, 22, 23', 0, 0),
(10, 0, 'shop', '女人街', '', 0, '10, 24, 25, 26, 27', 0, 0),
(11, 0, 'shop', '美食街', '', 0, '11, 28, 29, 30, 31, 32, 33', 0, 0),
(12, 0, 'shop', '親子街', '', 0, '12, 34, 35, 36, 37', 0, 0),
(13, 0, 'shop', '遊戲數碼', '', 0, '13, 38, 39, 40, 41', 0, 0),
(14, 1, 'shop', '整體廚房', '', 0, '14', 0, 0),
(15, 1, 'shop', '衛浴瓷磚', '', 0, '15', 0, 0),
(16, 1, 'shop', '集成吊頂', '', 0, '16', 0, 0),
(17, 1, 'shop', '地板地暖', '', 0, '17', 0, 0),
(18, 8, 'shop', '知名影樓', '', 0, '18', 0, 0),
(19, 8, 'shop', '品牌珠寶', '', 0, '19', 0, 0),
(20, 8, 'shop', '閃亮美鑽', '', 0, '20', 0, 0),
(21, 8, 'shop', '婚紗禮服', '', 0, '21', 0, 0),
(22, 9, 'shop', '4S店', '', 0, '22', 0, 0),
(23, 9, 'shop', '保養護理', '', 0, '23', 0, 0),
(24, 10, 'shop', '護膚彩妝', '', 0, '24', 0, 0),
(25, 10, 'shop', '美容美發', '', 0, '25', 0, 0),
(26, 10, 'shop', '品牌服飾', '', 0, '26', 0, 0),
(27, 10, 'shop', '潮人飾品', '', 0, '27', 0, 0),
(28, 11, 'shop', '杭幫菜', '', 0, '28', 0, 0),
(29, 11, 'shop', '茶館', '', 0, '29', 0, 0),
(30, 11, 'shop', '咖啡館', '', 0, '30', 0, 0),
(31, 11, 'shop', '江浙菜', '', 0, '31', 0, 0),
(32, 11, 'shop', '湘菜', '', 0, '32', 0, 0),
(33, 11, 'shop', '家常菜', '', 0, '33', 0, 0),
(34, 12, 'shop', '孕嬰服務', '', 0, '34', 0, 0),
(35, 12, 'shop', '寶寶服飾', '', 0, '35', 0, 0),
(36, 12, 'shop', '媽媽用品', '', 0, '36', 0, 0),
(37, 12, 'shop', '食品保健', '', 0, '37', 0, 0),
(38, 13, 'shop', '電腦', '', 0, '38', 0, 0),
(39, 13, 'shop', '手機', '', 0, '39', 0, 0),
(40, 13, 'shop', '相機', '', 0, '40', 0, 0),
(41, 13, 'shop', '遊戲', '', 0, '41', 0, 0),
(42, 0, 'good', '婚嫁街', '', 0, '42, 49, 50, 51, 52', 0, 0),
(43, 0, 'good', '女人街', '', 0, '43, 53, 54, 55, 56', 0, 0),
(44, 0, 'good', '遊戲數碼', '', 0, '44, 57, 58, 59, 60', 0, 0),
(45, 3, 'good', '整體廚房', '', 0, '45', 0, 0),
(46, 3, 'good', '衛浴瓷磚', '', 0, '46', 0, 0),
(47, 3, 'good', '集成吊頂', '', 0, '47', 0, 0),
(48, 3, 'good', '地板地暖', '', 0, '48', 0, 0),
(49, 42, 'good', '知名影樓', '', 0, '49', 0, 0),
(50, 42, 'good', '品牌珠寶', '', 0, '50', 0, 0),
(51, 42, 'good', '閃亮美鑽', '', 0, '51', 0, 0),
(52, 42, 'good', '婚紗禮服', '', 0, '52', 0, 0),
(53, 43, 'good', '護膚彩妝', '', 0, '53', 0, 0),
(54, 43, 'good', '美容美發', '', 0, '54', 0, 0),
(55, 43, 'good', '品牌服飾', '', 0, '55', 0, 0),
(56, 43, 'good', '潮人飾品', '', 0, '56', 0, 0),
(57, 44, 'good', '電腦', '', 0, '57', 0, 0),
(58, 44, 'good', '手機', '', 0, '58', 0, 0),
(59, 44, 'good', '相機', '', 0, '59', 0, 0),
(60, 44, 'good', '遊戲', '', 0, '60', 0, 0),
(61, 0, 'album', '美食街', '', 0, '61, 68, 69, 70, 71, 72, 73', 0, 0),
(62, 0, 'album', '女人街', '', 0, '62, 74, 75, 76, 77', 0, 0),
(63, 0, 'album', '遊戲數碼', '', 0, '63, 78, 79, 80, 81', 0, 0),
(64, 2, 'album', '知名影樓', '', 0, '64', 0, 0),
(65, 2, 'album', '品牌珠寶', '', 0, '65', 0, 0),
(66, 2, 'album', '閃亮美鑽', '', 0, '66', 0, 0),
(67, 2, 'album', '婚紗禮服', '', 0, '67', 0, 0),
(68, 61, 'album', '杭幫菜', '', 0, '68', 0, 0),
(69, 61, 'album', '茶館', '', 0, '69', 0, 0),
(70, 61, 'album', '咖啡館', '', 0, '70', 0, 0),
(71, 61, 'album', '江浙菜', '', 0, '71', 0, 0),
(72, 61, 'album', '湘菜', '', 0, '72', 0, 0),
(73, 61, 'album', '家常菜', '', 0, '73', 0, 0),
(74, 62, 'album', '護膚彩妝', '', 0, '74', 0, 0),
(75, 62, 'album', '美容美發', '', 0, '75', 0, 0),
(76, 62, 'album', '品牌服飾', '', 0, '76', 0, 0),
(77, 62, 'album', '潮人飾品', '', 0, '77', 0, 0),
(78, 63, 'album', '電腦', '', 0, '78', 0, 0),
(79, 63, 'album', '手機', '', 0, '79', 0, 0),
(80, 63, 'album', '相機', '', 0, '80', 0, 0),
(81, 63, 'album', '遊戲', '', 0, '81', 0, 0),
(82, 0, 'consume', '婚嫁街', '', 0, '82, 90, 91, 92, 93', 0, 0),
(83, 0, 'consume', '女人街', '', 0, '83, 94, 95, 96, 97', 0, 0),
(84, 0, 'consume', '美食街', '', 0, '84, 98, 99, 100, 101, 102, 103', 0, 0),
(85, 0, 'consume', '遊戲數碼', '', 0, '85, 104, 105, 106, 107', 0, 0),
(86, 4, 'consume', '整體廚房', '', 0, '86', 0, 0),
(87, 4, 'consume', '衛浴瓷磚', '', 0, '87', 0, 0),
(88, 4, 'consume', '集成吊頂', '', 0, '88', 0, 0),
(89, 4, 'consume', '地板地暖', '', 0, '89', 0, 0),
(90, 82, 'consume', '知名影樓', '', 0, '90', 0, 0),
(91, 82, 'consume', '品牌珠寶', '', 0, '91', 0, 0),
(92, 82, 'consume', '閃亮美鑽', '', 0, '92', 0, 0),
(93, 82, 'consume', '婚紗禮服', '', 0, '93', 0, 0),
(94, 83, 'consume', '護膚彩妝', '', 0, '94', 0, 0),
(95, 83, 'consume', '美容美發', '', 0, '95', 0, 0),
(96, 83, 'consume', '品牌服飾', '', 0, '96', 0, 0),
(97, 83, 'consume', '潮人飾品', '', 0, '97', 0, 0),
(98, 84, 'consume', '杭幫菜', '', 0, '98', 0, 0),
(99, 84, 'consume', '茶館', '', 0, '99', 0, 0),
(100, 84, 'consume', '咖啡館', '', 0, '100', 0, 0),
(101, 84, 'consume', '江浙菜', '', 0, '101', 0, 0),
(102, 84, 'consume', '湘菜', '', 0, '102', 0, 0),
(103, 84, 'consume', '家常菜', '', 0, '103', 0, 0),
(104, 85, 'consume', '電腦', '', 0, '104', 0, 0),
(105, 85, 'consume', '手機', '', 0, '105', 0, 0),
(106, 85, 'consume', '相機', '', 0, '106', 0, 0),
(107, 85, 'consume', '遊戲', '', 0, '107', 0, 0),
(108, 0, 'groupbuy', '遊戲數碼', '', 0, '108, 116, 117, 118, 119', 0, 0),
(109, 0, 'groupbuy', '美食街', '', 0, '109, 120, 121, 122, 123, 124, 125', 0, 0),
(110, 0, 'groupbuy', '女人街', '', 0, '110, 126, 127, 128, 129', 0, 0),
(112, 7, 'groupbuy', '知名影樓', '', 0, '112', 0, 0),
(113, 7, 'groupbuy', '品牌珠寶', '', 0, '113', 0, 0),
(114, 7, 'groupbuy', '閃亮美鑽', '', 0, '114', 0, 0),
(115, 7, 'groupbuy', '婚紗禮服', '', 0, '115', 0, 0),
(116, 108, 'groupbuy', '電腦', '', 0, '116', 0, 0),
(117, 108, 'groupbuy', '手機', '', 0, '117', 0, 0),
(118, 108, 'groupbuy', '相機', '', 0, '118', 0, 0),
(119, 108, 'groupbuy', '遊戲', '', 0, '119', 0, 0),
(120, 109, 'groupbuy', '杭幫菜', '', 0, '120', 0, 0),
(121, 109, 'groupbuy', '茶館', '', 0, '121', 0, 0),
(122, 109, 'groupbuy', '咖啡館', '', 0, '122', 0, 0),
(123, 109, 'groupbuy', '江浙菜', '', 0, '123', 0, 0),
(124, 109, 'groupbuy', '湘菜', '', 0, '124', 0, 0),
(125, 109, 'groupbuy', '家常菜', '', 0, '125', 0, 0),
(126, 110, 'groupbuy', '護膚彩妝', '', 0, '126', 0, 0),
(127, 110, 'groupbuy', '美容美發', '', 0, '127', 0, 0),
(128, 110, 'groupbuy', '品牌服飾', '', 0, '128', 0, 0),
(129, 110, 'groupbuy', '潮人飾品', '', 0, '129', 0, 0),
(130, 0, 'notice', '本店資訊', '', 0, '130', 0, 0);

-- --------------------------------------------------------

--
-- 表的結構 `brand_commentmodels`
--

DROP TABLE IF EXISTS `brand_commentmodels`;
CREATE TABLE `brand_commentmodels` (
  `cmid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `modelname` char(20) NOT NULL DEFAULT '',
  `modeltype` char(10) NOT NULL DEFAULT '',
  `scorenum` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `scorename` varchar(255) NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

INSERT INTO `brand_commentmodels` (`cmid`, `modelname`, `modeltype`, `scorenum`, `scorename`, `dateline`) VALUES
(1, '美食街', '', 3, 'a:3:{i:1;s:4:"口味";i:2;s:4:"環境";i:3;s:4:"服務";}', 1281096294),
(2, '女人街', '', 3, 'a:3:{i:1;s:4:"效果";i:2;s:4:"環境";i:3;s:4:"服務";}', 1281096448);

-- --------------------------------------------------------

--
-- 表的結構 `brand_commentscores`
--

DROP TABLE IF EXISTS `brand_commentscores`;
CREATE TABLE `brand_commentscores` (
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `score` float(3,2) unsigned NOT NULL DEFAULT '0.00',
  `score1` tinyint(1) NOT NULL DEFAULT '0',
  `score2` tinyint(1) NOT NULL DEFAULT '0',
  `score3` tinyint(1) NOT NULL DEFAULT '0',
  `score4` tinyint(1) NOT NULL DEFAULT '0',
  `score5` tinyint(1) NOT NULL DEFAULT '0',
  `score6` tinyint(1) NOT NULL DEFAULT '0',
  `score7` tinyint(1) NOT NULL DEFAULT '0',
  `score8` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的結構 `brand_consumeitems`
--

DROP TABLE IF EXISTS `brand_consumeitems`;
CREATE TABLE `brand_consumeitems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所屬店舖id',
  `catid` smallint(6) unsigned DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `displayorder_s` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `username` char(15) NOT NULL DEFAULT '',
  `subject` char(80) NOT NULL DEFAULT '',
  `subjectimage` char(160) NOT NULL DEFAULT '',
  `imagetype` tinyint(1) NOT NULL DEFAULT '1',
  `imgtplid` tinyint(1) NOT NULL DEFAULT '0',
  `rates` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reportnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) NOT NULL DEFAULT '1',
  `grade_s` tinyint(1) NOT NULL DEFAULT '3',
  `grade` tinyint(1) NOT NULL DEFAULT '3',
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `validity_start` int(10) NOT NULL DEFAULT '0',
  `validity_end` int(10) NOT NULL DEFAULT '0',
  `downnum` int(10) unsigned NOT NULL DEFAULT '0',
  `updateverify` tinyint(1) DEFAULT '0',
  `bbstid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `shopid` (`shopid`, `grade_s`, `grade`),
  KEY `catid` (`catid`, `grade_s`, `grade`, `validity_end`, `displayorder`),
  KEY `catid_s` (`catid`, `grade_s`, `grade`, `validity_end`, `displayorder_s`),
  KEY `grade_s` (`grade_s`, `grade`, `displayorder`),
  KEY `grade` (`grade`, `displayorder`),
  KEY `updateverify` (`updateverify`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_consumemessage`
--

DROP TABLE IF EXISTS `brand_consumemessage`;
CREATE TABLE `brand_consumemessage` (
  `nid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `exception` text NOT NULL,
  `address` text NOT NULL,
  `hotline` varchar(20) NOT NULL,
  `postip` varchar(15) NOT NULL DEFAULT '',
  `relativeitemids` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`nid`),
  KEY `itemid` (`itemid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_crons`
--

DROP TABLE IF EXISTS `brand_crons`;
CREATE TABLE `brand_crons` (
  `cronid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system') NOT NULL DEFAULT 'user',
  `name` char(50) NOT NULL DEFAULT '',
  `filename` char(50) NOT NULL DEFAULT '',
  `lastrun` int(10) unsigned NOT NULL DEFAULT '0',
  `nextrun` int(10) unsigned NOT NULL DEFAULT '0',
  `weekday` tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`cronid`),
  KEY `nextrun` (`available`,`nextrun`)
) ENGINE=MyISAM ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

--
-- Dumping data for table `brand_crons`
--

INSERT INTO `brand_crons` (`cronid`, `available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES
(1, 1, 'system', '更新店舖狀態', 'updateshopgrade.php', 0, 0, -1, -1, 0, '0'),
(2, 0, 'system', '更新商品狀態', 'updategoodgrade.php', 0, 0, -1, -1, 0, '15'),
(3, 0, 'system', '更新消費卷狀態', 'updateconsumegrade.php', 0, 0, -1, -1, 1, '15'),
(4, 0, 'system', '更新公告狀態', 'updatenoticegrade.php', 0, 0, -1, -1, 2, '15'),
(5, 0, 'system', '更新團購狀態', 'updategroupbuygrade.php', 0, 0, -1, -1, 3, '15');

-- --------------------------------------------------------

--
-- 表的結構 `brand_data`
--

DROP TABLE IF EXISTS `brand_data`;
CREATE TABLE `brand_data` (
  `variable` varchar(32) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`variable`)
) ENGINE=MyISAM;

--
-- 轉存表中的數據 `brand_data`
--

INSERT INTO `brand_data` (`variable`, `value`) VALUES
('ads_show_type', 'topic'),
('topic', 'a:2:{i:0;a:2:{s:5:"image";s:24:"static/image/index/4.jpg";s:3:"url";s:9:"index.php";}i:1;a:2:{s:5:"image";s:24:"static/image/index/5.jpg";s:3:"url";s:9:"index.php";}}');

-- --------------------------------------------------------

--
-- 表的結構 `brand_gooditems`
--

DROP TABLE IF EXISTS `brand_gooditems`;
CREATE TABLE `brand_gooditems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所屬店舖id',
  `catid` smallint(6) unsigned DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `displayorder_s` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `username` char(15) NOT NULL DEFAULT '',
  `subject` char(80) NOT NULL DEFAULT '',
  `subjectimage` char(160) NOT NULL DEFAULT '',
  `rates` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reportnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) NOT NULL DEFAULT '1',
  `grade_s` tinyint(1) NOT NULL DEFAULT '3',
  `grade` tinyint(1) NOT NULL DEFAULT '3',
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `priceo` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `minprice` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `maxprice` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `validity_start` int(11) NOT NULL COMMENT '有效期開始時間',
  `validity_end` int(11) NOT NULL COMMENT '有效期結束時間',
  `updateverify` tinyint(1) DEFAULT '0',
  `intro` char(200) NOT NULL COMMENT '商品簡介',
  `bbstid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `shopid` (`shopid`, `grade_s`, `grade`),
  KEY `catid` (`catid`, `grade_s`, `grade`, `displayorder`),
  KEY `catid_s` (`catid`, `grade_s`, `grade`, `displayorder_s`),
  KEY `grade_s` (`grade_s`, `grade`, `displayorder`),
  KEY `grade` (`grade`, `displayorder`),
  KEY `updateverify` (`updateverify`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_goodmessage`
--

DROP TABLE IF EXISTS `brand_goodmessage`;
CREATE TABLE `brand_goodmessage` (
  `nid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `postip` varchar(15) NOT NULL DEFAULT '',
  `relativeitemids` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`nid`),
  KEY `itemid` (`itemid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_goodrelated`
--

DROP TABLE IF EXISTS `brand_goodrelated`;
CREATE TABLE `brand_goodrelated` (
  `goodid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` char(10) NOT NULL,
  `relatedid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `goodid` (`goodid`,`type`,`relatedid`)
) ENGINE=MyISAM DEFAULT ROW_FORMAT=FIXED ;

--
-- 轉存表中的數據 `brand_goodrelated`
--


-- --------------------------------------------------------

--
-- 表的結構 `brand_groupbuyitems`
--

DROP TABLE IF EXISTS `brand_groupbuyitems`;
CREATE TABLE `brand_groupbuyitems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所屬店舖id',
  `catid` smallint(6) unsigned DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `displayorder_s` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `username` char(15) NOT NULL DEFAULT '',
  `subject` char(80) NOT NULL DEFAULT '',
  `subjectimage` char(160) NOT NULL DEFAULT '',
  `rates` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reportnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `buyingnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) NOT NULL DEFAULT '1',
  `grade_s` tinyint(1) NOT NULL DEFAULT '3',
  `grade` tinyint(1) NOT NULL DEFAULT '3',
  `close` tinyint(1) NOT NULL DEFAULT '0',
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `groupbuyprice` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `groupbuypriceo` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `groupbuymaxnum` int(10) unsigned NOT NULL DEFAULT '0',
  `validity_start` int(11) NOT NULL COMMENT '有效期開始時間',
  `validity_end` int(11) NOT NULL COMMENT '有效期結束時間',
  `updateverify` tinyint(1) DEFAULT '0',
  `bbstid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `shopid` (`shopid`, `grade_s`, `grade`),
  KEY `catid` (`catid`, `grade_s`, `grade`, `displayorder`),
  KEY `catid_s` (`catid`, `grade_s`, `grade`, `displayorder_s`),
  KEY `grade_s` (`grade_s`, `grade`, `displayorder`),
  KEY `grade` (`grade`, `displayorder`),
  KEY `updateverify` (`updateverify`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- 表的結構 `brand_groupbuymessage`
--

DROP TABLE IF EXISTS `brand_groupbuymessage`;
CREATE TABLE `brand_groupbuymessage` (
  `nid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `postip` varchar(15) NOT NULL DEFAULT '',
  `relativeitemids` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`nid`),
  KEY `itemid` (`itemid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- 表的結構 `brand_itemattribute`
--

DROP TABLE IF EXISTS `brand_itemattribute`;
CREATE TABLE `brand_itemattribute` (
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '內容id',
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分類id',
  `attr_id_0` mediumint(8) unsigned DEFAULT '0',
  `attr_id_1` mediumint(8) unsigned DEFAULT '0',
  `attr_id_2` mediumint(8) unsigned DEFAULT '0',
  `attr_id_3` mediumint(8) unsigned DEFAULT '0',
  `attr_id_4` mediumint(8) unsigned DEFAULT '0',
  `attr_id_5` mediumint(8) unsigned DEFAULT '0',
  `attr_id_6` mediumint(8) unsigned DEFAULT '0',
  `attr_id_7` mediumint(8) unsigned DEFAULT '0',
  `attr_id_8` mediumint(8) unsigned DEFAULT '0',
  `attr_id_9` mediumint(8) unsigned DEFAULT '0',
  `attr_id_10` mediumint(8) unsigned DEFAULT '0',
  `attr_id_11` mediumint(8) unsigned DEFAULT '0',
  `attr_id_12` mediumint(8) unsigned DEFAULT '0',
  `attr_id_13` mediumint(8) unsigned DEFAULT '0',
  `attr_id_14` mediumint(8) unsigned DEFAULT '0',
  `attr_id_15` mediumint(8) unsigned DEFAULT '0',
  `attr_id_16` mediumint(8) unsigned DEFAULT '0',
  `attr_id_17` mediumint(8) unsigned DEFAULT '0',
  `attr_id_18` mediumint(8) unsigned DEFAULT '0',
  `attr_id_19` mediumint(8) unsigned DEFAULT '0',
  `attr_id_20` mediumint(8) unsigned DEFAULT '0',
  `attr_id_21` mediumint(8) unsigned DEFAULT '0',
  `attr_id_22` mediumint(8) unsigned DEFAULT '0',
  `attr_id_23` mediumint(8) unsigned DEFAULT '0',
  `attr_id_24` mediumint(8) unsigned DEFAULT '0',
  `attr_id_25` mediumint(8) unsigned DEFAULT '0',
  `attr_id_26` mediumint(8) unsigned DEFAULT '0',
  `attr_id_27` mediumint(8) unsigned DEFAULT '0',
  `attr_id_28` mediumint(8) unsigned DEFAULT '0',
  `attr_id_29` mediumint(8) unsigned DEFAULT '0',
  `attr_id_30` mediumint(8) unsigned DEFAULT '0',
  `attr_id_31` mediumint(8) unsigned DEFAULT '0',
  `attr_id_32` mediumint(8) unsigned DEFAULT '0',
  `attr_id_33` mediumint(8) unsigned DEFAULT '0',
  `attr_id_34` mediumint(8) unsigned DEFAULT '0',
  `attr_id_35` mediumint(8) unsigned DEFAULT '0',
  `attr_id_36` mediumint(8) unsigned DEFAULT '0',
  `attr_id_37` mediumint(8) unsigned DEFAULT '0',
  `attr_id_38` mediumint(8) unsigned DEFAULT '0',
  `attr_id_39` mediumint(8) unsigned DEFAULT '0',
  `attr_id_40` mediumint(8) unsigned DEFAULT '0',
  `attr_id_41` mediumint(8) unsigned DEFAULT '0',
  `attr_id_42` mediumint(8) unsigned DEFAULT '0',
  `attr_id_43` mediumint(8) unsigned DEFAULT '0',
  `attr_id_44` mediumint(8) unsigned DEFAULT '0',
  `attr_id_45` mediumint(8) unsigned DEFAULT '0',
  `attr_id_46` mediumint(8) unsigned DEFAULT '0',
  `attr_id_47` mediumint(8) unsigned DEFAULT '0',
  `attr_id_48` mediumint(8) unsigned DEFAULT '0',
  `attr_id_49` mediumint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`itemid`),
  UNIQUE KEY `catid_itemid` (`catid`, `itemid`),
  KEY `attr_id_0` (`attr_id_0`),
  KEY `attr_id_1` (`attr_id_1`),
  KEY `attr_id_2` (`attr_id_2`),
  KEY `attr_id_3` (`attr_id_3`),
  KEY `attr_id_4` (`attr_id_4`),
  KEY `attr_id_5` (`attr_id_5`),
  KEY `attr_id_6` (`attr_id_6`),
  KEY `attr_id_7` (`attr_id_7`),
  KEY `attr_id_8` (`attr_id_8`),
  KEY `attr_id_9` (`attr_id_9`),
  KEY `attr_id_10` (`attr_id_10`),
  KEY `attr_id_11` (`attr_id_11`),
  KEY `attr_id_12` (`attr_id_12`),
  KEY `attr_id_13` (`attr_id_13`),
  KEY `attr_id_14` (`attr_id_14`),
  KEY `attr_id_15` (`attr_id_15`),
  KEY `attr_id_16` (`attr_id_16`),
  KEY `attr_id_17` (`attr_id_17`),
  KEY `attr_id_18` (`attr_id_18`),
  KEY `attr_id_19` (`attr_id_19`),
  KEY `attr_id_20` (`attr_id_20`),
  KEY `attr_id_21` (`attr_id_21`),
  KEY `attr_id_22` (`attr_id_22`),
  KEY `attr_id_23` (`attr_id_23`),
  KEY `attr_id_24` (`attr_id_24`),
  KEY `attr_id_25` (`attr_id_25`),
  KEY `attr_id_26` (`attr_id_26`),
  KEY `attr_id_27` (`attr_id_27`),
  KEY `attr_id_28` (`attr_id_28`),
  KEY `attr_id_29` (`attr_id_29`),
  KEY `attr_id_30` (`attr_id_30`),
  KEY `attr_id_31` (`attr_id_31`),
  KEY `attr_id_32` (`attr_id_32`),
  KEY `attr_id_33` (`attr_id_33`),
  KEY `attr_id_34` (`attr_id_34`),
  KEY `attr_id_35` (`attr_id_35`),
  KEY `attr_id_36` (`attr_id_36`),
  KEY `attr_id_37` (`attr_id_37`),
  KEY `attr_id_38` (`attr_id_38`),
  KEY `attr_id_39` (`attr_id_39`),
  KEY `attr_id_40` (`attr_id_40`),
  KEY `attr_id_41` (`attr_id_41`),
  KEY `attr_id_42` (`attr_id_42`),
  KEY `attr_id_43` (`attr_id_43`),
  KEY `attr_id_44` (`attr_id_44`),
  KEY `attr_id_45` (`attr_id_45`),
  KEY `attr_id_46` (`attr_id_46`),
  KEY `attr_id_47` (`attr_id_47`),
  KEY `attr_id_48` (`attr_id_48`),
  KEY `attr_id_49` (`attr_id_49`)
) ENGINE=MyISAM ROW_FORMAT=FIXED COMMENT='內容屬性關係表';


-- --------------------------------------------------------

--
-- 表的結構 `brand_itemupdates`
--

DROP TABLE IF EXISTS `brand_itemupdates`;
CREATE TABLE `brand_itemupdates` (
  `itemid` int(11) unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `updatestatus` tinyint(1) DEFAULT '1',
  `update` text,
  UNIQUE KEY `itemid` (`itemid`,`type`)
) ENGINE=MyISAM;

--
-- 轉存表中的數據 `brand_itemupdates`
--

-- --------------------------------------------------------

--
-- 表的結構 `brand_groupbuyjoin`
--

DROP TABLE IF EXISTS `brand_groupbuyjoin`;
CREATE TABLE `brand_groupbuyjoin` (
  `itemid` mediumint(8) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` char(15) NOT NULL,
  `realname` char(30) NOT NULL,
  `mobile` char(15) NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  UNIQUE KEY `itemid` (`itemid`,`uid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的結構 `brand_managelog`
--

DROP TABLE IF EXISTS `brand_managelog`;
CREATE TABLE `brand_managelog` (
  `mlogid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(10) NOT NULL,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL,
  `username` char(15) NOT NULL,
  `opcheck` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `reason` char(255) NOT NULL,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mlogid`),
  KEY `dateline` (`dateline`),
  KEY `type` (`type`,`itemid`,`uid`)
) ENGINE=MyISAM ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

--
-- 轉存表中的數據 `brand_managelog`
--


-- --------------------------------------------------------

--
-- 表的結構 `brand_members`
--

DROP TABLE IF EXISTS `brand_members`;
CREATE TABLE `brand_members` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用戶組id暫未使用',
  `username` char(15) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `email` char(100) NOT NULL DEFAULT '',
  `myshopid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '我的店舖id',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '',
  `lastsearchtime` int(10) unsigned NOT NULL DEFAULT '0',
  `lastcommenttime` int(10) unsigned NOT NULL DEFAULT '0',
  `taskstatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否在作新手任務',
  `allowadmincp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否擁有管理員權限',
  PRIMARY KEY (`uid`),
  KEY `myshopid` (`myshopid`)
) ENGINE=MyISAM DEFAULT ROW_FORMAT=FIXED ;

--
-- 轉存表中的數據 `brand_members`
--

-- --------------------------------------------------------

--
-- 表的結構 `brand_modelcolumns`
--

DROP TABLE IF EXISTS `brand_modelcolumns`;
CREATE TABLE `brand_modelcolumns` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `mid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `fieldname` varchar(30) NOT NULL DEFAULT '',
  `fieldtitle` char(40) NOT NULL,
  `fieldcomment` varchar(60) NOT NULL DEFAULT '',
  `fieldtype` varchar(20) NOT NULL DEFAULT '',
  `fieldminlength` int(5) unsigned NOT NULL DEFAULT '0',
  `fieldlength` int(5) unsigned NOT NULL DEFAULT '0',
  `fielddefault` mediumtext NOT NULL,
  `formtype` varchar(20) NOT NULL DEFAULT '',
  `fielddata` mediumtext NOT NULL,
  `displayorder` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `allowshow` tinyint(1) NOT NULL DEFAULT '0',
  `allowpost` tinyint(1) NOT NULL DEFAULT '0',
  `isfixed` tinyint(1) NOT NULL DEFAULT '0',
  `isrequired` tinyint(1) NOT NULL DEFAULT '0',
  `isimage` tinyint(1) NOT NULL DEFAULT '0',
  `thumbsize` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fieldname` (`mid`,`fieldname`),
  KEY `mid` (`mid`,`displayorder`)
) ENGINE=MyISAM  AUTO_INCREMENT=43 ;

--
-- 轉存表中的數據 `brand_modelcolumns`
--

INSERT INTO `brand_modelcolumns` (`id`, `mid`, `fieldname`, `fieldtitle`, `fieldcomment`, `fieldtype`, `fieldminlength`, `fieldlength`, `fielddefault`, `formtype`, `fielddata`, `displayorder`, `allowshow`, `allowpost`, `isfixed`, `isrequired`, `isimage`, `thumbsize`) VALUES
(1, 2, 'address', '店舖地址', '店舖地址', 'CHAR', 0, 80, '', 'text', '', 1, 1, 1, 1, 1, 0, ''),
(2, 2, 'tel', '店舖電話', '店舖電話', 'CHAR', 0, 30, '', 'text', '', 2, 1, 1, 1, 1, 0, ''),
(3, 2, 'isdiscount', '是否支持會員卡', '是否支持會員卡', 'TINYINT', 0, 1, '0', 'radio', '0\n1', 8, 1, 0, 1, 0, 0, ''),
(4, 2, 'discount', '會員卡折扣信息', '會員卡折扣信息', 'CHAR', 0, 100, '', 'text', '', 9, 1, 1, 1, 0, 0, ''),
(5, 2, 'banner', '品牌Banner圖，固定大小980 x 150', '品牌Banner圖，固定大小980 x 150', 'VARCHAR', 0, 150, '', 'img', '', 3, 1, 1, 0, 0, 0, ''),
(6, 2, 'windowsimg', '櫥窗海報圖片', '櫥窗海報圖片', 'VARCHAR', 0, 150, '', 'img', '', 4, 1, 1, 0, 0, 1, ''),
(7, 2, 'windowstext', '櫥窗展示文字', '櫥窗展示文字', 'VARCHAR', 0, 200, '', 'text', '', 5, 1, 1, 0, 0, 0, ''),
(8, 2, 'mapapimark', '地圖API商家地點參數', '地圖API商家地點參數', 'VARCHAR', 0, 60, '', 'text', '', 16, 1, 1, 0, 0, 0, ''),
(9, 2, 'tips', '櫥窗上方公告文字', '櫥窗上方公告文字', 'VARCHAR', 0, 255, '', 'textarea', '', 6, 1, 1, 0, 0, 0, ''),
(10, 2, 'applicant', '申請人姓名', '申請人姓名', 'VARCHAR', 2, 12, '', 'text', '', 10, 0, 1, 0, 1, 0, ''),
(11, 2, 'applicantmobi', '申請人手機', '申請人手機', 'VARCHAR', 7, 11, '', 'text', '', 12, 0, 1, 0, 1, 0, ''),
(12, 2, 'applicanttel', '申請人座機', '申請人座機', 'VARCHAR', 7, 18, '', 'text', '', 13, 0, 1, 0, 1, 0, ''),
(13, 2, 'applicantid', '申請人身份證', '申請人身份證', 'VARCHAR', 15, 18, '', 'text', '', 11, 0, 1, 0, 1, 0, ''),
(14, 2, 'applicantadd', '申請人住址', '申請人住址', 'VARCHAR', 4, 80, '', 'text', '', 14, 0, 1, 0, 1, 0, ''),
(15, 2, 'applicantpost', '申請人郵編', '申請人郵編', 'VARCHAR', 6, 6, '', 'text', '', 15, 0, 1, 0, 1, 0, ''),
(16, 2, 'forum', '互動專區', '互動專區', 'VARCHAR', 0, 150, '', 'text', '', 7, 1, 1, 0, 0, 0, ''),
(17, 2, 'styletitle', '店舖標題樣式', '店舖標題樣式', 'CHAR', 0, 10, '', 'text', '', 1, 0, 1, 1, 0, 0, ''),
(18, 2, 'region', '地區分類id', '地區分類id', 'SMALLINT', 0, 6, '', 'text', '', 0, 1, 1, 1, 0, 0, ''),
(19, 2, 'groupid', '用戶組id', '用戶組id', 'SMALLINT', 0, 6, '', 'text', '', 0, 0, 0, 1, 0, 0, ''),
(20, 2, 's_enablegood', '發佈商品權限', '發佈商品權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
(21, 2, 's_enablenotice', '發佈公告權限', '發佈公告權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
(22, 2, 's_enableconsume', '發佈消費券權限', '發佈消費券權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
(23, 2, 's_enablealbum', '發佈相冊權限', '發佈相冊權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
(24, 2, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(25, 2, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(26, 3, 'styletitle', '標題樣式', '標題樣式', 'CHAR', 0, 10, '', 'text', '', 0, 0, 1, 1, 0, 0, ''),
(27, 3, 'jumpurl', '公告鏈接地址', '公告鏈接地址', 'VARCHAR', 0, 150, '', 'text', '', 0, 1, 1, 0, 0, 0, ''),
(28, 3, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(29, 3, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(30, 4, 'priceo', '商品原價', '商品原價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
(31, 4, 'minprice', '網店特價', '網店特價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
(32, 4, 'maxprice', '網店最高價', '網店最高價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 0, 0, ''),
(33, 4, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(34, 4, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(35, 5, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(36, 5, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(37, 2, 's_enablegroupbuy', '發佈團購權限', '發佈團購權限', 'TINYINT', 0, 2, '', 'radio_a', '', 0, 0, 0, 1, 0, 0, ''),
(38, 6, 'groupbuyprice', '原價', '原價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
(39, 6, 'groupbuypriceo', '團購價', '團購價', 'DECIMAL', 0, 11, '0', 'text', '', 0, 1, 1, 1, 1, 0, ''),
(40, 6, 'groupbuymaxnum', '最大購買數', '最大購買數', 'INT', 0, 10, '0', 'text', '', 0, 1, 1, 1, 0, 0, ''),
(41, 6, 'validity_start', '有效期開始時間', '有效期開始時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, ''),
(42, 6, 'validity_end', '有效期結束時間', '有效期結束時間', 'INT', 0, 10, '0', 'timestamp', '', 0, 1, 1, 1, 1, 0, '');


-- --------------------------------------------------------

--
-- 表的結構 `brand_models`
--

DROP TABLE IF EXISTS `brand_models`;
CREATE TABLE `brand_models` (
  `mid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `modelname` char(20) NOT NULL DEFAULT '',
  `modelalias` char(60) NOT NULL DEFAULT '',
  `allowpost` tinyint(1) NOT NULL DEFAULT '0',
  `allowguest` tinyint(1) NOT NULL DEFAULT '0',
  `allowgrade` tinyint(1) NOT NULL DEFAULT '0',
  `allowcomment` tinyint(1) NOT NULL DEFAULT '0',
  `allowrate` tinyint(1) NOT NULL DEFAULT '0',
  `allowguestsearch` tinyint(1) NOT NULL DEFAULT '0',
  `allowfeed` tinyint(1) NOT NULL DEFAULT '1',
  `searchinterval` smallint(6) unsigned NOT NULL DEFAULT '0',
  `allowguestdownload` tinyint(1) NOT NULL DEFAULT '0',
  `downloadinterval` smallint(6) unsigned NOT NULL DEFAULT '0',
  `allowfilter` tinyint(1) NOT NULL DEFAULT '0',
  `listperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seokeywords` char(200) NOT NULL DEFAULT '',
  `seodescription` char(200) NOT NULL DEFAULT '',
  `thumbsize` char(19) NOT NULL DEFAULT '',
  `tpl` char(20) NOT NULL DEFAULT '',
  `fielddefault` char(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mid`),
  UNIQUE KEY `modelname` (`modelname`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=6 ;

--
-- 轉存表中的數據 `brand_models`
--

INSERT INTO `brand_models` (`mid`, `modelname`, `modelalias`, `allowpost`, `allowguest`, `allowgrade`, `allowcomment`, `allowrate`, `allowguestsearch`, `allowfeed`, `searchinterval`, `allowguestdownload`, `downloadinterval`, `allowfilter`, `listperpage`, `seokeywords`, `seodescription`, `thumbsize`, `tpl`, `fielddefault`) VALUES
(2, 'shop', '店舖展示', 0, 0, 0, 0, 0, 0, 3, 0, 0, 30, 1, 10, '', '', '100,80', 'default', 'subject = 店舖名稱\r\nsubjectimage = 品牌LOGO （固定100 x 80）\r\ncatid = 店舖分類\r\nmessage = 店舖介紹'),
(3, 'notice', '店舖公告', 0, 0, 0, 0, 0, 0, 1, 0, 0, 30, 1, 20, '', '', '300,225', 'default', 'subject = 公告標題\r\nsubjectimage = 圖標\r\ncatid = 公告分類\r\nmessage = 告示內容'),
(4, 'good', '商品', 0, 0, 0, 0, 0, 0, 1, 0, 0, 30, 1, 10, '', '', '300,220', 'default', 'subject = 商品名稱\r\nsubjectimage = 商品圖片\r\ncatid = 商品分類\r\nmessage = 商品詳情'),
(5, 'consume', '消費券', 0, 0, 0, 0, 0, 0, 1, 0, 0, 30, 1, 20, '', '', '192,120', 'default', 'subject = 消費券名稱\r\nsubjectimage = 消費券圖片（限制最大560x350）\r\ncatid = 消費券分類\r\nmessage = 文字描述，替代圖片'),
(6, 'groupbuy', '團購', 0, 0, 0, 0, 0, 0, 1, 0, 0, 30, 1, 10, '', '', '400,300', 'default', 'subject = 團購名稱\r\nsubjectimage =  團購圖片\r\ncatid =  團購品牌\r\nmessage =  團購詳情');

-- --------------------------------------------------------

--
-- 表的結構 `brand_nav`
--

DROP TABLE IF EXISTS `brand_nav`;
CREATE TABLE `brand_nav` (
  `navid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '導航id',
  `type` char(6) NOT NULL DEFAULT 'site' COMMENT '導航類型',
  `shopid` mediumint(8) unsigned DEFAULT NULL COMMENT '所屬店舖id',
  `available` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否可用',
  `displayorder` smallint(3) NOT NULL DEFAULT '0' COMMENT '排序',
  `flag` char(10) NOT NULL,
  `name` char(5) NOT NULL,
  `url` char(150) NOT NULL,
  `target` tinyint(1) NOT NULL DEFAULT '0',
  `highlight` char(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`navid`),
  KEY `navindex` (`type`,`shopid`,`available`,`displayorder`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=8 ;

INSERT INTO `brand_nav` (`navid`, `type`, `shopid`, `available`, `displayorder`, `flag`, `name`, `url`, `target`, `highlight`) VALUES
(1, 'sys', 0, 1, 1, 'index', '首頁', 'index.php', 0, ''),
(2, 'sys', 0, 1, 2, 'goods', '商品', 'goodsearch.php', 0, ''),
(3, 'sys', 0, 1, 3, 'street', '商圈', 'street.php', 0, ''),
(4, 'sys', 0, 1, 4, 'album', '相冊', 'album.php', 0, ''),
(5, 'sys', 0, 1, 5, 'consume', '消費券', 'consume.php', 0, ''),
(6, 'sys', 0, 1, 6, 'card', '會員卡', 'card.php', 0, ''),
(7, 'sys', 0, 1, 7, 'groupbuy', '團購', 'groupbuy.php', 0, '');

-- --------------------------------------------------------

--
-- 表的結構 `brand_noticeitems`
--

DROP TABLE IF EXISTS `brand_noticeitems`;
CREATE TABLE `brand_noticeitems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所屬店舖id',
  `catid` smallint(6) unsigned DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `displayorder_s` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `username` char(15) NOT NULL DEFAULT '',
  `subject` char(80) NOT NULL DEFAULT '',
  `subjectimage` char(160) NOT NULL DEFAULT '',
  `rates` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reportnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) NOT NULL DEFAULT '1',
  `grade_s` tinyint(1) NOT NULL DEFAULT '3',
  `grade` tinyint(1) NOT NULL DEFAULT '3',
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `styletitle` char(10) NOT NULL,
  `validity_start` int(11) NOT NULL COMMENT '有效期開始時間',
  `validity_end` int(11) NOT NULL COMMENT '有效期結束時間',
  `updateverify` tinyint(1) DEFAULT '0',
  `bbstid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `shopid` (`shopid`, `grade_s`, `grade`),
  KEY `catid` (`catid`, `grade_s`, `grade`, `displayorder`),
  KEY `catid_s` (`catid`, `grade_s`, `grade`, `displayorder_s`),
  KEY `grade_s` (`grade_s`, `grade`, `displayorder`),
  KEY `grade` (`grade`, `displayorder`),
  KEY `updateverify` (`updateverify`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;


--
-- 表的結構 `brand_noticemessage`
--

DROP TABLE IF EXISTS `brand_noticemessage`;
CREATE TABLE `brand_noticemessage` (
  `nid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `message` mediumtext NOT NULL,
  `postip` varchar(15) NOT NULL DEFAULT '',
  `relativeitemids` varchar(255) NOT NULL DEFAULT '',
  `jumpurl` varchar(255) NOT NULL,
  PRIMARY KEY (`nid`),
  KEY `itemid` (`itemid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

--
-- 轉存表中的數據 `brand_noticemessage`
--

-- --------------------------------------------------------

--
-- 表的結構 `brand_photoitems`
--

DROP TABLE IF EXISTS `brand_photoitems`;
CREATE TABLE `brand_photoitems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所屬店舖id',
  `albumid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '相冊id，0為默認相冊',
  `bbsaid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '從論壇獲取的附件圖片aid（可重複）',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `displayorder_s` mediumint(6) unsigned NOT NULL DEFAULT '100',
  `username` char(15) NOT NULL DEFAULT '',
  `subject` char(80) NOT NULL DEFAULT '',
  `subjectimage` char(160) NOT NULL DEFAULT '',
  `rates` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) NOT NULL DEFAULT '1',
  `grade_s` tinyint(1) NOT NULL DEFAULT '3',
  `grade` tinyint(1) NOT NULL DEFAULT '3',
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `shopid` (`shopid`, `grade_s`, `grade`),
  KEY `albumid` (`albumid`),
  KEY `bbsaid` (`bbsaid`),
  KEY `grade` (`grade_s`, `grade`, `displayorder`),
  KEY `grade_s` (`grade_s`, `grade`, `displayorder_s`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- 表的結構 `brand_relatedinfo`
--

DROP TABLE IF EXISTS `brand_relatedinfo`;
CREATE TABLE `brand_relatedinfo` (
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` char(10) NOT NULL,
  `relatedid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `relatedtype` char(10) NOT NULL,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  KEY `itemid` (`itemid`,`type`, `shopid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的結構 `brand_reportlog`
--

DROP TABLE IF EXISTS `brand_reportlog`;
CREATE TABLE `brand_reportlog` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(10) NOT NULL,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL,
  `username` char(15) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `reasonid` smallint(6) unsigned NOT NULL,
  `reason` char(255) NOT NULL,
  `shopid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rid`),
  KEY `dateline` (`dateline`),
  KEY `type` (`type`,`itemid`,`uid`)
) ENGINE=MyISAM ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_reportreasons`
--

DROP TABLE IF EXISTS `brand_reportreasons`;
CREATE TABLE `brand_reportreasons` (
  `rrid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(10) NOT NULL DEFAULT '',
  `content` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`rrid`)
) ENGINE=MyISAM DEFAULT ROW_FORMAT=FIXED ;

--
-- 轉存表中的數據 `brand_reportreasons`
--

INSERT INTO `brand_reportreasons` (`rrid`, `type`, `content`) VALUES
(1, '', '信息有誤'),
(2, '', '不切實際');

-- --------------------------------------------------------

--
-- 表的結構 `brand_scorestats`
--

DROP TABLE IF EXISTS `brand_scorestats`;
CREATE TABLE `brand_scorestats` (
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` char(10) NOT NULL DEFAULT '',
  `remarknum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score` float(8,2) unsigned NOT NULL DEFAULT '0.00',
  `score1` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score2` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score3` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score4` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score5` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score6` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score7` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `score8` mediumint(8) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `itemid` (`itemid`,`type`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的結構 `brand_settings`
--

DROP TABLE IF EXISTS `brand_settings`;
CREATE TABLE `brand_settings` (
  `variable` varchar(32) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`variable`)
) ENGINE=MyISAM;

--
-- 轉存表中的數據 `brand_settings`
--

INSERT INTO `brand_settings` (`variable`, `value`) VALUES
('albumsearchperpage', '10'),
('allowcache', '1'),
('allowguest', '0'),
('allowguestdownload', '0'),
('allowguestsearch', '0'),
('allowcreateimg', '1'),
('allowregister', '1'),
('analytics', ''),
('attachmentdir', './attachments'),
('attachmentdirtype', 'month'),
('attachmenturl', ''),
('attachmenturls', ''),
('attachmenturlcount', '1'),
('attenddescription', '尊敬的商家，您好！感謝您關注品牌空間！\r\n\r\n品牌空間是我們為商家提供的網絡展示空間，您可以在此發佈熱門商品、促銷活動和公司動態。每個店舖與實際商家一一對應，您發佈的任何信息都能第一時間傳遞給目標消費者。快速的信息傳遞速度，在促進店舖銷售的同時又能幫助您提升品牌價值！\r\n通過品牌空間，商家與消費者有了更多的交流機會，大大提高了商家在本地的知名度、美譽度、親和力，迅速提升商家在消費市場的口碑與活躍度。入駐品牌空間將為您的店舖提供更多機遇、更多消費者！再次感謝您選擇入駐品牌空間，我們將竭誠為您服務。'),
('auditnewshops', '1'),
('backupdir', 'ece1b2'),
('cachemode', 'database'),
('cardperpage', '9'),
('closemessage', ''),
('closesite', '0'),
('commentmodel', '1'),
('commorderby', '1'),
('commentperpage', '5'),
('commenttime', '30'),
('commstatus', '1'),
('consumeperpage', '9'),
('consumesearchperpage', '9'),
('custombackup', ''),
('dateformat', 'Y-n-j'),
('defaultshopgroup', ''),
('discounturl', ''),
('enablecard', '1'),
('enablemap', '0'),
('formhash', ''),
('fontpath', 'en/PilsenPlakat.ttf'),
('goodperpage', '10'),
('goodsearchperpage', '10'),
('groupbuyperpage', '10'),
('groupbuysearchperpage', '10'),
('mapapikey', ''),
('miibeian', ''),
('multipleshop', '0'),
('newsjammer', '0'),
('noticeperpage', '15'),
('registerrule', '《品牌空間加盟申請條款》\r\n\r\n1、申請商家承諾對提交的任何店舖信息真實、有效，無任何不良或虛假信息；\r\n2、申請商家使用實名申請，同時對店舖經營的品牌、產品、活動及服務說明的任何承諾都獨立承擔相應的法律責任；\r\n3、申請商家遵守所從事本行業的相關法律、法規及對消費者的相關服務條款，如行業三包服務等；\r\n4、一旦有違規、違法現象發生或相關內容的發佈，我方將有權對申請商家的店舖按管理要求進行處理。若情節嚴重，將轉交相關責任部門登記備案。\r\n5、各商家通過我方平台在接待網友消費時，若有違反當地法律、法規或存在消費糾紛等不良服務，我方有權對事件進行曝光。同時將積極協助消費者，配合當地政府相關監管機構進行合理維權。\r\n6、已入駐品牌空間的商家，同時必須遵守本網站的管理規範和各分論壇版規要求。若有違規，品牌空間將給以站內警告、封號等處理意見；情況嚴重者，將關閉品牌空間平台使用權限。\r\n7、我方擁有各申請商家的加盟申請、入駐建店、內容監督、違規處理等最終審核、複查、糾正等相關權利。\r\n\r\n以上信息，若有變動，我方將及時在線公佈，恕不另行通知。'),
('regurl', ''),
('seccode', '0'),
('seodescription', ''),
('seohead', ''),
('seokeywords', ''),
('seotitle', ''),
('shopsearchperpage', '10'),
('showindex', '0'),
('sitekey', ''),
('sitename', '品牌空間'),
('siteqq', ''),
('sitetel', ''),
('sitetheme', 'default'),
('template', 'default'),
('thumbarray', 'a:1:{s:4:"news";a:2:{i:0;s:3:"300";i:1;s:3:"250";}}'),
('thumbbgcolor', '#C0C0C0'),
('thumbcutmode', '2'),
('thumbcutstartx', '0'),
('thumbcutstarty', '0'),
('thumboption', '4'),
('timeoffset', '8'),
('timeformat', 'H:i'),
('updateview', '1'),
('urltype', '4'),
('watermark', '0'),
('watermarkfile', 'images/base/watermark.gif'),
('watermarkjpgquality', '85'),
('watermarkstatus', '9'),
('watermarktrans', '30'),
('wwwname', '品牌空間'),
('wwwurl', '')
;

-- --------------------------------------------------------

--
-- 表的結構 `brand_shopgroup`
--

DROP TABLE IF EXISTS `brand_shopgroup`;
CREATE TABLE `brand_shopgroup` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '組id',
  `type` enum('system','member','shop','special') NOT NULL DEFAULT 'shop' COMMENT '組類型',
  `title` char(30) NOT NULL COMMENT '組標題',
  `album_field` text NOT NULL COMMENT '可選相冊類型',
  `good_field` text NOT NULL COMMENT '可選商品類型',
  `notice_field` text NOT NULL COMMENT '可選公告類型',
  `consume_field` text NOT NULL COMMENT '可選消費券類型',
  `groupbuy_field` varchar(255) NOT NULL DEFAULT 'all',
  `enablegood` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `enablenotice` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `enableconsume` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `enablealbum` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `enablebrandlinks` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `enablegroupbuy` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `verifygood` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `verifynotice` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `verifyconsume` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `verifyalbum` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `verifyshop` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `verifygroupbuy` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `consumemaker` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `maxnumgood` mediumint(8) UNSIGNED NULL DEFAULT '0',
  `maxnumnotice` mediumint(8) UNSIGNED NULL DEFAULT '0',
  `maxnumconsume` mediumint(8) UNSIGNED NULL DEFAULT '0',
  `maxnumalbum` mediumint(8) UNSIGNED NULL DEFAULT '0',
  `maxnumbrandlinks` mediumint(8) UNSIGNED NULL DEFAULT '0',
  `maxnumgroupbuy` mediumint(8) UNSIGNED NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=2 ;

--
-- 轉存表中的數據 `brand_shopgroup`
--

INSERT INTO `brand_shopgroup` (`id`, `type`, `title`, `album_field`, `good_field`, `notice_field`, `consume_field`, `groupbuy_field`, `enablegood`, `enablenotice`, `enableconsume`, `enablealbum`, `enablebrandlinks`, `enablegroupbuy`, `verifygood`, `verifynotice`, `verifyconsume`, `verifyalbum`, `verifyshop`, `verifygroupbuy`, `consumemaker`, `maxnumgood`, `maxnumnotice`, `maxnumconsume`, `maxnumalbum`, `maxnumbrandlinks`, `maxnumgroupbuy`) VALUES
(1, 'shop', '鑽石商家', 'all', 'all', 'all', 'all', 'all', 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0),
(2, 'shop', '水晶商家', 'all', 'all', 'all', 'all', 'all', 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 100, 50, 30, 50, 30, 20),
(3, 'shop', 'VIP商家', 'all', 'all', 'all', 'all', 'all', 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 0, 80, 30, 20, 30, 15, 10);

-- --------------------------------------------------------

--
-- 表的結構 `brand_shopitems`
--

DROP TABLE IF EXISTS `brand_shopitems`;
CREATE TABLE `brand_shopitems` (
  `itemid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '店舖id',
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '店舖分類id',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '發佈者uid',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `region` smallint(6) unsigned NOT NULL DEFAULT '0',
  `displayorder` mediumint(6) unsigned NOT NULL DEFAULT '100' COMMENT '顯示順序',
  `username` char(15) NOT NULL COMMENT '發佈者用戶名',
  `subject` char(80) NOT NULL COMMENT '店舖名稱',
  `keywords` varchar(200) NOT NULL COMMENT '店舖SEO關鍵字',
  `description` varchar(200) NOT NULL COMMENT '店舖SEO描述',
  `subjectimage` char(160) NOT NULL COMMENT 'logo圖片',
  `rates` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `viewnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `replynum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `reportnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `allowreply` tinyint(1) NOT NULL DEFAULT '1',
  `grade` tinyint(1) NOT NULL DEFAULT '0',
  `recommend` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `address` char(80) NOT NULL,
  `tel` char(30) NOT NULL,
  `letter` char(1) NOT NULL,
  `isdiscount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持會員卡',
  `discount` char(100) NOT NULL,
  `styletitle` char(10) NOT NULL,
  `themeid` smallint(3) NOT NULL DEFAULT '0' COMMENT '店舖模板',
  `s_enablegood` tinyint(1) NOT NULL DEFAULT '0',
  `s_enablenotice` tinyint(1) NOT NULL DEFAULT '0',
  `s_enableconsume` tinyint(1) NOT NULL DEFAULT '0',
  `s_enablealbum` tinyint(1) NOT NULL DEFAULT '0',
  `s_enablebrandlinks` tinyint(1) NOT NULL DEFAULT '0',
  `s_enablegroupbuy` tinyint(1) NOT NULL DEFAULT '0',
  `validity_start` int(11) NOT NULL COMMENT '有效期開始時間',
  `validity_end` int(11) NOT NULL COMMENT '有效期結束時間',
  `updateverify` tinyint(1) DEFAULT '0',
  `itemnum_notice` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `itemnum_good` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `itemnum_consume` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `itemnum_album` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `itemnum_brandlinks` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `itemnum_groupbuy` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `syncfid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `catid` (`catid`, `grade`, `displayorder`),
  KEY `isdiscount` (`isdiscount`, `grade`, `displayorder`),
  KEY `recommend` (`recommend`, `grade`),
  KEY `letter` (`letter`),
  KEY `grade` (`grade`,`displayorder`),
  KEY `updateverify` (`updateverify`)
) ENGINE=MyISAM  ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_shopmessage`
--

DROP TABLE IF EXISTS `brand_shopmessage`;
CREATE TABLE `brand_shopmessage` (
  `nid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '店舖詳情id',
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '店舖信息主id',
  `message` mediumtext NOT NULL COMMENT '店舖介紹',
  `postip` varchar(15) NOT NULL DEFAULT '',
  `relativeitemids` varchar(255) NOT NULL DEFAULT '',
  `banner` varchar(150) NOT NULL COMMENT '頂部banner圖',
  `windowsimg` varchar(150) NOT NULL COMMENT '櫥窗圖片',
  `windowstext` varchar(150) NOT NULL COMMENT '櫥窗文字',
  `mapapimark` varchar(60) NOT NULL COMMENT '地圖標注點',
  `tips` varchar(255) NOT NULL COMMENT '櫥窗上方滾動公告',
  `applicant` varchar(12) NOT NULL COMMENT '申請人',
  `applicantmobi` varchar(11) NOT NULL,
  `applicanttel` varchar(18) NOT NULL,
  `applicantid` varchar(18) NOT NULL,
  `applicantadd` varchar(80) NOT NULL,
  `applicantpost` varchar(6) NOT NULL,
  `forum` varchar(150) NOT NULL,
  PRIMARY KEY (`nid`),
  KEY `itemid` (`itemid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_shopupdate`
--

DROP TABLE IF EXISTS `brand_shopupdate`;
CREATE TABLE `brand_shopupdate` (
  `shopid` int(11) unsigned NOT NULL,
  `updatestatus` tinyint(1) DEFAULT '1',
  `update` text,
  PRIMARY KEY (`shopid`)
) ENGINE=MyISAM;

--
-- 轉存表中的數據 `brand_shopupdate`
--


-- --------------------------------------------------------

--
-- 表的結構 `brand_spacecomments`
--

DROP TABLE IF EXISTS `brand_spacecomments`;
CREATE TABLE `brand_spacecomments` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT '',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `author` varchar(15) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(150) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `hot` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `click_33` smallint(6) unsigned NOT NULL DEFAULT '0',
  `click_34` smallint(6) unsigned NOT NULL DEFAULT '0',
  `floornum` smallint(6) unsigned NOT NULL DEFAULT '0',
  `hideauthor` tinyint(1) NOT NULL DEFAULT '0',
  `hideip` tinyint(1) NOT NULL DEFAULT '0',
  `hidelocation` tinyint(1) NOT NULL DEFAULT '0',
  `firstcid` int(10) unsigned NOT NULL DEFAULT '0',
  `upcid` int(10) unsigned NOT NULL DEFAULT '0',
  `shopuid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `isprivate` tinyint(1) NOT NULL DEFAULT '0',
  `subtype` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `itemid` (`itemid`,`dateline`),
  KEY `uid` (`uid`,`dateline`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的結構 `brand_admincp_group`
--
CREATE TABLE `brand_admincp_group` (
  `cpgroupid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `cpgroupname` varchar(255) NOT NULL,
  `cpgroupshopcats` text,
  PRIMARY KEY (`cpgroupid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的結構 `brand_admincp_member`
--
CREATE TABLE `brand_admincp_member` (
  `uid` int(11) unsigned NOT NULL,
  `cpgroupid` int(10) unsigned DEFAULT NULL,
  `customperm` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的結構 `brand_admincp_perm`
--
CREATE TABLE `brand_admincp_perm` (
  `cpgroupid` smallint(6) unsigned NOT NULL,
  `perm` varchar(255) NOT NULL,
  PRIMARY KEY (`cpgroupid`,`perm`)
) ENGINE=MyISAM;