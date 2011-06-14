<?php

/*
	[qqcat_picexif] (C) qqcat 2009-2010
	$File: upgrade.php, v1.0.1
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@require_once DISCUZ_ROOT.'./plugins/qqcat_picexif/ver.php';
echo "<script type=\"text/javascript\" src=\"$p_url?a=upgrade&u=$boardurl&pn=".$p_name."&v=".$p_ver."&dz=$version\"></script>";
$finish = true;

?>