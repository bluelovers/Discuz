<?php
if(!defined('IN_DISCUZ')) {
	exit('Access denied');
}
$sql = <<<EOT
DROP TABLE IF EXISTS pre_plugin_auction;
DROP TABLE IF EXISTS pre_plugin_auctionapply;
DROP TABLE IF EXISTS pre_plugin_auction_message;
DROP TABLE IF EXISTS pre_plugin_auction_xml;
EOT;

runquery($sql);

$finish = true;
?>
