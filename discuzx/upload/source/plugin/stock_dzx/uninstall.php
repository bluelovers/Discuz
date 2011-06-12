<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-05-21
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$sql = <<<EOF
DROP TABLE IF EXISTS kfsm_apply;
DROP TABLE IF EXISTS kfsm_customer;
DROP TABLE IF EXISTS kfsm_deal;
DROP TABLE IF EXISTS kfsm_news;
DROP TABLE IF EXISTS kfsm_sminfo;
DROP TABLE IF EXISTS kfsm_smlog;
DROP TABLE IF EXISTS kfsm_stock;
DROP TABLE IF EXISTS kfsm_transaction;
DROP TABLE IF EXISTS kfsm_user;
EOF;
runquery($sql);
$finish = TRUE;
?>
