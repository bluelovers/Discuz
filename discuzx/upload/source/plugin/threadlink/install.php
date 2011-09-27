<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-07 10:38
 *	Filename: install.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access denied');
}

$sql = <<<SQL
CREATE TABLE pre_threadlink_base (
  `tid` mediumint(8) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `tltpl` char(100) NOT NULL,
  `maxrow` smallint(4) unsigned NOT NULL,
  `subject` varchar(80) NOT NULL,
  `dateline` int(10) NOT NULL,
  `summarylength` smallint(6) NOT NULL,
  PRIMARY KEY  (`tid`)
) ENGINE=MyISAM;

CREATE TABLE pre_threadlink_link (
  `lid` mediumint(8) unsigned NOT NULL auto_increment,
  `tid` mediumint(8) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `subject` varchar(80) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `pic` varchar(255) NOT NULL,
  `aid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`lid`),
  UNIQUE KEY `tid` (`tid`,`pid`)
) ENGINE=MyISAM;

ALTER TABLE  pre_threadlink_base 
ADD  `picwidth` SMALLINT( 3 ) UNSIGNED NOT NULL DEFAULT  '100',
ADD  `picheight` SMALLINT( 3 ) UNSIGNED NOT NULL DEFAULT  '100';
SQL;

runquery($sql);
$finish = 1;
?>
