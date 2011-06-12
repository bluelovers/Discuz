<?php
/* * * * * * * * * * * * * * * * * * * * 
 *	bank_ane
 *	version 1.11 X1.5
 *	date 2010-9-9
 *	author ane
 * * * * * * * * * * * * * * * * * * * */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$tablepre = DB::table('');
$inslang = $installlang['bank'];//语言包
$additionexist = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_addon')." WHERE `key`='44yP53Cy4O'");//是否已经存在
if($additionexist){
	cpmsg($inslang['installalready'],'','error');
}
DB::query("INSERT INTO ".DB::table('common_addon')." VALUES ('44yP53Cy4O', '$inslang[czw1]', '$inslang[czw2]', 'http://www.jhdxr.com', '$inslang[czw3]', '303209174', 'http://www.jhdxr.com/czw_server/logo.gif', '1')");

$sql = <<<EOF
DROP TABLE IF EXISTS {$tablepre}plugin_banklist;
CREATE TABLE pre_plugin_banklist (
  id int(10) unsigned NOT NULL auto_increment,
  bankname varchar(20) NOT NULL default '',
  banklogo varchar(255) NOT NULL default '',
  creator varchar(15) NOT NULL default '',
  opentime int(10) unsigned NOT NULL default '0',
  bankstatus tinyint(1) NOT NULL default '0',
  bankadmin varchar(200) NOT NULL default '',
  investment int(10) unsigned NOT NULL default '0',
  bankroll int(10) NOT NULL default '0',
  deposit int(10) NOT NULL default '0',
  usernum int(10) unsigned NOT NULL default '0',
  notice text NOT NULL,
  opencost int(10) NOT NULL default '0',
  currentrate text NOT NULL,
  fixedrate varchar(10) NOT NULL default '',
  lendingrate varchar(10) NOT NULL default '',
  changetax varchar(10) NOT NULL default '',
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS {$tablepre}plugin_bankoperation;
CREATE TABLE pre_plugin_bankoperation (
  id int(10) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  bankid int(10) unsigned NOT NULL default '0',
  optype tinyint(1) NOT NULL default '0',
  opstatus tinyint(1) NOT NULL default '0',
  opnum int(10) unsigned NOT NULL default '0',
  extchar char(32) NOT NULL default '',
  begintime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY ubank (bankid,uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS {$tablepre}plugin_banklog;
CREATE TABLE pre_plugin_banklog (
  id int(10) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  bankid int(10) unsigned NOT NULL default '0',
  issystem tinyint(1) NOT NULL default '0',
  opnum int(10) NOT NULL default '0',
  remark text,
  otheruser varchar(15) NOT NULL default '',
  optime int(10) unsigned NOT NULL default '0',
  opip varchar(15) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY ubank (bankid,uid)
) TYPE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;
?>
