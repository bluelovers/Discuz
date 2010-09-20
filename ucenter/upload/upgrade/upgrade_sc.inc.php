<?php

/*
	UCenter [SC] (C)2000-2009 Bluelovers Net.

	$Id: upgrade_sc.inc.php 3 2009-10-15 17:51:15Z bluelovers $
*/

$upgradetable = array(

	array('memberfields', 'ADD', 'avatar', "VARCHAR( 255 ) NOT NULL DEFAULT ''"),
	array('memberfields', 'ADD', 'nickname', "VARCHAR( 30 ) NOT NULL DEFAULT '' AFTER `uid`"),

	array('members', 'ADD', 'gender', "TINYINT( 1 ) NOT NULL DEFAULT '0'"),
	array('members', 'ADD', 'bday', "DATE NOT NULL DEFAULT '0000-00-00'"),
	array('members', 'ADD', 'timeoffset', "CHAR( 4 ) NOT NULL DEFAULT '9999'"),

	array('memberfields', 'CHANGE', 'avatar', "`avatar` TEXT NOT NULL"),

);

?>