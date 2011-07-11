<?php

/**
 *      [Discuz! X1.5] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $author : CongYuShuai(Max.Cong) Date:2010-09-09$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF
DROP TABLE IF EXISTS pre_plugin_groupfriend;
EOF;
runquery($sql);
$finish = TRUE;
?>