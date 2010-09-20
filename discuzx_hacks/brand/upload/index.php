<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 4378 2010-09-09 02:55:13Z fanshengshuai $
 */
define('CURSCRIPT', 'index');
require_once('./common.php');

$bannerhtml = htmlspecialchars_decode($_G['brandads']['banner']);

$active['index'] = " class=\"active\"";
$location['title'] = $_G['setting']['site_nav']['index']['name'];

include template('templates/site/default/index.html.php', 1);

ob_out(); //正則處理url/模板

?>