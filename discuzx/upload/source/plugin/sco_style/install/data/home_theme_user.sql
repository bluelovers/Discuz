
CREATE TABLE `pre_home_theme_user` (
`uid` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0',
`theme_id` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0',
`theme_disable` TINYINT( 1 ) NOT NULL DEFAULT '0',
INDEX ( `theme_id` ) ,
UNIQUE (
`uid`
)
) ENGINE = MYISAM ;
