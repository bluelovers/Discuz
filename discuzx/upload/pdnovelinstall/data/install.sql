CREATE TABLE IF NOT EXISTS pre_pdmodule_power (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  action varchar(255) NOT NULL,
  moduleid int(10) unsigned NOT NULL default '0',
  power varchar(255) NOT NULL,
  PRIMARY KEY (id),
  KEY moduleid (moduleid)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_pdnovel_collect (
  siteid smallint(6) unsigned NOT NULL auto_increment,
  sitename varchar(255) NOT NULL,
  siteurl varchar(255) NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  enable tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (siteid),
  KEY siteurl (siteurl),
  KEY enable (enable)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_pdmodule_view (
  moduleid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  displayorder smallint(6) unsigned NOT NULL default '0',
  version varchar(255) NOT NULL,
  status smallint(3) unsigned NOT NULL default '0',
  alias varchar(255) NOT NULL,
  seotitle varchar(255) NOT NULL,
  seokeywords varchar(255) NOT NULL,
  seodescription varchar(255) NOT NULL,
  seohead varchar(255) NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (moduleid),
  KEY dateline (dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_author;
CREATE TABLE IF NOT EXISTS pre_pdnovel_author (
  authorid int(10) unsigned NOT NULL AUTO_INCREMENT,
  author char(50) NOT NULL,
  PRIMARY KEY (authorid)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_pdnovel_category (
  catid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  upid mediumint(8) unsigned NOT NULL default '0',
  catname varchar(255) NOT NULL default '',
  caption varchar(255) NOT NULL default '',
  displayorder smallint(6) NOT NULL default '0',
  description text NOT NULL,
  keyword text NOT NULL,
  PRIMARY KEY (catid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_chapter;
CREATE TABLE IF NOT EXISTS pre_pdnovel_chapter (
  chapterid int(10) unsigned NOT NULL AUTO_INCREMENT,
  novelid int(10) unsigned NOT NULL default '0',
  volumeid int(10) unsigned NOT NULL default '0',
  posterid int(10) unsigned NOT NULL default '0',
  poster varchar(30) NOT NULL default '',
  postdate int(10) unsigned NOT NULL default '0',
  lastupdate int(10) unsigned NOT NULL default '0',
  chaptername varchar(100) NOT NULL default '',
  chapterorder smallint(6) unsigned NOT NULL default '0',
  chaptertype tinyint(3) unsigned NOT NULL default '0',
  chaptercontent text NOT NULL,
  chapterwords int(10) unsigned NOT NULL default '0',
  display tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (chapterid),
  KEY novelid (novelid),
  KEY chaptertype (chaptertype),
  KEY volumeid (volumeid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_comment;
CREATE TABLE IF NOT EXISTS pre_pdnovel_comment (
  cid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(255) NOT NULL default '',
  novelid int(10) unsigned NOT NULL default '0',
  postip varchar(255) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  status tinyint(1) unsigned NOT NULL default '0',
  message text NOT NULL,
  PRIMARY KEY (cid),
  KEY novelid (novelid,cid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_download;
CREATE TABLE IF NOT EXISTS pre_pdnovel_download (
  did int(11) unsigned NOT NULL AUTO_INCREMENT,
  novelid int(11) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  price smallint(6) unsigned NOT NULL default '0',
  name char(100) NOT NULL default '',
  type char(50) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  path char(100) NOT NULL default '',
  downloads mediumint(8) NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (did),
  KEY uid (uid),
  KEY dateline (dateline,downloads),
  KEY novelid (novelid)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

DROP TABLE IF EXISTS pre_pdnovel_mark;
CREATE TABLE IF NOT EXISTS pre_pdnovel_mark (
  markid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL default '0',
  novelid int(10) unsigned NOT NULL default '0',
  chapterid int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (markid),
  KEY uid (uid,dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_rate;
CREATE TABLE IF NOT EXISTS pre_pdnovel_rate (
  rateid int(10) unsigned NOT NULL AUTO_INCREMENT,
  novelid int(10) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  credits tinyint(1) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (rateid),
  KEY dateline (dateline),
  KEY novelid (novelid,dateline)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_star;
CREATE TABLE IF NOT EXISTS pre_pdnovel_star (
  starid int(10) unsigned NOT NULL AUTO_INCREMENT,
  novelid int(10) unsigned NOT NULL,
  clickid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY (starid),
  KEY uid (uid),
  KEY novelid (novelid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_view;
CREATE TABLE IF NOT EXISTS pre_pdnovel_view (
  novelid int(10) unsigned NOT NULL AUTO_INCREMENT,
  catid mediumint(8) unsigned NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  initial char(1) NOT NULL default '',
  cover varchar(255) NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  lastupdate int(10) unsigned NOT NULL default '0',
  keyword varchar(255) NOT NULL default '',
  intro text NOT NULL,
  notice text NOT NULL,
  permission tinyint(1) unsigned NOT NULL default '0',
  first tinyint(1) unsigned NOT NULL default '0',
  full tinyint(1) unsigned NOT NULL default '0',
  vip tinyint(1) unsigned NOT NULL default '0',
  authorid int(10) unsigned NOT NULL default '0',
  author varchar(30) NOT NULL default '',
  posterid int(10) unsigned NOT NULL default '0',
  poster varchar(30) NOT NULL default '',
  adminid int(10) unsigned NOT NULL default '0',
  admin varchar(30) NOT NULL default '',
  comments int(10) unsigned NOT NULL default '0',
  volumes int(10) unsigned NOT NULL default '0',
  chapters int(10) unsigned NOT NULL default '0',
  lastvolumeid int(10) unsigned NOT NULL default '0',
  lastvolume varchar(100) NOT NULL default '',
  lastchapterid int(10) unsigned NOT NULL default '0',
  lastchapter varchar(100) NOT NULL default '',
  lastchaptercontent text NOT NULL,
  words int(10) unsigned NOT NULL default '0',
  lastvisit int(10) unsigned NOT NULL default '0',
  dayvisit int(10) unsigned NOT NULL default '0',
  weekvisit int(10) unsigned NOT NULL default '0',
  monthvisit int(10) unsigned NOT NULL default '0',
  allvisit int(10) unsigned NOT NULL default '0',
  lastvote int(10) unsigned NOT NULL default '0',
  dayvote int(10) unsigned NOT NULL default '0',
  weekvote int(10) unsigned NOT NULL default '0',
  monthvote int(10) unsigned NOT NULL default '0',
  allvote int(10) unsigned NOT NULL default '0',
  allmark int(10) unsigned NOT NULL default '0',
  rate int(10) unsigned NOT NULL default '0',
  click int(10) unsigned NOT NULL default '0',
  click1 int(10) unsigned NOT NULL default '0',
  click2 int(10) unsigned NOT NULL default '0',
  click3 int(10) unsigned NOT NULL default '0',
  click4 int(10) unsigned NOT NULL default '0',
  click5 int(10) unsigned NOT NULL default '0',
  recommend tinyint(1) unsigned NOT NULL default '0',
  unverify tinyint(1) unsigned NOT NULL default '0',
  display tinyint(1) unsigned NOT NULL default '0',
  type tinyint(3) unsigned NOT NULL default '0',
  siteid tinyint(3) unsigned NOT NULL default '0',
  fromid int(10) unsigned NOT NULL default '0',
  lcid int(10) unsigned NOT NULL default '0',
  lvid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (novelid),
  KEY lastupdate (lastupdate),
  KEY posterid (posterid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_volume;
CREATE TABLE IF NOT EXISTS pre_pdnovel_volume (
  volumeid int(10) unsigned NOT NULL AUTO_INCREMENT,
  novelid int(10) unsigned NOT NULL default '0',
  volumename varchar(100) NOT NULL default '',
  volumeorder smallint(6) unsigned NOT NULL default '0',
  volumewords int(10) unsigned NOT NULL default '0',
  volumechapters int(10) unsigned NOT NULL default '0',
  display tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (volumeid),
  KEY volumeorder (volumeorder),
  KEY novelid (novelid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_pdnovel_vote;
CREATE TABLE IF NOT EXISTS pre_pdnovel_vote (
  voteid int(10) unsigned NOT NULL AUTO_INCREMENT,
  novelid int(10) unsigned NOT NULL,
  uid int(10) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY (voteid),
  KEY uid (uid),
  KEY novelid (novelid)
) ENGINE=MyISAM;