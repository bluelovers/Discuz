<?php

/*
	$Id: $
*/

scAddHooks('CheckMethodAuthBefore', 'eCheckMethodAuthBefore');

function eCheckMethodAuthBefore (&$methodarray) {
	if (!in_array('sc', $methodarray)) $methodarray[] = 'sc';
}

?>