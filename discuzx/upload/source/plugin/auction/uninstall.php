<?php
if(!defined('IN_DISCUZ')) {
	exit('Access denied');
}
$sql = <<<EOT
DROP TABLE IF EXISTS pre_plugin_auction;
DROP TABLE IF EXISTS pre_plugin_auctionapply;
EOT;

runquery($sql);
$finish = true;

?>
