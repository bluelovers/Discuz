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
$fromversion = '9.4.0 Beta';
$toversion = '9.4.2';
$sql = <<<EOF
RENAME TABLE `kfsm_trust` TO `kfsm_deal`;
ALTER TABLE `kfsm_deal` CHANGE `tid` `did` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;
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
) ENGINE=MyISAM DEFAULT CHARSET=gbk;
ALTER TABLE `kfsm_apply` ADD `issuetime` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `applytime`;
ALTER TABLE `kfsm_user` ADD `capital_ava` DECIMAL( 14, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' AFTER `capital`;
UPDATE kfsm_user SET capital_ava=capital;
ALTER TABLE `kfsm_customer` ADD `ip` VARCHAR( 20 ) NOT NULL DEFAULT '';
EOF;
runquery($sql);
$finish = TRUE;
?>
