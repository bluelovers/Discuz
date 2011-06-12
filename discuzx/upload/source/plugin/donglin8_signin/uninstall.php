<?php

/*
	ц©хугюб╔г╘╣╫ for DX 1.5
	Powered by Donglin8.Com 2010.10
*/

if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
}
$plugintable = DB::table('forum_donglin8_signin');
$sql = <<<EOF
	DROP TABLE IF EXISTS $plugintable;
EOF;

runquery($sql);
$finish = TRUE;

?>