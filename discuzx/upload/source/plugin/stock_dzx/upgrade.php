<?php
/*
 * Kilofox Services
 * StockIns v9.4
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
$fromversion = '9.4.2';
$toversion = '9.4.3';
$sql = <<<EOF
RENAME TABLE kfsm_apply TO pre_kfsm_apply;
RENAME TABLE kfsm_customer TO pre_kfsm_customer;
RENAME TABLE kfsm_deal TO pre_kfsm_deal;
RENAME TABLE kfsm_news TO pre_kfsm_news;
RENAME TABLE kfsm_sminfo TO pre_kfsm_sminfo;
RENAME TABLE kfsm_smlog TO pre_kfsm_smlog;
RENAME TABLE kfsm_stock TO pre_kfsm_stock;
RENAME TABLE kfsm_transaction TO pre_kfsm_transaction;
RENAME TABLE kfsm_user TO pre_kfsm_user;
EOF;
runquery($sql);
$finish = TRUE;
?>
