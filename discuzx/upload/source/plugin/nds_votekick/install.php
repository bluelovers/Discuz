<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF

DROP TABLE IF EXISTS pre_nds_votekick;
CREATE TABLE pre_nds_votekick (
  `tid` mediumint(8)  unsigned not null,
  `votes` int(5) unsigned not null default 0,
  `uids` char(80) not null,
  PRIMARY KEY  (`tid`)
) ENGINE=MyISAM;
EOF;

runquery($sql);
$finish = TRUE;

?>