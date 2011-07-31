
#安裝後預設關閉論壇
UPDATE `pre_common_setting` SET `svalue` = '1' WHERE `skey` = 'bbclosed';

#安裝後預設關閉註冊
UPDATE `pre_common_setting` SET `svalue` = '0' WHERE `skey` = 'regstatus';
