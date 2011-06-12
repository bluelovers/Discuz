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
$fromversion = '9.4.0 Beta 2';
$toversion = '9.4.2';
$sql = <<<EOF
ALTER TABLE `kfsm_customer` ADD `ip` VARCHAR( 20 ) NOT NULL DEFAULT '';
EOF;
runquery($sql);
$finish = TRUE;
?>
