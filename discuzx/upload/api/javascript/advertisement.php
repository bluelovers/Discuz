<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: advertisement.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60).' GMT');

if(!defined('IN_API')) {
	exit('document.write(\'Access Denied\')');
}

loadcore();

$adid = $_G['gp_adid'];
$data = adshow($adid);

echo 'document.write(\''.preg_replace("/\r\n|\n|\r/", '\n', addcslashes($data, "'\\")).'\');';

?>