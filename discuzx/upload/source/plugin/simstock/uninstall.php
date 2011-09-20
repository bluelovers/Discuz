<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-06-18
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$sql = <<<EOF
DROP TABLE IF EXISTS pre_kfss_apply;
DROP TABLE IF EXISTS pre_kfss_customer;
DROP TABLE IF EXISTS pre_kfss_deal;
DROP TABLE IF EXISTS pre_kfss_news;
DROP TABLE IF EXISTS pre_kfss_sminfo;
DROP TABLE IF EXISTS pre_kfss_smlog;
DROP TABLE IF EXISTS pre_kfss_stock;
DROP TABLE IF EXISTS pre_kfss_transaction;
DROP TABLE IF EXISTS pre_kfss_user;
EOF;
runquery($sql);
$finish = TRUE;
?>
