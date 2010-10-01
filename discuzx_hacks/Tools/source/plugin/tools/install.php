<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 2255 2010-09-10 08:45:39Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_tools_censorhome;
CREATE TABLE IF NOT EXISTS `pre_tools_censorhome` (
  `id` int(10) NOT NULL auto_increment,
  `itemid` int(10) NOT NULL,
  `type` char(200) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `re` (`itemid`,`type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_tools_rule;
CREATE TABLE IF NOT EXISTS pre_tools_rule (
  `name` char(50) NOT NULL,
  `rule` char(255) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM;

REPLACE INTO `pre_tools_rule` (`name`, `rule`, `comment`) VALUES
('first', '/eval\\\\(\\\\\$_(POST|GET)\\\\[(.*?)\\\\]\\\\)/ies', ''),
('sec', '/eval.*?\\\\(base64_decode\\\\([\'"](.*?)[\'"]\\\\)[\\\\)]*\\\\)/ies', '');
EOF;

runquery($sql);

$finish = TRUE;

?>