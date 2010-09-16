--
-- Discuz! XPlus INSTALL MAKE SQL DUMP V1.0
-- DO NOT modify this file
--
-- Create: 2010-08-30 15:36:24
--

DROP TABLE IF EXISTS pre_common_admincp_group;
CREATE TABLE pre_common_admincp_group (
  cpgroupid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  cpgroupname varchar(255) NOT NULL,
  PRIMARY KEY (cpgroupid)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pre_common_admincp_member;
CREATE TABLE pre_common_admincp_member (
  uid int(10) unsigned NOT NULL,
  cpgroupid int(10) unsigned NOT NULL,
  customperm text NOT NULL,
  PRIMARY KEY (uid),
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_admincp_perm;
CREATE TABLE pre_common_admincp_perm (
  cpgroupid smallint(6) unsigned NOT NULL,
  perm varchar(255) NOT NULL,
  UNIQUE KEY cpgroupperm (cpgroupid,perm),
  KEY cpgroupid (cpgroupid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_admincp_session;
CREATE TABLE pre_common_admincp_session (
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  adminid smallint(6) unsigned NOT NULL DEFAULT '0',
  panel tinyint(1) NOT NULL DEFAULT '0',
  ip varchar(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  errorcount tinyint(1) NOT NULL DEFAULT '0',
  `storage` mediumtext NOT NULL,
  PRIMARY KEY (uid,panel)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_adminsession;
CREATE TABLE pre_common_adminsession (
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  adminid smallint(6) unsigned NOT NULL DEFAULT '0',
  panel tinyint(1) NOT NULL DEFAULT '0',
  ip varchar(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  errorcount tinyint(1) NOT NULL DEFAULT '0',
  `storage` mediumtext NOT NULL,
  PRIMARY KEY (uid,panel)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_attachment;
CREATE TABLE pre_common_attachment (
  aid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  mid smallint(6) unsigned NOT NULL,
  authorid mediumint(8) unsigned NOT NULL,
  filesize int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  filename varchar(255) NOT NULL,
  url text NOT NULL,
  PRIMARY KEY (aid),
  KEY mid (mid)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pre_common_cron;
CREATE TABLE pre_common_cron (
  cronid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  available tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system') NOT NULL DEFAULT 'user',
  `name` char(50) NOT NULL DEFAULT '',
  filename char(50) NOT NULL DEFAULT '',
  lastrun int(10) unsigned NOT NULL DEFAULT '0',
  nextrun int(10) unsigned NOT NULL DEFAULT '0',
  weekday tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (cronid),
  KEY nextrun (available,nextrun)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_credit;
CREATE TABLE pre_common_credit (
  creditid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  mid smallint(6) NOT NULL,
  title varchar(255) NOT NULL,
  unit varchar(255) NOT NULL,
  icon varchar(255) NOT NULL,
  inital int(10) unsigned NOT NULL DEFAULT '0',
  form tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (creditid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_failedlogin;
CREATE TABLE pre_common_failedlogin (
  ip char(15) NOT NULL DEFAULT '',
  count tinyint(1) unsigned NOT NULL DEFAULT '0',
  lastupdate int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (ip)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_log;
CREATE TABLE pre_common_log (
  logid int(10) unsigned NOT NULL AUTO_INCREMENT,
  mid smallint(6) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  ip varchar(255) NOT NULL,
  content text NOT NULL,
  PRIMARY KEY (logid),
  KEY dateline (dateline),
  KEY mid (mid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_member;
CREATE TABLE pre_common_member (
  uid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  email char(40) NOT NULL DEFAULT '',
  username char(15) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  emailstatus tinyint(1) NOT NULL DEFAULT '0',
  adminid tinyint(1) NOT NULL DEFAULT '0',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  regdate int(10) unsigned NOT NULL DEFAULT '0',
  credits int(10) NOT NULL DEFAULT '0',
  allowadmincp tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY username (username)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_member_credit;
CREATE TABLE pre_common_member_credit (
  uid mediumint(8) unsigned NOT NULL,
  creditid smallint(6) unsigned NOT NULL,
  num int(10) unsigned NOT NULL,
  KEY uid (uid,creditid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_member_status;
CREATE TABLE pre_common_member_status (
  uid mediumint(8) unsigned NOT NULL,
  regip char(15) NOT NULL DEFAULT '',
  lastip char(15) NOT NULL DEFAULT '',
  lastvisit int(10) unsigned NOT NULL DEFAULT '0',
  lastactivity int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_member_profile_setting;
CREATE TABLE pre_common_member_profile_setting (
  fieldid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  mid smallint(6) unsigned NOT NULL,
  identifier varchar(255) NOT NULL DEFAULT '',
  available tinyint(1) NOT NULL DEFAULT '0',
  invisible tinyint(1) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  description varchar(255) NOT NULL DEFAULT '',
  displayorder smallint(6) unsigned NOT NULL DEFAULT '0',
  required tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  rules text NOT NULL,
  PRIMARY KEY (fieldid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_module;
CREATE TABLE pre_common_module (
  mid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  identifier varchar(255) NOT NULL,
  version varchar(255) NOT NULL,
  apikey varchar(255) NOT NULL,
  displayorder tinyint(3) unsigned NOT NULL,
  available tinyint(3) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (mid)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pre_common_nav;
CREATE TABLE pre_common_nav (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  target tinyint(1) NOT NULL DEFAULT '0',
  available tinyint(1) NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL,
  highlight tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pre_common_process;
CREATE TABLE pre_common_process (
  processid char(32) NOT NULL,
  expiry int(10) DEFAULT NULL,
  extra int(10) DEFAULT NULL,
  PRIMARY KEY (processid),
  KEY expiry (expiry)
) TYPE=MEMORY;

DROP TABLE IF EXISTS pre_common_session;
CREATE TABLE pre_common_session (
  sid char(6) NOT NULL DEFAULT '',
  ip1 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip2 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip3 tinyint(3) unsigned NOT NULL DEFAULT '0',
  ip4 tinyint(3) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username char(15) NOT NULL DEFAULT '',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  invisible tinyint(1) NOT NULL DEFAULT '0',
  `action` tinyint(1) unsigned NOT NULL DEFAULT '0',
  lastactivity int(10) unsigned NOT NULL DEFAULT '0',
  fid mediumint(8) unsigned NOT NULL DEFAULT '0',
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY sid (sid),
  KEY uid (uid)
) TYPE=MEMORY;

DROP TABLE IF EXISTS pre_common_setting;
CREATE TABLE pre_common_setting (
  skey varchar(255) NOT NULL DEFAULT '',
  stype varchar(255) NOT NULL DEFAULT '',
  svalue text NOT NULL,
  PRIMARY KEY (skey)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_syscache;
CREATE TABLE pre_common_syscache (
  cname varchar(32) NOT NULL,
  ctype tinyint(3) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (cname)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_template;
CREATE TABLE pre_common_template (
  templateid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  directory varchar(100) NOT NULL DEFAULT '',
  `available` tinyint(1) NOT NULL,
  mid smallint(6) unsigned NOT NULL,
  copyright varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (templateid),
  KEY mid (mid,`available`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_usergroup;
CREATE TABLE pre_common_usergroup (
  groupid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('system','special','member') NOT NULL DEFAULT 'member',
  system varchar(255) NOT NULL DEFAULT 'private',
  grouptitle varchar(255) NOT NULL DEFAULT '',
  allowvisit tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (groupid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_common_admincp_perm;
CREATE TABLE pre_common_admincp_perm (
  cpgroupid smallint(6) unsigned NOT NULL,
  perm varchar(255) NOT NULL,
  UNIQUE KEY cpgroupperm (cpgroupid,perm),
  KEY cpgroupid (cpgroupid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_poll_choice;
CREATE TABLE pre_poll_choice (
  choiceid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  itemid mediumint(8) unsigned NOT NULL,
  caption varchar(255) NOT NULL,
  displayorder tinyint(3) unsigned NOT NULL DEFAULT '0',
  imageurl varchar(255) NOT NULL,
  detailurl varchar(255) NOT NULL,
  pollnum mediumint(8) unsigned NOT NULL DEFAULT '0',
  aid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (choiceid),
  KEY itemid (itemid),
  KEY itemid_displayorder (itemid,displayorder),
  KEY itemid_pollnum (itemid,pollnum)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_poll_item;
CREATE TABLE pre_poll_item (
  itemid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  templateid smallint(6) unsigned NOT NULL DEFAULT '1',
  choicenum mediumint(8) unsigned NOT NULL DEFAULT '0',
  numperpage smallint(6) unsigned NOT NULL DEFAULT '0',
  limittime mediumint(8) unsigned NOT NULL DEFAULT '0',
  totalnum mediumint(8) unsigned NOT NULL DEFAULT '0',
  title char(80) NOT NULL,
  available tinyint(1) unsigned NOT NULL DEFAULT '1',
  repeattype smallint(6) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  contenttype tinyint(3) NOT NULL DEFAULT '0',
  resultview_mod tinyint(1) NOT NULL DEFAULT '1',
  resultview_time tinyint(1) NOT NULL DEFAULT '0',
  errordetail tinyint(1) NOT NULL DEFAULT '0',
  username char(15) NOT NULL,
  dateline int(10) unsigned NOT NULL,
  starttime int(10) unsigned NOT NULL,
  endtime int(10) unsigned NOT NULL,
  PRIMARY KEY (itemid),
  KEY available (available)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pre_poll_item_field;
CREATE TABLE pre_poll_item_field (
  itemid mediumint(8) unsigned NOT NULL,
  description varchar(255) NOT NULL,
  seokeywords varchar(255) NOT NULL,
  seodesc varchar(255) NOT NULL,
  creditrule text NOT NULL,
  lazyload tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (itemid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_poll_setting;
CREATE TABLE pre_poll_setting (
  skey varchar(255) NOT NULL DEFAULT '',
  stype varchar(255) NOT NULL DEFAULT '',
  svalue text NOT NULL,
  PRIMARY KEY (skey)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_poll_value;
CREATE TABLE pre_poll_value (
  itemid mediumint(8) unsigned NOT NULL,
  choiceid mediumint(8) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  ip varchar(15) NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  KEY itemid (itemid),
  KEY choiceid (choiceid),
  KEY choiceid_dateline (choiceid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_poll_choice_count;
CREATE TABLE pre_poll_choice_count (
  choiceid mediumint(8) unsigned NOT NULL,
  itemid mediumint(8) unsigned NOT NULL,
  ip varchar(255) NOT NULL,
  area varchar(255) NOT NULL,
  count mediumint(8) unsigned NOT NULL,
  KEY choiceid (choiceid,ip),
  KEY choiceid_count (choiceid,count)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pre_poll_item_count;
CREATE TABLE pre_poll_item_count (
  itemid mediumint(8) unsigned NOT NULL,
  ip varchar(255) NOT NULL,
  area varchar(255) NOT NULL,
  count mediumint(8) unsigned NOT NULL,
  KEY itemid (itemid,ip),
  KEY itemid_count (itemid,count)
) TYPE=MyISAM;

