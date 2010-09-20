DROP TABLE IF EXISTS pre_category_area;
CREATE TABLE IF NOT EXISTS pre_category_area (
  aid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  aup smallint(6) unsigned NOT NULL,
  displayorder tinyint(3) NOT NULL,
  cid smallint(6) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  PRIMARY KEY (aid),
  KEY cid (cid,displayorder),
  KEY aup (aup,displayorder)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_channel;
CREATE TABLE IF NOT EXISTS pre_category_channel (
  cid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  identifier varchar(255) NOT NULL,
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  url varchar(255) NOT NULL,
  logo varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  postgid text NOT NULL,
  managegid text NOT NULL,
  mapinfo text NOT NULL,
  listmode varchar(255) NOT NULL,
  imageinfo text NOT NULL,
  seoinfo text NOT NULL,
  PRIMARY KEY (cid),
  KEY identifier (identifier),
  KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_house_member;
CREATE TABLE IF NOT EXISTS pre_category_house_member (
  uid mediumint(8) unsigned NOT NULL,
  groupid smallint(6) unsigned NOT NULL,
  cid smallint(6) unsigned NOT NULL,
  threads mediumint(8) unsigned NOT NULL,
  todaythreads mediumint(8) unsigned NOT NULL,
  todaypush mediumint(8) unsigned NOT NULL,
  todayrecommend mediumint(8) unsigned NOT NULL,
  todayhighlight mediumint(8) unsigned NOT NULL,
  lastpost int(10) unsigned NOT NULL,
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_house_pic;
CREATE TABLE IF NOT EXISTS pre_category_house_pic (
  aid int(10) unsigned NOT NULL AUTO_INCREMENT,
  tid mediumint(8) unsigned NOT NULL,
  displayorder tinyint(3) NOT NULL,
  filename varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  dateline int(10) NOT NULL,
  PRIMARY KEY (aid),
  KEY tid (tid,displayorder)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_house_thread;
CREATE TABLE IF NOT EXISTS pre_category_house_thread (
  tid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  sortid smallint(6) unsigned NOT NULL,
  `subject` char(80) NOT NULL,
  author char(15) NOT NULL,
  authorid mediumint(8) unsigned NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  message text NOT NULL,
  ip varchar(15) NOT NULL,
  expiration tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (tid),
  KEY sortid (sortid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_house_usergroup;
CREATE TABLE IF NOT EXISTS pre_category_house_usergroup (
  gid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  cid smallint(6) unsigned NOT NULL,
  displayorder smallint(6) unsigned NOT NULL,
  title varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  icon varchar(255) NOT NULL,
  banner varchar(255) NOT NULL,
  description text NOT NULL,
  allowpost tinyint(1) NOT NULL,
  allowrecommend tinyint(1) NOT NULL,
  allowhighlight tinyint(1) NOT NULL,
  allowpush tinyint(1) NOT NULL,
  postdayper smallint(6) unsigned NOT NULL,
  pushdayper smallint(6) unsigned NOT NULL,
  recommenddayper smallint(6) unsigned NOT NULL,
  highlightdayper smallint(6) unsigned NOT NULL,
  manageuid mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (gid),
  KEY cid (cid),
  KEY displayorder (displayorder)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_sort;
CREATE TABLE IF NOT EXISTS pre_category_sort (
  sortid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  cid smallint(6) unsigned NOT NULL DEFAULT '0',
  displayorder smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  icon varchar(255) NOT NULL,
  special smallint(6) NOT NULL DEFAULT '0',
  modelid smallint(6) unsigned NOT NULL DEFAULT '0',
  expiration tinyint(1) NOT NULL DEFAULT '0',
  imgnum tinyint(3) NOT NULL,
  perpage tinyint(3) NOT NULL,
  template text NOT NULL,
  stemplate text NOT NULL,
  sttemplate text NOT NULL,
  ptemplate text NOT NULL,
  btemplate text NOT NULL,
  vtemplate text NOT NULL,
  ntemplate text NOT NULL,
  rtemplate text NOT NULL,
  threads mediumint(8) unsigned NOT NULL,
  todaythreads smallint(6) unsigned NOT NULL,
  PRIMARY KEY (sortid),
  KEY cid (cid,displayorder)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_sortoption;
CREATE TABLE IF NOT EXISTS pre_category_sortoption (
  optionid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  classid smallint(6) unsigned NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL,
  expiration tinyint(1) NOT NULL,
  protect varchar(255) NOT NULL,
  description varchar(255) NOT NULL,
  identifier varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  unit varchar(255) NOT NULL,
  rules mediumtext NOT NULL,
  PRIMARY KEY (optionid),
  KEY classid (classid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_sortoptionvar;
CREATE TABLE IF NOT EXISTS pre_category_sortoptionvar (
  sortid smallint(6) unsigned NOT NULL DEFAULT '0',
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  optionid smallint(6) unsigned NOT NULL DEFAULT '0',
  expiration int(10) unsigned NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  KEY sortid (sortid),
  KEY tid (tid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_sortvar;
CREATE TABLE IF NOT EXISTS pre_category_sortvar (
  sortid smallint(6) NOT NULL DEFAULT '0',
  optionid smallint(6) NOT NULL DEFAULT '0',
  available tinyint(1) NOT NULL DEFAULT '0',
  required tinyint(1) NOT NULL DEFAULT '0',
  unchangeable tinyint(1) NOT NULL DEFAULT '0',
  search tinyint(1) NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  subjectshow tinyint(1) NOT NULL DEFAULT '0',
  visitedshow tinyint(1) NOT NULL,
  orderbyshow tinyint(1) NOT NULL,
  mapshow tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY optionid (sortid,optionid),
  KEY sortid (sortid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_sortvalue1;
CREATE TABLE IF NOT EXISTS pre_category_sortvalue1 (
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  attachid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  expiration int(10) unsigned NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  recommend tinyint(3) NOT NULL DEFAULT '0',
  attachnum tinyint(3) NOT NULL DEFAULT '0',
  highlight tinyint(3) NOT NULL DEFAULT '0',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  city smallint(6) unsigned NOT NULL DEFAULT '0',
  district smallint(6) unsigned NOT NULL DEFAULT '0',
  street smallint(6) unsigned NOT NULL DEFAULT '0',
  mapposition VARCHAR(50) NOT NULL DEFAULT'',
  Phone mediumtext NOT NULL,
  H_toilet smallint(6) unsigned NOT NULL DEFAULT '0',
  H_hall smallint(6) unsigned NOT NULL DEFAULT '0',
  H_room smallint(6) unsigned NOT NULL DEFAULT '0',
  H_Address mediumtext NOT NULL,
  H_Type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_balcony smallint(6) unsigned NOT NULL DEFAULT '0',
  H_toward smallint(6) unsigned NOT NULL DEFAULT '0',
  H_floor_total int(10) unsigned NOT NULL DEFAULT '0',
  H_floor_rent int(10) unsigned NOT NULL DEFAULT '0',
  H_area int(10) unsigned NOT NULL DEFAULT '0',
  H_rents mediumint(8) NOT NULL,
  H_payment_type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_deposit smallint(6) unsigned NOT NULL DEFAULT '0',
  H_owner_type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_total_rent_type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_in_date mediumtext NOT NULL,
  H_equipment mediumtext NOT NULL,
  H_bus_info mediumtext NOT NULL,
  H_decoration smallint(6) unsigned NOT NULL DEFAULT '0',
  H_shortest_lease smallint(6) unsigned NOT NULL DEFAULT '0',
  H_Additional mediumtext NOT NULL,
  contactor mediumtext NOT NULL,
  H_name mediumtext NOT NULL,
  qq mediumtext NOT NULL,
  KEY tid (tid),
  KEY groupid (groupid),
  KEY dateline (dateline),
  KEY city (city),
  KEY district (district),
  KEY street (street),
  KEY H_toilet (H_toilet),
  KEY H_hall (H_hall),
  KEY H_room (H_room),
  KEY H_Type (H_Type),
  KEY H_balcony (H_balcony),
  KEY H_toward (H_toward),
  KEY H_floor_total (H_floor_total),
  KEY H_floor_rent (H_floor_rent),
  KEY H_area (H_area),
  KEY H_payment_type (H_payment_type),
  KEY H_deposit (H_deposit),
  KEY H_owner_type (H_owner_type),
  KEY H_total_rent_type (H_total_rent_type),
  KEY H_decoration (H_decoration),
  KEY H_shortest_lease (H_shortest_lease)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_sortvalue2;
CREATE TABLE IF NOT EXISTS pre_category_sortvalue2 (
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  attachid int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  expiration int(10) unsigned NOT NULL DEFAULT '0',
  displayorder tinyint(3) NOT NULL DEFAULT '0',
  recommend tinyint(3) NOT NULL DEFAULT '0',
  attachnum tinyint(3) NOT NULL DEFAULT '0',
  highlight tinyint(3) NOT NULL DEFAULT '0',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  city smallint(6) unsigned NOT NULL DEFAULT '0',
  district smallint(6) unsigned NOT NULL DEFAULT '0',
  street smallint(6) unsigned NOT NULL DEFAULT '0',
  Phone mediumtext NOT NULL,
  mapposition VARCHAR(50) NOT NULL DEFAULT'',
  H_name mediumtext NOT NULL,
  H_Type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_room_type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_Address mediumtext NOT NULL,
  H_room smallint(6) unsigned NOT NULL DEFAULT '0',
  H_hall smallint(6) unsigned NOT NULL DEFAULT '0',
  H_toilet smallint(6) unsigned NOT NULL DEFAULT '0',
  H_balcony smallint(6) unsigned NOT NULL DEFAULT '0',
  H_toward smallint(6) unsigned NOT NULL DEFAULT '0',
  H_floor_total int(10) unsigned NOT NULL DEFAULT '0',
  H_floor_rent int(10) unsigned NOT NULL DEFAULT '0',
  H_area int(10) unsigned NOT NULL DEFAULT '0',
  H_use_area int(10) unsigned NOT NULL DEFAULT '0',
  H_building_year smallint(6) unsigned NOT NULL DEFAULT '0',
  H_owner_type_1 smallint(6) unsigned NOT NULL DEFAULT '0',
  H_total_price mediumtext NOT NULL,
  H_price_condition smallint(6) unsigned NOT NULL DEFAULT '0',
  H_price_description smallint(6) unsigned NOT NULL DEFAULT '0',
  H_owner_type smallint(6) unsigned NOT NULL DEFAULT '0',
  H_in_date mediumtext NOT NULL,
  H_equipment mediumtext NOT NULL,
  H_decoration smallint(6) unsigned NOT NULL DEFAULT '0',
  H_Frame smallint(6) unsigned NOT NULL DEFAULT '0',
  H_bus_info mediumtext NOT NULL,
  H_feature mediumtext NOT NULL,
  H_Additional mediumtext NOT NULL,
  contactor mediumtext NOT NULL,
  KEY tid (tid),
  KEY groupid (groupid),
  KEY dateline (dateline),
  KEY city (city),
  KEY district (district),
  KEY street (street),
  KEY H_Type (H_Type),
  KEY H_room_type (H_room_type),
  KEY H_room (H_room),
  KEY H_hall (H_hall),
  KEY H_toilet (H_toilet),
  KEY H_balcony (H_balcony),
  KEY H_toward (H_toward),
  KEY H_floor_total (H_floor_total),
  KEY H_floor_rent (H_floor_rent),
  KEY H_area (H_area),
  KEY H_use_area (H_use_area),
  KEY H_building_year (H_building_year),
  KEY H_owner_type_1 (H_owner_type_1),
  KEY H_price_condition (H_price_condition),
  KEY H_price_description (H_price_description),
  KEY H_owner_type (H_owner_type),
  KEY H_decoration (H_decoration),
  KEY H_Frame (H_Frame)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_phonecount;
CREATE TABLE IF NOT EXISTS pre_category_phonecount (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  number varchar(255) NOT NULL,
  count smallint(6) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY number (number)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_payoption;
CREATE TABLE IF NOT EXISTS pre_category_payoption (
  tid mediumint(8) unsigned NOT NULL,
  optionid mediumint(8) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  KEY tid (tid),
  KEY optionid (optionid),
  KEY uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pre_category_threadmod;
CREATE TABLE IF NOT EXISTS pre_category_threadmod (
  tid mediumint(8) unsigned NOT NULL,
  expiration int(10) unsigned NOT NULL,
  `action` varchar(255) CHARACTER SET utf8 NOT NULL,
  KEY tid (tid,`action`)
) ENGINE=MyISAM;
