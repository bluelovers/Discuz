<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 527 2010-08-30 05:57:14Z yexinhao $
 */

@include './config/config_global.php';
if(empty($_config)) {
	if(!file_exists('./data/install.lock')) {
		header('location: install');
		exit;
	} else {
		error('config_notfound');
	}
}

$_t_curapp = '';

foreach($_config['app']['domain'] as $_t_app => $_t_domain) {
	if($_t_domain == $_SERVER['HTTP_HOST']) {
		$_t_curapp = $_t_app;
		break;
	}
}

if(empty($_t_curapp) || $_t_curapp == 'default') {
	$_t_curapp = !empty($_config['app']['default']) ? $_config['app']['default'] : 'admin';
}

$_SERVER['PHP_SELF'] = str_replace('index.php', $_t_curapp.'.php', $_SERVER['PHP_SELF']);

require './'.$_t_curapp.'.php';

?>