<?php

/*
	$HeadURL:  $
	$Revision: $
	$Author: $
	$Date: $
	$Id:  $
*/

!defined('IN_UC') && exit('Access Denied');



include UC_ROOT.'./data/config.inc.php';
require_once UC_ROOT.'./lib/db.class.php';

$db = new ucserver_db;
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);

if ($mfavatar = $db->result_first("SELECT avatar FROM ".UC_DBTABLEPRE."memberfields WHERE uid='$uid' LIMIT 1")) {

	if($check) {
		echo 1;
		exit;
	}

	header('Location: '.$mfavatar);
	exit();
}

?>