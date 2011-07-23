<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-07-20
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$fromversion = '9.4.3';
$toversion = '9.4.4';
$sql = <<<EOF
ALTER TABLE `pre_kfsm_customer` ADD INDEX ( `cid` , `sid` );
EOF;
runquery($sql);
$finish = TRUE;
?>
