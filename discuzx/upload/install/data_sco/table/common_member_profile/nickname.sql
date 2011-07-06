
ALTER TABLE pre_common_member_profile ADD `nickname` VARCHAR( 255 ) NOT NULL DEFAULT '' COMMENT '暱稱';
INSERT INTO pre_common_member_profile_setting (
`fieldid` ,
`available` ,
`invisible` ,
`needverify` ,
`title` ,
`description` ,
`displayorder` ,
`required` ,
`unchangeable` ,
`showinthread` ,
`allowsearch` ,
`formtype` ,
`size` ,
`choices` ,
`validate`
)
VALUES (
'nickname', '1', '0', '0', '暱稱', '', '0', '0', '0', '0', '1', 'text', '0', '', ''
);
