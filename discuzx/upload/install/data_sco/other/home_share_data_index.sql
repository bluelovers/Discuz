
ALTER TABLE `pre_home_share` ADD `data_index` VARCHAR( 255 ) NOT NULL ,
ADD `image_1` VARCHAR( 255 ) NOT NULL ,
ADD `image_2` VARCHAR( 255 ) NOT NULL ,
ADD `image_3` VARCHAR( 255 ) NOT NULL ,
ADD `image_4` VARCHAR( 255 ) NOT NULL ,
ADD INDEX ( `data_index` );
