<?php

/*
	$HeadURL:  $
	$Revision: $
	$Author: $
	$Date: $
	$Id:  $
*/

scAddHooks('CheckMethodAuthBefore_InAdmin', 'eCheckMethodAuthBefore_InAdmin');

function eCheckMethodAuthBefore_InAdmin (&$methodarray) {

	if (!in_array($_SERVER['REMOTE_ADDR'], array('122.116.39.240', '192.168.0.25', '192.168.1.25'))) {
		exit(header("HTTP/1.1 403 Forbidden"));
	}

}

?>