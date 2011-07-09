<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$operation = in_array($operation, array('index', 'setting', 'credits', 'category', 'power', 'manage', 'collectset', 'collect', 'batchcollect', 'diy', 'cron', 'cache')) ? $operation : 'index';
$do = $do ? $do : 'show';

cpheader();

require_once libfile('function/pdnovelcp');

require './source/module/pdnovel/admincp_'.$operation.'.php';

?>