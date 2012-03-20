<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: upgrade.php 28668 2012-03-07 07:46:05Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = '';

if($_GET['fromversion'] <= '1.04') {

	$connect = $_G['setting']['connect'];
	$connect['t']['reply'] = 1;
	$connect['t']['reply_showauthor'] = 1;
	$connect = serialize($connect);

	$sql .= <<<EOF

	CREATE TABLE IF NOT EXISTS pre_connect_tthreadlog (
	  twid char(16) NOT NULL,
	  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
	  conopenid char(32) NOT NULL,
	  pagetime int(10) unsigned DEFAULT '0',
	  lasttwid char(16) DEFAULT NULL,
	  nexttime int(10) unsigned DEFAULT '0',
	  updatetime int(10) unsigned DEFAULT '0',
	  dateline int(10) unsigned DEFAULT '0',
	  PRIMARY KEY (twid),
	  KEY nexttime (tid,nexttime),
	  KEY updatetime (tid,updatetime)
	) TYPE=MyISAM;

	REPLACE INTO pre_common_setting VALUES ('connect', '{$connect}');

	CREATE TABLE IF NOT EXISTS pre_common_connect_guest (
	  `conopenid` char(32) NOT NULL default '',
	  `conuin` char(40) NOT NULL default '',
	  `conuinsecret` char(16) NOT NULL default '',
	  PRIMARY KEY (conopenid)
	) TYPE=MyISAM;

EOF;

	$columnexisted = false;

	$query = DB::query("SHOW COLUMNS FROM pre_common_member_connect");
	while($temp = DB::fetch($query)) {
		if($temp['Field'] == 'conisqqshow') {
			$columnexisted = true;
			break;
		}
	}

	$sql .= !$columnexisted ? "ALTER TABLE pre_common_member_connect ADD COLUMN conisqqshow tinyint(1) unsigned NOT NULL default '0';" : '';

}

if($sql) {
	runquery($sql);
}

$finish = true;

?>